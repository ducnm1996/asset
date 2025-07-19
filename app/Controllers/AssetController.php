<?php

namespace App\Controllers;

use App\Core\Auth;

class AssetController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        // Mock assets data
        $assets = [
            ['id' => 1, 'asset_code' => 'AST001', 'name' => 'Dell Laptop Inspiron 15', 'category' => 'Computer', 'status' => 'allocated'],
            ['id' => 2, 'asset_code' => 'AST002', 'name' => 'HP LaserJet Pro', 'category' => 'Printer', 'status' => 'available'],
            ['id' => 3, 'asset_code' => 'AST003', 'name' => 'Office Chair Executive', 'category' => 'Furniture', 'status' => 'allocated']
        ];
        
        echo "<!DOCTYPE html><html><head><title>Assets</title>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "</head><body>";
        echo "<div class='container mt-4'>";
        echo "<h1>Assets Management</h1>";
        echo "<a href='/dashboard' class='btn btn-secondary mb-3'>← Back to Dashboard</a>";
        echo "<a href='/assets/create' class='btn btn-primary mb-3'>+ Add New Asset</a>";
        
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>";
        echo "<tbody>";
        foreach ($assets as $asset) {
            echo "<tr>";
            echo "<td>" . $asset['asset_code'] . "</td>";
            echo "<td>" . $asset['name'] . "</td>";
            echo "<td>" . $asset['category'] . "</td>";
            echo "<td><span class='badge bg-" . ($asset['status'] === 'available' ? 'success' : 'primary') . "'>" . ucfirst($asset['status']) . "</span></td>";
            echo "<td><a href='/assets/edit/" . $asset['id'] . "' class='btn btn-sm btn-primary'>Edit</a></td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div></body></html>";
    }
    
    public function create()
    {
        Auth::requireAuth();
        
        echo "<!DOCTYPE html><html><head><title>Add Asset</title>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "</head><body>";
        echo "<div class='container mt-4'>";
        echo "<h1>Add New Asset</h1>";
        echo "<a href='/assets' class='btn btn-secondary mb-3'>← Back to Assets</a>";
        echo "<form method='post'>";
        echo "<div class='mb-3'><label class='form-label'>Asset Code</label><input type='text' class='form-control' name='asset_code' required></div>";
        echo "<div class='mb-3'><label class='form-label'>Name</label><input type='text' class='form-control' name='name' required></div>";
        echo "<div class='mb-3'><label class='form-label'>Category</label><select class='form-control' name='category'><option>Computer</option><option>Printer</option><option>Furniture</option></select></div>";
        echo "<button type='submit' class='btn btn-primary'>Save Asset</button>";
        echo "</form>";
        echo "</div></body></html>";
    }
}