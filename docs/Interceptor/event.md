# Event

@Event annotation allows to send events.

An implementation of OpenClassrooms\UseCase\Application\Services\EventSender\EventFactory must be written in the
application context.

```php
use OpenClassrooms\UseCase\BusinessRules\Requestors\UseCase;
use OpenClassrooms\UseCase\BusinessRules\Requestors\UseCaseRequest;
use OpenClassrooms\UseCase\BusinessRules\Responders\UseCaseResponse;
use OpenClassrooms\UseCase\Application\Annotations\EventSender;

class AUseCase implements UseCase
{
    /**
     * @Event
     *
     * @return UseCaseResponse
     */
    public function execute(UseCaseRequest $useCaseRequest)
    {
        // do things
        
        return $useCaseResponse;
    }
}
```

The message can be send:

- pre execute
- post execute
- on exception
- or all of them.

Post is default.

The name of the event is the name of the use case with underscore, prefixed by the method.
For previous example, the name would be : use_case.post.a_use_case

Prefixes can be :

- use_case.pre.
- use_case.post.
- use_case.exception.

```php
/**
 * @Event(name="event_name")
 * Send an event with event name equals to *prefix*.event_name
 * (note: the name is always converted to underscore)
 *
 * @Event(methods="pre")
 * Send an event before the call of UseCase->execute()
 *
 * @Event(methods="pre, post, onException")
 * Send an event before the call of UseCase->execute(), after the call of UseCase->execute() or on exception
 * 
 * @Event(name="first_event")
 * @Event(name="second_event")
 * Send two events
 */
```
