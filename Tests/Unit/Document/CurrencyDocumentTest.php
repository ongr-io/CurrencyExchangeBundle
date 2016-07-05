<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Unit\Document;

use ONGR\CurrencyExchangeBundle\Document\CurrencyDocument;
use ONGR\CurrencyExchangeBundle\Document\RatesObject;
use ONGR\ElasticsearchBundle\Collection\Collection;

class CurrencyDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getters and setters
     */
    public function testGettersAndSetters()
    {
        $time = time();
        $rate = new RatesObject();
        $rate->setName('foo');
        $rates = new Collection([$rate]);
        $rate->setName('bar');
        $currency = new CurrencyDocument();
        $currency->setCreatedAt($time);
        $currency->setRates($rates);
        $currency->addRate($rate);
        $rates[] = $rate;
        $this->assertEquals($time, $currency->getCreatedAt());
        $this->assertEquals($rates, $currency->getRates());
    }
}
