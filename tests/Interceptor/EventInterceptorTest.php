<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use Doctrine\Common\Annotations\AnnotationException;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\EventInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event\EventHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\EventAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\InvalidMethodEventAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class EventInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private EventHandlerMock $handler;

    private EventAnnotatedClass $proxy;

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
        $this->proxy = $this->proxyFactory->createProxy(new EventAnnotatedClass());
    }

    public function testInvalidMethodEventThrowException(): void
    {
        $this->expectException(AnnotationException::class);
        $this->proxyFactory->createProxy(new InvalidMethodEventAnnotatedClass());
    }

    public function testOnExceptionEventSendException(): void
    {
        try {
            $this->proxy->eventOnException('whatever');
        } catch (\Exception $e) {
            $this->assertEventsCount(1);
            $this->assertEvent(
                'use_case.exception.event_annotated_class',
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
            'use_case.pre.event_annotated_class',
            $dataWithEmptyResponse
        );
        $this->assertEvent(
            'use_case.post.event_annotated_class',
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
            'use_case.post.event_annotated_class',
            1,
        ];

        yield 'named event' => [
            'eventWithOnlyName',
            'event_name',
            1,
        ];

        yield 'pre event' => [
            'eventPre',
            'use_case.pre.event_annotated_class',
            null,
        ];

        yield 'post event' => [
            'eventPost',
            'use_case.post.event_annotated_class',
            1,
        ];

        yield 'duplicated named event' => [
            'duplicatedEvent',
            'first_event',
            1,
        ];

        yield 'prefixed event' => [
            'prefixedEvent',
            'toto.post.event_annotated_class',
            1,
        ];

        yield 'prefixed named event' => [
            'namedEventWithPrefix',
            'first_event',
            1,
        ];

        yield 'empty prefix' => [
            'eventEmptyPrefix',
            'use_case.post.event_annotated_class',
            1,
        ];

        yield 'included method name' => [
            'EventWithMethodName',
            'use_case.post.event_with_method_name.event_annotated_class',
            1,
        ];
    }

    private function assertEventsCount(int $count): void
    {
        $this->assertCount($count, $this->handler->getEvents());
    }

    /**
     * @param array{parameters: array, response: mixed, exception: \Exception} $data
     */
    private function assertEvent(string $expectedEventName, array $data, int $position = 0): void
    {
        $this->assertNotEmpty($this->handler->getEvents());
        $event = $this->handler->getEvent($expectedEventName, $position);
        $this->assertNotEmpty($event);
        $this->assertSame($expectedEventName, $event->getName());
        $this->assertEquals($data, $event->getData());
    }
}
