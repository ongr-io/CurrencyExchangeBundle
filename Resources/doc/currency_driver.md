Currency Rates Driver
===

This bundle comes with two currency rate providers:

### Open Exchange Rates
                       
Before using this driver you need to create your free account at [openexchangerates.org][3].
                       
To use this driver you need to set driver ID and Open Exchange Rates API ID in
your config:

```yml
ongr_currency_exchange:
    # ...
    driver: ongr_currency_exchange.open_exchange_driver
    open_exchange_rates_api_id: XXXXXX
```

> The list of available currencies can be found [here][1].

### European Central Bank

To use this driver set `ongr_currency_exchange.ecb_driver` in your config.

> The list of available currencies can be found [here][2].

Adding Custom Currency Rates Driver
---

If you are going to use currency rates provider other than provided by this
bundle you can write a driver for it by implementing `CurrencyDriverInterface`
interface.

See example below:

```php
// src/AppBundle/Service/CurrencyDriver.php

namespace AppBundle\Service;

use ONGR\CurrencyExchangeBundle\Currency\CurrencyDriverInterface;

class CurrencyDriver implements CurrencyDriverInterface
{
    public function getRates()
    {
        return [
            'EUR' => 1,
            'USD' => 1.3345,
            'JPY' => 136.70,
        ];
    }
    
    public function getDefaultCurrencyName()
    {
        return 'EUR';
    }
}
```

As you can see currency driver is required to implement only two methods. One
method returns a list of available currency rates, and other returns the name
of base currency to which over currencies are compared. 

Once you have implemented custom currency driver, register it as a service:

```yml
# app/config/services.yml
services:
    app.currency_driver:
        class: AppBundle\Service\CurrencyDriver
```

Last, set new driver in config:

```yml
# app/config/config.yml
ongr_currency_exchange:
    # ...
    driver:
        service: app.currency_driver
```

Now you can test your custom driver by running currency update command:

```bash
$ app/console ongr:currency:update
```

[1]: https://openexchangerates.org/currencies
[2]: https://www.ecb.europa.eu/stats/exchange/eurofxref/html/index.en.html
[3]: https://openexchangerates.org/signup/free
