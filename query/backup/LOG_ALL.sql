
drop table if exists log_all;
create table log_all(
	id_log_all int primary key auto_increment,
    id_user int,
    log_date datetime,
    log varchar(1000),
    
);
drop procedure if exists insert_log_all;
delimiter $$
create procedure insert_log_all(in id_user int, in log_date datetime, in log_text varchar(1000), out id_log_all int)
begin
	insert into log_all(id_user,log_date,log) values(id_user,log_date,log_text);
    select last_insert_id() into id_log_all ;
end$$
delimiter ;