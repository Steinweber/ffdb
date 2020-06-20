<?php


namespace FFDB\Instance;


use Exception;
use FFDB\Instance\Filter\Execute;
use FFDB\Instance\Filter\Logic;
use FFDB\Instance\Filter\Operator;
use FFDB\Instance\Filter\Rule;
use http\Exception\InvalidArgumentException;

class Filter
{

    public $execute;
    private $operator;
    private $logic;
    private $lastParent = 'where';

    public function __construct(Data $data)
    {
        $this->operator = new Operator();
        $this->logic = new Logic();
        $this->execute = new Execute($data);
    }

    public function where($key = null)
    {
        $rule = new Rule();
        $rule->setSpecification($key);
        $this->execute->rules[] = $rule;
        return $this;
    }

    public function child($key)
    {
        end($this->execute->rules)->setSpecification($key);
        return $this;
    }

    public function get($rawOutput = false)
    {
        $this->execute->command = 'get';
        $this->execute->rawOutput = ($rawOutput == true);
        return $this->execute->run();
    }

    public function delete($rawOutput = false)
    {
        $this->execute->command = 'delete';
        $this->execute->rawOutput = ($rawOutput == true);
        return $this->execute->run();
    }

    public function limit($limit = 0)
    {
        $this->execute->limit = (int)$limit;
        return $this;
    }

    public function skip($skip = 0)
    {
        $this->execute->skip = (int)$skip;
        return $this;
    }

    public function first()
    {
        $this->execute->first = true;
        return $this;
    }

    public function sort($key)
    {
        if (is_array($key) || is_object($key)) {
            throw new InvalidArgumentException("ERROR: sort() key can not be a array or object");
        }
        $this->execute->sort[] = $key;
        return $this;
    }

    public function order($key)
    {
        $this->execute->sortOrder = ($key === 'ASC') ? 'ASC' : 'DESC';
        return $this;
    }

    public function __call($name, $arguments)
    {
        switch (true) {
            case method_exists($this->operator, $name):
                $rule = end($this->execute->rules);
                $rule->setOperator($name);
                $rule->setStatement($arguments[0]);
                break;
            case method_exists($this->logic, 'set' . ucfirst($name)):
                $logic = new Logic();
                $logic->{'set' . ucfirst($name)}($arguments);
                $this->execute->logic[] = $logic;
                break;
            default:
                throw new Exception('ERROR: Can not call ' . $name);
        }
        return $this;
    }
}