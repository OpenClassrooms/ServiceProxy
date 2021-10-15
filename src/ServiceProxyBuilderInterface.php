<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Transaction\TransactionAdapterInterface;

interface ServiceProxyBuilderInterface
{
    public function create(object $class): ServiceProxyBuilderInterface;

    public function withCache(CacheProviderDecorator $cacheProvider): ServiceProxyBuilderInterface;

    public function withTransaction(TransactionAdapterInterface $transactionAdapter): ServiceProxyBuilderInterface;

    public function build(): ServiceProxyInterface;
}
