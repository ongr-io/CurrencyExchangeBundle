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

use ONGR\CurrencyExchangeBundle\Service\CurrencyExchangeService;
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
        $container = self::createClient()->getContainer();
        $twig = $container->get('twig');
        /** @var PriceExtension $extension */
        $extension = $container->get('ongr_currency_exchange.twig.price_extension');
        $currencies = ['EUR', 'LTL'];
        $extension->setToListMap($currencies);
        $extension->setFormatsMap(array_combine($currencies, ['%s EUR', '%s LTL']));

        $result = $extension->getPriceList($twig, 1000);

        $expected = '<span class="currency currency-eur">1.000 EUR</span>' .
            '<span class="currency currency-ltl">3.454,60 LTL</span>';

        $this->assertEquals(trim($expected), trim($result));
    }
}
