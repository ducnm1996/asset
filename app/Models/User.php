<?php

namespace App\Models;

class User extends BaseModel
{
    protected $table = 'users';

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}