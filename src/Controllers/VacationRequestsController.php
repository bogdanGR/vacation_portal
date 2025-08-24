<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\VacationRequest;
use App\Repositories\VacationRequestRepository;

class VacationRequestsController extends BaseController
{
    /**
     * Handles the requests index page
     * @return void
     */
    public function index(): void
    {
        $this->requireManager();
        $status = $_GET['status'] ?? 'pending';
        $managerId = (int)$this->user()['id'];

        $requests = VacationRequestRepository::findByManager($managerId, $status);

        $this->render('vacation_requests/index', [
            'requests' => $requests,
            'status'   => $status,
        ]);
    }

    /**
     * Show form for creating a new vacation request.
     */
    public function create(): void
    {
        $this->render('vacation_requests/create');
    }

    /**
     * Store new vacation request in DB.
     */
    public function store(): void
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
            $this->render('vacation_requests/create', [
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
            $this->render('vacation_requests/create', [
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
    public function edit(array $params): void
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
            $this->render('vacation_requests/edit', ['request' => $vacationRequest, 'errors' => [], 'old' => []]);
            return;
        }

        $this->verifyCsrf();
        $start = (string)($_POST['start_date'] ?? '');
        $end = (string)($_POST['end_date'] ?? '');
        $reason = trim((string)($_POST['reason'] ?? ''));

        $errors = VacationRequestRepository::validate($employeeId, $start, $end, $reason, $vacationRequest->getId());

        if ($errors) {
            $this->render('vacation_requests/edit', [
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
            $this->render('vacation_requests/edit', [
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
    public function delete(array $params): void
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

        $this->render('users/employee_index', [
            'error'    => 'Unable to delete request.',
            'requests' => VacationRequestRepository::findByEmployee($employeeId),
        ]);
    }

    /**
     * Handles the approval of the vacation request
     * @param array $params
     * @return void
     */
    public function approve(array $params): void
    {
        $this->processRequestStatusChange($params, 'approve');
    }

    /**
     * Handles the rejection of the vacation request
     * @param array $params
     * @return void
     */
    public function reject(array $params): void
    {
        $this->processRequestStatusChange($params, 'reject');
    }

    /**
     * Common logic for approving/rejecting vacation requests
     * @param array $params
     * @param string $action 'approve'|'reject'
     * @return void
     */
    private function processRequestStatusChange(array $params, string $action): void
    {
        $this->verifyCsrf();
        $this->requireManager();
        $managerId = (int)$this->user()['id'];
        $id = (int)$params['id'];

        $request = VacationRequestRepository::findOneForManager($id, $managerId);

        if (!$request) {
            http_response_code(404);
            echo "Request not found";
            return;
        }

        $success = $action === 'approve' ? $request->approve() : $request->reject();

        if (!$success) {
            $this->render('vacation_requests/index', [
                'requests' => VacationRequestRepository::findByManager($managerId),
                'error' => 'Unable to update Vacation request'
            ]);
            return;
        }

        $this->redirect('/manager/requests');
    }
}