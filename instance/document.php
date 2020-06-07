<?php


namespace FFDB\Instance;


class Document{
    public $__id;


    public function __get($name)
    {
        return $this->{$name};
    }
}