<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Interceptor;

use OpenClassrooms\ServiceProxy\Attribute\Security;
use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class SecurityInterceptor extends AbstractInterceptor implements PrefixInterceptor
{
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
        $expression = $attribute->expression;
        if ($expression === null) {
            $role = $this->guessRoleName($instance);
            $expression = "is_granted(['{$role}'])";
        }
        $handler = $this->getHandler(SecurityHandler::class, $attribute);
        $this->resolveExpression($handler, $expression, $parameters);

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
                (array) $attributes,
                $object
            )
        );

        /** @var bool $authorized */
        $authorized = $expressionLanguage->evaluate($expression, $parameters);
        if (!$authorized) {
            throw $handler->getAccessDeniedException();
        }
    }
}
