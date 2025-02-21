<?php

namespace Employee\Utils\Model;

use Employee\Utils\Data\WorkerData;

/**
 * Class Worker
 *
 * Represents an employee model that provides structured access to worker data.
 */
class Worker
{
    /** @var WorkerData The worker data instance */
    protected $workerData;

    /**
     * Worker constructor.
     *
     * @param WorkerData $data The worker data instance containing employee details.
     */
    public function __construct(WorkerData $data)
    {
        $this->workerData = $data;
    }

    /**
     * Magic getter to dynamically access worker properties.
     *
     * @param string $property The property name.
     * @return mixed|null The property value if found, otherwise null.
     */
    public function __get($property)
    {
        return $this->workerData->$property ?? null;
    }

    /**
     * Displays structured data for debugging or output purposes.
     *
     * @param mixed $data The data to display.
     * @return void
     */
    public static function __displayData($data)
    {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}
