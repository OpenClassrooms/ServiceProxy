<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Proxy\AccessInterceptorsInterface;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\ProxyFactoryConfiguration;
use PHPUnit\Framework\TestCase as Assert;
use Symfony\Component\Filesystem\Filesystem;

trait ProxyTestTrait
{
    protected static string $cacheDir = __DIR__ . '/cache';

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove(self::$cacheDir);
    }

    /**
     * @param class-string $input
     */
    protected function assertProxy(string $input, object $proxy): void
    {
        Assert::assertInstanceOf($input, $proxy);
        Assert::assertInstanceOf(AccessInterceptorsInterface::class, $proxy);
    }

    /**
     * @param class-string $class
     */
    protected function assertNotProxy(string $class, object $proxy): void
    {
        Assert::assertInstanceOf($class, $proxy);
        Assert::assertNotInstanceOf(AccessInterceptorsInterface::class, $proxy);
    }

    /**
     * @param PrefixInterceptor[]|SuffixInterceptor[] $interceptors
     */
    private function getProxyFactory(array $interceptors): ProxyFactory
    {
        $filter = static fn (string $type) => static fn (object $interceptor) => is_a($interceptor, $type);

        return new ProxyFactory(
            new ProxyFactoryConfiguration(self::$cacheDir),
            array_filter($interceptors, $filter(PrefixInterceptor::class)),
            array_filter($interceptors, $filter(SuffixInterceptor::class))
        );
    }
}
