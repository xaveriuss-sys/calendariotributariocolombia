-- ============================================
-- SAAS MODULES SCHEMA
-- ============================================

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('superadmin', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    activation_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. COMPANIES TABLE (Empresas creadas por usuarios)
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nit VARCHAR(20) NOT NULL,
    dv CHAR(1) NOT NULL,
    name VARCHAR(200) NOT NULL,
    city VARCHAR(100) DEFAULT NULL,
    settings JSON DEFAULT NULL COMMENT 'Config: periodicidad IVA, ICA, etc.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. CALENDAR EVENTS (Eventos persistentes y editables)
CREATE TABLE IF NOT EXISTS calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT,
    type ENUM('tax', 'custom') DEFAULT 'tax',
    status ENUM('active', 'completed', 'deleted') DEFAULT 'active',
    original_rule_id INT DEFAULT NULL COMMENT 'Ref a tax_rules si viene de auto-generación',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_companies_user ON companies(user_id);
CREATE INDEX idx_events_company_date ON calendar_events(company_id, start_date);
