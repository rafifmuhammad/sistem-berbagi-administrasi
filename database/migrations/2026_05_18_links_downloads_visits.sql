USE db_berbagi_dokumen;

ALTER TABLE tb_dokumen
  MODIFY file VARCHAR(255) NULL;

ALTER TABLE tb_dokumen
  ADD COLUMN link_url VARCHAR(2048) NULL AFTER preview_file;

ALTER TABLE tb_dokumen
  ADD COLUMN download_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER link_url;

ALTER TABLE tb_dokumen
  ADD INDEX idx_document_download_count (download_count);

CREATE TABLE tb_kunjungan (
  id_visit BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user VARCHAR(30) NULL,
  session_id VARCHAR(128) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  browser VARCHAR(80) NULL,
  operating_system VARCHAR(80) NULL,
  device_type VARCHAR(30) NULL,
  page_url VARCHAR(2048) NULL,
  referrer VARCHAR(2048) NULL,
  visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_visit_user (id_user),
  INDEX idx_visit_session (session_id),
  INDEX idx_visit_visited_at (visited_at),
  INDEX idx_visit_device_type (device_type),

  CONSTRAINT fk_visit_user
    FOREIGN KEY (id_user)
    REFERENCES tb_user (id_user)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
