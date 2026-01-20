<?php
namespace App;

use App\Models\User;

class Auth
{
    private $pdo;
    private $userModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register($email, $password, $fullName)
    {
        return $this->userModel->create($email, $password, $fullName);
    }

    public function login($email, $password)
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password_hash'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }

        return false;
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
    }

    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser()
    {
        if (!$this->isAuthenticated())
            return null;
        return $this->userModel->findById($_SESSION['user_id']);
    }

    public function requireLogin()
    {
        if (!$this->isAuthenticated()) {
            header('Location: login.php');
            exit;
        }
    }
}
