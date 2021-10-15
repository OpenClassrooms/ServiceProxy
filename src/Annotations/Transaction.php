<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotations;

/**
 * @Annotation
 */
class Transaction implements ServiceProxyAnnotation
{
    public ?string $onConflictThrow = null;

    public function getOnConflictThrow(): ?string
    {
        return $this->onConflictThrow;
    }
}
