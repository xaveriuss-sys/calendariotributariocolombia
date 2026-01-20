<?php
namespace App\Models;

class Company
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($userId, $data)
    {
        $sql = "INSERT INTO companies (user_id, nit, dv, name, city, settings) 
                VALUES (:user_id, :nit, :dv, :name, :city, :settings)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'nit' => $data['nit'],
            'dv' => $data['dv'],
            'name' => $data['name'],
            'city' => $data['city'] ?? null,
            'settings' => isset($data['settings']) ? json_encode($data['settings']) : null
        ]);

        return $this->pdo->lastInsertId();
    }

    public function findByUser($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM companies WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM companies WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['nit', 'dv', 'name', 'city', 'settings'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = ($field === 'settings') ? json_encode($data[$field]) : $data[$field];
            }
        }

        if (empty($fields))
            return false;

        $sql = "UPDATE companies SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM companies WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
