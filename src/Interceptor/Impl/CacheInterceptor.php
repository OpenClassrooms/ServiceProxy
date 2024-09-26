<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Cache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Helper\TypesExtractor;
use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache\AutoTaggable;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Exception\InternalCodeRetrievalException;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;
use OpenClassrooms\ServiceProxy\Util\Expression;
use Symfony\Component\PropertyInfo\Type;

final class CacheInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    private const DEFAULT_POOL_NAME = 'default';

    /**
     * @var string[][]
     */
    private static array $hits = [];

    /**
     * @var string[][]
     */
    private static array $misses = [];

    private TypesExtractor $typesExtractor;

    private static string $reservedCharacters = '{}()/@:';

    public function __construct(
        private readonly CacheInterceptorConfig $config,
        iterable                                $handlers = [],
    ) {
        parent::__construct($handlers);

        $this->typesExtractor = new TypesExtractor();
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
            $data = $handler->fetch($pool, $cacheKey);

            if (!$data->isHit()) {
                $missedPools[] = $pool;

                self::$misses[$pool] = self::$misses[$pool] ?? [];
                self::$misses[$pool][] = $cacheKey;

                continue;
            }

            self::$hits[$pool] = self::$hits[$pool] ?? [];
            self::$hits[$pool][] = $cacheKey;

            foreach ($missedPools as $missedPool) {
                $handler->save(
                    $missedPool,
                    $cacheKey,
                    $data->get(),
                    $attribute->ttl ?? $this->config->defaultTtl,
                    $this->getTags($instance, $attribute, $data)
                );
            }

            return new Response($data->get(), true);
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
        $identifier = implode(
            '.',
            [
                $instance->getReflection()
                    ->getName(),
                $instance->getMethod()
                    ->getName(),
                $this->getParametersHash($instance->getMethod()->getParameters()),
                ...$this->getTags($instance, $attribute),
                $attribute->ttl,
                $this->getInnerCode($instance->getMethod()->getReflection()),
                $this->getTypesInnerCode($instance->getMethod()->getReflection()),
            ],
        );

        return hash('xxh3', $identifier);
    }

    private function getTypesInnerCode(\ReflectionMethod $method): string
    {
        $types = $this->typesExtractor->extractFromMethod($method);

        return array_reduce(
            $types,
            fn (string $code, string $classname): string => $this->getTypeInnerCode(
                $classname,
                $code
            ),
            '',
        );
    }

    private function getTypeInnerCode(string $type, string $code): string
    {
        if (\in_array($type, Type::$builtinTypes, true)) {
            return $code . '.' . $type;
        }

        if (!class_exists($type) && !interface_exists($type)) {
            return $code . '.' . $type;
        }

        try {
            $returnClassCode = $this->getInnerCode(new \ReflectionClass($type));
        } catch (InternalCodeRetrievalException) {
            $returnClassCode = '';
        }

        return $code . '.' . $type . '.' . $returnClassCode;
    }

    /**
     * @param \ReflectionMethod|\ReflectionClass<object> $reflection
     */
    private function getInnerCode(\ReflectionMethod|\ReflectionClass $reflection): string
    {
        $name = $reflection->getName();
        $filename = $reflection->getFileName();

        if ($filename === false) {
            throw new InternalCodeRetrievalException($name);
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
                'An error occurred while cleaning %s %s\'s code.',
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
     * @param array<string, string> $registeredTags
     *
     * @return array<string, string>
     */
    private function guessObjectsTags(mixed $object, array $excludedClasses = [], array $registeredTags = []): array
    {
        if (!\is_object($object) && !is_iterable($object)) {
            return $registeredTags;
        }

        foreach ($excludedClasses as $excludedClass) {
            if ($object instanceof $excludedClass) {
                return $registeredTags;
            }
        }

        if (is_iterable($object)) {
            foreach ($object as $item) {
                $registeredTags = $this->guessObjectsTags($item, $excludedClasses, $registeredTags);
            }

            return $registeredTags;
        }

        if (!$object instanceof AutoTaggable) {
            return $registeredTags;
        }

        $tag = $this->buildTag($object);

        if (isset($registeredTags[$tag])) {
            return $registeredTags;
        }

        $registeredTags[$tag] = $tag;

        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $propRef) {
            $subObject = $this->getPropertyValue($ref, $object, $propRef->getName());

            $registeredTags = $this->guessObjectsTags($subObject, $excludedClasses, $registeredTags);
        }

        return $registeredTags;
    }

    private function buildTag(AutoTaggable $object): string
    {
        return str_replace('\\', '.', \get_class($object)) . '.' . str_replace(self::$reservedCharacters, '', (string)$object->getId());
    }

    /**
     * @param \ReflectionClass<object> $ref
     */
    private function getPropertyValue(\ReflectionClass $ref, object $object, string $propertyName): mixed
    {
        $getter = 'get' . ucfirst($propertyName);
        $refMethod = $ref->hasMethod($getter) ? $ref->getMethod($getter) : null;
        if ($refMethod !== null && $refMethod->isPublic() && \count($refMethod->getParameters()) === 0) {
            return $refMethod->invoke($object);
        }

        $propRef = $ref->getProperty($propertyName);
        if (!$propRef->isInitialized($object)) {
            return null;
        }

        $propRef->setAccessible(true);

        return $propRef->getValue($object);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function getParametersHash(array $parameters): string
    {
        $identifier = '';
        foreach ($parameters as $parameterName => $parameterValue) {
            $identifier .= '.' . $parameterName . '.' . serialize($parameterValue);
        }

        return $identifier;
    }
}
