<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_toko extends ci_model{
    private $tbl_name = "mstr_toko";
    private $columns = array();
    private $id_pk_toko;
    private $toko_nama;
    private $toko_kode;
    private $toko_status;
    private $toko_create_date;
    private $toko_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->set_column("toko_nama","nama toko",true);
        $this->set_column("toko_kode","kode toko",false);
        $this->set_column("toko_status","status toko",false);
        $this->set_column("toko_last_modified","last modified",false);
        
        $this->toko_create_date = date("y-m-d h:i:s");
        $this->toko_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;  
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
    public function install(){
        $sql = "drop table if exists mstr_toko;
        create table mstr_toko(
            id_pk_toko int primary key auto_increment,
            toko_nama varchar(100),
            toko_kode varchar(20),
            toko_status varchar(15),
            toko_create_date datetime,
            toko_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_toko_log;
        create table mstr_toko_log(
            id_pk_toko_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_toko int,
            toko_nama varchar(100),
            toko_kode varchar(20),
            toko_status varchar(15),
            toko_create_date datetime,
            toko_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_toko;
        delimiter $$
        create trigger trg_after_insert_toko
        after insert on mstr_toko
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_nama,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_toko,new.toko_nama,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_toko;
        delimiter $$
        create trigger trg_after_update_toko
        after update on mstr_toko
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_nama,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_toko,new.toko_nama,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_toko like '%".$search_key."%' or
                toko_nama like '%".$search_key."%' or
                toko_kode like '%".$search_key."%' or
                toko_status like '%".$search_key."%' or
                toko_create_date like '%".$search_key."%' or
                toko_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_toko,toko_nama,toko_kode,toko_status,toko_create_date,toko_last_modified
        from ".$this->tbl_name." 
        where toko_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_toko
        from ".$this->tbl_name." 
        where toko_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_id(){
        $where = array(
            "id_pk_toko" => $this->id_pk_toko
        );
        $field = array(
            "id_pk_toko","toko_nama","toko_kode","toko_status","toko_create_date","toko_last_modified","id_create_data","id_last_modified",
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "toko_nama" => $this->toko_nama, 
                "toko_kode" => $this->toko_kode, 
                "toko_status" => $this->toko_status, 
                "toko_create_date" => $this->toko_create_date, 
                "toko_last_modified" => $this->toko_last_modified, 
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
                "id_pk_toko" => $this->id_pk_toko
            );
            $data = array(
                "toko_nama" => $this->toko_nama, 
                "toko_kode" => $this->toko_kode, 
                "toko_last_modified" => $this->toko_last_modified, 
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
                "id_pk_toko" => $this->id_pk_toko
            );
            $data = array(
                "toko_status" => "nonaktif", 
                "toko_last_modified" => $this->toko_last_modified, 
                "id_last_modified" => $this->id_last_modified, 
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->toko_nama == ""){
            return false;
        }
        if($this->toko_kode == ""){
            return false;
        }
        if($this->toko_status == ""){
            return false;
        }
        if($this->toko_create_date == ""){
            return false;
        }
        if($this->toko_last_modified == ""){
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
        if($this->toko_nama == ""){
            return false;
        }
        if($this->toko_kode == ""){
            return false;
        }
        if($this->toko_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_toko == ""){
            return false;
        }
        if($this->toko_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($toko_nama,$toko_kode,$toko_status){
        if(!$this->set_toko_nama($toko_nama)){
            return false;
        }
        if(!$this->set_toko_kode($toko_kode)){
            return false;
        }
        if(!$this->set_toko_status($toko_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_toko,$toko_nama,$toko_kode){
        if(!$this->set_id_pk_toko($id_pk_toko)){
            return false;
        }
        if(!$this->set_toko_nama($toko_nama)){
            return false;
        }
        if(!$this->set_toko_kode($toko_kode)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_toko){
        if(!$this->set_id_pk_toko($id_pk_toko)){
            return false;
        }
        return true;
    }
    public function get_id_pk_toko(){
        return $this->id_pk_toko;
    }
    public function get_toko_nama(){
        return $this->toko_nama;
    }
    public function get_toko_kode(){
        return $this->toko_kode;
    }
    public function get_toko_status(){
        return $this->toko_status;
    }
    public function set_id_pk_toko($id_pk_toko){
        if($id_pk_toko != ""){
            $this->id_pk_toko = $id_pk_toko;
            return true;
        }
        return false;
    }
    public function set_toko_nama($toko_nama){
        if($toko_nama != ""){
            $this->toko_nama = $toko_nama;
            return true;
        }
        return false;
    }
    public function set_toko_kode($toko_kode){
        if($toko_kode != ""){
            $this->toko_kode = $toko_kode;
            return true;
        }
        return false;
    }
    public function set_toko_status($toko_status){
        if($toko_status != ""){
            $this->toko_status = $toko_status;
            return true;
        }
        return false;
    }
}