Setup
=====

Step 1: Install Currency Exchange bundle
----------------------------------------

Add the bundle to your ``composer.json`` file:

.. code-block:: yaml

    "ongr/currency-exchange-bundle": "dev-master"
..

Then run a composer update:

.. code-block:: bash

    composer.phar update
    # OR
    composer.phar update ongr/currency-exchange-bundle # to only update the bundle
..

Step 1: Enable Currency Exchange bundle
---------------------------------------

Register it in ``AppKernel.php``.
Also you need to register the `TedivmStashBundle <https://github.com/tedious/TedivmStashBundle>`_ bundle.

.. code-block:: php

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            return [
                // ...
                new ONGR\CurrencyExchangeBundle\ONGRCurrencyExchangeBundle(),
                new Tedivm\StashBundle\TedivmStashBundle(),
            ];
        }

        // ...
    }
..

Step 3: Add configuration
-------------------------

This bundle use `TedivmStashBundle <https://github.com/tedious/TedivmStashBundle>`_ for saving currencies into the cache.
To reload currency rates you need remove cache dir, or setup configuration to use an APC cache which has a TTL option.
(See the `Stash documentation <http://stash.tedivm.com>`_ for more information on using the cache service.)
Stash basic configuration:

.. code-block:: yaml

    # app/config/config.yml
    stash:
        caches:
            files:
                drivers: [ FileSystem ]
                FileSystem:
                    dirSplit:               2
                    path:                   %kernel.cache_dir%/stash
                    filePermissions:        0660
                    dirPermissions:         0770
                    memKeyLimit:            20
..

Currency Exchange configuration:

You will have to define your api id in the config.yml file of you environment.
A free api id is available `here <https://openexchangerates.org/signup/free>`_.
Please check the the list of available `currencies <https://openexchangerates.org/currencies>`_.

You can add the currencies you need in ``config.yml`` file. Display map maps currency with its format. ``%s`` stands for the price itself.


.. code-block:: yaml

    # app/config/config.yml
    ongr_currency_exchange:
        default: EUR
        separators:
            decimal: ','
            thousands: '.'
        currencies:
            EUR: "%s €"
            USD: "$ %s"
            NOK: "NOK %s"
        exchange:
            cache: stash.files_cache
            driver:
                open_exchange_rates:
                    app_id: 123456 #Your https://openexchangerates.org api key.
..

There is a possibility to use The European Central Bank.

.. code-block:: yaml

    driver:
        custom: ongr_currency_exchange.european_central_bank_rates_driver
..