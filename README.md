# MakeCommerce SDK 2.0

## Set up:

Copy the contents of the repository to your desired location (preferably in the same folder where the implementation is located)

### 1. Install Dependencies
```bash
  composer install
```

This command will read the composer.json file and download all the dependencies into a vendor folder

### 2. Integrate the SDK

After the dependencies are installed, you need to include the Composer autoloader in your project. This file automatically loads all the classes from the SDK and its dependencies, so you don't have to manually include each file. Add the following line to the main file where you handle the payment logic or to a central bootstrap file in your application:

```php
require __DIR__ . '/vendor/autoload.php';
```


### 3. Use the Main SDK Class
The core functionality of the SDK is located in the MakeCommerceClient class. You can now use this class in your code to start implementing your payment logic. 

```php

require '../vendor/autoload.php';

use MakeCommerce\MakeCommerceClient;

$makecommerce = new MakeCommerceClient(
    'YOUR_SHOP_ID',
    'YOUR_SECRET_ID',
    'YOUR_PLATFORM',
    'YOUR_PLATFORM_VERSION',
    true // True for TEST env. By default, set to FALSE for LIVE env
);
```

To get your API keys, please visit https://merchant.test.maksekeskus.ee/ or https://merchant.maksekeskus.ee/


# Examples
Once you have an instance of the `MakeCommerceClient` class, you can access its methods to interact with the API. It's a best practice to wrap your API calls in a `try...catch` block to handle any potential errors gracefully.

Here are few examples to get you started:
### Get Shop configuration

``` php
<?php

require '../vendor/autoload.php';

use MakeCommerce\MakeCommerceClient;

$makecommerce = new MakeCommerceClient(
    'YOUR_SHOP_ID',
    'YOUR_SECRET_ID',
    'YOUR_PLATFORM',
    'YOUR_PLATFORM_VERSION',
    true
);
try {
    $getShopConfig = $makecommerce->getShopConfiguration();
} catch (\MakeCommerce\MCException $e) {
    echo $e->getMessage();
}
```

### Create Transaction

``` php
<?php

require '../vendor/autoload.php';

use MakeCommerce\MakeCommerceClient;

$makecommerce = new MakeCommerceClient(
    'YOUR_SHOP_ID',
    'YOUR_SECRET_ID',
    'YOUR_PLATFORM',
    'YOUR_PLATFORM_VERSION',
    true
);
try {
    $createTrx = $makecommerce->createTransaction([
        'transaction' => ['amount' => '98.15', 'currency' => 'EUR'],
        'customer' => ['ip' => '89.40.104.147']
    ]);
} catch (\MakeCommerce\MCException $e) {
    echo $e->getMessage();
}
```
