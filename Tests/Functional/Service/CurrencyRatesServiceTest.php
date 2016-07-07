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

use ONGR\CurrencyExchangeBundle\Document\CurrencyDocument;
use ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService;
use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;

class CurrencyRatesServiceTest extends AbstractElasticsearchTestCase
{
    const CURRENT_DATE = '2015-01-01';

    /**
     * @var array
     */
    private $ratesFixture = [
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

    public function setUp()
    {
        parent::setUp();

        $this->getContainer()->get('ong_currency.cache_provider')->deleteAll();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataArray()
    {
        $rates = [];

        foreach ($this->ratesFixture as $rate => $value) {
            $rates[] = [
                'name' => $rate,
                'value' => $value,
            ];
        }

        return [
            'default' => [
                'currency' => [
                    [
                        '_id' => 1,
                        'date' => self::CURRENT_DATE,
                        'rates' => $rates,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string     $base  Base currency name.
     * @param null|array $rates Currency rates.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\ONGR\CurrencyExchangeBundle\Driver\CurrencyDriverInterface
     */
    private function getDriverMock($base, $rates = null)
    {
        $mock = $this->getMock('ONGR\CurrencyExchangeBundle\Driver\CurrencyDriverInterface');
        $mock->expects($this->any())->method('getRates')->will($this->returnValue($rates));
        $mock->expects($this->any())->method('getBaseCurrency')->will($this->returnValue($base));

        return $mock;
    }

    public function testGetCurrenciesFromEs()
    {
        $service = new CurrencyRatesService(
            $this->getDriverMock('EUR', null),
            $this->getManager('default'),
            $this->getContainer()->get('ong_currency.cache_provider'),
            'EUR'
        );

        $rates = $service->getRates(self::CURRENT_DATE);

        $this->assertEquals($this->ratesFixture, $rates);
    }

    public function testSaveCurrenciesToEs()
    {
        $manager = $this->getManager('default');
        $service = new CurrencyRatesService(
            $this->getDriverMock('EUR', $this->ratesFixture),
            $this->getManager('default'),
            $this->getContainer()->get('ong_currency.cache_provider'),
            'EUR'
        );

        $date = date('Y-m-d');

        $repo = $manager->getRepository('ONGRCurrencyExchangeBundle:CurrencyDocument');
        $document = $repo->findOneBy(['date' => $date]);
        $this->assertNull($document);

        $rates = $service->getRates($date);
        $this->assertEquals($this->ratesFixture, $rates);

        /** @var CurrencyDocument $document */
        $document = $repo->findOneBy(['date' => $date]);
        $this->assertEquals($date, $document->getDate()->format('Y-m-d'));
    }
}
