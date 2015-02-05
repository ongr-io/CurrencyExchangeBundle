<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Command;

use GuzzleHttp\Exception\ConnectException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command which store currency rates in ES.
 */
class UpdateEsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ongr:currency:update')
            ->setDescription('Currency Update');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $data = $this->getContainer()->get('ongr_currency_exchange.currency_rates_service');
            $data->reloadRates();
            $output->writeln(sprintf('<info>Currency rates updated</info>'));
        } catch (ConnectException $e) {
            $output->writeln(
                sprintf(
                    '<error>Error ocurred during update. </error> <comment>`%s`</comment>',
                    $e->getMessage()
                )
            );
        }
    }
}
