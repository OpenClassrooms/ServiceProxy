<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Request;

use OpenClassrooms\ServiceProxy\Annotation\Annotation;

final class Method
{
    /**
     * @var array<int, object>
     */
    private array $annotations = [];

    /**
     * @var array<string, mixed>
     */
    private array $definedValues = [];

    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    private \ReflectionMethod $reflection;

    /**
     * @var mixed
     */
    private $response;

    private function __construct()
    {
    }

    /**
     * @param array<int, object> $annotations
     */
    public static function create(\ReflectionMethod $reflection, array $annotations): self
    {
        $self = new self();
        $self->reflection = $reflection;
        $self->annotations = $annotations;

        return $self;
    }

    /**
     * @template T of Annotation
     *
     * @param class-string<T> $annotationClass
     *
     * @return T
     */
    public function getAnnotation(string $annotationClass): object
    {
        /** @var array<int, T> $annotations */
        $annotations = array_filter(
            $this->annotations,
            static function ($annotation) use ($annotationClass) {
                return is_a($annotation, $annotationClass, true);
            }
        );

        if (count($annotations) > 0) {
            return array_values($annotations)[0];
        }

        throw new \LogicException("The annotation {$annotationClass} is not defined.");
    }

    /**
     * @template T
     *
     * @param class-string<T>|null $annotationClass
     *
     * @return array<int, T>
     */
    public function getAnnotations(?string $annotationClass = null): array
    {
        /** @var array<int, T> $annotations */
        $annotations = array_filter(
            $this->annotations,
            static function ($annotation) use ($annotationClass) {
                if ($annotationClass === null) {
                    return true;
                }
                return is_a($annotation, $annotationClass, true);
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

    /**
     * @return mixed[]
     */
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

    /**
     * @return mixed|\Exception
     */
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
        $response = $this->getResponse();

        return $response instanceof \Exception ? $response : null;
    }

    /**
     * @return mixed
     */
    public function getReturnedValue()
    {
        return !$this->threwException() ? $this->getResponse() : null;
    }

    /**
     * @param class-string<Annotation> $annotationClass
     */
    public function hasAnnotation(string $annotationClass): bool
    {
        try {
            $this->getAnnotations($annotationClass);

            return true;
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (\LogicException $_) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
        $this->definedValues['parameters'] = true;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
        $this->definedValues['response'] = true;
    }
}
