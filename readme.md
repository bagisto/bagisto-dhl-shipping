# Introduction

DHL Shipping Add-on provides DHL Shipping methods for shipping the product.

It packs in lots of demanding features that allows your business to scale in no time:

- The admin can enable or disable the DHL Shipping method.
- The admin can set the DHL shipping method name that will be shown from the front side.
- The admin can allow country wise shipping from backend.
- The admin can define the allowed methods and weight units.
- The admin can set content type
- Tax rate can be calculated based on DHL shipping

## Requirements:

- **Bagisto**: v1.3.1

## Installation with composer:
- Run the following command
```
composer require bagisto/bagisto-dhl-shipping
```

- Run these commands below to complete the setup
```
composer dump-autoload
```

```
php artisan route:cache
php artisan config:cache
```

## Installation without composer:

- Unzip the respective extension zip and then merge "packages" folders into project root directory.
- Goto config/app.php file and add following line under 'providers'

```
Webkul\DHLShipping\Providers\DHLShippingServiceProvider::class
```

- Goto composer.json file and add following line under 'psr-4'

```
 "Webkul\\DHLShipping\\": "packages/Webkul/DHLShipping/src"
```

- Run these commands below to complete the setup

```
composer dump-autoload
```

```
php artisan route:cache
```

```
php artisan config:cache
```

> now execute the project on your specified domain.
