<?php

namespace XMicro;

require_once('functions.php');

use \PDO;
use PDOException;

/**
 * A class that provides LightWeight `Micro Service` operations for a Rest API.
 */
class MicroService
{
    /**
     * Constructor method that sets the response content type header and defines the DOMAIN constant.
     */
    function __construct()
    {
        header('Content-type: application/json; charset=utf-8');
        define('DOMAIN', $this->get_server());
    }
    
    /**
     * Sends a response in JSON format with the specified message and HTTP status code.
     *
     * @param array $MessageArray An associative array containing the message and data to be sent in the response.
     * @param int $Code The HTTP status code to be sent in the response.
     */
    function response(array $MessageArray = ['messgae' => 'OK', 'data' => null], int $Code = 200){
        response($MessageArray, $Code);
    }

    /**
     * Creates a new instance of the MySql class and returns it.
     *
     * @param string $DatabaseServer The server name or IP address for the MySQL server.
     * @param string $DatabaseName The name of the MySQL database to connect to.
     * @param string $DatabaseUser The username to use when connecting to the MySQL server.
     * @param string $DatabasePassword The password to use when connecting to the MySQL server.
     * @return MySql An instance of the MySql class.
     */
    function conn_mysql(string $DatabaseServer,string $DatabaseName,string $DatabaseUser,string $DatabasePassword){
        return new MySql($DatabaseServer,$DatabaseName,$DatabaseUser,$DatabasePassword);
    }

    /**
     * Retrieves the current server's base URL.
     *
     * @return string The current server's base URL.
     */
    protected function get_server()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = 'https://';
        } else {
            $url = 'http://';
        }
        // Append the host(domain name, ip) to the URL.
        $url .= $_SERVER['HTTP_HOST'];

        // Append the requested resource location to the URL
        /* $url .= $_SERVER['REQUEST_URI']; */

        return $url . '/';
    }

}

/**
 * A class that provides basic CRUD operations for a MySQL database.
 */
class MySql{
    private $db ;
    /**
     * Constructor that initializes a PDO connection to the MySQL database.
     *
     * @param string $DatabaseServer The database server.
     * @param string $DatabaseName The name of the database to connect to.
     * @param string $DatabaseUser The username to use to connect to the database.
     * @param string $DatabasePassword The password to use to connect to the database.
     */
    function __construct(string $DatabaseServer,string $DatabaseName,string $DatabaseUser,string $DatabasePassword)
    {
        try {
            $this->db = new PDO("mysql:host=$DatabaseServer;dbname=$DatabaseName", "$DatabaseUser", "$DatabasePassword");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            response(['message' => 'Service Unavailable',], 503);
            exit;
        }
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $TableName The name of the table to create.
     * @param array $Columns An array of column names and types to create in the new table.
     * @param bool $SetDefaultColumns (optional) Whether or not to add default columns (id, created_at, deleted_at) to the table. Default is true.
     * @return bool Returns true if the table was created successfully, false otherwise.
     */
    function create(string $TableName, array $Columns, bool $SetDefaultColumns=true){
        $query  = "CREATE TABLE if not exists $TableName (";
        foreach($Columns as $name=>$type){
            $query .= "`$name` $type,";
        }
        
        if( (isset($Columns['id']) || isset($Columns['ID'])) && $SetDefaultColumns ){
            $query .= '`created_at` DATETIME NOT NULL DEFAULT NOW(),';
            $query .= '`deleted_at` DATETIME DEFAULT NULL,';
            $query .= 'PRIMARY KEY (`'.( $Columns['id']?'id':'ID' ).'`)';
        }
        else{
            $query = rtrim($query,',');
        }
        

        $query .= ');';

        /* echo json_encode($query);
        exit; */
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return true;
        } catch (\Throwable $th) {
            throw($th);
            return false;
        }
    }

    /**
     * Drops a table from the database.
     *
     * @param string $TableName The name of the table to drop.
     * @return bool Returns true if the table was dropped successfully, false otherwise.
     */
    function drop(string $TableName){
        $query  = "DROP TABLE IF EXISTS $TableName;";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
        
    }

    /**
     * Inserts a new row into a table in the database.
     *
     * @param string $TableName The name of the table to insert into.
     * @param array $Columns An array of column names and values to insert into the new row.
     * @return bool Returns true if the row was inserted successfully, false otherwise.
     */
    function insert(string $TableName, array $Columns){
        // TODO implement uuid creation
        $keys = array_keys($Columns);

        $query  = "INSERT INTO $TableName (`" . join('`,`',$keys).'`) values (:'.join(',:',$keys).')';
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt = $this->db->prepare($query);
            foreach($Columns as $key => $value) {
                $stmt->bindParam(":$key",$value);
            }
            $stmt->execute();

            //$stmt->execute($Columns);
            return true;
        } catch (\Throwable $th) {
            
            throw($th);
            return false;  
        }
    }

    /**
     * Deletes one or more rows from a table in the database.
     *
     * @param string $TableName The name of the table to delete from.
     * @param array $Where (optional) An array of column names and values to filter the rows to delete. If empty, all rows in the table will be deleted.
     * @return bool Returns true if the rows were deleted successfully, false otherwise.
     */
    function delete(string $TableName,array $Where=[]){
        // TODO implement uuid creation
        
        if(count($Where) === 0){
            $query  = "UPDATE $TableName SET deleted_at = NOW() WHERE 1";
        }
        else{
            $keys = array_keys($Where);
            $query  = "UPDATE $TableName SET deleted_at = NOW() WHERE ";
            foreach($keys as $key){
                $query .= "`$key` = :$key AND";
            }

            $query = rtrim($query, ' AND');
        }
        
        /* echo json_encode($query);
        exit; */

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($Where);
            return true;
        } catch (\Throwable $th) {
            
            throw($th);
            return false;  
        }
    

        /* echo json_encode([$query]); */
    }

    function find(){
        
    }

    function first(){
        
    }

    function last(){
        
    }

}