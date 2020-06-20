<?php

namespace FFDB\Instance\Adaptor;

use FFDB\Helper\File;
use FFDB\Helper\Registry;
use FFDB\Instance\Container;
use FFDB\Instance\Method;

class Php extends Container implements Method
{

    public function __construct($db_name, Registry $registry)
    {
        $registry->instances->{$db_name}->set('extension', 'php');
        parent::__construct($db_name, $registry);


        $registry->microtime->set('phpAdaptor', microtime(true));

        foreach ($this->index->getDbFiles() as $dbFile) {

            require($dbFile);
            $registry->microtime->set('php_file_read', microtime(true));
            //ToDo inject needs a lot of time...
            //$this->data->inject(isset($d)?$d:[]);
            $this->data->data = isset($d) ? $d : [];

            unset($d);

            $registry->microtime->set('php_file: ' . $dbFile, microtime(true));
        }
    }

    public function save()
    {
        $file = File::dbFilePath($this->registry->get('path'), $this->db_name, $this->registry->instances->{$this->db_name}->get('extension'));
        $this->write($file, '<?php $d = ' . str_replace(PHP_EOL, '', var_export($this->data->data(), true)) . ';');
        if (function_exists('opcache_compile_file')) {
            opcache_invalidate($file, true);
            touch($file, time() - 120);
            opcache_compile_file($file);
        }
        $this->data->modified(false);
    }

    public function __destruct()
    {
        if ($this->data->modified()) {
            $this->save();
        }
    }
}