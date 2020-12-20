<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Bundle\DependencyInjection;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    private string $alias;

    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->alias);
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('logger')
                    ->defaultNull()
                    ->info('A PSR3 logger to receive logs')
                ->end()
                ->scalarNode('event_dispatcher')
                    ->defaultNull()
                    ->info('A PSR14 event dispatcher to dispatch events')
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
                        ->thenInvalid('Invalid')
                    ->end()
                    ->children()
                        ->scalarNode('subject')
                            ->isRequired()
                            ->info('The URL of the service or an email address')
                        ->end()
                        ->scalarNode('cache')
                            ->defaultNull()
                            ->info('A PSR6 cache pool to enable caching feature')
                        ->end()
                        ->scalarNode('cache_lifetime')
                            ->defaultValue('now + 30min')
                            ->info('A PSR6 cache pool to enable caching feature')
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
                                    ->info('The VAPID private key')
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
                                            return !in_array($conf, ['none', 'max', 'recommended']) && !(is_int($conf));
                                        })
                                        ->thenInvalid('Invalid')
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
                                            return !in_array($conf, ['none', 'max', 'recommended']) && !(is_int($conf));
                                        })
                                        ->thenInvalid('Invalid')
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
