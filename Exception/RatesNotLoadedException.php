<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Exception;

/**
 * This exception is thrown when we try to retrieve currency rates while it's not loaded.
 */
class RatesNotLoadedException extends \RuntimeException
{
}
