<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Security;

use OpenClassrooms\ServiceProxy\Handler\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
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

    public function checkAccess(string $attribute, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    public function getAccessDeniedException(?string $message = null): AccessDeniedException
    {
        if ($message === null) {
            return new AccessDeniedException();
        }

        return new AccessDeniedException($message);
    }
}
