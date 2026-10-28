-- Record services delivered to patients, attributed to the staff member.
-- Apply after migrations 001 and 002. This migration does not alter existing records.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

SET @seed_patient_service_permissions := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_003_patient_services'
);

INSERT INTO permissions (permission_key, name, description, category) VALUES
    ('patient_services.create', 'Record patient services', 'Record a service personally provided to a patient.', 'Patient Services'),
    ('patient_services.view_own', 'View own patient services', 'View service records created by the signed-in user.', 'Patient Services'),
    ('patient_services.view_all', 'View all patient services', 'Review service records created by any staff member.', 'Patient Services')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category);

CREATE TABLE IF NOT EXISTS patient_services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    provided_by BIGINT UNSIGNED NOT NULL,
    service_date DATE NOT NULL,
    category VARCHAR(40) NOT NULL,
    service_name VARCHAR(160) NOT NULL,
    details TEXT NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient_services_patient_date (patient_id, service_date, created_at),
    INDEX idx_patient_services_staff_date (provided_by, service_date, created_at),
    CONSTRAINT fk_patient_services_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_patient_services_user FOREIGN KEY (provided_by) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE @seed_patient_service_permissions = 1
  AND r.role_key IN (
      'admin', 'super_admin', 'hospital_admin', 'doctor', 'nurse',
      'receptionist', 'pharmacist', 'laboratory_technician', 'radiologist',
      'accountant', 'store_keeper', 'hr', 'ambulance_driver'
  )
  AND p.permission_key IN ('patient_services.create', 'patient_services.view_own');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE @seed_patient_service_permissions = 1
  AND r.role_key IN ('admin', 'super_admin', 'hospital_admin')
  AND p.permission_key = 'patient_services.view_all';

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_003_patient_services');