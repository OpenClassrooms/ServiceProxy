<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Attribute\Event\Listen;
use OpenClassrooms\ServiceProxy\Model\Request\Method;
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
        $this->addStartupListeners();
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
    }

    private function addStartupListeners(): void
    {
        if (!$this->container->findDefinition('event_dispatcher')) {
            return;
        }

        $eventDispatcherDefinition = $this->container->findDefinition('event_dispatcher');
        $proxies = $this->container->findTaggedServiceIds('openclassrooms.service_proxy');

        foreach (\array_keys($proxies) as $proxy) {
            $definition = $this->container->findDefinition($proxy);
            $class = $definition->getClass();
            $instanceRef = new \ReflectionClass($class);
            $methods = $instanceRef->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $methodRef) {

                try {
                    $methodAnnotations = (new AnnotationReader())->getMethodAnnotations($methodRef);
                } catch (AnnotationException) {
                    continue;
                }

                $method = Method::create(
                    $methodRef,
                    $methodAnnotations,
                );

                if ($method->hasAttribute(Listen::class)) {
                    $attributes = $method->getAttributesInstances(Listen::class);

                    foreach ($attributes as $attribute) {
                        /**
                         * @var Listen $attribute
                         */
                        $name = $attribute->name;
                        $transport = $attribute->transport;

                        if ($transport !== null) {
                            $name .= '.' . $transport->value;
                        }

                        $eventDispatcherDefinition->addMethodCall('addListener', [
                            $name,
                            [new Reference($proxy), $method->getName()],
                            $attribute->priority
                        ]);
                    }

                }
            }
        }
    }
}
