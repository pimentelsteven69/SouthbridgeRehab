CREATE DATABASE IF NOT EXISTS southbridge_loto
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE southbridge_loto;

CREATE TABLE IF NOT EXISTS loto_entries (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  entry_date DATE NULL,
  time_out TIME NULL,
  equipment TEXT,
  location VARCHAR(255),
  hazard TEXT,
  lock_tag VARCHAR(255),
  employee VARCHAR(255),
  verified VARCHAR(10),
  time_in TIME NULL,
  work_performed TEXT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS loto_settings (
  id TINYINT UNSIGNED NOT NULL,
  month_year VARCHAR(7) DEFAULT '',
  area VARCHAR(255) DEFAULT '',
  supervisor VARCHAR(255) DEFAULT '',
  review_signature VARCHAR(255) DEFAULT '',
  date_reviewed DATE NULL,
  additional_notes TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;
