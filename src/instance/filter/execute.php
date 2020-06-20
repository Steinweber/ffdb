<?php

namespace FFDB\Instance\Filter;

use \InvalidArgumentException;
use Exception;
use FFDB\Instance\Data;

class Execute
{
    public $data = [];
    private $commands;
    public $resultData;
    public $stats;
    private $counter = 0;

    public $rules = [];
    public $logic = [];
    public $sort = [];
    public $sortOrder = 'ASC';
    public $skip = 0;
    public $limit = 0;
    public $first = false;
    public $command = 'get';
    public $commandData;
    public $rawOutput;

    public function __construct(Data $data)
    {
        $this->data = $data;
        $this->stats = new Stats();
        $this->commands = new Command();
        $this->resultData = new Data();
    }

    public function run()
    {
        $start = microtime(true);


        //empty DB
        if (empty($this->data->data())) {
            return $this->result();
        }

        $data = $this->data->data(true);
        if (isset($this->sort[0])) {
            if ($this->sort[0] === '__id') {
                ($this->sortOrder === 'ASC') ? ksort($data) : krsort($data);
            } else {
                $data = $this->sort();
            }
        }

        //no rules
        if (empty($this->rules)) {
            if ($this->skip > 0 || $this->limit > 0) {
                $data = array_slice($data, (int)$this->skip, (int)$this->limit, true);
                $this->commands->{$this->command}($this->data, $data);
                $this->resultData = new Data($data);
                $this->counter = count($this->resultData->data);
            } else {
                $this->resultData = new Data($data);
                $this->counter = count($this->resultData->data);
            }
            return $this->result();
        }

        //one more rules then logic required
        if (count($this->logic) + 1 !== count($this->rules)) {
            throw new Exception('ERROR: Missing logic and/or');
        }

        $operators = new Operator();

        //get the last "or"
        $logic_or = 0;
        foreach ($this->logic as $logicId => $logic) {
            if ($logic->operator === 'or') {
                $logic_or = $logicId;
            }
        }

        foreach ($data as $key => $value) {

            $status = true;
            $logic = 'and';
            $this->stats->document_checked++;

            foreach ($this->rules as $ruleId => $rule) {

                $this->stats->rules_started++;

                //if the first conditions group is true
                //So do not check the next and increase performance
                if ($status === true && $logic == 'or') {
                    $this->stats->rules_skipped++;
                    break;
                }

                //The previous conditions fails
                //But we checked before there is a coming "or"
                //So we jump over this checks to the next or
                if ($status === false && $logic == 'and') {
                    $this->stats->rules_skipped++;
                    continue;
                }

                //rest status for the next conditions group
                if ($status === false && $logic == 'or') {
                    $status = true;
                }

                $this->stats->rules_used++;

                //while status is true, we continue checking the rules
                $specification = $value;

                if ($rule->statement === null && $rule->operator === null) {
                    $result = true;
                } else {
                    $specification = $this->getValue($specification, $rule->specification);
                    $result = $operators->{$rule->operator}($rule->statement, $specification);
                }


                if ($result !== true) {

                    $this->stats->rules_missed++;

                    $status = false;

                    //the condition fails and
                    //there is no coming "or" in the query
                    //we can stop here to increase performance
                    if ($logic_or < $ruleId) {
                        break;
                    }

                } else {
                    $this->stats->rules_success++;
                }

                $logic = isset($this->logic[$ruleId]) ? $this->logic[$ruleId]->operator : '';
            }

            if ($status === true) {

                $this->stats->document_found++;

                if ($this->skip !== 0) {
                    $this->skip--;
                    $this->stats->document_skipped++;
                    continue;
                }
                $this->commands->{$this->command}($this, $key);
                $this->counter++;

                if ($this->limit > 0 && $this->limit === $this->counter) {
                    break;
                }
            } else {
                $this->stats->document_missed++;
            }
        }
        $this->stats->runtime = (microtime(true) - $start) * 1000;
        return $this->result();
    }

    private function sort()
    {
        $sortData = [];
        foreach ($this->data->data(true) as $key => $value) {
            try {
                $value = $this->getValue($value, $this->sort);
            } catch (InvalidArgumentException $e) {
                $value = null;
            }
            $sortData[$key] = $value;
        }

        if ($this->sortOrder === 'ASC') {
            asort($sortData);
        } else {
            arsort($sortData);
        }
        $sortedData = [];
        foreach ($sortData as $key => $value) {
            $sortedData[$key] = $this->data->data[$key];
        }
        unset($sortData);
        return $sortedData;
    }

    private function getValue($document, $specifications)
    {
        foreach ($specifications as $specification) {

            if (isset($document[$specification])) {
                $document = $document[$specification];
            } else {
                throw new InvalidArgumentException('Specification ' . $specification . ' not found in document');
            }
        }
        return $document;
    }

    private function result()
    {
        if ($this->first) {
            $d = $this->resultData->data();
            if (empty($d)) {
                return $d;
            }
            return current($d);
        }
        if ($this->rawOutput) {
            return $this->resultData->data();
        }
        $result = new Result($this->resultData, $this->stats);
        $result->num_rows = $this->counter;
        return $result;
    }

}