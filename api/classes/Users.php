<?php

namespace API;

use API\Database;
use API\Logger;
use PDOException;
use PDO;

/**
 * Users controller
 * 
 */
class Users
{
    private $db;
    private $handler;
    private $logger;
    private $data;

    public function __construct(array|null $data = [])
    {
        $this->db = Database::getInstance();
        $this->handler = $this->db->handler();
        $this->logger = Logger::getInstance();

        // JSON data
        if(null !== $data)
        {
            $this->data = $data;
        }
        
        else
        {
            View::error(['JSON badly formated']);
        }
    }
    
    /**
     * Tests if table exists before executing an callback
     *
     * This method tries to execute a $callback (which executes an SQL statement).
     * If the table does not exist, we try to create it with Database->init('table') method,
     * and execute the callback again. Database->init() will send an error 500 and kill the
     * script if it cannot create the table.
     * This allows us to try to create missing table only when needed, so we avoid checking
     * for it at every client requests.
     * 
     * @param  callable $callback
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
     * Trims out data from empty spaces and invisible characters
     *
     * @return string
     */
     private function cleanData(string $data): string
     {
        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $data));
     }
    
    /**
     * Fetches users in database
     * 
     * If ID is provided, fetches the specific user.
     * If ID is not provided, fetches all users and filter result with provided filters.
     *
     * @param  string $id (optional) ID of the user to retrieve
     * @return array
     */
    private function getCallback(string $id = ''): array
    {
        // If no id is specified, we return all users matching the filter
        if($id === '')
        {
            // Default values ans init
            $firstnamePresent = array_key_exists('firstname', $this->data);
            $lastnamePresent = array_key_exists('lastname', $this->data);
            $firstnameFilter = '';
            $lastnameFilter = '';
            $filterLink = 'AND';
            
            // Building query depending on presence of the filters
            $query = 'SELECT * FROM users WHERE ';

            if($firstnamePresent || $lastnamePresent)
            {
                if($firstnamePresent)
                {
                    $firstnameFilter = $this->cleanData($this->data['firstname']);
                    $query .= '(firstname LIKE :firstnameFilterStart OR firstname LIKE :firstnameFilterEnd)';

                    // Joining queries together
                    (!$lastnamePresent) ?: $query .= ' '.$filterLink.' ';
                }

                if($lastnamePresent)
                {
                    $lastnameFilter = $this->cleanData($this->data['lastname']);
                    $query .= '(lastname LIKE :lastnameFilterStart OR lastname LIKE :lastnameFilterEnd)';
                }
            }

            // WHERE = 1
            else{
                $query .= '1';
            }

            // Preparing query
            $statement = $this->handler->prepare($query);

            // Binding values depending on presence of the filters
            if($firstnamePresent)
            {
                $statement->bindValue(':firstnameFilterStart', $firstnameFilter.'%');
                $statement->bindValue(':firstnameFilterEnd', '%'.$firstnameFilter);
            }

            if($lastnamePresent)
            {
                $statement->bindValue(':lastnameFilterStart', $lastnameFilter.'%');
                $statement->bindValue(':lastnameFilterEnd', '%'.$lastnameFilter);
            }

            // Execute query, fetch data
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        }
        
        // Else we return the specific user by its id
        else
        {
            $statement = $this->handler->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $statement->bindValue(':id', hex2bin($id), PDO::PARAM_LOB);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        
        foreach($result as $index => $user)
        {
            // Unpacking id binary
            $result[$index]['id'] = bin2hex($user['id']);
        }
        
        // Replace $result with empty array when user id is not found (result === null)
        (null !== $result) ?: $result = [];

        // If only 1 user is in array and we were looking only for 1 user, collapse row
        return (count($result) == 1 && $id !== '') ? $result[0] : $result;
    }
    
    /**
     * Safely executes getCallback()
     *
     * @param  array $data (optional) Data from the router
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
     * Method to insert multiple user at once
     * 
     * //TODO Work in progress
     * 
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
    
    /**
     * Creates a new user with the provided JSON data
     *
     * @return array
     */
    private function createCallback(): array
    {
        // Check if data is present and sanitize
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

        // Returns inserted data to client
        return [
            'id' => bin2hex($binGUID),
            'firstname' => $this->data['firstname'],
            'lastname' => $this->data['lastname']
        ];
    }
    
    /**
     * Safely executes createCallback()
     *
     * @return array
     */
    public function create(): array
    {
        $result = (array) $this->testDatabase([$this, 'createCallback']);
        return View::buffer($result);
    }
    
    /**
     * Updates user information with provided JSON data
     *
     * @param string $id ID of the user to update
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
            // Data is optional
            $firstname = (array_key_exists('firstname', $this->data)) ? $this->cleanData($this->data['firstname']) : $user['firstname'];
            $lastname = (array_key_exists('lastname', $this->data)) ? $this->cleanData($this->data['lastname']) : $user['lastname'];
        } 
    
        else
        {
            View::notFound();
        }

        $statement = $this->handler->prepare('UPDATE users SET firstname = :firstname, lastname = :lastname WHERE id = :id');
        $statement->bindValue(':id', hex2bin($id), \PDO::PARAM_LOB);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->execute();

        // Returns inserted data to client
        return [            
            'id' => $id,
            'firstname' => $firstname,
            'lastname' => $lastname
        ];

    }
    
    /**
     * Safely executes updateCallback()
     *
     * @param array $data Data from the router
     * @return array
     */
    public function update(array $data): array
    {
        $result = (array) $this->testDatabase(function() use ($data) { return $this->updateCallback($data['id']); });
        return View::buffer($result);
    }
    
    /**
     * Deletes a specific user with its ID
     *
     * @param string $id GUID (16bytes) of the user
     * @return bool Returns false if nothing got deleted
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
     * Safely executes deleteCallback()
     *
     * @param array $data Data from the router
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