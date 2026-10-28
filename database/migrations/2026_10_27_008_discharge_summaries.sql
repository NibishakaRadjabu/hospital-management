-- Preserve diagnosis, discharge instructions, medicines, and follow-up details.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

CREATE TABLE IF NOT EXISTS discharge_summaries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admission_id BIGINT UNSIGNED NOT NULL UNIQUE,
    written_by BIGINT UNSIGNED NULL,
    diagnosis TEXT NULL,
    summary TEXT NOT NULL,
    medication_plan TEXT NULL,
    follow_up TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_discharge_summary_admission FOREIGN KEY (admission_id) REFERENCES admissions(id),
    CONSTRAINT fk_discharge_summary_user FOREIGN KEY (written_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_008_discharge_summaries');
