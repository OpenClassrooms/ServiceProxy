<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PhpDocResponseStub
{
    /**
     * @var array<string, HttpResponse>
     */
    public array $responses = [];

    /**
     * @return array<string, HttpResponse>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }
}
