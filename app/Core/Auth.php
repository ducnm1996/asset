<?php

namespace App\Core;

class Auth
{
    public static function check()
    {
        return Session::has("user_id");
    }

    public static function login($username, $password)
    {
        if ($username === "admin" && $password === "password") {
            Session::set("user_id", 1);
            Session::set("user_role", "admin");
            return true;
        }
        if ($username === "manager" && $password === "password") {
            Session::set("user_id", 2);
            Session::set("user_role", "manager");
            return true;
        }
        if ($username === "user" && $password === "password") {
            Session::set("user_id", 3);
            Session::set("user_role", "employee");
            return true;
        }
        return false;
    }

    public static function logout()
    {
        Session::destroy();
    }

    public static function user()
    {
        if (self::check()) {
            return [
                "id" => Session::get("user_id"),
                "username" => Session::get("user_role"),
                "role" => Session::get("user_role")
            ];
        }
        return null;
    }

    public static function requireAuth()
    {
        if (!self::check()) {
            header("Location: /login");
            exit;
        }
    }
}
EOF
