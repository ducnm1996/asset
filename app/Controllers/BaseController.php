### app/Controllers/BaseController.php
```php
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
        header("Location: $url");
        exit;
    }

    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function setFlash($key, $message)
    {
        Session::flash($key, $message);
    }

    protected function getFlash($key)
    {
        return Session::flash($key);
    }
}
```