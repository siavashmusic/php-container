<?php

namespace Siavash\Container;

use Psr\Container\ContainerInterface;
use Siavash\Container\Exceptions\CouldNotResolveCLassException;

class Container implements ContainerInterface
{
    protected array $services = [];

    protected static $instance;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }
    public function register(string $key, mixed $value): self
    {
        $this->services[$key] = $value;

        return $this;

    }

    public function get(string $service)
    {
        if ($this->has($service)) {
            $service = $this->services[$service];
            if ($service instanceof \Closure) {
                return $service();
            }
            return $service;
        }
        if (class_exists($service)) {
             return $this->build($service);
        }
       throw new CouldNotResolveCLassException();
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    protected function dd(...$args)
    {
        var_dump(...$args);
        die;
    }

    /**
     * @throws \ReflectionException
     */
    protected function build(string $service): object
    {
       $reflector = new \ReflectionClass($service);
       $parameters = $reflector->getConstructor()?->getParameters() ?? [];

       $resolveDependencies = array_map(function (\ReflectionParameter $parameter) {
           $class = $parameter->getType()->getName();

           if (class_exists($class)) {
               return $this->build($class);
           }
       }, $parameters);

//       $this->dd($resolveDependencies);
        return $reflector->newInstanceArgs($resolveDependencies);
    }
}