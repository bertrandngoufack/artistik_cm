-- =====================================================
-- CRÉATION DE LA TABLE AUDIT_LOGS
-- =====================================================

-- Table des logs d'audit
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NULL,
    old_values TEXT NULL,
    new_values TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertion de quelques logs de test
INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES
(1, 'CREATE', 'teachers', 1, NULL, '{"first_name":"Jean","last_name":"Dupont","email":"jean.dupont@kissai.cm"}', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'UPDATE', 'teachers', 1, '{"first_name":"Jean","last_name":"Dupont"}', '{"first_name":"Jean","last_name":"Dupont","phone":"+237679481111"}', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'ASSIGN', 'class_subjects', 1, NULL, '{"class_id":1,"subject_id":1,"teacher_id":1}', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'REMOVE', 'class_subjects', 1, '{"class_id":1,"subject_id":1,"teacher_id":1}', NULL, '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

-- Vérification de la création
SELECT 'Table audit_logs créée avec succès' as status;
SELECT COUNT(*) as nombre_logs FROM audit_logs;







