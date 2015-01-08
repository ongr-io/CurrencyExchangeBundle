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

        $container1 = new ContainerBuilder();
        $container1->setDefinition('ongr_currency_exchange.twig.price_extension', new Definition());
        $container1->setDefinition('stash.memcache', new Definition());
        $container1->setDefinition('my_service', new Definition());
        $config1 = [
            'currency' => [
                'exchange' => [
                    'cache' => 'stash.memcache',
                    'live_load' => false,
                    'driver' => [
                        'open_exchange_rates' => [
                            'app_id' => '123456'
                        ]
                    ]
                ]
            ]
        ];
        $config2 = [
            'currency' => [
                'exchange' => [
                    'cache' => 'stash.memcache',
                    'driver' => [
                        'custom' => 'my_service'
                    ]
                ]
            ],
        ];

        // case #0 we need currency rates service
        $out[] = [$config1, $container1, 'ongr_currency_exchange.currency_rates_service'];

        // case #1 we need currency exchange service
        $out[] = [$config1, $container1, 'ongr_currency_exchange.currency_exchange_service'];

        // case #2 we need open exchange rates driver
        $out[] = [$config1, $container1, 'ongr_currency_exchange.open_exchange_rates_driver'];

        // case #3 we need currency rates service for custom driver
        $out[] = [$config2, $container1, 'ongr_currency_exchange.currency_rates_service'];

        // case #4 we need currency exchange service for custom driver
        $out[] = [$config2, $container1, 'ongr_currency_exchange.currency_exchange_service'];

        return $out;
    }

    /**
     * Test if we are able to load currency services
     * @dataProvider getTestLoadCurrencyData
     *
     * @param array $config
     * @param ContainerBuilder $container
     * @param string $expectedId
     */
    public function testLoadCurrency($config, $container, $expectedId)
    {
        $extension = new ONGRCurrencyExchangeExtension();
        $extension->load(['ongr_currency_exchange' => $config], $container);

        $this->assertTrue($container->hasDefinition($expectedId));
    }

    /**
     * Test if default currency works
     */
    public function testDefaultCurrency()
    {
        $config = [];
        $extension = new ONGRCurrencyExchangeExtension();
        $container = new ContainerBuilder();
        $extension->load($config, $container);

        $this->assertTrue($container->hasParameter('ongr_currency_exchange.default_currency'));
        $this->assertEquals('EUR', $container->getParameter('ongr_currency_exchange.default_currency'));
    }
}
