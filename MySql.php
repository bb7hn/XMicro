<?php

    namespace XMicro;

    use PDO;
    use PDOException;

    class MySql
    {
        private PDO $db;
        private bool $debugger;

        public function __construct(string $host, string $dbname, string $username, string $password, bool $debugger = false)
        {
            try {
                $dsn = "mysql:host=$host;dbname=$dbname";
                $this->db = new PDO($dsn, $username, $password);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->debugger = $debugger;
            } catch (PDOException $e) {
                //response(['message' => 'Service Unavailable',], 503);
                //exit;
                if ($debugger) {
                    throw $e;
                }
                return false;
            }
            return true;
        }

        public function create($tableName, $columns, $insertCUD = false): void
        {
            // $columns is an associative array of column names and types
            // e.g. ['id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY', 'name' => 'VARCHAR(255)']

            $sqlSuffix = ($insertCUD ? ",
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL" : "");
            $sql = "CREATE TABLE IF NOT EXISTS $tableName (";

            foreach ($columns as $columnName => $columnType) {
                $sql .= "$columnName $columnType, ";
            }

            $sql = rtrim($sql, ", ") . $sqlSuffix . ")";
            $sql .= ';';

            if ($this->debugger) {
                echo '<h4>CREATE</h4>';
                echo '<div class="code language-sql">' . $sql . '</div>';
                /*return;*/
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        public function update(string $table, string|int $id, array $data): int
        {
            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleUpdatedAt = in_array('updated_at', $columnNames); // bool | int

            if ($handleUpdatedAt) {
                $data["updated_at"] = date("Y-m-d H:i:s");
            }

            $keys = array_keys($data);
            $values = array_values($data);
            $setClause = implode('=?,', $keys) . '=?';
            $sql = "UPDATE $table SET $setClause WHERE id = ?";
            $sql .= ';';

            $values[] = $id;

            if ($this->debugger) {
                echo '<h4>UPDATE</h4>';
                echo '<div class="code language-sql">' . $sql . '</div>';
                echo '<h4 style="margin-left: 12px; color: forestgreen">Values</h4>';
                echo '<div class="code code-inner language-js">' . json_encode($data) . '</div>';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            return $stmt->rowCount();
        }

        public function delete(string $table, $conditions = []): int
        {
            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleDeletedAt = in_array('deleted_at', $columnNames); // bool | int

            $isConditional = count($conditions) > 0;

            $sql = $handleDeletedAt ? "UPDATE $table SET deleted_at = NOW()" : "DELETE FROM $table";

            if ($isConditional) {
                $sql .= " WHERE ";
                $values = [];
                foreach ($conditions as $columnName => $columnValue) {
                    $sql .= "$columnName = ? AND ";
                    $values[] = $columnValue;
                }
                $sql = rtrim($sql, "AND ");
            }

            $sql .= ';';

            if ($this->debugger) {
                echo '<h3>DELETE' . ($isConditional ? ' (CONDITIONAL)' : '') . '</h3>';
                echo '<div class="code language-sql">' . $sql . '</div>';
                if ($isConditional) {
                    echo '<h4 style="margin-left: 12px; color: forestgreen">Values</h4>';
                    echo '<div style="margin-left: 12px" class="code language-js">' . json_encode($conditions) . '</div>';
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values ?? []);
            return $stmt->rowCount();
        }

        public function insert(string $table, array $data): array
        {
            $sql = "";

            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleCreatedAt = in_array('created_at', $columnNames); // bool | int

            $first = true;
            foreach ($data as $item) {
                $keys = array_keys($item);
                $values = array_merge($values ?? [], array_values($item));
                $placeholders = str_repeat('?,', count($keys) - 1) . '?';
                if ($first) {
                    $sql .= "INSERT INTO $table (" . implode(', ', $keys) . ($handleCreatedAt ? ', created_at' : '') . ") VALUES ($placeholders" . ($handleCreatedAt ? ',NOW())' : ')');
                    $first = false;
                    continue;
                }
                $sql .= ",($placeholders" . ($handleCreatedAt ? ',NOW())' : ')');
            }

            $sql .= ";";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values ?? null);

            $response = range($this->db->lastInsertId(), $this->db->lastInsertId() + count($data) - 1);

            if ($this->debugger) {
                echo '<h3>INSERT</h3>';
                echo '<div class="code language-sql">' . $sql . '</div>';
                echo '<h4 style="margin-left: 12px; color: forestgreen">Values</h4>';
                echo '<div class="code language-js">' . json_encode($data) . '</div>';
                echo '<h4 style="margin-left: 16px; color: forestgreen">RESPONSE</h4>';
                echo '<div class="code code-inner language-js">' . json_encode($response) . '</div>';
            }
            return $response;
        }

        public function select(string $table, null|array|string $where = null, array|null $params = null, false|int $index = false, false|int $limit = false)
        {
            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleDeletedAt = in_array('deleted_at', $columnNames); // bool | int

            $sql = "SELECT * FROM $table";
            if ($where !== null) {
                if (gettype($where) === "array") {
                    $sql .= " WHERE " . implode(' AND ', $where);
                    $sql = rtrim($sql, ' AND');
                } else {
                    $sql .= " WHERE $where";
                }
                if ($handleDeletedAt) {
                    $sql .= " AND deleted_at IS NULL";
                }
            } else {
                if ($handleDeletedAt) {
                    $sql .= " WHERE deleted_at IS NULL";
                }
            }
            //handle limit and index
            if ($limit !== false && $index !== false) {
                $sql .= " LIMIT $index,$limit";
            } elseif ($index !== false) {
                $sql .= " LIMIT $index,10";
            } elseif ($limit !== false) {
                $sql .= " LIMIT 0,$limit";
            }

            $sql .= ";";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $response = ($limit !== false || $index !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC));

            if ($this->debugger) {
                echo '<h4>SELECT</h4>';
                echo '<div class="code language-sql">' . $sql . '</div>';
                echo '<h4 style="margin-left: 12px; color: forestgreen">Values</h4>';
                echo '<div class="code code-inner language-js">' . json_encode($params) . '</div>';
                echo '<h4 style="margin-left: 16px; color: dodgerblue">RESPONSE</h4>';
                echo '<div class="code code-inner language-js">' . json_encode($response) . '</div>';
            }
            return $response;
        }

        public function selectAll($table, array|null|string $where = null, $params = null): array
        {
            //TODO CONVERT FOLLOWING HANDLER TO FUNCTION FOR DELETED_UPDATED AND CREATED_AT VALUES
            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleDeletedAt = in_array('deleted_at', $columnNames); // bool | int

            $sql = "SELECT * FROM $table";

            if ($where !== null) {
                if (gettype($where) === "array") {
                    $sql .= " WHERE " . implode(' AND ', $where);
                    $sql = rtrim($sql, ' AND');
                } else {
                    $sql .= " WHERE $where";
                }
                if ($handleDeletedAt) {
                    $sql .= " AND deleted_at IS NULL";
                }
            } else {
                if ($handleDeletedAt) {
                    $sql .= " WHERE deleted_at IS NULL";
                }
            }


            $sql .= ';';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($this->debugger) {
                echo '<h4>SELECT ALL</h4>';
                echo '<div class="code language-sql">' . $sql . '</div>';
                echo '<h4 style="margin-left: 12px; color: forestgreen">Values</h4>';
                echo '<div style="margin-left: 12px" class="code language-js">' . json_encode($params) . '</div>';
                echo '<h4 style="margin-left: 16px; color: dodgerblue">RESPONSE</h4>';
                echo '<div class="code code-inner language-js">' . json_encode($response) . '</div>';
                return [];
            }
            return $response;
        }

        public function count(string $table, string|null|array $where = null, array|null $params = null, bool $includeDeletedRows = false): int
        {
            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleDeletedAt = in_array('deleted_at', $columnNames); // bool | int

            $sql = "SELECT COUNT(*) FROM $table";

            if ($where !== null) {
                if (gettype($where) === "array") {
                    $sql .= " WHERE " . implode(' AND ', $where);
                    $sql = rtrim($sql, ' AND');
                } else {
                    $sql .= " WHERE $where";
                }
            }

            if ($handleDeletedAt && !$includeDeletedRows) {
                $sql .= " AND deleted_at IS NULL";
            }

            $sql .= ';';

            if ($this->debugger) {
                echo '<h4>COUNT</h4>';
                echo '<div class="code language-sql">' . $sql . '</div>';
                if ($params != null) {
                    echo '<h4 style="margin-left: 12px; color: forestgreen">Values</h4>';
                    echo '<div style="margin-left: 12px" class="code language-js">' . json_encode($params) . '</div>';
                }
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        }
    }
