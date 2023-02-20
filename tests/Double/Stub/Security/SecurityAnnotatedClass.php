<?php

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Security;

use OpenClassrooms\ServiceProxy\Annotations\Event;
use OpenClassrooms\ServiceProxy\Annotations\Security;

class SecurityAnnotatedClass
{

    /**
     * @Event(methods="post", name="first_event")
     * @Event(methods="post", name="first_event")
     */
    public function duplicatedEvent(): int
    {
        return 1;
    }

    /**
     * @Security(roles="ROLE_1", checkField="field")
     */
    public function fieldRoleSecurity($useCaseRequest): void
    {
    }

    /**
     * @Security(roles="ROLE_1", checkField="param2.field2.value2")
     */
    public function fieldRoleSecurityWithMultipleParams($param1, $param2): void
    {
    }

    /**
     * @Security(roles="ROLE_1, ROLE_2")
     */
    public function manyRoles($useCaseRequest): void
    {
    }

    /**
     * @Security()
     */
    public function missingRoles(): int
    {
        return 1;
    }

    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    /**
     * @Security(roles="ROLE_1")
     */
    public function oneRole($useCaseRequest): void
    {
    }

    /**
     * @Security(roles="ROLE_NOT_AUTHORIZED")
     */
    public function nonAuthorizedRole($useCaseRequest): void
    {
    }

    /**
     * @Security(roles="ROLE_1", checkRequest=true)
     */
    public function checkRequestRoleSecurity($useCaseRequest): void
    {
    }

    /**
     * @Security(roles="ROLE_1")
     * @Security(roles="ROLE_2")
     */
    public function multipleSecurityAnnotation(): void
    {
    }

    /**
     * @Security(roles="ROLE_1")
     * @Security(roles="ROLE_2")
     * @Security(roles="ROLE_NOT_AUTHORIZED")
     */
    public function multipleSecurityAnnotationNotAuthorized(): void
    {
    }
}
