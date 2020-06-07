<?php

namespace FFDB\Instance\Filter;

use FFDB\Instance\Data;
use FFDB\Instance\Filter;

class Result
{
    public $data = [];
    public $stats;

    public function __construct(Data $data,Stats $stats){
        $this->data = $data;
        $this->stats = $stats;
    }

    public function filter(){
        return new Filter($this->data);
    }
}