<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CurrencyExchangeBundle\Twig;

use ONGR\CurrencyExchangeBundle\Exception\UndefinedCurrencyException;
use ONGR\CurrencyExchangeBundle\Service\CurrencyExchangeService;
use ONGR\CurrencyExchangeBundle\Tests\Unit\DependencyInjection\ONGRCurrencyExchangeExtensionTest;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class for displaying changed currencies.
 */
class PriceExtension extends \Twig_Extension implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Extension name
     */
    const NAME = 'price_extension';

    /**
     * @var string Currency sign.
     */
    private $currencySign;

    /**
     * @var string Decimal point separator.
     */
    private $decPointSeparator;

    /**
     * @var string Thousands separator.
     */
    private $thousandsSeparator;

    /**
     * @var null Currency.
     */
    private $currency = null;

    /**
     * @var CurrencyExchangeService Service which provide currency exchange rates.
     */
    private $currencyService = null;

    /**
     * @var array Contains formats for each currency.
     */
    private $formatsMap;

    /**
     * @var array Array of currencies to be listed in twig while using the "list" functions.
     */
    private $toListMap;

    /**
     * @var string String containing the default currency_list template
     */
    private $currency_list;

    /**
     * @var string String containing the default price_list template
     */
    private $price_list;

    /**
     * Constructor.
     *
     * @param string $currencySign
     * @param string $decPointSeparator
     * @param string $thousandsSeparator
     * @param string $currency_list
     * @param string $price_list
     * @param array  $currency
     * @param array  $formatsMap
     * @param array  $toListMap
     */
    public function __construct(
        $currencySign,
        $decPointSeparator,
        $thousandsSeparator,
        $currency_list,
        $price_list,
        $currency = null,
        $formatsMap = [],
        $toListMap = []
    ) {
        $this->currencySign = $currencySign;
        $this->decPointSeparator = $decPointSeparator;
        $this->thousandsSeparator = $thousandsSeparator;
        $this->currency = $currency;
        $this->formatsMap = $formatsMap;
        $this->toListMap = $toListMap;
        $this->currency_list = $currency_list;
        $this->price_list = $price_list;

    }

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        $functions = [];
        $functions[] = new \Twig_SimpleFilter(
            'ongr_price',
            [$this, 'getFormattedPrice'],
            ['is_safe' => ['html']]
        );
        $functions[] = new \Twig_SimpleFilter(
            'ongr_price_list',
            [$this, 'getPriceList'],
            [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]
        );

        return $functions;
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'ongr_currency_list',
                [$this, 'getCurrencyList'],
                [
                    'needs_environment' => true,
                    'is_safe' => [
                        'html',
                    ],
                ]
            ),
        ];
    }

    /**
     * Returns formatted price.
     *
     * @param float  $price
     * @param int    $decimals
     * @param string $toCurrency
     * @param string $fromCurrency
     * @param string $customFormat
     * @param string $date
     *
     * @return string
     */
    public function getFormattedPrice(
        $price,
        $decimals = 0,
        $toCurrency = null,
        $fromCurrency = null,
        $customFormat = null,
        $date = ''
    ) {
        $targetCurrency = $toCurrency ? $toCurrency : $this->currency;

        if ($targetCurrency) {
            if (isset($this->currencyService)) {
                try {
                    $price = $this->currencyService->calculateRate($price, $targetCurrency, $fromCurrency, $date);
                } catch (UndefinedCurrencyException $ex) {
                    $this->logger && $this->logger->error(
                        'Got undefined currency on PriceExtension',
                        ['message' => $ex->getMessage()]
                    );

                    return '';
                }
            } else {
                $this->logger && $this->logger->error('Currency service is undefined on PriceExtension');

                return '';
            }
        }

        if (abs($price) > floor(abs($price))) {
            $decimals = 2;
        }

        $formattedPrice = number_format($price, $decimals, $this->decPointSeparator, $this->thousandsSeparator);

        $printFormat = null;
        if ($customFormat) {
            $printFormat = $customFormat;
        } elseif (isset($this->formatsMap[$targetCurrency])) {
            $printFormat = $this->formatsMap[$targetCurrency];
        }

        if ($printFormat) {
            return sprintf($printFormat, $formattedPrice);
        } else {
            return "{$formattedPrice} {$this->currencySign}";
        }
    }

    /**
     * Returns specified prices formatted by a specified template.
     *
     * @param \Twig_Environment $environment
     * @param int               $price
     * @param string            $template
     * @param null              $fromCurrency
     * @param string            $date
     *
     * @return string
     */
    public function getPriceList(
        $environment,
        $price,
        $template = '',
        $fromCurrency = null,
        $date = ''
    ) {
        if ($template == '') {
            $template = $this->price_list;
        }
        $values = [];
        foreach ($this->toListMap as $targetCurrency) {
            $values[] = [
                'value' => $this->getFormattedPrice($price, 0, $targetCurrency, $fromCurrency, '', $date),
                'currency' => strtolower($targetCurrency),
            ];
        }

        return $environment->render(
            $template,
            ['prices' => $values]
        );
    }

    /**
     * Returns all available currencies.
     *
     * @param \Twig_Environment $environment
     * @param string            $template
     *
     * @return string
     */
    public function getCurrencyList($environment, $template = '')
    {
        if ($template == '') {
            $template = $this->currency_list;
        }
        $values = [];
        foreach ($this->toListMap as $targetCurrency) {
            $values[] = [
                'value' => $targetCurrency,
                'code' => strtolower($targetCurrency),
                'default' => (strcasecmp($targetCurrency, $this->currency) == 0) ? true : false,
            ];
        }

        return $environment->render(
            $template,
            ['currencies' => $values]
        );
    }

    /**
     * Returns name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param null $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param CurrencyExchangeService $currencyService
     */
    public function setCurrencyExchangeService($currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * @param array $toListMap
     */
    public function setToListMap($toListMap)
    {
        $this->toListMap = $toListMap;
    }

    /**
     * @param array $formatsMap
     */
    public function setFormatsMap($formatsMap)
    {
        $this->formatsMap = $formatsMap;
    }
}
