### public/index.php
```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();

// Routes
$router->add('', 'DashboardController', 'index');
$router->add('dashboard', 'DashboardController', 'index');
$router->add('login', 'AuthController', 'login');
$router->add('logout', 'AuthController', 'logout');

$router->add('assets', 'AssetController', 'index');
$router->add('assets/create', 'AssetController', 'create');
$router->add('assets/edit/{id}', 'AssetController', 'edit');
$router->add('assets/delete/{id}', 'AssetController', 'delete');
$router->add('assets/export', 'AssetController', 'export');

$router->add('categories', 'AssetCategoryController', 'index');
$router->add('employees', 'EmployeeController', 'index');
$router->add('departments', 'DepartmentController', 'index');
$router->add('contracts', 'ContractController', 'index');
$router->add('allocations', 'AllocationController', 'index');
$router->add('maintenance', 'MaintenanceController', 'index');
$router->add('users', 'UserController', 'index');

// Dispatch request
$url = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $method);
```