<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Security;
use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Config\SecurityInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class SecurityInterceptor extends AbstractInterceptor implements PrefixInterceptor
{
    private readonly LoggerInterface $logger;

    public function __construct(
        iterable                                    $handlers = [],
        private readonly ?SecurityInterceptorConfig $config = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($handlers);
        $this->logger = $logger ?? new NullLogger();
    }

    public function getPrefixPriority(): int
    {
        return 30;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function prefix(Instance $instance): Response
    {
        $attribute = $instance->getMethod()
            ->getAttribute(Security::class)
        ;
        $parameters = $instance->getMethod()
            ->getParameters()
        ;

        if ($this->config?->bypassSecurity) {
            $this->logger->error('Security is bypassed.');
            return new Response();
        }

        $rolesExpressions = null;
        if ($attribute->roles !== null) {
            if ($attribute->expression !== null) {
                throw new \RuntimeException('You cannot use both roles and expression in the Security attribute.');
            }
            $rolesExpressions = array_map(
                static fn (string $role) => "is_granted('{$role}')",
                $attribute->roles
            );
        }

        $expression = $attribute->expression;
        if ($expression === null) {
            if ($rolesExpressions !== null) {
                $expression = implode(' or ', $rolesExpressions);
            } else {
                $role = $this->guessRoleName($instance);
                $expression = "is_granted('{$role}')";
            }
        }
        $handlers = $this->getHandlers(SecurityHandler::class, $attribute);
        foreach ($handlers as $handler) {
            $this->resolveExpression(
                $handler,
                $expression,
                $parameters,
                $attribute
            );
        }

        return new Response();
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAttribute(Security::class)
        ;
    }

    private function guessRoleName(Instance $instance): string
    {
        $className = $instance->getReflection()
            ->getShortName()
        ;
        $methodName = $instance->getMethod()
            ->getName()
        ;

        $className = $this->camelCaseToSnakeCase($className);
        $methodName = $this->camelCaseToSnakeCase($methodName);

        $role = 'ROLE_' . $className;
        if (!\in_array($methodName, ['__CONSTRUCT', '__INVOKE', 'EXECUTE'], true)) {
            $role .= '_' . $methodName;
        }

        return $role;
    }

    private function camelCaseToSnakeCase(string $string): string
    {
        return mb_strtoupper((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    /**
     * @param mixed[] $parameters
     *
     * @throws \Exception
     */
    private function resolveExpression(
        SecurityHandler $handler,
        string          $expression,
        array           $parameters,
        Security        $attribute,
    ): void {
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->register(
            'is_granted',
            static fn ($attributes, string $object = 'null') => sprintf(
                'return $handler->checkAccess(%s, %s)',
                $attributes,
                $object
            ),
            static fn (array $variables, $attributes, $object = null) => $handler->checkAccess(
                $attributes,
                $object
            )
        );

        /** @var bool $authorized */
        $authorized = $expressionLanguage->evaluate($expression, $parameters);
        if (!$authorized) {
            if ($attribute->exception !== null) {
                $exception = $attribute->exception;
                throw new $exception($attribute->message);
            }

            throw $handler->getAccessDeniedException($attribute->message);
        }
    }
}
