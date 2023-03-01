# Cache

@Cache annotation allows cache management.


```php
namespace MyProject\AClass;

use OpenClassrooms\ServiceProxy\Annotation\Cache;

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

## Options:

### Lifetime:

```php
/**
 * @Cache(lifetime=1000)
 * Add a TTL of 1000 seconds
 */
```

### Id (key):

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

### Namespace:

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
