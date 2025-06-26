<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Interceptor\Config\EventInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\Event\ServiceProxyEventFactory;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\EventInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event\EventHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\CustomMessage;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\EventAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\InvalidMethodEventAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\InvalidResponseMessageClassAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\Metadata;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\ObjectResponseMessageClassAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\ResponseObject;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class EventInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private EventHandlerMock $handler;

    private EventAnnotatedClass $proxy;

    private ProxyFactory $proxyFactory;

    protected function setUp(): void
    {
        $this->handler = new EventHandlerMock();
        $this->proxyFactory = $this->getProxyFactory(
            [
                new EventInterceptor(
                    new ServiceProxyEventFactory(),
                    new EventInterceptorConfig(),
                    [$this->handler],
                ),
            ]
        );
        $this->proxy = $this->proxyFactory->createProxy(new EventAnnotatedClass());
    }

    public function testInvalidMethodEventThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $proxy = $this->proxyFactory->createProxy(new InvalidMethodEventAnnotatedClass());
        $proxy->eventWithWrongMethods();
    }

    public function testOnExceptionEventSendException(): void
    {
        try {
            $this->proxy->eventOnException('whatever');
        } catch (\Exception $e) {
            $this->assertEventsCount(1);
            $this->assertEvent(
                0,
                [
                    'name' => 'exception.event_annotated_class.event_on_exception',
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
        $this->assertEventsCount(8);
        $data = [
            'parameters' => [
                'useCaseRequest' => 'whatever',
            ],
            'response' => $response,
            'exception' => null,
        ];
        $dataWithoutResponse = [
            'parameters' => [
                'useCaseRequest' => 'whatever',
            ],
        ];
        $this->assertEvent(
            0,
            $dataWithoutResponse
        );
        $this->assertEvent(
            1,
            $dataWithoutResponse,
        );
        $this->assertEvent(
            2,
            $dataWithoutResponse
        );
        $this->assertEvent(
            3,
            $data
        );
        $this->assertEvent(
            4,
            $data,
            1
        );
        $this->assertEvent(
            5,
            $data
        );
        $this->assertEvent(
            6,
            $data
        );
    }

    public function testMessageClassEventDispatchedWithObjectResponse(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ObjectResponseMessageClassAnnotatedClass());
        $result = $proxy->handle('world');

        $this->assertInstanceOf(ResponseObject::class, $result);
        $this->assertSame('world', $result->content);
        $this->assertIsInt($result->id);
        $this->assertInstanceOf(Metadata::class, $result->meta);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->createdAt);

        $this->assertEventsCount(1);
        $event = $this->handler->getEvents()[0];
        $this->assertInstanceOf(CustomMessage::class, $event);
        $this->assertSame('world', $event->content);
        $this->assertSame($result->id, $event->id);
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->createdAt);
        $this->assertSame(
            $result->createdAt->format(\DateTimeInterface::ATOM),
            $event->createdAt->format(\DateTimeInterface::ATOM)
        );
        $this->assertInstanceOf(Metadata::class, $event->meta);
        $this->assertTrue($event->meta->active);
    }

    public function testInvalidResponseForMessageClassThrowsException(): void
    {
        $proxy = $this->proxyFactory->createProxy(new InvalidResponseMessageClassAnnotatedClass());

        $this->expectException(\InvalidArgumentException::class);
        $proxy->invalid('test');
    }

    private function assertEventsCount(int $count): void
    {
        $this->assertCount($count, $this->handler->getEvents());
    }

    /**
     * @param array{parameters: array, response: mixed, exception: \Exception} $data
     */
    private function assertEvent(int $index, array $data): void
    {
        $this->assertNotEmpty($this->handler->getEvents());
        $event = $this->handler->getEvents()[$index];
        foreach ($data as $key => $value) {
            $this->assertObjectHasProperty($key, $event);
            $this->assertSame($value, $event->{$key});
        }
    }
}
