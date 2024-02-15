<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Subscriber;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Method;
use ProxyManager\Proxy\ValueHolderInterface;
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
        private readonly iterable $proxies,
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
        usort(
            $this->startUpInterceptors,
            static fn (
                StartUpInterceptor $a,
                StartUpInterceptor $b
            ) => $a->getStartUpPriority() <=> $b->getStartUpPriority()
        );
        foreach ($this->getInstances() as $instance) {
            foreach ($this->startUpInterceptors as $interceptor) {
                if ($interceptor->supportsStartUp($instance)) {
                    $interceptor->startUp($instance);
                }
            }
        }
    }

    /**
     * @return iterable<Instance>
     */
    public function getInstances(): iterable
    {
        foreach ($this->proxies as $proxy) {
            $object = $proxy;
            if ($proxy instanceof ValueHolderInterface) {
                $object = $proxy->getWrappedValueHolderValue();
                if ($object === null) {
                    continue;
                }
            }
            $instanceRef = new \ReflectionObject($object);
            $methods = $instanceRef->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $methodRef) {
                $methodAnnotations = $this->annotationReader->getMethodAnnotations($methodRef);
                $instance = Instance::create(
                    $proxy,
                    $instanceRef,
                    Method::create(
                        $methodRef,
                        $methodAnnotations,
                    )
                );
                yield $instance;
            }
        }
    }
}
