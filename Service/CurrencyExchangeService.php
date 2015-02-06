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

use ONGR\CurrencyExchangeBundle\Exception\UndefinedCurrencyException;

/**
 * This class handles currency rates download and exchange.
 */
class CurrencyExchangeService
{
    /**
     * @var CurrencyRatesService
     */
    private $rates = null;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @param CurrencyRatesService $rates
     * @param string               $defaultCurrency
     */
    public function __construct(CurrencyRatesService $rates, $defaultCurrency)
    {
        $this->rates = $rates;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @param string $currency
     *
     * @throws UndefinedCurrencyException
     *
     * @return float
     */
    public function getCurrencyRate($currency)
    {
        $rates = $this->rates->getRates();

        if (isset($rates[$currency])) {
            return $rates[$currency];
        }

        throw new UndefinedCurrencyException('Currency ' . $currency . ' not found.');
    }

    /**
     * @return array|null
     */
    public function getCurrencies()
    {
        return $this->rates->getRates();
    }

    /**
     * This function calculate rates.
     *
     * @param float|int $amount
     * @param string    $toCurrency
     * @param null      $fromCurrency
     *
     * @return float
     */
    public function calculateRate($amount, $toCurrency, $fromCurrency = null)
    {
        if (!isset($fromCurrency)) {
            $fromCurrency = $this->defaultCurrency;
        }

        if ($this->rates->getBaseCurrency() != $fromCurrency) {
            $amount = $amount / $this->getCurrencyRate($fromCurrency);
        }

        if ($toCurrency == $this->rates->getBaseCurrency()) {
            return $amount;
        }

        return $amount * $this->getCurrencyRate($toCurrency);
    }
}
