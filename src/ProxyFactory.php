<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Factory\AccessInterceptorValueHolderFactory;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Method;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use ProxyManager\Configuration as ProxyManagerConfiguration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use Symfony\Component\Filesystem\Filesystem;

class ProxyFactory
{
    /**
     * @var null|\Doctrine\Common\Annotations\AnnotationReader
     */
    private ?AnnotationReader $annotationReader;

    private Configuration $configuration;

    /**
     * @var PrefixInterceptor[]|SuffixInterceptor[]
     */
    private array $interceptors;

    public function __construct(
        Configuration $configuration,
        array $prefixInterceptors,
        array $suffixInterceptors
    ) {
        $this->annotationReader = new AnnotationReader();
        $this->configuration = $configuration;
        $this->interceptors = [
            'prefix' => $prefixInterceptors,
            'suffix' => $suffixInterceptors,
        ];
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function createProxy(object $object): object
    {
        $instanceRef = new \ReflectionObject($object);
        $methods = $instanceRef->getMethods(\ReflectionMethod::IS_PUBLIC);
        $interceptionClosures = [];
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
            foreach (['prefix', 'suffix'] as $type) {
                $interceptors = $this->filterInterceptors($instance, $type);
                if (count($interceptors) > 0) {
                    $interceptionClosures[$type][$methodRef->getName()] = $this->getInterceptionClosure(
                        $type,
                        $interceptors,
                        $instance
                    );
                }
            }
        }

        if (count($interceptionClosures) === 0) {
            return $object;
        }

        return $this->getInterceptorFactory()->createProxy(
            $object,
            $interceptionClosures['prefix'] ?? [],
            $interceptionClosures['suffix'] ?? [],
        );
    }

    /**
     * @param 'prefix'|'suffix' $type
     *
     * @return PrefixInterceptor[]|SuffixInterceptor[]
     */
    private function filterInterceptors(Instance $instance, string $type): array
    {
        $interceptors = [];
        foreach ($this->interceptors[$type] as $interceptor) {
            if ($type === 'prefix'
                && $interceptor instanceof PrefixInterceptor
                && $interceptor->supportsPrefix($instance)
            ) {
                $interceptors[] = $interceptor;
            }
            if (
                $type === 'suffix'
                && $interceptor instanceof SuffixInterceptor
                && $interceptor->supportsSuffix($instance)
            ) {
                $interceptors[] = $interceptor;
            }
        }

        return $interceptors;
    }

    /**
     * @param 'prefix'|'suffix' $type
     * @param SuffixInterceptor[]|PrefixInterceptor[] $interceptors
     * @param object[]                                $annotations
     *
     * @return \Closure
     */
    private function getInterceptionClosure(
        string $type,
        array $interceptors,
        Instance $instance
    ): \Closure {

        if ($type === 'prefix') {
            return function ($proxy, $object, $methodName, $params, &$returnEarly) use (
                $instance,
                $type,
                $interceptors
            ) {
                $instance->setParameters($params);

                return $this->intercept(
                    $type,
                    $interceptors,
                    $instance,
                    null,
                    $returnEarly
                );
            };
        }

        return function ($proxy, $object, $methodName, $params, $response, &$returnEarly) use (
            $instance,
            $type,
            $interceptors
        ) {

            $instance
                ->setParameters($params)
                ->setResponse($response)
            ;

            return $this->intercept(
                $type,
                $interceptors,
                $instance,
                $response,
                $returnEarly
            );
        };
    }

    /**
     * @param 'prefix'|'suffix' $type
     *
     * @return mixed
     */
    public function intercept(
        string $type,
        array $interceptors,
        Instance $instance,
        $response,
        &$returnEarly
    ) {
        foreach ($interceptors as $interceptor) {
            /** @var Response $interceptorResponse */
            $interceptorResponse = $interceptor->{$type}($instance);
            if ($interceptorResponse->isEarlyReturn()) {
                $returnEarly = true;

                return $interceptorResponse->getValue();
            }
        }

        return $response;
    }

    private function getInterceptorFactory(): AccessInterceptorValueHolderFactory
    {
        $proxiesDir = $this->configuration->getProxiesDir();
        if (sys_get_temp_dir() !== $proxiesDir) {
            $fs = new Filesystem();
            $fs->mkdir($proxiesDir);
        }
        $conf = new ProxyManagerConfiguration();
        $conf->setProxiesTargetDir($proxiesDir);
        $fileLocator = new FileLocator($proxiesDir);
        $conf->setGeneratorStrategy(new FileWriterGeneratorStrategy($fileLocator));
        spl_autoload_register($conf->getProxyAutoloader());

        return new AccessInterceptorValueHolderFactory($conf);
    }
}
