<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security;

use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;

final class SecurityHandlerMock implements SecurityHandler
{
    /**
     * @var string[]
     */
    public array $authorized = ['ROLE_1', 'ROLE_2', 'ROLE_SECURITY_ANNOTATED_CLASS_MISSING_ROLES'];

    /**
     * @var string[]
     */
    public array $attributes = [];

    public mixed $param;

    public function getName(): string
    {
        return 'array';
    }

    public function checkAccess(string $attribute, mixed $subject = null): bool
    {
        $this->attributes = [...$this->attributes, $attribute];
        $this->param = $subject;

        return array_intersect($this->attributes, $this->authorized) !== [];
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function getAccessDeniedException(?string $message = null): \RuntimeException
    {
        if ($message === null) {
            $message = 'Access Denied.';
        }
        return new \RuntimeException($message);
    }

    public function setDefaultHandlers(array $defaultHandlers): void
    {
    }
}
