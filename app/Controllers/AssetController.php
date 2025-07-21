<?php

namespace App\Controllers;

use App\Core\Auth;

class AssetController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        echo "<!DOCTYPE html><html><head><title>Assets</title>";
        echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">";
        echo "</head><body>";
        echo "<nav class=\"navbar navbar-dark bg-dark\"><div class=\"container\"><a class=\"navbar-brand\" href=\"/dashboard\">Asset Management</a>";
        echo "<a class=\"nav-link text-white\" href=\"/logout\">Logout</a></div></nav>";
        echo "<div class=\"container mt-4\">";
        echo "<h1>Assets Management</h1>";
        echo "<a href=\"/dashboard\" class=\"btn btn-secondary mb-3\">← Back to Dashboard</a>";
        echo "<table class=\"table table-striped\">";
        echo "<thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Status</th></tr></thead>";
        echo "<tbody>";
        echo "<tr><td>AST001</td><td>Dell Laptop Inspiron 15</td><td>Computer</td><td><span class=\"badge bg-primary\">Allocated</span></td></tr>";
        echo "<tr><td>AST002</td><td>HP LaserJet Pro</td><td>Printer</td><td><span class=\"badge bg-success\">Available</span></td></tr>";
        echo "<tr><td>AST003</td><td>Office Chair Executive</td><td>Furniture</td><td><span class=\"badge bg-primary\">Allocated</span></td></tr>";
        echo "</tbody></table>";
        echo "</div></body></html>";
    }
}
EOF