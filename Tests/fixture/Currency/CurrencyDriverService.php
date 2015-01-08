<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\fixture\Currency;

use ONGR\CurrencyExchangeBundle\Currency\CurrencyDriverInterface;

/**
 * This class provides fixture for currency getter.
 */
class CurrencyDriverService implements CurrencyDriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        return [
            'EUR' => 1,
            'USD' => 1.3345,
            'LTL' => 3.4546,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCurrencyName()
    {
        return 'EUR';
    }
}
