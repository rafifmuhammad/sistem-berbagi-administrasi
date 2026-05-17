USE db_berbagi_dokumen;

ALTER TABLE tb_dokumen
  ADD COLUMN rejection_reason TEXT NULL AFTER status;
