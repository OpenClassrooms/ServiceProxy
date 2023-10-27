<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use Symfony\Component\Filesystem\Filesystem;

trait CacheTestTrait
{
    protected static string $tmpDir = __DIR__ . '/tmp';

    protected static string $templateFilePath = __DIR__ . '/Interceptor/templates/CachedClass.php.template';

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove(self::$tmpDir);
    }

    public function getCacheHandlerMock(): CacheHandlerMock
    {
        return new CacheHandlerMock(null, true, self::$tmpDir);
    }

    protected function executeAndAssertCacheHit(string $className): void
    {
        [$exitCode, $output] = $this->executeWrittenClass($className);

        $this->assertEquals(1, $exitCode, implode("\n", $output));
    }

    protected function executeAndAssertCacheMiss(string $className): void
    {
        [$exitCode, $output] = $this->executeWrittenClass($className);

        $this->assertEquals(0, $exitCode, implode("\n", $output));
    }

    private function writeClass(
        string $className,
        string $methodName,
        ?string $methodAttribute = '',
        ?string $methodArgs = '',
        ?string $methodReturnType = '',
        array $methodPhpDoc = [],
        array $classProperties = [],
        string $methodBody = ''
    ): void {
        $namespace = __NAMESPACE__ . str_replace('/', '\\', str_replace(__DIR__, '', self::$tmpDir));

        $content = file_get_contents(self::$templateFilePath);
        $content = str_replace('__CLASS_NAME__', $className, $content);
        $content = str_replace('__METHOD_NAME__', $methodName, $content);
        $content = str_replace('__METHOD_BODY__', $methodBody, $content);
        $content = str_replace('__METHOD_ARGS__', $methodArgs, $content);
        $content = str_replace('__METHOD_ATTRIBUTE__', $methodAttribute, $content);
        $content = str_replace('__METHOD_RETURN_TYPE__', $methodReturnType ? ': ' . $methodReturnType : '', $content);
        $content = str_replace('__NAMESPACE__', $namespace, $content);

        $phpDoc = empty($methodPhpDoc)
            ? '*'
            : implode("\n", array_map(static fn (string $statement) => '* ' . $statement, $methodPhpDoc))
        ;
        $content = str_replace('__METHOD_PHPDOC__', $phpDoc, $content);

        $properties = empty($classProperties)
            ? ''
            : implode("\n", array_map(static fn (string $statement) => $statement . ';', $classProperties))
        ;
        $content = str_replace('__CLASS_PROPERTIES__', $properties, $content);

        $filePath = self::$tmpDir . '/' . $className . '.php';

        $fs = new Filesystem();
        $fs->dumpFile($filePath, $content);
    }

    private function executeWrittenClass(string $className): array
    {
        exec('php ' . __DIR__ . '/Interceptor/TestCodeTemplate.php ' . $className, $output, $exitCode);

        return [$exitCode, $output];
    }
}
