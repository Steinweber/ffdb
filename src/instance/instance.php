<?php
namespace FFDB\Instance;

use Exception;
use FFDB\Helper\Registry;

class Instance
{

    private $adaptor;

    public function __construct($db_name, $adaptor, Registry $registry)
    {
        $class = 'FFDB\Instance\Adaptor\\' . ucfirst($adaptor);

        $file = __DIR__ . '/adaptor/' . strtolower($adaptor) . '.php';
        if (is_file($file)) {
            include_once($file);
        }

        if (class_exists($class)) {
            $registry->instances->{$db_name}->set('adaptor', $adaptor);
            $registry->microtime->set('loadAdaptor', microtime(true));
            $this->adaptor = new $class($db_name, $registry);
        } else {
            throw new Exception('Error: Could not load FFDB\Instance adaptor ' . $adaptor . '!');
        }
    }

    public function filter()
    {
        return $this->adaptor->filter();
    }

    public function insert($value)
    {
        return $this->adaptor->insert($value);
    }

    public function update(array $value)
    {
        return $this->adaptor->update($value);
    }

    public function updateData(Data $data)
    {
        $this->adaptor->updateData($data);
    }

    public function updateArray(array $data)
    {
        $this->adaptor->updateArray($data);
    }

    public function data($raw = true)
    {
        return $this->adaptor->data($raw);
    }

    public function save()
    {
        return $this->adaptor->save();
    }
}