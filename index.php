<?php

    use XMicro\MicroService;

    require_once 'autoload.php';
    // INIT CLASS
    // NOTE THAT: IF DEBUGGER ENABLED YOU'LL SEE ONLY QUERIES. NONE OF THEM WILL RUN
    $service = new MicroService(true);
    $db = $service->conn_mysql('localhost', 'x-micro', 'root', '');

    /*// CREATE EXAMPLE
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
    $data = [ // array of arrays
        ["first_name" => "John",
            "last_name" => "Doe",
            "age" => 25
        ],
        ["first_name" => "Jane",
            "last_name" => "Doe",
            "age" => 24
        ],
        ["first_name" => "Jack",
            "last_name" => "Boe",
            "age" => 26
        ],
        ["first_name" => "June",
            "last_name" => "Boe",
            "age" => 25
        ]
    ];
    $db->insert('test', $data);*/

    // SELECT FIRST EXAMPLE 1 (ALL)
    /*$db->select("test");*/

    // SELECT FIRST EXAMPLE 2 (With id)
    /*$db->select("test", "id = ?", [1]);*/


    // SELECT FIRST EXAMPLE 3 (with id in)
    /*$inArr = [2, 3, 4];
    $db->select("test", "id NOT IN (?)", ['(' . implode(',', $inArr) . ')']);*/

    // SELECT LAST EXAMPLE 4 (ALL)
    /*$db->select("test", "1 ORDER BY id DESC");*/

    // SELECT LAST EXAMPLE 5 (With id)
    /*$db->select("test", "id = ? ORDER BY id DESC", [1]);*/


    // SELECT FIRST EXAMPLE 6 (with id in)
    /*$inArr = [2, 3, 4];
    $db->select("test", "id NOT IN (?) ORDER BY id DESC", ['(' . implode(',', $inArr) . ')']);*/

    // SELECT ALL EXAMPLE
    /*$db->selectAll("test");*/

    // SELECT ALL EXAMPLE WITH CONDITIONS
    /*$conditions = ['first_name = ?', 'age <= ?'];
    $params = ['John', 30];
    $db->selectAll("test", $conditions, $params);*/

    // UPDATE EXAMPLE
    $db->update('test', 1, ['age' => 31]);

    // COUNT CONDITIONAL EXAMPLE (AND)
    $db->count('test', ['age > ?', 'salary < ?'], [18, 20000]);

    // COUNT CONDITIONAL EXAMPLE (OR)
    $db->count('test', 'age > ? OR salary < ?', [18, 20000]);

    // DELETE CONDITIONAL EXAMPLE
    $db->delete('test', ["id" => 1]);

    // DELETE ALL EXAMPLE
    $db->delete('test');
