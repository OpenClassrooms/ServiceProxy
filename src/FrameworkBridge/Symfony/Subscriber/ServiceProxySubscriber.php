<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Subscriber;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Method;
use ProxyManager\Proxy\ValueHolderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class ServiceProxySubscriber implements EventSubscriberInterface
{
    private AnnotationReader $annotationReader;

    /**
     * @var array<StartUpInterceptor>
     */
    private array $startUpInterceptors;

    /**
     * @param iterable<StartUpInterceptor> $startUpInterceptors
     * @param iterable<object>             $proxies
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        private readonly iterable $proxyMethodInstances,
        iterable          $startUpInterceptors,
    ) {
        if (!\is_array($startUpInterceptors)) {
            $this->startUpInterceptors = iterator_to_array($startUpInterceptors);
        }
        $this->annotationReader = new AnnotationReader();
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
