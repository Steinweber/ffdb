<?php

namespace FFDB\Helper;

class Registry
{
    protected $data = [];

    public function get($key)
    {
        return (isset($this->data[$key]) ? $this->data[$key] : null);
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __get($index)
    {
        if (!isset($this->data[$index])) {
            $this->data[$index] = new Registry();
        }
        return $this->data[$index];
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function data()
    {
        return $this->data;
    }
}