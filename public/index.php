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


$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
