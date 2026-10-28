-- Seed the general hospital department structure without removing local departments.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

INSERT IGNORE INTO departments (name, description) VALUES
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
INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_006_hospital_departments');
