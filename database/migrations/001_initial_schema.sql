CREATE DATABASE IF NOT EXISTS government_schemes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE government_schemes;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  accepted_terms_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE user_profiles (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  date_of_birth DATE NULL,
  annual_income DECIMAL(12,2) NULL,
  state VARCHAR(80) NULL,
  occupation VARCHAR(80) NULL,
  education VARCHAR(80) NULL,
  gender VARCHAR(40) NULL,
  social_category VARCHAR(40) NULL,
  residence_type ENUM('rural','urban') NULL,
  disability_status TINYINT(1) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_profile_income CHECK (annual_income IS NULL OR annual_income >= 0)
) ENGINE=InnoDB;

CREATE TABLE categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(120) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE schemes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  summary VARCHAR(500) NOT NULL,
  description TEXT NOT NULL,
  benefit_description TEXT NOT NULL,
  application_instructions TEXT NULL,
  status ENUM('draft','active','inactive','archived') NOT NULL DEFAULT 'draft',
  verification_status ENUM('demo','unverified','pending_review','verified') NOT NULL DEFAULT 'demo',
  last_verified_at DATE NULL,
  is_demo TINYINT(1) NOT NULL DEFAULT 1,
  rule_version INT UNSIGNED NOT NULL DEFAULT 1,
  rule_group_operator ENUM('AND','OR') NOT NULL DEFAULT 'AND',
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_schemes_catalogue (status, verification_status),
  FULLTEXT KEY ft_schemes_search (title, summary, description),
  CONSTRAINT fk_schemes_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_schemes_updater FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE scheme_categories (
  scheme_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (scheme_id, category_id),
  CONSTRAINT fk_sc_scheme FOREIGN KEY (scheme_id) REFERENCES schemes(id) ON DELETE CASCADE,
  CONSTRAINT fk_sc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE eligibility_rule_groups (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  scheme_id BIGINT UNSIGNED NOT NULL,
  group_key VARCHAR(40) NOT NULL,
  label VARCHAR(120) NOT NULL,
  condition_operator ENUM('AND','OR') NOT NULL DEFAULT 'AND',
  position INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_group_key (scheme_id, group_key),
  CONSTRAINT fk_groups_scheme FOREIGN KEY (scheme_id) REFERENCES schemes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE scheme_eligibility_rules (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  scheme_id BIGINT UNSIGNED NOT NULL,
  group_id BIGINT UNSIGNED NOT NULL,
  attribute_key VARCHAR(50) NOT NULL,
  operator VARCHAR(30) NOT NULL,
  expected_value VARCHAR(500) NULL,
  description VARCHAR(300) NOT NULL,
  mandatory TINYINT(1) NOT NULL DEFAULT 1,
  applicability_attribute VARCHAR(50) NULL,
  applicability_operator VARCHAR(30) NULL,
  applicability_value VARCHAR(500) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  position INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rules_scheme FOREIGN KEY (scheme_id) REFERENCES schemes(id) ON DELETE CASCADE,
  CONSTRAINT fk_rules_group FOREIGN KEY (group_id) REFERENCES eligibility_rule_groups(id) ON DELETE CASCADE,
  INDEX idx_rules_scheme (scheme_id, active, position)
) ENGINE=InnoDB;

CREATE TABLE scheme_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  scheme_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  description VARCHAR(500) NULL,
  mandatory TINYINT(1) NOT NULL DEFAULT 1,
  position INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_documents_scheme FOREIGN KEY (scheme_id) REFERENCES schemes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE scheme_sources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  scheme_id BIGINT UNSIGNED NOT NULL,
  source_name VARCHAR(180) NOT NULL,
  source_url VARCHAR(1000) NOT NULL,
  verification_status ENUM('demo','unverified','pending_review','verified') NOT NULL DEFAULT 'unverified',
  verified_at DATE NULL,
  reviewed_by BIGINT UNSIGNED NULL,
  CONSTRAINT fk_sources_scheme FOREIGN KEY (scheme_id) REFERENCES schemes(id) ON DELETE CASCADE,
  CONSTRAINT fk_sources_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE bookmarks (
  user_id BIGINT UNSIGNED NOT NULL,
  scheme_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, scheme_id),
  CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookmarks_scheme FOREIGN KEY (scheme_id) REFERENCES schemes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE eligibility_assessments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  scheme_id BIGINT UNSIGNED NOT NULL,
  assessed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  overall_status ENUM('potentially_eligible','more_information','conditions_not_met','unable_to_assess') NOT NULL,
  rule_version INT UNSIGNED NOT NULL,
  profile_snapshot JSON NOT NULL,
  CONSTRAINT fk_assessments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_assessments_scheme FOREIGN KEY (scheme_id) REFERENCES schemes(id) ON DELETE RESTRICT,
  INDEX idx_assessment_history (user_id, assessed_at)
) ENGINE=InnoDB;

CREATE TABLE eligibility_assessment_results (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  rule_id BIGINT UNSIGNED NULL,
  condition_description VARCHAR(300) NOT NULL,
  result_status ENUM('PASS','FAIL','UNKNOWN','NOT_APPLICABLE','INVALID') NOT NULL,
  supplied_value VARCHAR(500) NULL,
  expected_value VARCHAR(500) NULL,
  explanation VARCHAR(1000) NOT NULL,
  CONSTRAINT fk_results_assessment FOREIGN KEY (assessment_id) REFERENCES eligibility_assessments(id) ON DELETE CASCADE,
  CONSTRAINT fk_results_rule FOREIGN KEY (rule_id) REFERENCES scheme_eligibility_rules(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id BIGINT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_recent (created_at),
  CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email_hash CHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_rate (email_hash, attempted_at)
) ENGINE=InnoDB;
