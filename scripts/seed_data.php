<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Bootstrap;

Bootstrap::init();
/** @var PDO $db */
$db = Bootstrap::$db;

/**
 * Fetch user id by email.
 */
function getUserIdByEmail(PDO $db, string $email): ?int {
    $q = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $q->execute([$email]);
    $id = $q->fetchColumn();
    return $id ? (int)$id : null;
}

/**
 * Ensure a specific vacation request (by employee, manager, start/end) exists.
 * If exists, update status/reason/processed_at; else insert new.
 *
 * @return int request id
 */
function ensureVacationRequest(
    PDO $db,
    int $employeeId,
    int $managerId,
    string $startDate,
    string $endDate,
    string $reason,
    string $status
): int {
    // Is there already a request for this exact range?
    $sel = $db->prepare('
        SELECT id FROM vacation_requests
        WHERE employee_id=? AND manager_id=? AND start_date=? AND end_date=?
        LIMIT 1
    ');
    $sel->execute([$employeeId, $managerId, $startDate, $endDate]);
    $existingId = $sel->fetchColumn();

    $processedAt = ($status === 'approved' || $status === 'rejected') ? date('Y-m-d H:i:s') : null;

    if ($existingId) {
        $upd = $db->prepare('
            UPDATE vacation_requests
            SET reason=?, status=?, processed_at=?
            WHERE id=?
        ');
        $upd->execute([$reason, $status, $processedAt, $existingId]);
        echo "Updated vacation_request #{$existingId} ({$startDate}..{$endDate}) to {$status}\n";
        return (int)$existingId;
    }

    $ins = $db->prepare('
        INSERT INTO vacation_requests
            (employee_id, manager_id, start_date, end_date, reason, status, submitted_at, processed_at)
        VALUES
            (?, ?, ?, ?, ?, ?, NOW(), ?)
    ');
    $ins->execute([$employeeId, $managerId, $startDate, $endDate, $reason, $status, $processedAt]);
    $newId = (int)$db->lastInsertId();
    echo "Created vacation_request #{$newId} ({$startDate}..{$endDate}) with status {$status}\n";
    return $newId;
}

/**
 * Optional: helper to add days to "today" and format Y-m-d.
 */
function dayOffset(int $days): string {
    $d = new DateTimeImmutable('today');
    if ($days !== 0) $d = $d->modify(($days >= 0 ? '+' : '').$days.' days');
    return $d->format('Y-m-d');
}

// ---- Make sure users exist (you can also run your seed_users first) ----
$managerEmail  = 'manager@email.gr';
$employeeEmail = 'bogdan@email.gr';

$managerId  = getUserIdByEmail($db, $managerEmail);
$employeeId = getUserIdByEmail($db, $employeeEmail);

if (!$managerId || !$employeeId) {
    fwrite(STDERR, "Manager/Employee not found. Run your users seeder first (composer seed:users) or adjust emails.\n");
    exit(1);
}

// ---- Create a few example requests ----
// 1) Pending (future)
ensureVacationRequest(
    $db,
    $employeeId,
    $managerId,
    dayOffset(+14),  // start in 2 weeks
    dayOffset(+18),  // 5 days
    'Family trip (pending)',
    'pending'
);

// 2) Approved (past)
ensureVacationRequest(
    $db,
    $employeeId,
    $managerId,
    dayOffset(-30),  // last month
    dayOffset(-27),
    'Doctor appointment (approved)',
    'approved'
);

// 3) Rejected (past different range)
ensureVacationRequest(
    $db,
    $employeeId,
    $managerId,
    dayOffset(-10),
    dayOffset(-8),
    'Short getaway (rejected)',
    'rejected'
);

echo "Vacation requests seeding complete.\n";
