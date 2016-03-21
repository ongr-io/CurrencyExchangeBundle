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

use ONGR\CurrencyExchangeBundle\Driver\OpenExchangeRatesDriver;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class OpenExchangeRatesDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests getRates method with guzzle mock
     */
    public function testGetRates()
    {
        $response = [
            'disclaimer' => 'https://openexchangerates.org/terms/',
            'license' => 'https://openexchangerates.org/license/',
            'timestamp' => 1449877801,
            'base' => 'USD',
            'rates' => [
                'EUR' => 0.935485,
                'AFN' => 66.809999,
                'ALL' => 125.716501,
                'AMD' => 484.902502,
                'ANG' => 1.788575,
                'AOA' => 135.295998,
                'ARS' => 9.750101,
                'AUD' => 1.390866
            ]
        ];
        $response_json = json_encode($response);
        $mock = new MockHandler([
            new Response(200, [], $response_json)
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $driver = new OpenExchangeRatesDriver(
            $client,
            'http://openexchangerates.org/api/latest.json'
        );
        $data = $driver->getRates();
        $this->assertEquals($data, $response['rates']);
    }

    /**
     * Tests throwing of exception after empty response
     *
     * @expectedException \UnexpectedValueException
     */
    public function testGetRatesException()
    {
        $mock = new MockHandler([
            new Response(200, [])
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $driver = new OpenExchangeRatesDriver(
            $client,
            'http://openexchangerates.org/api/latest.json'
        );
        $driver->getRates();
    }

    /**
     * Tests throwing of exception after unexpected base currency
     *
     * @expectedException \UnexpectedValueException
     */
    public function testBaseCurrencyFromResponseException()
    {
        $response = [
            'disclaimer' => 'https://openexchangerates.org/terms/',
            'license' => 'https://openexchangerates.org/license/',
            'timestamp' => 1449877801,
            'base' => 'LTL',
            'rates' => [
                'EUR' => 0.935485,
            ],
        ];
        $response_json = json_encode($response);
        $mock = new MockHandler([
            new Response(200, [], $response_json)
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $driver = new OpenExchangeRatesDriver(
            $client,
            'http://openexchangerates.org/api/latest.json'
        );
        $driver->getRates();
    }

    /**
     * Tests app id setters and gettWers
     */
    public function testAppId()
    {
        $appId = 'id';
        $client = new Client();
        $driver = new OpenExchangeRatesDriver(
            $client,
            'http://openexchangerates.org/api/latest.json'
        );
        $driver->setAppId($appId);
        $this->assertEquals($appId, $driver->getAppId());
    }
}
