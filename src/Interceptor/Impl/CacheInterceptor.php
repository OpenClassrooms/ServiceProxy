<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Cache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;
use OpenClassrooms\ServiceProxy\Util\Expression;

final class CacheInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    private const DEFAULT_POOL_NAME = 'default';

    private const AUTO_TAG_PROPERTY_NAME = 'id';

    /**
     * @var string[][]
     */
    private static array $hits = [];

    /**
     * @var string[][]
     */
    private static array $misses = [];

    public function __construct(
        private readonly CacheInterceptorConfig $config,
        iterable                                $handlers = [],
    ) {
        parent::__construct($handlers);
    }

    /**
     * @return array<int, string>
     */
    public static function getHits(?string $poolName = self::DEFAULT_POOL_NAME): array
    {
        return self::$hits[$poolName] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public static function getMisses(?string $poolName = self::DEFAULT_POOL_NAME): array
    {
        return self::$misses[$poolName] ?? [];
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

        $pools = \count($attribute->pools) === 0 ? [self::DEFAULT_POOL_NAME] : $attribute->pools;

        if ($returnType instanceof \ReflectionNamedType && $returnType->getName() === 'void') {
            self::$misses = array_combine(
                $pools,
                [array_fill(0, \count($pools), $cacheKey)]
            );

            return new Response(null, false);
        }

        $handler = $this->getHandlers(CacheHandler::class, $attribute)[0];

        $missedPools = [];

        foreach ($pools as $pool) {
            if (!$handler->contains($pool, $cacheKey)) {
                $missedPools[] = $pool;

                self::$misses[$pool] = self::$misses[$pool] ?? [];
                self::$misses[$pool][] = $cacheKey;

                continue;
            }

            $data = $handler->fetch($pool, $cacheKey);

            self::$hits[$pool] = self::$hits[$pool] ?? [];
            self::$hits[$pool][] = $cacheKey;

            foreach ($missedPools as $missedPool) {
                $handler->save(
                    $missedPool,
                    $cacheKey,
                    $data,
                    $attribute->ttl ?? $this->config->defaultTtl,
                    $this->getTags($instance, $attribute, $data)
                );
            }

            return new Response($data, true);
        }

        return new Response(null, false);
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

        $handler = $this->getHandlers(CacheHandler::class, $attribute)[0];
        $pools = \count($attribute->pools) === 0 ? [self::DEFAULT_POOL_NAME] : $attribute->pools;

        foreach ($pools as $pool) {
            $handler->save(
                $pool,
                $cacheKey,
                $data,
                $attribute->ttl ?? $this->config->defaultTtl,
                $this->getTags($instance, $attribute, $data)
            );
        }

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
            static fn (string $identifier, string $tag) => $identifier . '.' . $tag,
            $identifier
        );

        $identifier .= $attribute->ttl;
        $identifier .= $this->getMethodInnerCode($method->getReflection());
        $identifier .= $method->getReflection()->getReturnType() !== null
            ? $this->getResponseInnerCode($method->getReflection()->getReturnType())
            : '';

        return hash('xxh3', $identifier);
    }

    private function getMethodInnerCode(\ReflectionMethod $method): string
    {
        return $this->getCode($method);
    }

    private function getResponseInnerCode(\ReflectionType $returnType): string
    {
        if (!$returnType instanceof \ReflectionNamedType) {
            return '';
        }

        if ($returnType->getName() === 'void') {
            return $returnType->getName();
        }

        if ($returnType->isBuiltin()) {
            return $returnType->getName();
        }

        $returnClassName = $returnType->getName();

        if (!class_exists($returnClassName)) {
            return '';
        }

        $returnClass = new \ReflectionClass($returnClassName);

        return $this->getCode($returnClass);
    }

    /**
     * @param \ReflectionMethod|\ReflectionClass<object> $reflection
     */
    private function getCode(\ReflectionMethod|\ReflectionClass $reflection): string
    {
        $name = $reflection->getName();
        $filename = $reflection->getFileName();

        if ($filename === false) {
            throw new \RuntimeException("Method {$name} seems to be an internal method.");
        }

        $file = file($filename);

        if ($file === false) {
            throw new \LogicException("Unable to open file {$filename}.");
        }

        $startLine = $reflection->getStartLine();

        if ($startLine === false) {
            throw new \LogicException("Unable to find {$name} start line.");
        }

        $endLine = $reflection->getEndLine();

        if ($endLine === false) {
            throw new \LogicException("Unable to find {$name} end line.");
        }

        $length = $endLine - $startLine;

        $code = \array_slice($file, $startLine, $length);
        $code = preg_replace('/\s+/', '', implode('', $code));

        if ($code === null) {
            throw new \RuntimeException(sprintf(
                'An error occured while cleaning %s %s\'s code.',
                $name,
                $reflection instanceof \ReflectionMethod ? 'method' : 'class',
            ));
        }

        return $code;
    }

    /**
     * @return array<int, string>
     */
    private function getTags(Instance $instance, Cache $attribute, mixed $response = null): array
    {
        $parameters = $instance->getMethod()
            ->getParameters();

        $tags = array_map(
            static fn (string $expression) => Expression::evaluateToString($expression, $parameters),
            $attribute->tags
        );

        if ($response !== null) {
            $tags = array_values(array_filter([
                ...$tags,
                ...$this->guessObjectsTags(
                    $response,
                    $this->config->autoTagsExcludedClasses
                ),
            ]));
        }

        return $tags;
    }

    /**
     * @param array<class-string> $excludedClasses
     * @return array<string, string>
     */
    private function guessObjectsTags(mixed $object, array $excludedClasses = []): array
    {
        if (!\is_object($object)) {
            return [];
        }

        foreach ($excludedClasses as $excludedClass) {
            if ($object instanceof $excludedClass) {
                return [];
            }
        }

        $ref = new \ReflectionClass($object);

        $tags = [];
        foreach ($ref->getProperties() as $propRef) {
            if (!$propRef->isInitialized($object)) {
                continue;
            }
            $getter = 'get' . ucfirst(self::AUTO_TAG_PROPERTY_NAME);
            if ($propRef->getName() === self::AUTO_TAG_PROPERTY_NAME || $ref->hasMethod($getter)) {
                $tag =
                    str_replace('\\', '.', \get_class($object))
                    .
                    '.'
                    . $this->getPropertyValue($ref, $object, self::AUTO_TAG_PROPERTY_NAME);
                if (isset($tags[$tag])) {
                    return $tags;
                }
                $tags[$tag] = $tag;
                continue;
            }
            $subObject = $this->getPropertyValue($ref, $object, $propRef->getName());
            if (is_iterable($subObject)) {
                foreach ($subObject as $item) {
                    $tags = [...$tags, ...$this->guessObjectsTags($item, $excludedClasses)];
                }
            } else {
                $tags = [...$tags, ...$this->guessObjectsTags($subObject, $excludedClasses)];
            }
        }

        return $tags;
    }

    /**
     * @param \ReflectionClass<object> $ref
     */
    private function getPropertyValue(\ReflectionClass $ref, object $object, string $propertyName): mixed
    {
        $getter = 'get' . ucfirst($propertyName);
        $refMethod = $ref->hasMethod($getter) ? $ref->getMethod($getter) : null;
        if ($refMethod !== null && $refMethod->isPublic()) {
            return $refMethod->invoke($object);
        }
        $propRef = $ref->getProperty($propertyName);
        $propRef->setAccessible(true);

        return $propRef->getValue($object);
    }
}
