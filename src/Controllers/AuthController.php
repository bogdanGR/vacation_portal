<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Repositories\UserRepository;

class AuthController extends BaseController
{
    /**
     * login route, redirect user to manager page or employee page if it's employee account
     * @return void
     */
    public function showLogin(): void
    {
        if ($this->user()) {
            $this->redirect(($this->user()['role'] ?? '') === 'manager' ? '/manager' : '/employee');
        }

        $this->render('auth/login');
    }

    /**
     * Handle login process
     * @return void
     */
    public function login(): void
    {
        $this->verifyCsrf();
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $this->render('auth/login', ['error' => 'Username and password are required']);
            return;
        }

        $user = UserRepository::findByUsername($username);

        // Check user and password
        if (!$user || !$user->verifyPassword($password)) {
            $this->render('auth/login', ['error' => 'Invalid credentials']);
            return;
        }

        // Store session data
        $_SESSION['user'] = $user->toSession();

        $this->redirect($user->getRole() === 'manager' ? '/manager' : '/employee');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}
