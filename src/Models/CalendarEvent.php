<?php
namespace App\Models;

class CalendarEvent
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($companyId, $data)
    {
        $sql = "INSERT INTO calendar_events (company_id, title, start_date, end_date, description, type, original_rule_id) 
                VALUES (:company_id, :title, :start, :end, :desc, :type, :rule_id)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'title' => $data['title'],
            'start' => $data['start_date'],
            'end' => $data['end_date'],
            'desc' => $data['description'] ?? '',
            'type' => $data['type'] ?? 'tax',
            'rule_id' => $data['rule_id'] ?? null
        ]);

        return $this->pdo->lastInsertId();
    }

    public function findByCompany($companyId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM calendar_events WHERE company_id = :cid AND status = 'active' ORDER BY start_date ASC");
        $stmt->execute(['cid' => $companyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
