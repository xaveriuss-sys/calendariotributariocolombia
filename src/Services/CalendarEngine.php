<?php
namespace App\Services;

require_once dirname(__DIR__) . '/config.php';

class CalendarEngine
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Generar y guardar/sobrescribir eventos para una empresa
     */
    public function generateEvents($companyId, $settings)
    {
        // 1. Limpiar eventos 'tax' previos activos para esta empresa (evitar duplicados en regeneración)
        $this->clearTaxEvents($companyId);

        // 2. Extraer parámetros
        $nit = $settings['nit'];
        // $dv = $settings['dv']; // No necesario para cálculo, solo para descripción
        $city = $settings['city'];
        $ingresos = floatval(preg_replace('/[^0-9]/', '', $settings['settings']['ingresos'] ?? 0));
        $ica_cargo = floatval(preg_replace('/[^0-9]/', '', $settings['settings']['ica_cargo'] ?? 0));

        // 3. Determinar perfil tributario
        $ultimoDigito = substr($nit, -1);
        $grupoDigitos = ($ultimoDigito >= 1 && $ultimoDigito <= 5) ? '1-5' : '6-0';

        $umbalIVA = uvtToPesos(UVT_TOPE_IVA);
        $umbralICABog = uvtToPesos(UVT_TOPE_ICA_BOG);

        $ivaPeriodicidad = ($ingresos > $umbalIVA) ? 'bimestral' : 'cuatrimestral';
        $ivaCodigo = ($ivaPeriodicidad === 'bimestral') ? 'IVA_BIM' : 'IVA_CUAT';

        $icaBogotaCodigo = null;
        if ($city === 'Bogotá') {
            $icaBogotaCodigo = ($ica_cargo > $umbralICABog) ? 'ICA_BOG_BIM' : 'ICA_BOG_ANUAL';
        }

        // 4. Obtener reglas y deadlines
        // TODO: Optimizar con una sola query grande o vistas, por ahora queries separadas por claridad

        // Renta
        $this->insertEventsFromRule('RENTA_PJ', $ultimoDigito, $companyId);

        // IVA
        $this->insertEventsFromRule($ivaCodigo, $grupoDigitos, $companyId);

        // Retefuente
        $this->insertEventsFromRule('RETEFUENTE', $grupoDigitos, $companyId);

        // ICA Bogotá
        if ($icaBogotaCodigo) {
            $this->insertEventsFromRule($icaBogotaCodigo, '*', $companyId);
        }

        // ICA Medellín
        if ($city === 'Medellín') {
            $this->insertEventsFromRule('ICA_MED', '*', $companyId);
        }

        // ICA Cali
        if ($city === 'Cali') {
            $this->insertEventsFromRule('ICA_CALI', '*', $companyId);
        }

        // Laborales
        $this->insertEventsFromRule('LAB_%', '*', $companyId, true);

        return true;
    }

    private function clearTaxEvents($companyId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM calendar_events WHERE company_id = :id AND type = 'tax'");
        $stmt->execute(['id' => $companyId]);
    }

    private function insertEventsFromRule($ruleCodigo, $digito, $companyId, $isLike = false)
    {
        $operator = $isLike ? 'LIKE' : '=';

        // Primero obtener el rule_id (o rules si es LIKE)
        $ruleSql = "SELECT id, impuesto_nombre FROM tax_rules WHERE impuesto_codigo $operator :code AND activo = 1";
        $stmt = $this->pdo->prepare($ruleSql);
        $stmt->execute(['code' => $ruleCodigo]);
        $rules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rules))
            return;

        foreach ($rules as $rule) {
            // Buscar deadlines
            $sql = "SELECT fecha_vencimiento, descripcion, periodo 
                    FROM tax_deadlines_2026 
                    WHERE rule_id = :rule_id 
                    AND (ultimo_digito_nit = :digito OR ultimo_digito_nit = '*' OR ultimo_digito_nit IS NULL)";

            // Ajuste para lógica de dígito exacto vs rango vs *
            // La BD tiene '1', '1-5', '*', NULL.
            // Si mi $digito es '3', coincide con '3', con '1-5' (si la lógica estuviera en SQL, pero aquí está simplificada en la DB actual)
            // IMPORTANTE: El backend generator.php usaba queries específicas para '1-5' o 'digit'. 
            // Aquí replicamos esa lógica exacta para no romper nada.

            // Si el argumento $digito es '1-5' o '6-0', la query debe buscar eso LITERALMENTE en la DB 
            // porque así están guardados los rangos en text.

            $stmtDeadlines = $this->pdo->prepare($sql);
            $stmtDeadlines->execute(['rule_id' => $rule['id'], 'digito' => $digito]);
            $deadlines = $stmtDeadlines->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($deadlines as $d) {
                // Insertar evento
                $title = $rule['impuesto_nombre'] . " - " . $d['periodo'];
                $this->saveEvent($companyId, [
                    'title' => $title,
                    'start_date' => $d['fecha_vencimiento'],
                    'end_date' => $d['fecha_vencimiento'], // Eventos de todo el día
                    'description' => $d['descripcion'],
                    'type' => 'tax',
                    'original_rule_id' => $rule['id']
                ]);
            }
        }
    }

    private function saveEvent($companyId, $data)
    {
        $sql = "INSERT INTO calendar_events (company_id, title, start_date, end_date, description, type, original_rule_id) 
                VALUES (:cid, :title, :start, :end, :desc, :type, :rid)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cid' => $companyId,
            'title' => $data['title'],
            'start' => $data['start_date'],
            'end' => $data['end_date'],
            'desc' => $data['description'],
            'type' => $data['type'],
            'rid' => $data['original_rule_id']
        ]);
    }
}
