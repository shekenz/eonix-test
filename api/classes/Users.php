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
    private $receivedData;

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
                View::serverError();
            }
        }
    }
    
    /**
     * validatePostData
     * 
     * Trims firstname and last name from empty spaces and invisible characters,
     * and send a Error view if one of them is empty
     *
     * @return void
     */
    private function validatePostData(bool $strict = true): void
    {
        // Stripping invisible characters
        if (array_key_exists('firstname', $_POST))
        {
            $_POST['firstname'] = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $_POST['firstname']));
        }

        if (array_key_exists('lastname', $_POST))
        {
            $_POST['lastname'] = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $_POST['lastname']));
        }

        // Data validation
        if ($strict)
        {
            $errors = [];
            if(empty($_POST['firstname'])) { array_push($errors, 'Firstname is required'); }
            if(empty($_POST['lastname'])) { array_push($errors, 'Lastname is required'); }
            if(!empty($errors)) { View::error($errors); }
        }
    }
    
    /**
     * getData
     * 
     * Returns the previously fetched data, which represent the data before calling the View class.
     * This is usefull to test the data.
     *
     * @return void
     */
    public function getData()
    {
        return $this->receivedData;
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
        $this->receivedData = (array) $this->testDatabase(function() use ($id) { return $this->getCallback($id); });
        return View::buffer($this->receivedData);
    }
    
    /**
     * createCallback
     *
     * @param  mixed $id
     * @return array
     */
    private function createCallback(): array
    {
        // Validation
        $this->validatePostData();

        // Creates a new user
        $binGUID = md5(uniqid(rand(), true), true);
        $statement = $this->handler->prepare('INSERT INTO users(id, firstname, lastname) VALUES (:id, :firstname, :lastname)');
        $statement->bindParam(':id', $binGUID, \PDO::PARAM_LOB);
        $statement->bindParam(':firstname', $_POST['firstname']);
        $statement->bindParam(':lastname', $_POST['lastname']);
        $statement->execute();

        return [
            'id' => bin2hex($binGUID),
            'firstname' => $_POST['firstname'],
            'lastname' => $_POST['lastname']
        ];
    }
    
    /**
     * create wrapper
     *
     * @return array
     */
    public function create(): array
    {
        $this->receivedData = (array) $this->testDatabase([$this, 'createCallback']);
        return View::buffer($this->receivedData);
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
        
        // Validation
        $this->validatePostData(false);

        // Preparing data to update
        // Would be better to create a query builder but I have no time for that
        if(!empty($user))
        {
            // Post data is opional
            $firstname = $_POST['firstname'] ?? $user['firstname'];
            $lastname = $_POST['lastname'] ?? $user['lastname'];
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
        $this->receivedData = (array) $this->testDatabase(function() use ($data) { return $this->updateCallback($data['id']); });
        return View::buffer($this->receivedData);
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