<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator\Strategy;

use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use Zend\Code\Generator\ClassGenerator;

class TmpFileGeneratorStrategy implements GeneratorStrategyInterface
{
    public const DEFAULT_DIR = 'openclassrooms_service_proxy';

    /**
     * @var string|null
     */
    protected $path;

    /**
     * @param \ProxyManager\FileLocator\FileLocatorInterface $fileLocator
     */
    public function __construct(string $path = null)
    {
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(ClassGenerator $classGenerator): string
    {
        $code = $classGenerator->generate();
        $path = $this->path ?? sys_get_temp_dir() . '/' . self::DEFAULT_DIR;
        $fileName = $path . '/' . self::class . $classGenerator->getName() . '.tmp';
        file_put_contents($fileName, "<?php\n" . $code);
        /* @noinspection PhpIncludeInspection */
        require $fileName;

        return $code;
    }
}
