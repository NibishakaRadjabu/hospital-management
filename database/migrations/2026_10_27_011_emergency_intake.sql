-- Allow triage to be recorded before patient registration is complete.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

ALTER TABLE emergency_triage MODIFY patient_id BIGINT UNSIGNED NULL;

SET @has_patient_name := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'emergency_triage'
      AND COLUMN_NAME = 'patient_name'
);
SET @emergency_name_ddl := IF(
    @has_patient_name = 0,
    'ALTER TABLE emergency_triage ADD COLUMN patient_name VARCHAR(160) NULL AFTER patient_id',
    'SELECT 1'
);
PREPARE emergency_name_stmt FROM @emergency_name_ddl;
EXECUTE emergency_name_stmt;
DEALLOCATE PREPARE emergency_name_stmt;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_011_emergency_intake');
