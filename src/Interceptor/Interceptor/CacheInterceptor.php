<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Cache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class CacheInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    public function prefix(Instance $instance): Response
    {
        $annotation = $instance->getMethod()
            ->getAnnotation(Cache::class)
        ;

        $cacheKey = $this->buildCacheKey($instance, $annotation);

        $returnType = $instance->getMethod()
            ->getReflection()
            ->getReturnType()
        ;

        if ($returnType instanceof \ReflectionNamedType && $returnType->getName() === 'void') {
            return new Response(null, false);
        }

        $handler = $this->getHandler(CacheHandler::class, $annotation);

        if ($handler->contains($cacheKey) === false) {
            return new Response(null, false);
        }

        return new Response($handler->fetch($cacheKey), true);
    }

    public function suffix(Instance $instance): Response
    {
        if ($instance->getMethod()->getResponse() instanceof \Exception) {
            return new Response();
        }

        $annotation = $instance->getMethod()
            ->getAnnotation(Cache::class)
        ;
        $cacheKey = $this->buildCacheKey($instance, $annotation);
        $tags = $this->getTags($instance, $annotation);

        $data = $instance->getMethod()
            ->getResponse()
        ;

        $handler = $this->getHandler(CacheHandler::class, $annotation);

        $handler->save(
            $cacheKey,
            $data,
            $annotation->getLifetime(),
            $tags
        );

        return new Response($data);
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $this->supportsPrefix($instance);
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAnnotation(Cache::class)
        ;
    }

    public function getPrefixPriority(): int
    {
        return 10;
    }

    public function getSuffixPriority(): int
    {
        return 20;
    }

    private function getNamespace(Instance $instance, Cache $annotation): string
    {
        $parameters = $instance->getMethod()
            ->getParameters()
        ;

        if ($annotation->getNamespace() !== null) {
            return $this->resolveExpression(
                $annotation->getNamespace(),
                $parameters
            );
        }

        $namespace = $instance->getReflection()
            ->getName() . '::' . $instance->getMethod()->getName();

        if (\count($parameters) > 0) {
            foreach ($parameters as $parameter) {
                $namespace .= '::' . serialize($parameter);
            }
        }

        return md5($namespace);
    }

    private function buildCacheKey(Instance $instance, Cache $annotation): string
    {
        $id = '';

        $parameters = $instance->getMethod()
            ->getParameters()
        ;

        if ($annotation->getId() !== null) {
            $id = $this->resolveExpression(
                $annotation->getId(),
                $parameters
            );
        }

        return $this->getNamespace($instance, $annotation) . $id;
    }

    /**
     * @param mixed[] $parameters
     */
    private function resolveExpression(string $expression, array $parameters): string
    {
        $expressionLanguage = new ExpressionLanguage();
        $resolvedExpression = $expressionLanguage->evaluate(
            $expression,
            $parameters
        );

        if (!\is_string($resolvedExpression)) {
            throw new \InvalidArgumentException(
                "Provided expression `{$expression}` did not resolve to a string."
            );
        }

        return $resolvedExpression;
    }

    /**
     * @return array<int, string>
     */
    private function getTags(Instance $instance, Cache $annotation): array
    {
        $parameters = $instance->getMethod()
            ->getParameters();
        $tags = [];
        foreach ($annotation->getTags() as $tag) {
            $tags[] = $this->resolveExpression(
                $tag,
                $parameters
            );
        }

        return array_filter($tags);
    }
}
