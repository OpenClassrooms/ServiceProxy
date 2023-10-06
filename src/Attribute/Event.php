<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

use OpenClassrooms\ServiceProxy\Handler\Contract\AttributeHandler;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Event extends Attribute
{
    public const ON_EXCEPTION_METHOD = 'onException';

    public const POST_METHOD = 'post';

    public const PRE_METHOD = 'pre';

    private const ALLOWED_METHODS = [
        self::PRE_METHOD,
        self::POST_METHOD,
        self::ON_EXCEPTION_METHOD,
    ];

    private const DEFAULT_METHOD = self::POST_METHOD;

    private const DEFAULT_PREFIX = 'use_case';

    /**
     * @var string|array<string, 'pre'|'post'|'onException'>
     */
    private string|array $methods;

    /**
     * @param string|array<string, 'pre'|'post'|'onException'> $methods
     */
    public function __construct(
        string|array $methods = [self::DEFAULT_METHOD],
        public readonly ?string $name = null,
        public readonly ?string $defaultPrefix = self::DEFAULT_PREFIX,
        public readonly bool $useClassNameOnly = true,
    ) {
        parent::__construct();

        $this->setMethods($methods);
    }

    public function hasMethod(string $method): bool
    {
        return \in_array($method, $this->methods, true);
    }

    /**
     * @return class-string<AttributeHandler>
     */
    public function getHandlerClass(): string
    {
        return EventHandler::class;
    }

    /**
     * @param string|array<string, 'pre'|'post'|'onException'> $methods
     */
    private function setMethods(string|array $methods): void
    {
        if (\is_string($methods)) {
            $methods = array_map('trim', explode(',', $methods));
        }

        $diff = array_diff($methods, self::ALLOWED_METHODS);
        if (\count($diff) > 0) {
            throw new \InvalidArgumentException(
                'Method "'
                . implode(',', $diff) . '" is not allowed. Allowed: '
                . implode(',', self::ALLOWED_METHODS)
            );
        }

        $this->methods = $methods;
    }
}
