<?php

namespace App\Repositories;

use App\Core\Bootstrap;
use App\Models\VacationRequest;
use PDO;

class VacationRequestRepository
{
    /**
     * Find all vacation requests by employee id.
     *
     * @return VacationRequest[]
     */
    public static function findByEmployee(int $employeeId): array
    {
        $stmt = Bootstrap::$db->prepare("SELECT * FROM vacation_requests WHERE employee_id=? ORDER BY submitted_at DESC");
        $stmt->execute([$employeeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new VacationRequest($r), $rows);
    }

    /**
     * Find the specific request of employee
     * @param int $id
     * @param int $employeeId
     * @return VacationRequest|null
     */
    public static function findOneOwned(int $id, int $employeeId): ?VacationRequest
    {
        $stmt = Bootstrap::$db->prepare("SELECT * FROM vacation_requests WHERE id=? AND employee_id=? LIMIT 1");
        $stmt->execute([$id, $employeeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new VacationRequest($row) : null;
    }
}
