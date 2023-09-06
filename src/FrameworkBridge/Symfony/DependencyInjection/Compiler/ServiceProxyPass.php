<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection\Compiler;

use OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Subscriber\ServiceProxySubscriber;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use Symfony\Component\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ServiceProxyPass implements CompilerPassInterface
{
    private Compiler $compiler;

    private ContainerBuilder $container;

    public function process(ContainerBuilder $container): void
    {
        $this->container = $container;
        $this->compiler = $container->getCompiler();

        $this->buildServiceProxies();
    }

    private function buildServiceProxies(): void
    {
        $serviceProxyIds = [];
        $taggedServices = $this->container->findTaggedServiceIds('openclassrooms.service_proxy');
        $locateableServices = [];
        foreach ($taggedServices as $taggedServiceId => $tagParameters) {
            $this->buildServiceProxyFactoryDefinition($taggedServiceId);
            $serviceProxyIds[] = $taggedServiceId;
            $locateableServices[$taggedServiceId] = new Reference($taggedServiceId);
            $this->compiler->log($this, "Add proxy for {$taggedServiceId} service.");
        }

        $subscriber = new Definition(ServiceProxySubscriber::class);
        $subscriber->addTag('kernel.event_subscriber');
        $subscriber->addArgument(ServiceLocatorTagPass::register($this->container, $locateableServices));
        $subscriber->addArgument($serviceProxyIds);
        $startUpInterceptors = $this->container->findTaggedServiceIds(
            'openclassrooms.service_proxy.start_up_interceptor'
        );
        foreach ($startUpInterceptors as $id => $tags) {
            $subscriber->addMethodCall('addStartUpInterceptor', [new Reference($id)]);
        }
        $this->container->addDefinitions([ServiceProxySubscriber::class => $subscriber]);

        $this->container->setParameter('openclassrooms.service_proxy.service_proxy_ids', $serviceProxyIds);
    }

    private function buildServiceProxyFactoryDefinition(string $taggedServiceName): void
    {
        $definition = $this->container->findDefinition($taggedServiceName);
        $factoryDefinition = new Definition($definition->getClass());
        $factoryDefinition->setFactory([new Reference(ProxyFactory::class), 'createProxy']);
        $factoryDefinition->setArguments([$definition]);
        $this->container->setDefinition($taggedServiceName, $factoryDefinition);
        $factoryDefinition->setPublic($definition->isPublic());
        $factoryDefinition->setLazy($definition->isLazy());
        $factoryDefinition->setTags($definition->getTags());
    }
}
