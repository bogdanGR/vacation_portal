<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\VacationRequestRepository;

class UsersController extends BaseController
{
    public function employeeIndex(): void
    {
        $this->requireLogin();
        $user = $this->user();
        $status = $_GET['status'] ?? 'pending';

        $requests = [];

        if (!empty($user)) {
            $requests = VacationRequestRepository::findByEmployee($user['id'], $status);
        }

        $this->render('users/employee_index', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    /**
     *
     * index end point of Manager page
     * @return void
     */
    public function managerIndex(): void
    {
        $this->requireManager();
        $managerId = (int)$this->user()['id'];

        $users = UserRepository::allEmployees();

        $this->render('users/manager_index', [
            'users' => $users,
            'pendingCount' => VacationRequestRepository::countPendingByManager($managerId)
        ]);
    }

    /**
     * Render form for new user
     * @return void
     */
    public function create(): void
    {
        $this->requireManager();
        $this->render('users/create', [
            'prefill_employee_code' => UserRepository::generateNextEmployeeCode()
        ]);
    }

    /**
     * Handles store of user
     * @return void
     */
    public function store(): void
    {
        $this->verifyCsrf();
        $this->requireManager();

        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $employee_code = $_POST['employee_code'] ?? null;

        $errors = UserRepository::validateUserData($_POST);;

        if ($errors) {
            $this->render('manager/users_new', [
                'errors' => $errors,
                'old' => [
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'employee_code' => $employee_code
                ]
            ]);
            return;
        }

        $currentManagerId = $this->user()['id'];

        $data = $_POST;
        if (!empty($data['role']) && $data['role'] === 'employee') {
            $data['manager_id'] = $currentManagerId;
        } else {
            $data['manager_id'] = null;
            $data['employee_code'] = null;
        }

        $user = new User($data);

        if ($user->save()) {
            $this->redirect('/manager');
        } else {
            $this->render('users/create', [
                'errors' => ['general' => 'Failed to save user. Please try again.'],
                'old' => [
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'employee_code' => $employee_code
                ]
            ]);
        }
    }

    /**
     * Show the user edit form (GET) or handle user update (POST).
     *
     * Routes:
     *  - GET  /manager/users/{id}/edit  → Show edit form
     *  - POST /manager/users/{id}/edit  → Update user data
     *
     * Behavior:
     *  - Ensures the current user is a manager.
     *  - Loads the target user by ID.
     *  - On GET: renders the edit form with current user data.
     *  - On POST: validates inputs (name, email, optional password),
     *    updates the user record, and redirects to manager dashboard
     *    if successful, otherwise redisplays the form with errors.
     *
     * @param array{id:int|string} $params Named route parameters (expects 'id').
     *
     * @return void
     */
    public function edit(array $params): void
    {
        $this->requireManager();

        $id = (int)$params['id'];

        $user = UserRepository::findById($id);

        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $name = trim($_POST['name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $errors = UserRepository::validateUserData($_POST, $id, true);

            if ($errors) {
                $this->render('users/edit', [
                    'user' => $user,
                    'errors' => $errors,
                    'old' => [
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                    ]
                ]);
                return;
            }

            $user->setName($name);
            $user->setUsername($username);
            $user->setEmail($email);
            if ($password !== '') {
                $user->setPasswordPlain($password);
            }

            if ($user->save()) {
                $this->redirect('/manager');
            } else {
                $this->render('manager/users_edit', [
                    'user' => $user,
                    'errors' => ['general' => 'Failed to update user. Please try again.'],
                    'old' => [
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                    ]
                ]);
            }
        } else {
            $this->render('users/edit', [
                'user' => $user,
                'errors' => [],
                'old' => []
            ]);
        }
    }

    /**
     * Delete a user by ID.
     *
     * Route:
     *  - POST /manager/users/{id}/delete
     *
     * Behavior:
     *  - Ensures the current user is a manager.
     *  - Loads the target user by ID.
     *  - If the user exists, deletes it from the database.
     *  - Redirects back to the manager dashboard with success or error.
     *
     * @param array{id:int|string} $params Named route parameters (expects 'id').
     *
     * @return void
     */
    public function delete(array $params): void
    {
        $this->verifyCsrf();
        $this->requireManager();

        $userId = (int)$params['id'];

        $user = UserRepository::findById($userId);

        if (!$user) {
            $this->render('users/manager_index', [
                'errors' => ['general' => 'User not found'],
                'users'  => UserRepository::allEmployees()
            ]);
            return;
        }

        if ($user->delete()) {
            $this->redirect('/manager');
        } else {
            $this->render('users/manager_index', [
                'errors' => ['general' => 'Failed to delete user'],
                'users'  => UserRepository::allEmployees()
            ]);
        }
    }
}
