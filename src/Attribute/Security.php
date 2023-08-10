<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Security extends Attribute
{
    public function __construct(
        public readonly ?string $expression = null,
        public ?string $handler = null,
    ) {
        parent::__construct();
    }

    /**
     * @return class-string<AnnotationHandler>
     */
    public function getHandlerClass(): string
    {
        return SecurityHandler::class;
    }
}
