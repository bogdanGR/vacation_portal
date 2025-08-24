<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Bootstrap;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\UsersController;
use App\Controllers\VacationRequestsController;

Bootstrap::init();

$router = new Router();

/** Auth */
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

/** Dashboards */
$router->get('/manager', [UsersController::class, 'managerIndex']);
$router->get('/employee', [UsersController::class, 'employeeIndex']);

/** Manager: Registration (create user) */
$router->get('/manager/users/new',  [UsersController::class, 'create']);
$router->post('/manager/users',     [UsersController::class, 'store']);

/** Manager edit, update and delete user */
$router->get('/manager/users/{id}/edit', [UsersController::class, 'edit']);
$router->post('/manager/users/{id}/edit', [UsersController::class, 'edit']);
$router->post('/manager/users/{id}/delete', [UsersController::class, 'delete']);

/** Requests new/store/delete and edit*/
$router->get('/employee/requests/new', [VacationRequestsController::class, 'create']);
$router->post('/employee/requests/store', [VacationRequestsController::class, 'store']);
$router->post('/employee/requests/{id}/delete', [VacationRequestsController::class, 'delete']);
$router->get('/employee/requests/{id}/edit',  [VacationRequestsController::class, 'edit']);
$router->post('/employee/requests/{id}/edit', [VacationRequestsController::class, 'edit']);

/** managers request index, approve/reject requests */
$router->get('/manager/requests', [VacationRequestsController::class, 'index']);
$router->post('/manager/requests/{id}/approve', [VacationRequestsController::class, 'approve']);
$router->post('/manager/requests/{id}/reject', [VacationRequestsController::class, 'reject']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
