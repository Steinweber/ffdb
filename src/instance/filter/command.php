<?php


namespace FFDB\Instance\Filter;

class Command
{
    function get(Execute $execute, $key)
    {
        $execute->resultData->data[$key] = $execute->data->data[$key];
    }

    public function delete(Execute $execute, $key)
    {
        unset($execute->data->data[$key]);
        $execute->data->modified = true;
        $execute->resultData->data[$key] = 'deleted';
    }


}