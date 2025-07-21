<?php

namespace App\Controllers;

use App\Core\Auth;

class MaintenanceController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        $maintenances = [
            ["id" => 1, "asset" => "Dell Laptop Inspiron 15", "asset_code" => "AST001", "type" => "repair", "description" => "Screen replacement", "date" => "2024-06-10", "cost" => 2500000, "status" => "completed"],
            ["id" => 2, "asset" => "HP LaserJet Pro", "asset_code" => "AST002", "type" => "maintenance", "description" => "Regular cleaning and calibration", "date" => "2024-07-01", "cost" => 500000, "status" => "scheduled"],
            ["id" => 3, "asset" => "Office Chair Executive", "asset_code" => "AST003", "type" => "disposal", "description" => "End of life disposal", "date" => "2024-12-31", "cost" => 0, "status" => "planned"]
        ];
        
        $this->view("maintenance", ["maintenances" => $maintenances]);
    }
}