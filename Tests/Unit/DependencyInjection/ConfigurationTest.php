<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Tests\Unit\DependencyInjection;

use ONGR\CurrencyExchangeBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * Unit test for configuration tree.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Simple test configuration.
     */
    public function testConfiguration()
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new Configuration(), [[]]);
        $this->assertEquals(
            $processedConfig,
            []
        );
    }
}
