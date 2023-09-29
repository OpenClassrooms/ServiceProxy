<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface SecurityHandler extends AttributeHandler
{
    /**
     * @throws \Exception
     */
    public function checkAccess(string $attribute, mixed $subject = null): bool;

    public function getAccessDeniedException(?string $message = null): \Exception;
}
