<?php

namespace API;

use API\Database;
use API\Logger;
use Exception;
use PDOException;
use PDO;

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
     * If the table does not exist, we try to create it with Database->init('table') method,
     * and execute the callback again. Database->init() will send an error 500 and kill the
     * script if it cannot create the table.
     * This allows us to try to create missing table only when needed, so we avoid checking
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
            if(isset($e->errorInfo) && $e->errorInfo[1] === 1146) // Error 1146 : Base table or view not found
            {
                $this->db->init('table');
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
    private function getCallback(string $id = ''): array
    {
        if($id === '')
        {
            $statement = $this->handler->query('SELECT * FROM users');
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        }
        
        else
        {
            $id = pack('H*', $id); // equivalent of hex2bin()

            $statement = $this->handler->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $statement->bindParam(':id', $id, PDO::PARAM_LOB);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        
        foreach($result as $index => $user)
        {
            $result[$index]['id'] = bin2hex($user['id']);
        }
        
        // Replace $result with empty array when user id is not found (result === null)
        (null !== $result) ?: $result = [];

        // If only 1 is in array and we were looking only for 1 user, collapse row
        return (count($result) == 1 && $id !== '') ? $result[0] : $result;
    }
    
    /**
     * get wrapper
     *
     * @param  mixed $id
     * @return array
     */
    public function get(array $data)
    {
        $id = $data['id'] ?? '';
        echo '<pre>'.print_r($this->testDatabase(function() use ($id) { return $this->getCallback($id); }), true).'</pre>';
    }
    
    /**
     * createCallback
     *
     * @param  mixed $id
     * @return void
     */
    private function createCallback(string $firstname = 'john', string $lastname = 'doe'): void
    {
        // Creates a new user
        $binGUID = md5(uniqid(rand(), true), true);
        $statement = $this->handler->prepare('INSERT INTO users(id, firstname, lastname) VALUES (:id, :firstname, :lastname)');
        $statement->bindParam(':id', $binGUID, \PDO::PARAM_LOB);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->execute();

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