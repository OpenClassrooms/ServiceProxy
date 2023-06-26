<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\ExpressionLanguage;

use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;

final class ExpressionResolver
{
    private ExpressionLanguage $expressionLanguage;

    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * @param mixed[] $parameters
     */
    public function resolve(string $expression, array $parameters): string
    {
        $resolvedExpression = $this->expressionLanguage->evaluate(
            $expression,
            $parameters
        );

        if (!\is_string($resolvedExpression)) {
            throw new \InvalidArgumentException(
                "Provided expression `{$expression}` did not resolve to a string."
            );
        }

        return $resolvedExpression;
    }
}
