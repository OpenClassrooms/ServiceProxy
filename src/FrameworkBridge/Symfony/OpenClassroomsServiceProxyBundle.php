<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony;

use OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection\Compiler\CachePoolPass;
use OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection\Compiler\ServiceProxyPass;
use OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection\OpenClassroomsServiceProxyExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpenClassroomsServiceProxyBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new OpenClassroomsServiceProxyExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ServiceProxyPass(), PassConfig::TYPE_AFTER_REMOVING);

        if (class_exists('Symfony\Component\Cache\Adapter\TagAwareAdapter')) {
            $container->addCompilerPass(new CachePoolPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        }
    }
}
