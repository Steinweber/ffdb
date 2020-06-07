<?php
//PHP 7.3 required
require_once __DIR__ . '/ffdb.php';
$db = new FFDB(__DIR__.'/db/');
$start = microtime(true);

$foo = $db->table('foo');
$result = $foo->filter()
    ->where()
    ->limit(10)->skip(4990)->get();
var_dump($result);
//generate($db,'foo',5000);

function generate($db,$db_name,$quantity){
    $dummy = json_decode(file_get_contents('data.json'), true);

    $data = [];
    for($x=0;$x != $quantity; $x++) {
        $data[] = $dummy[rand(0,999)];
    }

    $db->create('json')->table($db_name);
    $foo = $db->table($db_name);

    for($x=0;$x != $quantity; $x++) {
        $foo->insert($data[$x]);
    }
    $foo->save();
    unset($data,$dummy,$foo);
}
var_dump((microtime(true)-$start)*1000);