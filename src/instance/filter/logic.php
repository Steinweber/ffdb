<?php


namespace FFDB\Instance\Filter;


class Logic
{
    public $operator;

    public function setAnd(){
        $this->operator = 'and';
    }

    public function setOr(){
        $this->operator = 'or';
    }
}