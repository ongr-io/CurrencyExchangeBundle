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

use GuzzleHttp\Client;

/**
 * This class downloads exchange rates from http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml.
 */
class EcbDriver implements CurrencyDriverInterface
{
    /**
     * @var null|Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $url;

    /**
     * @param null|Client $httpClient
     * @param string      $url
     */
    public function __construct(Client $httpClient, $url)
    {
        $this->httpClient = $httpClient;
        $this->url = $url;
    }

    /**
     * Downloads raw currency data.
     *
     * @return array
     */
    private function getRawData()
    {
        $request = $this->httpClient->get($this->url);

        try {
            $xml = simplexml_load_string((string)$request->getBody(), 'SimpleXMLElement');
        } catch (\Exception $e) {
            throw new \UnexpectedValueException('Got invalid response');
        }

        return $xml;
    }

    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        $rates = [];
        $response = $this->getRawData();

        $valid = isset($response) && isset($response->Cube->Cube->Cube);
        if (!$valid) {
            throw new \UnexpectedValueException('Got invalid response');
        }

        $data = $response->xpath('//gesmes:Envelope/*[3]/*');
        foreach ($data[0]->children() as $child) {
            $code = (string)$child->attributes()->currency;
            $rate = (float)$child->attributes()->rate;
            $rates[$code] = $rate;
        }

        return $rates;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrency()
    {
        // Default base currency of The European Central Bank
        return 'EUR';
    }
}
