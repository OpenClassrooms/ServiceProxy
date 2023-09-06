<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Cache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use OpenClassrooms\ServiceProxy\Util\Expression;

final class CacheInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    /**
     * @var string[]
     */
    private static array $hits = [];

    /**
     * @var string[]
     */
    private static array $misses = [];

    /**
     * @return string[]
     */
    public static function getHits(): array
    {
        return self::$hits;
    }

    /**
     * @return string[]
     */
    public static function getMisses(): array
    {
        return self::$misses;
    }

    public function prefix(Instance $instance): Response
    {
        self::$hits = [];
        self::$misses = [];

        $attribute = $instance->getMethod()
            ->getAttribute(Cache::class);

        $cacheKey = $this->buildCacheKey($instance, $attribute);

        $returnType = $instance->getMethod()
            ->getReflection()
            ->getReturnType()
        ;

        if ($returnType instanceof \ReflectionNamedType && $returnType->getName() === 'void') {
            self::$misses[] = $cacheKey;

            return new Response(null, false);
        }

        $handler = $this->getHandler(CacheHandler::class, $attribute);

        if ($handler->contains($cacheKey) === false) {
            self::$misses[] = $cacheKey;

            return new Response(null, false);
        }

        self::$hits[] = $cacheKey;

        return new Response($handler->fetch($cacheKey), true);
    }

    public function suffix(Instance $instance): Response
    {
        if ($instance->getMethod()->threwException()) {
            return new Response();
        }

        $attribute = $instance->getMethod()
            ->getAttribute(Cache::class)
        ;

        $cacheKey = $this->buildCacheKey($instance, $attribute);

        $data = $instance->getMethod()
            ->getResponse()
        ;

        $handler = $this->getHandler(CacheHandler::class, $attribute);

        $handler->save(
            $cacheKey,
            $data,
            $attribute->getLifetime(),
            $this->getTags($instance, $attribute)
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
            ->hasAttribute(Cache::class);
    }

    public function getPrefixPriority(): int
    {
        return 10;
    }

    public function getSuffixPriority(): int
    {
        return 20;
    }

    private function buildCacheKey(Instance $instance, Cache $attribute): string
    {
        $method = $instance->getMethod();

        $parameters = $method->getParameters();

        $identifier = $instance->getReflection()
            ->getName() . '.' . $method->getName();

        if (\count($parameters) > 0) {
            foreach ($parameters as $parameterName => $parameterValue) {
                $identifier .= '.' . $parameterName . '.' . hash('xxh3', serialize($parameterValue));
            }
        }

        $identifier = array_reduce(
            $this->getTags($instance, $attribute),
            static fn (string $identifier, string $tag) => $identifier .= '.' . $tag,
            $identifier
        );

        $identifier .= $this->getMethodCode($method->getReflection());

        return hash('xxh3', $identifier);
    }

    private function getMethodCode(\ReflectionMethod $reflectionMethod): string
    {
        $methodName = $reflectionMethod->getName();
        $filename = $reflectionMethod->getFileName();

        if ($filename === false) {
            throw new \RuntimeException("Method {$methodName} seems to be an internal method.");
        }

        $file = file($filename);

        if ($file === false) {
            throw new \LogicException("Unable to open file {$filename}.");
        }

        $startLine = $reflectionMethod->getStartLine();

        if ($startLine === false) {
            throw new \LogicException("Unable to find {$methodName} start line.");
        }

        $endLine = $reflectionMethod->getEndLine();

        if ($endLine === false) {
            throw new \LogicException("Unable to find {$methodName} end line.");
        }

        $length = $endLine - $startLine;

        $code = \array_slice($file, $startLine, $length);
        $code = preg_replace('/\s+/', '', implode('', $code));

        if ($code === null) {
            throw new \RuntimeException("An error occurd while cleaning {$methodName} method's code.");
        }

        return $code;
    }

    /**
     * @return array<int, string>
     */
    private function getTags(Instance $instance, Cache $attribute): array
    {
        $parameters = $instance->getMethod()
            ->getParameters();

        $tags = array_map(
            static fn (string $expression) => Expression::evaluateToString($expression, $parameters),
            $attribute->getTags()
        );

        return array_filter($tags);
    }
}
