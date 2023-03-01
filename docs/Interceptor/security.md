# Security

@Security annotation allows to check access.

```php

class AUseCase
{
    /**
     * @Security(roles ="ROLE_1")
     */
    public function execute(UseCaseRequest $useCaseRequest)
    {
        // do things
        
        return $useCaseResponse;
    }
}
```

"roles" is mandatory.

## Other options :

```php
/**
 * @Security (roles = "ROLE_1, ROLE_2")
 * Check the array of roles
 *
 * @Security (roles = "ROLE_1", checkRequest = true)
 * Check access for the object $useCaseRequest
 *
 * @Security (roles = "ROLE_1", checkField = "fieldName")
 * Check access for the field "fieldName" of the object $useCaseRequest
 */
```
