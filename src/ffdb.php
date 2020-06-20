<?php

function ffdb_al($class)
{
    if (substr($class, 0, 4) == 'FFDB') {
        $class = str_replace(['ffdb', '\\'], ['', '/'], strtolower($class));
    }
    $file = __DIR__ . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
    return true;
}

spl_autoload_register('ffdb_al');

use FFDB\Instance\Instance;
use FFDB\Instance\Index;
use FFDB\Helper\Registry;

class FFDB
{
    private $instances = array();
    private $table_pre_method = false;
    private $table_method = [
        'has' => false,
        'create' => false,
        'delete' => false,
    ];
    private $registry;


    public function __construct($path)
    {
        $this->registry = new Registry();
        $this->registry->set('path', $path);
        $this->registry->set('instance_default_format', 'json');
        $this->registry->set('index', new Index($this->registry));
        $this->registry->microtime->set('init', microtime(true));
    }

    public function table($db_name) {

        if($this->table_pre_method === true){
            $result = null;
            if($this->table_method['has'] === true){
                $result = $this->hasTable($db_name);
            }elseif ($this->table_method['create'] !== true){
                $result = $this->createTable($db_name,$this->table_method['create']);
            }elseif ($this->table_method['delete'] === true){
                $result = $this->deleteTable($db_name);
            }
            $this->resetTableConfig();
            return $result;
        }

        if(isset($this->instances[$db_name])){
            return $this->instances[$db_name];
        }
        $instanceIndex = $this->registry->get('index')->{$db_name};
        if($instanceIndex !== null){
            return $this->createTable($db_name,$instanceIndex->getAdaptor());
        }
        return false;
    }

    public function has(){
        $this->setTableConfig('has',true);
        return $this;
    }

    public function delete(){
        $this->setTableConfig('delete',true);
        return $this;
    }

    public function create($format = null){
        $this->setTableConfig('create',$format);
        return $this;
    }

    private function createTable($name,$format) {
        try {
            if(array_key_exists($name,$this->instances)){
                return $this->instances[$name];
            }
            return $this->instances[$name] = new Instance($name, $format, $this->registry);
        }catch (Exception $exception){
            //handle or return
            return $exception;
        }

    }

    private function hasTable($name) {
        return isset($this->instances[$name]);
    }

    private function deleteTable($name) {
        if(!isset($this->instances[$name])){
            return true;
        }
        $this->instances[$name]->delete();
        unset($this->instances[$name]);
        return true;
    }

    private function setTableConfig($key, $value)
    {
        $this->resetTableConfig();
        $this->table_pre_method = true;
        $this->table_method[$key] = $value;
    }

    private function resetTableConfig()
    {
        $this->table_pre_method = false;
        foreach ($this->table_method as $key => $value) {
            $this->table_method[$key] = false;
        }
    }

    public function __destruct()
    {
        echo "Timings: \n";
        $start = $this->registry->microtime->get('init');
        foreach ($this->registry->microtime->data() as $key => $ms) {
            if ($key === 'init')
                continue;
            var_dump($key . ' ' . ($ms - $start) * 1000);
        }
    }
}