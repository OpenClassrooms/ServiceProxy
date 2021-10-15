<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidTransactionAdapterException;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface;
use OpenClassrooms\ServiceProxy\Transaction\TransactionAdapterInterface;

class ServiceProxyBuilder implements ServiceProxyBuilderInterface
{
    private ?CacheProviderDecorator $cacheProvider = null;

    private ?TransactionAdapterInterface $transactionAdapter = null;

    private object $class;

    private ProxyFactoryInterface $proxyFactory;

    public function create(object $class): ServiceProxyBuilderInterface
    {
        $this->class = $class;
        $this->cacheProvider = null;

        return $this;
    }

    public function withCache(CacheProviderDecorator $cacheProvider): ServiceProxyBuilderInterface
    {
        $this->cacheProvider = $cacheProvider;

        return $this;
    }

    public function withTransaction(TransactionAdapterInterface $transactionAdapter): ServiceProxyBuilderInterface
    {
        $this->transactionAdapter = $transactionAdapter;

        return $this;
    }

    /**
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\InvalidTransactionAdapterException
     */
    public function build(): ServiceProxyInterface
    {
        $proxy = $this->proxyFactory->createProxy($this->class);

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

    public function setProxyFactory(ProxyFactoryInterface $proxyFactory): void
    {
        $this->proxyFactory = $proxyFactory;
    }
}
