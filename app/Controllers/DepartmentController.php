<?php

namespace App\Controllers;

use App\Core\Auth;

class DepartmentController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        $departments = [
            ["id" => 1, "name" => "IT Department", "description" => "Information Technology", "employees" => 15],
            ["id" => 2, "name" => "HR Department", "description" => "Human Resources", "employees" => 8],
            ["id" => 3, "name" => "Finance Department", "description" => "Finance and Accounting", "employees" => 12],
            ["id" => 4, "name" => "Marketing Department", "description" => "Marketing and Sales", "employees" => 10]
        ];
        
        $this->view("departments", ["departments" => $departments]);
    }
}