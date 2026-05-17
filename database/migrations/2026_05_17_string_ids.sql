USE db_berbagi_dokumen;

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE tb_dokumen DROP FOREIGN KEY fk_document_category;
ALTER TABLE tb_dokumen DROP FOREIGN KEY fk_document_user;
ALTER TABLE tb_dokumen DROP FOREIGN KEY fk_document_approved_by;

ALTER TABLE tb_user MODIFY id_user VARCHAR(30) NOT NULL;
ALTER TABLE tb_kategori MODIFY id_category VARCHAR(30) NOT NULL;
ALTER TABLE tb_dokumen MODIFY id_category VARCHAR(30) NOT NULL;
ALTER TABLE tb_dokumen MODIFY id_user VARCHAR(30) NULL;
ALTER TABLE tb_dokumen MODIFY approved_by VARCHAR(30) NULL;

ALTER TABLE tb_dokumen
  ADD CONSTRAINT fk_document_category
    FOREIGN KEY (id_category)
    REFERENCES tb_kategori (id_category)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  ADD CONSTRAINT fk_document_user
    FOREIGN KEY (id_user)
    REFERENCES tb_user (id_user)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  ADD CONSTRAINT fk_document_approved_by
    FOREIGN KEY (approved_by)
    REFERENCES tb_user (id_user)
    ON UPDATE CASCADE
    ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;
