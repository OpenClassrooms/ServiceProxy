<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Cache;
use OpenClassrooms\ServiceProxy\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class CacheInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    protected int $prefixPriority = 10;

    protected int $suffixPriority = 20;

    private ?string $namespace = null;

    private string $proxyId;

    public function prefix(Instance $instance): Response
    {
        $annotation = $instance->getMethod()->getAnnotation(Cache::class);
        $this->setNamespace($instance, $annotation);
        $this->setProxyId($instance, $annotation);

        $returnType = $instance->getMethod()->getReflection()->getReturnType();
        if ($returnType instanceof \ReflectionNamedType && 'void' === $returnType->getName()) {
            return new Response(null, false);
        }

        $data = $this->getHandler(CacheHandler::class, $annotation)
                     ->fetchWithNamespace($this->proxyId, $this->namespace)
        ;

        if (false === $data) {
            return new Response(null, false);
        }

        return new Response($data, true);
    }

    private function setNamespace(Instance $instance, Cache $annotation): void
    {
        $expressionLanguage = new ExpressionLanguage();
        $parameters = $instance->getMethod()->getParameters();
        if (null !== $annotation->getNamespace()) {
            $this->namespace = md5(
                $expressionLanguage->evaluate(
                    $annotation->getNamespace(),
                    $parameters
                )
            );
        }
    }

    private function setProxyId(Instance $instance, Cache $annotation): void
    {
        $expressionLanguage = new ExpressionLanguage();
        $parameters = $instance->getMethod()->getParameters();
        if (null !== $annotation->getId()) {
            $this->proxyId = $expressionLanguage->evaluate(
                $annotation->getId(),
                $parameters
            );
        } else {
            $key = $instance->getReflection()->getName() . '::' . $instance->getMethod()->getName();
            if (count($parameters) > 0) {
                foreach ($parameters as $parameter) {
                    $key .= '::' . serialize($parameter);
                }
            }
            $this->proxyId = md5($key);
        }
    }

    public function suffix(Instance $instance): Response
    {
        if ($instance->getMethod()->getResponse() instanceof \Exception) {
            return new Response();
        }

        $annotation = $instance->getMethod()->getAnnotation(Cache::class);
        $data = $instance->getMethod()->getResponse();
        $this->getHandler(CacheHandler::class, $annotation)
             ->saveWithNamespace(
                 $this->proxyId,
                 $data,
                 $this->namespace,
                 $annotation->getLifetime()
             )
        ;

        return new Response($data);
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $this->supportsPrefix($instance);
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()->hasAnnotation(Cache::class);
    }
}
