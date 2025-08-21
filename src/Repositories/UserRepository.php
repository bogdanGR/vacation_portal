<?php
namespace App\Repositories;

use App\Core\Bootstrap;
use App\Models\User;
use PDO;

/**
 * UserRepository: all SQL for the User entity.
 */
class UserRepository
{
    /**
     * Map a User from a DB row
     * @param array $row
     * @return User
     */
    private static function mapRow(array $row): User
    {
        $user = new User([
            'id' => (int)$row['id'],
            'username' => $row['username'],
            'name' => $row['name'],
            'email'  => $row['email'],
            'employee_code' => $row['employee_code'],
            'role' => $row['role'],
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'manager_id' => $row['manager_id'] ?? null,
        ]);

        $user->_setPasswordHash((string)$row['password']);
        return $user;
    }

    /**
     * Find a user by id
     * @param int $id
     * @return User|null
     */
    public static function findById(int $id): ?User
    {
        $s = Bootstrap::$db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $s->execute([$id]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        return $row ? self::mapRow($row) : null;
    }

    /**
     * Find a user by username
     * @param string $username
     * @return User|null
     */
    public static function findByUsername(string $username): ?User
    {
        $s = Bootstrap::$db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $s->execute([$username]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        return $row ? self::mapRow($row) : null;
    }

    /**
     * Returns all users
     * @return array
     */
    public static function all(): array
    {
        $rows = Bootstrap::$db
            ->query("SELECT * FROM users ORDER BY id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);

        return array_map([self::class, 'mapRow'], $rows);
    }

    /**
     * Returns all employees
     * @return array
     */
    public static function allEmployees(): array
    {
        $rows = Bootstrap::$db
            ->query("SELECT * FROM users WHERE role = 'employee' ORDER BY id DESC")
            ->fetchAll(\PDO::FETCH_ASSOC);

        return array_map([self::class, 'mapRow'], $rows);
    }


    /**
     * Check if user exists by username
     * To find if username is unique
     * @param string $username
     * @param int|null $excludeId
     * @return bool
     */
    public static function existsByUsername(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM users WHERE username = ?".($excludeId ? " AND id <> ?" : "")." LIMIT 1";
        $s = Bootstrap::$db->prepare($sql);
        $s->execute($excludeId ? [$username, $excludeId] : [$username]);
        return (bool)$s->fetchColumn();
    }

    /**
     *  Check if user exists by email
     *  To find if email is unique
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public static function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM users WHERE email = ?".($excludeId ? " AND id <> ?" : "")." LIMIT 1";
        $s = Bootstrap::$db->prepare($sql);
        $s->execute($excludeId ? [$email, $excludeId] : [$email]);
        return (bool)$s->fetchColumn();
    }

    /**
     *  Check if user exists by employee code
     *  To find if employee code is unique
     * @param string|null $code
     * @param int|null $excludeId
     * @return bool
     */
    public static function existsByEmployeeCode(?string $code, ?int $excludeId = null): bool
    {
        if ($code === null) return false;
        $sql = "SELECT 1 FROM users WHERE employee_code = ?".($excludeId ? " AND id <> ?" : "")." LIMIT 1";
        $s = Bootstrap::$db->prepare($sql);
        $s->execute($excludeId ? [$code, $excludeId] : [$code]);
        return (bool)$s->fetchColumn();
    }
}
