# Service Proxy
[![Build Status](https://travis-ci.org/OpenClassrooms/ServiceProxy.svg?branch=master)](https://travis-ci.org/OpenClassrooms/ServiceProxy)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e0840e44-8f14-4620-96cf-76300727e808/mini.png)](https://insight.sensiolabs.com/projects/e0840e44-8f14-4620-96cf-76300727e808)
[![Coverage Status](https://codecov.io/gh/OpenClassrooms/ServiceProxy/branch/master/graph/badge.svg)](https://codecov.io/gh/OpenClassrooms/ServiceProxy)

Service Proxy is a library that provides functionality to manage technical code over a class:
- Cache management
- Transactional context
- Security access (not implemented yet)
- Events (not implemented yet)
- Logs (not implemented yet)

## Installation
The easiest way to install ServiceProxy is via [composer](http://getcomposer.org/).

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
### Instantiation

If you plan to use ServiceProxy in a Symfony2 project, check out the [ServiceProxyBundle](http://github.com/openclassrooms/ServiceProxyBundle).
The bundle provides an easy configuration option for this library.

#### Basic
##### Factory
```php
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;

$serviceProxyFactory = $this->getServiceProxyFactory();
$proxy = $serviceProxyFactory->createProxy(new Class());
```

##### Builder

```php
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\Transaction\PDOTransactionAdapter;

$proxy = $this->getServiceProxyBuilder()
              ->create(new Class())
              ->withCache(new CacheProviderDecorator(new ArrayCache()))
              ->withTransaction(new PDOTransactionAdapter(new \PDO(/* ...your config options */)))
              ->build();
```

#### Custom
See [ProxyManager](https://github.com/Ocramius/ProxyManager)
##### Factory
```php
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;

$serviceProxyFactory = $this->getServiceProxyFactory();
$serviceProxyFactory->setCacheProvider(new CacheProviderDecorator(new ArrayCache()));
$serviceProxyFactory->setProxyFactory($this->buildProxyFactory(new Configuration()));
$proxy = $serviceProxyFactory->createProxy(new Class());
```

##### Builder
```php
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\Transaction\PDOTransactionAdapter;

$proxyBuilder = $this->getServiceProxyBuilder();
$proxyBuilder->setProxyFactory($this->buildProxyFactory(new Configuration()));

$proxy = $proxyBuilder->create(new Class())
             ->withCache(new CacheProviderDecorator(new ArrayCache()))
             ->withTransaction(new PDOTransactionAdapter(new \PDO(/* ...your config options */)))
             ->build();
```

### Cache
@Cache annotation allows cache management.

```php
namespace MyProject\AClass;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

class AClass
{
    /**
     * @Cache
     *
     * @return mixed
     */
    public function aMethod($aParameter)
    {
        // do things
        
        return $data;
    }
}
```
The id is equal to: ```md5('MyProject\AClass::aMethod::'.serialize($aParameter))``` and the TTL is the default.

#### Other options:
##### Lifetime:
```php
/**
 * @Cache(lifetime=1000)
 * Add a TTL of 1000 seconds
 */
```
##### Id (key):
```php
/**
 * @Cache(id="'key'")
 * Set the id to "key"
 */
```
Supports Symfony ExpressionLanguage, for example:
```php
/**
 * @Cache(id="'key' ~ aParameter.field")
 * Set the id to 'key'.$aParameter->field
 */
```
##### Namespace:
```php
/**
 * @Cache(namespace="'namespace'")
 * Add a namespace to the id with a namespace id equals to "namespace" 
 */
```
Supports Symfony ExpressionLanguage, for example:
```php
/**
 * @Cache(namespace="'namespace' ~ aParameter.field")
 * Add a namespace to the id with a namespace id equals to 'namespace'.$aParameter->field
 */
```

### Transaction

`@Transaction` annotation will start a transaction (if not already started) at the beginning, commit at the end, and 
rollback if an error is thrown.

```php
namespace MyProject\AClass;

use OpenClassrooms\ServiceProxy\Annotations\Transaction;

class AClass
{
    /**
     * @Transaction
     *
     * @return mixed
     */
    public function execute($toSave)
    {
        $this->prepareSave($toSave);
        
        return $toSave;
    }
}
```

#### Handle conflicts

The `@Transaction` will throw by default a `OpenClassrooms\ServiceProxy\Exceptions\TransactionConflictException` if a 
conflict has been encountered while committing.

This exception can be overridden this way:

```php
namespace MyProject\AClass;

use OpenClassrooms\ServiceProxy\Annotations\Transaction;

class AClass
{
    /**
     * @Transaction(onConflictThrow="\MyProject\AClass\Exception\MyFunctionalException")
     */
}
```

## Known limitations
- a class can not have different cache providers

## Acknowledgments  
This library is based on [Ocramius\ProxyManager](https://github.com/Ocramius/ProxyManager).
