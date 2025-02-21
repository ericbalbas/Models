<?php

namespace Employee\Utils\Time;

use Helper\ClockWork;
use Employee\Utils\Model\Worker;
use stdClass;

/**
 * Class Timesheet
 *
 * Manages worker's time records, shifts, and tracking within a specified date range.
 */
class Timesheet
{

    /** @var Worker The worker instance */
    protected $worker;

    /** @var string The current shift */
    protected $shift;

    /** @var array The date range */
    protected $range;

    /** @var array The shift range */
    protected $rangeShift;

    /** @var string The current time record */
    protected $currentTimeRecord;

    /** @var array The time record range */
    protected $rangeTimeRecord;

    /**
     * Timesheet constructor.
     *
     * @param Worker $worker The worker instance.
     */
    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
        $this->shift = 'unknown';
        $this->range = [];
        $this->rangeShift = [];
        $this->currentTimeRecord = '';
        $this->rangeTimeRecord = [];
    }

    /**
     * Get the value of the date range.
     *
     * @return array The date range.
     */
    public function getRange(): array
    {
        return $this->range;
    }

    /**
     * Set the date range for the timesheet.
     *
     * @param array $range The date range.
     * @return self Returns the instance of the Timesheet class.
     */
    public function setRange(array $range): self
    {
        $this->range = $range;
        return $this;
    }

    /**
     * Get the current shift details of the worker.
     *
     * @return stdClass The current shift details.
     */
    public function currentShift(): stdClass
    {
        return (object) ClockWork::getWorkerCurrentShift($this->worker->employeeId);
    }

    /**
     * Get the worker's shift details within the specified range.
     *
     * @return stdClass The shift details within the date range.
     */
    public function shiftWithRange(): stdClass
    {
        return (object) ClockWork::getWorkerShiftWithRange($this->range, $this->worker->employeeId);
    }

    /**
     * Get the worker's daily time record (DTR) within the specified range.
     *
     * @return stdClass The DTR details within the date range.
     */
    public function DTRWithRange(): stdClass
    {
        return (object) ClockWork::getWorkerDTRWithRange($this->range, $this->worker->idNumber);
    }

    /**
     * Get the current daily time record (DTR) of the worker.
     *
     * @return stdClass The current DTR details.
     */
    public function currentDTR(): stdClass
    {
        return (object) ClockWork::getWorkerCurrentDTR($this->worker->idNumber);
    }

    /**
     * Get the total working days within the specified date range.
     *
     * @return array The total working days.
     */
    public function totalWorkingDays(): array
    {
        return ClockWork::getTotalWorkingDays($this->range);
    }

    /**
     * Get the worker's absent days within the specified date range.
     *
     * @return array The list of absent days.
     */
    public function workerAbsences(): array
    {
        return ClockWork::getWorkerAbsences($this->worker->idNumber, $this->range);
    }
}
 