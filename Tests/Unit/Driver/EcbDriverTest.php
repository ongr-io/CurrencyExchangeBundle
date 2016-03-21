<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Unit\Driver;

use ONGR\CurrencyExchangeBundle\Driver\EcbDriver;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class EcbDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests getRates method
     */
    public function testGetRates()
    {
        $data = [];
        $data['xml'] = '
            <gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01">
                <gesmes:subject>Reference rates</gesmes:subject>
                <gesmes:Sender>
                    <gesmes:name>European Central Bank</gesmes:name>
                </gesmes:Sender>
                <Cube>
                    <Cube time="2016-03-18">
                        <Cube currency="USD" rate="1.1279"/>
                        <Cube currency="JPY" rate="125.79"/>
                        <Cube currency="BGN" rate="1.9558"/>
                        <Cube currency="CZK" rate="27.035"/>
                    </Cube>
                </Cube>
            </gesmes:Envelope>
        ';
        $data['array'] = [
            'USD' => 1.1279,
            'JPY' => 125.79,
            'BGN' => 1.9558,
            'CZK' => 27.035,
        ];
        $mock = new MockHandler([
            new Response(200, [], $data['xml'])
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $driver = new EcbDriver(
            $client,
            'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml'
        );
        $rates = $driver->getRates();
        $this->assertEquals($data['array'], $rates);
    }

    /**
     * Tests exception in getRates when the response
     * From ECB is not formatted correctly
     *
     * @expectedException \UnexpectedValueException
     */
    public function testBadResponseException()
    {
        $mock = new MockHandler([
            new Response(200, [], 'really bad response')
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $driver = new EcbDriver(
            $client,
            'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml'
        );
        $driver->getRates();
    }

    /**
     * Tests exception in getRates when the response
     * From ECB is empty
     *
     * @expectedException \UnexpectedValueException
     */
    public function testEmptyResponseException()
    {
        $mock = new MockHandler([
            new Response(200, [])
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $driver = new EcbDriver(
            $client,
            'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml'
        );
        $driver->getRates();
    }

    /**
     * Tests if base currency is returned correctly
     */
    public function testGetBaseCurrency()
    {
        $client = new Client();
        $driver = new EcbDriver(
            $client,
            'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml'
        );
        $this->assertEquals($driver->getBaseCurrency(), 'EUR');
    }
}
