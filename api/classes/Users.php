<?php

namespace API;

use API\Database;
use API\Logger;
use PDOException;

class Users
{
    private $db;
    private $handler;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->handler = $this->db->handler();
        $this->logger = Logger::getInstance();
    }
    
    /**
     * testDatabase
     *
     * This method tries to execute a $callback (which executes an SQL statement).
     * If the database does not exist, we try to create it with Database->init() method,
     * and execute the callback again. Database->init() will send an error 500 and kill
     * the script if it cannot create the database (and the table).
     * This allows us to try to create missing database only if needed, so we avoid checking
     * for it at every client requests.
     * 
     * @param  mixed $callback
     * @return void
     */
    private function testDatabase(callable $callback)
    {
        try
        {
            return $callback();
        }

        catch(PDOException $e)
        {
            if(isset($e->errorInfo) && $e->errorInfo[1] === 1046) // Error 1046 : No database selected
            {
                $this->db->init();
                // Retry query after initiating db
                return $callback();
            }

            else
            {
                $this->logger->error('Caught unhandled PDOException : '.$e->getMessage());
            }
        }
    }
    
    /**
     * getCallback
     *
     * @param  mixed $id
     * @return array
     */
    private function getCallback(int $id = 0): array
    {
        $this->handler->query('SELECT * FROM users');
        return [];
    }
    
    /**
     * get wrapper
     *
     * @param  mixed $id
     * @return array
     */
    public function get(int $id = 0): array
    {
        return $this->testDatabase(function() use ($id) { return $this->getCallback($id); });
    }
    
    /**
     * createCallback
     *
     * @param  mixed $id
     * @return void
     */
    private function createCallback(int $id = 0): void
    {
        // Creates a new user
        $this->handler->query();
    }
    
    /**
     * create wrapper
     *
     * @return void
     */
    public function create(): void
    {
        $this->testDatabase([$this, 'createCallback']);
    }
    
    /**
     * updateCallback
     *
     * @return boolean
     */
    private function updateCallback()
    {
        $this->handler->query();
    }
    
    /**
     * update wrapper
     *
     * @return void
     */
    public function update()
    {
        $this->testDatabase([$this, 'updatedCallback']);
    }
    
    /**
     * deleteCallback
     *
     * @return void
     */
    private function deleteCallback()
    {
        $this->handler->query();
    }
    
    /**
     * delete wrapper
     *
     * @return void
     */
    public function delete()
    {
        $this->testDatabase([$this, 'deleteCallback']);
    }
}