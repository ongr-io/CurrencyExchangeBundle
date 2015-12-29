<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Functional\Twig;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use ONGR\CurrencyExchangeBundle\Twig\PriceExtension;

/**
 * Class PriceExtensionTest.
 *
 * @package ONGR\CurrencyExchangeBundle\Tests\Unit\Twig
 */
class PriceExtensionTest extends WebTestCase
{
    /**
     * Test getPriceList().
     */
    public function testGetPriceList()
    {
        $currencyService = $this->getMockBuilder('ONGR\CurrencyExchangeBundle\Service\CurrencyExchangeService')
            ->setMethods(['calculateRate'])
            ->disableOriginalConstructor()
            ->getMock();

        $callback = function ($amount, $toCurrency, $fromCurrency = null) {
            return $amount;
        };
        $currencyService->expects($this->any())->method('calculateRate')->willReturnCallback($callback);

        $container = self::createClient()->getContainer();
        $twig = $container->get('twig');
        /** @var PriceExtension $extension */
        $extension = $container->get('ongr_currency_exchange.twig.price_extension');
        $extension->setCurrencyExchangeService($currencyService);
        $currencies = ['EUR', 'LTL'];
        $extension->setToListMap($currencies);
        $extension->setFormatsMap(array_combine($currencies, ['%s EUR', '%s LTL']));

        $result = $extension->getPriceList($twig, 2500);
        $this->assertContains('<li class="currency currency-eur">2.500 EUR</li>', $result);
        $this->assertContains('LTL</li>', $result);
    }
}
