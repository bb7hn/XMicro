<?php

    namespace XMicro;

    use PDO;

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
            } catch (PDOException) {
                response(['message' => 'Service Unavailable',], 503);
                exit;
            }
        }

        public function create($tableName, $columns, $insertCUD = false): void
        {
            // $columns is an associative array of column names and types
            // e.g. ['id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY', 'name' => 'VARCHAR(255)']

            $sqlSuffix = ($insertCUD ? "
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL" : "");
            $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
            foreach ($columns as $columnName => $columnType) {
                $sql .= "$columnName $columnType, ";
            }
            $sql = rtrim($sql, ", ") . $sqlSuffix . ")";
            if ($this->debugger) {
                echo '<h4>CREATE</h4>';
                echo '<pre><code class="language-sql">' . $sql . '</code></pre>';
                return;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        public function insert(string $table, array $data)
        {
            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleCreatedAt = in_array('created_at', $columnNames); // bool | int

            $keys = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            $sql = "INSERT INTO $table (" . implode(', ', $keys) . ($handleCreatedAt ? ', created_at' : '') . ") VALUES ($placeholders" . ($handleCreatedAt ? ',NOW())' : ')');

            if ($this->debugger) {
                echo '<h3>INSERT</h3>';
                echo '<pre><code class="language-sql">' . $sql . '</code></pre>';
                echo '<h4 style="margin-left: 12px; color: forestgreen">values</h4>';
                echo '<pre style="margin-left: 12px"><code class="language-js">' . json_encode($data) . '</code></pre>';
                return;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            return $this->db->lastInsertId();
        }

        public function select(string $table, string|int $id)
        {
            $sql = "SELECT * FROM $table WHERE id = ?";
            if ($this->debugger) {
                echo '<h4>SELECT</h4>';
                echo '<pre><code class="language-sql">' . $sql . '</code></pre>';
                echo '<h4 style="margin-left: 12px; color: forestgreen">values</h4>';
                echo '<pre style="margin-left: 12px"><code class="language-js">' . json_encode([$id]) . '</code></pre>';
                return;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        }

        public function selectAll($tableName, array $conditions = []): array
        {
            $isConditional = count($conditions) > 0;
            $sql = "SELECT * FROM $tableName";
            if ($isConditional) {
                $sql .= " WHERE ";
                $values = [];
                foreach ($conditions as $columnName => $columnValue) {
                    $sql .= "$columnName = ? AND ";
                    $values[] = $columnValue;
                }
                $sql = rtrim($sql, "AND ");
            }
            if ($this->debugger) {
                echo '<h4>SELECT ALL' . ($isConditional ? ' => (CONDITIONAL)' : '') . '</h4>';
                echo '<pre><code class="language-sql">' . $sql . '</code></pre>';
                if ($isConditional) {
                    echo '<h4 style="margin-left: 12px; color: forestgreen">values</h4>';
                    echo '<pre style="margin-left: 12px"><code class="language-js">' . json_encode($conditions) . '</code></pre>';
                }
                return [];
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values ?? []);

            return $stmt->fetchAll(PDO::FETCH_OBJ) ?? [];
        }

        public function update(string $table, string|int $id, array $data): int
        {
            $tableDescription = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'");
            $tableDescription->execute();
            $columnNames = $tableDescription->fetchAll(PDO::FETCH_COLUMN);

            $handleUpdatedAt = in_array('updated_at', $columnNames); // bool | int

            $keys = array_keys($data);
            $values = array_values($data);
            $setClause = implode('=?,', $keys) . '=?';
            $sql = "UPDATE $table SET $setClause WHERE id = ?" . ($handleUpdatedAt ? ' AND updated_at = NOW()' : '');

            $values[] = $id;
            if ($this->debugger) {
                echo '<h4>UPDATE</h4>';
                echo '<pre><code class="language-sql">' . $sql . '</code></pre>';
                echo '<h4 style="margin-left: 12px; color: forestgreen">values</h4>';
                echo '<pre style="margin-left: 12px"><code class="language-js">' . json_encode($data) . '</code></pre>';
                return 0;
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

            if ($this->debugger) {
                echo '<h3>DELETE' . ($isConditional ? ' (CONDITIONAL)' : '') . '</h3>';
                echo '<pre><code class="language-sql">' . $sql . '</code></pre>';
                if ($isConditional) {
                    echo '<h4 style="margin-left: 12px; color: forestgreen">values</h4>';
                    echo '<pre style="margin-left: 12px"><code class="language-js">' . json_encode($conditions) . '</code></pre>';
                }
                return 0;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values ?? []);
            return $stmt->rowCount();
        }
    }
