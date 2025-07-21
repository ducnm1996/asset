<?php

namespace App\Controllers;

use App\Core\Auth;

class AllocationController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        $allocations = [
            ["id" => 1, "asset" => "Dell Laptop Inspiron 15", "asset_code" => "AST001", "employee" => "Nguyen Van A", "department" => "IT", "allocated_date" => "2024-01-15", "status" => "allocated"],
            ["id" => 2, "asset" => "Office Chair Executive", "asset_code" => "AST003", "employee" => "Tran Thi B", "department" => "HR", "allocated_date" => "2024-03-05", "status" => "allocated"],
            ["id" => 3, "asset" => "HP Monitor 24\"", "asset_code" => "AST004", "employee" => "Le Van C", "department" => "Finance", "allocated_date" => "2024-02-10", "status" => "returned", "returned_date" => "2024-06-15"]
        ];
        
        $this->view("allocations", ["allocations" => $allocations]);
    }
    
    public function create()
    {
        Auth::requireRole("manager");
        $this->view("allocation-form", ["title" => "Allocate Asset"]);
    }
}