<?php
namespace FFDB\Instance;
class Data
{

    public $modified = false;
    public $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function set($value)
    {
        $this->data[$value['__id']] = $value;
        $this->modified = true;
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function remove($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            $this->modified = true;
            return true;
        }
        return false;
    }

    public function merge($data)
    {
        foreach ($data as $value) {
            $this->data[$value['__id']] = $value;
            $this->modified = true;
        }

    }

    public function inject($data)
    {
        $modified = $this->modified;
        $this->merge($data);
        $this->modified = $modified;
    }

    public function modified($status = null)
    {
        $modified = $this->modified;
        if ($status !== null) {
            $this->modified = ($status == true);
        }
        return $modified;
    }

    public function data($raw = true)
    {
        return $raw ? $this->data : $this;
    }
}