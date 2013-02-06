create table llx_paiementdepenses_facturedep
(
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  fk_paiementfourn INTEGER DEFAULT NULL,
  fk_facturefourn  INTEGER DEFAULT NULL,
  amount double(24,8) DEFAULT 0
)ENGINE=innodb;