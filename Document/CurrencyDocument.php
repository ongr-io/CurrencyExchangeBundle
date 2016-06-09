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
     * @ES\Property(type="date")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ES\Property(type="date", options={"format":"yyyy-MM-dd"})
     */
    private $creationDate;

    /**
     * CurrencyDocument constructor.
     */
    public function __construct()
    {
        $this->rates = new Collection();
        $this->createdAt = new \DateTime();
        $this->creationDate = date('Y-m-d');
    }

    /**
     * @return RatesObject
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param RatesObject $rates
     */
    public function setRates($rates)
    {
        $this->rates = $rates;
    }

    /**
     * @param RatesObject $rate
     */
    public function addRate($rate)
    {
        $this->rates[] = $rate;
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

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
