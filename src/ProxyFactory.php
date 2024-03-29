<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use OpenClassrooms\ServiceProxy\Generator\Factory\AccessInterceptorValueHolderFactory;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Method;
use ProxyManager\Configuration as ProxyManagerConfiguration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use Symfony\Component\Filesystem\Filesystem;

final class ProxyFactory
{
    private Reader $annotationReader;

    private ProxyFactoryConfiguration $configuration;

    /**
     * @var array<string, PrefixInterceptor[]|SuffixInterceptor[]>
     */
    private array $interceptors;

    /**
     * @param PrefixInterceptor[] $prefixInterceptors
     * @param SuffixInterceptor[] $suffixInterceptors
     *
     * @throws AnnotationException
     */
    public function __construct(
        ProxyFactoryConfiguration $configuration,
        iterable                  $prefixInterceptors,
        iterable                  $suffixInterceptors,
        Reader|null               $annotationReader = null,
    ) {
        $this->configuration = $configuration;
        $this->interceptors = [
            PrefixInterceptor::PREFIX_TYPE => $this->orderByPriority(
                $prefixInterceptors,
                PrefixInterceptor::PREFIX_TYPE
            ),
            SuffixInterceptor::SUFFIX_TYPE => $this->orderByPriority(
                $suffixInterceptors,
                SuffixInterceptor::SUFFIX_TYPE
            ),
        ];

        $this->annotationReader = $annotationReader ?? new AnnotationReader();
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
            foreach ([PrefixInterceptor::PREFIX_TYPE, SuffixInterceptor::SUFFIX_TYPE] as $type) {
                $interceptors = $this->filterInterceptors($instance, $type);
                if (\count($interceptors) > 0) {
                    $interceptionClosures[$type][$methodRef->getName()] = $this->getInterceptionClosure(
                        $type,
                        $interceptors,
                        $instance
                    );
                }
            }
        }

        if (\count($interceptionClosures) === 0) {
            return $object;
        }

        return $this->getInterceptorFactory()
            ->createProxy(
                $object,
                $interceptionClosures[PrefixInterceptor::PREFIX_TYPE] ?? [],
                $interceptionClosures[SuffixInterceptor::SUFFIX_TYPE] ?? [],
            )
        ;
    }

    /**
     * @return array<string, PrefixInterceptor[]|SuffixInterceptor[]>
     */
    public function getInterceptors(): array
    {
        return $this->interceptors;
    }

    /**
     * @param PrefixInterceptor::PREFIX_TYPE|SuffixInterceptor::SUFFIX_TYPE $type
     * @param PrefixInterceptor[]|SuffixInterceptor[]                       $interceptors
     */
    private function intercept(
        string   $type,
        array    $interceptors,
        Instance $instance,
        mixed    $response,
        bool     &$returnEarly
    ): mixed {
        foreach ($interceptors as $interceptor) {
            if ($type === PrefixInterceptor::PREFIX_TYPE && $interceptor instanceof PrefixInterceptor) {
                $interceptorResponse = $interceptor->prefix($instance);
            } elseif ($type === SuffixInterceptor::SUFFIX_TYPE && $interceptor instanceof SuffixInterceptor) {
                $interceptorResponse = $interceptor->suffix($instance);
            } else {
                continue;
            }
            if ($interceptorResponse->isEarlyReturn()) {
                $returnEarly = true;

                return $interceptorResponse->getValue();
            }
        }

        return $response;
    }

    /**
     * @param PrefixInterceptor[]|SuffixInterceptor[] $interceptors
     * @param PrefixInterceptor::PREFIX_TYPE|SuffixInterceptor::SUFFIX_TYPE $type
     *
     * @return PrefixInterceptor[]|SuffixInterceptor[]
     */
    private function orderByPriority(iterable $interceptors, string $type): array
    {
        if (!\is_array($interceptors)) {
            $interceptors = iterator_to_array($interceptors);
        }
        usort(
            $interceptors,
            /**
             * @param PrefixInterceptor|SuffixInterceptor $a
             * @param PrefixInterceptor|SuffixInterceptor $b
             */
            static function (object $a, object $b) use ($type) {
                if ($type === PrefixInterceptor::PREFIX_TYPE
                    && $a instanceof PrefixInterceptor
                    && $b instanceof PrefixInterceptor
                ) {
                    return $b->getPrefixPriority() <=> $a->getPrefixPriority();
                }

                if ($type === SuffixInterceptor::SUFFIX_TYPE
                    && $a instanceof SuffixInterceptor
                    && $b instanceof SuffixInterceptor
                ) {
                    return $b->getSuffixPriority() <=> $a->getSuffixPriority();
                }

                return 0;
            }
        );

        return $interceptors;
    }

    /**
     * @param PrefixInterceptor::PREFIX_TYPE|SuffixInterceptor::SUFFIX_TYPE $type
     *
     * @return PrefixInterceptor[]|SuffixInterceptor[]
     */
    private function filterInterceptors(Instance $instance, string $type): array
    {
        $interceptors = [];
        foreach ($this->interceptors[$type] as $interceptor) {
            if ($type === PrefixInterceptor::PREFIX_TYPE
                && $interceptor instanceof PrefixInterceptor
                && $interceptor->supportsPrefix($instance)
            ) {
                $interceptors[] = $interceptor;
            }
            if (
                $type === SuffixInterceptor::SUFFIX_TYPE
                && $interceptor instanceof SuffixInterceptor
                && $interceptor->supportsSuffix($instance)
            ) {
                $interceptors[] = $interceptor;
            }
        }

        return $interceptors;
    }

    /**
     * @param PrefixInterceptor::PREFIX_TYPE|SuffixInterceptor::SUFFIX_TYPE $type
     * @param SuffixInterceptor[]|PrefixInterceptor[]                       $interceptors
     */
    private function getInterceptionClosure(
        string $type,
        array $interceptors,
        Instance $instance
    ): \Closure {
        if ($type === PrefixInterceptor::PREFIX_TYPE) {
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

    private function getInterceptorFactory(): AccessInterceptorValueHolderFactory
    {
        if ($this->configuration->isEval()) {
            return new AccessInterceptorValueHolderFactory();
        }

        $proxiesDir = $this->configuration->getProxiesDir();
        if (sys_get_temp_dir() !== $proxiesDir) {
            $fs = new Filesystem();
            $fs->mkdir($proxiesDir);
        }
        $conf = new ProxyManagerConfiguration();
        $conf->setProxiesTargetDir($proxiesDir);
        $fileLocator = new FileLocator($proxiesDir);
        $conf->setGeneratorStrategy(new FileWriterGeneratorStrategy($fileLocator));
        // @phpstan-ignore-next-line
        spl_autoload_register($conf->getProxyAutoloader());

        return new AccessInterceptorValueHolderFactory($conf);
    }
}
