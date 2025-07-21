<?php

namespace App\Controllers;

use App\Core\Auth;

class EmployeeController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        $employees = [
            ["id" => 1, "code" => "EMP001", "name" => "Nguyen Van A", "department" => "IT", "email" => "nva@company.com", "phone" => "0123456789"],
            ["id" => 2, "code" => "EMP002", "name" => "Tran Thi B", "department" => "HR", "email" => "ttb@company.com", "phone" => "0987654321"],
            ["id" => 3, "code" => "EMP003", "name" => "Le Van C", "department" => "Finance", "email" => "lvc@company.com", "phone" => "0111222333"]
        ];
        
        $this->view("employees", ["employees" => $employees]);
    }
    
    public function create()
    {
        Auth::requireRole("manager");
        
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Process form
            $this->setFlash("success", "Employee created successfully");
            $this->redirect("/employees");
        }
        
        $this->view("employee-form", ["title" => "Add Employee", "employee" => null]);
    }
}