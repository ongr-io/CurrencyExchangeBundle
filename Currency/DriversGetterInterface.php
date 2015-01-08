<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Currency;

/**
 * this interface defines structure for currency rates download driver
 */
interface DriversGetterInterface
{
    /**
     * Returns array of currency rates. For example:
     * <code>['USD' => 1, 'EUR' => '1.678']</code>
     *
     * @return array
     */
    public function getRates();

    /**
     * Returns default currency name
     *
     * @return string
     */
    public function getDefaultCurrencyName();
}
