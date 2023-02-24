<?php

    function response(array $MessageArray = ['messgae' => 'OK', 'data' => null], int $Code = 200): void
    {
        http_response_code($Code);
        echo json_encode($MessageArray);
    }

    function getWhereQuery($Where)
    {
        $result = "";
        foreach ($Where as $key => $value) {
            if (gettype($value) === 'array') {
                $result .= "`$key` in ('" . join("', '", `$value`) . "') AND ";
                unset($Where[$key]);
                continue;
            }

            if (gettype($value) === 'boolean') {
                $result .= "`$key` IS " . ($value ? '' : 'NOT ') . 'NULL AND ';
                unset($Where[$key]);
                continue;
            }

            $result .= "`$key` = :$key AND ";
        }
    }
