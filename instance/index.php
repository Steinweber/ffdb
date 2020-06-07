<?php

namespace FFDB\Instance;

use FFDB\Helper\File;
use FFDB\Helper\Registry;

class Index
{
    protected $registry;
    private $data = [];

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $path = $registry->get('path');
        File::createDirIfNotExists($path);
        $this->init(File::read(File::indexFile($path)));
    }

    public function getId(){
        $this->data['id']++;
        return strval($this->data['id']);
    }

    public function startDb($db_name){
        if(!isset($this->data['instances'][$db_name])){
            $this->data['instances'][$db_name] = new InstanceIndex($this->registry,$db_name);
        }
    }

    private function init($data)
    {
        if ($data === null) {
            $this->data['id'] = 0;
            $this->data['instances'] = [];
        } else {
            $this->data = json_decode($data, true);
            foreach ($this->data['instances'] as $db_name => $instance){
                $this->data['instances'][$db_name] = new InstanceIndex($this->registry,$db_name,$this->data['instances'][$db_name]);
            }
        }
    }

    public function __get($name)
    {
        if (isset($this->data['instances'][$name])){
            return $this->data['instances'][$name];
        }
        return null;
    }

    public function __destruct()
    {
        $data = $this->data;
        foreach ($data['instances'] as $key => $instance){
            $data['instances'][$key] = $instance->data();
        }
        File::write(File::indexFile($this->registry->get('path')), json_encode($data));
    }
}