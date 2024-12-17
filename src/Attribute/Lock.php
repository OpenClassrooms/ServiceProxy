<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Lock extends Attribute
{
    /**
     * @param array<string>|string|null       $handlers
     */
    public function __construct(
        public readonly string $key,
        array|string|null $handlers = null,
    ) {
        parent::__construct();
        $this->setHandlers($handlers);
    }
}
