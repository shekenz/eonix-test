<?php

namespace API;

use API\Database;
use API\Logger;
use PDOException;
use PDO;

class Users
{
    private $db;
    private $handler;
    private $logger;
    private $data;

    public function __construct(array $data = [])
    {
        $this->db = Database::getInstance();
        $this->handler = $this->db->handler();
        $this->logger = Logger::getInstance();

        // JSON data
        $this->data = $data;
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
                View::serverError();
            }
        }
    }
    
    /**
     * cleanData
     * 
     * Trims data from empty spaces and invisible characters.
     *
     * @return string
     */
     private function cleanData(string $data): string
     {
        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $data));
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
            $id = hex2bin($id);

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
    public function get(array $data = []): array
    {
        $id = $data['id'] ?? '';
        $result = (array) $this->testDatabase(function() use ($id) { return $this->getCallback($id); });

        if(empty($result))
        {
            View::notFound(false);
            return [];
        }
        
        else {
            return View::buffer($result);
        }
    }
    
    /**
     * createMultipleCallback
     * 
     * //TODO Work in progress
     * Method to insert multiple user at once
     *
     * @param  mixed $id
     * @return array
     */
    /*
    private function createMultipleCallback()//: array
    {
        // Needs data sanitation an validation

        $query = 'INSERT INTO users(id, firstname, lastname) VALUES ';

        // Building query string
        foreach($this->data as $index => $value)
        {
            if(is_int($index))
            {
                $query .= '(:id_'.$index.', :firstname_'.$index.', :lastname_'.$index.'),';
            }
        }
        $query = substr($query, 0, -1);

        // Creates a new users
        $statement = $this->handler->prepare($query);

        // Binding values
        foreach($this->data as $index => $value)
        {
            $statement->bindValue(':id_'.$index, md5(uniqid(rand(), true), true), \PDO::PARAM_LOB);
            $statement->bindParam(':firstname_'.$index, $value['firstname']);
            $statement->bindParam(':lastname_'.$index, $value['lastname']);
        }
        
        $statement->execute();
    }
    */

    private function createCallback(): array
    {
        if(array_key_exists('firstname', $this->data) && array_key_exists('lastname', $this->data))
        {
            foreach($this->data as $key => $value)
            {
                $this->data[$key] = $this->cleanData($value);
            }
        }

        else
        {
            View::error();
        }

        // Query
        $binGUID = md5(uniqid(rand(), true), true);
        $statement = $this->handler->prepare('INSERT INTO users(id, firstname, lastname) VALUES (:id, :firstname, :lastname)');
        $statement->bindParam(':id', $binGUID, PDO::PARAM_LOB);
        $statement->bindParam(':firstname', $this->data['firstname']);
        $statement->bindParam(':lastname', $this->data['lastname']);
        $statement->execute();

        return [
            'id' => bin2hex($binGUID),
            'firstname' => $this->data['firstname'],
            'lastname' => $this->data['lastname']
        ];
    }
    
    /**
     * create wrapper
     *
     * @return array
     */
    public function create(): array
    {
        $result = (array) $this->testDatabase([$this, 'createCallback']);
        return View::buffer($result);
    }
    
    /**
     * updateCallback
     *
     * @return array
     */
    private function updateCallback(string $id): array
    {
        // NB: Router enforce the presence of a 16 bytes GUID
        $user = $this->getCallback($id);

        // Preparing data to update
        // NOTE : Would be better to create a query builder but I have no time for that
        if(!empty($user))
        {
            // Post data is opional
            // REFACTOR Needs optimization : No need to cleanData when it is coming from database
            $firstname = $this->cleanData($this->data['firstname'] ?? $user['firstname']);
            $lastname = $this->cleanData($this->data['lastname'] ?? $user['lastname']);
        } 
    
        else
        {
            View::notFound();
        }

        $binId = hex2bin($id);

        $statement = $this->handler->prepare('UPDATE users SET firstname = :firstname, lastname = :lastname WHERE id = :id');
        $statement->bindParam(':id', $binId, \PDO::PARAM_LOB);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->execute();

        return [            
            'id' => $id,
            'firstname' => $firstname,
            'lastname' => $lastname
        ];

    }
    
    /**
     * update wrapper
     *
     * @return void
     */
    public function update(array $data): array
    {
        $result = (array) $this->testDatabase(function() use ($data) { return $this->updateCallback($data['id']); });
        return View::buffer($result);
    }
    
    /**
     * deleteCallback
     *
     * @return void
     */
    private function deleteCallback(string $id): bool
    {
        $binId = hex2bin($id);

        $statement = $this->handler->prepare('DELETE FROM users WHERE id = :id');
        $statement->bindParam(':id', $binId, PDO::PARAM_LOB);
        $statement->execute();
        return (bool) $statement->rowCount();
    }
    
    /**
     * delete wrapper
     *
     * @return void
     */
    public function delete(array $data): void
    {
        // NB: Router enforce the presence of a 16 bytes GUID

        $result = $this->testDatabase(function() use ($data) { return $this->deleteCallback($data['id']); });

        if($result)
        {
            View::buffer();
        }

        else
        {
            View::notFound();
        }

    }
}