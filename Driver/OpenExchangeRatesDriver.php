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
    const REQUEST_URI = 'http://openexchangerates.org/api/latest.json?app_id=%s';

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
     * Downloads raw currency data.
     *
     * @return array
     */
    private function getRawData()
    {
        $ch = curl_init(sprintf(self::REQUEST_URI, $this->appId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (($json = curl_exec($ch)) === false) {
            return [];
        }
        curl_close($ch);

        return json_decode($json, true);
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
