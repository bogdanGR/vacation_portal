<?php
namespace App\Controllers;

use App\Core\BaseController;

class EmployeeController extends BaseController
{
    public function home(): void
    {
        $this->requireLogin();
        $me = $this->user();
        $this->render('employee/index', ['me' => $me, 'requests' => []]);
    }
}
