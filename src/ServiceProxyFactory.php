<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidTransactionAdapterException;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactory;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface;
use OpenClassrooms\ServiceProxy\Transaction\TransactionAdapterInterface;

class ServiceProxyFactory implements ServiceProxyFactoryInterface
{
    private ?CacheProviderDecorator $cacheProvider = null;

    private ?TransactionAdapterInterface $transactionAdapter = null;

    private ProxyFactory $proxyFactory;

    /**
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\InvalidTransactionAdapterException
     */
    public function createProxy(object $class): ServiceProxyInterface
    {
        $proxy = $this->proxyFactory->createProxy($class);
        if ($proxy instanceof ServiceProxyCacheInterface) {
            if (null === $this->cacheProvider) {
                throw new InvalidCacheProviderException();
            }
            $proxy->proxy_setCacheProvider($this->cacheProvider);
        }
        if ($proxy instanceof ServiceProxyTransactionInterface) {
            if (null === $this->transactionAdapter) {
                throw new InvalidTransactionAdapterException();
            }
            $proxy->proxy_setTransactionAdapter($this->transactionAdapter);
        }

        return $proxy;
    }

    public function setCacheProvider(CacheProviderDecorator $cacheProvider): void
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function setTransactionAdapter(TransactionAdapterInterface $transactionAdapter): void
    {
        $this->transactionAdapter = $transactionAdapter;
    }

    public function setProxyFactory(ProxyFactoryInterface $proxyFactory): void
    {
        $this->proxyFactory = $proxyFactory;
    }
}
