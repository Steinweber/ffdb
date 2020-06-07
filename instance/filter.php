<?php


namespace FFDB\Instance;


use FFDB\Instance\Filter\Execute;
use FFDB\Instance\Filter\Logic;
use FFDB\Instance\Filter\Operator;
use FFDB\Instance\Filter\Rule;

class Filter
{

    public $execute;
    private $operator;
    private $logic;

    public function __construct(Data $data)
    {
        $this->operator = new Operator();
        $this->logic = new Logic();
        $this->execute = new Execute($data);
    }

    public function where($key=null){
        $rule = new Rule();
        $rule->setSpecification($key);
        $this->execute->rules[] = $rule;
        return $this;
    }

    public function child($key){
        end($this->execute->rules)->setSpecification($key);
        return $this;
    }

    public function get($rawOutput=false){
        return $this->execute->run($rawOutput);
    }

    public function __call($name, $arguments)
    {
        switch (true){
            case method_exists($this->operator,$name):
                $rule = end($this->execute->rules);
                $rule->setOperator($name);
                $rule->setStatement($arguments[0]);
                break;
            case method_exists($this->logic,'set'.ucfirst($name)):
                $logic = new Logic();
                $logic->{'set'.ucfirst($name)}($arguments);
                $this->execute->logic[] = $logic;
                break;
            case $name == 'sort':
                $this->execute->sort = (string)$arguments[0];
                break;
            case $name == 'limit':
                $this->execute->limit = (int)$arguments[0];
                break;
            case $name == 'skip':
                $this->execute->skip = (int)$arguments[0];
                break;
            default:
                throw new \Exception('ERROR: Can not call '.$name);
        }
        return $this;
    }

    public function data()
    {
        //return $this->execute->data;
    }
}