<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;

interface EventHandler extends AnnotationHandler
{
    public function dispatch(Event $event, ?string $queue = null): void;

    public function listen(Instance $instance, string $name, int $priority = 0): void;
}
