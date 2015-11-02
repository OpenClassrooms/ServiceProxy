<?php

namespace OpenClassrooms\ServiceProxy\Annotations;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 * @Annotation
 */
class Cache
{
    const MEMCACHE_KEY_MAX_LENGTH = 240;

    const QUOTES_LENGTH = 4;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $namespace;

    /**
     * @var int
     */
    public $lifetime;

    /**
     * @return string
     *
     * @throws InvalidCacheIdException
     */
    public function getId()
    {
        if (null !== $this->id && self::MEMCACHE_KEY_MAX_LENGTH + self::QUOTES_LENGTH < mb_strlen($this->id)) {
            throw new InvalidCacheIdException('id is too long, MUST be inferior to 240');
        }

        return $this->id;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }
}
