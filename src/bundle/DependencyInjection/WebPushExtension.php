<?php

declare(strict_types=1);

namespace WebPush\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use WebPush\Bundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\LoggerSetterCompilerPass;
use WebPush\Bundle\DependencyInjection\Compiler\PayloadContentEncodingCompilerPass;
use WebPush\Loggable;
use WebPush\Payload\ContentEncoding;
use WebPush\VAPID\JWSProvider;

final class WebPushExtension extends Extension
{
    public function __construct(
        private readonly string $alias
    ) {
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->registerForAutoconfiguration(\WebPush\Extension::class)->addTag(ExtensionCompilerPass::TAG);
        $container->registerForAutoconfiguration(Loggable::class)->addTag(LoggerSetterCompilerPass::TAG);
        $container->registerForAutoconfiguration(ContentEncoding::class)->addTag(
            PayloadContentEncodingCompilerPass::TAG
        );

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/'));
        $loader->load('services.php');

        $container->setAlias('webpush.http_client', $config['http_client']);
        if ($config['logger'] !== null) {
            $container->setAlias(LoggerSetterCompilerPass::SERVICE, $config['logger']);
        }

        $this->configureVapidSection($container, $loader, $config['vapid']);
        $this->configurePayloadSection($container, $config['payload']);
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration($this->alias);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureVapidSection(ContainerBuilder $container, LoaderInterface $loader, array $config): void
    {
        if (! $config['enabled']) {
            return;
        }

        $container->setParameter('webpush.vapid.subject', $config['subject']);
        $container->setParameter('webpush.vapid.token_lifetime', $config['token_lifetime']);
        $loader->load('vapid.php');

        switch (true) {
            case $config['web_token']['enabled']:
                $loader->load('vapid.web_token.php');
                $container->setParameter('webpush.vapid.web_token.private_key', $config['web_token']['private_key']);
                $container->setParameter('webpush.vapid.web_token.public_key', $config['web_token']['public_key']);

                break;
            case $config['lcobucci']['enabled']:
                $loader->load('vapid.lcobucci.php');
                $container->setParameter('webpush.vapid.lcobucci.private_key', $config['lcobucci']['private_key']);
                $container->setParameter('webpush.vapid.lcobucci.public_key', $config['lcobucci']['public_key']);

                break;
            case $config['custom']['enabled']:
                $container->setAlias(JWSProvider::class, $config['custom']['id']);

                break;
        }
    }

    private function configurePayloadSection(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('webpush.payload.aesgcm.cache_lifetime', $config['aesgcm']['cache_lifetime']);
        $container->setParameter('webpush.payload.aesgcm.padding', $config['aesgcm']['padding']);
        if ($config['aesgcm']['cache'] !== null) {
            $container->setAlias('webpush.payload.aesgcm.cache', $config['aesgcm']['cache']);
        }

        $container->setParameter('webpush.payload.aes128gcm.cache_lifetime', $config['aes128gcm']['cache_lifetime']);
        $container->setParameter('webpush.payload.aes128gcm.padding', $config['aes128gcm']['padding']);
        if ($config['aes128gcm']['cache'] !== null) {
            $container->setAlias('webpush.payload.aes128gcm.cache', $config['aes128gcm']['cache']);
        }
    }
}
