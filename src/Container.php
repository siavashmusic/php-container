<?php

namespace Siavash\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected array $services = [];
    public function register(string $key, mixed $value): self
    {
        $this->services[$key] = $value;

        return $this;

    }

    public function get(string $id)
    {
        $service = $this->services[$id];

        if ($service instanceof \Closure) {
            return $service();
        }

        return  $service;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}