<?php

namespace FFDB\Instance\Adaptor;

use FFDB\Helper\File;
use FFDB\Helper\Registry;
use FFDB\Instance\Container;
use FFDB\Instance\Data;
use FFDB\Instance\Method;

class Json extends Container implements Method
{

    public function __construct($db_name, Registry $registry)
    {
        $registry->instances->{$db_name}->set('extension', 'json');
        parent::__construct($db_name, $registry);

        $this->data = new Data();
        foreach ($this->index->getDbFiles() as $dbFile) {
            $content = $this->read($dbFile);
            $content = $content?json_decode($content,true):[];
            $this->data->merge($content);
        }
        unset($content);
    }

    public function save()
    {
        $file = File::dbFilePath($this->registry->get('path'), $this->db_name, $this->registry->instances->{$this->db_name}->get('extension'));
        var_dump($file);
        $this->write($file,json_encode($this->data()));
        $this->data->modified(false);
    }

    public function __destruct()
    {
        if ($this->data->modified()) {
            $this->save();
        }
    }
}