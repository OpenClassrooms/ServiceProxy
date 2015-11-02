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
     * {@inheritdoc}
     */
    public function execute(ServiceProxyStrategyRequestInterface $request)
    {
        return $this->serviceProxyStrategyResponseBuilder
            ->create()
            ->withPreSource($this->generatePreSource($request))
            ->withPostSource($this->generatePostSource($request->getAnnotation()))
            ->withExceptionSource('')
            ->withProperties($this->generateProperties())
            ->withMethods($this->generateMethods())
            ->build();
    }

    /**
     * @return string
     */
    private function generatePreSource(ServiceProxyStrategyRequestInterface $request)
    {
        $source = '';
        $source .= $this->generateNamespace($request);
        $source .= $this->generateProxyId($request);
        $source .= $this->generateFetch($request);

        return $source;
    }

    /**
     * @return string
     */
    private function generateNamespace(ServiceProxyStrategyRequestInterface $request)
    {
        $source = '';
        $annotation = $request->getAnnotation();
        if (null !== $annotation->getNamespace()) {
            $parametersLanguage = $this->getParametersLanguage($request);
            $source = "\$expressionLanguage = new \\Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage();\n"
                .'$namespace = md5($expressionLanguage->evaluate("'
                .$annotation->getNamespace().'",'.$parametersLanguage."));\n";
        }

        return $source;
    }

    /**
     * @return string
     */
    private function getParametersLanguage(ServiceProxyStrategyRequestInterface $request)
    {
        $parameters = $request->getMethod()->getParameters();
        $parametersLanguage = '[';
        foreach ($parameters as $parameter) {
            $parametersLanguage .= "'".$parameter->getName()."' => \$".$parameter->getName().',';
        }
        $parametersLanguage .= ']';

        return $parametersLanguage;
    }

    /**
     * @return string
     */
    private function generateProxyId(ServiceProxyStrategyRequestInterface $request)
    {
        if (null !== $request->getAnnotation()->getId()) {
            $parametersLanguage = $this->getParametersLanguage($request);
            $source = "\$expressionLanguage = new \\Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage();\n"
                .'$proxy_id = $expressionLanguage->evaluate("'.$request->getAnnotation()->getId(
                ).'",'.$parametersLanguage.");\n";
        } else {
            $source = "\$proxy_id = md5('".$request->getClass()->getName().'::'.$request->getMethod()->getName()."'";
            $parameters = $request->getMethod()->getParameters();
            if (0 < count($parameters)) {
                foreach ($parameters as $parameter) {
                    $source .= ".'::'.serialize(\$".$parameter->getName().')';
                }
            }
            $source .= ");\n";
        }

        return $source;
    }

    /**
     * @return string
     */
    private function generateFetch(ServiceProxyStrategyRequestInterface $request)
    {
        $source = '$data = $this->'.self::PROPERTY_PREFIX.'cacheProvider->fetchWithNamespace($proxy_id';
        if (null !== $request->getAnnotation()->getNamespace()) {
            $source .= ', $namespace';
        }
        $source .= ");\n"
            ."if (false !== \$data){\n"
            ."return \$data;\n"
            .'}';

        return $source;
    }

    /**
     * @return string
     */
    private function generatePostSource(Cache $annotation)
    {
        $source = '$this->'.self::PROPERTY_PREFIX.'cacheProvider->saveWithNamespace($proxy_id, $data';
        if (null !== $annotation->getNamespace()) {
            $source .= ',$namespace';
        } else {
            $source .= ',null';
        }
        $lifetime = $annotation->getLifetime();
        if (null !== $lifetime) {
            $source .= ','.$lifetime;
        }
        $source .= ');';

        return $source;
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
