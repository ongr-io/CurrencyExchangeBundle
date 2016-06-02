# Configuration

A lot of things can be defined in the configuration of the bundle, here we provide the full configuration with comments on what can be defined:

```yaml

# app/config/config.yml
ongr_currency_exchange:
    es_manager: ongr           # ES manager to use (defaults to `default`)
    default_currency: EUR      # by default currency is EUR
    currency_sign: €           # the sign is also defaulted to €
    cache: stash.memcache      # caching system to use, this field is required
    separators:                # separators default to `,` for decimal and `.` for thousands
        decimal: ','
        thousands: '.'
    currencies:                # here you define all the currencies you will be using
        EUR: "%s €"
        USD: "$ %s"
    # here you can define the templates that will be used by twig functions, defaults will be used if not set
    templates:
        currency_list: AppBundle::currency_list.html.twig
        price_list: AppBundle::price_list.html.twig
    # here you can define a driver that provides all the currency rates, default is ongr_currency_exchange.ecb_driver
    driver: ongr_currency_exchange.open_exchange_driver
    open_exchange_rates_api_id: XXXX   # this parameter is only needed when using open_exchange_driver

```