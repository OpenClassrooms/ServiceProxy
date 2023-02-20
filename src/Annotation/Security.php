<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

use OpenClassrooms\ServiceProxy\Contract\SecurityHandler;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Security extends Annotation
{
    private ?string $checkField = null;

    private bool $checkRequest = false;

    private array $roles;

    public function checkRequest(): bool
    {
        return $this->checkRequest;
    }

    public function getCheckField(): ?string
    {
        return $this->checkField;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setCheckField(string $checkField): void
    {
        $this->checkField = $checkField;
    }

    public function setCheckRequest($checkRequest): void
    {
        $this->checkRequest = $checkRequest;
    }

    /**
     * @param string|array $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = is_array($roles)
            ? $roles
            : array_map('trim', explode(',', $roles));
    }

    public function getHandlerClass(): string
    {
        return SecurityHandler::class;
    }
}
