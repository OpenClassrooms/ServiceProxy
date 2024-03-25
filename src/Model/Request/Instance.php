<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model\Request;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @template T of object
 */
final class Instance
{
    private Method $method;

    /**
     * @var \ReflectionClass<T>
     */
    private \ReflectionClass $reflection;

    private function __construct()
    {
    }

    /**
     * @param class-string<T> $class
     * @param array<string, mixed>|null $parameters
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     *
     * @return self<T>
     */
    public static function createFromMethod(
        string $class,
        string $methodName,
        ?array $parameters = null,
        mixed $response = null
    ): self {
        $annotationReader = new AnnotationReader();
        $reflection = new \ReflectionClass($class);
        $methodRef = $reflection->getMethod($methodName);
        $annotations = $annotationReader->getMethodAnnotations($methodRef);
        $method = Method::create($methodRef, $annotations);
        if ($parameters !== null) {
            $method->setParameters($parameters);
        }
        if ($response !== null) {
            $method->setResponse($response);
        }

        return self::create($reflection, $method);
    }

    public function getMethod(): Method
    {
        return $this->method;
    }

    /**
     * @param \ReflectionClass<T> $reflection
     *
     * @return self<T>
     */
    public static function create(
        \ReflectionClass $reflection,
        Method $method
    ): self {
        /** @var Instance<T> $self */
        $self = new self();
        $self->reflection = $reflection;
        $self->method = $method;

        return $self;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return self<T>
     */
    public function setParameters(array $parameters): self
    {
        $this->method->setParameters($parameters);

        return $this;
    }

    /**
     * @return self<T>
     */
    public function setResponse(mixed $response): self
    {
        $this->method->setResponse($response);

        return $this;
    }

    /**
     * @return \ReflectionClass<T>
     */
    public function getReflection(): \ReflectionClass
    {
        return $this->reflection;
    }
}
