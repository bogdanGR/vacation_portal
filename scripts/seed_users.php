#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Bootstrap;

Bootstrap::init();
$db = Bootstrap::$db;

/**
 * Find an available username.
 * If $base is taken (excluding $excludeId), append -1, -2, ...
 */
function ensureUniqueUsername(PDO $db, string $base, ?int $excludeId = null): string {
    $base = strtolower(preg_replace('/[^a-z0-9_\\.\\-]/i', '', $base));
    if ($base === '') $base = 'user';

    $candidate = $base;
    $i = 1;

    while (true) {
        if ($excludeId) {
            $stmt = $db->prepare('SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1');
            $stmt->execute([$candidate, $excludeId]);
        } else {
            $stmt = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$candidate]);
        }
        $exists = (bool)$stmt->fetchColumn();
        if (!$exists) return $candidate;
        $candidate = $base . '-' . $i;
        $i++;
    }
}

/**
 * Create or update a user by email. Returns the user id.
 */
function ensureUser(PDO $db, array $user): int {
    // Required fields
    $name = trim($user['name'] ?? '');
    $email = trim($user['email'] ?? '');
    $role = trim($user['role'] ?? 'employee'); // 'manager' | 'employee'
    $pass = (string)($user['password_plain'] ?? '');
    $code = $user['employee_code'] ?? null;  // nullable for managers
    $username = trim($user['username'] ?? '');

    if ($name === '' || $email === '' || $pass === '') {
        throw new InvalidArgumentException('name, email, and password_plain are required');
    }

    // See if user exists by email
    $selectUser = $db->prepare('SELECT id, username FROM users WHERE email = ? LIMIT 1');
    $selectUser->execute([$email]);
    $row = $selectUser->fetch(PDO::FETCH_ASSOC);
    $existingId = $row['id'] ?? null;
    $currentUsername = $row['username'] ?? null;

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    if ($existingId) {
        // If username provided and different, ensure unique; else keep current
        if ($username === '') {
            $usernameToUse = $currentUsername ?: ensureUniqueUsername($db, explode('@', $email, 2)[0], (int)$existingId);
        } else {
            $usernameToUse = ($username === $currentUsername)
                ? $currentUsername
                : ensureUniqueUsername($db, $username, (int)$existingId);
        }

        $upd = $db->prepare('
            UPDATE users
            SET username = ?, name = ?, role = ?, employee_code = ?, password = ?
            WHERE id = ?
        ');
        $upd->execute([$usernameToUse, $name, $role, $code, $hash, $existingId]);
        echo "Updated user: {$email} (id {$existingId}, username {$usernameToUse})\n";
        return (int)$existingId;
    }

    // New user: if username empty, derive from email local-part and ensure unique
    if ($username === '') {
        $base = explode('@', $email, 2)[0];
        $username = ensureUniqueUsername($db, $base, null);
    } else {
        $username = ensureUniqueUsername($db, $username, null);
    }

    $ins = $db->prepare('
        INSERT INTO users (username, name, email, employee_code, role, password)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $ins->execute([$username, $name, $email, $code, $role, $hash]);
    $newId = (int)$db->lastInsertId();
    echo "Created user: {$email} (id {$newId}, username {$username})\n";
    return $newId;
}

/**
 * Ensure the employee ↔ manager link exists in user_managers.
 */
function ensureUserManagerLink(PDO $db, int $employeeId, int $managerId): void {
    $q = $db->prepare('SELECT 1 FROM user_managers WHERE employee_id = ? AND manager_id = ?');
    $q->execute([$employeeId, $managerId]);
    if ($q->fetch()) {
        echo "Link already exists: employee {$employeeId} → manager {$managerId}\n";
        return;
    }
    $ins = $db->prepare('INSERT INTO user_managers (employee_id, manager_id) VALUES (?, ?)');
    $ins->execute([$employeeId, $managerId]);
    echo "Linked employee {$employeeId} → manager {$managerId}\n";
}

$manager = [
    'name'           => 'Joe Doe Manager',
    'username'       => 'manager1',
    'email'          => 'manager@email.gr',
    'role'           => 'manager',
    'password_plain' => 'password123',
    'employee_code'  => null,
];

$employee = [
    'name'           => 'Bogdan Vaskan',
    'username'       => 'bogdan',
    'email'          => 'bogdan@email.gr',
    'role'           => 'employee',
    'password_plain' => 'password123',
    'employee_code'  => '1000002',
];

try {
    $db->beginTransaction();

    $managerId  = ensureUser($db, $manager);
    $employeeId = ensureUser($db, $employee);

    ensureUserManagerLink($db, $employeeId, $managerId);

    $db->commit();
    echo "Seeding complete.\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, "Error seeding users: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
