create table llx_depenses_tvarate 
(
  rowid int unsigned not null auto_increment, 
  tva19_6 float, 
  tva7_0 float, 
  tva5_5 float,
  tva2_1 float,  
  tva0 float,
  fk_rowid int unsigned not null, 
  constraint pk_depenses_tvarate primary key (rowid),
  constraint fk_depenses_tvarate foreign key (fk_rowid)
  references llx_depenses (rowid) on delete cascade
 )ENGINE=innodb;
 
 