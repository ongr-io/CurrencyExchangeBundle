<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Currency\Drivers;

use ONGR\CurrencyExchangeBundle\Currency\CurrencyDriverInterface;

/**
 * This class downloads exchange rates from http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml.
 */
class EcbDriver implements CurrencyDriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        $rates = [];
        $xml = @simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        $data = $xml->xpath('//gesmes:Envelope/*[3]/*');
        foreach ($data[0]->children() as $child) {
            $code = (string)$child->attributes()->currency;
            $rate = (float)$child->attributes()->rate;
            $rates[$code] = $rate;
        }

        return $rates;
    }

    /**
     * Default base currency of The European Central Bank.
     *
     * {@inheritdoc}
     */
    public function getDefaultCurrencyName()
    {
        return 'EUR';
    }
}
