<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\VacationRequest;

final class VacationRequestModelTest extends TestCase
{
    public function testBasicSetters(): void
    {
        $req = new VacationRequest([
            'employee_id' => 10,
            'manager_id'  => 2,
            'start_date'  => '2025-09-10',
            'end_date'    => '2025-09-12',
            'reason'      => 'Trip',
        ]);

        $this->assertSame(10, $req->getEmployeeId());
        $this->assertSame(2,  $req->getManagerId());
        $this->assertSame('2025-09-10', $req->getStartDate());
        $this->assertSame('2025-09-12', $req->getEndDate());
        $this->assertSame('Trip', $req->getReason());
        $this->assertSame('pending', $req->getStatus());
    }
}
