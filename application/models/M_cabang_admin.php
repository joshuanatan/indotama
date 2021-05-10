<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");

class M_cabang_admin extends ci_model{
    private $tbl_name = "tbl_cabang_admin";
    private $columns = array();
    private $id_pk_cabang_admin;
    private $id_fk_cabang;
    private $id_fk_user;
    private $cabang_admin_status;
    private $cabang_admin_create_date;
    private $cabang_admin_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->columns = array();
        $this->set_column("user_name","user name","required");
        $this->set_column("user_email","email","required");
        $this->set_column("cabang_admin_status","status","required");
        $this->set_column("cabang_admin_last_modified","last modified","required");
        $this->cabang_admin_create_date = date("y-m-d h:i:s");
        $this->cabang_admin_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists tbl_cabang_admin;
        create table tbl_cabang_admin(
            id_pk_cabang_admin int primary key auto_increment,
            id_fk_cabang int,
            id_fk_user int,
            cabang_admin_status varchar(15),
            cabang_admin_create_date datetime,
            cabang_admin_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_cabang_admin_log;
        create table tbl_cabang_admin_log(
            id_pk_cabang_admin_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_cabang_admin int,
            id_fk_cabang int,
            id_fk_user int,
            cabang_admin_status varchar(15),
            cabang_admin_create_date datetime,
            cabang_admin_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_cabang_admin;
        delimiter $$
        create trigger trg_after_insert_cabang_admin
        after insert on tbl_cabang_admin
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.cabang_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_cabang_admin_log(executed_function,id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_create_date,cabang_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_cabang_admin,new.id_fk_cabang,new.id_fk_user,new.cabang_admin_status,new.cabang_admin_create_date,new.cabang_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_cabang_admin;
        delimiter $$
        create trigger trg_after_update_cabang_admin
        after update on tbl_cabang_admin
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.cabang_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_cabang_admin_log(executed_function,id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_create_date,cabang_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_cabang_admin,new.id_fk_cabang,new.id_fk_user,new.cabang_admin_status,new.cabang_admin_create_date,new.cabang_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function columns(){
        return $this->columns;
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = 20){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_cabang_admin like '%".$search_key."%' or
                id_fk_cabang like '%".$search_key."%' or
                id_fk_user like '%".$search_key."%' or
                cabang_admin_status like '%".$search_key."%' or
                cabang_admin_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_last_modified,user_name,user_email
        from ".$this->tbl_name." 
        inner join mstr_user on mstr_user.id_pk_user = ".$this->tbl_name.".id_fk_user
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        where cabang_admin_status = ? and id_fk_cabang = ? and user_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif",$this->id_fk_cabang,"aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_cabang_admin
        from ".$this->tbl_name." 
        inner join mstr_user on mstr_user.id_pk_user = ".$this->tbl_name.".id_fk_user
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        where cabang_admin_status = ? and id_fk_cabang = ? and user_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function set_cabang_admin_columns(){
        $this->columns = array();
        $this->set_column("toko_nama","toko",true);
        $this->set_column("cabang_daerah","daerah",false);
        $this->set_column("cabang_notelp","no telp",false);
        $this->set_column("cabang_alamat","alamat",false);
        $this->set_column("cabang_status","status",false);
        $this->set_column("cabang_last_modified","last modified",false);
    }
    public function list_cabang_admin($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = 20){
        $this->set_cabang_admin_columns();
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_cabang like '%".$search_key."%' or 
                cabang_daerah like '%".$search_key."%' or 
                cabang_notelp like '%".$search_key."%' or 
                cabang_alamat like '%".$search_key."%' or 
                cabang_status like '%".$search_key."%' or 
                cabang_create_date like '%".$search_key."%' or 
                cabang_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_cabang,toko_nama,cabang_daerah,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_pk_toko
        from ".$this->tbl_name." 
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where cabang_status = ? and id_fk_user = ? and cabang_admin_status = ? and toko_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif",$this->id_fk_user,"aktif","aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_cabang
        from ".$this->tbl_name." 
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where cabang_status = ? and id_fk_user = ? and cabang_admin_status = ? and toko_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_cabang" => $this->id_fk_cabang,
                "id_fk_user" => $this->id_fk_user,
                "cabang_admin_status" => $this->cabang_admin_status,
                "cabang_admin_create_date" => $this->cabang_admin_create_date,
                "cabang_admin_last_modified" => $this->cabang_admin_last_modified,
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
                "id_pk_cabang_admin" => $this->id_pk_cabang_admin
            );
            $data = array(
                "id_fk_cabang" => $this->id_fk_cabang,
                "id_fk_user" => $this->id_fk_user,
                "cabang_admin_last_modified" => $this->cabang_admin_last_modified,
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
                "id_pk_cabang_admin" => $this->id_pk_cabang_admin
            );
            $data = array(
                "cabang_admin_status" => "nonaktif",
                "cabang_admin_last_modified" => $this->cabang_admin_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->id_fk_user == ""){
            return false;
        }
        if($this->cabang_admin_status == ""){
            return false;
        }
        if($this->cabang_admin_create_date == ""){
            return false;
        }
        if($this->cabang_admin_last_modified == ""){
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
        if($this->id_pk_cabang_admin == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->id_fk_user == ""){
            return false;
        }
        if($this->cabang_admin_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){

        if($this->id_pk_cabang_admin == ""){
            return false;
        }
        if($this->cabang_admin_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_cabang,$id_fk_user,$cabang_admin_status){
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        if(!$this->set_id_fk_user($id_fk_user)){
            return false;
        }
        if(!$this->set_cabang_admin_status($cabang_admin_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_cabang_admin,$id_fk_cabang,$id_fk_user){
        if(!$this->set_id_pk_cabang_admin($id_pk_cabang_admin)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        if(!$this->set_id_fk_user($id_fk_user)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_cabang_admin){
        if(!$this->set_id_pk_cabang_admin($id_pk_cabang_admin)){
            return false;
        }
        return true;
    }
    public function get_id_pk_cabang_admin(){
        return $this->id_pk_cabang_admin;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
    public function get_id_fk_user(){
        return $this->id_fk_user;
    }
    public function get_cabang_admin_status(){
        return $this->cabang_admin_status;
    }
    public function set_id_pk_cabang_admin($id_pk_cabang_admin){
        if($id_pk_cabang_admin != ""){
            $this->id_pk_cabang_admin = $id_pk_cabang_admin;
            return true;
        }
        return false;
    }
    public function set_id_fk_cabang($id_fk_cabang){
        if($id_fk_cabang != ""){
            $this->id_fk_cabang = $id_fk_cabang;
            return true;
        }
        return false;
    }
    public function set_id_fk_user($id_fk_user){
        if($id_fk_user != ""){
            $this->id_fk_user = $id_fk_user;
            return true;
        }
        return false;
    }
    public function set_cabang_admin_status($cabang_admin_status){
        if($cabang_admin_status != ""){
            $this->cabang_admin_status = $cabang_admin_status;
            return true;
        }
        return false;
    }
}