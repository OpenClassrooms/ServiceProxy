<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyTransactionInterface;
use OpenClassrooms\ServiceProxy\Transaction\TransactionAdapterInterface;
use PHPUnit\Framework\TestCase as Assert;
use Symfony\Component\Filesystem\Filesystem;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

trait ServiceProxyTest
{
    protected static string $cacheDir = __DIR__ . '/cache';

    protected function assertServiceCacheProxy($inputClass, ServiceProxyCacheInterface $proxy): void
    {
        $this->assertProxy($inputClass, $proxy);
        Assert::assertInstanceOf(ServiceProxyCacheInterface::class, $proxy);
        Assert::assertAttributeInstanceOf(
            CacheProviderDecorator::class,
            'proxy_cacheProvider',
            $proxy
        );
    }

    protected function assertServiceTransactionProxy($inputClass, ServiceProxyTransactionInterface $proxy): void
    {
        $this->assertProxy($inputClass, $proxy);
        Assert::assertInstanceOf(ServiceProxyTransactionInterface::class, $proxy);
        Assert::assertAttributeInstanceOf(
            TransactionAdapterInterface::class,
            'proxy_transactionAdapter',
            $proxy
        );
    }

    protected function assertNotServiceCacheProxy($proxy): void
    {
        Assert::assertNotInstanceOf(ServiceProxyCacheInterface::class, $proxy);
    }

    protected function assertNotServiceTransactionProxy($proxy): void
    {
        Assert::assertNotInstanceOf(ServiceProxyTransactionInterface::class, $proxy);
    }

    protected function assertProxy($inputClass, ServiceProxyInterface $proxy): void
    {
        Assert::assertInstanceOf(get_class($inputClass), $proxy);
        Assert::assertInstanceOf(ServiceProxyInterface::class, $proxy);
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove(self::$cacheDir);
    }
}
