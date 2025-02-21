<?php

namespace Helper;

use Core\Database;

class ClockWork extends Database
{
    /**
     * Builds a SQL range condition for date filtering.
     *
     * @param array $range An array containing two date values [from, to].
     * @return string SQL BETWEEN clause.
     * @throws \Exception If the range is empty.
     */
    private static function rangeBuilder(array $range)
    {
        if (!$range) throw new \Exception("Error Processing Request At rangeBuilder ", 1);
        list($from, $to) = $range;
        return "BETWEEN '$from' AND '$to'";
    }

    /**
     * Retrieves all holidays within a given date range.
     *
     * @param array $range An array containing two date values [from, to].
     * @return array Returns all records from the hr_holiday table as an array of stdClass objects.
     */
    public static function getHolidays(array $range): array
    {
        $rangeClause = $range ? "WHERE holidayDate " . self::rangeBuilder($range) : "";
        $holidays = Database::fetchSql("SELECT * FROM hr_holiday $rangeClause ORDER BY holidayDate DESC");
        return $holidays ? $holidays : [];
    }

    /**
     * Retrieves the current shift details of a worker.
     *
     * @param string $employeeId The ID of the employee.
     * @return array Returns all records from the hr_shiftcalendar and hr_shift tables for the given employee as an array of stdClass objects.
     */
    public static function getWorkerCurrentShift(string $employeeId): \stdClass
    {
        $shiftData =  Database::fetchSql("SELECT * FROM hr_shiftcalendar INNER JOIN hr_shift USING (shiftId) WHERE employeeId ='$employeeId' LIMIT 1");
        return $shiftData ? $shiftData[0] : (object)[];
    }

    /**
     * Retrieves shift details of a worker within a given date range.
     *
     * @param array $range An array containing two date values [from, to].
     * @param string $employeeId The ID of the employee.
     * @return array Returns all records from the hr_shiftcalendar and hr_shift tables within the specified date range as an array of stdClass objects.
     * @throws \Exception If the range is empty.
     */
    public static function getWorkerShiftWithRange(array $range, string $employeeId): array
    {
        if (!$range) throw new \Exception("Error Processing Request At getWorkerShiftWithRange ", 1);
        $shiftData =  Database::fetchSql("SELECT * FROM hr_shiftcalendar INNER JOIN hr_shift USING (shiftId) WHERE employeeId ='$employeeId' AND shiftDate " . self::rangeBuilder($range));
        return $shiftData ? $shiftData : [];
    }

    /**
     * Retrieves the current daily time record (DTR) of a worker.
     *
     * @param string $employeeId The ID of the employee.
     * @return array Returns the current DTR record from the hr_dtr table as an stdClass object.
     * @throws \Exception If the employee ID is not provided.
     */
    public static function getWorkerCurrentDTR(string $employeeId): array
    {
        if (!$employeeId) throw new \Exception("Error Processing Request At getWorkerCurrentDTR", 1);
        $dtrData = Database::fetchSql("SELECT * FROM hr_dtr WHERE employeeId LIKE '$employeeId' ORDER BY timeIn DESC LIMIT 1");
        return $dtrData ? $dtrData : [];
    }

    /**
     * Retrieves the daily time record (DTR) of a worker within a given date range.
     *
     * @param array $range An array containing two date values [from, to].
     * @param string $employeeId The ID of the employee.
     * @return array Returns all records from the hr_dtr table within the specified date range as an array of stdClass objects.
     * @throws \Exception If the range is empty.
     */
    public static function getWorkerDTRWithRange(array $range, string $employeeId): array
    {
        if (!$range && !$employeeId) throw new \Exception("Error Processing Request At getWorkerDTRWithRange", 1);
        $dtrData = Database::fetchSql("SELECT * FROM hr_dtr WHERE employeeId LIKE '$employeeId' AND DATE(timeIn) " . self::rangeBuilder($range));
        return $dtrData ? $dtrData : [];
    }

    /**
     * Get the total working days within a specified range, excluding Sundays.
     *
     * @param array $range The date range [start_date, end_date]
     * @param int $returnType If 1, return count; otherwise, return array of dates
     * @return int|array The total working days count or list of working dates
     * @throws \Exception If an invalid range is provided
     */
    public static function getTotalWorkingDays(array $range, int $returnType = 0)
    {
        if (!$range || count($range) !== 2) {
            throw new \Exception("Invalid date range provided in getTotalWorkingDays", 1);
        }

        $startDate = new \DateTime($range[0]);
        $endDate = new \DateTime($range[1]);
        $endDate->modify('+1 day'); // Include the end date

        $totalWorkingDays = [];

        while ($startDate < $endDate) {
            if ($startDate->format('w') != 0) { // Exclude Sundays (0 = Sunday)
                $totalWorkingDays[] = $startDate->format('Y-m-d');
            }
            $startDate->modify('+1 day');
        }

        $holidays = self::getHolidays($range) ?? [];
        $holidayDates = array_column($holidays, 'holidayDate');
        $totalWorkingDays = array_diff($totalWorkingDays, $holidayDates);
        return $returnType == 1 ? count($totalWorkingDays) : array_values($totalWorkingDays);
    }

    /**
     * Get the list of absent days for a worker within a given date range.
     *
     * @param string $employeeId The employee's unique identifier.
     * @param array $range The date range in the format [start_date, end_date].
     * @param int $returnType If 1, returns the count of absences; otherwise, returns an array of absent dates.
     * @return int|array The number of absences or an array of absent dates.
     * @throws \Exception If an invalid range or employee ID is provided.
     */
    public static function getWorkerAbsences(string $employeeId, array $range = [], int $returnType = 0)
    {
        if (!$range || !$employeeId) {
            throw new \Exception("Error Processing Request At getWorkerAbsences", 1);
        }

        $workerDTR = self::getWorkerDTRWithRange($range, $employeeId);
        $workingDays = self::getTotalWorkingDays($range);

        $workedDays = [];
        foreach ($workerDTR as $entry) {
            $date = date('Y-m-d', strtotime($entry->timeIn));
            $workedDays[$date] = $entry; // Store the full object for later half-day checks
        }

        // Find absent days by filtering $workingDays
        $absentDays = array_filter($workingDays, function ($date) use ($workedDays) {
            // TODO: Add logic for undertime detection.
            return !isset($workedDays[$date]); // Keep only dates NOT in workedDays
        });

        return $returnType == 1 ? count($absentDays) : array_values($absentDays);
    }

}