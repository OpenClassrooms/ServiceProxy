<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     * @noinspection NullPointerExceptionInspection
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('openclassrooms_service_proxy');

        $treeBuilder
            ->getRootNode()
            ->children()
            ->scalarNode('cache_dir')
            ->cannotBeEmpty()
            ->defaultValue('%kernel.cache_dir%/openclassrooms_service_proxy')
            ->end()
            ->arrayNode('default_handlers')
            ->prototype('scalar')
            ->end()
            ->defaultValue([
                'cache' => 'request_scope',
                'transaction' => 'doctrine_orm',
                'event' => 'symfony_event_dispatcher',
                'security' => 'symfony_authorization_checker',
            ])
            ->end()
            ->arrayNode('production_environments')
            ->prototype('scalar')
            ->end()
            ->defaultValue(['prod'])
            ->end()
            ->end();

        return $treeBuilder;
    }
}
