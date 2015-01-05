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

/**
 * Unit tests for Extension class.
 */
class ONGRCurrencyExchangeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * First test.
     */
    public function testIfContainerHasDefinition()
    {
        $container = new ContainerBuilder();
        $extension = new ONGRCurrencyExchangeExtension();
        $extension->load([], $container);
        $this->assertTrue($container->hasDefinition('ongr_currency_exchange.example'));
    }
}
