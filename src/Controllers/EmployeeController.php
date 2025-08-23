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
        $status = $_GET['status'] ?? 'pending';

        $requests = [];

        if (!empty($user)) {
            $requests = VacationRequestRepository::findByEmployee($user['id'], $status);
        }

        $this->render('employee/index', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    /**
     * Show form for creating a new vacation request.
     */
    public function createRequest(): void
    {
        $this->render('employee/vacation_request/new');
    }

    /**
     * Store new vacation request in DB.
     */
    public function storeRequest(): void
    {
        $this->verifyCsrf();
        $employee = $this->user();
        $employeeId = $employee['id'];
        $managerId  = $employee['manager_id'];

        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        $errors = VacationRequestRepository::validate($employeeId, $start, $end, $reason);

        if ($errors) {
            $this->render('employee/vacation_request/new', [
                'errors' => $errors,
                'old' => [
                    'start_date' => $start,
                    'end_date'   => $end,
                    'reason'     => $reason,
                ],
            ]);
            return;
        }

        $request = new VacationRequest([
            'employee_id' => $employeeId,
            'manager_id' => $managerId,
            'start_date' => $start,
            'end_date' => $end,
            'reason' => $reason,
        ]);

        if ($request->save()) {
            $this->redirect('/employee');
        } else {
            $this->render('employee/vacation_request/new', [
                'errors' => ['general' => 'Something went wrong'],
                'old' => compact('start','end','reason')
            ]);
        }
    }

    /**
     * Handles show edit form and store vacation request
     * @param array $params
     * @return void
     */
    public function editRequest(array $params): void
    {
        $employeeId = (int)$this->user()['id'];
        $id = (int)$params['id'];

        $vacationRequest = VacationRequestRepository::findOneOwned($id, $employeeId);

        if (!$vacationRequest) {
            http_response_code(404);
            echo "Request not found";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('employee/vacation_request/edit', ['request' => $vacationRequest, 'errors' => [], 'old' => []]);
            return;
        }

        $this->verifyCsrf();
        $start = (string)($_POST['start_date'] ?? '');
        $end = (string)($_POST['end_date'] ?? '');
        $reason = trim((string)($_POST['reason'] ?? ''));

        $errors = VacationRequestRepository::validate($employeeId, $start, $end, $reason, $vacationRequest->getId());

        if ($errors) {
            $this->render('employee/vacation_request/edit', [
                'request' => $vacationRequest,
                'errors' => $errors,
                'old' => [
                    'start' => $start,
                    'end' => $end,
                    'reason' => $reason
                ],
                ]);
            return;
        }

        $vacationRequest->setStartDate($start);
        $vacationRequest->setEndDate($end);
        $vacationRequest->setReason($reason);

        if ($vacationRequest->save()) {
            $this->redirect('/employee');
        } else {
            $this->render('employee/vacation_request/edit', [
                'request' => $vacationRequest,
                'errors' => ['general' => 'Unable to update request.'],
                'old' => [
                    'start_date' => $start,
                    'end_date' => $end,
                    'reason' => $reason,
                ],
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
