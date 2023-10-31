<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Cache;
use OpenClassrooms\ServiceProxy\ExpressionLanguage\ExpressionResolver;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Exception\InternalCodeRetrievalException;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Symfony\Component\PropertyInfo\PhpStan\NameScope;
use Symfony\Component\PropertyInfo\PhpStan\NameScopeFactory;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\PropertyInfo\Util\PhpStanTypeHelper;

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

    private ExpressionResolver $expressionResolver;

    private PhpDocParser $phpDocParser;

    private Lexer $lexer;

    private PhpStanTypeHelper $typeHelper;

    private NameScopeFactory $nameScopeFactory;

    /**
     * @var array<class-string, NameScope>
     */
    private array $resolvedNameScopes = [];

    public function __construct(
        private readonly CacheInterceptorConfig $config,
        iterable                                $handlers = [],
    ) {
        parent::__construct($handlers);

        $this->expressionResolver = new ExpressionResolver();
        $this->typeHelper = new PhpStanTypeHelper();
        $this->nameScopeFactory = new NameScopeFactory();
        $this->lexer = new Lexer();

        $constExprParser = new ConstExprParser();

        $this->phpDocParser = new PhpDocParser(
            new TypeParser($constExprParser),
            $constExprParser
        );
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

        $handler = $this->getHandler(CacheHandler::class, $attribute);

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

        $handler = $this->getHandler(CacheHandler::class, $attribute);
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
                $instance->getReflection()->getName(),
                $instance->getMethod()->getName(),
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
        $returnedTypes = $this->getTypes($method);

        foreach ($returnedTypes as $type) {
            $returnedTypes = $this->getClassMembersTypes($type, $returnedTypes);
        }

        return array_reduce(
            $returnedTypes,
            fn (string $code, string $returnClassname): string => $this->getTypeInnerCode(
                $returnClassname,
                $code
            ),
            '',
        );
    }

    /**
     * @param string[] $registeredTypes
     *
     * @return string[]
     */
    private function getClassMembersTypes(string $type, array $registeredTypes = []): array
    {
        if ((!class_exists($type) && !interface_exists($type)) || isset($registeredTypes[$type])) {
            return $registeredTypes;
        }

        $registeredTypes[$type] = $type;

        if (in_array($type, Type::$builtinTypes, true)) {
            return $registeredTypes;
        }

        $ref = new \ReflectionClass($type);

        $subTypes = $this->getMembersTypes(
            [
                ...$ref->getMethods(\ReflectionMethod::IS_PUBLIC),
                ...$ref->getProperties(\ReflectionProperty::IS_PUBLIC),
            ]
        );

        foreach ($subTypes as $subType) {
            $registeredTypes = $this->getClassMembersTypes($subType, $registeredTypes);
        }

        return $registeredTypes;
    }

    /**
     * @return array<int, string>
     */
    private function getTypes(\ReflectionMethod|\ReflectionProperty $member): array {
        $type = $member instanceof \ReflectionMethod
            ? $member->getReturnType()
            : $member->getType();

        $docComment = $member->getDocComment() !== false
            ? $member->getDocComment()
            : null;

        $types = [];

        if ($type !== null) {
            $types = array_merge(
                $types,
                $this->getReflectionTypeNames($type)
            );
        }

        if ($docComment !== null) {
            $types = array_merge(
                $types,
                $this->getPhpDocTypesNames(
                    $member->getDeclaringClass()->getName(),
                    $docComment,
                    ['@var', '@return'],
                )
            );
        }

        return array_unique($types);
    }

    /**
     * @param array<\ReflectionMethod|\ReflectionProperty> $members
     * @return array<int, string>
     */
    private function getMembersTypes(array $members): array
    {
        $types = [];
        foreach ($members as $member) {
            $types[] = $this->getTypes($member);
        }

        return array_unique(array_merge(...$types));
    }

    /**
     * @return array<int, string>
     */
    private function getReflectionTypeNames(\ReflectionType $type): array
    {
        if ($type instanceof \ReflectionNamedType) {
            return [$type->getName()];
        }

        if ($type instanceof \ReflectionUnionType) {
            return array_map(
                static fn (\ReflectionNamedType $type) => $type->getName(),
                $type->getTypes()
            );
        }

        return [];
    }

    /**
     * @param array<string> $tagNames
     * @param class-string  $classContext
     *
     * @return array<int, string>
     */
    private function getPhpDocTypesNames(string $classContext, string $docComment, array $tagNames = []): array
    {
        $classNames = [];
        $nameScope = $this->getNameScope($classContext);
        $tokens = new TokenIterator($this->lexer->tokenize($docComment));

        $phpDocNode = $this->phpDocParser->parse($tokens);
        foreach ($tagNames as $tagName) {
            $tags = $phpDocNode->getTagsByName($tagName);
            foreach ($tags as $tag) {
                $types = $this->typeHelper->getTypes($tag->value, $nameScope);
                foreach ($types as $type) {
                    $classNames[] = $type->getClassName() ?? $type->getBuiltinType();
                    if ($type->isCollection()) {
                        foreach ($type->getCollectionValueTypes() as $collectionValueType) {
                            $classNames[] = $collectionValueType->getClassName() ?? $collectionValueType->getBuiltinType();
                        }
                    }
                }
            }
        }

        return array_filter($classNames);
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
            fn (string $expression) => $this->expressionResolver->resolve($expression, $parameters),
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

    /**
     * @param class-string $className
     */
    private function getNameScope(string $className): NameScope
    {
        if (!isset($this->resolvedNameScopes[$className])) {
            $this->resolvedNameScopes[$className] = $this->nameScopeFactory->create($className);
        }

        return $this->resolvedNameScopes[$className];
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
