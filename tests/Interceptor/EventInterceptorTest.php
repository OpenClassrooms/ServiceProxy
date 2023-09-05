<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use Doctrine\Common\Annotations\AnnotationException;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\EventInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
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
                0,
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
