<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

use OpenClassrooms\ServiceProxy\Attribute\Event\On;
use Webmozart\Assert\Assert;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Event extends Attribute
{
    /**
     * @param array<On>          $dispatch
     * @param array<string>|string|null $handler
     * @param array<string>|string|null $transport
     */
    public function __construct(
        array|string|null                 $handler = null,
        array|string|null                 $transport = null,
        public readonly ?string $name = null,
        public readonly ?string  $queue = null,
        public readonly array   $dispatch = [On::POST],
    ) {
        parent::__construct();
        Assert::allIsInstanceOf($dispatch, On::class);
        $this->setHandlers(aliases: compact('handler', 'transport'));
    }

    public function isOnException(): bool
    {
        return \in_array(On::EXCEPTION, $this->dispatch, true);
    }

    public function isPost(): bool
    {
        return \in_array(On::POST, $this->dispatch, true);
    }

    public function isPre(): bool
    {
        return \in_array(On::PRE, $this->dispatch, true);
    }
}
