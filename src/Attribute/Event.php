<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

use OpenClassrooms\ServiceProxy\Handler\Contract\AttributeHandler;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Event extends Attribute
{
    /**
     * @var string|array<string, 'pre'|'post'|'onException'>
     */
    private string|array $methods;

    /**
     * @param string|array<string, 'pre'|'post'|'onException'> $methods
     */
    public function __construct(
        string|array $methods = ['post'],
        public readonly ?string $name = null,
        public readonly ?string $defaultPrefix = self::DEFAULT_PREFIX,
        public readonly bool $useClassNameOnly = true,
    ) {
        parent::__construct();

        $this->setMethods($methods);
    }

    public function hasMethod(EventMethodEnum $method): bool
    {
        return \in_array($method->name, $this->methods, true);
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

        if (EventMethodEnum::exists($methods)) {
            throw new \InvalidArgumentException(
                'Method "'
                . implode(',', $methods) . '" is not allowed. Allowed: '
                . implode(',', EventMethodEnum::caseNames())
            );
        }

        $this->methods = $methods;
    }
}
