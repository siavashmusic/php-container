<?php

namespace Siavash\Container;

use Psr\Container\ContainerInterface;
use Siavash\Container\Exceptions\CouldNotResolveAbstraction;
use Siavash\Container\Exceptions\CouldNotResolveCLassException;

class Container implements ContainerInterface
{
    protected array $services = [];

    protected array $instances = [];

    protected static $instance;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    public function singleton(string $key, mixed $callback): self
    {
       return $this->register($key, $callback, singleton:true);
    }

    public function register(string $key, mixed $value, bool $singleton = false): self
    {
        if (is_string($value) && class_exists($value)){
            $value = fn() => new $value;
        }
        $this->services[$key] = $value;

        if ($singleton) {
            $this->instances[$key] = null;
        }
        return $this;

    }

    public function get(string $id): mixed
    {
        if ($this->has($id)) {
            return $this->fetchBoundService($id);
        }

        return $this->build($id);

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

    protected function build(string $service): object
    {
        try{
            $reflector = new \ReflectionClass($service);

        } catch (\ReflectionException) {

            throw new CouldNotResolveCLassException();
        }
       if (! $reflector->isInstantiable()) {
           throw new CouldNotResolveAbstraction(sprintf('Could not resolve interface or abstract class [%s] ', $service));
       }

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

    protected function fetchBoundService(string $service)
    {
        if (array_key_exists($service, $this->instances) && ! is_null($this->instances[$service])) {
            return $this->instances[$service];
        }

        $serviceResolver =$this->services[$service];

        $serviceResolver = $serviceResolver instanceof \Closure
            ? $serviceResolver($this)
            : $serviceResolver;

        if (array_key_exists($service, $this->instances)) {
            $this->instances[$service] = $serviceResolver;
        }

        return $serviceResolver;
    }
}