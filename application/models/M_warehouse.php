<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_warehouse extends ci_model{
    private $tbl_name = "mstr_warehouse";
    private $columns = array();
    private $id_pk_warehouse;
    private $warehouse_nama;
    private $warehouse_alamat;
    private $warehouse_notelp;
    private $warehouse_desc;
    private $id_fk_cabang;
    private $warehouse_status;
    private $warehouse_create_date;
    private $warehouse_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->set_column("warehouse_nama","nama warehouse","required");
        $this->set_column("warehouse_alamat","alamat","required");
        $this->set_column("warehouse_notelp","no telpon","required");
        $this->set_column("warehouse_desc","deskripsi","required");
        $this->set_column("id_fk_cabang","cabang","required");
        $this->set_column("warehouse_status","status","required");
        $this->set_column("warehouse_last_modified","last modified","required");
        $this->warehouse_create_date = date("y-m-d h:i:s");
        $this->warehouse_last_modified = date("y-m-d h:i:s");
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
        $sql = "
        drop table if exists mstr_warehouse;
        create table mstr_warehouse(
            id_pk_warehouse int primary key auto_increment,
            warehouse_nama varchar(100),
            warehouse_alamat varchar(200),
            warehouse_notelp varchar(30),
            warehouse_desc varchar(150),
            id_fk_cabang int,
            warehouse_status varchar(15),
            warehouse_create_date datetime,
            warehouse_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_warehouse_log;
        create table mstr_warehouse_log(
            id_pk_warehouse_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_warehouse int,
            warehouse_nama varchar(100),
            warehouse_alamat varchar(200),
            warehouse_notelp varchar(30),
            warehouse_desc varchar(150),
            id_fk_cabang int,
            warehouse_status varchar(15),
            warehouse_create_date datetime,
            warehouse_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_warehouse;
        delimiter $$
        create trigger trg_after_insert_warehouse
        after insert on mstr_warehouse
        for each row
        begin
            insert into mstr_warehouse_log(executed_function,id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,id_fk_cabang,warehouse_status,warehouse_create_date,warehouse_last_modified,id_create_data,id_last_modified) values ('after insert',new.id_pk_warehouse,new.warehouse_nama,new.warehouse_alamat,new.warehouse_notelp,new.warehouse_desc,new.id_fk_cabang, new.warehouse_status,new.warehouse_create_date,new.warehouse_last_modified,new.id_create_data,new.id_last_modified);

            select last_insert_id() into @last_id;
            set @log_msg = concat('Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: ',now());
            set @log_it = concat('Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log ',@last_id);
            set @log_data = concat('','[id_pk_warehouse: ',new.id_pk_warehouse,']','[warehouse_nama: ',new.warehouse_nama,']','[warehouse_alamat: ',new.warehouse_alamat,']','[warehouse_notelp: ',new.warehouse_notelp,']','[warehouse_desc: ',new.warehouse_desc,']','[id_fk_cabang: ',new.id_fk_cabang,']','[warehouse_status: ',new.warehouse_status,']','[warehouse_create_date: ',new.warehouse_create_date,']','[warehouse_last_modified: ',new.warehouse_last_modified,']','[id_create_data: ',new.id_create_data,']','[id_last_modified: ',new.id_last_modified,']');
            call insert_log_all(new.id_last_modified,@log_msg,@log_data,@log_it);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_warehouse;
        delimiter $$
        create trigger trg_after_update_warehouse
        after update on mstr_warehouse
        for each row
        begin
            insert into mstr_warehouse_log(executed_function,id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,id_fk_cabang,warehouse_status,warehouse_create_date,warehouse_last_modified,id_create_data,id_last_modified) values ('after update',new.id_pk_warehouse,new.warehouse_nama,new.warehouse_alamat,new.warehouse_notelp,new.warehouse_desc,new.id_fk_cabang,new.warehouse_status,new.warehouse_create_date,new.warehouse_last_modified,new.id_create_data,new.id_last_modified);

            select last_insert_id() into @last_id;
            set @log_msg = concat('Data diubah pada tabel mstr_warehouse. Waktu perubahan: ',now());
            set @log_it = concat('Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log ',@last_id);
            set @log_data = concat('','[id_pk_warehouse: ',old.id_pk_warehouse,' => ',new.id_pk_warehouse,']','[warehouse_nama: ',old.warehouse_nama,' => ',new.warehouse_nama,']','[warehouse_alamat: ',old.warehouse_alamat,' => ',new.warehouse_alamat,']','[warehouse_notelp: ',old.warehouse_notelp,' => ',new.warehouse_notelp,']','[warehouse_desc: ',old.warehouse_desc,' => ',new.warehouse_desc,']','[id_fk_cabang: ',old.id_fk_cabang,' => ',new.id_fk_cabang,']','[warehouse_status: ',old.warehouse_status,' => ',new.warehouse_status,']','[warehouse_create_date: ',old.warehouse_create_date,' => ',new.warehouse_create_date,']','[warehouse_last_modified: ',old.warehouse_last_modified,' => ',new.warehouse_last_modified,']','[id_create_data: ',old.id_create_data,' => ',new.id_create_data,']','[id_last_modified: ',old.id_last_modified,' => ',new.id_last_modified,']');
            call insert_log_all(new.id_last_modified,@log_msg,@log_data,@log_it);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                warehouse_nama like '%".$search_key."%' or 
                warehouse_alamat like '%".$search_key."%' or 
                warehouse_notelp like '%".$search_key."%' or 
                warehouse_desc like '%".$search_key."%' or 
                id_fk_cabang like '%".$search_key."%' or 
                warehouse_status like '%".$search_key."%' or 
                warehouse_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,id_fk_cabang,warehouse_status,warehouse_last_modified
        from ".$this->tbl_name." 
        where warehouse_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_warehouse
        from ".$this->tbl_name." 
        where warehouse_status = ? ".$search_query." 
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list_warehouse(){
        $query = "
        select id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,id_fk_cabang,warehouse_status,warehouse_last_modified
        from ".$this->tbl_name." 
        where warehouse_status = ?";
        $args = array(
            "aktif"
        );
        return executeQuery($query,$args);
    }
    public function detail_by_id(){
        $field = array(
            "id_pk_warehouse",
            "warehouse_nama",
            "warehouse_alamat",
            "warehouse_notelp",
            "warehouse_desc",
            "id_fk_cabang",
            "warehouse_status",
            "warehouse_last_modified"
        );
        $where = array(
            "id_pk_warehouse" => $this->id_pk_warehouse
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "warehouse_nama" => $this->warehouse_nama,
                "warehouse_alamat" => $this->warehouse_alamat,
                "warehouse_notelp" => $this->warehouse_notelp,
                "warehouse_desc" => $this->warehouse_desc,
                "id_fk_cabang" => $this->id_fk_cabang,
                "warehouse_status" => $this->warehouse_status,
                "warehouse_create_date" => $this->warehouse_create_date,
                "warehouse_last_modified" => $this->warehouse_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertrow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_warehouse" => $this->id_pk_warehouse
            );
            $data = array(
                "warehouse_nama" => $this->warehouse_nama,
                "warehouse_alamat" => $this->warehouse_alamat,
                "warehouse_notelp" => $this->warehouse_notelp,
                "warehouse_desc" => $this->warehouse_desc,
                "id_fk_cabang" => $this->id_fk_cabang,
                "warehouse_last_modified" => $this->warehouse_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_warehouse" => $this->id_pk_warehouse
            );
            $data = array(
                "warehouse_status" => "nonaktif",
                "warehouse_last_modified" => $this->warehouse_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->warehouse_nama == ""){
            return false;
        }
        if($this->warehouse_alamat == ""){
            return false;
        }
        if($this->warehouse_notelp == ""){
            return false;
        }
        if($this->warehouse_desc == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->warehouse_status == ""){
            return false;
        }
        if($this->warehouse_create_date == ""){
            return false;
        }
        if($this->warehouse_last_modified == ""){
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
        if($this->id_pk_warehouse == ""){
            return false;
        }
        if($this->warehouse_nama == ""){
            return false;
        }
        if($this->warehouse_alamat == ""){
            return false;
        }
        if($this->warehouse_notelp == ""){
            return false;
        }
        if($this->warehouse_desc == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->warehouse_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_warehouse == ""){
            return false;
        }
        if($this->warehouse_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($warehouse_nama,$warehouse_alamat,$warehouse_notelp,$warehouse_desc,$id_fk_cabang,$warehouse_status){
        if(!$this->set_warehouse_nama($warehouse_nama)){
            return false;
        }
        if(!$this->set_warehouse_alamat($warehouse_alamat)){
            return false;
        }
        if(!$this->set_warehouse_notelp($warehouse_notelp)){
            return false;
        }
        if(!$this->set_warehouse_desc($warehouse_desc)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        if(!$this->set_warehouse_status($warehouse_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_warehouse,$warehouse_nama,$warehouse_alamat,$warehouse_notelp,$warehouse_desc,$id_fk_cabang){
        if(!$this->set_id_pk_warehouse($id_pk_warehouse)){
            return false;
        }
        if(!$this->set_warehouse_nama($warehouse_nama)){
            return false;
        }
        if(!$this->set_warehouse_alamat($warehouse_alamat)){
            return false;
        }
        if(!$this->set_warehouse_notelp($warehouse_notelp)){
            return false;
        }
        if(!$this->set_warehouse_desc($warehouse_desc)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_warehouse){
        if(!$this->set_id_pk_warehouse($id_pk_warehouse)){
            return false;
        }
        return true;}
    public function set_id_pk_warehouse($id_pk_warehouse){
        if($id_pk_warehouse != ""){
            $this->id_pk_warehouse = $id_pk_warehouse;
            return true;
        }
        return false;
    }
    public function set_warehouse_nama($warehouse_nama){
        if($warehouse_nama != ""){
            $this->warehouse_nama = $warehouse_nama;
            return true;
        }
        return false;
    }
    public function set_warehouse_alamat($warehouse_alamat){
        if($warehouse_alamat != ""){
            $this->warehouse_alamat = $warehouse_alamat;
            return true;
        }
        return false;
    }
    public function set_warehouse_notelp($warehouse_notelp){
        if($warehouse_notelp != ""){
            $this->warehouse_notelp = $warehouse_notelp;
            return true;
        }
        return false;
    }
    public function set_warehouse_desc($warehouse_desc){
        if($warehouse_desc != ""){
            $this->warehouse_desc = $warehouse_desc;
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
    public function set_warehouse_status($warehouse_status){
        if($warehouse_status != ""){
            $this->warehouse_status = $warehouse_status;
            return true;
        }
        return false;
    }
    public function data_excel(){
        $query = "
        select id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_last_modified
        from ".$this->tbl_name." 
        where warehouse_status = ?";
        $args = array(
            "aktif"
        );
        return executeQuery($query,$args);
    }
    public function columns_excel(){
        $this->columns = array();
        
        $this->set_column("warehouse_nama","nama warehouse","required");
        $this->set_column("warehouse_alamat","alamat","required");
        $this->set_column("warehouse_notelp","no telpon","required");
        $this->set_column("warehouse_desc","deskripsi","required");
        $this->set_column("id_fk_cabang","cabang","required");
        $this->set_column("warehouse_status","status","required");
        $this->set_column("warehouse_last_modified","last modified","required");
        return $this->columns;
    }
}