<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Subscriber;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Method;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class ServiceProxySubscriber implements EventSubscriberInterface
{
    private AnnotationReader $annotationReader;

    /**
     * @param StartUpInterceptor[] $startUpInterceptors
     * @param string[]             $proxiesIds
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
        private readonly array              $proxiesIds,
        private array                       $startUpInterceptors = [],
    ) {
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * @return iterable<Instance>
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getInstances(): iterable
    {
        foreach ($this->proxiesIds as $proxiesId) {
            /** @var object $object */
            $object = $this->serviceLocator->get($proxiesId);
            $instanceRef = new \ReflectionObject($object);
            $methods = $instanceRef->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $methodRef) {
                $methodAnnotations = $this->annotationReader->getMethodAnnotations($methodRef);
                $instance = Instance::create(
                    $object,
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

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function startUp(RequestEvent $event): void
    {
        usort(
            $this->startUpInterceptors,
            static fn (StartUpInterceptor $a, StartUpInterceptor $b) => $a->getStartUpPriority(
            ) <=> $b->getStartUpPriority()
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
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'startUp',
        ];
    }

    public function addStartUpInterceptor(StartUpInterceptor $interceptor): void
    {
        $this->startUpInterceptors[] = $interceptor;
    }
}
