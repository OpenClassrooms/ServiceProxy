<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Security;
use OpenClassrooms\ServiceProxy\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

final class SecurityInterceptor extends AbstractInterceptor implements PrefixInterceptor
{
    protected int $prefixPriority = 30;

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function prefix(Instance $instance): Response
    {
        $annotations = $instance->getMethod()
            ->getAnnotations(Security::class);
        foreach ($annotations as $annotation) {
            $handler = $this->getHandler(SecurityHandler::class, $annotation);
            $handler->checkAccess(
                $annotation->getRoles(),
                $this->getCheckParam($annotation, $instance)
            );
        }

        return new Response();
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function getByPath($input, string $path)
    {
        $field = $this->getFirstField($path);

        if ($path) {
            $value = $this->getByField($input, $field);

            if (!\is_array($value) && !\is_object($value)) {
                throw new \InvalidArgumentException('The path is not valid.');
            }

            return $this->getByPath($value, $path);
        }

        return $this->getByField($input, $field);
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAnnotation(Security::class);
    }

    /**
     * @return mixed
     *
     * @throws \ReflectionException
     */
    private function getCheckParam(Security $annotation, Instance $instance)
    {
        $param = null;
        $field = $annotation->getCheckField();

        $parameters = $instance->getMethod()
            ->getParameters();
        if (count($parameters) === 1) {
            $parameters = reset($parameters);
        }

        if ($annotation->checkRequest()) {
            $param = $parameters;
        } elseif ($field !== null) {
            $param = $this->getByPath($parameters, $field);
        }

        return $param;
    }

    private function getFirstField(string &$path): string
    {
        $parts = explode('.', $path);
        $field = array_shift($parts);
        $path = implode('.', $parts);

        return $field;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    private function getByField($input, string $field)
    {
        if (\is_array($input) || $input instanceof \ArrayAccess) {
            return $input[$field];
        }

        if (\is_object($input)) {
            try {
                // @phpstan-ignore-next-line
                return $input->{$field};
                // @phpstan-ignore-next-line
            } catch (\Throwable $_) {
                $ref = new \ReflectionClass($input);
                $property = $ref->getProperty($field);
                $property->setAccessible(true);

                return $property->getValue($input);
            }
        }

        throw new \InvalidArgumentException('Input must be an array or an object');
    }
}
