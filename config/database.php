<?php
// config/database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'employee_management';
    private $username = 'postgres';
    private $password = 'your_password';
    private $port = '5432';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Hàm helper để format ngày tháng
function formatDate($date) {
    if ($date) {
        return date('d/m/Y', strtotime($date));
    }
    return '';
}

// Hàm helper để format datetime
function formatDateTime($datetime) {
    if ($datetime) {
        return date('d/m/Y H:i:s', strtotime($datetime));
    }
    return '';
}

// Hàm helper để validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hàm helper để validate phone number
function validatePhone($phone) {
    return preg_match('/^[0-9+\-\(\)\s]{10,15}$/', $phone);
}

// Hàm helper để sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Hàm helper để tạo response JSON
function jsonResponse($data, $success = true, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
?>