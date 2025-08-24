<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Bootstrap;

Bootstrap::init();
$db = Bootstrap::$db;

/**
 * Find a unique username (append -1, -2 if needed).
 * @param PDO $db
 * @param string $base
 * @param int|null $excludeId
 * @return string
 */
function isUsernameUnique(PDO $db, string $base, ?int $excludeId = null): string {
    $base = strtolower(preg_replace('/[^a-z0-9_\.\-]/i', '', $base));
    if ($base === '') $base = 'user';

    $candidate = $base;
    $i = 1;

    while (true) {
        $sql = 'SELECT id FROM users WHERE username = ?';
        $params = [$candidate];
        if ($excludeId) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        if (!$stmt->fetchColumn()) return $candidate;

        $candidate = $base . '-' . $i;
        $i++;
    }
}

/**
 * Create or update a user by email. Returns the user id.
 * @param PDO $db
 * @param array $user
 * @param int|null $managerId
 * @return int
 */
function createUser(PDO $db, array $user, ?int $managerId = null): int {
    $name = trim($user['name'] ?? '');
    $email = trim($user['email'] ?? '');
    $role = trim($user['role'] ?? 'employee'); // 'manager' | 'employee'
    $pass = (string)($user['password_plain'] ?? '');
    $code = $user['employee_code'] ?? null;
    $username = trim($user['username'] ?? '');

    if ($name === '' || $email === '' || $pass === '') {
        throw new InvalidArgumentException('name, email, and password_plain are required');
    }

    $stmt = $db->prepare('SELECT id, username FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $existingId = $row['id'] ?? null;
    $currentUsername = $row['username'] ?? null;

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    if ($existingId) {
        $usernameToUse = $username !== ''
            ? isUsernameUnique($db, $username, (int)$existingId)
            : ($currentUsername ?: isUsernameUnique($db, explode('@', $email, 2)[0], (int)$existingId));

        $upd = $db->prepare('
            UPDATE users
            SET username=?, name=?, role=?, employee_code=?, password=?, manager_id=?
            WHERE id=?
        ');
        $upd->execute([$usernameToUse, $name, $role, $code, $hash, $managerId, $existingId]);
        echo "Updated user: {$email} (id {$existingId}, username {$usernameToUse})\n";
        return (int)$existingId;
    }

    if ($username === '') {
        $username = isUsernameUnique($db, explode('@', $email, 2)[0]);
    } else {
        $username = isUsernameUnique($db, $username);
    }

    $ins = $db->prepare('
        INSERT INTO users (username, name, email, employee_code, role, password, manager_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $ins->execute([$username, $name, $email, $code, $role, $hash, $managerId]);
    $newId = (int)$db->lastInsertId();
    echo "Created user: {$email} (id {$newId}, username {$username})\n";
    return $newId;
}

// ----------------- Seed data -----------------

$manager = [
    'name'           => 'Joe Doe Manager',
    'username'       => 'manager',
    'email'          => 'manager@email.gr',
    'role'           => 'manager',
    'password_plain' => 'password',
    'employee_code'  => null,
];

$employee = [
    'name'           => 'Bogdan Vaskan',
    'username'       => 'bogdan',
    'email'          => 'bogdan@email.gr',
    'role'           => 'employee',
    'password_plain' => 'password',
    'employee_code'  => '1000001',
];

try {
    $db->beginTransaction();

    $managerId  = createUser($db, $manager, null);
    $employeeId = createUser($db, $employee, $managerId);

    $db->commit();
    echo "Seeding complete.\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, "Error seeding users: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
