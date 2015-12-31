<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * Currency rates data.
 *
 * @ES\Object()
 */
class RatesObject
{
    /**
     * @var string
     *
     * @ES\Property(type="string")
     */
    public $name;

    /**
     * @var float
     *
     * @ES\Property(type="float")
     */
    public $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
