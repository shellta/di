<?php
declare(strict_types=1);


namespace Mo\Container;


use Mo\Container\Exception\ContainerException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    protected $vars = [];

    /**
     * @param string $id
     * @param mixed $value
     */
    public function set(string $id, $value)
    {
        $this->vars[$id] = $value;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        if (! $this->has($id)) {
            $this->vars[$id] = $this->make($id);
            return $this->vars[$id];
        }

        $val = $this->vars[$id];

        if (is_callable($val)) {
            return $this->resolveClosure($val);
        }

        return $val;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->vars[$id]);
    }

    /**
     * @param $key
     * @return mixed|object
     * @throws ContainerException
     */
    public function make($key)
    {
        if (class_exists($key)) {
            return $this->resolveClass($key);
        }

        return null;
    }

    /**
     * @param string $class
     * @return object
     * @throws ContainerException
     */
    protected function resolveClass(string $class)
    {
        $resolver = new Resolver($class);
        return $resolver
            ->use($this)
            ->resolve();
    }

    /**
     * @param callable $closure
     * @return mixed
     */
    protected function resolveClosure(callable $closure)
    {
        return $closure();
    }
}
