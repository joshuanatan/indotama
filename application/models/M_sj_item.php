<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_sj_item extends ci_model{
    private $tbl_name = "tbl_sj_item";
    private $columns = array();
    private $id_pk_sj_item;
    private $sj_item_qty;
    private $sj_item_note;
    private $sj_item_status;
    private $id_fk_satuan;
    private $id_fk_surat_jalan;
    private $id_fk_brg_penjualan;
    private $sj_item_create_date;
    private $sj_item_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->sj_item_create_date = date("y-m-d h:i:s");
        $this->sj_item_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "drop table if exists tbl_sj_item;
        create table tbl_sj_item(
            id_pk_sj_item int primary key auto_increment,
            sj_item_qty double,
            sj_item_note varchar(150),
            sj_item_status varchar(15),
            id_fk_satuan int,
            id_fk_surat_jalan int,
            id_fk_brg_penjualan int,
            sj_item_create_date datetime,
            sj_item_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_sj_item_log;
        create table tbl_sj_item_log(
            id_pk_sj_item_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_sj_item int,
            sj_item_qty double,
            sj_item_note varchar(150),
            sj_item_status varchar(15),
            id_fk_satuan int,
            id_fk_surat_jalan int,
            id_fk_brg_penjualan int,
            sj_item_create_date datetime,
            sj_item_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_sj_item;
        delimiter $$
        create trigger trg_after_insert_sj_item
        after insert on tbl_sj_item
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_item_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.sj_item_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_sj_item_log(executed_function,id_pk_sj_item,sj_item_qty,sj_item_note,sj_item_status,id_fk_satuan,id_fk_surat_jalan,id_fk_brg_penjualan,sj_item_create_date,sj_item_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_sj_item,new.sj_item_qty,new.sj_item_note,new.sj_item_status,new.id_fk_satuan,new.id_fk_surat_jalan,new.id_fk_brg_penjualan,new.sj_item_create_date,new.sj_item_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_sj_item;
        delimiter $$
        create trigger trg_after_update_sj_item
        after update on tbl_sj_item
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_item_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.sj_item_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_sj_item_log(executed_function,id_pk_sj_item,sj_item_qty,sj_item_note,sj_item_status,id_fk_satuan,id_fk_surat_jalan,id_fk_brg_penjualan,sj_item_create_date,sj_item_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_sj_item,new.sj_item_qty,new.sj_item_note,new.sj_item_status,new.id_fk_satuan,new.id_fk_surat_jalan,new.id_fk_brg_penjualan,new.sj_item_create_date,new.sj_item_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "sj_item_qty" => $this->sj_item_qty,
                "sj_item_note" => $this->sj_item_note,
                "sj_item_status" => $this->sj_item_status,
                "id_fk_satuan" => $this->id_fk_satuan,
                "id_fk_surat_jalan" => $this->id_fk_surat_jalan,
                "id_fk_brg_penjualan" => $this->id_fk_brg_penjualan,
                "sj_item_create_date" => $this->sj_item_create_date,
                "sj_item_last_modified" => $this->sj_item_last_modified,
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
                "id_pk_sj_item" => $this->id_pk_sj_item
            );
            $data = array(
                "sj_item_qty" => $this->sj_item_qty,
                "sj_item_note" => $this->sj_item_note,
                "id_fk_satuan" => $this->id_fk_satuan,
                "id_fk_surat_jalan" => $this->id_fk_surat_jalan,
                "id_fk_brg_penjualan" => $this->id_fk_brg_penjualan,
                "sj_item_last_modified" => $this->sj_item_last_modified,
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
                "id_pk_sj_item" => $this->id_pk_sj_item
            );
            $data = array(
                "sj_item_status" => "nonaktif",
                "sj_item_last_modified" => $this->sj_item_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->sj_item_qty == ""){
            return false;
        }
        if($this->sj_item_note == ""){
            return false;
        }
        if($this->sj_item_status == ""){
            return false;
        }
        if($this->id_fk_satuan == ""){
            return false;
        }
        if($this->id_fk_surat_jalan == ""){
            return false;
        }
        if($this->id_fk_brg_penjualan == ""){
            return false;
        }
        if($this->sj_item_create_date == ""){
            return false;
        }
        if($this->sj_item_last_modified == ""){
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
        if($this->id_pk_sj_item == ""){
            return false;
        }
        if($this->sj_item_qty == ""){
            return false;
        }
        if($this->sj_item_note == ""){
            return false;
        }
        if($this->id_fk_satuan == ""){
            return false;
        }
        if($this->id_fk_surat_jalan == ""){
            return false;
        }
        if($this->id_fk_brg_penjualan == ""){
            return false;
        }
        if($this->sj_item_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_sj_item == ""){
            return false;
        }
        if($this->sj_item_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($sj_item_qty,$sj_item_note,$sj_item_status,$id_fk_satuan,$id_fk_surat_jalan,$id_fk_brg_penjualan){
        if(!$this->set_sj_item_qty($sj_item_qty)){
            return false;
        }
        if(!$this->set_sj_item_note($sj_item_note)){
            return false;
        }
        if(!$this->set_sj_item_status($sj_item_status)){
            return false;
        }
        if(!$this->set_id_fk_satuan($id_fk_satuan)){
            return false;
        }
        if(!$this->set_id_fk_surat_jalan($id_fk_surat_jalan)){
            return false;
        }
        if(!$this->set_id_fk_brg_penjualan($id_fk_brg_penjualan)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_sj_item,$sj_item_qty,$sj_item_note,$id_fk_satuan,$id_fk_surat_jalan,$id_fk_brg_penjualan){
        if(!$this->set_id_pk_sj_item($id_pk_sj_item)){
            return false;
        }
        if(!$this->set_sj_item_qty($sj_item_qty)){
            return false;
        }
        if(!$this->set_sj_item_note($sj_item_note)){
            return false;
        }
        if(!$this->set_id_fk_satuan($id_fk_satuan)){
            return false;
        }
        if(!$this->set_id_fk_surat_jalan($id_fk_surat_jalan)){
            return false;
        }
        if(!$this->set_id_fk_brg_penjualan($id_fk_brg_penjualan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_sj_item){
        if(!$this->set_id_pk_sj_item($id_pk_sj_item)){
            return false;
        }
        return true;
    }
    public function get_id_pk_sj_item(){
        return $this->id_pk_sj_item;
    }
    public function get_sj_item_qty(){
        return $this->sj_item_qty;
    }
    public function get_sj_item_note(){
        return $this->sj_item_note;
    }
    public function get_sj_item_status(){
        return $this->sj_item_note;
    }
    public function get_id_fk_satuan(){
        return $this->id_fk_satuan;
    }
    public function get_id_fk_surat_jalan(){
        return $this->id_fk_surat_jalan;
    }
    public function get_id_fk_brg_penjualan(){
        return $this->id_fk_brg_penjualan;
    }
    public function set_id_pk_sj_item($id_pk_sj_item){
        if($id_pk_sj_item != ""){
            $this->id_pk_sj_item = $id_pk_sj_item;
            return true;
        }
        return false;
    }
    public function set_sj_item_qty($sj_item_qty){
        if($sj_item_qty != ""){
            $this->sj_item_qty = $sj_item_qty;
            return true;
        }
        return false;
    }
    public function set_sj_item_note($sj_item_note){
        if($sj_item_note != ""){
            $this->sj_item_note = $sj_item_note;
            return true;
        }
        return false;
    }
    public function set_sj_item_status($sj_item_status){
        if($sj_item_status != ""){
            $this->sj_item_status = $sj_item_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_satuan($id_fk_satuan){
        if($id_fk_satuan != ""){
            $this->id_fk_satuan = $id_fk_satuan;
            return true;
        }
        return false;
    }
    public function set_id_fk_surat_jalan($id_fk_surat_jalan){
        if($id_fk_surat_jalan != ""){
            $this->id_fk_surat_jalan = $id_fk_surat_jalan;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg_penjualan($id_fk_brg_penjualan){
        if($id_fk_brg_penjualan != ""){
            $this->id_fk_brg_penjualan = $id_fk_brg_penjualan;
            return true;
        }
        return false;
    }
}