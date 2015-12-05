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

Step 2: Enable Currency Exchange bundle
---------------------------------------

Register it in ``AppKernel.php``.
Also you need to register the `TedivmStashBundle <https://github.com/tedious/TedivmStashBundle>`_ bundle and
`ONGR Elasticsearch Bundle <https://github.com/ongr-io/ElasticsearchBundle>`_

.. code-block:: php

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            return [
                // ...
                new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
                new ONGR\CurrencyExchangeBundle\ONGRCurrencyExchangeBundle(),
                new Tedivm\StashBundle\TedivmStashBundle(),
            ];
        }

        // ...
    }
..

Step 3: Add configuration
-------------------------

This bundle use `TedivmStashBundle <https://github.com/tedious/TedivmStashBundle>`_ for saving currencies into the cache
and `ONGR Elasticsearch Bundle <https://github.com/ongr- io/ElasticsearchBundle>`_ for currencies backup.
To reload rates you need to set up a cron job and run a command `app/console ongr:currency:update` daily. It will save
currency rates into the ES and update the cache.

Elasticsearch basic configuration:

.. code-block:: yaml

    ongr_elasticsearch:
        connections:
            default:
                hosts:
                    - 127.0.0.1:9200
                index_name: acme
                settings:
                    index:
                        refresh_interval: -1
                    number_of_replicas: 1
        managers:
            default:
                connection: default
                mappings:
                    - ONGRCurrencyExchangeBundle
..

Please note that you need to run these two commands:
- ``app/console ongr:es:index:create`` - to create an index
- ``app/console ongr:es:mapping:update`` - to create a type

More information about Ongr Elasticsearch `bundle <http://ongr.readthedocs.org/en/latest/components/ElasticsearchBundle/>`_


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

This bundle comes with two currency rates drivers:

- ongr_currency_exchange.open_exchange_driver

  You will have to define your api id in the config.yml file of you environment.
  A free api id is available `here <https://openexchangerates.org/signup/free>`_.
  Please check the the list of available `currencies <https://openexchangerates.org/currencies>`_.

- ongr_currency_exchange.ecb_driver

  `List of available currencies <https://www.ecb.europa.eu/stats/exchange/eurofxref/html/index.en.html>`_

Add the currencies you need in ``config.yml`` file. Display maps currency with its format. ``%s``
stands for the price itself.


.. code-block:: yaml

    # app/config/config.yml
    ongr_currency_exchange:
        es_manager: default
        default_currency: EUR
        cache: stash.files_cache
        separators:
            decimal: ','
            thousands: '.'
        currencies:
            EUR: "%s €"
            USD: "$ %s"
        driver:
            service: ongr_currency_exchange.open_exchange_driver
            setters:
                setAppId: ['8b447edc6e0e4661b584772ab6aa7611']
..

