<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute\Event;

enum On
{
    case PRE;

    case POST;

    case EXCEPTION;
}
