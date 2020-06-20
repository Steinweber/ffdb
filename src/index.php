<?php
//PHP 7.3 required
require_once __DIR__ . '/ffdb.php';
$db = new FFDB(__DIR__ . '/db/');
$start = microtime(true);

$foo = $db->create('php')->table('php200k');

$result = $foo->filter()
    //->where('id')->greater('300')
    ->limit(0)
    //->sort('email')->order('DESC')
    ->get();

var_dump($result->num_rows);
//generate($db,'php','php200k',200000);

var_dump(memory_get_usage() / 1024 / 1024);
var_dump(memory_get_peak_usage() / 1024 / 1024);


function generate($db, $adaptor, $db_name, $quantity)
{
    $dummy = json_decode(file_get_contents('data2.json'), true);

    $data = [];
    for ($x = 0; $x != $quantity; $x++) {
        $data[] = $dummy[rand(0, 999)];
    }

    $db->create($adaptor)->table($db_name);
    $foo = $db->table($db_name);

    for ($x = 0; $x != $quantity; $x++) {
        $foo->insert($data[$x]);
    }
    $foo->save();
    unset($data,$dummy,$foo);
}

var_dump('TOTAL MS:' . (microtime(true) - $start) * 1000);