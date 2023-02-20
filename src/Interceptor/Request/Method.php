<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Request;

final class Method
{
    private array $annotations = [];

    private array $definedValues = [];

    private array $parameters;

    private \ReflectionMethod $reflection;

    /**
     * @var mixed
     */
    private $response;

    private function __construct()
    {
    }

    public static function create(\ReflectionMethod $reflection, array $annotations): self
    {
        $self = new self();
        $self->reflection = $reflection;
        $self->annotations = $annotations;

        return $self;
    }

    /**
     * @template T of class-string
     *
     * @param T $annotationClass
     *
     * @return T
     */
    public function getAnnotation(?string $annotationClass): object
    {
        $annotations = array_filter(
            $this->annotations,
            static function ($annotation) use ($annotationClass) {
                return $annotation instanceof $annotationClass;
            }
        );

        if (count($annotations) > 0) {
            return array_values($annotations)[0];
        }

        throw new \LogicException("The annotation {$annotationClass} is not defined.");
    }

    /**
     * @template T of class-string
     * @param T $annotationClass
     *
     * @return array<int, T>
     */
    public function getAnnotations(?string $annotationClass = null): array
    {
        $annotations = array_filter(
            $this->annotations,
            static function ($annotation) use ($annotationClass) {
                return $annotation instanceof $annotationClass;
            }
        );

        $annotations = array_unique($annotations, SORT_REGULAR);

        if (count($annotations) > 0) {
            return array_values($annotations);
        }

        throw new \LogicException("The annotation {$annotationClass} is not defined.");
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getParameters(): array
    {
        if (!isset($this->definedValues['parameters'])) {
            throw new \LogicException('The parameters are not defined at this point.');
        }

        return $this->parameters;
    }

    public function getReflection(): \ReflectionMethod
    {
        return $this->reflection;
    }

    public function getResponse()
    {
        if (!isset($this->definedValues['response'])) {
            throw new \LogicException('The response is not defined at this point.');
        }

        return $this->response;
    }

    public function threwException(): bool
    {
        return $this->getResponse() instanceof \Exception;
    }

    public function getException(): ?\Exception
    {
        return $this->threwException() ? $this->getResponse() : null;
    }

    /**
     * @return mixed
     */
    public function getReturnedValue()
    {
        return !$this->threwException() ? $this->getResponse() : null;
    }

    public function hasAnnotation(string $annotationClass): bool
    {
        try {
            $this->getAnnotations($annotationClass);

            return true;
        } catch (\LogicException $e) {
            return false;
        }
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        $this->definedValues['parameters'] = true;

        return $this;
    }

    public function setResponse($response): self
    {
        $this->response = $response;
        $this->definedValues['response'] = true;

        return $this;
    }
}
