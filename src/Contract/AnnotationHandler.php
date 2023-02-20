<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Contract;

interface AnnotationHandler
{
    public function getName(): string;

    public function isDefault(): bool;
}
