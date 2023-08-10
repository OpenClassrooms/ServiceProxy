<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Security;

use OpenClassrooms\ServiceProxy\Attribute\Security;

class SecurityAnnotatedClass
{
    #[Security("is_granted('ROLE_1', useCaseRequest.field)")]
    public function fieldRoleSecurity(mixed $useCaseRequest): void
    {
    }

    #[Security("is_granted('ROLE_1', param2.field2.value2)")]
    public function fieldRoleSecurityWithMultipleParams(mixed $param1, mixed $param2): void
    {
    }

    #[Security("is_granted('ROLE_1') or is_granted('ROLE_2')")]
    public function orRoles(): void
    {
    }

    #[Security("is_granted('ROLE_1') and is_granted('ROLE_2')")]
    public function andRoles(): void
    {
    }

    #[Security]
    public function missingRoles(): void
    {
    }

    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    #[Security("is_granted('ROLE_1')")]
    public function oneRole(mixed $useCaseRequest): void
    {
    }

    #[Security("is_granted('ROLE_NOT_AUTHORIZED')")]
    public function nonAuthorizedRole(mixed $useCaseRequest): void
    {
    }
}
