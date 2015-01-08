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

use ONGR\CurrencyExchangeBundle\Currency\CurrencyDriverInterface;
use ONGR\CurrencyExchangeBundle\Exception\RatesNotLoadedException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;

/**
 * This class provides currency rates.
 */
class CurrencyRatesService implements LoggerAwareInterface
{
    /**
     * @var CurrencyDriverInterface
     */
    protected $driver;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var bool
     */
    protected $poorManLoad = true;

    /**
     * @var null|array
     */
    protected $rates = null;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CurrencyDriverInterface $driver      Currency exchange driver.
     * @param PoolInterface           $pool        Cache pool.
     * @param bool                    $poorManLoad Set to true if we want to load currencies on request.
     */
    public function __construct(CurrencyDriverInterface $driver, PoolInterface $pool, $poorManLoad = true)
    {
        $this->driver = $driver;
        $this->pool = $pool;
        $this->poorManLoad = $poorManLoad;
    }

    /**
     * @return ItemInterface
     */
    protected function getCachePoolItem()
    {
        return $this->pool->getItem('ongr_currency');
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

        /** @var ItemInterface $item */
        $item = $this->getCachePoolItem();

        $this->rates = $item->get();
        if (isset($this->rates)) {
            return $this->rates;
        }

        if ($this->poorManLoad) {
            $this->logger && $this->logger->notice('Auto reloaded currency rates on CurrencyRatesService');
            $this->reloadRates();
        } else {
            throw new RatesNotLoadedException('Currency rates are not loaded and could not be loaded on demand');
        }

        return $this->rates;
    }

    /**
     * Returns actual base currency name.
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->driver->getDefaultCurrencyName();
    }

    /**
     * Reloads rates using given driver.
     */
    public function reloadRates()
    {
        $this->rates = $this->driver->getRates();
        $this->getCachePoolItem()->set($this->rates);
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
