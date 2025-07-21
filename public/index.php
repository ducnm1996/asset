<?php

// Check autoload exists
if (!file_exists(__DIR__ . "/../vendor/autoload.php")) {
    die("Composer autoload not found. Please run: composer install");
}

require_once __DIR__ . "/../vendor/autoload.php";

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();

// Routes
$router->add("", "DashboardController", "index");
$router->add("dashboard", "DashboardController", "index");
$router->add("login", "AuthController", "login", "GET");
$router->add("login", "AuthController", "login", "POST");
$router->add("logout", "AuthController", "logout");
$router->add("assets", "AssetController", "index");

// Dispatch
$url = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];

$router->dispatch($url, $method);