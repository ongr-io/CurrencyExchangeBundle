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
 * This class downloads exchange rates from openexchangerates.org.
 */
class OpenExchangeRatesDriver implements CurrencyDriverInterface
{
    /**
     * @var string
     */
    private $appId;

    /**
     * @var null|Client
     */
    private $httpClient;

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @param null|Client $httpClient
     */
    public function __construct(Client $httpClient = null)
    {
        $this->httpClient = $httpClient ? $httpClient : new Client();
    }

    /**
     * Downloads raw currency data.
     *
     * @return array
     */
    private function getRawData()
    {
        $request = $this->httpClient->get(
            'http://openexchangerates.org/api/latest.json',
            ['query' => ['app_id' => $this->getAppId()]]
        );

        return json_decode($request->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        $response = $this->getRawData();

        // Validate response.
        $valid = isset($response) && is_array($response) && isset($response['base']) && isset($response['rates']);
        if (!$valid) {
            throw new \UnexpectedValueException('Got invalid response');
        }

        // Check if base currency is correct.
        if ($response['base'] != $this->getBaseCurrency()) {
            throw new \UnexpectedValueException(
                sprintf(
                    'We expected to get values in base currency USD. Got %s',
                    $response['base']
                )
            );
        }

        return $response['rates'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrency()
    {
        return 'USD';
    }
}
