<?php

namespace App\Controllers;

use App\Core\Auth;

class ContractController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        $contracts = [
            ["id" => 1, "number" => "CT001", "name" => "IT Support Contract", "supplier" => "Tech Solutions Ltd", "start_date" => "2024-01-01", "end_date" => "2024-12-31", "value" => 50000000, "status" => "active"],
            ["id" => 2, "number" => "CT002", "name" => "Office Cleaning", "supplier" => "Clean Pro Service", "start_date" => "2024-06-01", "end_date" => "2025-05-31", "value" => 24000000, "status" => "active"],
            ["id" => 3, "number" => "CT003", "name" => "Security Service", "supplier" => "SecureGuard Inc", "start_date" => "2024-01-15", "end_date" => "2024-07-15", "value" => 36000000, "status" => "expiring"]
        ];
        
        $this->view("contracts", ["contracts" => $contracts]);
    }
}