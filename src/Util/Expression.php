<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Util;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Expression
{
    private static ExpressionLanguage $expressionLanguage;

    /**
     * @param mixed[] $values
     */
    public static function evaluate(string $expression, array $values = []): mixed
    {
        self::$expressionLanguage = self::$expressionLanguage ?? new ExpressionLanguage();

        return self::$expressionLanguage->evaluate($expression, $values);
    }

    /**
     * @param mixed[] $values
     */
    public static function evaluateToString(string $expression, array $values = []): string
    {
        self::$expressionLanguage = self::$expressionLanguage ?? new ExpressionLanguage();

        $resolvedExpression = self::$expressionLanguage->evaluate(
            $expression,
            $values
        );

        if (!\is_string($resolvedExpression)) {
            throw new \InvalidArgumentException(
                "Provided expression `{$expression}` did not resolve to a string."
            );
        }

        return $resolvedExpression;
    }
}
