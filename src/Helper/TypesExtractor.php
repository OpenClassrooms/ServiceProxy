<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Helper;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

final class TypesExtractor
{
    private Lexer $lexer;

    private PhpDocParser $phpDocParser;

    public function __construct()
    {
        $config = new ParserConfig([]);
        $this->lexer = new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $this->phpDocParser = new PhpDocParser($config, new TypeParser($config, $constExprParser), $constExprParser);
    }

    /**
     * @return array<string>
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

        $types = [];

        if ($type !== null) {
            $types = array_merge(
                $types,
                $this->getReflectionTypeNames($type)
            );
        }

        // Parse PHPDoc (@return, @var) with PHPStan PhpDocParser
        $docComment = $member->getDocComment() !== false ? $member->getDocComment() : null;
        if ($docComment !== null) {
            $declaringNs = $member->getDeclaringClass()->getNamespaceName();
            $types = array_merge(
                $types,
                $this->getPhpDocTypesNames($declaringNs, $docComment, ['@var', '@return'])
            );
        }

        return array_values(array_unique($types));
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
     * Extract type names from PHPDoc tags using PHPStan PhpDocParser.
     * @param array<string> $tagNames
     * @return array<int, string>
     */
    private function getPhpDocTypesNames(string $namespace, string $docComment, array $tagNames): array
    {
        $results = [];
        $tokens = new TokenIterator($this->lexer->tokenize($docComment));
        $phpDocNode = $this->phpDocParser->parse($tokens);

        foreach ($phpDocNode->getTags() as $tagNode) {
            if (!\in_array($tagNode->name, $tagNames, true)) {
                continue;
            }
            $results = array_merge($results, $this->extractFromTagNode($tagNode, $namespace));
        }

        return array_values(array_unique(array_filter($results)));
    }

    /**
     * @return array<int, string>
     */
    private function extractFromTagNode(PhpDocTagNode $tagNode, string $namespace): array
    {
        $builtins = [
            'int', 'string', 'bool', 'float', 'array', 'object', 'callable', 'iterable', 'mixed', 'void', 'null', 'false', 'true', 'self', 'static', 'parent',
        ];

        $typeNode = $tagNode->value instanceof ReturnTagValueNode || $tagNode->value instanceof VarTagValueNode
            ? $tagNode->value->type
            : null;

        if ($typeNode === null) {
            return [];
        }

        return $this->collectTypeNames($typeNode, $namespace, $builtins);
    }

    /**
     * Walk a TypeNode tree and collect class-like names.
     * @param array<string> $builtins
     * @return array<int, string>
     */
    private function collectTypeNames(TypeNode $typeNode, string $namespace, array $builtins): array
    {
        $names = [];

        if ($typeNode instanceof IdentifierTypeNode) {
            $names = array_merge($names, $this->resolveClassName($typeNode->name, $namespace, $builtins));
        } elseif ($typeNode instanceof NullableTypeNode) {
            $names = array_merge($names, $this->collectTypeNames($typeNode->type, $namespace, $builtins));
        } elseif ($typeNode instanceof ArrayTypeNode) {
            $names = array_merge($names, $this->collectTypeNames($typeNode->type, $namespace, $builtins));
        } elseif ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $t) {
                $names = array_merge($names, $this->collectTypeNames($t, $namespace, $builtins));
            }
        } elseif ($typeNode instanceof IntersectionTypeNode) {
            foreach ($typeNode->types as $t) {
                $names = array_merge($names, $this->collectTypeNames($t, $namespace, $builtins));
            }
        } elseif ($typeNode instanceof GenericTypeNode) {
            $names = array_merge($names, $this->collectTypeNames($typeNode->type, $namespace, $builtins));
            foreach ($typeNode->genericTypes as $gt) {
                $names = array_merge($names, $this->collectTypeNames($gt, $namespace, $builtins));
            }
        } elseif ($typeNode instanceof ThisTypeNode) {
            // ignore $this type
        }

        return $names;
    }

    /**
     * @param array<string> $builtins
     * @return array<int, string>
     */
    private function resolveClassName(string $token, string $namespace, array $builtins): array
    {
        $token = mb_trim($token);
        if ($token === '') {
            return [];
        }

        $isFqcn = str_starts_with($token, '\\');
        $normalized = $isFqcn ? mb_substr($token, 1) : $token;
        if (\in_array(mb_strtolower($normalized), $builtins, true)) {
            return [];
        }

        if ($isFqcn || str_contains($normalized, '\\')) {
            return [$normalized];
        }

        if ($namespace !== '') {
            return [$namespace . '\\' . $normalized];
        }

        return [$normalized];
    }

    /**
     * @param class-string[] $registeredTypes
     * @return class-string[]
     */
    private function getClassMembersTypes(string $type, array $registeredTypes = []): array
    {
        if ((!class_exists($type) && !interface_exists($type)) || isset($registeredTypes[$type])) {
            return $registeredTypes;
        }

        $registeredTypes[$type] = $type;

        $ref = new \ReflectionClass($type);

        $subTypes = $this->getMembersTypes([
            ...$ref->getMethods(\ReflectionMethod::IS_PUBLIC),
            ...$ref->getProperties(\ReflectionProperty::IS_PUBLIC),
        ]);

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
}
