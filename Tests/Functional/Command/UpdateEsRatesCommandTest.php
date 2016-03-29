<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Functional\Command;

use ONGR\CurrencyExchangeBundle\Command\UpdateEsRatesCommand;
use ONGR\CurrencyExchangeBundle\Driver\OpenExchangeRatesDriver;
use ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService;
use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class UpdateEsRatesCommandTest extends AbstractElasticsearchTestCase
{
    /**
     * Tests the UpdateEsRates command
     */
    public function testUpdateEsRatesCommand()
    {
        $this->setUpConfiguration(true);
        $app = new Application();
        $command = new UpdateEsRatesCommand();
        $command->setContainer($this->getContainer());
        $app->add($command);
        $command = $app->find('ongr:currency:update');
        $tester = new CommandTester($command);
        $tester->execute(
            [
                'command' => $command->getName(),
            ]
        );

        $this->assertContains(
            'Currency rates updated',
            $tester->getDisplay()
        );
        $this->assertEquals(0, $tester->getStatusCode(), 'Status code should be zero.');
    }

    /**
     * Tests the UpdateEsRates command
     */
    public function testUpdateEsRatesCommandException()
    {
        $this->setUpConfiguration(false);
        $app = new Application();
        $command = new UpdateEsRatesCommand();
        $command->setContainer($this->getContainer());
        $app->add($command);
        $command = $app->find('ongr:currency:update');
        $tester = new CommandTester($command);
        $tester->execute(
            [
                'command' => $command->getName(),
            ]
        );
        $this->assertContains(
            'Error ocurred during update.',
            $tester->getDisplay()
        );
    }

    /**
     * mocks http client to set a service to the container
     *
     * @param bool $loadInfo determines if the request will provide a response
     */
    public function setUpConfiguration($loadInfo)
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
        if ($loadInfo) {
            $response_json = json_encode($response);
            $mock = new MockHandler([
                new Response(200, [], $response_json)
            ]);
        } else {
            $mock = new MockHandler([new Response(400, [])]);
        }
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $driver = new OpenExchangeRatesDriver(
            $client,
            'http://openexchangerates.org/api/latest.json'
        );
        $item = $this->getMock('Stash\Interfaces\ItemInterface');
        $item->expects($this->any())->method('set');
        $pool = $this->getMock('Stash\Interfaces\PoolInterface');
        $pool->expects($this->any())->method('getItem')->with('ongr_currency')->willReturn($item);
        $currencyRateService = new CurrencyRatesService(
            $driver,
            $this->getManager(),
            $pool
        );
        $this->getContainer()->set('ongr_currency_exchange.currency_rates_service', $currencyRateService);
    }
}
