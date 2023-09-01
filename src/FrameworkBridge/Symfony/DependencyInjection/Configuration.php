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
                'cache' => 'array',
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
            ->arrayNode('handlers')
            ->children()
            ->arrayNode('symfony_http')
            ->children()
            ->scalarNode('esb.endpoint')
            ->isRequired()
            ->cannotBeEmpty()
            ->defaultValue('%env(resolve:ESB_ENDPOINT)%')
            ->end()
            ->children()
            ->scalarNode('esb.api_key')
            ->isRequired()
            ->cannotBeEmpty()
            ->defaultValue('%env(resolve:ESB_API_KEY)%')
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
