<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_so_pj extends ci_model{
    private $tbl_name = "tbl_so_pj";
    private $columns = array();
    private $id_pk_so_pj;
    private $id_fk_stock_opname;
    private $id_fk_emp;
    private $so_pj_create_date;
    private $so_pj_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->so_pj_create_date = date("y-m-d h:i:s");
        $this->so_pj_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "drop table if exists tbl_so_pj;
        create table tbl_so_pj(
            id_pk_so_pj int primary key auto_increment,
            id_fk_stock_opname int,
            id_fk_emp int,
            so_pj_create_date datetime,
            so_pj_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_so_pj_log;
        create table tbl_so_pj_log(
            id_pk_so_pj_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_so_pj int,
            id_fk_stock_opname int,
            id_fk_emp int,
            so_pj_create_date datetime,
            so_pj_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_so_pj;
        delimiter $$
        create trigger trg_after_insert_so_pj
        after insert on tbl_so_pj
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.so_pj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.so_pj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_so_pj_log(executed_function,id_pk_so_pj,id_fk_stock_opname,id_fk_emp,so_pj_create_date,so_pj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_so_pj,new.id_fk_stock_opname,new.id_fk_emp,new.so_pj_create_date,new.so_pj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_so_pj;
        delimiter $$
        create trigger trg_after_update_so_pj
        after update on tbl_so_pj
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.so_pj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.so_pj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_so_pj_log(executed_function,id_pk_so_pj,id_fk_stock_opname,id_fk_emp,so_pj_create_date,so_pj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_so_pj,new.id_fk_stock_opname,new.id_fk_emp,new.so_pj_create_date,new.so_pj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_emp" => $this->id_fk_emp,
                "so_pj_create_date" => $this->so_pj_create_date,
                "so_pj_last_modified" => $this->so_pj_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
            );
            return insertrow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_so_pj" => $this->id_pk_so_pj
            );
            $data = array(
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_emp" => $this->id_fk_emp,
                "so_pj_last_modified" => $this->so_pj_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_so_pj" => $this->id_pk_so_pj
            );
            $data = array(
                "so_pj_last_modified" => $this->so_pj_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_stock_opname == ""){
            return false;
        }
        if($this->id_fk_emp == ""){
            return false;
        }
        if($this->so_pj_create_date == ""){
            return false;
        }
        if($this->so_pj_last_modified == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_update(){
        if($this->id_pk_so_pj != ""){
            return false;
        }
        if($this->id_fk_stock_opname != ""){
            return false;
        }
        if($this->id_fk_emp != ""){
            return false;
        }
        if($this->so_pj_last_modified != ""){
            return false;
        }
        if($this->id_last_modified != ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_so_pj != ""){
            return false;
        }
        if($this->so_pj_last_modified != ""){
            return false;
        }
        if($this->id_last_modified != ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_stock_opname,$id_fk_emp){
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_emp($id_fk_emp)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_so_pj,$id_fk_stock_opname,$id_fk_emp){
        if(!$this->set_id_pk_so_pj($id_pk_so_pj)){
            return false;
        }
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_emp($id_fk_emp)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_so_pj){
        if(!$this->set_id_pk_so_pj($id_pk_so_pj)){
            return false;
        }

        return true;
    }
    public function set_id_pk_so_pj($id_pk_so_pj){
        if($id_pk_so_pj != ""){
            $this->id_pk_so_pj = $id_pk_so_pj;
            return true;
        }
        return false;
    }
    public function set_id_fk_stock_opname($id_fk_stock_opname){
        if($id_fk_stock_opname != ""){
            $this->id_fk_stock_opname = $id_fk_stock_opname;
            return true;
        }
        return false;
    }
    public function set_id_fk_emp($id_fk_emp){
        if($id_fk_emp != ""){
            $this->id_fk_emp = $id_fk_emp;
            return true;
        }
        return false;
    }
    public function get_id_pk_so_pj(){
        return $this->id_pk_so_pj;
    }
    public function get_id_fk_stock_opname(){
        return $this->id_fk_stock_opname;
    }
    public function get_id_fk_emp(){
        return $this->id_fk_emp;
    }
}