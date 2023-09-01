<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Event extends Attribute
{
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

    /**
     * @param array<int, 'pre'|'post'|'onException'> $methods
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $topic = null,
        public readonly array $methods = [self::POST_METHOD],
        ?string $handler = null,
    ) {
        foreach ($this->methods as $method) {
            if (!\in_array($method, self::$allowedMethods, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid method "%s" for event annotation. Allowed methods are "%s".',
                        $method,
                        implode('", "', self::$allowedMethods)
                    )
                );
            }
        }
        parent::__construct($handler);
    }

    public function isPre(): bool
    {
        return \in_array(self::PRE_METHOD, $this->methods, true);
    }

    public function isPost(): bool
    {
        return \in_array(self::POST_METHOD, $this->methods, true);
    }

    public function isOnException(): bool
    {
        return \in_array(self::ON_EXCEPTION_METHOD, $this->methods, true);
    }
}
