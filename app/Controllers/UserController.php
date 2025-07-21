<?php

namespace App\Controllers;

use App\Core\Auth;

class UserController extends BaseController
{
    public function index()
    {
        Auth::requireRole("admin");
        
        $users = [
            ["id" => 1, "username" => "admin", "full_name" => "Administrator", "email" => "admin@company.com", "role" => "admin", "status" => "active"],
            ["id" => 2, "username" => "manager", "full_name" => "Manager", "email" => "manager@company.com", "role" => "manager", "status" => "active"],
            ["id" => 3, "username" => "user", "full_name" => "Employee", "email" => "user@company.com", "role" => "employee", "status" => "active"]
        ];
        
        $this->view("users", ["users" => $users]);
    }
}