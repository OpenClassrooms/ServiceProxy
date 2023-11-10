<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\ProxyFactoryConfiguration;
use PHPUnit\Framework\TestCase as Assert;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use Symfony\Component\Filesystem\Filesystem;

trait ProxyTestTrait
{
    protected static string $cacheDir = __DIR__ . '/cache';

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove(self::$cacheDir);
    }

    protected function assertProxy(object $input, object $proxy): void
    {
        Assert::assertInstanceOf(\get_class($input), $proxy);
        Assert::assertInstanceOf(ValueHolderInterface::class, $proxy);
        Assert::assertInstanceOf(AccessInterceptorInterface::class, $proxy);
    }

    protected function assertNotProxy(object $input, object $proxy): void
    {
        Assert::assertInstanceOf(\get_class($input), $proxy);
        Assert::assertNotInstanceOf(ValueHolderInterface::class, $proxy);
        Assert::assertNotInstanceOf(AccessInterceptorInterface::class, $proxy);
    }

    /**
     * @param PrefixInterceptor[]|SuffixInterceptor[] $interceptors
     */
    private function getProxyFactory(array $interceptors): ProxyFactory
    {
        return new ProxyFactory(
            new ProxyFactoryConfiguration(self::$cacheDir),
            $interceptors,
            $interceptors,
        );
    }
}
