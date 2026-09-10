<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Config;

use OpenClassrooms\ServiceProxy\Model\Event;

final class EventInterceptorConfig
{
    /**
     * @param class-string<Event> $eventInstanceClassName
     */
    public function __construct(
        public readonly string $eventInstanceClassName = Event::class,
        public readonly ?string $mapperCacheDir = null,
    ) {
    }
}
