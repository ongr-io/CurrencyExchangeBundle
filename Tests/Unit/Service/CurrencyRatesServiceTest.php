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

use ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService;

/**
 * This class holds unit tests for currency rates service.
 */
class CurrencyRatesServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
    }

    /**
     * @var array
     */
    protected $ratesFixture = [
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
     * @param string     $base  Base currency name.
     * @param null|array $rates Currency rates.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\ONGR\CurrencyExchangeBundle\Currency\CurrencyDriverInterface
     */
    protected function getLoaderService($base, $rates = null)
    {
        $mock = $this->getMock('ONGR\CurrencyExchangeBundle\Currency\CurrencyDriverInterface');

        if ($rates) {
            $mock->expects($this->any())->method('getRates')->will($this->returnValue($rates));
        }

        $mock->expects($this->any())->method('getDefaultCurrencyName')->will($this->returnValue($base));

        return $mock;
    }

    /**
     * @param mixed $value
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Stash\Interfaces\ItemInterface
     */
    protected function getCacheItem($value)
    {
        $mock = $this->getMock('Stash\Interfaces\ItemInterface');

        if ($value) {
            $mock->expects($this->any())->method('get')->will($this->returnValue($value));
        }

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Stash\Interfaces\PoolInterface
     */
    public function getCachePool()
    {
        $mock = $this->getMock('Stash\Interfaces\PoolInterface');

        return $mock;
    }

    /**
     * Test if we return correct base currency.
     */
    public function testGetBaseCurrency()
    {
        $service = new CurrencyRatesService($this->getLoaderService('EUR'), $this->getCachePool());
        $service->setLogger($this->getLogger());
        $this->assertEquals('EUR', $service->getBaseCurrency());
    }

    /**
     * Test if we are able to retrieve rates from cache.
     */
    public function testGetRatesFromCache()
    {
        $pool = $this->getCachePool();
        $pool->expects($this->once())->method('getItem')->with('ongr_currency')->will(
            $this->returnValue($this->getCacheItem($this->ratesFixture))
        );
        $loader = $this->getLoaderService('EUR');
        $loader->expects($this->never())->method('getRates');

        $service = new CurrencyRatesService($loader, $pool);
        $service->setLogger($this->getLogger());

        $this->assertEquals($this->ratesFixture, $service->getRates());

        // Test local cache.
        $this->assertEquals($this->ratesFixture, $service->getRates());
    }

    /**
     * Test if we are able to retrieve rates from cache.
     */
    public function testGetRatesFromDriver()
    {
        $pool = $this->getCachePool();
        $item = $this->getCacheItem(null);
        $item->expects($this->once())->method('set')->with($this->ratesFixture);
        $pool->expects($this->any())->method('getItem')->with('ongr_currency')->will(
            $this->returnValue($item)
        );
        $loader = $this->getLoaderService('EUR', $this->ratesFixture);

        $service = new CurrencyRatesService($loader, $pool);
        $service->setLogger($this->getLogger());

        $this->assertEquals($this->ratesFixture, $service->getRates());

        // Test local cache.
        $this->assertEquals($this->ratesFixture, $service->getRates());
    }

    /**
     * Exception when rates are not loaded.
     *
     * @expectedException \ONGR\CurrencyExchangeBundle\Exception\RatesNotLoadedException
     */
    public function testException()
    {
        $pool = $this->getCachePool();
        $pool->expects($this->any())->method('getItem')->with('ongr_currency')->will(
            $this->returnValue($this->getCacheItem(null))
        );

        $service = new CurrencyRatesService($this->getLoaderService('EUR'), $pool, false);
        $service->setLogger($this->getLogger());
        $service->getRates();
    }
}
