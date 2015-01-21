========================
Currency Exchange Bundle
========================

This bundle is used to display currency in twig templates and basic conversion.

Installation
------------

Add the bunde to your ``composer.json`` file:

.. code-block:: yaml

    "ongr/currency-exchange-bundle": "dev-master"
..

Then run a composer update:

.. code-block:: bash

    composer.phar update
    # OR
    composer.phar update ongr/currency-exchange-bundle # to only update the bundle
..

Register it in ``AppKernel.php``.
Also you need to register the `TedivmStashBundle <https://github.com/tedious/TedivmStashBundle>`_ bundle. Please check the configuration_ section for more information.

.. code-block:: php

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            return [
            // ...
            new ONGR\CurrencyExchangeBundle\ONGRCurrencyExchangeBundle(),
            new Tedivm\StashBundle\TedivmStashBundle(),
            );
        }

        // ...
    }
..




.. _configuration:

Configuration
-------------

This bundle use `TedivmStashBundle <https://github.com/tedious/TedivmStashBundle>`_ for saving currencies into the cache.
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
        custom: european_central_bank_rates
..

Extension filters and functions
-------------------------------

- ``ongr-price`` **filter**

  Returns a formatted price according to the parameters given. This filter has optional parameters, which has to be provided in corresponding order.

  - **Parameters list**

    - price (mandatory argument) - price to format.
    - decimals - count of decimal digits. Default is 2.
    - toCurrency - currency (`ISO 4217 <http://en.wikipedia.org/wiki/ISO_4217>`_ format) which to convert price to.
    - fromCurrency - currency (`ISO 4217 <http://en.wikipedia.org/wiki/ISO_4217>`_ format) which to convert price from.
    - customFormat - custom format for printing price.

  - **Usage example**

    Using this code in twig template {{ product.price | ongr_price(2, "USD", "EUR", "%s dollars.") }} would convert Euros to Dollars.

- ``ongr_price_list`` **filter**

  Used to list formatted and converted prices from a single currency price. The list is specified by the ``currencies`` argument. This filter has optional parameters, which has to be provided in corresponding order.

  - **Parameters list**

    - price (mandatory argument) - price to formant
    - template - a template in which to render all of the currencies. By default ``ONGRCurrencyExchangeBundle:Price:priceList.html.twig`` is used.
    - fromCurrency - currency to convert price from, if it isn't specified the default one will be used.

  - **Usage example**

    All you need to do is add the line {{ <price_value> | ongr_price_list() }} to your twig template. If you'd like to use your template for listing prices, the arguments passed to the template are:

    - ``currencies`` - an array of currencies Each currency contains:

      - ``stringValue`` - a string representation of the converted price.
      - ``tla`` - three letter acronym code of the currency `ISO 4217 <http://en.wikipedia.org/wiki/ISO_4217>`_.

    Example twig template:

    .. code-block:: yaml

        {% for currency in currencies %}
            <div class="label label-default pull-right currency-{{ currency.tla }}-item">
                {{ currency.stringValue }}
            </div>
        {% endfor %}
    ..

- ``ongr_price_currency_list`` **function**

  Lists the available currencies as specified by the ``ongr_utils.twig.price_extension.to_print_list`` option, so the list can be used in a website.

  - **Parameters list**

    - template - a template in which to render all of the currencies. By default ``ONGRCurrencyExchangeBundle:Price:currencyList.html.twig`` is used.

  - **Usage example**

    To use it just add the line {{ ongr_price_currency_list() }}. If you'd like to use your template for listing prices, the arguments passed to the template are:

    - ``currencies`` - an array of currencies. Each currency contains:

      - ``stringValue`` - a string representation of the converted price.
      - ``tla`` - three letter acronym code of the currency `ISO 4217 <http://en.wikipedia.org/wiki/ISO_4217>`_.
      - ``default`` - (boolean) whether this currency is default one.

Using multi currency example
----------------------------

To use multi currency on your website you must have:

- Include these JS(browser storage, logger libraries) examples files in your project, for example:

  .. code-block:: php

      'CurrencyExchangeBundle/Resources/public/scripts/utils/browserStorage.js',
      'CurrencyExchangeBundle/Resources/public/scripts/utils/jquery.ongrCurrency.js',
      'CurrencyExchangeBundle/Resources/public/scripts/utils/log.js',
      'CurrencyExchangeBundle/Resources/public/scripts/widgets/jquery.ongr.currencyApply.js',
      'CurrencyExchangeBundle/Resources/public/scripts/widgets/jquery.ongr.currencySelector.js',
      'CurrencyExchangeBundle/Resources/public/scripts/main.js'
  ..

  Also you need to include these libraries in your project:

  .. code-block:: html

      <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
      <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
      <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  ..

  The minimum css style is required to hide not important prices:

  .. code-block:: css

      .currency-item {
        display: none;
      }
      .currency-item.active {
        display: inline-block;
      }

      .currency-status,
      .currency-info-price {
        display: inline-block;
      }
  ..

- Update ``app/config/config.yml`` file.
- List currencies to choose from using ``{{ ongr_price_currency_list() }}``. Example:

  .. code-block:: php

    <nav class="navbar navbar-default" role="navigation">
      {{ ongr_price_currency_list('AcmeBundle:inc:currencyList.html.twig') }}
    </nav>
  ..

  Example of ``currencyList.html.twig`` file:

  .. code-block:: php

    {% block currency %}
          <ul class="nav navbar-nav pull-right">
              <li class="dropdown pull-right">
                  <a href="#" data-toggle="dropdown" class="dropdown-toggle">Currency:
                  {% for currency in currencies %}
                      <span class="hide-not-important currency-item currency-{{ currency.tla }}">{{ currency.stringValue }}</span>
                  {% endfor %}
                  <span class="caret"  ></span></a>
                  <ul class="currency_list dropdown-menu">
                      {% for currency in currencies %}
                          <li><a {% if currency.default %} class="currency-default" {% endif %}data-currency="{{ currency.tla }}" href="#">{{ currency.stringValue }}</a></li>
                      {% endfor %}
                  </ul>
              </li>
          </ul>
    {% endblock %}
  ..

- List multiple currencies for each price using ``{{ <price_value>| ongr_price_list() }}``. Example:

  .. code-block:: php

      {{ product.price | ongr_price_list('AcmeBundle:inc:priceList.html.twig')}}
  ..

  Example of ``priceList.html.twig`` file:

  .. code-block:: php

      {% block price %}
          {% for currency in currencies %}
              <div class="label label-default pull-right hide-not-important currency-item currency-{{ currency.tla }}">
                  {{ currency.stringValue }}
              </div>
          {% endfor %}
      {% endblock %}
  ..

