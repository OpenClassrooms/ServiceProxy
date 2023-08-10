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

    public function checkAccess(array $attributes, mixed $subject = null): bool
    {
        $this->attributes = [...$this->attributes, ...$attributes];
        $this->param = $subject;

        return array_intersect($attributes, $this->authorized) !== [];
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function getAccessDeniedException(): \RuntimeException
    {
        return new \RuntimeException();
    }
}
