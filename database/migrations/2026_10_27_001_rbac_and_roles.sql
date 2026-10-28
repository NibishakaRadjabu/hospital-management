-- Upgrade an existing KivuCare database without dropping data.
-- Run once against the existing hospital_management database.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(120) NOT NULL PRIMARY KEY,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @seed_default_role_permissions := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_001_rbac_and_roles'
);

INSERT INTO roles (role_key, name) VALUES
    ('admin', 'Legacy System Administrator'),
    ('super_admin', 'Super Admin / System Owner'),
    ('hospital_admin', 'Hospital Administrator'),
    ('doctor', 'Doctor'),
    ('nurse', 'Nurse'),
    ('receptionist', 'Receptionist'),
    ('pharmacist', 'Pharmacist'),
    ('laboratory_technician', 'Laboratory Technician'),
    ('radiologist', 'Radiologist'),
    ('accountant', 'Accountant / Cashier'),
    ('store_keeper', 'Store Keeper / Inventory Officer'),
    ('hr', 'Human Resources'),
    ('ambulance_driver', 'Ambulance Driver'),
    ('patient_portal', 'Patient Portal User')
ON DUPLICATE KEY UPDATE name = VALUES(name);

CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NOT NULL,
    category VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id TINYINT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO permissions (permission_key, name, description, category) VALUES
    ('dashboard.view', 'View dashboard', 'Open the role-specific hospital dashboard.', 'System'),
    ('users.view', 'View staff accounts', 'View staff account records.', 'Administration'),
    ('users.manage', 'Manage staff accounts', 'Create and deactivate staff accounts.', 'Administration'),
    ('roles.manage', 'Manage roles and permissions', 'Create roles and configure role permissions.', 'System'),
    ('settings.manage', 'Manage system settings', 'Change global system configuration.', 'System'),
    ('database.backup', 'Back up database', 'Create and download database backups.', 'System'),
    ('audit.view', 'View audit activity', 'Review the system audit trail.', 'System'),
    ('departments.view', 'View departments', 'View hospital departments.', 'Administration'),
    ('departments.manage', 'Manage departments', 'Create and update hospital departments.', 'Administration'),
    ('reports.view', 'View hospital reports', 'View operational hospital reports.', 'Reports'),
    ('billing.overview', 'View billing overview', 'View high-level billing and revenue summaries.', 'Billing'),
    ('patients.view', 'View patient information', 'View patient identity and contact information.', 'Patients'),
    ('patients.create', 'Register patients', 'Register new patients.', 'Patients'),
    ('patients.update', 'Update patient information', 'Edit patient identity and contact information.', 'Patients'),
    ('patients.history', 'View medical history', 'View clinical visit and admission history.', 'Medical'),
    ('appointments.view', 'View appointments', 'View appointment schedules and statuses.', 'Appointments'),
    ('appointments.manage', 'Manage appointments', 'Book, update, and cancel appointments.', 'Appointments'),
    ('appointments.schedule', 'Manage doctor schedules', 'Configure doctor schedules.', 'Appointments'),
    ('appointments.self_book', 'Book own appointments', 'Book appointments for the signed-in patient only.', 'Patient Portal'),
    ('visits.view', 'View consultations', 'View consultation records.', 'Medical'),
    ('visits.manage', 'Manage consultations', 'Create diagnoses and medical notes.', 'Medical'),
    ('prescriptions.view', 'View prescriptions', 'View prescription records for care.', 'Medical'),
    ('prescriptions.create', 'Create prescriptions', 'Prescribe medicines for a patient.', 'Medical'),
    ('prescriptions.own_view', 'View own prescriptions', 'View prescriptions linked to the signed-in patient only.', 'Patient Portal'),
    ('lab.requests.view', 'View laboratory requests', 'View laboratory test requests.', 'Laboratory'),
    ('lab.requests.create', 'Request laboratory tests', 'Create laboratory test requests.', 'Laboratory'),
    ('lab.results.view', 'View laboratory results', 'View completed laboratory results.', 'Laboratory'),
    ('lab.results.manage', 'Record laboratory results', 'Enter and update laboratory results.', 'Laboratory'),
    ('lab.results.own_view', 'View own laboratory results', 'View results linked to the signed-in patient only.', 'Patient Portal'),
    ('radiology.requests.view', 'View radiology requests', 'View radiology scan requests.', 'Radiology'),
    ('radiology.requests.create', 'Request radiology scans', 'Create radiology scan requests.', 'Radiology'),
    ('radiology.results.manage', 'Record radiology results', 'Upload scan results and radiology reports.', 'Radiology'),
    ('admissions.view', 'View admissions', 'View inpatient admission and ward placement records.', 'Admissions'),
    ('admissions.create', 'Admit patients', 'Create patient admissions and assign beds.', 'Admissions'),
    ('admissions.discharge', 'Discharge patients', 'Discharge patients and release beds.', 'Admissions'),
    ('wards.view', 'View ward records', 'View wards, beds, and inpatient census.', 'Wards'),
    ('wards.manage', 'Manage wards', 'Manage ward and bed availability.', 'Wards'),
    ('nursing_notes.manage', 'Manage nursing notes and vitals', 'Record vital signs, nursing notes, and administered medicines.', 'Wards'),
    ('medicines.view', 'View medicine stock', 'View the medicine catalogue and stock levels.', 'Pharmacy'),
    ('medicines.manage', 'Manage medicine inventory', 'Manage medicines, stock levels, and expiries.', 'Pharmacy'),
    ('pharmacy.dispense', 'Dispense medicines', 'Dispense medicines against prescriptions.', 'Pharmacy'),
    ('pharmacy.reports', 'View pharmacy reports', 'Review dispensing and medicine stock reports.', 'Pharmacy'),
    ('billing.view', 'View financial records', 'View invoices and financial records.', 'Billing'),
    ('invoices.manage', 'Manage invoices', 'Create and update patient invoices.', 'Billing'),
    ('payments.manage', 'Receive payments', 'Record payments and insurance claims.', 'Billing'),
    ('receipts.issue', 'Issue payment receipts', 'Issue payment receipts.', 'Billing'),
    ('financial_reports.view', 'View financial reports', 'View detailed revenue and financial reports.', 'Billing'),
    ('inventory.view', 'View inventory', 'View equipment and general inventory stock.', 'Inventory'),
    ('inventory.manage', 'Manage inventory', 'Manage stock and equipment records.', 'Inventory'),
    ('suppliers.manage', 'Manage suppliers', 'Create and update supplier records.', 'Inventory'),
    ('purchase_orders.manage', 'Manage purchase orders', 'Create and update inventory purchase orders.', 'Inventory'),
    ('inventory.reports', 'View inventory reports', 'Review stock, purchases, and reorder reports.', 'Inventory'),
    ('employees.manage', 'Manage employees', 'Manage staff employee records and recruitment.', 'Human Resources'),
    ('payroll.manage', 'Manage payroll', 'Manage staff payroll records.', 'Human Resources'),
    ('attendance.manage', 'Manage attendance and leave', 'Manage staff attendance and leave records.', 'Human Resources'),
    ('ambulance.manage', 'Manage ambulance transport', 'View and manage ambulance requests and transport records.', 'Ambulance'),
    ('emergency_requests.view', 'View emergency requests', 'View emergency transport requests.', 'Ambulance'),
    ('invoices.own_view', 'View own invoices', 'View invoices linked to the signed-in patient only.', 'Patient Portal'),
    ('profile.own_update', 'Update own profile', 'Update the signed-in patient portal profile only.', 'Patient Portal')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category);

INSERT INTO departments (name, description) VALUES
    ('Reception', 'Patient registration and front desk'),
    ('OPD', 'Outpatient consultations'),
    ('Emergency', 'Emergency and urgent care'),
    ('Admission', 'Inpatient admission services'),
    ('Wards', 'Inpatient ward services'),
    ('Laboratory', 'Laboratory testing and results'),
    ('Radiology', 'Imaging and radiology services'),
    ('Pharmacy', 'Medicine dispensing and stock'),
    ('Surgery / Theatre', 'Surgical and theatre services'),
    ('Billing', 'Patient billing and payments'),
    ('Inventory', 'Supplies and equipment'),
    ('Human Resources', 'Staff and payroll'),
    ('Ambulance', 'Emergency patient transport')
ON DUPLICATE KEY UPDATE description = VALUES(description);

CREATE TABLE IF NOT EXISTS patient_portal_accounts (
    user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_patient_portal_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_portal_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id VARCHAR(80) NULL,
    details VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_logs_created (created_at),
    INDEX idx_audit_logs_actor (actor_user_id, created_at),
    CONSTRAINT fk_audit_logs_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Repair doctor profiles for doctor accounts created before doctor profiles were automated.
INSERT IGNORE INTO doctors (user_id)
SELECT u.id
FROM users u
JOIN roles r ON r.id = u.role_id
LEFT JOIN doctors d ON d.user_id = u.id
WHERE r.role_key = 'doctor' AND d.id IS NULL;

-- Seed default grants only on the first run. Re-running this migration will not
-- restore permissions that a system owner intentionally removed later.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permissions p
WHERE @seed_default_role_permissions = 1
  AND r.role_key IN ('admin', 'super_admin');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'users.view', 'users.manage', 'departments.view', 'departments.manage',
    'reports.view', 'billing.overview', 'patients.view', 'patients.create', 'patients.update',
    'appointments.view', 'appointments.manage', 'appointments.schedule',
    'admissions.view', 'admissions.create', 'admissions.discharge', 'wards.view', 'wards.manage',
    'medicines.view', 'inventory.view'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'hospital_admin';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'patients.create', 'patients.update',
    'appointments.view', 'appointments.manage', 'admissions.view', 'admissions.create'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'receptionist';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'patients.history', 'appointments.view', 'appointments.manage',
    'visits.view', 'visits.manage', 'prescriptions.view', 'prescriptions.create',
    'lab.requests.view', 'lab.requests.create', 'lab.results.view',
    'radiology.requests.view', 'radiology.requests.create', 'admissions.view',
    'admissions.create', 'admissions.discharge', 'wards.view'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'doctor';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'admissions.view', 'wards.view', 'wards.manage',
    'nursing_notes.manage'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'nurse';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'lab.requests.view', 'lab.results.view', 'lab.results.manage'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'laboratory_technician';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'prescriptions.view', 'medicines.view', 'medicines.manage',
    'pharmacy.dispense', 'pharmacy.reports'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'pharmacist';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'radiology.requests.view', 'radiology.results.manage'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'radiologist';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'billing.view', 'invoices.manage', 'payments.manage',
    'receipts.issue', 'financial_reports.view'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'accountant';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'inventory.view', 'inventory.manage', 'suppliers.manage',
    'purchase_orders.manage', 'inventory.reports'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'store_keeper';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'employees.manage', 'payroll.manage', 'attendance.manage'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'hr';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'ambulance.manage', 'emergency_requests.view'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'ambulance_driver';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'appointments.self_book', 'prescriptions.own_view',
    'lab.results.own_view', 'invoices.own_view', 'profile.own_update'
) WHERE @seed_default_role_permissions = 1 AND r.role_key = 'patient_portal';

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_001_rbac_and_roles');
