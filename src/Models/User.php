<?php
namespace App\Models;

use App\Core\Bootstrap;

/**
 * User entity Model
 *
 * - Holds user data and business rules (e.g., password hashing/verification).
 */
class User
{
    /** @var int|null Primary key (null until inserted) */
    private ?int $id = null;

    /** @var string Unique username for login */
    private string $username = '';

    /** @var string Display name */
    private string $name = '';

    /** @var string Unique email */
    private string $email = '';

    /** @var string|null 7-digit code for employees (null for managers) */
    private ?string $employee_code = null;

    /** @var string 'employee'|'manager' */
    private string $role = 'employee';

    /** @var string Password hash (never store plain text) */
    private string $passwordHash = '';

    /** @var string|null Timestamps (read-only from DB) */
    private ?string $created_at = null;
    private ?string $updated_at = null;

    /**
     * Optional bulk construction.
     * @param array<string,mixed> $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->setUser($data);
        }
    }

    /**
     * Set entity data
     * Accepts:
     * - id, username, name, email, employee_code, role
     * - password_plain (to be hashed) OR password (already hashed)
     * - created_at, updated_at (usually when loading from DB)
     *
     * @param array<string,mixed> $data
     */
    public function setUser(array $data): void
    {
        if (array_key_exists('username', $data)) {
            $this->setUsername((string)$data['username']);
        }
        if (array_key_exists('name', $data)) {
            $this->setName((string)$data['name']);
        }
        if (array_key_exists('email', $data))           {
            $this->setEmail((string)$data['email']);
        }
        if (array_key_exists('employee_code', $data))   {
            $this->setEmployeeCode($data['employee_code']);
        }
        if (array_key_exists('role', $data))            {
            $this->setRole((string)$data['role']);
        }
        if (!empty($data['password'])) {
            $this->setPasswordPlain((string)$data['password']);
        }

        if (array_key_exists('created_at', $data))    {
            $this->setCreatedAt((string)$data['created_at']);
        }
        if (array_key_exists('updated_at', $data))    {
            $this->setUpdatedAt((string)$data['updated_at']);
        }
    }

    /**
     * Hash and set password from plain text.
     * @param string $plain
     * @return void
     */
    public function setPasswordPlain(string $plain): void
    {
        $this->passwordHash = password_hash($plain, PASSWORD_DEFAULT);
    }

    /**
     * Set already-hashed password (for hydration from DB)
     * @param string $hash
     * @return void
     */
    public function setPasswordHashed(string $hash): void
    {
        $this->passwordHash = $hash;
    }

    /**
     * Hash & set a new password.
     * @param string $plain
     * @return void
     */
    public function setPassword(string $plain): void
    {
        $this->passwordHash = password_hash($plain, PASSWORD_DEFAULT);
    }

    /**
     * Verify a plaintext password against the stored hash
     * @param string $plain
     * @return bool
     */
    public function verifyPassword(string $plain): bool
    {
        return $this->passwordHash !== '' && password_verify($plain, $this->passwordHash);
    }

    /**
     * Check if user is manager
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Store user data in session
     * @return array
     */
    public function toSession(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'employee_code' => $this->employee_code,
            'role' => $this->role,
        ];
    }

    /**
     * save User data
     * @return int The user id after persistence.
     */
    public function save(): int
    {
        $db = Bootstrap::$db;

        if ($this->id) {
            // update
            $stmt = $db->prepare("
                UPDATE users SET username=?, name=?, email=?, employee_code=?, role=?, password=?, updated_at=NOW()
                WHERE id=?
            ");
            return $stmt->execute([
                $this->username,
                $this->name,
                $this->email,
                $this->employee_code,
                $this->role,
                $this->passwordHash,
                $this->id
            ]);
        } else {
            // insert
            $stmt = $db->prepare("
                INSERT INTO users (username,name,email,employee_code,role,password,created_at,updated_at)
                VALUES (?,?,?,?,?,?,NOW(),NOW())
            ");
            $ok = $stmt->execute([
                $this->username,
                $this->name,
                $this->email,
                $this->employee_code,
                $this->role,
                $this->passwordHash
            ]);
            if ($ok) {
                $this->id = (int)$db->lastInsertId();
            }
            return $ok;
        }
    }

    /**
     * Delete this user via the repository.
     * @return bool True if deleted.
     */
    public function delete(): bool
    {
        if (!$this->id) return false;
        $stmt = Bootstrap::$db->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Internal accessor for repository only.
     * (Repository will read the hash to write it to DB.)
     */
    public function _getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * Internal setter for repository when hydrating from DB.
     */
    public function _setPasswordHash(string $hash): void
    {
        $this->passwordHash = $hash;
    }
    // -------------------- GETTERS --------------------

    /** @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return string */
    public function getUsername(): string
    {
        return $this->username;
    }

    /** @return string */
    public function getName(): string
    {
        return $this->name;
    }

    /** @return string */
    public function getEmail(): string
    {
        return $this->email;
    }

    /** @return string|null */
    public function getEmployeeCode(): ?string {
        return $this->employee_code;
    }

    /** @return string */
    public function getRole(): string {
        return $this->role;
    }

    /**
     * Return formatted created_at
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return date_format(date_create($this->created_at),"d/m/Y");
    }

    /**
     * Return formatted updated_at
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return date_format(date_create($this->updated_at),"d/m/Y");
    }

    // -------------------- SETTERS --------------------

    /** @param int|null $id */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /** @param string $username */
    public function setUsername(string $username): void
    {
        $this->username = trim($username);
    }

    /** @param string $name */
    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    /** @param string $email */
    public function setEmail(string $email): void
    {
        $this->email = strtolower(trim($email));
    }

    /** @param string|null $code 7-digit for employees; null for managers */
    public function setEmployeeCode(?string $code): void
    {
        $this->employee_code = ($code === null || $code === '') ? null : $code;
    }

    /**
     * @param 'employee'|'manager' $role
     */
    public function setRole(string $role): void
    {
        $role = strtolower(trim($role));
        if ($role !== 'employee' && $role !== 'manager') {
            throw new \InvalidArgumentException('Role must be employee or manager');
        }
        $this->role = $role;
    }

    /**
     * Set the created_at timestamp (normally set from DB only).
     *
     * @param string|null $createdAt
     * @return void
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->created_at = $createdAt;
    }

    /**
     * Set the updated_at timestamp (normally set from DB only).
     *
     * @param string|null $updatedAt
     * @return void
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updated_at = $updatedAt;
    }
}
