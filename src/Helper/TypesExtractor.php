<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Helper;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Symfony\Component\PropertyInfo\PhpStan\NameScope;
use Symfony\Component\PropertyInfo\PhpStan\NameScopeFactory;
use Symfony\Component\PropertyInfo\Util\PhpStanTypeHelper;

final class TypesExtractor
{
    private Lexer $lexer;

    private PhpDocParser $phpDocParser;

    private PhpStanTypeHelper $typeHelper;

    private NameScopeFactory $nameScopeFactory;

    /**
     * @var array<class-string, NameScope>
     */
    private array $resolvedNameScopes = [];

    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->nameScopeFactory = new NameScopeFactory();
        $this->typeHelper = new PhpStanTypeHelper();

        $constExprParser = new ConstExprParser();
        $this->phpDocParser = new PhpDocParser(
            new TypeParser($constExprParser),
            $constExprParser
        );
    }

    /**
     * @return array<string, string>
     */
    public function extractFromMethod(\ReflectionMethod $method): array
    {
        $methodReturnTypes = $this->getTypes($method);
        $types = [$methodReturnTypes];

        foreach ($methodReturnTypes as $methodType) {
            $types[] = $this->getClassMembersTypes($methodType);
        }

        return array_unique(array_merge(...$types));
    }

    /**
     * @return array<int, string>
     */
    private function getTypes(\ReflectionMethod|\ReflectionProperty $member): array
    {
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
                    $member->getDeclaringClass()
                        ->getName(),
                    $docComment,
                    ['@var', '@return'],
                )
            );
        }

        return array_unique($types);
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
            return array_merge(...array_map(
                fn (\ReflectionType $type) => $this->getReflectionTypeNames($type),
                $type->getTypes()
            ));
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
                    foreach ($type->getCollectionValueTypes() as $collectionType) {
                        $classNames[] = $collectionType->getClassName() ?? $collectionType->getBuiltinType();
                    }
                    foreach ($type->getCollectionKeyTypes() as $collectionType) {
                        $classNames[] = $collectionType->getClassName() ?? $collectionType->getBuiltinType();
                    }
                }
            }
        }

        return array_filter($classNames);
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
     * @param class-string $className
     */
    private function getNameScope(string $className): NameScope
    {
        if (!isset($this->resolvedNameScopes[$className])) {
            $this->resolvedNameScopes[$className] = $this->nameScopeFactory->create($className);
        }

        return $this->resolvedNameScopes[$className];
    }
}
