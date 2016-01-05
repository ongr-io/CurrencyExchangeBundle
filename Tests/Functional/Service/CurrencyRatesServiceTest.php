<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Functional\Service;

use ONGR\CurrencyExchangeBundle\Driver\CurrencyDriverInterface;
use ONGR\CurrencyExchangeBundle\Driver\EcbDriver;
use ONGR\CurrencyExchangeBundle\Driver\OpenExchangeRatesDriver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This class holds unit tests for currency rates service.
 */
class CurrencyRatesServiceTest extends WebTestCase
{

    /**
     * Data provider for Currency Rates Driver test.
     *
     * @return array
     */
    public function dataProviderToGetDrivers()
    {
        $openExchange = new OpenExchangeRatesDriver();
        $openExchange->setAppId('8b447edc6e0e4661b584772ab6aa7611');

        $ecb = new EcbDriver();

        return [
            [$openExchange],
            [$ecb]
        ];
    }


    /**
     * @param CurrencyDriverInterface $driver
     *
     * @dataProvider dataProviderToGetDrivers
     */
    public function testGetRatesFromDriver(CurrencyDriverInterface $driver)
    {
        $this->assertNotEmpty($driver->getRates());
    }
}
