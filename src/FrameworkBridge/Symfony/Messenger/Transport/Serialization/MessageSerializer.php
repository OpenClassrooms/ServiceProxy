<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\FrameworkBridge\Symfony\Messenger\Transport\Serialization;

use OpenClassrooms\ServiceProxy\Model\Message;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

final class MessageSerializer extends Serializer
{
    /**
     * @return array<string, mixed>
     */
    public function encode(Envelope $envelope): array
    {
        $result = parent::encode($envelope);
        $message = $envelope->getMessage();
        if ($message instanceof Message) {
            $result['headers'] = [...$result['headers'], ...$message->headers];
        }

        return $result;
    }
}
