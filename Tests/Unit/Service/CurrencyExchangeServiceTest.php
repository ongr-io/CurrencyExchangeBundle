<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Unit\Service;

use ONGR\CurrencyExchangeBundle\Service\CurrencyExchangeService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class CurrencyExchangeServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array Currency rates when base is EUR.
     */
    private $currencyRates = [
        'DOP' => '58.3638',
        'DZD' => '111.7223',
        'EEK' => '16.0508',
        'EGP' => '9.4839',
        'ETB' => '26.1282',
        'EUR' => '1.0000',
        'LTL' => '3.4516',
        'FJD' => '2.5182',
        'FKP' => '0.8561',
        'UGX' => '3475.3425',
        'USD' => '1.3766',
        'UYU' => '29.6077',
        'UZS' => '2988.6321',
        'VEF' => '8.6630',
        'VND' => '29105.6858',
    ];

    /**
     * @param array  $rates
     * @param string $base
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService
     */
    private function getRatesService($rates, $base)
    {
        $mock = $this->getMockBuilder('ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService')
            ->disableOriginalConstructor()->getMock();

        $mock->expects($this->any())->method('getRates')->will($this->returnValue($rates));
        $mock->expects($this->any())->method('getBaseCurrency')->will($this->returnValue($base));

        return $mock;
    }

    /**
     * Rate undefined.
     *
     * @expectedException \ONGR\CurrencyExchangeBundle\Exception\UndefinedCurrencyException
     */
    public function testUndefinedRate()
    {
        $service = new CurrencyExchangeService($this->getRatesService($this->currencyRates, 'USD'), 'USD');

        $service->getCurrencyRate('BBB');
    }

    /**
     * Test if service returns rates as expected.
     */
    public function testGetRates()
    {
        $service = new CurrencyExchangeService($this->getRatesService($this->currencyRates, 'USD'), 'USD');
        $this->assertEquals($this->currencyRates, $service->getCurrencies());
    }

    /**
     * Data provider for currency conversion test.
     *
     * @return array
     */
    public function dataProviderToTestCurrencyConversion()
    {
        return [
            [200, 'USD', 'LTL', 400],
            [100, null, 'USD', 200],
            [100, null, 'EUR', 100],
            [100, null, 'LTL', 400],
            [100, 'EUR', 'LTL', 400],
            [100, 'EUR', 'USD', 200],
            [100, 'LTL', 'USD', 50],
            [100, 'LTL', 'EUR', 25],
            [100, 'LTL', 'LTL', 100],
        ];
    }

    /**
     * Currency conversion test.
     *
     * @param float  $amountToConvert
     * @param string $from
     * @param string $to
     * @param float  $expect
     *
     * @dataProvider dataProviderToTestCurrencyConversion
     */
    public function testCurrencyConversation($amountToConvert, $from, $to, $expect)
    {
        $rates = [
            'LTL' => 2,
            'USD' => 1,
            'EUR' => 0.5,
        ];
        $service = new CurrencyExchangeService($this->getRatesService($rates, 'USD'), 'EUR');
        $this->assertEquals($expect, $service->calculateRate($amountToConvert, $to, $from));
    }
}
