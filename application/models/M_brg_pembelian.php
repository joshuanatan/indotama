<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_brg_pembelian extends ci_model{
    private $tbl_name = "tbl_brg_pembelian";
    private $columns = array();
    private $id_pk_brg_pembelian;
    private $brg_pem_qty;
    private $brg_pem_satuan;
    private $brg_pem_harga;
    private $brg_pem_note;
    private $brg_pem_status;
    private $id_fk_pembelian;
    private $id_fk_barang;
    private $brg_pem_create_date;
    private $brg_pem_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->brg_pem_create_date = date("y-m-d h:i:s");
        $this->brg_pem_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists tbl_brg_pembelian;
        create table tbl_brg_pembelian(
            id_pk_brg_pembelian int primary key auto_increment,
            brg_pem_qty double,
            brg_pem_satuan varchar(20),
            brg_pem_harga int,
            brg_pem_note varchar(150),
            brg_pem_status varchar(15),
            id_fk_pembelian int,
            id_fk_barang int,
            brg_pem_create_date datetime,
            brg_pem_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_pembelian_log;
        create table tbl_brg_pembelian_log(
            id_pk_brg_pembelian_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_pembelian int,
            brg_pem_qty double,
            brg_pem_satuan varchar(20),
            brg_pem_harga int,
            brg_pem_note varchar(150),
            brg_pem_status varchar(15),
            id_fk_pembelian int,
            id_fk_barang int,
            brg_pem_create_date datetime,
            brg_pem_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_pembelian;
        delimiter $$
        create trigger trg_after_insert_brg_pembelian
        after insert on tbl_brg_pembelian
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pembelian_log(executed_function,id_pk_brg_pembelian,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_pem_status,id_fk_pembelian,id_fk_barang,brg_pem_create_date,brg_pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pembelian,new.brg_pem_qty,new.brg_pem_satuan,new.brg_pem_harga,new.brg_pem_note,new.brg_pem_status,new.id_fk_pembelian,new.id_fk_barang,new.brg_pem_create_date,new.brg_pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_brg_pembelian;
        delimiter $$
        create trigger trg_after_update_brg_pembelian
        after update on tbl_brg_pembelian
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pembelian_log(executed_function,id_pk_brg_pembelian,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_pem_status,id_fk_pembelian,id_fk_barang,brg_pem_create_date,brg_pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pembelian,new.brg_pem_qty,new.brg_pem_satuan,new.brg_pem_harga,new.brg_pem_note,new.brg_pem_status,new.id_fk_pembelian,new.id_fk_barang,new.brg_pem_create_date,new.brg_pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function list(){
        $sql = "
        select id_pk_brg_pembelian,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,id_fk_pembelian,id_fk_barang,brg_nama,brg_pem_create_date,brg_pem_last_modified
        from ".$this->tbl_name."
        inner join mstr_barang on mstr_barang.id_pk_brg = ".$this->tbl_name.".id_fk_barang
        where brg_pem_status = ? and id_fk_pembelian = ? and brg_status = ?
        ";
        $args = array(
            "aktif",$this->id_fk_pembelian,"aktif"
        );
        return executequery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_pem_qty" => $this->brg_pem_qty,
                "brg_pem_satuan" => $this->brg_pem_satuan,
                "brg_pem_harga" => $this->brg_pem_harga,
                "brg_pem_note" => $this->brg_pem_note,
                "brg_pem_status" => $this->brg_pem_status,
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_pem_create_date" => $this->brg_pem_create_date,
                "brg_pem_last_modified" => $this->brg_pem_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertrow($this->tbl_name,$data);
        }
        else{
            return false;
        }
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_brg_pembelian" => $this->id_pk_brg_pembelian,
            );
            $data = array(
                "brg_pem_qty" => $this->brg_pem_qty,
                "brg_pem_satuan" => $this->brg_pem_satuan,
                "brg_pem_harga" => $this->brg_pem_harga,
                "brg_pem_note" => $this->brg_pem_note,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_pem_last_modified" => $this->brg_pem_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_brg_pembelian" => $this->id_pk_brg_pembelian,
            );
            $data = array(
                "brg_pem_status" => "nonaktif",
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "brg_pem_last_modified" => $this->brg_pem_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;

        }
    }
    public function check_insert(){
        if($this->brg_pem_qty == ""){
            return false;
        }
        if($this->brg_pem_satuan == ""){
            return false;
        }
        if($this->brg_pem_harga == ""){
            return false;
        }
        if($this->brg_pem_note == ""){
            return false;
        }
        if($this->brg_pem_status == ""){
            return false;
        }
        if($this->id_fk_pembelian == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_pem_create_date == ""){
            return false;
        }
        if($this->brg_pem_last_modified == ""){
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

        if($this->id_pk_brg_pembelian == ""){
            return false;
        }
        if($this->brg_pem_qty == ""){
            return false;
        }
        if($this->brg_pem_satuan == ""){
            return false;
        }
        if($this->brg_pem_harga == ""){
            return false;
        }
        if($this->brg_pem_note == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_pembelian == ""){
            return false;
        }
        if($this->brg_pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_pem_qty,$brg_pem_satuan,$brg_pem_harga,$brg_pem_note,$brg_pem_status,$id_fk_pembelian,$id_fk_barang){
        if(!$this->set_brg_pem_qty($brg_pem_qty)){
            return false;
        }
        if(!$this->set_brg_pem_satuan($brg_pem_satuan)){
            return false;
        }
        if(!$this->set_brg_pem_harga($brg_pem_harga)){
            return false;
        }
        if(!$this->set_brg_pem_note($brg_pem_note)){
            return false;
        }
        if(!$this->set_brg_pem_status($brg_pem_status)){
            return false;
        }
        if(!$this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_pembelian,$brg_pem_qty,$brg_pem_satuan,$brg_pem_harga,$brg_pem_note,$id_fk_barang){
        if(!$this->set_id_pk_brg_pembelian($id_pk_brg_pembelian)){
            return false;
        }
        if(!$this->set_brg_pem_qty($brg_pem_qty)){
            return false;
        }
        if(!$this->set_brg_pem_satuan($brg_pem_satuan)){
            return false;
        }
        if(!$this->set_brg_pem_harga($brg_pem_harga)){
            return false;
        }
        if(!$this->set_brg_pem_note($brg_pem_note)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_pembelian,$id_fk_pembelian){
        if($this->set_id_pk_brg_pembelian($id_pk_brg_pembelian)){
            return false;
        }
        if($this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_pembelian($id_pk_brg_pembelian){
        if($id_pk_brg_pembelian != ""){
            $this->id_pk_brg_pembelian = $id_pk_brg_pembelian;
            return true;
        }
        return false;
    }
    public function set_brg_pem_qty($brg_pem_qty){
        if($brg_pem_qty != ""){
            $this->brg_pem_qty = $brg_pem_qty;
            return true;
        }
        return false;
    }
    public function set_brg_pem_satuan($brg_pem_satuan){
        if($brg_pem_satuan != ""){
            $this->brg_pem_satuan = $brg_pem_satuan;
            return true;
        }
        return false;
    }
    public function set_brg_pem_harga($brg_pem_harga){
        if($brg_pem_harga != ""){
            $this->brg_pem_harga = $brg_pem_harga;
            return true;
        }
        return false;
    }
    public function set_brg_pem_note($brg_pem_note){
        if($brg_pem_note != ""){
            $this->brg_pem_note = $brg_pem_note;
            return true;
        }
        return false;
    }
    public function set_brg_pem_status($brg_pem_status){
        if($brg_pem_status != ""){
            $this->brg_pem_status = $brg_pem_status;
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
    public function set_id_fk_barang($id_fk_barang){
        if($id_fk_barang != ""){
            $this->id_fk_barang = $id_fk_barang;
            return true;
        }
        return false;
    }
    public function get_id_pk_brg_pembelian(){
        return $this->id_pk_brg_pembelian;
    }
    public function get_brg_pem_qty(){
        return $this->brg_pem_qty;
    }
    public function get_brg_pem_satuan(){
        return $this->brg_pem_satuan;
    }
    public function get_brg_pem_harga(){
        return $this->brg_pem_harga;
    }
    public function get_brg_pem_note(){
        return $this->brg_pem_note;
    }
    public function get_brg_pem_status(){
        return $this->brg_pem_note;
    }
    public function get_id_fk_pembelian(){
        return $this->id_fk_pembelian;
    }
    public function get_id_fk_barang(){
        return $this->id_fk_barang;
    }
}