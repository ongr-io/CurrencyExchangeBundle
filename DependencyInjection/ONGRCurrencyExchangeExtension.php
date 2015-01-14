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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 */
class ONGRCurrencyExchangeExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ongr_currency_exchange.default_currency', $config['default']);
        $container->setParameter(
            'ongr_currency_exchange.twig.price_extension.to_print_list',
            array_keys($config['currencies'])
        );

        if (isset($config['separators']['decimal'])) {
            $container->setParameter(
                'ongr_currency_exchange.twig.price_extension.currency.dec_point_separator',
                $config['separators']['decimal']
            );
        }
        if (isset($config['separators']['thousands'])) {
            $container->setParameter(
                'ongr_currency_exchange.twig.price_extension.currency.thousands_separator',
                $config['separators']['thousands']
            );
        }
        $container->setParameter(
            'ongr_currency_exchange.twig.price_extension.display_map',
            $config['currencies']
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['exchange'])) {
            $this->loadCurrencyServices($config, $container);
        }
    }

    /**
     * Build currency services.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadCurrencyServices($config, ContainerBuilder $container)
    {
        $driver = null;

        if (isset($config['exchange']['driver']['custom'])) {
            $driver = $config['exchange']['driver']['custom'];
        } elseif (isset($config['exchange']['driver']['open_exchange_rates'])) {
            $driver = 'ongr_currency_exchange.open_exchange_rates_driver';
            $def = new Definition(
                $container->getParameter('ongr_currency_exchange.open_exchange_rates_driver.class'),
                [$config['exchange']['driver']['open_exchange_rates']['app_id']]
            );
            $container->setDefinition($driver, $def);
        }

        // Currency rates service.
        $def = new Definition(
            $container->getParameter('ongr_currency_exchange.currency_rates_service.class'),
            [
                new Reference($driver),
                new Reference($config['exchange']['cache']),
                $config['exchange']['live_load'],
            ]
        );
        $def->addMethodCall('setLogger', [new Reference('logger')]);
        $def->addTag('monolog.logger', ['channel' => 'ongr_currency']);
        $container->setDefinition('ongr_currency_exchange.currency_rates_service', $def);

        // Currency exchange service.
        $def = new Definition(
            $container->getParameter('ongr_currency_exchange.currency_exchange_service.class'),
            [new Reference('ongr_currency_exchange.currency_rates_service'), $config['default']]
        );
        $container->setDefinition('ongr_currency_exchange.currency_exchange_service', $def);

        // Apply exchange service to price extension.
        $def = $container->getDefinition('ongr_currency_exchange.twig.price_extension');
        $def->addMethodCall('setLogger', [new Reference('logger')]);
        $def->addTag('monolog.logger', ['channel' => 'ongr_currency']);
        $def->addMethodCall(
            'setCurrencyExchangeService',
            [new Reference('ongr_currency_exchange.currency_exchange_service')]
        );
    }
}
