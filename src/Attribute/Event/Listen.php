<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute\Event;

use OpenClassrooms\ServiceProxy\Attribute\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Listen extends Attribute
{
    /**
     * @param array<string, string>|null $handler
     * @param array<string, string>|null $transport
     */
    public function __construct(
        public readonly string $name,
        ?array                $handler = null,
        ?array                $transport = null,
        public readonly int    $priority = 0,
    ) {
        $this->setHandlers(aliases: compact('handler', 'transport'));
        parent::__construct();
    }
}
