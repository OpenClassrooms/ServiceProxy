<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class InvalidateCache extends Attribute
{
    /**
     * @var array<int, string>
     */
    private array $tags = [];

    /**
     * @param array<int, string> $tags
     */
    public function __construct(
        array $tags,
        ?string $handler = null,
        ?string $pool = null
    ) {
        $this->handler = $handler;
        $this->tags = $tags;

        if ($pool !== null && $handler !== null && $handler !== $pool) {
            throw new \RuntimeException(
                'Argument \'pool\' is an alias for \'handler\'.
                You can only define one of the two arguments.'
            );
        }

        if ($pool !== null) {
            $this->handler = $pool;
        }

        parent::__construct();
    }

    /**
     * @return array<int, string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return class-string<AnnotationHandler>
     */
    public function getHandlerClass(): string
    {
        return CacheHandler::class;
    }
}
