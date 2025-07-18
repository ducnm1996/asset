### app/Controllers/AuthController.php
```php
<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;

class AuthController extends BaseController
{
    public function login()
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (Auth::login($username, $password)) {
                $this->redirect('/dashboard');
            } else {
                $this->setFlash('error', 'Invalid username or password');
            }
        }

        $this->view('auth/login', [
            'error' => $this->getFlash('error')
        ]);
    }

    public function logout()
    {
        Auth::logout();
        $this->redirect('/login');
    }
}