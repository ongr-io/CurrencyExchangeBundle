<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ongr_currency_exchange');

        $rootNode->children()
            ->arrayNode('currency')
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('default')
                        ->defaultValue('EUR')
                        ->info('set default currency')
                    ->end()
                    ->arrayNode('currencies')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->arrayNode('separators')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('decimal')
                                ->defaultValue(',')
                            ->end()
                            ->scalarNode('thousands')
                                ->defaultValue('.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('exchange')
                        ->children()
                            ->booleanNode('live_load')
                                ->defaultTrue()
                                ->info('set to false if we do not want to load currencies on request')
                            ->end()
                            ->scalarNode('cache')
                                ->isRequired()
                                ->info('set cache pool service id')
                                ->example('stash.memcache')
                            ->end()
                            ->arrayNode('driver')
                                ->children()
                                    ->arrayNode('open_exchange_rates')
                                        ->children()
                                            ->scalarNode('app_id')
                                                ->isRequired()
                                                ->info('set api key from openexchangerates.org')
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->scalarNode('custom')->info('set custom service id for driver')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
