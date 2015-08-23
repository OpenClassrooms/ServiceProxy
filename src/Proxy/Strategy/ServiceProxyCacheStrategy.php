<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy;

use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseBuilderInterface;
use Zend\Code\Generator\MethodGenerator;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyCacheStrategy implements ServiceProxyStrategyInterface
{
    /**
     * @var ServiceProxyStrategyResponseBuilderInterface
     */
    private $serviceProxyStrategyResponseBuilder;

    /**
     * @inheritDoc
     */
    public function execute(ServiceProxyStrategyRequestInterface $request)
    {
        return $this->serviceProxyStrategyResponseBuilder
            ->create()
            ->withPreSource($this->generatePreSource($request->getAnnotation()))
            ->withPostSource($this->generatePostSource($request->getAnnotation()))
            ->withMethods($this->generateMethods())
            ->build();
    }

    /**
     * @return string
     */
    private function generatePreSource(Cache $annotation)
    {
        return '';
    }

    /**
     * @return string
     */
    private function generatePostSource(Cache $annotation)
    {
        return '';
    }

    /**
     * @return MethodGenerator[]
     */
    public function generateMethods()
    {
        return array();
    }

    public function setServiceProxyStrategyResponseBuilder(
        ServiceProxyStrategyResponseBuilderInterface $serviceProxyStrategyResponseBuilder
    )
    {
        $this->serviceProxyStrategyResponseBuilder = $serviceProxyStrategyResponseBuilder;
    }
}
