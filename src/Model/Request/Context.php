<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model\Request;

use OpenClassrooms\ServiceProxy\Attribute\Attribute;

final class Context
{
    /**
     * @template T of Attribute
     * @param T $attribute
     */
    public function __construct(
        public readonly ContextType $type,
        public readonly Attribute $attribute,
    ) {
    }
}
