-- Add emergency triage, nursing observations, radiology reporting, and dispensing records.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

SET @seed_operational_workflows := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_007_operational_workflows'
);

INSERT INTO permissions (permission_key, name, description, category) VALUES
    ('triage.view', 'View emergency triage', 'View emergency arrivals and triage assessments.', 'Emergency'),
    ('triage.manage', 'Assess emergency patients', 'Record triage acuity, symptoms, and initial vital signs.', 'Emergency')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), category = VALUES(category);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE @seed_operational_workflows = 1
  AND r.role_key IN ('admin', 'super_admin', 'hospital_admin', 'doctor', 'nurse')
  AND p.permission_key = 'triage.view';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE @seed_operational_workflows = 1
  AND r.role_key IN ('admin', 'super_admin', 'doctor', 'nurse')
  AND p.permission_key = 'triage.manage';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE @seed_operational_workflows = 1
  AND r.role_key = 'doctor'
  AND p.permission_key = 'medicines.view';

CREATE TABLE IF NOT EXISTS radiology_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NULL,
    scan_name VARCHAR(160) NOT NULL,
    clinical_notes TEXT NULL,
    status ENUM('requested', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
    report TEXT NULL,
    performed_by BIGINT UNSIGNED NULL,
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    INDEX idx_radiology_status (status, requested_at),
    CONSTRAINT fk_radiology_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_radiology_requester FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_radiology_performer FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS emergency_triage (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    assessed_by BIGINT UNSIGNED NOT NULL,
    acuity ENUM('resuscitation', 'emergency', 'urgent', 'less_urgent', 'non_urgent') NOT NULL,
    chief_complaint VARCHAR(500) NOT NULL,
    temperature_c DECIMAL(4,1) NULL,
    pulse_bpm SMALLINT UNSIGNED NULL,
    respiratory_rate SMALLINT UNSIGNED NULL,
    systolic_bp SMALLINT UNSIGNED NULL,
    diastolic_bp SMALLINT UNSIGNED NULL,
    oxygen_saturation TINYINT UNSIGNED NULL,
    notes TEXT NULL,
    assessed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_triage_time (assessed_at, acuity),
    CONSTRAINT fk_triage_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_triage_assessor FOREIGN KEY (assessed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nursing_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    recorded_by BIGINT UNSIGNED NOT NULL,
    note_type ENUM('observation', 'vital_signs', 'medication_administered', 'care') NOT NULL,
    note TEXT NOT NULL,
    temperature_c DECIMAL(4,1) NULL,
    pulse_bpm SMALLINT UNSIGNED NULL,
    respiratory_rate SMALLINT UNSIGNED NULL,
    systolic_bp SMALLINT UNSIGNED NULL,
    diastolic_bp SMALLINT UNSIGNED NULL,
    oxygen_saturation TINYINT UNSIGNED NULL,
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nursing_patient_time (patient_id, recorded_at),
    CONSTRAINT fk_nursing_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_nursing_user FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prescription_dispensations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prescription_item_id BIGINT UNSIGNED NOT NULL UNIQUE,
    dispensed_by BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    dispensed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dispense_item FOREIGN KEY (prescription_item_id) REFERENCES prescription_items(id),
    CONSTRAINT fk_dispense_user FOREIGN KEY (dispensed_by) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_007_operational_workflows');
