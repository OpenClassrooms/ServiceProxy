<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Factory\AccessInterceptorValueHolderFactory;
use OpenClassrooms\ServiceProxy\Interceptor\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Method;
use ProxyManager\Configuration as ProxyManagerConfiguration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use Symfony\Component\Filesystem\Filesystem;

class ProxyFactory
{
    private ?AnnotationReader $annotationReader;

    private Configuration $configuration;

    /**
     * @var array<string, PrefixInterceptor[]|SuffixInterceptor[]>
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
            PrefixInterceptor::PREFIX_TYPE => $this->orderByPriority(
                $prefixInterceptors,
                PrefixInterceptor::PREFIX_TYPE
            ),
            SuffixInterceptor::SUFFIX_TYPE => $this->orderByPriority(
                $suffixInterceptors,
                SuffixInterceptor::SUFFIX_TYPE
            ),
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
            foreach ([PrefixInterceptor::PREFIX_TYPE, SuffixInterceptor::SUFFIX_TYPE] as $type) {
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

        return $this->getInterceptorFactory()
            ->createProxy(
                $object,
                $interceptionClosures[PrefixInterceptor::PREFIX_TYPE] ?? [],
                $interceptionClosures[SuffixInterceptor::SUFFIX_TYPE] ?? [],
            );
    }

    /**
     * @param PrefixInterceptor::PREFIX_TYPE|SuffixInterceptor::SUFFIX_TYPE $type
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
            if ($type === PrefixInterceptor::PREFIX_TYPE) {
                $interceptorResponse = $interceptor->prefix($instance);
            } else {
                $interceptorResponse = $interceptor->suffix($instance);
            }
            if ($interceptorResponse->isEarlyReturn()) {
                $returnEarly = true;

                return $interceptorResponse->getValue();
            }
        }

        return $response;
    }

    /**
     * @return array<string, PrefixInterceptor[]|SuffixInterceptor[]>
     */
    public function getInterceptors(): array
    {
        return $this->interceptors;
    }

    /**
     * @param PrefixInterceptor[]|SuffixInterceptor[]                       $interceptors
     * @param PrefixInterceptor::PREFIX_TYPE|SuffixInterceptor::SUFFIX_TYPE $type
     *
     * @return PrefixInterceptor[]|SuffixInterceptor[]
     */
    private function orderByPriority(array $interceptors, string $type): array
    {
        usort(
            $interceptors,
            static function (AbstractInterceptor $a, AbstractInterceptor $b) use ($type) {
                return $type === PrefixInterceptor::PREFIX_TYPE
                    ? $b->getPrefixPriority() <=> $a->getPrefixPriority()
                    : $b->getSuffixPriority() <=> $a->getSuffixPriority();
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
