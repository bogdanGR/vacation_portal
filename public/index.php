<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Bootstrap;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\ManagerController;
use App\Controllers\EmployeeController;

Bootstrap::init();

$router = new Router();

/** Auth */
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

/** Dashboards */
$router->get('/manager', [ManagerController::class, 'home']);
$router->get('/employee', [EmployeeController::class, 'home']);

/** Manager: Registration (create user) */
$router->get('/manager/users/new',  [ManagerController::class, 'usersNew']);
$router->post('/manager/users',     [ManagerController::class, 'usersStore']);

/** Manager edit, update and delete user */
$router->get('/manager/users/{id}/edit', [ManagerController::class, 'usersEdit']);
$router->post('/manager/users/{id}/edit', [ManagerController::class, 'usersEdit']);
$router->post('/manager/users/{id}/delete', [ManagerController::class, 'usersDelete']);

/** Requests new/store/delete and edit*/
$router->get('/employee/requests/new', [EmployeeController::class, 'createRequest']);
$router->post('/employee/requests/store', [EmployeeController::class, 'storeRequest']);
$router->post('/employee/requests/{id}/delete', [EmployeeController::class, 'deleteRequest']);
$router->get('/employee/requests/{id}/edit',  [App\Controllers\EmployeeController::class, 'editRequest']);
$router->post('/employee/requests/{id}/edit', [App\Controllers\EmployeeController::class, 'editRequest']);

/** managers request index, approve/reject requests */
$router->get('/manager/requests', [ManagerController::class, 'requestsIndex']);
$router->post('/manager/requests/{id}/approve', [ManagerController::class, 'requestsApprove']);
$router->post('/manager/requests/{id}/reject', [ManagerController::class, 'requestsReject']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
