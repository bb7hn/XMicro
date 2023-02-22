<?php 
function response(array $MessageArray = ['messgae' => 'OK', 'data' => null], int $Code = 200)
{
    http_response_code($Code);
    echo json_encode($MessageArray);
}