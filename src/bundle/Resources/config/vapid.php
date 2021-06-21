<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\VAPIDExtension;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set(VAPIDExtension::class)
        ->args([
            param('webpush.vapid.subject'),
            service(JWSProvider::class),
        ])
        ->call('setTokenExpirationTime', [
            param('webpush.vapid.token_lifetime'),
        ])
    ;
};
