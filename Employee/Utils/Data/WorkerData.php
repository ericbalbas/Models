<?php

namespace Employee\Utils\Data;

use Core\Database;
use Exception;

/**
 * Class WorkerData
 *
 * Handles retrieval of employee data from the database.
 */
class WorkerData
{
    /** 
     * @var object Holds the worker data as an object 
     */
    protected $data;

    /**
     * Private constructor to prevent direct instantiation.
     *
     * @param object $data The worker's data retrieved from the database
     */
    private function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Retrieve an instance of WorkerData based on the employee ID.
     *
     * @param string $id The employee's ID
     * @return WorkerData Returns an instance of WorkerData
     * @throws Exception If the worker data is not found
     */
    public static function getInstance(string $id): WorkerData
    {
        // Fetch worker data from the database
        $workerData = Database::fetchSql("
            SELECT *, CONCAT(firstName, ' ', surName) AS fullName 
            FROM hr_employee 
            WHERE idNumber = '$id'
        ")[0] ?? null;

        if (!$workerData) {
            throw new Exception("Error Processing Request at WorkerData Class: Employee not found", 1);
        }

        return new self($workerData); // Store as WorkerData object
    }

    /**
     * Magic getter to retrieve properties dynamically.
     *
     * @param string $key The property name
     * @return mixed|null Returns the property value if found, otherwise null
     */
    public function __get(string $key)
    {
        return $this->data->$key ?? null;  // Use -> for stdClass access
    }
}
