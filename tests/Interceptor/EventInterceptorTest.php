<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\EventInterceptor;
use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event\EventHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\ClassImplementingUseCaseInterface;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\ClassWithEventAttributes;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\ClassWithInvalidEventAttributes;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class EventInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private EventHandlerMock $handler;

    private ClassWithEventAttributes $proxy;

    private ProxyFactory $proxyFactory;

    protected function setUp(): void
    {
        $this->handler = new EventHandlerMock();
        $this->proxyFactory = $this->getProxyFactory(
            [
                new EventInterceptor(
                    [$this->handler],
                ),
            ]
        );
        $this->proxy = $this->proxyFactory->createProxy(new ClassWithEventAttributes());
    }

    public function testInvalidMethodEventThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $proxy = $this->proxyFactory->createProxy(new ClassWithInvalidEventAttributes());

        $proxy->eventWithWrongMethods('whatever');
    }

    public function testOnExceptionEventSendException(): void
    {
        try {
            $this->proxy->eventOnException('whatever');
        } catch (\Exception $e) {
            $this->assertEventsCount(1);
            $this->assertEvent(
                'use_case.exception.class_with_event_attributes',
                [
                    'parameters' => [
                        'useCaseRequest' => 'whatever',
                    ],
                    'response' => null,
                    'exception' => $e,
                ]
            );
        }
    }

    public function testMultiEventsSendMultiple(): void
    {
        $response = $this->proxy->multiEvents('whatever');
        $this->assertSame(1, $response);
        $this->assertEventsCount(7);
        $data = [
            'parameters' => [
                'useCaseRequest' => 'whatever',
            ],
            'response' => $response,
            'exception' => null,
        ];
        $dataWithEmptyResponse = [
            'parameters' => [
                'useCaseRequest' => 'whatever',
            ],
            'response' => null,
            'exception' => null,
        ];
        $this->assertEvent(
            'first_event',
            $dataWithEmptyResponse
        );
        $this->assertEvent(
            'first_event',
            $data,
            1
        );
        $this->assertEvent(
            'second_event',
            $data
        );
        $this->assertEvent(
            'third_event',
            $dataWithEmptyResponse
        );
        $this->assertEvent(
            'third_event',
            $data,
            1
        );
        $this->assertEvent(
            'use_case.pre.class_with_event_attributes',
            $dataWithEmptyResponse
        );
        $this->assertEvent(
            'use_case.post.class_with_event_attributes',
            $data
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testEventNamesTest(
        string $method,
        string $eventName,
        $expectedResponse = null,
        ?\Exception $expectedException = null
    ): void {
        $response = $this->proxy->{$method}('whatever');
        $this->assertSame(1, $response);
        $this->assertEventsCount(1);
        $this->assertEvent(
            $eventName,
            [
                'parameters' => [
                    'useCaseRequest' => 'whatever',
                ],
                'response' => $expectedResponse,
                'exception' => $expectedException,
            ]
        );
    }

    public function dataProvider(): iterable
    {
        yield 'default' => [
            'annotatedMethod',
            'use_case.post.class_with_event_attributes',
            1,
        ];

        yield 'named event' => [
            'eventWithOnlyName',
            'event_name',
            1,
        ];

        yield 'pre event' => [
            'eventPre',
            'use_case.pre.class_with_event_attributes',
            null,
        ];

        yield 'post event' => [
            'eventPost',
            'use_case.post.class_with_event_attributes',
            1,
        ];

        yield 'duplicated named event' => [
            'duplicatedEvent',
            'first_event',
            1,
        ];

        yield 'prefixed event' => [
            'prefixedEvent',
            'toto.post.class_with_event_attributes',
            1,
        ];

        yield 'prefixed named event' => [
            'namedEventWithPrefix',
            'first_event',
            1,
        ];

        yield 'empty prefix' => [
            'eventEmptyPrefix',
            'post.class_with_event_attributes',
            1,
        ];

        yield 'included method name' => [
            'EventWithMethodName',
            'use_case.post.event_with_method_name.class_with_event_attributes',
            1,
        ];
    }

    public function testGenericEventSendPostExecution(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassImplementingUseCaseInterface());
        $response = $proxy->execute('whateverParameter');
        $this->assertSame(1, $response);

        $events = $this->handler->getCreatedEvents('use_case.post.execute');
        $event = reset($events);
        $this->assertInstanceOf(Event::class, $event);
        $this->assertNotEmpty($event);
        $this->assertSame('use_case.post.execute', $event->eventName);
        $this->assertSame('ClassImplementingUseCaseInterface', $event->senderClassShortName);
        $this->assertSame([
            'parameters' => 'whateverParameter',
        ], $event->parameters);
        $this->assertSame(1, $event->response);
    }

    private function assertEventsCount(int $count): void
    {
        $this->assertCount($count, $this->handler->getCreatedEvents());
        $this->assertCount($count, $this->handler->getSentEvents());
    }

    /**
     * @param array{parameters: array, response: mixed, exception: \Exception} $data
     */
    private function assertEvent(string $expectedEventName, array $data, int $position = 0): void
    {
        $this->assertNotEmpty($this->handler->getCreatedEvents());
        $event = $this->handler->getCreatedEvent($expectedEventName, $position);
        $this->assertInstanceOf(Event::class, $event);
        $this->assertNotEmpty($event);
        $this->assertSame($expectedEventName, $event->eventName);
        $this->assertEquals($data, [
            'parameters' => $event->parameters,
            'response' => $event->response,
            'exception' => $event->exception,
        ]);
        $this->assertContains($expectedEventName, $this->handler->getSentEvents());
    }
}
