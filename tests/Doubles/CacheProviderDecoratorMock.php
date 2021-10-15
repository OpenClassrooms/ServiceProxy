<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

class CacheProviderDecoratorMock extends CacheProviderDecorator
{
    public static ?int $lifeTime;

    public function __construct()
    {
        parent::__construct(new ArrayCache());
        self::$lifeTime = null;
    }

    /**
     * {@inheritdoc}
     */
    public function saveWithNamespace($id, $data, $namespaceId = null, $lifeTime = null): bool
    {
        self::$lifeTime = $lifeTime;

        return parent::saveWithNamespace($id, $data, $namespaceId, $lifeTime);
    }
}
