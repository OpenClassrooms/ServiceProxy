<?php declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Factory;

use OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\AccessInterceptorGenerator;
use ProxyManager\Configuration;
use ProxyManager\Factory\AbstractBaseFactory;

class AccessInterceptorFactory extends AbstractBaseFactory
{
    private AccessInterceptorGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new AccessInterceptorGenerator();
    }

    /**
     * @template T of object
     * 
     * @param class-string<T> $class
     * @param array<mixed> $args
     * @param array<string, \Closure> $prefixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                    before method logic is executed
     * @param array<string, \Closure> $suffixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                    after method logic is executed
     *
     * @return T
     */
    public function createInstance(
        string $class,
        array $args,
        array $prefixInterceptors = [],
        array $suffixInterceptors = []
    ): object {
        $methods = array_merge(
            array_keys($prefixInterceptors),
            array_keys($suffixInterceptors)
        );
        $methods = array_unique($methods);

        $proxyClassName = $this->generateProxy($class, ['methods' => $methods]);

        $instance = new $proxyClassName(...$args);
        
        $instance->setPrefixInterceptors($prefixInterceptors);
        $instance->setSuffixInterceptors($suffixInterceptors);
        
        return $instance;
    }

    public function getGenerator(): AccessInterceptorGenerator
    {
        return $this->generator;
    }
}
