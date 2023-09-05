<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;

interface EventHandler extends AnnotationHandler
{
    public function dispatch(Instance $instance): void;

    public function listen(Instance $instance): void;
}
