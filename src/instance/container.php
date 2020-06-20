<?php
namespace FFDB\Instance;

use FFDB\Helper\File;
use FFDB\Helper\Registry;


class Container{
    protected $data;
    protected $filter;
    protected $registry;
    protected $db_name;
    protected $DBindex;
    protected $index;

    public function __construct($db_name, Registry $registry)
    {
        $this->registry = $registry;
        $this->DBindex = $this->registry->get('index');
        $this->DBindex->startDb($db_name);
        $this->index = $this->DBindex->{$db_name};
        $this->db_name = $db_name;
        $this->data = new Data();
    }

    public function getId(){
        return $this->index->getId();
    }

    public function filter(){
        return $this->filter = new Filter($this->data);
    }

    public function insert($value)
    {
        $document = $value;
        $document['__id'] = $this->DBindex->getId();
        $this->data->set($document);
        return $document['__id'];
    }

    public function update($value)
    {
        if ($this->data->has($value['__id']) === false) {
            return false;
        }
        $this->data->set($value);
        return true;
    }

    public function updateData(Data $data)
    {
        $this->updateArray($data->data());
    }

    public function updateArray(array $data)
    {
        foreach ($data as $value) {
            $this->update($value);
        }
    }

    public function delete($key)
    {
        $this->data->remove($key);
    }

    public function get($key)
    {
        return $this->data->get($key);
    }

    public function has($key)
    {
        return $this->data->has($key);
    }

    public function data($raw = false)
    {
        return $raw ? $this->data->data(true) : $this->data;
    }

    public function clear()
    {
        //ToDo remove files from index
        $path = File::path($this->registry->get('path'), $this->db_name);
        File::clearFolder($path);
    }

    protected function write($file, $content)
    {
        return File::write($file, $content);
    }

    protected function read($file)
    {
        return File::read($file);
    }

}
