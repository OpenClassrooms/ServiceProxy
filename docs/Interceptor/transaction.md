### Transaction

@Transaction annotation gives a transactional context around a method.

- begin transaction
- execute()
- commit
- rollback on exception

```php

class AUseCase
{
    /**
     * @Transaction
     */
    public function execute(UseCaseRequest $useCaseRequest)
    {
        // do things
        
        return $useCaseResponse;
    }
}
```

Exceptions occurring during the transaction can be mapped so that the method 
returns another exception instead

```php

class AUseCase
{
    /**
     * @Transaction(exceptions={
     *     "SomeInfrastructureException"="SomeDomainException"
     * })
     */
    public function execute(UseCaseRequest $useCaseRequest)
    {
        // do things
        
        return $useCaseResponse;
    }
}
```

