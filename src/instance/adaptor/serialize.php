<?php

namespace FFDB\Instance\Adaptor;

use FFDB\Helper\File;
use FFDB\Helper\Registry;
use FFDB\Instance\Container;
use FFDB\Instance\Data;
use FFDB\Instance\Method;

class Serialize extends Container implements Method
{

    public function __construct($db_name, Registry $registry)
    {
        $registry->instances->{$db_name}->set('extension', 'data');
        parent::__construct($db_name, $registry);

        $this->data = new Data();
        $registry->microtime->set('serializeAdaptor', microtime(true));
        foreach ($this->index->getDbFiles() as $dbFile) {

            $content = $this->read($dbFile);
            $registry->microtime->set('serialize_file_read', microtime(true));

            $content = $content ? unserialize($content) : [];
            $registry->microtime->set('serialize_file_decode', microtime(true));

            $this->data->merge($content);
            $registry->microtime->set('serialize_file: ' . $dbFile, microtime(true));
        }
        unset($content);
    }

    public function save()
    {
        $file = File::dbFilePath($this->registry->get('path'), $this->db_name, $this->registry->instances->{$this->db_name}->get('extension'));
        $this->write($file, serialize($this->data->data(true)));
        $this->data->modified(false);
    }

    public function __destruct()
    {
        if ($this->data->modified()) {
            $this->save();
        }
    }
}