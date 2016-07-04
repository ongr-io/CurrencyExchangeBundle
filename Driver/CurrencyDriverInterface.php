<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Driver;

/**
 * This interface defines methods for currency driver.
 *
 * Currency driver is a service that provides currency rates from single source.
 */
interface CurrencyDriverInterface
{
    /**
     * Returns array of currency rates.
     *
     * For example: <code>['USD' => 1, 'EUR' => '1.678']</code>.
     * To get currency rate from the past simply use $date in '2014-05-21' format for specific date.
     *
     * @param string $date
     *
     * @return array
     */
    public function getRates($date = null);

    /**
     * Returns base currency code.
     *
     * @return string
     */
    public function getBaseCurrency();
}
