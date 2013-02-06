 alter table llx_depenses add column fk_soc int(10) not null;
 alter table llx_depenses add column amount float;
 alter table llx_depenses add column paye smallint not null default 0;
 alter table llx_depenses add column fk_statut smallint not null default 1;
 alter table llx_depenses add column fk_user_author int(10);
 alter table llx_depenses add column date_lim_reglement date; 
