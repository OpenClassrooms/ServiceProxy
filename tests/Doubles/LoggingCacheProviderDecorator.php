<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

class LoggingCacheProviderDecorator extends CacheProviderDecorator
{
    /**
     * @var mixed
     */
    public static $fetchReturn = false;

    public function __construct()
    {
    }

    public function fetchWithNamespace($id, $namespaceId = null)
    {
        CallsLog::log();

        return self::$fetchReturn;
    }

    public function saveWithNamespace($id, $data, $namespaceId = null, $lifeTime = null)
    {
        CallsLog::log();
    }
}
