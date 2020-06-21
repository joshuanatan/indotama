<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");

class M_hak_akses extends ci_model{
    private $tbl_name = "tbl_hak_akses";
    private $columns = array();
    private $id_pk_hak_akses;
    private $id_fk_jabatan;
    private $id_fk_menu;
    private $hak_akses_status;
    private $hak_akses_create_date;
    private $hak_akses_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->hak_akses_create_date = date("y-m-d h:i:s");
        $this->hak_akses_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "drop table if exists tbl_hak_akses;
        create table tbl_hak_akses(
            id_pk_hak_akses int primary key auto_increment,
            id_fk_jabatan int,
            id_fk_menu int,
            hak_akses_status varchar(15),    
            hak_akses_create_date datetime,
            hak_akses_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_hak_akses_log;
        create table tbl_hak_akses_log(
            id_pk_hak_akses_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_hak_akses int,
            id_fk_jabatan int,
            id_fk_menu int,
            hak_akses_status varchar(15),    
            hak_akses_create_date datetime,
            hak_akses_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_hak_akses;
        delimiter $$
        create trigger trg_after_insert_hak_akses
        after insert on tbl_hak_akses
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.hak_akses_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.hak_akses_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_hak_akses_log(executed_function,id_pk_hak_akses,id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_hak_akses,new.id_fk_jabatan,new.id_fk_menu,new.hak_akses_status,new.hak_akses_create_date,new.hak_akses_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_hak_akses;
        delimiter $$
        create trigger trg_after_update_hak_akses
        after update on tbl_hak_akses
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.hak_akses_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.hak_akses_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_hak_akses_log(executed_function,id_pk_hak_akses,id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_hak_akses,new.id_fk_jabatan,new.id_fk_menu,new.hak_akses_status,new.hak_akses_create_date,new.hak_akses_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
    }
    public function insert(){
        $where = array(
            "id_fk_jabatan" => $this->id_fk_jabatan,
            "id_fk_menu" => $this->id_fk_menu,
        );
        if(isexistsintable($this->tbl_name,$where)){
            $data = array(
                "id_fk_jabatan" => $this->id_fk_jabatan,
                "id_fk_menu" => $this->id_fk_menu,
                "hak_akses_create_date" => $this->hak_akses_create_date,
                "hak_akses_last_modified" => $this->hak_akses_last_modified,
                "hak_akses_create_date" => $this->hak_akses_create_date,
                "id_last_modified" => $this->id_last_modified,
            );
            return insertrow($this->tbl_name,$data);
        }
        else{
            return false;
        }
    }
    public function update(){
        $where = array(
            "id_pk_hak_akses !=" => $this->id_pk_hak_akses,
            "id_fk_jabatan" => $this->id_fk_jabatan,
            "id_fk_menu" => $this->id_fk_menu,
        );
        if(isexistsintable($this->tbl_name,$where)){
            $where = array(
                "id_pk_hak_akses" => $this->id_pk_hak_akses,
            );
            $data = array(
                "id_fk_jabatan" => $this->id_fk_jabatan,
                "id_fk_menu" => $this->id_fk_menu,
                "hak_akses_last_modified" => $this->hak_akses_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function remove_hak_akses(){
        $where = array(
            "id_fk_jabatan" => $this->id_fk_jabatan,
            "id_fk_menu" => $this->id_fk_menu
        );
        $data = array(
            "hak_akses_status" => "nonaktif"
        );
        updaterow("tbl_hak_akses",$data,$where);
        return true;
    }
    public function list_role_hak_akses(){
        $sql = "
        select id_pk_hak_akses,id_fk_jabatan,id_fk_menu,hak_akses_create_date,hak_akses_status,hak_akses_last_modified,menu_name,menu_display,menu_icon
        from ".$this->tbl_name."
        inner join mstr_jabatan on mstr_jabatan.id_pk_jabatan = ".$this->tbl_name.".id_fk_jabatan
        inner join mstr_menu on mstr_menu.id_pk_menu = ".$this->tbl_name.".id_fk_menu
        where id_fk_jabatan = ?
        ";
        $args = array(
            $this->id_fk_jabatan
        );
        return executequery($sql,$args);
    }
    public function reset_hak_akses(){
        if($this->id_fk_jabatan != ""){
            $where = array(
                "id_fk_jabatan" => $this->id_fk_jabatan
            );
        }
        else if($this->id_fk_menu != ""){
            $where = array(
                "id_fk_menu" => $this->id_fk_menu
            );
        }
        $data = array(
            "hak_akses_status" => "nonaktif"
        );
        updaterow("tbl_hak_akses",$data,$where);
    }
    public function activate_hak_akses(){
        $where = array(
            "id_fk_jabatan" => $this->id_fk_jabatan,
            "id_fk_menu" => $this->id_fk_menu
        );
        $data = array(
            "hak_akses_status" => "aktif"
        );
        updaterow("tbl_hak_akses",$data,$where);
        return true;
    }
    public function check_insert(){
        if($this->id_fk_jabatan == ""){
            return false;
        }
        if($this->id_fk_menu == ""){
            return false;
        }
        if($this->hak_akses_create_date == ""){
            return false;
        }
        if($this->hak_akses_last_modified == ""){
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
        if($this->id_pk_hak_akses == ""){
            return false;
        }
        if($this->id_fk_jabatan == ""){
            return false;
        }
        if($this->id_fk_menu == ""){
            return false;
        }
        if($this->hak_akses_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_hak_akses == ""){
            return false;
        }
        if($this->hak_akses_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_jabatan,$id_fk_menu){
        if(!$this->set_id_fk_jabatan($id_fk_jabatan)){
            return false;
        }
        if(!$this->set_id_fk_menu($id_fk_menu)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_hak_akses,$id_fk_jabatan,$id_fk_menu){
        if(!$this->set_id_pk_hak_akses($id_pk_hak_akses)){
            return false;
        }
        if(!$this->set_id_fk_jabatan($id_fk_jabatan)){
            return false;
        }
        if(!$this->set_id_fk_menu($id_fk_menu)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_hak_akses){
        if(!$this->set_id_pk_hak_akses($id_pk_hak_akses)){
            return false;
        }
        return true;
    }
    public function get_id_pk_hak_akses(){
        return $this->id_pk_hak_akses;
    }
    public function get_id_fk_jabatan(){
        return $this->id_fk_jabatan;
    }
    public function get_id_fk_menu(){
        return $this->id_fk_menu;
    }
    public function get_hak_akses_create_date(){
        return $this->hak_akses_create_date;
    }
    public function get_hak_akses_last_modified(){
        return $this->hak_akses_last_modified;
    }
    public function get_id_create_data(){
        return $this->id_create_data;
    }
    public function get_id_last_modified(){
        return $this->id_last_modified;
    }
    public function set_id_pk_hak_akses($id_pk_hak_akses){
        if($id_pk_hak_akses != ""){
            $this->id_pk_hak_akses = $id_pk_hak_akses;
            return true;
        }
        else return false;
    }
    public function set_id_fk_jabatan($id_fk_jabatan){
        if($id_fk_jabatan != ""){
            $this->id_fk_jabatan = $id_fk_jabatan;
            return true;
        }
        else return false;
    }
    public function set_id_fk_menu($id_fk_menu){
        if($id_fk_menu != ""){
            $this->id_fk_menu = $id_fk_menu;
            return true;
        }
        else return false;
    }
    public function set_hak_akses_create_date($hak_akses_create_date){
        if($hak_akses_create_date != ""){
            $this->hak_akses_create_date = $hak_akses_create_date;
            return true;
        }
        else return false;
    }
    public function set_hak_akses_last_modified($hak_akses_last_modified){
        if($hak_akses_last_modified != ""){
            $this->hak_akses_last_modified = $hak_akses_last_modified;
            return true;
        }
        else return false;
    }
    public function set_id_create_data($id_create_data){
        if($id_create_data != ""){
            $this->id_create_data = $id_create_data;
            return true;
        }
        else return false;
    }
    public function set_id_last_modified($id_last_modified){
        if($id_last_modified != ""){
            $this->id_last_modified = $id_last_modified;
            return true;
        }
        else return false;
    }
}