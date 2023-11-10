<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Event;

use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Message;
use OpenClassrooms\ServiceProxy\Model\MessageContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;

trait RequestAwareMessageTrait
{
    private function createMessage(Event $event, ?string $queue = null): Message
    {
        if ($queue !== null) {
            [$subject, $state] = explode('-', $queue);
        } else {
            $state = self::getState($event->classShortName);
            $subject = self::getSubject($event->classShortName);
        }

        return new Message(
            new MessageContext($subject, $state),
            $event,
            $this->getHeaders()
        );
    }

    private static function getState(string $classShortName): string
    {
        $tmp = explode('_', self::camelCaseToSnakeCase($classShortName));

        return array_shift($tmp);
    }

    private static function getSubject(string $classShortName): string
    {
        $tmp = explode('_', self::camelCaseToSnakeCase($classShortName));
        array_shift($tmp);

        return implode('_', $tmp);
    }

    abstract private function getRequest(): ?RequestStack;

    private function getOrigin(): string
    {
        return $this->getRequest()?->getCurrentRequest()?->headers?->get('X-Origin') ?? 'OpenClassrooms';
    }

    /**
     * @return array<string, string>
     */
    private function getHeaders(): array
    {
        return [
            'X-Correlation-Id' => $this->getCorrelationId(),
            'X-Origin' => $this->getOrigin(),
        ];
    }

    private function getCorrelationId(): string
    {
        return $this->getRequest()?->getCurrentRequest()?->headers?->get('X-Correlation-Id') ?? Uuid::v4()->toRfc4122();
    }

    private static function camelCaseToSnakeCase(string $input): string
    {
        return mb_strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
