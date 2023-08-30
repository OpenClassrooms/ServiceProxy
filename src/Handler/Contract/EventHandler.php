<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

use OpenClassrooms\ServiceProxy\Model\Message\Message;

interface EventHandler extends AnnotationHandler
{
    public function dispatch(Message $message): void;
}
