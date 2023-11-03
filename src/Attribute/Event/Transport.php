<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute\Event;

enum Transport: string
{
    case SYNC = 'sync';
    case ASYNC = 'async';
}
