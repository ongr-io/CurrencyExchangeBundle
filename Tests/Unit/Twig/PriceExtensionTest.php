<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Unit\Twig;

use ONGR\CurrencyExchangeBundle\Service\CurrencyExchangeService;
use ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use ONGR\CurrencyExchangeBundle\Twig\PriceExtension;

class PriceExtensionTest extends WebTestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
    }

    /**
     * @param array  $rates
     * @param string $base
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService
     */
    protected function getRatesService($rates, $base)
    {
        $mock = $this->getMockBuilder('ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService')
            ->disableOriginalConstructor()->getMock();

        $mock->expects($this->any())->method('getRates')->will($this->returnValue($rates));
        $mock->expects($this->any())->method('getDefaultCurrency')->will($this->returnValue($base));

        return $mock;
    }

    /**
     * Test data getter.
     *
     * @return array[]
     */
    public function testGetFormattedPriceData()
    {
        $out = [];

        // Case 0.
        $out[] = [
            '1.990 €',
            1990.0,
            '€',
            ',',
            '.',
            0,
            null,
            null,
            null,
        ];
        // Case 1.
        $out[] = [
            '199 €',
            199.0,
            '€',
            ',',
            '.',
            0,
            null,
            null,
            null,
        ];
        // Case 2.
        $out[] = [
            '19,90 €',
            19.9,
            '€',
            ',',
            '.',
            0,
            null,
            null,
            null,
        ];
        // Case 3.
        $out[] = [
            '1,99 €',
            1.99,
            '€',
            ',',
            '.',
            0,
            null,
            null,
            null,
        ];
        // Case 4.
        $out[] = [
            '1.990,00 €',
            1990.0,
            '€',
            ',',
            '.',
            2,
            null,
            null,
            null,
        ];
        // Case 5: base currency different than the one that we are converting to.
        $out[] = [
            '$ 1.334,50',
            1000.0,
            '',
            ',',
            '.',
            2,
            // Value toCurrency.
            'USD',
            // Value fromCurrency.
            'EUR',
            null,
        ];
        // Case 6: custom format to price.
        $out[] = [
            '$ 1.334,50 $',
            1000.0,
            '',
            ',',
            '.',
            2,
            // Value toCurrency.
            'USD',
            // Value fromCurrency.
            'EUR',
            '$ %s $',
        ];
        // Case 7: converting to USD.
        $out[] = [
            '$ 1.334,50',
            1000,
            '',
            ',',
            '.',
            2,
            // Value toCurrency.
            'USD',
            null,
            null,
        ];

        return $out;
    }

    /**
     * Test price formatting.
     *
     * @param string $expected
     * @param float  $price
     * @param string $currencySign
     * @param string $decPointSeparator
     * @param string $thousandsSeparator
     * @param int    $decimalPlaces
     * @param string $toCurrency
     * @param string $fromCurrency
     * @param string $customFormat
     *
     * @dataProvider testGetFormattedPriceData()
     */
    public function testGetFormattedPrice(
        $expected,
        $price,
        $currencySign,
        $decPointSeparator,
        $thousandsSeparator,
        $decimalPlaces,
        $toCurrency,
        $fromCurrency,
        $customFormat
    ) {
        $rates = [
            'EUR' => '1',
            'USD' => '1.3345',
            'LTL' => '3.4546',
        ];

        $formatsMap = [
            'EUR' => '%s €',
            'USD' => '$ %s',
        ];

        $exchangeService = new CurrencyExchangeService($this->getRatesService($rates, 'EUR'), 'EUR');
        $extension = new PriceExtension($currencySign, $decPointSeparator, $thousandsSeparator, null, $formatsMap);
        // EUR set by default.
        $extension->setCurrency('EUR');
        $extension->setCurrencyExchangeService($exchangeService);

        $this->assertEquals(
            $expected,
            $extension->getFormattedPrice($price, $decimalPlaces, $toCurrency, $fromCurrency, $customFormat)
        );
    }

    /**
     * Tests if extension contains functions.
     */
    public function testGetFunctions()
    {
        $extension = new PriceExtension('', '', '');
        $this->assertNotEmpty($extension->getFunctions(), 'Extension should contain functions.');
    }

    /**
     * Expected filters getter.
     *
     * @return array
     */
    public function getExpectedFilters()
    {
        return [['ongr_price']];
    }

    /**
     * Test filters.
     *
     * @param string $filter
     *
     * @dataProvider getExpectedFilters()
     */
    public function testGetFilters($filter)
    {
        $extension = new PriceExtension('', '', '');

        $filters = $extension->getFilters();

        $exists = false;
        foreach ($filters as $filterObject) {
            if ($filterObject->getName() == $filter) {
                $exists = true;
                $this->assertTrue(is_callable($filterObject->getCallable()));
                $node = new \Twig_Node();
                $this->assertEquals(['html'], $filterObject->getSafe($node));
                break;
            }
        }

        $this->assertTrue($exists);
    }

    /**
     * Require correct extension name.
     */
    public function testGetName()
    {
        $extension = new PriceExtension('', '', '');
        $this->assertEquals('price_extension', $extension->getName());
    }

    /**
     * Test Currency setter.
     */
    public function testCurrencySetter()
    {
        $extension = new PriceExtension('', '', '');
        $extension->setCurrency('USD');

        $this->assertEquals('USD', $extension->getCurrency());
    }

    /**
     * Test default factory.
     */
    public function testDefaultCurrency()
    {
        $extension = new PriceExtension('', '.', '', 'EUR');

        $this->assertEquals('EUR', $extension->getCurrency());
    }

    /**
     * Data provider for testPriceWithCurrency.
     *
     * @return array
     */
    public function testPriceWithCurrencyData()
    {
        $out = [];

        $rates = [
            'EUR' => '1',
            'USD' => '1.3345',
            'LTL' => '3.4546',
        ];

        $out[] = [$rates, 'EUR', 100, '100 '];
        $out[] = [$rates, 'USD', 100, '133.45 '];
        $out[] = [$rates, 'LTL', 100, '345.46 '];

        return $out;
    }

    /**
     * Test prices with currency data.
     *
     * @param array  $rates
     * @param string $currency
     * @param int    $price
     * @param string $expected
     *
     * @dataProvider testPriceWithCurrencyData
     */
    public function testPriceWithCurrency($rates, $currency, $price, $expected)
    {
        $exchangeService = new CurrencyExchangeService($this->getRatesService($rates, 'EUR'), 'EUR');

        $extension = new PriceExtension('', '.', '', $currency);
        $extension->setCurrencyExchangeService($exchangeService);

        $result = $extension->getFormattedPrice($price, 0);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test price formatting with default currency.
     */
    public function testFormatWithDefaultCurrency()
    {
        $rates = [
            'EUR' => '1',
            'USD' => '1.3345',
        ];

        $formatMap = [
            'EUR' => 'Price in euros: %s',
            'USD' => 'Price in dollars: %s',
        ];

        $exchangeService = new CurrencyExchangeService($this->getRatesService($rates, 'EUR'), 'EUR');

        $extension = new PriceExtension('', '.', '', 'EUR', $formatMap);
        $extension->setCurrencyExchangeService($exchangeService);

        $result = $extension->getFormattedPrice(1000, 0, null, null);
        $expected = 'Price in euros: 1000';

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testPriceListFormatting.
     *
     * @return array
     */
    public function testPriceListFormattingData()
    {
        $out = [];
        $rates = [
            'SEK' => '8.79',
            'EUR' => '1',
            'USD' => '1.3345',
            'LTL' => '3.4546',
        ];
        $toPrintList = [
            'SEK',
            'EUR',
        ];
        // Case #1 printlist currencies with all formats specified.
        $formatsMap = [
            'SEK' => '%s Svensk krona',
            'EUR' => '%s Euros',
        ];
        $expectedParams = [
            'prices' => [
                [
                    'value' => '8790 Svensk krona',
                    'currency' => 'sek',
                ],
                [
                    'value' => '1000 Euros',
                    'currency' => 'eur',
                ],
            ],
        ];
        $out[] = [$rates, $toPrintList, $formatsMap, $expectedParams];
        // Case #1 printlist currencies with one format specified.
        $formatsMap = [
            'SEK' => '%s Svensk krona',
        ];
        $expectedParams = [
            'prices' => [
                [
                    'value' => '8790 Svensk krona',
                    'currency' => 'sek',
                ],
                [
                    'value' => '1000 EUR',
                    'currency' => 'eur',
                ],
            ],
        ];
        $out[] = [$rates, $toPrintList, $formatsMap, $expectedParams];

        return $out;
    }

    /**
     * Test getPriceList output formatting.
     *
     * @param array $rates
     * @param array $toPrintList
     * @param array $formatMap
     * @param array $expectedParams
     *
     * @dataProvider testPriceListFormattingData
     */
    public function testPriceListFormatting($rates, $toPrintList, $formatMap, $expectedParams)
    {
        $exchangeService = new CurrencyExchangeService($this->getRatesService($rates, 'EUR'), 'EUR');
        $extension = new PriceExtension('EUR', '.', '', 'EUR', $formatMap, $toPrintList);
        $extension->setCurrencyExchangeService($exchangeService);
        $env = $this->getMock('stdClass', ['render']);
        $env->expects($this->once())->method('render')->with(
            'testTemplate',
            $expectedParams
        )->will($this->returnValue($extension->getFormattedPrice(1000)));

        $extension->getPriceList($env, 1000, 'testTemplate', null);
    }

    /**
     * Data provider for testPriceListWithCurrency.
     *
     * @return array
     */
    public function testPriceListWithCurrencyData()
    {
        $out = [];
        $rates = [
            'SEK' => '0.1',
            'EUR' => '1',
            'USD' => '1.3345',
            'LTL' => '3.4546',
        ];
        $toPrintList = [
            'EUR',
            'LTL',
        ];
        // Case #1 the default currency is used.
        $expectedParams = [
            'prices' => [
                [
                    'value' => '100 ',
                    'currency' => 'eur',
                ],
                [
                    'value' => '345.46 ',
                    'currency' => 'ltl',
                ],
            ],
        ];
        $out[] = [$rates, $toPrintList, 'EUR', 100, $expectedParams];
        // Case #2 currency not in the toPrintlist is used.
        $expectedParams = [
            'prices' => [
                [
                    'value' => '74.93 ',
                    'currency' => 'eur',
                ],
                [
                    'value' => '258.87 ',
                    'currency' => 'ltl',
                ],
            ],
        ];
        $out[] = [$rates, $toPrintList, 'USD', 100, $expectedParams];
        // Case #3 currency in the toPrintlist is used.
        $expectedParams = [
            'prices' => [
                [
                    'value' => '28.95 ',
                    'currency' => 'eur',
                ],
                [
                    'value' => '100 ',
                    'currency' => 'ltl',
                ],
            ],
        ];
        $out[] = [$rates, $toPrintList, 'LTL', 100, $expectedParams];

        return $out;
    }

    /**
     * Test getPriceList with currency.
     *
     * @param array  $rates
     * @param array  $toPrintList
     * @param string $currency
     * @param int    $price
     * @param array  $expectedParams
     *
     * @dataProvider testPriceListWithCurrencyData
     */
    public function testPriceListWithCurrency($rates, $toPrintList, $currency, $price, $expectedParams)
    {
        $exchangeService = new CurrencyExchangeService($this->getRatesService($rates, 'EUR'), 'EUR');
        $extension = new PriceExtension('', '.', '', $currency, null, $toPrintList);
        $extension->setCurrencyExchangeService($exchangeService);
        $env = $this->getMock('stdClass', ['render']);
        $env->expects($this->once())->method('render')->with(
            'testTemplate',
            $expectedParams
        );
        $extension->getPriceList($env, $price, 'testTemplate', $currency);
    }

    /**
     * Test getPriceList with default currency.
     */
    public function testPriceListWithDefaultCurrency()
    {
        $rates = [
            'EUR' => '1',
            'USD' => '1.3345',
            'LTL' => '3.4546',
        ];
        $toPrintList = [
            'EUR',
            'LTL',
        ];
        $exchangeService = new CurrencyExchangeService($this->getRatesService($rates, 'EUR'), 'EUR');
        $extension = new PriceExtension('', '.', '', 'EUR', null, $toPrintList);
        $extension->setCurrencyExchangeService($exchangeService);
        $expectedParams = [
            'prices' => [
                [
                    'value' => '1000 ',
                    'currency' => 'eur',
                ],
                [
                    'value' => '3454.60 ',
                    'currency' => 'ltl',
                ],
            ],
        ];
        $env = $this->getMock('stdClass', ['render']);
        $env->expects($this->once())->method('render')->with(
            'testTemplate',
            $expectedParams
        );
        $extension->getPriceList($env, 1000, 'testTemplate', null);
    }

    /**
     * Test getCurrencyList.
     */
    public function testCurrencyList()
    {
        $rates = [
            'EUR' => '1',
            'USD' => '1.3345',
            'LTL' => '3.4546',
        ];
        $toPrintList = [
            'EUR',
            'LTL',
            'USD',
        ];
        $exchangeService = new CurrencyExchangeService($this->getRatesService($rates, 'EUR'), 'EUR');
        $extension = new PriceExtension('', '.', '', 'EUR', null, $toPrintList);
        $extension->setCurrencyExchangeService($exchangeService);
        $expectedParams = [
            'currencies' => [
                [
                    'value' => 'EUR',
                    'code' => 'eur',
                    'default' => true,
                ],
                [
                    'value' => 'LTL',
                    'code' => 'ltl',
                    'default' => false,
                ],
                [
                    'value' => 'USD',
                    'code' => 'usd',
                    'default' => false,
                ],
            ],
        ];
        $env = $this->getMock('stdClass', ['render']);
        $env->expects($this->once())->method('render')->with(
            'testTemplate',
            $expectedParams
        );
        $extension->getCurrencyList($env, 'testTemplate');
    }

    /**
     * Test behavior when there is no currency exchange service defined.
     */
    public function testNoCurrencyExchange()
    {
        $extension = new PriceExtension('', '.', '', 'EUR', null, []);

        $this->assertEquals('', $extension->getFormattedPrice(2, 0, 'a', 'b'));
    }

    /**
     * Test case when we pass undefined currency.
     */
    public function testUndefinedCurrency()
    {
        $exchangeService = new CurrencyExchangeService($this->getRatesService([], 'EUR'), 'EUR');
        $extension = new PriceExtension('', '.', '', 'EUR', null, []);
        $extension->setCurrencyExchangeService($exchangeService);
        $extension->setLogger($this->getLogger());

        $this->assertEquals('', $extension->getFormattedPrice(2, 0, 'a', 'b'));
    }
}
