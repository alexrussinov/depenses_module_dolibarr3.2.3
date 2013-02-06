create table llx_depenses 
(
 rowid int unsigned not null auto_increment, 
 type varchar(20), 
 societe varchar(30), 
 ref varchar(30), 
 total_ht float, 
 total_ttc float, 
 total_tva float, 
 tvarate float, 
 payment varchar(10), 
 datec datetime, 
 dated date, 
 note text, 
 constraint pk_depenses primary key (rowid)
 )ENGINE=innodb;