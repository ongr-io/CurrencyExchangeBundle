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

use ONGR\CurrencyExchangeBundle\Document\RatesObject;

class RatesObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getters and setters
     */
    public function testGettersAndSetters()
    {
        $name = 'USD';
        $value = 1.2543;
        $rate = new RatesObject();
        $rate->setName($name);
        $rate->setValue($value);
        $this->assertEquals($name, $rate->getName());
        $this->assertEquals($value, $rate->getValue());
    }
}
