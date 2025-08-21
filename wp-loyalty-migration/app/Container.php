<?php

namespace WPLoyalty\Migration\App;

defined('ABSPATH') or exit;

class Container
{
    public $bindings = [];

    public function set($key, $value)
    {
        if (isset($this->bindings[$key])) {
            return $this->bindings[$key];
        }

        $this->bindings[$key] = $value;
        return $this->bindings[$key];
    }

    public function get($key)
    {
        if (isset($this->bindings[$key])) {
            return $this->bindings[$key];
        }
        
        return null;
    }

    public function has($key)
    {
        return isset($this->bindings[$key]);
    }

    public function bind($abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function make($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract];
        }
        
        // Auto-resolve if not bound
        return $this->resolve($abstract);
    }

    protected function resolve($abstract)
    {
        if (class_exists($abstract)) {
            return new $abstract();
        }
        
        throw new \Exception("Unable to resolve {$abstract}");
    }
}