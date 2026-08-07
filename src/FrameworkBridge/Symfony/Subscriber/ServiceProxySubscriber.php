<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Subscriber;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class ServiceProxySubscriber implements EventSubscriberInterface
{
    /**
     * @var array<StartUpInterceptor>
     */
    private array $startUpInterceptors;

    private Reader $annotationReader;

    /**
     * @param iterable<StartUpInterceptor> $startUpInterceptors
     * @param iterable<object>             $proxies
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        private readonly iterable $proxyMethodInstances,
        iterable          $startUpInterceptors,
        Reader|null       $annotationReader = null,
    ) {
        if (!\is_array($startUpInterceptors)) {
            $this->startUpInterceptors = iterator_to_array($startUpInterceptors);
        }

        $this->annotationReader = $annotationReader ?? new AnnotationReader();
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'startUp',
        ];
    }

    public function startUp(RequestEvent $event): void
    {
        if (\count($this->startUpInterceptors) === 0) {
            return;
        }

        usort(
            $this->startUpInterceptors,
            static fn (
                StartUpInterceptor $a,
                StartUpInterceptor $b
            ) => $a->getStartUpPriority() <=> $b->getStartUpPriority()
        );
        foreach ($this->proxyMethodInstances as $proxyMethodInstance) {
            foreach ($this->startUpInterceptors as $interceptor) {
                if ($interceptor->supportsStartUp($proxyMethodInstance)) {
                    $interceptor->startUp($proxyMethodInstance);
                }
            }
        }
    }
}
