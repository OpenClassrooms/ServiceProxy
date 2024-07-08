<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Cache;
use OpenClassrooms\ServiceProxy\Attribute\Cache\Tag;
use OpenClassrooms\ServiceProxy\Attribute\InvalidateCache;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache\AutoTaggable;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Util\Expression;

trait CacheTagsTrait
{
    private function normalizePrefixName(string $name): string
    {
        return str_replace(
            ['\\', 'SharedResponse', 'Embedded', '_Shared'],
            ['.', '', '', ''],
            $name,
        );
    }

    /**
     * @return array<class-string>
     */
    abstract private function getAutoTagsExcludedClasses(): array;

    /**
     * @return array<int, string>
     */
    private function getTags(Instance $instance, Cache|InvalidateCache $attribute, mixed $response = null): array
    {
        $parameters = $instance->getMethod()
            ->getParameters()
        ;

        $tags = array_map(
            static fn (string $expression) => Expression::evaluateToString($expression, $parameters),
            $attribute->tags
        );

        /** @noinspection PhpConditionCheckedByNextConditionInspection */
        if ($response !== null && \is_object($response)) {
            $prefix = $this->normalizePrefixName(\get_class($response));
        } else {
            $prefix = $this->normalizePrefixName(
                $instance->getReflection()->getName() . $instance->getMethod()->getName()
            );
        }

        return array_values(
            array_filter([
                ...$tags,
                ...$this->guessObjectsTags(
                    $response,
                    prefix: $prefix,
                    excludedClasses: $this->getAutoTagsExcludedClasses(),
                ),
                ...array_merge(...array_values(array_map(fn ($param) => $this->guessObjectsTags(
                    $param,
                    prefix: $prefix,
                    excludedClasses: $this->getAutoTagsExcludedClasses(),
                ), $parameters))),
            ])
        );
    }

    /**
     * @param array<class-string>   $excludedClasses
     * @param array<string, string> $registeredTags
     *
     * @return array<string, string>
     */
    private function guessObjectsTags(
        mixed  $object,
        string $prefix,
        array  $excludedClasses = [],
        array  $registeredTags = []
    ): array {
        if ($object === null) {
            return $registeredTags;
        }

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
                $registeredTags = $this->guessObjectsTags($item, $prefix, $excludedClasses, $registeredTags);
            }

            return $registeredTags;
        }

        if (!$object instanceof AutoTaggable) {
            return $registeredTags;
        }

        $ref = new \ReflectionObject($object);
        $tags = $this->buildTags($object, $ref, $prefix);

        foreach ($tags as $tag) {
            if (isset($registeredTags[$tag])) {
                return $registeredTags;
            }
            $registeredTags[$tag] = $tag;
        }

        foreach ($ref->getProperties() as $propRef) {
            $subObject = $this->getPropertyValue($ref, $object, $propRef->getName());

            $registeredTags = $this->guessObjectsTags($subObject, $prefix, $excludedClasses, $registeredTags);
        }

        return $registeredTags;
    }

    /**
     * @return string[]
     */
    private function buildTags(AutoTaggable $object, \ReflectionObject $ref, string $prefix): array
    {
        $tags = [];
        /** @var \ReflectionMethod|\ReflectionProperty $member */
        foreach ([...$ref->getMethods(), ...$ref->getProperties()] as $member) {
            $tagsAttributes = $member->getAttributes(Tag::class);
            if (\count($tagsAttributes) === 0) {
                if (!$member->isPublic()) {
                    continue;
                }
                if (\in_array($member->getName(), ['id', 'getId', 'userId', 'getUserId'], true)) {
                    $tags[] = $this->buildTagName(
                        $object,
                        $member,
                        $prefix,
                    );
                }
                continue;
            }

            foreach ($tagsAttributes as $tagAttribute) {
                if ($member instanceof \ReflectionMethod) {
                    if (!$member->isPublic() || \count($member->getParameters()) > 0) {
                        throw new \LogicException(
                            sprintf(
                                'Method %s::%s must be public and have no parameters to be used as a tag.',
                                $ref->getName(),
                                $member->getName()
                            )
                        );
                    }
                    $tags[] = $this->buildTagName(
                        $object,
                        $member,
                        $prefix,
                        $tagAttribute
                    );
                }

                if ($member instanceof \ReflectionProperty) {
                    if (!$member->isInitialized($object)) {
                        throw new \LogicException(
                            sprintf(
                                'Property %s::%s must be initialized to be used as a tag.',
                                $ref->getName(),
                                $member->getName()
                            )
                        );
                    }
                    $tags[] = $this->buildTagName(
                        $object,
                        $member,
                        $prefix,
                        $tagAttribute
                    );
                }
            }
        }

        return array_unique($tags);
    }

    /**
     * @param \ReflectionAttribute<Tag>|null $tagAttribute
     */
    private function buildTagName(
        AutoTaggable                          $object,
        \ReflectionProperty|\ReflectionMethod $member,
        ?string                               $prefix,
        ?\ReflectionAttribute                 $tagAttribute = null
    ): string {
        $value = $member instanceof \ReflectionProperty
            ? $member->getValue($object)
            : $member->invoke($object, []);

        $memberPrefix = str_replace(
            ['get', 'has', 'is'],
            ['', '', ''],
            mb_strtolower($member->getName())
        );
        if ($tagAttribute?->newInstance()?->prefix !== null) {
            return $this->normalizePrefixName(
                $tagAttribute->newInstance()->prefix
            ) . '.' . $memberPrefix . '.' . $value;
        }

        return $prefix . '.' . $memberPrefix . '.' . $value;
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

        return $propRef->getValue($object);
    }
}
