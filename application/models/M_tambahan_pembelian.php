<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_tambahan_pembelian extends ci_model{
    private $tbl_name = "tbl_tambahan_pembelian";
    private $columns = array();
    private $id_pk_tmbhn;
    private $tmbhn;
    private $tmbhn_jumlah;
    private $tmbhn_satuan;
    private $tmbhn_harga;
    private $tmbhn_notes;
    private $tmbhn_status;
    private $id_fk_pembelian;
    private $tmbhn_create_date;
    private $tmbhn_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->tmbhn_create_date = date("y-m-d h:i:s");
        $this->tmbhn_last_modified = $this->session->id_user;
        $this->id_create_data = date("y-m-d h:i:s");
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "drop table if exists tbl_tambahan_pembelian;
        create table tbl_tambahan_pembelian(
            id_pk_tmbhn int primary key auto_increment,
            tmbhn varchar(100),
            tmbhn_jumlah double,
            tmbhn_satuan varchar(20),
            tmbhn_harga int,
            tmbhn_notes varchar(200),
            tmbhn_status varchar(15),
            id_fk_pembelian int,
            tmbhn_create_date datetime,
            tmbhn_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_tambahan_pembelian_log;
        create table tbl_tambahan_pembelian_log(
            id_pk_tmbhn_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_tmbhn int,
            tmbhn varchar(100),
            tmbhn_jumlah double,
            tmbhn_satuan varchar(20),
            tmbhn_harga int,
            tmbhn_notes varchar(200),
            tmbhn_status varchar(15),
            id_fk_pembelian int,
            tmbhn_create_date datetime,
            tmbhn_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_tambahan_pembelian;
        delimiter $$
        create trigger trg_after_insert_tambahan_pembelian
        after insert on tbl_tambahan_pembelian
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_pembelian_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_pembelian,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_pembelian,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_tambahan_pembelian;
        delimiter $$
        create trigger trg_after_update_tambahan_pembelian
        after update on tbl_tambahan_pembelian
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_pembelian_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_pembelian,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_pembelian,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    public function list(){
        $sql = "
        select id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,tmbhn_last_modified
        from ".$this->tbl_name."
        where tmbhn_status = ? and id_fk_pembelian = ?";
        $args = array(
            "aktif",$this->id_fk_pembelian
        );
        return executequery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "tmbhn" => $this->tmbhn, 
                "tmbhn_jumlah" => $this->tmbhn_jumlah, 
                "tmbhn_satuan" => $this->tmbhn_satuan, 
                "tmbhn_harga" => $this->tmbhn_harga, 
                "tmbhn_notes" => $this->tmbhn_notes, 
                "tmbhn_status" => $this->tmbhn_status, 
                "id_fk_pembelian" => $this->id_fk_pembelian, 
                "tmbhn_create_date" => $this->tmbhn_create_date, 
                "tmbhn_last_modified" => $this->tmbhn_last_modified, 
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
                "id_pk_tmbhn" => $this->id_pk_tmbhn
            );
            $data = array(
                "tmbhn" => $this->tmbhn, 
                "tmbhn_jumlah" => $this->tmbhn_jumlah, 
                "tmbhn_satuan" => $this->tmbhn_satuan, 
                "tmbhn_harga" => $this->tmbhn_harga, 
                "tmbhn_notes" => $this->tmbhn_notes, 
                "tmbhn_last_modified" => $this->tmbhn_last_modified, 
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
                "id_pk_tmbhn" => $this->id_pk_tmbhn
            );
            $data = array(
                "tmbhn_status" => "nonaktif", 
                "tmbhn_last_modified" => $this->tmbhn_last_modified, 
                "id_last_modified" => $this->id_last_modified, 
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->tmbhn == ""){
            return false;
        }
        if($this->tmbhn_jumlah == ""){
            return false;
        }
        if($this->tmbhn_satuan == ""){
            return false;
        }
        if($this->tmbhn_harga == ""){
            return false;
        }
        if($this->tmbhn_notes == ""){
            return false;
        }
        if($this->tmbhn_status == ""){
            return false;
        }
        if($this->id_fk_pembelian == ""){
            return false;
        }
        if($this->tmbhn_create_date == ""){
            return false;
        }
        if($this->tmbhn_last_modified == ""){
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
        if($this->id_pk_tmbhn == ""){
            return false;
        }
        if($this->tmbhn == ""){
            return false;
        }
        if($this->tmbhn_jumlah == ""){
            return false;
        }
        if($this->tmbhn_satuan == ""){
            return false;
        }
        if($this->tmbhn_harga == ""){
            return false;
        }
        if($this->tmbhn_notes == ""){
            return false;
        }
        if($this->tmbhn_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_tmbhn == ""){
            return false;
        }
        if($this->tmbhn_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($tmbhn,$tmbhn_jumlah,$tmbhn_satuan,$tmbhn_harga,$tmbhn_notes,$tmbhn_status,$id_fk_pembelian){
        if(!$this->set_tmbhn($tmbhn)){
            return false;
        }
        if(!$this->set_tmbhn_jumlah($tmbhn_jumlah)){
            return false;
        }
        if(!$this->set_tmbhn_satuan($tmbhn_satuan)){
            return false;
        }
        if(!$this->set_tmbhn_harga($tmbhn_harga)){
            return false;
        }
        if(!$this->set_tmbhn_notes($tmbhn_notes)){
            return false;
        }
        if(!$this->set_tmbhn_status($tmbhn_status)){
            return false;
        }
        if(!$this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_tmbhn,$tmbhn,$tmbhn_jumlah,$tmbhn_satuan,$tmbhn_harga,$tmbhn_notes){
        if(!$this->set_id_pk_tmbhn($id_pk_tmbhn)){
            return false;
        }
        if(!$this->set_tmbhn($tmbhn)){
            return false;
        }
        if(!$this->set_tmbhn_jumlah($tmbhn_jumlah)){
            return false;
        }
        if(!$this->set_tmbhn_satuan($tmbhn_satuan)){
            return false;
        }
        if(!$this->set_tmbhn_harga($tmbhn_harga)){
            return false;
        }
        if(!$this->set_tmbhn_notes($tmbhn_notes)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_tmbhn){
        if(!$this->set_id_pk_tmbhn($id_pk_tmbhn)){
            return false;
        }
        return true;
    }
    public function set_id_pk_tmbhn($id_pk_tmbhn){
        if($id_pk_tmbhn != ""){
            $this->id_pk_tmbhn = $id_pk_tmbhn;
            return true;
        }
        return false;
    }
    public function set_tmbhn($tmbhn){
        if($tmbhn != ""){
            $this->tmbhn = $tmbhn;
            return true;
        }
        return false;
    }
    public function set_tmbhn_jumlah($tmbhn_jumlah){
        if($tmbhn_jumlah != ""){
            $this->tmbhn_jumlah = $tmbhn_jumlah;
            return true;
        }
        return false;
    }
    public function set_tmbhn_satuan($tmbhn_satuan){
        if($tmbhn_satuan != ""){
            $this->tmbhn_satuan = $tmbhn_satuan;
            return true;
        }
        return false;
    }
    public function set_tmbhn_harga($tmbhn_harga){
        if($tmbhn_harga != ""){
            $this->tmbhn_harga = $tmbhn_harga;
            return true;
        }
        return false;
    }
    public function set_tmbhn_notes($tmbhn_notes){
        if($tmbhn_notes != ""){
            $this->tmbhn_notes = $tmbhn_notes;
            return true;
        }
        return false;
    }
    public function set_tmbhn_status($tmbhn_status){
        if($tmbhn_status != ""){
            $this->tmbhn_status = $tmbhn_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_pembelian($id_fk_pembelian){
        if($id_fk_pembelian != ""){
            $this->id_fk_pembelian = $id_fk_pembelian;
            return true;
        }
        return false;
    }
    public function get_id_pk_tmbhn(){
        return $this->id_pk_tmbhn;
    }
    public function get_tmbhn(){
        return $this->tmbhn;
    }
    public function get_tmbhn_jumlah(){
        return $this->tmbhn_jumlah;
    }
    public function get_tmbhn_satuan(){
        return $this->tmbhn_satuan;
    }
    public function get_tmbhn_harga(){
        return $this->tmbhn_harga;
    }
    public function get_tmbhn_notes(){
        return $this->tmbhn_notes;
    }
    public function get_tmbhn_status(){
        return $this->tmbhn_status;
    }
    public function get_id_fk_pembelian(){
        return $this->id_fk_pembelian;
    }
}