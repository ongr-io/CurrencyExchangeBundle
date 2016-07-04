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
use ONGR\ElasticsearchBundle\Collection\Collection;

/**
 * Stores currency rates.
 *
 * @ES\Document(type="currency")
 */
class CurrencyDocument
{
    /**
     * @var RatesObject
     *
     * @ES\Embedded(class="ONGRCurrencyExchangeBundle:RatesObject", multiple=true)
     */
    private $rates;

    /**
     * @var \DateTime
     *
     * @ES\Property(type="date", options={"format":"strict_date"})
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ES\Property(type="date")
     */
    private $createdAt;

    /**
     * CurrencyDocument constructor.
     */
    public function __construct()
    {
        $this->rates = new Collection();
        $this->date = date('Y-m-d');
        $this->createdAt = new \DateTime();
    }

    /**
     * @return Collection
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param Collection $rates
     */
    public function setRates($rates)
    {
        $this->rates = $rates;
    }

    /**
     * @param RatesObject|array $rate
     */
    public function addRate($rate)
    {
        $this->rates[] = $rate;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
