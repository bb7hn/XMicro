<?php

    function response(array $MessageArray = ['message' => 'OK', 'data' => null], int $Code = 200): void
    {
        http_response_code($Code);
        echo json_encode(is_array($MessageArray)?$MessageArray:['warn'=>"check your response array"]);
    }
