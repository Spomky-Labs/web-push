<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use WebPush\ExtensionManager;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\PreferAsyncExtension;
use WebPush\TopicExtension;
use WebPush\TTLExtension;
use WebPush\UrgencyExtension;
use WebPush\WebPush;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()
        ->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set(ExtensionManager::class);
    $container->set(UrgencyExtension::class);
    $container->set(TTLExtension::class);
    $container->set(TopicExtension::class);
    $container->set(PreferAsyncExtension::class);
    $container->set(PayloadExtension::class);
    $container->set(AESGCM::class);
    $container->set(AES128GCM::class);

    $container->set(WebPush::class)
        ->args([
            service('webpush.http_client'),
            service('webpush.request_factory'),
            service(ExtensionManager::class),
        ])
        ->public()
    ;
};
