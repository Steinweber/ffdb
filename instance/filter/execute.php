<?php

namespace FFDB\Instance\Filter;

use FFDB\Instance\Data;

class Execute
{
    private $data = [];
    public $rules = [];
    public $logic = [];
    public $sort;
    public $skip = 0;
    public $limit = 0;

    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    public function run($rawOutput)
    {
        $start = microtime(true);
        $hits = [];
        $counter = 0;
        $skip = $this->skip;
        $data = $this->data->data();
        $operators = new Operator();
        $stats = new Stats();

        //one more rules then logic required
        if(count($this->logic)+1 !== count($this->rules)){
            throw new \Exception('ERROR: Missing logic and/or');
        }

        //get the last "or"
        $logic_or = 0;
        foreach ($this->logic as $logicId => $logic){
            if($logic->operator === 'or'){
                $logic_or = $logicId;
            }
        }

        foreach ($data as $key => $value) {
            $status = true;
            $logic = 'and';
            $stats->document_checked++;
            foreach ($this->rules as $ruleId =>  $rule) {
                $stats->rules_started++;
                //if the first conditions group is true
                //So do not check the next and increase performance
                if($status === true && $logic == 'or'){
                    $stats->rules_skipped++;
                    break;
                }

                //The previous conditions fails
                //But we checked before there is a coming "or"
                //So we jump over this checks to the next or
                if($status === false && $logic == 'and'){
                    $stats->rules_skipped++;
                    continue;
                }

                //rest status for the next conditions group
                if($status === false && $logic == 'or'){
                    $status = true;
                }
                $stats->rules_used++;
                //while status is true, we continue checking the rules
                $specification = $value;

                if($rule->statement === null && $rule->operator === null){
                    $result = true;
                }else{
                    $specification = $this->getValue($specification, $rule->specification);
                    $result = $operators->{$rule->operator}($rule->statement,$specification);
                }


                if ($result !== true) {
                    $stats->rules_missed++;
                    $status = false;

                    //the condition fails and
                    //there is no coming "or" in the query
                    //we can stop here to increase performance
                    if($logic_or < $ruleId){
                        break;
                    }
                }else{
                    $stats->rules_success++;
                }

                $logic = isset($this->logic[$ruleId])?$this->logic[$ruleId]->operator:'';
            }

            if($status === true){
                $stats->document_found++;
                if($skip !== 0){
                    $skip--;
                    $stats->document_skipped++;
                    continue;
                }
                $hits[] = $data[$key];
                $counter++;
                if($this->limit > 0 && $this->limit === $counter){
                    break;
                }
            }else{
                $stats->document_missed++;
            }
        }
        $stats->runtime = (microtime(true)-$start)*1000;
        $result = new Result(new Data($hits),$stats);
        return $rawOutput?$hits:$result;
    }

    private function getValue($document, $specifications)
    {

        foreach ($specifications as $specification) {

            if (isset($document[$specification])) {
                $document = $document[$specification];
            } else {
                throw new \Exception('Specification ' . $specification . ' not found in document');
            }
        }
        return $document;
    }

}