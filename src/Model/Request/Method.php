<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model\Request;

use OpenClassrooms\ServiceProxy\Annotation\Annotation;
use OpenClassrooms\ServiceProxy\Attribute\Attribute;

final class Method
{
    /**
     * @var array<object>
     */
    private array $annotations = [];

    /**
     * @var array<string, mixed>
     */
    private array $definedValues = [];

    /**
     * @var array<mixed>
     */
    private array $parameters;

    private \ReflectionMethod $reflection;

    private mixed $response;

    private function __construct()
    {
    }

    /**
     * @param array<object> $annotations
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
            static fn ($annotation) => is_a($annotation, $annotationClass, true)
        );

        if (\count($annotations) > 0) {
            return array_values($annotations)[0];
        }

        throw new \LogicException("The annotation {$annotationClass} is not defined.");
    }

    /**
     * @template T of object
     *
     * @param class-string<T>|null $annotationClass
     *
     * @return array<T>
     */
    public function getAnnotations(?string $annotationClass = null): array
    {
        /** @var array<T> $annotations */
        $annotations = array_filter(
            $this->annotations,
            static function ($annotation) use ($annotationClass) {
                if ($annotationClass === null) {
                    return true;
                }
                return is_a($annotation, $annotationClass, true);
            }
        );

        $annotations = array_unique($annotations, \SORT_REGULAR);

        if (\count($annotations) > 0) {
            return array_values($annotations);
        }

        throw new \LogicException("The annotation {$annotationClass} is not defined.");
    }

    /**
     * @template T of Attribute
     *
     * @param class-string<T> $attributeClass
     *
     * @return T
     */
    public function getAttribute(string $attributeClass): object
    {
        $attributes = $this->getAttributes($attributeClass);

        return array_values($attributes)[0]->newInstance();
    }

    /**
     * @template T of Annotation
     *
     * @param class-string<T>|null $attributeClass
     *
     * @return array<\ReflectionAttribute<T>>
     */
    public function getAttributes(?string $attributeClass = null): array
    {
        $attributes = $this->reflection->getAttributes($attributeClass);

        if (\count($attributes) === 0) {
            throw new \LogicException("The attribute {$attributeClass} is not defined.");
        }

        return $attributes;
    }

    /**
     * @template T of Attribute
     *
     * @param class-string<T>|null $attributeClass
     *
     * @return array<T>
     */
    public function getAttributesInstances(?string $attributeClass = null): array
    {
        $attributes = $this->getAttributes($attributeClass);

        return array_map(
            static fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            $attributes
        );
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

    public function getResponse(): mixed
    {
        return $this->response ?? null;
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

    public function getReturnedValue(): mixed
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
        } catch (\LogicException) {
            return false;
        }
    }

    /**
     * @param class-string<Annotation> $attributeClass
     */
    public function hasAttribute(string $attributeClass): bool
    {
        try {
            $this->getAttributes($attributeClass);

            return true;
        } catch (\LogicException) {
            return false;
        }
    }

    /**
     * @param array<mixed> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
        $this->definedValues['parameters'] = true;
    }

    public function setResponse(mixed $response): void
    {
        $this->response = $response;
        $this->definedValues['response'] = true;
    }
}
