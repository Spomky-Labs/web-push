<?php

declare(strict_types=1);

namespace WebPush\Bundle\DependencyInjection;

use function in_array;
use function is_int;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;

final class Configuration implements ConfigurationInterface
{
    public function __construct(
        private readonly string $alias
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->alias);
        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('doctrine_mapping')
            ->defaultFalse()
            ->info('If true, the doctrine schemas will be loaded')
            ->end()
            ->scalarNode('logger')
            ->defaultNull()
            ->info('A PSR3 logger to receive logs')
            ->end()
            ->scalarNode('http_client')
            ->defaultValue(ClientInterface::class)
            ->info('PSR18 client to send notification to Web Push Services')
            ->end()
            ->scalarNode('request_factory')
            ->defaultValue(RequestFactoryInterface::class)
            ->info('PSR17 Request Factory to create requests')
            ->end()
            ->arrayNode('vapid')
            ->canBeEnabled()
            ->validate()
            ->ifTrue(static function (array $conf): bool {
                $wt = $conf['web_token']['enabled'] ? 1 : 0;
                $lc = $conf['lcobucci']['enabled'] ? 1 : 0;
                $cu = $conf['custom']['enabled'] ? 1 : 0;

                return $wt + $lc + $cu !== 1;
            })
            ->thenInvalid('One, and only one, JWS Provider shall be set')
            ->end()
            ->children()
            ->scalarNode('subject')
            ->isRequired()
            ->info('The URL of the service or an email address')
            ->end()
            ->scalarNode('token_lifetime')
            ->defaultValue('now +1hour')
            ->info('A PSR6 cache pool to enable caching feature')
            ->end()
            ->arrayNode('web_token')
            ->canBeEnabled()
            ->children()
            ->scalarNode('private_key')
            ->isRequired()
            ->info('The VAPID private key')
            ->end()
            ->scalarNode('public_key')
            ->isRequired()
            ->info('The VAPID public key')
            ->end()
            ->end()
            ->end()
            ->arrayNode('lcobucci')
            ->canBeEnabled()
            ->children()
            ->scalarNode('private_key')
            ->isRequired()
            ->info('The VAPID private key')
            ->end()
            ->scalarNode('public_key')
            ->isRequired()
            ->info('The VAPID public key')
            ->end()
            ->end()
            ->end()
            ->arrayNode('custom')
            ->canBeEnabled()
            ->children()
            ->scalarNode('id')
            ->isRequired()
            ->info('The custom JWS Provider service ID')
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('payload')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('aes128gcm')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('padding')
            ->defaultValue('recommended')
            ->info('Length of the padding: none, recommended, max or and integer')
            ->validate()
            ->ifTrue(static function ($conf): bool {
                if (in_array($conf, ['none', 'max', 'recommended'], true)) {
                    return false;
                }
                if (! is_int($conf)) {
                    return true;
                }

                return $conf < 0 || $conf > AES128GCM::PADDING_MAX;
            })
            ->thenInvalid(
                sprintf(
                    'The padding must have one of the following value: none, recommended, max or an integer between 0 and %d',
                    AES128GCM::PADDING_MAX
                )
            )
            ->end()
            ->end()
            ->scalarNode('cache')
            ->defaultNull()
            ->info('A PSR6 cache pool to enable caching feature')
            ->end()
            ->scalarNode('cache_lifetime')
            ->defaultValue('now + 30min')
            ->info('A PSR6 cache pool to enable caching feature')
            ->end()
            ->end()
            ->end()
            ->arrayNode('aesgcm')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('padding')
            ->defaultValue('recommended')
            ->info('Length of the padding: none, recommended, max or and integer')
            ->validate()
            ->ifTrue(static function ($conf): bool {
                if (in_array($conf, ['none', 'max', 'recommended'], true)) {
                    return false;
                }
                if (! is_int($conf)) {
                    return true;
                }

                return $conf < 0 || $conf > AESGCM::PADDING_MAX;
            })
            ->thenInvalid(
                sprintf(
                    'The padding must have one of the following value: none, recommended, max or an integer between 0 and %d',
                    AESGCM::PADDING_MAX
                )
            )
            ->end()
            ->end()
            ->scalarNode('cache')
            ->defaultNull()
            ->info('A PSR6 cache pool to enable caching feature')
            ->end()
            ->scalarNode('cache_lifetime')
            ->defaultValue('now + 30min')
            ->info('A PSR6 cache pool to enable caching feature')
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
