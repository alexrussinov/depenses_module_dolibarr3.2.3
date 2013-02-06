 alter table llx_depenses add column
 fk_soc int(10) not null,
 amount float,
 paye smallint not null,
 fk_statut smallint not null,
 fk_user_author int(10),
 date_lim_reglement date 
 ENGINE=innodb;