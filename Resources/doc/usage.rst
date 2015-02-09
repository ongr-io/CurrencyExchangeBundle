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

**Add custom currency rates driver**

If you need to load conversions rates from another source you will have to create a CurrencyDriver and set it as the custom driver.
To create your custom driver you will have to implement ``ONGR\CurrencyExchangeBundle\Currency\CurrencyDriverInterface`` which define two methods:

 - getRates() - Returns array of currency rates. For example: <code>['USD' => 1, 'EUR' => '1.678']</code>.
 - getDefaultCurrencyName() - Returns the default currency name of rate provider . For example: 'EUR'.

  .. code-block:: php

      class CurrencyDriverService implements CurrencyDriverInterface
      {
          /**
           * {@inheritdoc}
           */
          public function getRates()
          {
              return [
                  'EUR' => 1,
                  'USD' => 1.3345,
                  'JPY' => 136.70,
              ];
          }

          /**
           * {@inheritdoc}
           */
          public function getDefaultCurrencyName()
          {
              return 'EUR';
          }
      }

Then define as a service.

  .. code-block:: php

      paramaters:
         acme_test.currency_rates_driver.class: Acme\TestBundle\CurrencyDrivers\CurrencyDriverService
      services:
         acme_test.currency_driver:
           class: %acme_test.currency_rates_driver.class%

Update the ``config.yml`` and run run a command ``app/console ongr:currency:update`` to see if everything is ok.
