<?php
use XMicro\MicroService;

require_once('XMicro.php');


$Service = new MicroService();

//$Service->response();

$DB = $Service->conn_mysql('localhost','x-micro','root','');

/* $cols = [
    "id" => "int(11) NOT NULL AUTO_INCREMENT",
    "name" =>  "varchar(255)",
    "last_name" =>  "varchar(255)"
];

$DB->create('test', $cols);
$DB->drop('test');
 */
$values = [
    "name" => "asd",
    "last_name" => "dsa"
];

$DB->insert('test', $values);
/* $conditions = [
    "id"=>1,
];

$DB->delete('test'); */

$Service->response();