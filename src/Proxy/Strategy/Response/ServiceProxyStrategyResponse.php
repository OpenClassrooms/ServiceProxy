<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyStrategyResponse implements ServiceProxyStrategyResponseInterface
{
    /**
     * @var string
     */
    public $exceptionSource;

    /**
     * @var MethodGenerator[]
     */
    public $methods = [];

    /**
     * @var string
     */
    public $postSource;

    /**
     * @var string
     */
    public $preSource;

    /**
     * @var PropertyGenerator[]
     */
    public $properties = [];

    /**
     * {@inheritdoc}
     */
    public function getPreSource()
    {
        return $this->preSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostSource()
    {
        return $this->postSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptionSource()
    {
        return $this->exceptionSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
