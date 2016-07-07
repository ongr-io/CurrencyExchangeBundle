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
     * @var string
     */
    private $url;

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
     * @param string $url
     */
    public function __construct(Client $httpClient, $url)
    {
        $this->httpClient = $httpClient;
        $this->url = $url;
    }

    /**
     * Downloads raw currency data.
     *
     * @param string $endpoint
     *
     * @return array
     */
    private function getRawData($endpoint = 'latest.json')
    {
        $url = $this->url. $endpoint . '?' . http_build_query(['app_id' => $this->getAppId()]);
        $request = $this->httpClient->request(
            'GET',
            $url
        );

        return json_decode($request->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getRates($date = null)
    {
        if ($date) {
            $endpoint = sprintf('historical/%s.json', $date);
        } else {
            $endpoint = 'latest.json';
        }

        $response = $this->getRawData($endpoint);

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
