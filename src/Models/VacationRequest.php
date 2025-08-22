<?php
namespace App\Models;

use App\Core\Bootstrap;
use PDO;

class VacationRequest
{
    private ?int $id = null;
    private int $employee_id;
    private int $manager_id;
    private string $start_date;
    private string $end_date;
    private ?string $reason = null;
    private string $status = 'pending'; // default
    private ?string $submitted_at = null;
    private ?string $processed_at = null;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        if ($data) {
            $this->setVacationRequest($data);
        }
    }

    /**
     * Bulk set VacationRequest data via setters.
     *
     * @param array<string,mixed> $data
     */
    public function setVacationRequest(array $data): void
    {
        if (isset($data['id'])) {
            $this->setId((int)$data['id']);
        }
        if (isset($data['employee_id'])) {
            $this->setEmployeeId((int)$data['employee_id']);
        }
        if (isset($data['manager_id'])) {
            $this->setManagerId((int)$data['manager_id']);
        }
        if (isset($data['start_date'])) {
            $this->setStartDate($data['start_date']);
        }
        if (isset($data['end_date'])) {
            $this->setEndDate($data['end_date']);
        }
        if (isset($data['reason'])) {
            $this->setReason($data['reason']);
        }
        if (isset($data['status'])) {
            $this->setStatus($data['status']);
        }
        if (isset($data['submitted_at'])) {
            $this->setSubmittedAt($data['submitted_at']);
        }
        if (isset($data['processed_at'])) {
            $this->setProcessedAt($data['processed_at']);
        }
    }

    /**
     * Save vacation request entity
     * @return bool
     */
    public function save(): bool
    {
        $db = Bootstrap::$db;

        if ($this->id) {
            $stmt = $db->prepare("
                UPDATE vacation_requests
                SET start_date=?, end_date=?, reason=?, status=?, processed_at=?
                WHERE id=? AND employee_id=?
            ");
            return $stmt->execute([
                $this->start_date,
                $this->end_date,
                $this->reason,
                $this->status,
                $this->processed_at,
                $this->id,
                $this->employee_id,
            ]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO vacation_requests
                (employee_id, manager_id, start_date, end_date, reason, status, submitted_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $ok = $stmt->execute([
                $this->employee_id,
                $this->manager_id,
                $this->start_date,
                $this->end_date,
                $this->reason,
                $this->status,
            ]);
            if ($ok) {
                $this->id = (int)$db->lastInsertId();
            }
            return $ok;
        }
    }

    /**
     * Delete vacation request entity if status is pending.
     * @param int $byEmployeeId
     * @return bool
     */
    public function deleteIfPending(int $byEmployeeId): bool
    {
        if ($this->id === null) return false;

        $stmt = Bootstrap::$db->prepare(
            "DELETE FROM vacation_requests WHERE id = ? AND employee_id = ? AND status = 'pending'"
        );
        $stmt->execute([$this->id, $byEmployeeId]);

        return $stmt->rowCount() === 1;
    }

    /**
     * Method to mark as approved the vacation request
     * @return bool
     */
    public function approve(): bool
    {
        if ($this->status !== 'pending' || $this->id === null) return false;

        $stmt = Bootstrap::$db->prepare("
        UPDATE vacation_requests
        SET status='approved', processed_at = NOW()
        WHERE id=? AND status='pending'
    ");
        $ok = $stmt->execute([$this->id]);
        if ($ok && $stmt->rowCount() === 1) {
            $this->status = 'approved';
            $this->processed_at = date('Y-m-d H:i:s');
            return true;
        }
        return false;
    }

    /**
     * Method to mark as rejected the vacation request
     * @return bool
     */
    public function reject(): bool
    {
        if ($this->status !== 'pending' || $this->id === null) return false;

        $stmt = Bootstrap::$db->prepare("
        UPDATE vacation_requests
        SET status='rejected', processed_at = NOW()
        WHERE id=? AND status='pending'
    ");
        $ok = $stmt->execute([$this->id]);
        if ($ok && $stmt->rowCount() === 1) {
            $this->status = 'rejected';
            $this->processed_at = date('Y-m-d H:i:s');
            return true;
        }
        return false;
    }

    // getters
    public function getId(): ?int {
        return $this->id;
    }
    public function getEmployeeId(): int {
        return $this->employee_id;
    }
    public function getManagerId(): int {
        return $this->manager_id;
    }
    public function getStartDate(): string {
        return date_format(date_create($this->start_date),"d/m/Y");
    }
    public function getEndDate(): string {
        return date_format(date_create($this->end_date),"d/m/Y");
    }
    public function getReason(): ?string {
        return $this->reason;
    }
    public function getStatus(): string {
        return $this->status;
    }
    public function getSubmittedAt(): ?string {
        return date_format(date_create($this->submitted_at),"d/m/Y");
    }
    public function getProcessedAt(): ?string {
        return date_format(date_create($this->processed_at),"d/m/Y");
    }

    // setters
    public function setId(?int $id): void {
        $this->id = $id;
    }
    public function setEmployeeId(int $employeeId): void {
        $this->employee_id = $employeeId;
    }
    public function setManagerId(int $managerId): void {
        $this->manager_id = $managerId;
    }
    public function setStartDate(string $date): void {
        $this->start_date = $date;
    }
    public function setEndDate(string $date): void {
        $this->end_date = $date;
    }
    public function setReason(?string $reason): void {
        $this->reason = $reason;
    }
    public function setStatus(string $status): void {
        $this->status = $status;
    }
    public function setSubmittedAt(?string $dt): void {
        $this->submitted_at = $dt;
    }
    public function setProcessedAt(?string $dt): void {
        $this->processed_at = $dt;
    }
}
