<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyStrategyResponse implements ServiceProxyStrategyResponseInterface
{
    /**
     * @var string
     */
    public $preSource;

    /**
     * @var string
     */
    public $postSource;

    /**
     * @var string
     */
    public $exceptionSource;

    /**
     * @var array
     */
    public $methods = [];

    /**
     * @inheritdoc
     */
    public function getPreSource()
    {
        return $this->preSource;
    }

    /**
     * @inheritdoc
     */
    public function getPostSource()
    {
        return $this->postSource;
    }

    /**
     * @inheritdoc
     */
    public function getExceptionSource()
    {
        return $this->exceptionSource;
    }

    /**
     * @inheritdoc
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
