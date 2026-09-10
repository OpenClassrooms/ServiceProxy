# Security

#[Security] attribute allows to check access.

```php

class AUseCase
{
    #[Security("is_granted('ROLE_1')")]
    public function execute(UseCaseRequest $useCaseRequest)
    {
        // do things
        
        return $useCaseResponse;
    }
}
```

## Other options :

```php

// You can use expressions to combine multiple checks, for instance role or voter:
#[Security("is_granted('ROLE_1') or is_granted('VOTER_1', request)")]

// Beware of the following syntax
#[Security]

// If it precedes a method named execute, __invoke or __construct, it will be interpreted as following:
#[Security("is_granted('ROLE_NAME_OF_CLASS_IN_SNAKE_CASE')")]
public function execute(UseCaseRequest $useCaseRequest)

// However, if it precedes a method with a different name, it will be interpreted as following:
#[Security("is_granted('ROLE_NAME_OF_CLASS_IN_SNAKE_CASE_PROCESS_ORDER')")]
public function processOrder(UseCaseRequest $useCaseRequest)
// Note that ROLE contains class name AND method name in snake case

```

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
