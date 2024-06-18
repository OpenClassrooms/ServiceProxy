<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\CacheWarmer;

use ProxyManager\Proxy\LazyLoadingInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\VarExporter\LazyObjectInterface;

final class ProxyCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var iterable<object>
     */
    private iterable $proxies;

    /**
     * @param iterable<object> $proxies
     */
    public function __construct(iterable $proxies)
    {
        $this->proxies = $proxies;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp(string $cacheDir): array
    {
        foreach ($this->proxies as $proxy) {
            if ($proxy instanceof LazyLoadingInterface && !$proxy->isProxyInitialized()) {
                $proxy->initializeProxy();
            }

            if (class_exists(LazyObjectInterface::class)
                && $proxy instanceof LazyObjectInterface
                && !$proxy->isLazyObjectInitialized()) {
                $proxy->initializeLazyObject();
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
