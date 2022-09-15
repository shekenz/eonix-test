<?php

namespace API;

/**
 * Aggregate returned data in a buffer and sets response headers
 * 
 */
class View
{
    private static $headers = [];
    private static $buffer;
    
    /**
     * Aggregates data in the output buffer
     * 
     * Sets response code to '200 OK'
     *
     * @param  array $data (optional) Data to buffer
     * @return array
     */
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
    
    /**
     * Aggregates errors and encode errors JSON object
     *
     * This method should be used to inform client that his request has errors. 
     * Sets response code to '400 Bad Request'
     *
     * @param  array $messages Array of errors
     * @param  bool $die Quits sript and send response right away
     * @return void
     */
    public static function error(array $messages = [], bool $die = true): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');
        
        if(!empty($messages))
        {
            self::$buffer = json_encode(['errors' => $messages]);
        }

        if($die)
        {
            self::render();
        }
    }
    
    /**
     * Sends a 404 error
     * 
     * Sets response code to '404 Not Found'
     *
     * @param  bool $die (optional) (default=true) Quits sript and send response right away
     * @return void
     */
    public static function notFound(bool $die = true): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');

        if($die)
        {
            self::render();
        }
    }

    /**
     * Sends a 405 error
     * 
     * Sets response code to '405 Method Not Allowed'
     *
     * @return void
     */
    public static function methodNotAllowed(): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');
    }

    /**
     * Sends a 500 error
     * 
     * Sets response code to '500 Internal Server Error'
     *
     * @param  bool $die (optional) (default=true) Quits sript and send response right away
     * @return void
     */
    public static function serverError(bool $die = true): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');
        
        if($die)
        {
            self::render();
        }
    }

    /**
     * Sends a 415 error
     * 
     * Sets response code to '415 Unsupported Media Type'
     *
     * @param  bool $die (optional) (default=true) Quits sript and send response right away
     * @return void
     */
    public static function wrongContentType(bool $die = true): void
    {
        array_push(self::$headers, $_SERVER['SERVER_PROTOCOL'].' 415 Unsupported Media Type');
        array_push(self::$headers, 'Content-Type: application/json; charset=utf-8');

        if($die)
        {
            self::render();
        }
    }
    
    /**
     * Sets the headers and sends aggregated data to client
     *
     * @return void
     */
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