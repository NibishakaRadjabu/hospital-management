CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

CREATE TABLE IF NOT EXISTS roles (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_key VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(120) NOT NULL PRIMARY KEY,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
    ('work_logs.create', 'Record own work', 'Create a work log entry for the signed-in user.', 'Work Log'),
    ('work_logs.view_own', 'View own work log', 'View work log entries created by the signed-in user.', 'Work Log'),
    ('work_logs.view_all', 'View all work logs', 'Review work log entries created by any user.', 'Work Log'),
    ('patient_services.create', 'Record patient services', 'Record a service personally provided to a patient.', 'Patient Services'),
    ('patient_services.view_own', 'View own patient services', 'View service records created by the signed-in user.', 'Patient Services'),
    ('patient_services.view_all', 'View all patient services', 'Review service records created by any staff member.', 'Patient Services'),
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
    ('radiology.results.own_view', 'View own radiology reports', 'View imaging reports linked to the signed-in patient only.', 'Patient Portal'),
    ('admissions.view', 'View admissions', 'View inpatient admission and ward placement records.', 'Admissions'),
    ('admissions.create', 'Admit patients', 'Create patient admissions and assign beds.', 'Admissions'),
    ('admissions.discharge', 'Discharge patients', 'Discharge patients and release beds.', 'Admissions'),
    ('wards.view', 'View ward records', 'View wards, beds, and inpatient census.', 'Wards'),
    ('wards.manage', 'Manage wards', 'Manage ward and bed availability.', 'Wards'),
    ('nursing_notes.manage', 'Manage nursing notes and vitals', 'Record vital signs, nursing notes, and administered medicines.', 'Wards'),
    ('triage.view', 'View emergency triage', 'View emergency arrivals and triage assessments.', 'Emergency'),
    ('triage.manage', 'Assess emergency patients', 'Record triage acuity, symptoms, and initial vital signs.', 'Emergency'),
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
    ('ambulance.requests.create', 'Request ambulance transport', 'Create an emergency transport request for a patient.', 'Ambulance'),
    ('emergency_requests.view', 'View emergency requests', 'View emergency transport requests.', 'Ambulance'),
    ('invoices.own_view', 'View own invoices', 'View invoices linked to the signed-in patient only.', 'Patient Portal'),
    ('profile.own_update', 'Update own profile', 'Update the signed-in patient portal profile only.', 'Patient Portal')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permissions p
WHERE r.role_key IN ('admin', 'super_admin');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'work_logs.create', 'work_logs.view_own'
) WHERE r.role_key IN (
    'hospital_admin', 'doctor', 'nurse', 'receptionist', 'pharmacist',
    'laboratory_technician', 'radiologist', 'accountant', 'store_keeper',
    'hr', 'ambulance_driver'
);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key = 'work_logs.view_all'
WHERE r.role_key IN ('hospital_admin');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'patient_services.create', 'patient_services.view_own'
) WHERE r.role_key IN (
    'admin', 'super_admin', 'hospital_admin', 'doctor', 'nurse', 'receptionist',
    'pharmacist', 'laboratory_technician', 'radiologist', 'accountant',
    'store_keeper', 'hr', 'ambulance_driver'
);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key = 'patient_services.view_all'
WHERE r.role_key IN ('admin', 'super_admin', 'hospital_admin');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'users.view', 'users.manage', 'departments.view', 'departments.manage',
    'reports.view', 'billing.overview', 'patients.view', 'patients.create', 'patients.update',
    'appointments.view', 'appointments.manage', 'appointments.schedule',
    'admissions.view', 'admissions.create', 'admissions.discharge', 'wards.view', 'wards.manage',
    'medicines.view', 'inventory.view'
) WHERE r.role_key = 'hospital_admin';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'patients.create', 'patients.update',
    'appointments.view', 'appointments.manage', 'admissions.view', 'admissions.create'
) WHERE r.role_key = 'receptionist';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'patients.history', 'appointments.view', 'appointments.manage',
    'visits.view', 'visits.manage', 'prescriptions.view', 'prescriptions.create',
    'lab.requests.view', 'lab.requests.create', 'lab.results.view',
    'radiology.requests.view', 'radiology.requests.create', 'admissions.view',
    'admissions.create', 'admissions.discharge', 'wards.view', 'medicines.view'
) WHERE r.role_key = 'doctor';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'admissions.view', 'wards.view', 'wards.manage',
    'nursing_notes.manage'
) WHERE r.role_key = 'nurse';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'patients.view', 'lab.requests.view', 'lab.results.view', 'lab.results.manage'
) WHERE r.role_key = 'laboratory_technician';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'prescriptions.view', 'medicines.view', 'medicines.manage',
    'pharmacy.dispense', 'pharmacy.reports'
) WHERE r.role_key = 'pharmacist';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'radiology.requests.view', 'radiology.results.manage'
) WHERE r.role_key = 'radiologist';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'billing.view', 'invoices.manage', 'payments.manage',
    'receipts.issue', 'financial_reports.view'
) WHERE r.role_key = 'accountant';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'inventory.view', 'inventory.manage', 'suppliers.manage',
    'purchase_orders.manage', 'inventory.reports'
) WHERE r.role_key = 'store_keeper';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'employees.manage', 'payroll.manage', 'attendance.manage'
) WHERE r.role_key = 'hr';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'ambulance.manage', 'emergency_requests.view'
) WHERE r.role_key = 'ambulance_driver';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key = 'triage.view'
WHERE r.role_key IN ('admin', 'super_admin', 'hospital_admin', 'doctor', 'nurse');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key = 'triage.manage'
WHERE r.role_key IN ('admin', 'super_admin', 'doctor', 'nurse');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key = 'ambulance.requests.create'
WHERE r.role_key IN ('admin', 'super_admin', 'hospital_admin', 'doctor', 'nurse', 'receptionist');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'appointments.self_book', 'prescriptions.own_view',
    'lab.results.own_view', 'radiology.results.own_view', 'invoices.own_view', 'profile.own_update'
) WHERE r.role_key = 'patient_portal';

CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

INSERT INTO departments (name, description) VALUES
    ('General Management', 'Overall administration and hospital management'),
    ('Reception and Registration', 'Patient reception, registration, and direction'),
    ('Emergency Department', 'Immediate care and urgent triage'),
    ('Admission', 'Inpatient admission services'),
    ('Wards', 'Inpatient ward services'),
    ('Ambulance', 'Emergency transport and patient transfers'),
    ('Outpatient Department', 'Outpatient consultations and treatment'),
    ('Internal Medicine', 'Diagnosis and treatment of adult medical conditions'),
    ('Pediatrics', 'Medical care for infants and children'),
    ('Maternity and Gynecology', 'Pregnancy, childbirth, and reproductive health'),
    ('Surgery', 'Surgical services and procedures'),
    ('Operating Theatre', 'Operating rooms and perioperative services'),
    ('Intensive Care and Critical Care Unit', 'Specialized care for critically ill patients'),
    ('Medical Laboratory', 'Laboratory tests and biological analyses'),
    ('Medical Imaging', 'X-rays, ultrasound, and diagnostic imaging'),
    ('Hospital Pharmacy', 'Medicine storage and dispensing'),
    ('Nursing Department', 'Nursing care and patient monitoring'),
    ('Nutrition and Dietetics', 'Nutrition assessment and dietary planning'),
    ('Physiotherapy and Rehabilitation', 'Recovery of physical function and mobility'),
    ('Infection Prevention and Control', 'Prevention and control of healthcare-associated infections'),
    ('Administration and Human Resources', 'Personnel and administrative documentation'),
    ('Accounting and Finance', 'Revenue, expenditure, accounting, and billing'),
    ('Information Technology and Digital Systems', 'Hospital systems, infrastructure, and data security'),
    ('Logistics and Procurement', 'Supplies, equipment, and purchasing'),
    ('Facilities Management and Maintenance', 'Buildings, infrastructure, and equipment maintenance'),
    ('Laundry and Cleaning Services', 'Premises cleanliness and hospital linen'),
    ('Mortuary Services', 'Preservation and management of deceased persons')
ON DUPLICATE KEY UPDATE description = VALUES(description);

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id TINYINT UNSIGNED NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS doctors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    department_id INT UNSIGNED NULL,
    license_number VARCHAR(80) NULL UNIQUE,
    CONSTRAINT fk_doctors_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_doctors_department FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS patients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_no VARCHAR(30) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    date_of_birth DATE NULL,
    sex ENUM('female', 'male', 'other', 'unspecified') NOT NULL DEFAULT 'unspecified',
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    address VARCHAR(255) NULL,
    blood_group VARCHAR(5) NULL,
    emergency_contact_name VARCHAR(160) NULL,
    emergency_contact_phone VARCHAR(40) NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_patients_name (last_name, first_name),
    INDEX idx_patients_phone (phone),
    CONSTRAINT fk_patients_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS patient_portal_accounts (
    user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_patient_portal_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_portal_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NULL,
    scheduled_at DATETIME NOT NULL,
    status ENUM('scheduled', 'checked_in', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'scheduled',
    reason VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_appointments_schedule (scheduled_at, status),
    CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NULL,
    appointment_id BIGINT UNSIGNED NULL,
    visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    chief_complaint TEXT NULL,
    diagnosis TEXT NULL,
    notes TEXT NULL,
    CONSTRAINT fk_visits_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_visits_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
    CONSTRAINT fk_visits_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    ward_type VARCHAR(80) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS beds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ward_id INT UNSIGNED NOT NULL,
    bed_number VARCHAR(30) NOT NULL,
    status ENUM('available', 'occupied', 'maintenance') NOT NULL DEFAULT 'available',
    UNIQUE KEY uq_beds_ward_number (ward_id, bed_number),
    INDEX idx_beds_status (status),
    CONSTRAINT fk_beds_ward FOREIGN KEY (ward_id) REFERENCES wards(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    bed_id INT UNSIGNED NOT NULL,
    admitted_by BIGINT UNSIGNED NULL,
    admitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    discharged_at DATETIME NULL,
    reason TEXT NOT NULL,
    status ENUM('admitted', 'discharged') NOT NULL DEFAULT 'admitted',
    active_patient_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN status = 'admitted' THEN patient_id ELSE NULL END) STORED,
    active_bed_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN status = 'admitted' THEN bed_id ELSE NULL END) STORED,
    UNIQUE KEY uq_admissions_active_patient (active_patient_id),
    UNIQUE KEY uq_admissions_active_bed (active_bed_id),
    INDEX idx_admissions_status (status, admitted_at),
    CONSTRAINT fk_admissions_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_admissions_bed FOREIGN KEY (bed_id) REFERENCES beds(id),
    CONSTRAINT fk_admissions_user FOREIGN KEY (admitted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS lab_tests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NULL,
    test_name VARCHAR(160) NOT NULL,
    status ENUM('requested', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lab_tests_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_lab_tests_user FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lab_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lab_test_id BIGINT UNSIGNED NOT NULL UNIQUE,
    result TEXT NOT NULL,
    recorded_by BIGINT UNSIGNED NULL,
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lab_results_test FOREIGN KEY (lab_test_id) REFERENCES lab_tests(id),
    CONSTRAINT fk_lab_results_user FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

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
    patient_id BIGINT UNSIGNED NULL,
    patient_name VARCHAR(160) NULL,
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

CREATE TABLE IF NOT EXISTS medicines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    sku VARCHAR(60) NOT NULL UNIQUE,
    unit VARCHAR(40) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    reorder_level INT UNSIGNED NOT NULL DEFAULT 0,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    expires_on DATE NULL,
    INDEX idx_medicines_stock (quantity, reorder_level)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prescriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NULL,
    visit_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_prescriptions_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_prescriptions_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
    CONSTRAINT fk_prescriptions_visit FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prescription_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prescription_id BIGINT UNSIGNED NOT NULL,
    medicine_id BIGINT UNSIGNED NOT NULL,
    dosage VARCHAR(120) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    instructions VARCHAR(255) NULL,
    CONSTRAINT fk_prescription_items_prescription FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
    CONSTRAINT fk_prescription_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id)
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

CREATE TABLE IF NOT EXISTS invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    invoice_no VARCHAR(40) NOT NULL UNIQUE,
    status ENUM('unpaid', 'partial', 'paid', 'void') NOT NULL DEFAULT 'unpaid',
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_patient FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('cash', 'mobile_money', 'card', 'insurance', 'bank_transfer', 'other') NOT NULL DEFAULT 'cash',
    reference VARCHAR(120) NULL,
    received_by BIGINT UNSIGNED NULL,
    paid_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    CONSTRAINT fk_payments_user FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(160) NOT NULL,
    category VARCHAR(100) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    reorder_level INT UNSIGNED NOT NULL DEFAULT 0,
    unit VARCHAR(40) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL UNIQUE,
    contact_person VARCHAR(160) NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    address VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(40) NOT NULL UNIQUE,
    supplier_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL,
    status ENUM('ordered', 'received', 'cancelled') NOT NULL DEFAULT 'ordered',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    received_at DATETIME NULL,
    INDEX idx_purchase_status (status, created_at),
    CONSTRAINT fk_purchase_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_purchase_item FOREIGN KEY (inventory_item_id) REFERENCES inventory(id),
    CONSTRAINT fk_purchase_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipment_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_tag VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    category VARCHAR(100) NOT NULL,
    location VARCHAR(160) NULL,
    status ENUM('available', 'maintenance', 'retired') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipment_maintenance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    scheduled_on DATE NOT NULL,
    maintenance_type VARCHAR(120) NOT NULL,
    details VARCHAR(1000) NULL,
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    recorded_by BIGINT UNSIGNED NOT NULL,
    completed_at DATETIME NULL,
    INDEX idx_maintenance_date (scheduled_on, status),
    CONSTRAINT fk_maintenance_asset FOREIGN KEY (asset_id) REFERENCES equipment_assets(id),
    CONSTRAINT fk_maintenance_user FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ambulance_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NULL,
    requested_by BIGINT UNSIGNED NULL,
    driver_id BIGINT UNSIGNED NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    transport_notes VARCHAR(500) NULL,
    status ENUM('requested', 'assigned', 'en_route', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ambulance_status (status, requested_at),
    CONSTRAINT fk_ambulance_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    CONSTRAINT fk_ambulance_requester FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_ambulance_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff_attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    status ENUM('present', 'absent', 'leave') NOT NULL,
    clock_in TIME NULL,
    clock_out TIME NULL,
    note VARCHAR(500) NULL,
    recorded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_attendance_user_date (user_id, work_date),
    INDEX idx_attendance_date_status (work_date, status),
    CONSTRAINT fk_attendance_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_attendance_recorder FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    pay_period DATE NOT NULL,
    gross_amount DECIMAL(12,2) NOT NULL,
    deductions DECIMAL(12,2) NOT NULL DEFAULT 0,
    net_amount DECIMAL(12,2) NOT NULL,
    status ENUM('prepared', 'paid') NOT NULL DEFAULT 'prepared',
    recorded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payroll_user_period (user_id, pay_period),
    CONSTRAINT fk_payroll_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_payroll_recorder FOREIGN KEY (recorded_by) REFERENCES users(id)
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

INSERT IGNORE INTO schema_migrations (version) VALUES
    ('2026_10_27_001_rbac_and_roles'),
    ('2026_10_27_002_user_work_logs'),
    ('2026_10_27_003_patient_services'),
    ('2026_10_27_004_patient_service_access'),
    ('2026_10_27_005_staff_patient_service_access'),
    ('2026_10_27_006_hospital_departments'),
    ('2026_10_27_007_operational_workflows'),
    ('2026_10_27_008_discharge_summaries'),
    ('2026_10_27_009_ambulance_dispatch'),
    ('2026_10_27_010_staff_attendance_payroll'),
    ('2026_10_27_011_emergency_intake'),
    ('2026_10_27_012_patient_portal_imaging'),
    ('2026_10_27_013_procurement_equipment');
