<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use ONGR\CurrencyExchangeBundle\DependencyInjection\ONGRCurrencyExchangeExtension;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Unit tests for Extension class.
 */
class ONGRCurrencyExchangeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestLoadCurrencyData()
    {
        $out = [];

        $container = new ContainerBuilder();
        $container->setDefinition('ongr_currency_exchange.twig.price_extension', new Definition());
        $container->setDefinition('my_service', new Definition());

        $config = [
            'driver' => 'ongr_currency_exchange.ecb_driver',
        ];
        // Case #0 we need currency rates service.
        $out[] = [$config, $container, 'ongr_currency_exchange.currency_rates_service'];
        // Case #1 we need currency exchange service.
        $out[] = [$config, $container, 'ongr_currency_exchange.currency_exchange_service'];
        // Case #2 we need open exchange rates driver.
        $out[] = [$config, $container, 'ongr_currency_exchange.open_exchange_driver'];

        return $out;
    }

    /**
     * Test if we are able to load currency services.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param string           $expectedId
     *
     * @dataProvider getTestLoadCurrencyData
     */
    public function testLoadCurrency($config, $container, $expectedId)
    {
        $extension = new ONGRCurrencyExchangeExtension();
        $extension->load(['ongr_currency_exchange' => $config], $container);

        $this->assertTrue($container->hasDefinition($expectedId));
    }

    /**
     * Test if default currency works.
     */
    public function testDefaultCurrency()
    {
        $config = [
            'ongr_currency_exchange' => [],
        ];
        $extension = new ONGRCurrencyExchangeExtension();
        $container = new ContainerBuilder();
        $extension->load($config, $container);

        $this->assertTrue($container->hasParameter('ongr_currency_exchange.default_currency'));
        $this->assertEquals('EUR', $container->getParameter('ongr_currency_exchange.default_currency'));
    }

    /**
     * Test if exception is thrown when Open Exchange Rates driver is configured without API ID.
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage "open_exchange_rates_api_id" must be set
     */
    public function testOpenExchangeRatesApiIdMissing()
    {
        $config = [
            'ongr_currency_exchange' => [
                'driver' => 'ongr_currency_exchange.open_exchange_driver',
            ],
        ];

        $extension = new ONGRCurrencyExchangeExtension();
        $extension->load($config, new ContainerBuilder());
    }

    /**
     * Test if exception is thrown if there is no definition of the driver in the container
     *
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testDriverNotFoundException()
    {
        $config = [
            'ongr_currency_exchange' => [
                'driver' => 'non_existing_driver',
            ],
        ];

        $extension = new ONGRCurrencyExchangeExtension();
        $extension->load($config, new ContainerBuilder());
    }
}
