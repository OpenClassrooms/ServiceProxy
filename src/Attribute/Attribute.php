<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

use OpenClassrooms\ServiceProxy\Annotation\Annotation;

abstract class Attribute extends Annotation
{
    public function __construct(
        ?string $handler = null
    ) {
        $this->handler = $handler;
        parent::__construct();
    }
}
