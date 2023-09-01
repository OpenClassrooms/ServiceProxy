<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Request;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;

final class Instance
{
    private Method $method;

    private object $object;

    private \ReflectionObject $reflection;

    private ?Context $context;

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed>|null $parameters
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public static function createFromMethod(
        object $object,
        string $methodName,
        ?array $parameters = null,
        mixed $response = null
    ): self {
        $annotationReader = new AnnotationReader();
        $reflection = new \ReflectionObject($object);
        $methodRef = $reflection->getMethod($methodName);
        $annotations = $annotationReader->getMethodAnnotations($methodRef);
        $method = Method::create($methodRef, $annotations);
        if ($parameters !== null) {
            $method->setParameters($parameters);
        }
        if ($response !== null) {
            $method->setResponse($response);
        }

        return self::create($object, $reflection, $method);
    }

    public function getMethod(): Method
    {
        return $this->method;
    }

    public static function create(
        object $object,
        \ReflectionObject $reflection,
        Method $method
    ): self {
        $self = new self();
        $self->object = $object;
        $self->reflection = $reflection;
        $self->method = $method;

        return $self;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): self
    {
        $this->method->setParameters($parameters);

        return $this;
    }

    public function setResponse(mixed $response): self
    {
        $this->method->setResponse($response);

        return $this;
    }

    public function getContext(): ?Context
    {
        return $this->context;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getReflection(): \ReflectionObject
    {
        return $this->reflection;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $data = [
            'class' => $this->getReflection()
                ->getName(),
            'class_short_name' => $this->getReflection()
                ->getShortName(),
            'method' => $this->getMethod()
                ->getName(),
            'parameters' => $this->getMethod()
                ->getParameters(),
        ];

        if ($this->getContext() === null) {
            return $data;
        }

        if ($this->getContext()->type !== ContextType::PREFIX) {
            $data['response'] = $this->getMethod()->getReturnedValue();
            $data['exception'] = $this->getMethod()->getException();
        }

        return $data;
    }

    public function setContext(Context $context): void
    {
        $this->context = $context;
    }
}
