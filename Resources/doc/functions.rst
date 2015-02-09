Extension filters and functions
-------------------------------

- ``ongr_price`` **filter**

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
