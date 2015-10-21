# Service Proxy

Service Proxy provide an easy way to add technical implementation to functional code using annotations
- transaction
- cache
- security
- event
- log

## Installation
The easiest way to install DoctrineCacheExtension is via [composer](http://getcomposer.org/).

Create the following `composer.json` file and run the `php composer.phar install` command to install it.

```json
{
    "require": {
        "openclassrooms/service-proxy": "*"
    }
}
```
```php
<?php
require 'vendor/autoload.php';

use OpenClassrooms\ServiceProxy\ServiceProxy;

//do things
```
<a name="install-nocomposer"/>

## Usage

``` php
$factory =  new ServiceProxyFactory();
$proxy = $factory->createProxy('classname');
```
