<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy;

use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseBuilderInterface;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

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
            ->withProperties($this->generateProperties())
            ->withMethods($this->generateMethods())
            ->build();
    }

    /**
     * @return string
     */
    private function generatePreSource(Cache $annotation)
    {
        //        $class = new \ReflectionClass('');
//        $method = new \ReflectionMethod('', '');
//        $cacheId = md5($class->getName().'::'.$method->getName());

//        md5(get_class($useCase).serialize($useCaseRequest));
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
     * @return PropertyGenerator[]
     */
    public function generateProperties()
    {
        return [new PropertyGenerator(self::PROPERTY_PREFIX.'cacheProvider', null, PropertyGenerator::FLAG_PRIVATE)];
    }

    /**
     * @return MethodGenerator[]
     */
    public function generateMethods()
    {
        return [
            new MethodGenerator(
                self::METHOD_PREFIX.'setCacheProvider',
                [
                    [
                        'name' => 'cacheProvider',
                        'type' => '\\OpenClassrooms\\DoctrineCacheExtension\\CacheProviderDecorator',
                    ],
                ],
                MethodGenerator::FLAG_PUBLIC,
                '$this->'.self::PROPERTY_PREFIX.'cacheProvider = $cacheProvider;'
            ),
        ];
    }

    public function setServiceProxyStrategyResponseBuilder(
        ServiceProxyStrategyResponseBuilderInterface $serviceProxyStrategyResponseBuilder
    ) {
        $this->serviceProxyStrategyResponseBuilder = $serviceProxyStrategyResponseBuilder;
    }
}
