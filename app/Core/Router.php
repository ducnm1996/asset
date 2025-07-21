<?php

if (!file_exists(__DIR__ . "/../vendor/autoload.php")) {
    die("Composer autoload not found. Please run: composer install");
}

require_once __DIR__ . "/../vendor/autoload.php";

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();

// Auth routes
$router->add("", "DashboardController", "index");
$router->add("dashboard", "DashboardController", "index");
$router->add("login", "AuthController", "login", "GET");
$router->add("login", "AuthController", "login", "POST");
$router->add("logout", "AuthController", "logout");

// Asset routes
$router->add("assets", "AssetController", "index");
$router->add("assets/create", "AssetController", "create");
$router->add("assets/edit/{id}", "AssetController", "edit");

// Employee routes
$router->add("employees", "EmployeeController", "index");
$router->add("employees/create", "EmployeeController", "create");

// Department routes
$router->add("departments", "DepartmentController", "index");

// Category routes
$router->add("categories", "CategoryController", "index");

// Contract routes
$router->add("contracts", "ContractController", "index");

// Allocation routes
$router->add("allocations", "AllocationController", "index");
$router->add("allocations/create", "AllocationController", "create");

// Maintenance routes
$router->add("maintenance", "MaintenanceController", "index");

// User routes
$router->add("users", "UserController", "index");

$url = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];

$router->dispatch($url, $method);