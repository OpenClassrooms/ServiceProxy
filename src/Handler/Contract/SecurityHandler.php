<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface SecurityHandler extends AnnotationHandler
{
    /**
     * @param string[] $attributes
     *
     * @throws \Exception
     */
    public function checkAccess(array $attributes, mixed $subject = null): bool;

    public function getAccessDeniedException(?string $message = null): \Exception;
}
