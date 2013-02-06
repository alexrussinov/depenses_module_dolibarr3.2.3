create table llx_paiementdepenses(
rowid int(11) not null auto_increment, 
tms timestamp DEFAULT CURRENT_TIMESTAMP not null, 
datec datetime, 
datep datetime, 
amount real, 
fk_user_author int(11), 
fk_paiement int(11) not null, 
num_paiement varchar(50), 
note text, 
fk_bank int(11) not null, 
statut smallint not null, 
constraint pk_paiementdepenses primary key (rowid)
)ENGINE=innodb;