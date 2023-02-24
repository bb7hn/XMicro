<?php

    use XMicro\MicroService;

    require_once 'MicroService.php';

    $Service = new MicroService(true);

    //$Service->response();

    $DB = $Service->conn_mysql('localhost', 'x-micro', 'root', '');

    $DB->select('test', '*');


    $cols = [
        'id' => 'int(11) NOT NULL AUTO_INCREMENT',
        'name' => 'varchar(255)',
        'last_name' => 'varchar(255)'
    ];

    //$DB->create('test', $cols);

    //$DB->drop('test');

    //$DB->create('test', $cols);

    /*$values = [
        "name" => "a",
        "last_name" => "b"
    ];

    $DB->insert('test', $values);

    $values["name"]="c";
    $values["last_name"]="d";

    $DB->insert('test', $values);

    $conditions = [
        "id"=>1,
    ];*/

    /*$DB->delete('test');*/

    /*$where = [
        "id" => 1,
        "deleted_at" => !0
    ];

    $results = $DB->find('test');

    echo'<pre>';
    var_dump($results);
    echo'</pre>';

    $results = $DB->find('test','*',$where);

    echo'<pre>';
    var_dump($results);
    echo'</pre>';

    $results = $DB->first('test');

    echo'<pre>';
    var_dump($results);
    echo'</pre>';

    $results = $DB->first('test',);

    echo'<pre>';
    var_dump($results);
    echo'</pre>';

    $results = $DB->last('test');

    echo'<pre>';
    var_dump($results);
    echo'</pre>';*/

    //$Service->response(['message'=>'ok','data'=>$results]);
    //$Service->response(['message'=>'ok','data'=>$results]);
