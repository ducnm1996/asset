<?php

namespace App\Controllers;

use App\Core\Auth;

class DashboardController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        echo "<!DOCTYPE html><html><head><title>Dashboard</title>";
        echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">";
        echo "<link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css\" rel=\"stylesheet\">";
        echo "</head><body>";
        
        // Navigation
        echo "<nav class=\"navbar navbar-expand-lg navbar-dark bg-dark\">";
        echo "<div class=\"container-fluid\">";
        echo "<a class=\"navbar-brand\" href=\"/dashboard\"><i class=\"fas fa-boxes\"></i> Asset Management</a>";
        echo "<div class=\"navbar-nav ms-auto\">";
        echo "<a class=\"nav-link\" href=\"/logout\"><i class=\"fas fa-sign-out-alt\"></i> Logout</a>";
        echo "</div></div></nav>";
        
        // Main content
        echo "<div class=\"container-fluid\"><div class=\"row\">";
        
        // Sidebar
        echo "<nav class=\"col-md-3 col-lg-2 d-md-block bg-light sidebar\">";
        echo "<div class=\"position-sticky pt-3\">";
        echo "<ul class=\"nav flex-column\">";
        echo "<li class=\"nav-item\"><a class=\"nav-link active\" href=\"/dashboard\"><i class=\"fas fa-tachometer-alt\"></i> Dashboard</a></li>";
        echo "<li class=\"nav-item\"><a class=\"nav-link\" href=\"/assets\"><i class=\"fas fa-boxes\"></i> Assets</a></li>";
        echo "</ul></div></nav>";
        
        // Dashboard content
        echo "<main class=\"col-md-9 ms-sm-auto col-lg-10 px-md-4\">";
        echo "<div class=\"d-flex justify-content-between pt-3 pb-2 mb-3 border-bottom\">";
        echo "<h1 class=\"h2\"><i class=\"fas fa-tachometer-alt\"></i> Dashboard</h1>";
        echo "</div>";
        
        // Stats cards
        echo "<div class=\"row mb-4\">";
        echo "<div class=\"col-xl-3 col-md-6 mb-4\">";
        echo "<div class=\"card border-left-primary shadow h-100 py-2\">";
        echo "<div class=\"card-body\"><div class=\"row no-gutters align-items-center\">";
        echo "<div class=\"col mr-2\"><div class=\"text-xs font-weight-bold text-primary text-uppercase mb-1\">Total Assets</div>";
        echo "<div class=\"h5 mb-0 font-weight-bold text-gray-800\">150</div></div>";
        echo "<div class=\"col-auto\"><i class=\"fas fa-boxes fa-2x text-primary\"></i></div>";
        echo "</div></div></div></div>";
        
        echo "<div class=\"col-xl-3 col-md-6 mb-4\">";
        echo "<div class=\"card border-left-success shadow h-100 py-2\">";
        echo "<div class=\"card-body\"><div class=\"row no-gutters align-items-center\">";
        echo "<div class=\"col mr-2\"><div class=\"text-xs font-weight-bold text-success text-uppercase mb-1\">Available</div>";
        echo "<div class=\"h5 mb-0 font-weight-bold text-gray-800\">45</div></div>";
        echo "<div class=\"col-auto\"><i class=\"fas fa-check-circle fa-2x text-success\"></i></div>";
        echo "</div></div></div></div>";
        
        echo "<div class=\"col-xl-3 col-md-6 mb-4\">";
        echo "<div class=\"card border-left-info shadow h-100 py-2\">";
        echo "<div class=\"card-body\"><div class=\"row no-gutters align-items-center\">";
        echo "<div class=\"col mr-2\"><div class=\"text-xs font-weight-bold text-info text-uppercase mb-1\">Allocated</div>";
        echo "<div class=\"h5 mb-0 font-weight-bold text-gray-800\">85</div></div>";
        echo "<div class=\"col-auto\"><i class=\"fas fa-user-check fa-2x text-info\"></i></div>";
        echo "</div></div></div></div>";
        
        echo "<div class=\"col-xl-3 col-md-6 mb-4\">";
        echo "<div class=\"card border-left-warning shadow h-100 py-2\">";
        echo "<div class=\"card-body\"><div class=\"row no-gutters align-items-center\">";
        echo "<div class=\"col mr-2\"><div class=\"text-xs font-weight-bold text-warning text-uppercase mb-1\">Expiring</div>";
        echo "<div class=\"h5 mb-0 font-weight-bold text-gray-800\">8</div></div>";
        echo "<div class=\"col-auto\"><i class=\"fas fa-exclamation-triangle fa-2x text-warning\"></i></div>";
        echo "</div></div></div></div>";
        echo "</div>";
        
        echo "</main></div></div>";
        echo "<script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>";
        echo "</body></html>";
    }
}
EOF'