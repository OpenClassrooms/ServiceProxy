<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection\Compiler;

use OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Subscriber\ServiceProxySubscriber;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use Symfony\Component\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
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
        foreach ($taggedServices as $taggedServiceId => $tagParameters) {
            $this->buildServiceProxyFactoryDefinition($taggedServiceId);
            $serviceProxyIds[] = $taggedServiceId;
            $this->compiler->log($this, "Add proxy for {$taggedServiceId} service.");
        }

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

        $proxyRef = new \ReflectionClass($definition->getClass());
        $proxyMethodsRef = $proxyRef->getMethods(\ReflectionMethod::IS_PUBLIC);
        $instances = [];
        foreach ($proxyMethodsRef as $proxyMethodRef) {
            $instance = new Definition(Instance::class);
            $instance->setFactory([new Reference(Instance::class), 'createFromProxy']);
            $instance->setArguments([
                new Reference($taggedServiceName),
                $proxyMethodRef->getName(),
            ]);
            $instances[] = $instance;
        }

        $this->container->getDefinition(ServiceProxySubscriber::class)->addMethodCall('setInstances', $instances);
    }
}
