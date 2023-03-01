<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\DependencyInjection;

use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\Interceptable;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
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

        $container->registerForAutoconfiguration(PrefixInterceptor::class)
            ->addTag('openclassrooms.service_proxy.prefix_interceptor')
        ;

        $container->registerForAutoconfiguration(SuffixInterceptor::class)
            ->addTag('openclassrooms.service_proxy.suffix_interceptor')
        ;

        $container->registerForAutoconfiguration(AbstractInterceptor::class)
            ->addMethodCall(
                'setHandlers',
                [tagged_iterator('openclassrooms.service_proxy.annotation_handler')]
            )
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
    }
}
