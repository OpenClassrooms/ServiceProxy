<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Security;

use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class SymfonySecurityHandler implements SecurityHandler
{
    use ConfigurableHandler;

    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getName(): string
    {
        return 'symfony_authorization_checker';
    }

    public function checkAccess(array $attributes, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attributes, $subject);
    }

    public function getAccessDeniedException(): AccessDeniedException
    {
        return new AccessDeniedException();
    }
}
