<?php

namespace FFDB\Instance\Filter;

class Rule
{
    public $specification = [];
    public $operator;
    public $statement;
    public $logical = [];

    public function execute(){

    }

    public function setSpecification($specification){
        $this->specification[] = $specification;
    }

    public function setOperator($operator){
        $this->operator = $operator;
    }

    public function setStatement($statement){
        $this->statement = $statement;
    }

    public function setLogical($logical){
        $key = array_key_last($this->specification);
        $logical[$key] = $logical;
    }

}