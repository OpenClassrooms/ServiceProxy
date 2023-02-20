# Service Proxy
[![Build Status](https://travis-ci.org/OpenClassrooms/ServiceProxy.svg?branch=master)](https://travis-ci.org/OpenClassrooms/ServiceProxy)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e0840e44-8f14-4620-96cf-76300727e808/mini.png)](https://insight.sensiolabs.com/projects/e0840e44-8f14-4620-96cf-76300727e808)
[![Coverage Status](https://codecov.io/gh/OpenClassrooms/ServiceProxy/branch/master/graph/badge.svg)](https://codecov.io/gh/OpenClassrooms/ServiceProxy)

Service Proxy is a library that provides functionality to manage technical code over a class:
- Transactional context
- Security access
- Cache management
- Events
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

## Concepts
### Handlers
Handlers are used by interceptors to manage the infrastructure code.
To be able to use built-in interceptors, you need to implement the built-in handlers contracts.

- All handles need to implement `OpenClassrooms\ServiceProxy\Contract\AnnotationHandler`.
- Each handler must have a unique name, you can use the `getName` method to return it.
- Each handler must return if it's the default handler, you can use the `isDefault` method to return it.
- You can't have two handlers with the same name by annotation.
- You can have only one default handler, by annotation.
- If you have only one handler by annotation, it will be the default one.

**example:**

```php
use OpenClassrooms\ServiceProxy\Annotation\Cache;

/**
 * @Cache(handler="in_memory")
 * to select the in_memory handler
 */

 ```

### Interceptors

Interceptors are used as decorators to react to the method execution. 
for example using `@Cache` annotation, is a condition to enable the cache interceptor.

There is two types of interceptors:
#### Prefix interceptors :
Interceptors that are called before the method execution, they must implement `OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor`
Two methods are called:
- `prefix` : called before the method execution. Should return instance of `OpenClassrooms\ServiceProxy\Interceptor\Response\Response`.
- `supportsPrefix` : called to know if the interceptor should be called, for example in the case of the cache interceptor, it will check that the method has the `@Cache` annotation.
- `getPrefixPriority` : called to know the priority of the interceptor, the higher the priority, the earlier the interceptor will be called.

#### Suffix interceptors :
Interceptors that are called after the method execution, even if an exception is thrown, they must implement `OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor`
Two methods are called:
- `suffix` : called after the method execution even if an exception is thrown. should return instance of `OpenClassrooms\ServiceProxy\Interceptor\Response\Response`.
- `supportsSuffix` : called to know if the interceptor should be called, for example in the case of the cache
  interceptor, it will check that the method has the `@Cache` annotation.
- `getSuffixPriority` : called to know the priority of the interceptor, the higher the priority, the earlier the interceptor will be called.

#### Handling exceptions
If you want to react to an exception thrown by the method, you can check for the exception in the suffix interceptor.
- To check `$instance->method()->threwException()`
- To get the exception (null if no exception was thrown) `$instance->method()->getException()`
- To get the return value (null in case of an exception) `$instance->method()->getReturnValue()`

#### Early return
- If a prefix interceptor returns a response with early return parameter set to `true` ex: `Response($data, true)`, the method won't be executed and the suffix interceptors won't be called.
- If a suffix interceptor returns a response with early return parameter set to `true`, the exception won't be thrown, in the case of a method that throws an exception.

You can create your own interceptors, or use the built-in ones:

## Usage
### Instantiation

#### Symfony
Check out the [ServiceProxyBundle](http://github.com/openclassrooms/ServiceProxyBundle).
The bundle provides an easy configuration option for this library.

#### Manual
##### Example
First implement the handlers

```php
use OpenClassrooms\ServiceProxy\Contract\CacheHandler;

class InMemoryCacheHandler implements CacheHandler
{
    public function getName(): string
    {
        return 'in_memory';
    }
    ...
}

class RedisCacheHandler implements CacheHandler
{
    public function getName(): string
    {
        return 'redis';
    }
    ...
}
```

Then you can inject the handlers into the interceptors:

```php
$cacheInterceptor = new CacheInterceptor([new ArrayCacheHandler(), new RedisCacheHandler()]);
$prefixInterceptors = [
    $cacheInterceptor,
    new EventInterceptor([/* event handlers */]),
    new TransactionInterceptor([/* transaction handlers */]),
    new SecurityInterceptor([/* security handlers */]),
];

$suffixInterceptors = [
    $cacheInterceptor,
    new EventInterceptor(),
    new TransactionInterceptor(),
];

$serviceProxyFactory = new ProxyFactory(
    new Configuration(), //if no proxies directory is provided, the system tmp dir is used
    $prefixInterceptors,
    $SecurityInterceptor,
);
$proxy = $serviceProxyFactory->createProxy(new Class());
```

#### Built-in interceptors

- [Cache](docs/Interceptor/cache.md)
- [Event](docs/Interceptor/event.md)
- [Security](docs/Interceptor/security.md)
- [Transaction](docs/Interceptor/transaction.md)

## Acknowledgments  
This library is based on [Ocramius\ProxyManager](https://github.com/Ocramius/ProxyManager).
