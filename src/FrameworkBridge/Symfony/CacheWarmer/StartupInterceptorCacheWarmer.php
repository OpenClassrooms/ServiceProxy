<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\CacheWarmer;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Method;
use ProxyManager\Proxy\ValueHolderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class StartupInterceptorCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var array<StartUpInterceptor>
     */
    private array $startUpInterceptors;

    private Reader $annotationReader;

    /**
     * @param iterable<Object> $proxies
     * @param iterable<StartUpInterceptor> $startUpInterceptors
     * @param Reader|null $annotationReader
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        private readonly iterable $proxies,
        iterable $startUpInterceptors,
        Reader|null $annotationReader = null,
    ) {
        if (!\is_array($startUpInterceptors)) {
            $this->startUpInterceptors = iterator_to_array($startUpInterceptors);
        }

        $this->annotationReader = $annotationReader ?? new AnnotationReader();
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir): array
    {
        if (\count($this->startUpInterceptors) === 0) {
            return [];
        }

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

        return [];
    }

    /**
     * @return iterable<Instance>
     */
    private function getInstances(): iterable
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
