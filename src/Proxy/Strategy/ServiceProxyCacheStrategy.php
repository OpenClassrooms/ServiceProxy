<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy;

use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseBuilderInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseInterface;

class ServiceProxyCacheStrategy implements ServiceProxyStrategyInterface
{
    private ServiceProxyStrategyResponseBuilderInterface $serviceProxyStrategyResponseBuilder;

    /**
     * @throws \OpenClassrooms\ServiceProxy\Annotations\InvalidCacheIdException
     */
    public function execute(ServiceProxyStrategyRequestInterface $request): ServiceProxyStrategyResponseInterface
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
     * @throws \OpenClassrooms\ServiceProxy\Annotations\InvalidCacheIdException
     */
    private function generatePreSource(ServiceProxyStrategyRequestInterface $request): string
    {
        $source = $this->generateNamespace($request);
        $source .= $this->generateProxyId($request);
        $source .= $this->generateFetch($request);

        return $source;
    }

    private function generateNamespace(ServiceProxyStrategyRequestInterface $request): string
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

    private function getParametersLanguage(ServiceProxyStrategyRequestInterface $request): string
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
     * @throws \OpenClassrooms\ServiceProxy\Annotations\InvalidCacheIdException
     */
    private function generateProxyId(ServiceProxyStrategyRequestInterface $request): string
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

    private function generateFetch(ServiceProxyStrategyRequestInterface $request): string
    {
        if (
            ($returnType = $request->getMethod()->getReturnType()) instanceof \ReflectionNamedType
            && 'void' === $returnType->getName()
        ) {
            return '';
        }

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

    private function generatePostSource(Cache $annotation): string
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
    public function generateProperties(): array
    {
        return [new PropertyGenerator(self::PROPERTY_PREFIX.'cacheProvider', null, AbstractMemberGenerator::FLAG_PRIVATE)];
    }

    /**
     * @return MethodGenerator[]
     */
    public function generateMethods(): array
    {
        return [
            new MethodGenerator(
                self::METHOD_PREFIX.'setCacheProvider',
                [
                    [
                        'name' => 'cacheProvider',
                        'type' => CacheProviderDecorator::class,
                    ],
                ],
                AbstractMemberGenerator::FLAG_PUBLIC,
                '$this->'.self::PROPERTY_PREFIX.'cacheProvider = $cacheProvider;'
            ),
        ];
    }

    public function setServiceProxyStrategyResponseBuilder(
        ServiceProxyStrategyResponseBuilderInterface $serviceProxyStrategyResponseBuilder
    ): void {
        $this->serviceProxyStrategyResponseBuilder = $serviceProxyStrategyResponseBuilder;
    }
}
