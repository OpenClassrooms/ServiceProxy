<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

use OpenClassrooms\ServiceProxy\Contract\EventHandler;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Event extends Annotation
{
    public const DEFAULT_METHOD = self::POST_METHOD;

    public const DEFAULT_PREFIX = 'use_case';

    public const ON_EXCEPTION_METHOD = 'onException';

    public const POST_METHOD = 'post';

    public const PRE_METHOD = 'pre';

    /**
     * @var string[]
     */
    private static array $allowedMethods = [
        self::PRE_METHOD,
        self::POST_METHOD,
        self::ON_EXCEPTION_METHOD,
    ];

    private ?string $defaultPrefix = self::DEFAULT_PREFIX;

    /**
     * @var array<int, 'pre'|'post'|'onException'>
     */
    private array $methods = [self::DEFAULT_METHOD];

    private ?string $name = null;

    private bool $useClassNameOnly = true;

    public function getDefaultPrefix(): string
    {
        return $this->defaultPrefix ?: self::DEFAULT_PREFIX;
    }

    /**
     * @return array<int, 'pre'|'post'|'onException'>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function hasMethod(string $method): bool
    {
        return \in_array($method, $this->methods, true);
    }

    public function isUseClassNameOnly(): bool
    {
        return $this->useClassNameOnly;
    }

    public function setDefaultPrefix(?string $defaultPrefix): void
    {
        $this->defaultPrefix = $defaultPrefix;
    }

    /**
     * @param string[]|string $methods
     */
    public function setMethods($methods): void
    {
        if (\is_string($methods)) {
            $methods = array_map('trim', explode(',', $methods));
        }

        $diff = array_diff($methods, self::$allowedMethods);
        if (count($diff) > 0) {
            throw new \InvalidArgumentException(
                'Method "'
                . implode(',', $diff) . '" is not allowed. Allowed: '
                . implode(',', self::$allowedMethods)
            );
        }

        // @phpstan-ignore-next-line
        $this->methods = $methods;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setUseClassNameOnly(bool $useClassNameOnly): void
    {
        $this->useClassNameOnly = $useClassNameOnly;
    }

    public function getHandlerClass(): string
    {
        return EventHandler::class;
    }
}
