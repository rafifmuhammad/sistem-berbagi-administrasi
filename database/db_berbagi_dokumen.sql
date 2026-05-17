CREATE DATABASE IF NOT EXISTS db_berbagi_dokumen
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_berbagi_dokumen;

CREATE TABLE IF NOT EXISTS tb_user (
  id_user VARCHAR(30) PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  nama VARCHAR(150) NOT NULL,
  tanggal_lahir DATE NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tb_kategori (
  id_category VARCHAR(30) PRIMARY KEY,
  kode_kategori VARCHAR(20) NOT NULL UNIQUE,
  nama_kategori VARCHAR(150) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tb_dokumen (
  id_document VARCHAR(30) PRIMARY KEY,
  id_category VARCHAR(30) NOT NULL,
  id_user VARCHAR(30) NULL,
  nama_dokumen VARCHAR(200) NOT NULL,
  keterangan TEXT NULL,
  tanggal_upload DATE NOT NULL,
  file VARCHAR(255) NOT NULL,
  preview_file VARCHAR(255) NULL,
  status ENUM('menunggu', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu',
  rejection_reason TEXT NULL,
  approved_by VARCHAR(30) NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_document_category (id_category),
  INDEX idx_document_user (id_user),
  INDEX idx_document_status (status),
  INDEX idx_document_approved_by (approved_by),
  INDEX idx_document_tanggal_upload (tanggal_upload),

  CONSTRAINT fk_document_category
    FOREIGN KEY (id_category)
    REFERENCES tb_kategori (id_category)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,

  CONSTRAINT fk_document_user
    FOREIGN KEY (id_user)
    REFERENCES tb_user (id_user)
    ON UPDATE CASCADE
    ON DELETE SET NULL,

  CONSTRAINT fk_document_approved_by
    FOREIGN KEY (approved_by)
    REFERENCES tb_user (id_user)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tb_user (id_user, email, nama, tanggal_lahir, password, role)
VALUES (
  'USR-ADMIN',
  'admin@outline.local',
  'Admin Outline',
  NULL,
  '$2y$10$1sVjUOw8mx8.u7ziSj70KORpjTv8B1Dk1HqOfrN2qTHzAnhIqIJLS',
  'admin'
)
ON DUPLICATE KEY UPDATE
  nama = VALUES(nama),
  password = VALUES(password),
  role = VALUES(role);

INSERT INTO tb_kategori (id_category, kode_kategori, nama_kategori) VALUES
  ('CAT-ADM', 'ADM', 'Administrasi Umum'),
  ('CAT-KEU', 'KEU', 'Keuangan'),
  ('CAT-PEG', 'PEG', 'Kepegawaian'),
  ('CAT-AKD', 'AKD', 'Akademik')
ON DUPLICATE KEY UPDATE nama_kategori = VALUES(nama_kategori);
