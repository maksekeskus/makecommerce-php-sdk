# MakeCommerce SDK 2.0

### Installation

### Composer
``` json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/maksekeskus/makecommerce-php-sdk"
        }
    ],
    "require": {
        "maksekeskus/makecommerce-php-sdk": "1.0.0"
    }
}
```

### Prebuilt packages

Download the packaged library from the repository [releases]
(https://github.com/maksekeskus/makecommerce-php-sdk/releases/).

Unpack it into your project folder (i.e. /htdocs/myshop/ )
and include the libarary file.

To get your API keys, please visit https://merchant.test.maksekeskus.ee/ or https://merchant.maksekeskus.ee/

# Example

### Get Shop configuration

``` php
<?php

require __DIR__ . '/makecommerce-php-sdk-1.0.0/vendor/autoload.php'; //Comment this line out if you are using Composer to build your project

use MakeCommerce\MakeCommerceClient;

$makecommerce = new MakeCommerceClient(
    'YOUR_SHOP_ID',
    'YOUR_SECRET_ID',
    'YOUR_PLATFORM',
    'YOUR_PLATFORM_VERSION',
    true // test environment, defaults to false (production)
);

$data = $makecommerce->getShopConfiguration();

print "<pre>";
print_r($data);
print "</pre>";

```
See more examples on https://developer.makecommerce.net/
