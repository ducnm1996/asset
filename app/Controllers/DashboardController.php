<?php

namespace App\Controllers;

use App\Core\Auth;

class DashboardController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        // Mock statistics data
        $stats = [
            'total_assets' => 150,
            'available_assets' => 45,
            'allocated_assets' => 85,
            'disposed_assets' => 20,
            'expiring_assets' => 8,
            'expiring_contracts' => 3
        ];
        
        $recentAssets = [
            ['id' => 1, 'name' => 'Dell Laptop Inspiron 15', 'status' => 'allocated', 'employee' => 'Nguyen Van A'],
            ['id' => 2, 'name' => 'HP LaserJet Pro', 'status' => 'available', 'employee' => null],
            ['id' => 3, 'name' => 'Office Chair Executive', 'status' => 'allocated', 'employee' => 'Tran Thi B']
        ];
        
        $this->view('dashboard', [
            'stats' => $stats,
            'recentAssets' => $recentAssets
        ]);
    }
}