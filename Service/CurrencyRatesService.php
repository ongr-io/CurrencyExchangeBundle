<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Service;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use ONGR\CurrencyExchangeBundle\Document\CurrencyDocument;
use ONGR\CurrencyExchangeBundle\Document\RatesObject;
use ONGR\CurrencyExchangeBundle\Driver\CurrencyDriverInterface;
use ONGR\CurrencyExchangeBundle\Exception\RatesNotLoadedException;
use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use ONGR\ElasticsearchBundle\Service\Manager;
use Psr\Log\LoggerAwareTrait;
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;

/**
 * This class provides currency rates.
 */
class CurrencyRatesService
{
    use LoggerAwareTrait;

    /**
     * @var null|array
     */
    public $rates = null;

    /**
     * @var CurrencyDriverInterface
     */
    private $driver;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param CurrencyDriverInterface $driver  Currency exchange driver.
     * @param Manager                 $manager ES Manager.
     * @param PoolInterface           $pool    Cache pool.
     */
    public function __construct(
        CurrencyDriverInterface $driver,
        Manager $manager,
        PoolInterface $pool
    ) {
        $this->driver = $driver;
        $this->manager = $manager;
        $this->pool = $pool;
    }

    /**
     * This method returns exchange rates.
     *
     * @throws RatesNotLoadedException
     * @return array
     */
    public function getRates()
    {
        if (isset($this->rates)) {
            return $this->rates;
        }

        $item = $this->getCachedRates();
        $this->rates = $item->get();
        if (isset($this->rates)) {
            return $this->rates;
        }

        $this->rates = $this->getRatesFromBackup();
        if (isset($this->rates)) {
            return $this->rates;
        }

        $this->rates = $this->reloadRates();
        if (isset($this->rates)) {
            return $this->rates;
        }
        throw new RatesNotLoadedException('Currency rates are not loaded and could not be loaded on demand');
    }

    /**
     * Returns currency rates from ES.
     *
     * @return array
     */
    private function getRatesFromBackup()
    {
        $rates = [];
        $repository = $this->manager->getRepository('ONGRCurrencyExchangeBundle:CurrencyDocument');
        $search = $repository->createSearch();
        $search->addSort(new FieldSort('created_at', FieldSort::DESC));
        $query = new MatchAllQuery();
        $search->addQuery($query);
        $search->setSize(1);
        try {
            $results = $repository->execute($search, Result::RESULTS_ARRAY);
        } catch (Missing404Exception $e) {
            $this->logger && $this->logger->notice('Failed to execute query. Please check ES configuration');

            return null;
        }

        if (count($results)) {
            foreach ($results[0]['rates'] as $data) {
                $rates[$data['name']] = $data['value'];
            }
            $this->logger && $this->logger->notice('Rates returned from ES. Cache updated.');
            $this->updateRatesCache($rates);

            return $rates;
        }

        return null;
    }

    /**
     * Update rates in cache.
     *
     * @param array $rates
     */
    private function updateRatesCache($rates)
    {
        $this->getCachedRates()->set($rates);
    }

    /**
     * @return ItemInterface
     */
    private function getCachedRates()
    {
        return $this->pool->getItem('ongr_currency');
    }

    /**
     * Reloads rates using given driver.
     *
     * @return array
     */
    public function reloadRates()
    {
        $this->rates = $this->driver->getRates();

        /** @var CurrencyDocument $document */
        $document = new CurrencyDocument();
        
        if ($this->rates) {
            foreach ($this->rates as $name => $value) {
                $ratesObject = new RatesObject();
                $ratesObject->setName($name);
                $ratesObject->setValue($value);
                $document->addRate($ratesObject);
            }
            $this->manager->persist($document);
            $this->manager->commit();
            $this->updateRatesCache($this->rates);

            return $this->rates;
        }
        $this->logger && $this->logger->notice('Failed to retrieve currency rates from provider.');

        return null;
    }

    /**
     * Returns actual base currency name.
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->driver->getBaseCurrency();
    }
}
