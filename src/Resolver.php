<?php
declare(strict_types=1);

namespace Mo\Container;


use Mo\Container\Exception\ContainerException;
use Psr\Container\ContainerInterface;

class Resolver
{
    private $class;

    /**
     * @var \ReflectionClass
     */
    private $reflectClass;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function use(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    public function resolve()
    {
        try {
            $this->reflectClass = new \ReflectionClass($this->class);
            $constructor = $this->reflectClass->getConstructor();

            return $this->resolveConstructor($constructor);

        } catch (\ReflectionException $e) {
            throw new ContainerException("resolve {$this->class} failed.");
        }
    }

    protected function resolveConstructor(?\ReflectionMethod $method)
    {
        if (is_null($method)) {
            return $this->reflectClass->newInstanceWithoutConstructor();
        }

        $parameters = new \ArrayObject();
        $reflectionParameters = $method->getParameters();

        foreach ($reflectionParameters as $reflectionParameter) {
            $parameter = $this->resolveParameter($reflectionParameter);
            $parameters->append($parameter);
        }

        return $this->reflectClass->newInstance(...$parameters->getArrayCopy());
    }

    protected function resolveParameter(\ReflectionParameter $parameter)
    {
        if (!is_null($parameter->getClass())) {
            if (is_null($this->container)) {
                throw new ContainerException('container not defined.');
            }

            return $this->container->get($parameter->getClass()->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new ContainerException(
            'resolve'
            . ' -> ' . $parameter->getDeclaringClass()->getName()
            . ' -> ' . $parameter->getDeclaringFunction()->getName()
            . ' -> $' . $parameter->getName()
            . ' failed.'
        );
    }

    public static function call(ContainerInterface $container, array $func, array $args)
    {
        [$class, $method] = $func;
        return call_user_func_array([$container->get($class), $method], $args);
    }
}
