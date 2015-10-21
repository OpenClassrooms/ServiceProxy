<?php

namespace OpenClassrooms\ServiceProxy\Annotations;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 * @Annotation
 */
class Cache
{
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
     * @throws InvalidCacheIdException
     */
    public function getId()
    {
        if (null !== $this->id && 244 < mb_strlen($this->id)) {
            var_dump(strlen($this->id));
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
