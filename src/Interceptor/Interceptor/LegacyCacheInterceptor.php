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

/**
 * @deprecated use CacheHandler instead
 */
final class LegacyCacheInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    public function prefix(Instance $instance): Response
    {
        $annotation = $instance->getMethod()
            ->getAnnotation(Cache::class)
        ;
        $proxyId = $this->getProxyId($instance, $annotation);
        $tags = $this->getTags($instance, $annotation);

        $returnType = $instance->getMethod()
            ->getReflection()
            ->getReturnType()
        ;
        if ($returnType instanceof \ReflectionNamedType && $returnType->getName() === 'void') {
            return new Response(null, false);
        }

        $handler = $this->getHandler(CacheHandler::class, $annotation);

        array_unshift($tags, $proxyId);
        $data = $handler->fetch(implode('|', $tags));

        // this is needed to solve a bug (when the false is stored in the cache)

        if ($data === false) {
            return new Response(null, false);
        }

        return new Response($data, true);
    }

    public function suffix(Instance $instance): Response
    {
        if ($instance->getMethod()->getResponse() instanceof \Exception) {
            return new Response();
        }

        $annotation = $instance->getMethod()
            ->getAnnotation(Cache::class)
        ;
        $proxyId = $this->getProxyId($instance, $annotation);
        $tags = $this->getTags($instance, $annotation);

        $data = $instance->getMethod()
            ->getResponse()
        ;

        $handler = $this->getHandler(CacheHandler::class, $annotation);

        $handler->save(
            $proxyId,
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
            ->hasAnnotation(Cache::class);
    }

    public function getPrefixPriority(): int
    {
        return 10;
    }

    public function getSuffixPriority(): int
    {
        return 20;
    }

    private function getNamespace(Instance $instance, Cache $annotation): ?string
    {
        $parameters = $instance->getMethod()
            ->getParameters()
        ;
        if ($annotation->getNamespace() !== null) {
            $resolvedExpression = $this->resolveExpression(
                $annotation->getNamespace(),
                $parameters
            );

            return md5($resolvedExpression);
        }

        return null;
    }

    private function getProxyId(Instance $instance, Cache $annotation): string
    {
        $parameters = $instance->getMethod()
            ->getParameters()
        ;
        if ($annotation->getId() !== null) {
            return $this->resolveExpression(
                $annotation->getId(),
                $parameters
            );
        }

        $key = $instance->getReflection()
            ->getName() . '::' . $instance->getMethod()->getName();
        if (\count($parameters) > 0) {
            foreach ($parameters as $parameter) {
                $key .= '::' . serialize($parameter);
            }
        }

        return md5($key);
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
        $namespace = $this->getNamespace($instance, $annotation);
        $parameters = $instance->getMethod()
            ->getParameters();
        $tags = [];
        foreach ($annotation->getTags() as $tag) {
            $tags[] = $this->resolveExpression(
                $tag,
                $parameters
            );
        }

        if ($namespace !== null) {
            array_unshift($tags, $namespace);
        }

        return array_filter($tags);
    }
}
