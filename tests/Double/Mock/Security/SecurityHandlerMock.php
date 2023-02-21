<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security;

use OpenClassrooms\ServiceProxy\Contract\SecurityHandler;

final class SecurityHandlerMock implements SecurityHandler
{
    /**
     * @var string[]
     */
    public array $authorized = ['ROLE_1', 'ROLE_2'];

    /**
     * @var string[]
     */
    public array $attributes;

    /**
     * @var mixed
     */
    public $param;

    public function getName(): string
    {
        return 'array';
    }

    public function checkAccess(array $attributes, $param = null): void
    {
        $this->attributes = $attributes;
        $this->param = $param;
        if (array_intersect($attributes, $this->authorized) === []) {
            throw new \RuntimeException('Not authorized');
        }
    }

    public function isDefault(): bool
    {
        return true;
    }
}