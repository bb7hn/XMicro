<?php

    function response(array $MessageArray = ['message' => 'OK', 'data' => null], int $Code = 200): void
    {
        header('Content-type: application/json; charset=utf-8');
        http_response_code($Code);
        echo json_encode(is_array($MessageArray)?$MessageArray:['warn'=>"check your response array"]);
    }
