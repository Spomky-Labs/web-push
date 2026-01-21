<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\WebTokenProvider;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()
        ->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set(JWSProvider::class)
        ->class(WebTokenProvider::class)
        ->args([param('webpush.vapid.web_token.public_key'), param('webpush.vapid.web_token.private_key')])
    ;
};
