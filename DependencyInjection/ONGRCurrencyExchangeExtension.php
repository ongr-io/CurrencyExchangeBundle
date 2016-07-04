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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->configCurrencyRatesService($config, $container);
        $this->configTwigExtension($config, $container);
        $this->configCurrencyExchangeService($container);
    }

    /**
     * Defines currency rates service.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @throws ServiceNotFoundException
     */
    private function configCurrencyRatesService(array $config, ContainerBuilder $container)
    {
        $driver = $config['driver'];
        $ecbApiId = $config['open_exchange_rates_api_id'];

        if ($driver == 'ongr_currency_exchange.open_exchange_driver' && !$ecbApiId) {
            throw new InvalidConfigurationException(
                '"open_exchange_rates_api_id" must be set when using ' .
                '"ongr_currency_exchange.open_exchange_driver" driver.'
            );
        }

        if ($container->hasDefinition($driver)) {
            $def = new Definition(
                'ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService',
                [
                    new Reference($driver),
                    new Reference(sprintf('es.manager.%s', $config['es_manager'])),
                    new Reference('ong_currency.cache_provider'),
                ]
            );
            $def->addMethodCall('setLogger', [new Reference('logger')]);
            $def->addTag('monolog.logger', ['channel' => 'ongr_currency']);
            $container->setDefinition('ongr_currency_exchange.currency_rates_service', $def);
        } else {
            throw new ServiceNotFoundException($driver);
        }
    }

    /**
     * Twig extension service.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configTwigExtension(array $config, ContainerBuilder $container)
    {
        $container->setParameter('ongr_currency_exchange.default_currency', $config['default_currency']);
        $container->setParameter(
            'ongr_currency_exchange.twig.price_extension.to_print_list',
            array_keys($config['currencies'])
        );
        if (isset($config['currency_sign'])) {
            $container->setParameter(
                'ongr_currency_exchange.twig.price_extension.currency.sign',
                $config['currency_sign']
            );
        }
        if (isset($config['default_currency'])) {
            $container->setParameter(
                'ongr_currency_exchange.twig.price_extension.currency.name',
                $config['default_currency']
            );
        }
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
        if (isset($config['templates']['currency_list'])) {
            $container->setParameter(
                'ongr_currency_exchange.twig.price_extension.currency.currency_list_template',
                $config['templates']['currency_list']
            );
        }
        if (isset($config['templates']['price_list'])) {
            $container->setParameter(
                'ongr_currency_exchange.twig.price_extension.currency.price_list_template',
                $config['templates']['price_list']
            );
        }
        $container->setParameter(
            'ongr_currency_exchange.twig.price_extension.display_map',
            $config['currencies']
        );
    }

    /**
     * Defines currency exchange service.
     *
     * @param ContainerBuilder $container
     */
    private function configCurrencyExchangeService(ContainerBuilder $container)
    {
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
