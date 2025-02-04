<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Cache;

use Doctrine\Common\Cache\CacheProvider;

class CacheProviderDecorator extends CacheProvider
{
    public const DEFAULT_LIFE_TIME = 0;

    protected CacheProvider $cacheProvider;

    /**
     * @var int
     */
    protected $defaultLifetime;

    public function __construct(CacheProvider $cacheProvider, $defaultLifetime = self::DEFAULT_LIFE_TIME)
    {
        $this->cacheProvider = $cacheProvider;
        $this->defaultLifetime = $defaultLifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        return $this->getCacheProvider()->$name(...$arguments);
    }

    public function getCacheProvider(): CacheProvider
    {
        return $this->cacheProvider;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id          The id of the cache entry to fetch.
     * @param string $namespaceId The id of the namespace entry to fetch.
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given namespace and id.
     */
    public function fetchWithNamespace($id, $namespaceId = null)
    {
        if (null !== $namespaceId) {
            $namespace = $this->fetch($namespaceId);
            $id = $namespace . $id;
        }

        return $this->fetch($id);
    }

    /**
     * Invalidate a namespace.
     *
     * @param string $namespaceId The id of the namespace to invalidate.
     *
     * @return bool TRUE if the namespace entry was successfully regenerated, FALSE otherwise.
     */
    public function invalidate($namespaceId)
    {
        $namespace = $this->fetch($namespaceId);

        if (false === $namespace) {
            return false;
        }

        $newNamespace = rand(0, 10000);
        // @codeCoverageIgnoreStart
        while ($namespace === $newNamespace) {
            $newNamespace = rand(0, 10000);
        }

        // @codeCoverageIgnoreEnd
        return $this->save($namespaceId, $namespaceId . '_' . $newNamespace . '_', 0);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id       The cache id.
     * @param mixed  $data     The cache entry/data.
     * @param int    $lifeTime The cache lifetime.
     *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     *
     * @return bool TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function save($id, $data, $lifeTime = null)
    {
        if (null === $lifeTime) {
            $lifeTime = $this->getDefaultLifetime();
        }

        return parent::save($id, $data, $lifeTime);
    }

    /**
     * @return int
     */
    public function getDefaultLifetime()
    {
        return $this->defaultLifetime;
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id          The cache id.
     * @param mixed  $data        The cache entry/data.
     * @param string $namespaceId The namespace id.
     * @param int    $lifeTime    The cache lifetime.
     *                            If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     *
     * @return bool TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function saveWithNamespace($id, $data, $namespaceId = null, $lifeTime = null)
    {
        if (null !== $namespaceId) {
            $namespace = $this->fetch($namespaceId);
            if (!$namespace) {
                $namespace = $namespaceId . '_' . rand(0, 10000);
                $this->save($namespaceId, $namespace, 0);
            }
            $id = $namespace . $id;
        }

        return $this->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return $this->getCacheProvider()->doContains($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->getCacheProvider()->doDelete($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return $this->getCacheProvider()->doFetch($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->getCacheProvider()->doFlush();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        return $this->getCacheProvider()->doGetStats();
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return $this->getCacheProvider()->doSave($id, $data, $lifeTime);
    }
}
