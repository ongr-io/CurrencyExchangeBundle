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

use Doctrine\Common\Cache\CacheProvider;
use ONGR\CurrencyExchangeBundle\Service\CurrencyRatesService;
use ONGR\CurrencyExchangeBundle\Document\CurrencyDocument;
use ONGR\ElasticsearchBundle\Service\Manager;
use ONGR\ElasticsearchBundle\Service\Repository;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * This class holds unit tests for currency rates service.
 */
class CurrencyRatesServiceTest extends \PHPUnit_Framework_TestCase
{
    const CURRENT_DATE = '2015-01-01';

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $esManagerMock;

    /**
     * @var CacheProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

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

    /**
     * @var array
     */
    private $result = [
        [
            'rates' => [
                [
                    'name' => 'EUR',
                    'value' => 1
                ],
                [
                    'name' => 'USD',
                    'value' => 1.12
                ]
            ],
            'created_at' => '2016-05-12T09:08:02+0300'
        ]
    ];

    /**
     * Before a test method is run, a template method called setUp() is invoked.
     */
    public function setUp()
    {
        $this->repositoryMock = $this->getMockBuilder('ONGR\ElasticsearchBundle\Service\Repository')
            ->setMethods(['findOneBy'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->esManagerMock = $this->getMockBuilder('ONGR\ElasticsearchBundle\Service\Manager')
            ->setMethods(['getRepository', 'persist', 'commit'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->esManagerMock->expects($this->any())->method('getRepository')->willReturn($this->repositoryMock);

        $this->cacheMock = $this->getMock('Doctrine\Common\Cache\CacheProvider');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    private function getLogger()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
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

    /**
     * Test if we are able to retrieve rates from cache.
     */
    public function testGetRatesFromCache()
    {
        $service = new CurrencyRatesService(
            $this->getDriverMock('EUR'),
            $this->esManagerMock,
            $this->cacheMock
        );

        $this->cacheMock->expects($this->once())->method('fetch')->willReturn($this->ratesFixture);
        $this->repositoryMock->expects($this->never())->method('findOneBy');

        $this->assertEquals($this->ratesFixture, $service->getRates(self::CURRENT_DATE));

        //Calling it again to test that rates are returned from class local cache.
        $this->assertEquals($this->ratesFixture, $service->getRates(self::CURRENT_DATE));
    }

    /**
     * Test if we are able to retrieve rates from cache.
     */
    public function testGetRatesFromDriver()
    {
        $this->repositoryMock->expects($this->exactly(2))->method('findOneBy')->willReturn(null);

        $service = new CurrencyRatesService(
            $this->getDriverMock('EUR', $this->ratesFixture),
            $this->esManagerMock,
            $this->cacheMock
        );

        $this->assertEquals($this->ratesFixture, $service->getRates(self::CURRENT_DATE));

        // Test local cache.
        $this->assertEquals($this->ratesFixture, $service->getRates(self::CURRENT_DATE));
    }

    /**
     * Test if we are able to retrieve rates from cache.
     */
    public function testGetBaseCurrency()
    {
        $service = new CurrencyRatesService(
            $this->getDriverMock('EUR'),
            $this->esManagerMock,
            $this->cacheMock
        );

        $this->assertEquals('EUR', $service->getBaseCurrency());
    }

    /**
     * Exception when rates are not loaded.
     *
     * @expectedException \ONGR\CurrencyExchangeBundle\Exception\RatesNotLoadedException
     */
    public function testWhenRateIsNotFound()
    {
        $this->repositoryMock->expects($this->once())->method('findOneBy')->willReturn(null);

        $service = new CurrencyRatesService(
            $this->getDriverMock('EUR', null),
            $this->esManagerMock,
            $this->cacheMock
        );

        $service->getRates();
    }
}
