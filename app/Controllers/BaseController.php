<?php

namespace App\Controllers;

use App\Core\Session;

abstract class BaseController
{
    protected function view($view, $data = [])
    {
        extract($data);
        
        $viewPath = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            die("View not found: $view");
        }
    }

    protected function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo "<script>window.location.href = \"$url\";</script>";
            exit;
        }
    }
}