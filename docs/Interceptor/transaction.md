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
