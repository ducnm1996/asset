### app/Core/Auth.php
```php
<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function check()
    {
        return Session::has('user_id');
    }

    public static function user()
    {
        if (self::check()) {
            $userId = Session::get('user_id');
            $userModel = new User();
            return $userModel->find($userId);
        }
        return null;
    }

    public static function login($username, $password)
    {
        $userModel = new User();
        $user = $userModel->findByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            Session::set('user_id', $user['id']);
            Session::set('user_role', $user['role']);
            return true;
        }
        return false;
    }

    public static function logout()
    {
        Session::destroy();
    }

    public static function hasRole($role)
    {
        $userRole = Session::get('user_role');
        if ($role === 'admin') {
            return $userRole === 'admin';
        } elseif ($role === 'manager') {
            return in_array($userRole, ['admin', 'manager']);
        }
        return true; // employee level
    }

    public static function requireAuth()
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole($role)
    {
        self::requireAuth();
        if (!self::hasRole($role)) {
            header('HTTP/1.0 403 Forbidden');
            echo "Access Denied";
            exit;
        }
    }
}
```