<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\SecurityInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security\SecurityHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Security\SecurityAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class SecurityInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private SecurityHandler $handler;

    private SecurityAnnotatedClass $proxy;

    private ProxyFactory $proxyFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new SecurityHandlerMock();
        $this->proxyFactory = $this->getProxyFactory(
            [
                new SecurityInterceptor(
                    [$this->handler],
                ),
            ]
        );
        $this->proxy = $this->proxyFactory->createInstance(SecurityAnnotatedClass::class);
    }

    public function testOnlyRoleNotAuthorizedThrowException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->proxy->nonAuthorizedRole(1);
    }

    public function testOnlyAuthorizedRoleAuthorize(): void
    {
        $this->proxy->oneRole(1);
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    public function testOrRolesAuthorize(): void
    {
        $this->proxy->orRoles();
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    public function testAndRolesAuthorize(): void
    {
        $this->proxy->andRoles();
        $this->assertSame(['ROLE_1', 'ROLE_2'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    public function testFieldCheckAccessOnField(): void
    {
        $this->proxy->fieldRoleSecurity(
            (object) [
                'field' => 1,
            ]
        );
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertSame(1, $this->handler->param);
    }

    public function testFieldOnMultipleParamsCheckAccessOnField(): void
    {
        $this->proxy->fieldRoleSecurityWithMultipleParams(
            (object) [
                'field' => (object) [
                    'value1' => 'result1',
                ],
            ],
            (object) [
                'field2' => (object) [
                    'value2' => 'result2',
                ],
            ]
        );
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertSame('result2', $this->handler->param);
    }

    public function testMissingRole(): void
    {
        $this->proxy->missingRoles();
        $this->assertSame(['ROLE_SECURITY_ANNOTATED_CLASS_MISSING_ROLES'], $this->handler->attributes);
    }

    public function testAccessDeniedWithMessage(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You are not allowed.');
        $this->proxy->accessDeniedWithMessage();
    }

    public function testAccessDeniedWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument.');
        $this->proxy->accessDeniedWithException();
    }
}
