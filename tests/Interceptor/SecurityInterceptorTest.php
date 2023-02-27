<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\SecurityInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security\SecurityHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Security\SecurityAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class SecurityInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private SecurityHandler $handler;

    private SecurityAnnotatedClass $proxy;

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

    public function testOnlyRoleNotAuthorizedThrowException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->proxy->nonAuthorizedRole(1);
    }

    public function testOnlyAuthorizedRoleDonTThrowException(): void
    {
        $this->proxy->oneRole(1);
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    public function testManyRolesDonTThrowException(): void
    {
        $this->proxy->manyRoles('whatever');
        $this->assertSame(['ROLE_1', 'ROLE_2'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }

    public function testRequestCheckAccessOnRequest(): void
    {
        $this->proxy->checkRequestRoleSecurity(1);
        $this->assertSame(['ROLE_1'], $this->handler->attributes);
        $this->assertSame(1, $this->handler->param);
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

    public function testMultipleSecurityAnnotationsDeny(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->proxy->multipleSecurityAnnotationNotAuthorized();
    }

    public function testMultipleSecurityAnnotationsAuthorize(): void
    {
        $this->proxy->multipleSecurityAnnotation();
        $this->assertSame(['ROLE_2'], $this->handler->attributes);
        $this->assertNull($this->handler->param);
    }
}
