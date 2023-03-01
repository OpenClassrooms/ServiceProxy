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

    /**
     * @throws AccessDeniedException
     */
    public function checkAccess(array $attributes, $param = null): void
    {
        foreach ($attributes as $attribute) {
            if ($this->authorizationChecker->isGranted($attribute, $param)) {
                return;
            }
        }

        throw new AccessDeniedException();
    }
}
