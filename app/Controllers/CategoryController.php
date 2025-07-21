<?php

namespace App\Controllers;

use App\Core\Auth;

class CategoryController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        $categories = [
            ["id" => 1, "name" => "Computer", "description" => "Desktop and laptop computers", "assets" => 45],
            ["id" => 2, "name" => "Printer", "description" => "Printing devices", "assets" => 12],
            ["id" => 3, "name" => "Furniture", "description" => "Office furniture", "assets" => 67],
            ["id" => 4, "name" => "Network Equipment", "description" => "Routers, switches, etc.", "assets" => 26]
        ];
        
        $this->view("categories", ["categories" => $categories]);
    }
}