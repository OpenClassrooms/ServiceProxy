<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

enum EventMethodEnum
{
    case post;
    case pre;
    case onException;

    /**
     * @param array<string> $methods
     */
    public static function exists(array $methods): bool
    {
        $diff = array_diff($methods, self::caseNames());

        return \count($diff) > 0;
    }

    /**
     * @return array<string>
     */
    public static function caseNames(): array
    {
        return array_column(self::cases(), 'name');
    }
}
