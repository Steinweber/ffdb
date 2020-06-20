<?php

namespace FFDB\Instance\Adaptor;

use FFDB\Helper\File;
use FFDB\Helper\Registry;
use FFDB\Instance\Container;
use FFDB\Instance\Data;
use FFDB\Instance\Method;
use function MongoDB\BSON\fromPHP;
use function MongoDB\BSON\toPHP;

class Bson extends Container implements Method
{

    public function __construct($db_name, Registry $registry)
    {
        $registry->instances->{$db_name}->set('extension', 'bson');
        parent::__construct($db_name, $registry);

        $this->data = new Data();
        $registry->microtime->set('bsonAdaptor', microtime(true));
        foreach ($this->index->getDbFiles() as $dbFile) {
            $content = $this->read($dbFile);
            $registry->microtime->set('bson_file_read', microtime(true));
            $content = $content ? toPHP($content, array(
                'array' => 'array',
                'document' => 'array',
                'root' => 'array'
            )) : [];
            $registry->microtime->set('bson_file_decode', microtime(true));
            $this->data->merge($content);
            $registry->microtime->set('bson_file: ' . $dbFile, microtime(true));
        }
        unset($content);
    }

    public function save()
    {
        $file = File::dbFilePath($this->registry->get('path'), $this->db_name, $this->registry->instances->{$this->db_name}->get('extension'));
        var_dump($file);
        $this->write($file, fromPHP($this->data->data(true)));
        $this->data->modified(false);
    }

    public function __destruct()
    {
        if ($this->data->modified()) {
            $this->save();
        }
    }
}