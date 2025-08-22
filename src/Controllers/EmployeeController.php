<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\VacationRequest;
use App\Repositories\VacationRequestRepository;

class EmployeeController extends BaseController
{
    public function home(): void
    {
        $this->requireLogin();
        $user = $this->user();

        $requests = [];

        if (!empty($user)) {
            $requests = VacationRequestRepository::findByEmployee($user['id']);
        }

        $this->render('employee/index', ['requests' => $requests]);
    }

    /**
     * Show form for creating a new vacation request.
     */
    public function createRequest(): void
    {
        $this->render('employee/request_new');
    }

    /**
     * Store new vacation request in DB.
     */
    public function storeRequest(): void
    {
        $employee = $this->user();
        $employeeId = $employee['id'];
        $managerId  = $employee['manager_id'];

        $start = $_POST['start_date'] ?? '';
        $end   = $_POST['end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        $errors = [];
        if (!$start || !$end) {
            $errors['dates'] = 'Start and end dates are required';
        } elseif ($end < $start) {
            $errors['dates'] = 'End date cannot be earlier than start date';
        }

        if ($errors) {
            $this->render('employee/request_new', [
                'errors' => $errors,
                'old' => compact('start','end','reason')
            ]);
            return;
        }

        $request = new VacationRequest([
            'employee_id' => $employeeId,
            'manager_id'  => $managerId,
            'start_date'  => $start,
            'end_date'    => $end,
            'reason'      => $reason,
        ]);

        if ($request->save()) {
            $this->redirect('/employee');
        } else {
            $this->render('employee/request_new', [
                'errors' => ['general' => 'Something went wrong'],
                'old' => compact('start','end','reason')
            ]);
        }
    }

    /**
     * Delete a vacation request if it's pending.
     * @param array{id:int} $params
     */
    public function deleteRequest(array $params): void
    {
        $employeeId = (int)$this->user()['id'];
        $requestId  = (int)$params['id'];

        $request = VacationRequestRepository::findOneOwned($requestId, $employeeId);

        if (!$request) {
            http_response_code(404);
            echo "Request not found";
            return;
        }

        if ($request->deleteIfPending($employeeId)) {
            $this->redirect('/employee');
            return;
        }

        $this->render('employee/vacations_index', [
            'error'    => 'Unable to delete request.',
            'requests' => VacationRequestRepository::findByEmployee($employeeId),
        ]);
    }
}
