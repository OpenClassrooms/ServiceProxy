<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute\Event;

use OpenClassrooms\ServiceProxy\Attribute\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Listen extends Attribute
{
}
