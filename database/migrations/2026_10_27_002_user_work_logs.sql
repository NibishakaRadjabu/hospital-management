-- Add personal and management work logs without changing existing records.
-- Apply after 2026_10_27_001_rbac_and_roles.sql.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

SET @seed_work_log_permissions := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_002_user_work_logs'
);

INSERT INTO permissions (permission_key, name, description, category) VALUES
    ('work_logs.create', 'Record own work', 'Create a work log entry for the signed-in user.', 'Work Log'),
    ('work_logs.view_own', 'View own work log', 'View work log entries created by the signed-in user.', 'Work Log'),
    ('work_logs.view_all', 'View all work logs', 'Review work log entries created by any user.', 'Work Log')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category);

CREATE TABLE IF NOT EXISTS work_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    category VARCHAR(40) NOT NULL,
    summary VARCHAR(160) NOT NULL,
    details TEXT NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_work_logs_user_date (user_id, work_date, created_at),
    INDEX idx_work_logs_date_category (work_date, category),
    CONSTRAINT fk_work_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE @seed_work_log_permissions = 1
  AND r.role_key IN (
      'admin', 'super_admin', 'hospital_admin', 'doctor', 'nurse',
      'receptionist', 'pharmacist', 'laboratory_technician', 'radiologist',
      'accountant', 'store_keeper', 'hr', 'ambulance_driver'
  )
  AND p.permission_key IN ('work_logs.create', 'work_logs.view_own');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE @seed_work_log_permissions = 1
  AND r.role_key IN ('admin', 'super_admin', 'hospital_admin')
  AND p.permission_key = 'work_logs.view_all';

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_002_user_work_logs');
