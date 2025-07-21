<?php

namespace App\Controllers;

use App\Core\Auth;

class AuthController extends BaseController
{
    public function login()
    {
        if (Auth::check()) {
            $this->redirect("/dashboard");
            return;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST["username"] ?? "";
            $password = $_POST["password"] ?? "";
            
            if (Auth::login($username, $password)) {
                $this->redirect("/dashboard");
            } else {
                $error = "Invalid username or password";
            }
        }

        echo "<!DOCTYPE html><html><head><title>Login</title>";
        echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">";
        echo "</head><body class=\"bg-light\">";
        echo "<div class=\"container\"><div class=\"row justify-content-center mt-5\"><div class=\"col-md-4\">";
        echo "<div class=\"card\"><div class=\"card-header text-center bg-primary text-white\"><h4>Asset Management Login</h4></div>";
        echo "<div class=\"card-body\">";
        if (isset($error)) {
            echo "<div class=\"alert alert-danger\">$error</div>";
        }
        echo "<form method=\"post\">";
        echo "<div class=\"mb-3\"><label class=\"form-label\">Username</label><input type=\"text\" class=\"form-control\" name=\"username\" required></div>";
        echo "<div class=\"mb-3\"><label class=\"form-label\">Password</label><input type=\"password\" class=\"form-control\" name=\"password\" required></div>";
        echo "<button type=\"submit\" class=\"btn btn-primary w-100\">Login</button>";
        echo "</form>";
        echo "<div class=\"text-center mt-3\"><small>Demo: admin/password, manager/password, user/password</small></div>";
        echo "</div></div></div></div></div></body></html>";
    }

    public function logout()
    {
        Auth::logout();
        $this->redirect("/login");
    }
}
