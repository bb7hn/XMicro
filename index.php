<?php

    use XMicro\MicroService;

    require_once 'auto_loader.php';
    // INIT CLASS
    // NOTE THAT: IF DEBUGGER ENABLED YOU'LL SEE ONLY QUERIES. NONE OF THEM WILL RUN
    $service = new MicroService(true);
    $db = $service->conn_mysql('localhost', 'x-micro', 'root', '');

    // CREATE EXAMPLE
    $structure = [
        'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
        'first_name' => 'VARCHAR(255)',
        'last_name' => 'VARCHAR(255)',
        'age' => 'INT(11) NOT NULL',
        'created_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'deleted_at' => 'TIMESTAMP NULL DEFAULT NULL',
    ];

    $db->create('test', $structure);

    // INSERT EXAMPLE
    $data = [
        "first_name" => "John",
        "last_name" => "Doe"
    ];
    $db->insert('test', $data);

    // SELECT EXAMPLE
    $db->select("test", 1);
    // SELECT ALL EXAMPLE
    $db->selectAll("test");
    // SELECT ALL EXAMPLE WITH CONDITIONS
    $conditions = ['name' => 'John', 'age' => 30];
    $db->selectAll("test", $conditions);
    // UPDATE EXAMPLE
    $db->update('test', 1, ['age' => 31]);
    // DELETE ALL EXAMPLE
    $db->delete('test', []);
    // DELETE CONDITIONAL EXAMPLE
    $db->delete('test', ["id" => 1]);
