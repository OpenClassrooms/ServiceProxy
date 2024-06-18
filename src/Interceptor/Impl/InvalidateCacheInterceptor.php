<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\InvalidateCache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;
use OpenClassrooms\ServiceProxy\Util\Expression;

final class InvalidateCacheInterceptor extends AbstractInterceptor implements SuffixInterceptor
{
    private string $defaultPoolName = 'default';

    public function suffix(Instance $instance): Response
    {
        if ($instance->getMethod()->threwException()) {
            return new Response();
        }

        $attribute = $instance->getMethod()
            ->getAttribute(InvalidateCache::class);

        $handler = $this->getHandlers(CacheHandler::class, $attribute)[0];
        $pools = \count($attribute->pools) === 0 ? [$this->defaultPoolName] : $attribute->pools;

        $tags = $this->getTags($instance, $attribute);

        foreach ($pools as $pool) {
            $handler->invalidateTags($pool, $tags);
        }

        return new Response();
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAttribute(InvalidateCache::class);
    }

    public function getSuffixPriority(): int
    {
        return 40;
    }

    /**
     * @template T of object
     *
     * @param Instance<T> $instance
     *
     * @return array<int, string>
     */
    private function getTags(Instance $instance, InvalidateCache $attribute): array
    {
        $parameters = $instance->getMethod()
            ->getParameters()
        ;

        $tags = array_map(
            static fn (string $expression) => Expression::evaluateToString($expression, $parameters),
            $attribute->tags
        );

        if (\count($tags) > 0) {
            return $tags;
        }

        $guessedTags = array_values(
            array_filter(
                $this->guessObjectsTags($instance->getMethod()->getResponse())
            )
        );

        if (\count($guessedTags) === 0) {
            throw new \RuntimeException('No tags found for method ' . $instance->getMethod()->getName());
        }

        return $guessedTags;
    }

    /**
     * @return array<string, string>
     */
    private function guessObjectsTags(mixed $objects): array
    {
        if (!is_iterable($objects)) {
            $objects = [$objects];
        }

        $tags = [];
        foreach ($objects as $object) {
            if (!\is_object($object)) {
                continue;
            }

            $ref = new \ReflectionClass($object);
            $id = $this->getPropertyValue($ref, $object, 'id');
            if ($id === false || $id === null) {
                continue;
            }
            $tag = $this->getNormalizedNamespace($object) . '.' . $id;
            $tags[$tag] = $tag;
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
        if (!$propRef->isInitialized($object)) {
            return false;
        }
        $propRef->setAccessible(true);

        return $propRef->getValue($object);
    }

    private function getNormalizedNamespace(object $object): string
    {
        return str_replace('\\', '.', \get_class($object));
    }
}
