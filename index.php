<?php
require_once 'autoload_models.php';

use Employee\Utils\Data\WorkerData;
use Employee\Utils\Model\Worker;
use Employee\Utils\Time\Timesheet;

try {
    // Initialize WorkerData and Worker Model
    $workerData = WorkerData::getInstance('1163');
    $worker = new Worker($workerData);
    $timesheet = new Timesheet($worker);

    // Set date range
    $range = ['2025-02-01', '2025-02-10'];
    $timesheet->setRange($range);

    // Fetch data
    $currentShift = $timesheet->currentShift();
    $shiftRange = $timesheet->shiftWithRange();
    $DTR = $timesheet->DTRWithRange();
    $currentDTR = $timesheet->currentDTR();
    $workingDays = $timesheet->totalWorkingDays();
    $absences = $timesheet->workerAbsences();

    // Display results
    echo "<h2>Worker Information</h2>";
    $worker::__displayData($workerData);

    echo "<h2>Current Shift</h2>";
    $worker::__displayData($currentShift);

    echo "<h2>Shift with Range</h2>";
    $worker::__displayData($shiftRange);

    echo "<h2>Daily Time Record (DTR) with Range</h2>";
    $worker::__displayData($DTR);

    echo "<h2>Current DTR</h2>";
    $worker::__displayData($currentDTR);

    echo "<h2>Total Working Days</h2>";
    $worker::__displayData($workingDays);

    echo "<h2>Worker Absences</h2>";
    $worker::__displayData($absences);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?> 



