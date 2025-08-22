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

    /**
     * List requests assigned to a manager (newest first).
     * @return array<int, array<string,mixed>>
     */
    public static function findByManager(int $managerId): array
    {
        $stmt = Bootstrap::$db->prepare("
            SELECT vr.id, vr.employee_id, vr.manager_id,
            vr.start_date, vr.end_date, vr.reason,
            vr.status, vr.submitted_at, vr.processed_at,
            u.name  AS employee_name,
            u.email AS employee_email
            FROM vacation_requests vr
            JOIN users u ON u.id = vr.employee_id
            WHERE vr.manager_id = ?
            ORDER BY (vr.status = 'pending') DESC, vr.submitted_at DESC
        ");

        $stmt->execute([$managerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns count of pending vacation requests of specific manager
     * @param int $managerId
     * @return int
     */
    public static function countPendingByManager(int $managerId): int
    {
        $stmt = Bootstrap::$db->prepare("SELECT COUNT(*) FROM vacation_requests WHERE manager_id=? AND status='pending'");
        $stmt->execute([$managerId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Returns one manager's vacation request
     * @param int $id
     * @param int $managerId
     * @return VacationRequest|null
     */
    public static function findOneForManager(int $id, int $managerId): ?VacationRequest
    {
        $stmt = Bootstrap::$db->prepare("SELECT * FROM vacation_requests WHERE id=? AND manager_id=? LIMIT 1");
        $stmt->execute([$id, $managerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new VacationRequest($row) : null;
    }

    /**
     * Check if there is a vacation request that overlaps with the new one
     * @param int $employeeId
     * @param string $start
     * @param string $end
     * @param int|null $excludeId
     * @return bool
     */
    public static function overlaps(int $employeeId, string $start, string $end, ?int $excludeId = null): bool
    {
        $sql = "
            SELECT 1 FROM vacation_requests
            WHERE employee_id=?
            AND status IN ('pending','approved')
            AND NOT (end_date < ? OR start_date > ?)
        ";
        $params = [$employeeId, $start, $end];

        if ($excludeId) {
            $sql .= " AND id<>? ";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1";

        $stmt = Bootstrap::$db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Validate vacation request input.
     *
     * @param int $employeeId
     * @param string|null $start
     * @param string|null $end
     * @param string|null $reason
     * @param int|null $ignoreId Optional request id to ignore when checking overlaps (for update)
     * @return array<string,string> Errors array
     */
    public static function validate(
        int $employeeId,
        ?string $start,
        ?string $end,
        ?string $reason,
        ?int $ignoreId = null
    ): array {
        $errors = [];

        if (!$start) {
            $errors['start_date'] = 'Start date is required';
        }
        if (!$end) {
            $errors['end_date'] = 'End date is required';
        }
        if (!$reason) {
            $errors['reason'] = 'Reason is required';
        }
        if ($start && $end && $end < $start) {
            $errors['end_date'] = 'End date cannot be earlier than start date';
        }
        if ($start && $end && self::overlaps($employeeId, $start, $end, $ignoreId)) {
            $errors['start_date'] = 'Your vacation request overlaps with an existing one.';
        }

        return $errors;
    }
}
