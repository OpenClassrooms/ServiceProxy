<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('openclassrooms_service_proxy');

        $rootNode = $treeBuilder->getRootNode();
        $children = $rootNode->children();

        $cacheDirNode = $children->scalarNode('cache_dir');
        $cacheDirNode->cannotBeEmpty();
        $cacheDirNode->defaultValue('%kernel.cache_dir%/openclassrooms_service_proxy')->end();

        $defaultHandlersNode = $children->arrayNode('default_handlers');
        $defaultHandlersNode->useAttributeAsKey('name')
                            ->arrayPrototype()
                            ->scalarPrototype()->end()
                            ->end();

        $defaultHandlersNode->defaultValue([
            'cache' => ['array'],
            'transaction' => ['doctrine_orm'],
            'event' => ['symfony_messenger', 'symfony_event_dispatcher'],
            'security' => ['symfony_authorization_checker'],
        ])->end();

        $productionEnvsNode = $children->arrayNode('production_environments');
        $productionEnvsNode->prototype('scalar')->end();
        $productionEnvsNode->defaultValue(['prod'])->end();

        $handlersNode = $children->arrayNode('handlers');
        $handlersNode->useAttributeAsKey('name')
                     ->arrayPrototype()
                     ->scalarPrototype()->end()
                     ->end();

        $children->end();

        return $treeBuilder;
    }
}
