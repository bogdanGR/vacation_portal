<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\User;
use App\Repositories\UserRepository;

class ManagerController extends BaseController
{
    /**
     *
     * @return void
     */
    public function home(): void
    {
        $this->requireManager();
        $users = UserRepository::all();
        $this->render('manager/index', ['users' => $users]);
    }

    public function usersNew(): void
    {
        $this->requireManager();
        $this->render('manager/users_new');
    }

    public function usersStore(): void
    {
        $this->requireManager();

        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $employee_code = $_POST['employee_code'] ?? null;
        $password = (string)($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'employee';

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required';
        }
        if ($username === '')           {
            $errors['username'] = 'Username is required';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email required';
        }
        if ($role !== 'employee' && $role !== 'manager') {
            $errors['role'] = 'Invalid role';
        }

        if ($role === 'employee') {
            if (!preg_match('/^\d{7}$/', (string)$employee_code)) {
                $errors['employee_code'] = 'Employee code must be exactly 7 digits';
            }
        } else {
            $employee_code = null;
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if (UserRepository::existsByUsername($username)) {
            $errors['username'] = 'Username already taken';
        }
        if (UserRepository::existsByEmail($email))       {
            $errors['email'] = 'Email already in use';
        }
        if ($employee_code && UserRepository::existsByEmployeeCode($employee_code)) {
            $errors['employee_code'] = 'Employee code already in use';
        }

        if ($errors) {
            $this->render('manager/users_new', [
                'errors' => $errors,
                'old' => compact('name','username','email','employee_code','role')
            ]);
            return;
        }

        $currentManagerId = $this->user()['id'];

        $data = $_POST;
        if (($data['role'] ?? 'employee') === 'employee') {
            $data['manager_id'] = $currentManagerId;
        } else {
            $data['manager_id'] = null;
        }

        $user = new User($data);

        if ($user->save()) {
            $this->redirect('/manager');
        } else {
            $this->render('manager/users_new', [
                'errors' => ['general' => 'Failed to save user. Please try again.'],
                'old'    => $_POST
            ]);
        }
    }
}
