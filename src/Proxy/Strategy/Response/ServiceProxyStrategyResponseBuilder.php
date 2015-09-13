<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyStrategyResponseBuilder implements ServiceProxyStrategyResponseBuilderInterface
{
    /**
     * @var ServiceProxyStrategyResponse
     */
    public $response;

    /**
     * @inheritdoc
     */
    public function create()
    {
        $this->response = new ServiceProxyStrategyResponse();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withExceptionSource($exceptionSource)
    {
        $this->response->exceptionSource = $exceptionSource;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withMethods(array $methods)
    {
        $this->response->methods = $methods;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withPostSource($postSource)
    {
        $this->response->postSource = $postSource;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withPreSource($preSource)
    {
        $this->response->preSource = $preSource;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withProperties(array $properties)
    {
        $this->response->properties = $properties;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        return $this->response;
    }
}
