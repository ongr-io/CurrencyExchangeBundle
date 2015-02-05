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

        $config = [
            'cache' => 'stash.memcache',
            'driver' => ['service' => 'ongr_currency_exchange.open_exchange_driver'],
        ];
        // Case #0 we need currency rates service.
        $out[] = [$config, $container1, 'ongr_currency_exchange.currency_rates_service'];
        // Case #1 we need currency exchange service.
        $out[] = [$config, $container1, 'ongr_currency_exchange.currency_exchange_service'];
        // Case #2 we need open exchange rates driver.
        $out[] = [$config, $container1, 'ongr_currency_exchange.open_exchange_driver'];

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
            'ongr_currency_exchange' => [
                'cache' => 'stash.files_cache',
                'driver' => [],
            ],
        ];
        $extension = new ONGRCurrencyExchangeExtension();
        $container = new ContainerBuilder();
        $extension->load($config, $container);

        $this->assertTrue($container->hasParameter('ongr_currency_exchange.default_currency'));
        $this->assertEquals('EUR', $container->getParameter('ongr_currency_exchange.default_currency'));
    }
}
