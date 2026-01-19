-- ============================================
-- CALENDARIO TRIBUTARIO COLOMBIA 2026
-- Base de Datos - Script de Creación
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS calendario_tributario
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE calendario_tributario;

-- ============================================
-- TABLA: tax_rules
-- Reglas tributarias base
-- ============================================
CREATE TABLE IF NOT EXISTS tax_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    impuesto_nombre VARCHAR(100) NOT NULL,
    impuesto_codigo VARCHAR(20) NOT NULL,
    ciudad VARCHAR(50) DEFAULT NULL COMMENT 'NULL = Nacional',
    periodicidad ENUM('anual', 'bimestral', 'cuatrimestral', 'mensual', 'fija') NOT NULL,
    uvt_tope DECIMAL(12,2) DEFAULT NULL COMMENT 'Tope en UVT para determinar periodicidad',
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLA: tax_deadlines_2026
-- Fechas de vencimiento 2026
-- ============================================
CREATE TABLE IF NOT EXISTS tax_deadlines_2026 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_id INT NOT NULL,
    ultimo_digito_nit VARCHAR(5) DEFAULT NULL COMMENT 'NULL o * = aplica a todos',
    periodo VARCHAR(50) DEFAULT NULL COMMENT 'Ej: Enero-Febrero, Cuota 1, etc.',
    fecha_vencimiento DATE NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rule_id) REFERENCES tax_rules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- INSERTAR REGLAS TRIBUTARIAS
-- ============================================

-- Nivel Nacional - DIAN
INSERT INTO tax_rules (impuesto_nombre, impuesto_codigo, ciudad, periodicidad, uvt_tope, descripcion) VALUES
('Renta Personas Jurídicas', 'RENTA_PJ', NULL, 'anual', NULL, 'Impuesto de Renta - 2 cuotas según último dígito NIT'),
('IVA Bimestral', 'IVA_BIM', NULL, 'bimestral', 92000, 'IVA para grandes contribuyentes (>92.000 UVT)'),
('IVA Cuatrimestral', 'IVA_CUAT', NULL, 'cuatrimestral', 92000, 'IVA para pequeños contribuyentes (<=92.000 UVT)'),
('Retención en la Fuente', 'RETEFUENTE', NULL, 'mensual', NULL, 'Retención mensual obligatoria'),

-- Nivel Municipal
('ICA Bogotá Bimestral', 'ICA_BOG_BIM', 'Bogotá', 'bimestral', 391, 'ICA Bogotá para impuesto cargo > 391 UVT'),
('ICA Bogotá Anual', 'ICA_BOG_ANUAL', 'Bogotá', 'anual', 391, 'ICA Bogotá para impuesto cargo <= 391 UVT'),
('ICA Medellín Bimestral', 'ICA_MED', 'Medellín', 'bimestral', NULL, 'ICA Medellín - Régimen común'),
('ICA Cali', 'ICA_CALI', 'Cali', 'bimestral', NULL, 'ICA Cali - Régimen común'),

-- Obligaciones Laborales
('Intereses sobre Cesantías', 'LAB_INT_CES', NULL, 'fija', NULL, 'Intereses sobre cesantías a empleados'),
('Consignación de Cesantías', 'LAB_CESANTIAS', NULL, 'fija', NULL, 'Consignación de cesantías al fondo'),
('Prima de Servicios Junio', 'LAB_PRIMA_JUN', NULL, 'fija', NULL, 'Prima de servicios primer semestre'),
('Reducción Jornada 46h', 'LAB_JORNADA', NULL, 'fija', NULL, 'Entrada en vigencia jornada de 46 horas'),
('Prima de Servicios Diciembre', 'LAB_PRIMA_DIC', NULL, 'fija', NULL, 'Prima de servicios segundo semestre');

-- ============================================
-- INSERTAR FECHAS DE VENCIMIENTO 2026
-- ============================================

-- ----------------------------------------
-- RENTA PERSONAS JURÍDICAS - Cuota 1 (Mayo 2026)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(1, '1', 'Cuota 1', '2026-05-12', 'Renta P.J. - Primera Cuota'),
(1, '2', 'Cuota 1', '2026-05-13', 'Renta P.J. - Primera Cuota'),
(1, '3', 'Cuota 1', '2026-05-14', 'Renta P.J. - Primera Cuota'),
(1, '4', 'Cuota 1', '2026-05-15', 'Renta P.J. - Primera Cuota'),
(1, '5', 'Cuota 1', '2026-05-18', 'Renta P.J. - Primera Cuota'),
(1, '6', 'Cuota 1', '2026-05-19', 'Renta P.J. - Primera Cuota'),
(1, '7', 'Cuota 1', '2026-05-20', 'Renta P.J. - Primera Cuota'),
(1, '8', 'Cuota 1', '2026-05-21', 'Renta P.J. - Primera Cuota'),
(1, '9', 'Cuota 1', '2026-05-22', 'Renta P.J. - Primera Cuota'),
(1, '0', 'Cuota 1', '2026-05-25', 'Renta P.J. - Primera Cuota');

-- RENTA PERSONAS JURÍDICAS - Cuota 2 (Julio 2026)
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(1, '1', 'Cuota 2', '2026-07-09', 'Renta P.J. - Segunda Cuota'),
(1, '2', 'Cuota 2', '2026-07-10', 'Renta P.J. - Segunda Cuota'),
(1, '3', 'Cuota 2', '2026-07-13', 'Renta P.J. - Segunda Cuota'),
(1, '4', 'Cuota 2', '2026-07-14', 'Renta P.J. - Segunda Cuota'),
(1, '5', 'Cuota 2', '2026-07-15', 'Renta P.J. - Segunda Cuota'),
(1, '6', 'Cuota 2', '2026-07-16', 'Renta P.J. - Segunda Cuota'),
(1, '7', 'Cuota 2', '2026-07-17', 'Renta P.J. - Segunda Cuota'),
(1, '8', 'Cuota 2', '2026-07-20', 'Renta P.J. - Segunda Cuota'),
(1, '9', 'Cuota 2', '2026-07-21', 'Renta P.J. - Segunda Cuota'),
(1, '0', 'Cuota 2', '2026-07-22', 'Renta P.J. - Segunda Cuota');

-- ----------------------------------------
-- IVA BIMESTRAL 2026 (rule_id = 2)
-- ----------------------------------------
-- Bimestre 1 (Enero-Febrero)
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(2, '1-5', 'Enero-Febrero', '2026-03-11', 'IVA Bimestral - Período 1'),
(2, '6-0', 'Enero-Febrero', '2026-03-12', 'IVA Bimestral - Período 1');

-- Bimestre 2 (Marzo-Abril)
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(2, '1-5', 'Marzo-Abril', '2026-05-14', 'IVA Bimestral - Período 2'),
(2, '6-0', 'Marzo-Abril', '2026-05-15', 'IVA Bimestral - Período 2');

-- Bimestre 3 (Mayo-Junio)
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(2, '1-5', 'Mayo-Junio', '2026-07-14', 'IVA Bimestral - Período 3'),
(2, '6-0', 'Mayo-Junio', '2026-07-15', 'IVA Bimestral - Período 3');

-- Bimestre 4 (Julio-Agosto)
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(2, '1-5', 'Julio-Agosto', '2026-09-10', 'IVA Bimestral - Período 4'),
(2, '6-0', 'Julio-Agosto', '2026-09-11', 'IVA Bimestral - Período 4');

-- Bimestre 5 (Septiembre-Octubre)
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(2, '1-5', 'Septiembre-Octubre', '2026-11-11', 'IVA Bimestral - Período 5'),
(2, '6-0', 'Septiembre-Octubre', '2026-11-12', 'IVA Bimestral - Período 5');

-- Bimestre 6 (Noviembre-Diciembre)
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(2, '1-5', 'Noviembre-Diciembre', '2027-01-13', 'IVA Bimestral - Período 6'),
(2, '6-0', 'Noviembre-Diciembre', '2027-01-14', 'IVA Bimestral - Período 6');

-- ----------------------------------------
-- IVA CUATRIMESTRAL 2026 (rule_id = 3)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(3, '1-5', 'Enero-Abril', '2026-05-14', 'IVA Cuatrimestral - Período 1'),
(3, '6-0', 'Enero-Abril', '2026-05-15', 'IVA Cuatrimestral - Período 1'),
(3, '1-5', 'Mayo-Agosto', '2026-09-10', 'IVA Cuatrimestral - Período 2'),
(3, '6-0', 'Mayo-Agosto', '2026-09-11', 'IVA Cuatrimestral - Período 2'),
(3, '1-5', 'Septiembre-Diciembre', '2027-01-13', 'IVA Cuatrimestral - Período 3'),
(3, '6-0', 'Septiembre-Diciembre', '2027-01-14', 'IVA Cuatrimestral - Período 3');

-- ----------------------------------------
-- RETENCIÓN EN LA FUENTE 2026 (rule_id = 4)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(4, '1-5', 'Enero', '2026-02-11', 'Retención Fuente - Enero'),
(4, '6-0', 'Enero', '2026-02-12', 'Retención Fuente - Enero'),
(4, '1-5', 'Febrero', '2026-03-11', 'Retención Fuente - Febrero'),
(4, '6-0', 'Febrero', '2026-03-12', 'Retención Fuente - Febrero'),
(4, '1-5', 'Marzo', '2026-04-14', 'Retención Fuente - Marzo'),
(4, '6-0', 'Marzo', '2026-04-15', 'Retención Fuente - Marzo'),
(4, '1-5', 'Abril', '2026-05-14', 'Retención Fuente - Abril'),
(4, '6-0', 'Abril', '2026-05-15', 'Retención Fuente - Abril'),
(4, '1-5', 'Mayo', '2026-06-10', 'Retención Fuente - Mayo'),
(4, '6-0', 'Mayo', '2026-06-11', 'Retención Fuente - Mayo'),
(4, '1-5', 'Junio', '2026-07-14', 'Retención Fuente - Junio'),
(4, '6-0', 'Junio', '2026-07-15', 'Retención Fuente - Junio'),
(4, '1-5', 'Julio', '2026-08-12', 'Retención Fuente - Julio'),
(4, '6-0', 'Julio', '2026-08-13', 'Retención Fuente - Julio'),
(4, '1-5', 'Agosto', '2026-09-10', 'Retención Fuente - Agosto'),
(4, '6-0', 'Agosto', '2026-09-11', 'Retención Fuente - Agosto'),
(4, '1-5', 'Septiembre', '2026-10-13', 'Retención Fuente - Septiembre'),
(4, '6-0', 'Septiembre', '2026-10-14', 'Retención Fuente - Septiembre'),
(4, '1-5', 'Octubre', '2026-11-11', 'Retención Fuente - Octubre'),
(4, '6-0', 'Octubre', '2026-11-12', 'Retención Fuente - Octubre'),
(4, '1-5', 'Noviembre', '2026-12-10', 'Retención Fuente - Noviembre'),
(4, '6-0', 'Noviembre', '2026-12-11', 'Retención Fuente - Noviembre'),
(4, '1-5', 'Diciembre', '2027-01-13', 'Retención Fuente - Diciembre'),
(4, '6-0', 'Diciembre', '2027-01-14', 'Retención Fuente - Diciembre');

-- ----------------------------------------
-- ICA BOGOTÁ BIMESTRAL (rule_id = 5)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(5, '*', 'Enero-Febrero', '2026-03-20', 'ICA Bogotá Bimestral - Período 1'),
(5, '*', 'Marzo-Abril', '2026-05-22', 'ICA Bogotá Bimestral - Período 2'),
(5, '*', 'Mayo-Junio', '2026-07-24', 'ICA Bogotá Bimestral - Período 3'),
(5, '*', 'Julio-Agosto', '2026-09-18', 'ICA Bogotá Bimestral - Período 4'),
(5, '*', 'Septiembre-Octubre', '2026-11-20', 'ICA Bogotá Bimestral - Período 5'),
(5, '*', 'Noviembre-Diciembre', '2027-01-22', 'ICA Bogotá Bimestral - Período 6');

-- ----------------------------------------
-- ICA BOGOTÁ ANUAL (rule_id = 6)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(6, '*', 'Anual 2025', '2027-02-26', 'ICA Bogotá Anual - Declaración año gravable 2025');

-- ----------------------------------------
-- ICA MEDELLÍN (rule_id = 7)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(7, '*', 'Enero-Febrero', '2026-03-13', 'ICA Medellín - Período 1'),
(7, '*', 'Marzo-Abril', '2026-05-15', 'ICA Medellín - Período 2'),
(7, '*', 'Mayo-Junio', '2026-07-15', 'ICA Medellín - Período 3'),
(7, '*', 'Julio-Agosto', '2026-09-15', 'ICA Medellín - Período 4'),
(7, '*', 'Septiembre-Octubre', '2026-11-13', 'ICA Medellín - Período 5'),
(7, '*', 'Noviembre-Diciembre', '2027-01-15', 'ICA Medellín - Período 6');

-- ----------------------------------------
-- ICA CALI (rule_id = 8)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(8, '*', 'Enero-Febrero', '2026-03-16', 'ICA Cali - Período 1'),
(8, '*', 'Marzo-Abril', '2026-05-18', 'ICA Cali - Período 2'),
(8, '*', 'Mayo-Junio', '2026-07-17', 'ICA Cali - Período 3'),
(8, '*', 'Julio-Agosto', '2026-09-16', 'ICA Cali - Período 4'),
(8, '*', 'Septiembre-Octubre', '2026-11-16', 'ICA Cali - Período 5'),
(8, '*', 'Noviembre-Diciembre', '2027-01-18', 'ICA Cali - Período 6');

-- ----------------------------------------
-- OBLIGACIONES LABORALES (rule_id = 9-13)
-- ----------------------------------------
INSERT INTO tax_deadlines_2026 (rule_id, ultimo_digito_nit, periodo, fecha_vencimiento, descripcion) VALUES
(9, '*', '2025', '2026-01-31', 'Pago intereses sobre cesantías a empleados'),
(10, '*', '2025', '2026-02-14', 'Consignación de cesantías a fondos'),
(11, '*', '2026', '2026-06-30', 'Prima de servicios - Primer semestre'),
(12, '*', '2026', '2026-07-15', 'Implementación jornada máxima de 46 horas semanales'),
(13, '*', '2026', '2026-12-20', 'Prima de servicios - Segundo semestre');

-- ============================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- ============================================
CREATE INDEX idx_deadlines_rule ON tax_deadlines_2026(rule_id);
CREATE INDEX idx_deadlines_digito ON tax_deadlines_2026(ultimo_digito_nit);
CREATE INDEX idx_deadlines_fecha ON tax_deadlines_2026(fecha_vencimiento);
CREATE INDEX idx_rules_codigo ON tax_rules(impuesto_codigo);
CREATE INDEX idx_rules_ciudad ON tax_rules(ciudad);
