<?php

namespace API;

/**
 * View
 */
class View
{
    private static $headers = [];
    private static $buffer;

    public static function buffer(array $data = []): array
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 200 OK');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');

        if(!empty($data))
        {
            self::$buffer = json_encode($data);
        }

        return $data;
    }

    public static function error(array $messages = [], bool $die = true): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');
        
        if(!empty($data))
        {
            self::$buffer = json_encode(['errors' => $messages]);
        }

        if($die)
        {
            self::render();
        }
    }

    public static function notFound(bool $die = true): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');

        if($die)
        {
            self::render();
        }
    }

    public static function methodNotAllowed(): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');
    }

    public static function serverError(bool $die = true): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');
        
        if($die)
        {
            self::render();
        }
    }

    public static function badGateway(): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL']." 502 Bad Gateway");
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');
    }

    public static function render(): void
    {
        foreach(self::$headers as $header)
        {
            header($header);
        }

        echo self::$buffer;

        exit();

    }
}