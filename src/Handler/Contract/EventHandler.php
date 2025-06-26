<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

use OpenClassrooms\ServiceProxy\Attribute\Event\Transport;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;

interface EventHandler extends AnnotationHandler
{
    public function dispatch(object $event, ?string $queue = null): void;

    public function listen(Instance $instance, string $name, Transport $transport = null, int $priority = 0): void;
}
