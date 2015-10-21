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
    public $namespace;

    /**
     * @var int
     */
    public $lifetime;

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
