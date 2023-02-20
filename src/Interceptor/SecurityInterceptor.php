<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Security;
use OpenClassrooms\ServiceProxy\Contract\SecurityHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

class SecurityInterceptor extends AbstractInterceptor implements PrefixInterceptor
{
    protected int $prefixPriority = 30;

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function prefix(Instance $instance): Response
    {
        $annotations = $instance->getMethod()->getAnnotations(Security::class);
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
     * @throws \ReflectionException
     */
    private function getCheckParam($annotation, Instance $instance)
    {
        $param = null;
        $field = $annotation->getCheckField();

        $parameters = $instance->getMethod()->getParameters();
        if (count($parameters) === 1) {
            $parameters = reset($parameters);
        }

        if ($annotation->checkRequest()) {
            $param = $parameters;
        } elseif (null !== $field) {
            $param = $this->getByPath($parameters, $field);
        }

        return $param;
    }

    /**
     * @param array|object $input
     * @param string       $path
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function getByPath($input, string $path)
    {
        $field = $this->getFirstField($path);

        if ($path) {
            return $this->getByPath($this->getByField($input, $field), $path);
        }

        return $this->getByField($input, $field);
    }

    private function getFirstField(string &$path): string
    {
        $parts = explode('.', $path);
        $field = array_shift($parts);
        $path = implode('.', $parts);

        return $field;
    }

    /**
     * @param array|object $input
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function getByField($input, string $field)
    {
        if (is_array($input) || $input instanceof \ArrayAccess) {
            return $input[$field];
        }

        if (is_object($input)) {
            try {
                return $input->$field;
            } catch (\Exception $e) {
                $ref = new \ReflectionClass($input);
                $property = $ref->getProperty($field);
                $property->setAccessible(true);

                return $property->getValue($input);
            }
        }

        throw new \InvalidArgumentException('Input must be an array or an object');
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()->hasAnnotation(Security::class);
    }
}
