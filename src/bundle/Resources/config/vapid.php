<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\VAPIDExtension;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()
        ->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set(VAPIDExtension::class)
        ->args([param('webpush.vapid.subject'), service(JWSProvider::class)])
        ->call('setTokenExpirationTime', [param('webpush.vapid.token_lifetime')])
    ;
};
