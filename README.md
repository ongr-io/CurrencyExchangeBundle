ONGR Currency Exchange Bundle
===

This bundle provides an easy way to display price in multiple currencies. It
gives a solution to fetch and store current currency rates, to convert prices
and display them in Twig templates.

If you need any help, [stack overflow](http://http://stackoverflow.com/questions/tagged/ongr)
is the preffered and recommended way to ask ONGR support questions.

[![Stable Release](https://poser.pugx.org/ongr/currency-exchange-bundle/v/stable.svg)](https://packagist.org/packages/ongr/currency-exchange-bundle)
[![Build Status](https://travis-ci.org/ongr-io/CurrencyExchangeBundle.svg?branch=master)](https://travis-ci.org/ongr-io/CurrencyExchangeBundle)
[![Coverage Status](https://coveralls.io/repos/github/ongr-io/CurrencyExchangeBundle/badge.svg?branch=master)](https://coveralls.io/github/ongr-io/CurrencyExchangeBundle?branch=master)
[![Quality Score](https://scrutinizer-ci.com/g/ongr-io/CurrencyExchangeBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ongr-io/CurrencyExchangeBundle/?branch=master)

Documentation
---

The documentation of the bundle can be found in [Resources/doc/][2]

Installation
---
    
Follow 5 quick steps to setup this bundle.

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following
command to download the latest stable version of this bundle:

```bash
$ composer require ongr/currency-exchange-bundle
```

> This command requires you to have Composer installed globally, as explained in
> the [installation chapter][3] of the Composer documentation.

### Step 2: Enable the Bundle

Register bundles in `app/AppKernel.php`:

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            // ...
            new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
            new Tedivm\StashBundle\TedivmStashBundle(),          
            new ONGR\CurrencyExchangeBundle\ONGRCurrencyExchangeBundle(),    
        ];
    }

    // ...
}
```

> __Note:__ This bundle uses [TedivmStashBundle][5] for saving currencies into
the cache and [ONGRElasticsearchBundle][4] for currencies backup.
       
### Step 3: Update Elasticsearch Mapping  

This bundle provides Elasticsearch document to store currency rates. Add this
bundle to your ES manager's mapping to associate it:

```yml                
# app/config/config.yml
ongr_elasticsearch:
    # ...
    managers:
        default:
            # ...
            mappings:
                # ...
                - AppBundle
                - ONGRCurrencyExchangeBundle
```

### Step 4: Configure Cache Layer

Add configuration for `TedivmStashBundle`. To get started quickly you can use
filesystem driver here: 

```yml
# app/config/config.yml
stash:
    caches:
        files:
            drivers: [ FileSystem ]
            FileSystem:
                dirSplit:        2
                path:            %kernel.cache_dir%/stash
                filePermissions: 0660
                dirPermissions:  0770
                memKeyLimit:     20
```

> See TedivmStashBundle [documentation][5] for full configuration reference. 
  
### Step 5: Configure the Bundle

Configure the currencies you need in `config.yml` file.

```yml
# app/config/config.yml
ongr_currency_exchange:
    es_manager: default
    default_currency: EUR
    cache: stash.files_cache
    separators:
        decimal: ','
        thousands: '.'
    currencies:
        EUR: "%s €"    # %s stands for the price itself
        USD: "$ %s"
```

That's it for setup, jump to the next chapter to learn how to use this bundle.

Usage
---

The main parts of this bundle are a command to update currency rates and Twig
helpers to display price in various currencies.

Before converting prices you need to fetch the latest currency rates:

```bash
$ app/console ongr:currency:update
```

> __Tip:__ setup a cron job to update currencies daily in your production environment. 

Now you are ready to use currency conversion logic in your templates. Here is
a simple example how to convert currency:

```twig
<ul>
    <li>Price in default currency: {{ 123.123|ongr_price(2) }}
    <li>Price in US dollars: {{ 123.123|ongr_price(2, 'USD') }}
</ul>
```

In this example the number 2 represents the number of decimal points. It will print the following
information:

```
Price in default currency: 123.12 €
Price in US dollars: $ 123.12 
```

To learn more read about provided [Twig helpers][6] or check
[example currency switching][7] implementation.

License
---

This package is licensed under the MIT license. For the full copyright and
license information, please view the [LICENSE][1] file that was distributed
with this source code. 

[1]: LICENSE
[2]: Resources/doc/index.md
[3]: https://getcomposer.org/doc/00-intro.md
[4]: https://github.com/ongr-io/ElasticsearchBundle
[5]: https://github.com/tedious/TedivmStashBundle
[6]: Resources/doc/twig_helpers.md
[7]: Resources/doc/switching_currency.md
