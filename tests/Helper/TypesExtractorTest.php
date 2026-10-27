<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Helper;

use OpenClassrooms\ServiceProxy\Helper\TypesExtractor;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\PhpDocResponseStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TypesExtractorTest extends TestCase
{
    public function testResolvesImportedTypesInPhpDocCollections(): void
    {
        $types = (new TypesExtractor())->extractFromMethod(
            new \ReflectionMethod(PhpDocResponseStub::class, 'getResponses')
        );

        $this->assertContains('array', $types);
        $this->assertContains(Response::class, $types);
        $this->assertNotContains('HttpResponse', $types);
    }

    public function testResolvesPropertyPhpDocInNestedObjects(): void
    {
        $types = (new TypesExtractor())->extractFromMethod(new \ReflectionMethod(self::class, 'createResponse'));

        $this->assertContains(PhpDocResponseStub::class, $types);
        $this->assertContains(Response::class, $types);
    }

    private function createResponse(): PhpDocResponseStub
    {
        return new PhpDocResponseStub();
    }
}
