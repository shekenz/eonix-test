<?php

namespace API;

class View
{
    public static function render(array $data): void
    {
        if(count($data) > 0)
        {
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
        }

        else
        {
            self::notFound();
        }
    }

    public static function notFOund(): void
    {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        header('Content-Type: application/json; charset=utf-8');
    }

    public static function methodNotAllowed(): void
    {
        header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
        header('Content-Type: application/json; charset=utf-8');
    }

    public static function serverError(bool $die = false): void
    {
        header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
        header('Content-Type: application/json; charset=utf-8');
        if($die) { die(); }
    }

    public static function badGateway(): void
    {
        header($_SERVER['SERVER_PROTOCOL']." 502 Bad Gateway");
        header('Content-Type: application/json; charset=utf-8');
    }
}