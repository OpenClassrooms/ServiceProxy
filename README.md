# Service Proxy
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e0840e44-8f14-4620-96cf-76300727e808/mini.png)](https://insight.sensiolabs.com/projects/e0840e44-8f14-4620-96cf-76300727e808)
[![Coverage Status](https://coveralls.io/repos/OpenClassrooms/ServiceProxy/badge.svg?branch=master&service=github)](https://coveralls.io/github/OpenClassrooms/ServiceProxy?branch=master)

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
