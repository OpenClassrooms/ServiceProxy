<?php

declare(strict_types=1);

use OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Messenger\Transport\Serialization\MessageSerializer;
use OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Subscriber\ServiceProxySubscriber;
use OpenClassrooms\ServiceProxy\Invoker\Impl\AggregateMethodInvoker;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\ProxyFactoryConfiguration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->load(
        'OpenClassrooms\\ServiceProxy\\Handler\\Impl\\',
        \dirname(__DIR__, 4) . '/src/Handler/Impl/*'
    );

    $services->load(
        'OpenClassrooms\\ServiceProxy\\Interceptor\\Impl\\',
        \dirname(__DIR__, 4) . '/src/Interceptor/Impl/*'
    );

    $services->load(
        'OpenClassrooms\\ServiceProxy\\Invoker\\Impl\\',
        \dirname(__DIR__, 4) . '/src/Invoker/Impl/*'
    );

    $services->set(ProxyFactory::class)
        ->public()
        ->args([
            inline_service(ProxyFactoryConfiguration::class)
                ->args([
                    '$proxiesDir' => param('openclassrooms.service_proxy.cache_dir'),
                    '$eval' => param('openclassrooms.service_proxy.eval'),
                ]),
            tagged_iterator('openclassrooms.service_proxy.prefix_interceptor'),
            tagged_iterator('openclassrooms.service_proxy.suffix_interceptor'),
        ])
    ;

    $services->set(AggregateMethodInvoker::class)
        ->args([
            tagged_iterator('openclassrooms.service_proxy.method_invoker'),
        ])
    ;

    $services->set(MessageSerializer::class)
        ->autowire()
        ->autoconfigure()
        ;

    $services->set(ServiceProxySubscriber::class)
        ->public()
        ->args([
            tagged_iterator('openclassrooms.service_proxy'),
            tagged_iterator('openclassrooms.service_proxy.start_up_interceptor'),
        ])
        ->tag('kernel.event_subscriber');
};
