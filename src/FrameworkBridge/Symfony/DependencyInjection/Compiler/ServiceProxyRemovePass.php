<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Method;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ServiceProxyRemovePass implements CompilerPassInterface
{
    private AnnotationReader $annotationReader;

    /**
     * @param StartUpInterceptor[] $interceptors
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        private array $interceptors = [],
    ) {
        usort(
            $this->interceptors,
            static fn (StartUpInterceptor $a, StartUpInterceptor $b) => $a->getStartUpPriority(
            ) <=> $b->getStartUpPriority()
        );
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * @throws \Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('openclassrooms.service_proxy');
        foreach ($taggedServices as $taggedServiceName => $tagParameters) {
            $object = $container->get($taggedServiceName);
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
                foreach ($this->interceptors as $interceptor) {
                    if ($interceptor->supportsStartUp($instance)) {
                        $interceptor->startUp($instance);
                    }
                }
            }
        }
    }
}
