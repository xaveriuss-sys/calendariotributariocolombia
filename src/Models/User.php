<?php
namespace App\Models;

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($email, $password, $fullName, $role = 'user')
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16)); // Activation token

        $sql = "INSERT INTO users (email, password_hash, full_name, role, activation_token) VALUES (:email, :pass, :name, :role, :token)";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([
                'email' => $email,
                'pass' => $hash,
                'name' => $fullName,
                'role' => $role,
                'token' => $token
            ]);
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new \Exception("El correo electrónico ya está registrado.");
            }
            throw $e;
        }
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
