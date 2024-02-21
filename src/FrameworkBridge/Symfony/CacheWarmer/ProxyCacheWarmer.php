<?php

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\CacheWarmer;


use ProxyManager\Proxy\LazyLoadingInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\VarExporter\LazyObjectInterface;

class ProxyCacheWarmer implements CacheWarmerInterface
{
    private iterable $proxies;

    public function __construct(iterable $proxies)
    {
        $this->proxies = $proxies;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp(string $cacheDir)
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
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
