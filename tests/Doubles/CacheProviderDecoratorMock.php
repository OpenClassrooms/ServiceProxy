<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class CacheProviderDecoratorMock extends CacheProviderDecorator
{
    /**
     * @var int
     */
    public static $lifeTime;

    public function __construct()
    {
        parent::__construct(new ArrayCache());
        self::$lifeTime = null;
    }

    /**
     * {@inheritdoc}
     */
    public function saveWithNamespace($id, $data, $namespaceId = null, $lifeTime = null)
    {
        self::$lifeTime = $lifeTime;

        return parent::saveWithNamespace($id, $data, $namespaceId, $lifeTime);
    }
}
