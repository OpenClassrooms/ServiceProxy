<?php

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Interceptor\SecurityInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security\SecurityHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Security\SecurityAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

class SecurityInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private SecurityHandler $handler;

    private SecurityAnnotatedClass $proxy;

    /**
     * @test
     */
    public function OnlyRoleNotAuthorized_ThrowException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->proxy->nonAuthorizedRole(1);
    }

    /**
     * @test
     */
    public function OnlyAuthorizedRole_DonTThrowException(): void
    {
        $this->proxy->oneRole(1);
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    /**
     * @test
     */
    public function ManyRoles_DonTThrowException(): void
    {
        $this->proxy->manyRoles('whatever');
        $this->assertSame(['ROLE_1', 'ROLE_2'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    /**
     * @test
     */
    public function Request_CheckAccessOnRequest(): void
    {
        $this->proxy->checkRequestRoleSecurity(1);
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertSame(1, $this->handler->param);
    }

    /**
     * @test
     */
    public function Field_CheckAccessOnField(): void
    {
        $this->proxy->fieldRoleSecurity(
            (object) [
                'field' => 1,
            ]
        );
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertSame(1, $this->handler->param);
    }

    /**
     * @test
     */
    public function FieldOnMultipleParams_CheckAccessOnField(): void
    {
        $this->proxy->fieldRoleSecurityWithMultipleParams(
            [
                'field' => [
                    'value1' => 'result1',
                ],
            ],
            [
                'field2' => [
                    'value2' => 'result2',
                ],
            ]
        );
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertSame('result2', $this->handler->param);
    }

    /**
     * @test
     */
    public function MultipleSecurityAnnotations_Deny(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->proxy->multipleSecurityAnnotationNotAuthorized();
    }

    /**
     * @test
     */
    public function MultipleSecurityAnnotations_Authorize(): void
    {
        $this->proxy->multipleSecurityAnnotation();
        $this->assertSame(['ROLE_2'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    protected function setUp(): void
    {
        $this->handler = new SecurityHandlerMock();
        $this->proxyFactory = $this->getProxyFactory(
            [
                new SecurityInterceptor(
                    [$this->handler],
                ),
            ]
        );
        $this->proxy = $this->proxyFactory->createProxy(new SecurityAnnotatedClass());
    }
}
