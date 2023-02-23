<?php

namespace XMicro;

require_once('functions.php');

use PDO;
use PDOException;
use Throwable;

/**
 * A class that provides LightWeight `Micro Service` operations for a Rest API.
 */
class MicroService
{
    protected bool $debugger;

    /**
     * Constructor method that sets the response content type header and defines the DOMAIN constant.
     */
    function __construct(bool $debugger = false)
    {
        if (!$debugger) {
            header('Content-type: application/json; charset=utf-8');
        } else {
            echo '<style>
                    html{
                        background:rgba(0,0,0,.88);
                        color: #FFF;
                    }

                    body{
                        height: 100vh;
                        max-width:100vw;
                        overflow-y: auto;
                        padding: 24px;
                    }
                    
                    
                    
                </style>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github-dark.min.css" integrity="sha512-rO+olRTkcf304DQBxSWxln8JXCzTHlKnIdnMUwYvQa9/Jd4cQaNkItIUj6Z4nvW1dqK0SKXLbn9h4KwZTNtAyw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
                <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
            ';
        }

        define('DOMAIN', $this->get_server());
        $this->debugger = $debugger;
    }

    /**
     * Sends a response in JSON format with the specified message and HTTP status code.
     *
     * @param array $MessageArray An associative array containing the message and data to be sent in the response.
     * @param int $Code The HTTP status code to be sent in the response.
     */
    function response(array $MessageArray = ['message' => 'OK', 'data' => null], int $Code = 200): void
    {
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
    function conn_mysql(string $DatabaseServer, string $DatabaseName, string $DatabaseUser, string $DatabasePassword): MySql
    {
        return new MySql($DatabaseServer, $DatabaseName, $DatabaseUser, $DatabasePassword, $this->debugger);
    }

    /**
     * Retrieves the current server's base URL.
     *
     * @return string The current server's base URL.
     */
    protected function get_server(): string
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

    function __destruct()
    {
        // if debugger enabled run sql syntax highlighterz"
        if ($this->debugger) {
            echo '
                <script>hljs.highlightAll();</script>
            ';
        }
    }
}

//TODO IMPLEMENT UPDATED AT

/**
 * A class that provides basic CRUD operations for a MySQL database.
 */
class MySql
{
    protected PDO $db;
    private bool $debug;

    /**
     * Constructor that initializes a PDO connection to the MySQL database.
     *
     * @param string $DatabaseServer The database server.
     * @param string $DatabaseName The name of the database to connect to.
     * @param string $DatabaseUser The username to use to connect to the database.
     * @param string $DatabasePassword The password to use to connect to the database.
     */
    function __construct(string $DatabaseServer, string $DatabaseName, string $DatabaseUser, string $DatabasePassword, bool $Debug = false)
    {
        try {
            $this->db = new PDO("mysql:host=$DatabaseServer;dbname=$DatabaseName", "$DatabaseUser", "$DatabasePassword");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->debug = $Debug;
        } catch (PDOException) {
            response(['message' => 'Service Unavailable',], 503);
            exit;
        }
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $TableName The name of the table to create.
     * @param array $Columns An array of column names and types to create in the new table.
     * @param bool $SetDefaultColumns (optional) Whether to add default columns (id, created_at, deleted_at) to the table. Default is true.
     * @return bool Returns true if the table was created successfully, false otherwise.
     * @throws Throwable
     */
    function create(string $TableName, array $Columns, bool $SetDefaultColumns = true): bool
    {
        $query = "CREATE TABLE IF NOT EXISTS $TableName (";
        foreach ($Columns as $name => $type) {
            $query .= "`$name` $type,";
        }

        if ((isset($Columns['id']) || isset($Columns['ID'])) && $SetDefaultColumns) {
            $query .= '`created_at` DATETIME NOT NULL DEFAULT NOW(),';
            $query .= '`deleted_at` DATETIME DEFAULT NULL,';
            $query .= 'PRIMARY KEY (`' . ($Columns['id'] ? 'id' : 'ID') . '`)';
        } else {
            $query = rtrim($query, ',');
        }


        $query .= ');';

        if ($this->debug) {
            echo '<h4>CREATE</h4>';
            echo '<pre><code class="language-sql">' . $query . '</code></pre>';
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return true;
        } catch (Throwable $th) {
            if ($this->debug) throw($th);
            return false;
        }
    }

    /**
     * Drops a table from the database.
     *
     * @param string $TableName The name of the table to drop.
     * @return bool Returns true if the table was dropped successfully, false otherwise.
     * @throws Throwable
     */
    function drop(string $TableName): bool
    {
        $query = "DROP TABLE IF EXISTS $TableName;";

        if ($this->debug) {
            echo '<h4>DROP</h4>';
            echo '<pre><code class="language-sql">' . $query . '</code></pre>';
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return true;
        } catch (Throwable $th) {
            if ($this->debug) throw($th);
            return false;
        }

    }

    /**
     * Truncates a table, removing all its rows and resetting any auto-incrementing ID values.
     * @param string $TableName The name of the table to truncate.
     * @return bool True if the table was successfully truncated, false otherwise.
     * @throws Throwable
     */
    function truncate(string $TableName): bool
    {
        $query = "TRUNCATE TABLE $TableName;";

        if ($this->debug) {
            echo '<h4>TRUNCATE</h4>';
            echo '<pre><code class="language-sql">' . $query . '</code></pre>';
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return true;
        } catch (Throwable $th) {
            if ($this->debug) throw($th);
            return false;
        }

    }

    /**
     * Inserts a new row into a table in the database.
     *
     * @param string $TableName The name of the table to insert into.
     * @param array $Columns An array of column names and values to insert into the new row.
     * @return bool Returns true if the row was inserted successfully, false otherwise.
     * @throws Throwable
     */
    function insert(string $TableName, array $Columns): bool
    {
        // TODO implement uuid creation
        $keys = array_keys($Columns);

        $query = "INSERT INTO $TableName (`" . join('`,`', $keys) . '`) values (:' . join(',:', $keys) . ')';

        if ($this->debug) {
            echo '<h4>INSERT</h4>';
            echo '<pre><code class="language-sql">' . $query . '</code></pre>';
        }

        try {
            $stmt = $this->db->prepare($query);

            $stmt->execute($Columns);
            return true;
        } catch (Throwable $th) {

            if ($this->debug) throw($th);
            return false;
        }
    }

    /**
     * Deletes one or more rows from a table in the database.
     *
     * @param string $TableName The name of the table to delete from.
     * @param array $Where (optional) An array of column names and values to filter the rows to delete. If empty, all rows in the table will be deleted.
     * @return bool Returns true if the rows were deleted successfully, false otherwise.
     * @throws Throwable
     */
    function delete(string $TableName, array $Where = []): bool
    {
        // TODO implement uuid creation

        if (count($Where) === 0) {
            $query = "UPDATE $TableName SET deleted_at = NOW() WHERE 1";
        } else {
            $keys = array_keys($Where);
            $query = "UPDATE $TableName SET deleted_at = NOW() WHERE ";
            foreach ($keys as $key) {
                $query .= "`$key` = :$key AND";
            }

            $query = rtrim($query, ' AND');
        }

        if ($this->debug) {
            echo '<h4>DELETE</h4>';
            echo '<pre><code class="language-sql">' . $query . '</code></pre>';
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($Where);
            return true;
        } catch (Throwable $th) {

            if ($this->debug) throw($th);
            return false;
        }
    }

    /**
     * Retrieve records from the given table with optional column selection and where clause
     * @param string $TableName The name of the table to retrieve records from
     * @param array|string $Columns (Optional) The columns to retrieve from the table. Default is '*'.
     * @param array $Where (Optional) Associative array of column name and value to filter records by. Default is empty array.
     * @return bool|array Returns an array of records on success or false on failure
     * @throws Throwable Throws an exception on database error
     */
    function find(string $TableName, array|string $Columns = '*', array $Where = []): bool|array
    {
        if ($Columns === '*') {
            $query = "SELECT * from $TableName WHERE ";
        } else {
            $query = "SELECT " . join(',', $Columns) . " from $TableName WHERE ";
        }

        if (count($Where) !== 0) {
            foreach ($Where as $key => $value) {
                if (gettype($value) === 'array') {
                    $query .= "`$key` in ('" . join("', '", $value) . "') AND ";
                    unset($Where[$key]);
                    continue;
                }

                if (gettype($value) === 'boolean') {
                    $query .= "`$key` IS " . ($value ? '' : 'NOT') . " NULL AND ";
                    unset($Where[$key]);
                    continue;
                }

                $query .= "`$key` = :$key AND ";
            }
            $query = rtrim($query, ' AND');
        } else {
            $query .= "`deleted_at` IS NULL";
        }

        if ($this->debug) {
            echo '<h4>FIND</h4>';
            echo '<pre><code class="language-sql">' . $query . '</code></pre>';
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($Where);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $th) {

            if ($this->debug) throw($th);
            return false;
        }

    }

    /**
     *
     * This function returns the first row from the given table name based on the provided conditions.
     *
     * If no conditions are provided, it returns the first row where the 'deleted_at' column is NULL.
     *
     * @param string $TableName The name of the table to query.
     *
     * @param array|string $Columns The columns to select. Default is '*'.
     *
     * @param array $Where An associative array of conditions to use in the query.
     *
     * @param bool $reverse If set to true, returns the last row instead of the first. Default is false.
     *
     * @return array|bool The first row that matches the conditions. Returns false on failure.
     *
     * @throws Throwable
     */
    function first(string $TableName, array|string $Columns = '*', array $Where = [], bool $reverse = false): bool|array
    {
        if ($Columns !== '*') {
            $query = "SELECT " . join(',', $Columns) . " from $TableName WHERE ";
        } else {
            $query = "SELECT * from $TableName WHERE ";
        }

        if (count($Where) === 0) {
            $query .= "`deleted_at` IS NULL";
        } else {
            foreach ($Where as $key => $value) {
                if (gettype($value) === 'array') {
                    $query .= "`$key` in ('" . join("', '", $value) . "') AND ";
                    unset($Where[$key]);
                    continue;
                }

                if (gettype($value) === 'boolean') {
                    $query .= "`$key` IS " . ($value ? '' : 'NOT ') . "NULL AND ";
                    unset($Where[$key]);
                    continue;
                }

                $query .= "`$key` = :$key AND ";
            }
            $query = rtrim($query, ' AND');
        }

        $query .= ($reverse ? ' ORDER BY id desc LIMIT 1;' : ' LIMIT 1;');
        if ($this->debug) {
            echo '<h4>' . (!$reverse ? 'FIRST' : 'LAST') . '</h4>';
            echo '<pre><code class="language-sql">' . $query . '</code></pre>';
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($Where);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $th) {

            if ($this->debug) throw($th);
            return false;
        }

    }

    /**
     *
     * This function returns the last row from the given table name based on the provided conditions.
     * If no conditions are provided, it returns the last row where the 'deleted_at' column is NULL.
     * @param string $TableName The name of the table to query.
     * @param array|string $Columns The columns to select. Default is '*'.
     * @param array $Where An associative array of conditions to use in the query.
     * @return array|bool The last row that matches the conditions. Returns false on failure.
     * @throws Throwable
     */
    function last(string $TableName, array|string $Columns = '*', array $Where = []): bool|array
    {
        return $this->first($TableName, $Columns, $Where, true);
    }

}