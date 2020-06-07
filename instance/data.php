<?php
namespace FFDB\Instance;
class Data{
    private $data = [];
    protected $modified = false;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function set($value){
        $this->data[$value['__id']] = $value;
        $this->modified = true;
    }

    public function get($key){
        return isset($this->data[$key])?$this->data[$key]:null;
    }

    public function has($key){
        return isset($this->data[$key]);
    }

    public function remove($key){
        if(isset($this->data[$key])){
            unset($this->data[$key]);
            $this->modified = true;
            return true;
        }
        return false;
    }

    public function merge($data){
        $this->data = array_merge($this->data,$data);
    }

    public function data(){
        return $this->data;
    }

    public function modified($status=null){
        $modified = $this->modified;
        if($status !== null){
            $this->modified = ($status == true);
        }
        return $modified;
    }
}