<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection;

use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\Interceptable;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Invoker\Contract\MethodInvoker;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class OpenClassroomsServiceProxyExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->processConfiguration(new Configuration(), $configs);
        $this->setParameters($configs, $container);

        $container->registerForAutoconfiguration(AnnotationHandler::class)
            ->addTag('openclassrooms.service_proxy.annotation_handler')
            ->addMethodCall(
                'setDefaultHandlers',
                ['%openclassrooms.service_proxy.handler.defaults%']
            )
        ;

        $container->registerForAutoconfiguration(EventHandler::class)
            ->addTag('openclassrooms.service_proxy.event_handler')
        ;

        $container->registerForAutoconfiguration(PrefixInterceptor::class)
            ->addTag('openclassrooms.service_proxy.prefix_interceptor')
        ;

        $container->registerForAutoconfiguration(SuffixInterceptor::class)
            ->addTag('openclassrooms.service_proxy.suffix_interceptor')
        ;

        $container->registerForAutoconfiguration(StartUpInterceptor::class)
            ->addTag('openclassrooms.service_proxy.start_up_interceptor')
        ;

        $container->registerForAutoconfiguration(AbstractInterceptor::class)
            ->addMethodCall(
                'setHandlers',
                [tagged_iterator('openclassrooms.service_proxy.annotation_handler')]
            )
        ;

        $container->registerForAutoconfiguration(MethodInvoker::class)
            ->addTag('openclassrooms.service_proxy.method_invoker')
        ;

        $container->registerForAutoconfiguration(Interceptable::class)
            ->addTag('openclassrooms.service_proxy')
        ;

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(\dirname(__DIR__) . '/Config/')
        );
        $loader->load('services.php');
    }

    public function getAlias(): string
    {
        return 'openclassrooms_service_proxy';
    }

    /**
     * @param array<string, mixed> $config
     */
    private function setParameters(array $config, ContainerBuilder $container): void
    {
        $container->setParameter(
            'openclassrooms.service_proxy.cache_dir',
            $container->getParameterBag()
                ->resolveValue($config['cache_dir'])
        );
        $container->setParameter(
            'openclassrooms.service_proxy.eval',
            !\in_array(
                $container->getParameter('kernel.environment'),
                (array) $config['production_environments'],
                true
            )
        );
        $container->setParameter(
            'openclassrooms.service_proxy.handler.defaults',
            (array) $config['default_handlers']
        );

        $container->setParameter('openclassrooms.service_proxy.handlers', $config['handlers']);

        $this->autoConfigure($config, $container);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function autoConfigure(array $config, ContainerBuilder $container): void
    {
        $types = ['handler', 'interceptor'];
        foreach ($types as $type) {
            $typeKey = $type . 's';
            /** @var array<string, array<string, mixed>> $elements */
            $elements = $config[$typeKey] ?? [];
            foreach ($elements as $name => $element) {
                $domain = $this->getElementType($name);
                $args = $this->prefixKeys($element);
                $configClass = $this->getConfigClass($name, $type, $domain);
                $container->register($configClass)
                    ->setArguments($args)
                ;
            }
        }
    }

    private function getElementType(string $name): ?string
    {
        $parts = explode('_', (string) preg_replace('/(?<!^)[A-Z]/', '_$0', $name));

        return $parts[\count($parts) - 2] ?? null;
    }

    private function getConfigClass(string $name, string $type, ?string $domain = null): string
    {
        $type = ucfirst($type);
        if ($domain !== null) {
            $domain = "{$domain}\\";
        }

        $configClasses = [
            "OpenClassrooms\\ServiceProxy\\{$type}\\Config\\{$domain}{$name}Config",
            "OpenClassrooms\\ServiceProxy\\{$type}\\Config\\{$name}Config",
        ];

        foreach ($configClasses as $configClass) {
            if (class_exists($configClass)) {
                return $configClass;
            }
        }

        throw new \InvalidArgumentException(
            "The '{$type}' config class for '{$name}' does not exist."
        );
    }

    /**
     * @param array<string, mixed> $element
     * @return array<string, mixed>
     */
    private function prefixKeys(array $element): array
    {
        $args = [];
        foreach ($element as $key => $value) {
            $args['$' . $key] = $value;
        }

        return $args;
    }
}
