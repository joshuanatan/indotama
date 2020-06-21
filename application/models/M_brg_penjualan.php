<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_brg_penjualan extends ci_model{
    private $tbl_name = "tbl_brg_penjualan";
    private $columns = array();
    private $id_pk_brg_penjualan;
    private $brg_penjualan_qty_real;
    private $brg_penjualan_qty;
    private $brg_penjualan_satuan;
    private $brg_penjualan_harga;
    private $brg_penjualan_note;
    private $brg_penjualan_status;
    private $id_fk_penjualan;
    private $id_fk_barang;
    private $brg_penjualan_create_date;
    private $brg_penjualan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->brg_penjualan_create_date = date("y-m-d h:i:s");
        $this->brg_penjualan_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists tbl_brg_penjualan;
        create table tbl_brg_penjualan(
            id_pk_brg_penjualan int primary key auto_increment,
            brg_penjualan_qty_real double,
            brg_penjualan_satuan_real varchar(20),
            brg_penjualan_qty double,
            brg_penjualan_satuan varchar(20),
            brg_penjualan_harga int,
            brg_penjualan_note varchar(150),
            brg_penjualan_status varchar(15),
            id_fk_penjualan int,
            id_fk_barang int,
            brg_penjualan_create_date datetime,
            brg_penjualan_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_penjualan_log;
        create table tbl_brg_penjualan_log(
            id_pk_brg_penjualan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_penjualan int,
            brg_penjualan_qty_real double,
            brg_penjualan_satuan_real varchar(20),
            brg_penjualan_qty double,
            brg_penjualan_satuan varchar(20),
            brg_penjualan_harga int,
            brg_penjualan_note varchar(150),
            brg_penjualan_status varchar(15),
            id_fk_penjualan int,
            id_fk_barang int,
            brg_penjualan_create_date datetime,
            brg_penjualan_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_penjualan;
        delimiter $$
        create trigger trg_after_insert_brg_penjualan
        after insert on tbl_brg_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_brg_penjualan;
        delimiter $$
        create trigger trg_after_update_brg_penjualan
        after update on tbl_brg_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function list(){
        $sql = "
        select id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,id_fk_penjualan,id_fk_barang,brg_nama,brg_harga,brg_penjualan_create_date,brg_penjualan_last_modified
        from ".$this->tbl_name."
        inner join mstr_barang on mstr_barang.id_pk_brg = ".$this->tbl_name.".id_fk_barang
        where brg_penjualan_status = ? and id_fk_penjualan = ?
        ";
        $args = array(
            "aktif",$this->id_fk_penjualan
        );
        return executequery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_penjualan_qty_real" => $this->brg_penjualan_qty_real,
                "brg_penjualan_satuan_real" => $this->brg_penjualan_satuan_real,
                "brg_penjualan_qty" => $this->brg_penjualan_qty,
                "brg_penjualan_satuan" => $this->brg_penjualan_satuan,
                "brg_penjualan_harga" => $this->brg_penjualan_harga,
                "brg_penjualan_note" => $this->brg_penjualan_note,
                "brg_penjualan_status" => $this->brg_penjualan_status,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_penjualan_create_date" => $this->brg_penjualan_create_date,
                "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
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
                "id_pk_brg_penjualan" => $this->id_pk_brg_penjualan,
            );
            $data = array(
                "brg_penjualan_qty_real" => $this->brg_penjualan_qty_real,
                "brg_penjualan_satuan_real" => $this->brg_penjualan_satuan_real,
                "brg_penjualan_qty" => $this->brg_penjualan_qty,
                "brg_penjualan_satuan" => $this->brg_penjualan_satuan,
                "brg_penjualan_harga" => $this->brg_penjualan_harga,
                "brg_penjualan_note" => $this->brg_penjualan_note,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
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
                "id_pk_brg_penjualan" => $this->id_pk_brg_penjualan,
            );
            $data = array(
                "brg_penjualan_status" => "nonaktif",
                "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;

        }
    }
    public function check_insert(){
        if($this->brg_penjualan_qty_real == ""){
            return false;
        }
        if($this->brg_penjualan_satuan_real == ""){
            return false;
        }
        if($this->brg_penjualan_qty == ""){
            return false;
        }
        if($this->brg_penjualan_satuan == ""){
            return false;
        }
        if($this->brg_penjualan_harga == ""){
            return false;
        }
        if($this->brg_penjualan_note == ""){
            return false;
        }
        if($this->brg_penjualan_status == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_penjualan_create_date == ""){
            return false;
        }
        if($this->brg_penjualan_last_modified == ""){
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

        if($this->id_pk_brg_penjualan == ""){
            return false;
        }
        if($this->brg_penjualan_qty_real == ""){
            return false;
        }
        if($this->brg_penjualan_satuan_real == ""){
            return false;
        }
        if($this->brg_penjualan_qty == ""){
            return false;
        }
        if($this->brg_penjualan_satuan == ""){
            return false;
        }
        if($this->brg_penjualan_harga == ""){
            return false;
        }
        if($this->brg_penjualan_note == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_penjualan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_penjualan == ""){
            return false;
        }
        if($this->brg_penjualan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_penjualan_qty_real,$brg_penjualan_satuan_real,$brg_penjualan_qty,$brg_penjualan_satuan,$brg_penjualan_harga,$brg_penjualan_note,$brg_penjualan_status,$id_fk_penjualan,$id_fk_barang){
        if(!$this->set_brg_penjualan_qty_real($brg_penjualan_qty_real)){
            return false;
        }
        if(!$this->set_brg_penjualan_qty($brg_penjualan_qty)){
            return false;
        }
        if(!$this->set_brg_penjualan_satuan_real($brg_penjualan_satuan_real)){
            return false;
        }
        if(!$this->set_brg_penjualan_qty($brg_penjualan_qty)){
            return false;
        }
        if(!$this->set_brg_penjualan_satuan($brg_penjualan_satuan)){
            return false;
        }
        if(!$this->set_brg_penjualan_harga($brg_penjualan_harga)){
            return false;
        }
        if(!$this->set_brg_penjualan_note($brg_penjualan_note)){
            return false;
        }
        if(!$this->set_brg_penjualan_status($brg_penjualan_status)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_penjualan,$brg_penjualan_qty_real,$brg_penjualan_satuan_real,$brg_penjualan_qty,$brg_penjualan_satuan,$brg_penjualan_harga,$brg_penjualan_note,$id_fk_barang){
        if(!$this->set_id_pk_brg_penjualan($id_pk_brg_penjualan)){
            return false;
        }
        if(!$this->set_brg_penjualan_qty_real($brg_penjualan_qty_real)){
            return false;
        }
        if(!$this->set_brg_penjualan_satuan_real($brg_penjualan_satuan_real)){
            return false;
        }
        if(!$this->set_brg_penjualan_qty($brg_penjualan_qty)){
            return false;
        }
        if(!$this->set_brg_penjualan_satuan($brg_penjualan_satuan)){
            return false;
        }
        if(!$this->set_brg_penjualan_harga($brg_penjualan_harga)){
            return false;
        }
        if(!$this->set_brg_penjualan_note($brg_penjualan_note)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_penjualan){
        if($this->set_id_pk_brg_penjualan($id_pk_brg_penjualan)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_penjualan($id_pk_brg_penjualan){
        if($id_pk_brg_penjualan != ""){
            $this->id_pk_brg_penjualan = $id_pk_brg_penjualan;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_qty_real($brg_penjualan_qty_real){
        if($brg_penjualan_qty_real != ""){
            $this->brg_penjualan_qty_real = $brg_penjualan_qty_real;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_satuan_real($brg_penjualan_satuan_real){
        if($brg_penjualan_satuan_real != ""){
            $this->brg_penjualan_satuan_real = $brg_penjualan_satuan_real;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_qty($brg_penjualan_qty){
        if($brg_penjualan_qty != ""){
            $this->brg_penjualan_qty = $brg_penjualan_qty;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_satuan($brg_penjualan_satuan){
        if($brg_penjualan_satuan != ""){
            $this->brg_penjualan_satuan = $brg_penjualan_satuan;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_harga($brg_penjualan_harga){
        if($brg_penjualan_harga != ""){
            $this->brg_penjualan_harga = $brg_penjualan_harga;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_note($brg_penjualan_note){
        if($brg_penjualan_note != ""){
            $this->brg_penjualan_note = $brg_penjualan_note;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_status($brg_penjualan_status){
        if($brg_penjualan_status != ""){
            $this->brg_penjualan_status = $brg_penjualan_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_penjualan($id_fk_penjualan){
        if($id_fk_penjualan != ""){
            $this->id_fk_penjualan = $id_fk_penjualan;
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
    public function get_id_pk_brg_penjualan(){
        return $this->id_pk_brg_penjualan;
    }
    public function get_brg_penjualan_qty_real(){
        return $this->brg_penjualan_qty_real;
    }
    public function get_brg_penjualan_satuan_real(){
        return $this->brg_penjualan_satuan_real;
    }
    public function get_brg_penjualan_qty(){
        return $this->brg_penjualan_qty;
    }
    public function get_brg_penjualan_satuan(){
        return $this->brg_penjualan_satuan;
    }
    public function get_brg_penjualan_harga(){
        return $this->brg_penjualan_harga;
    }
    public function get_brg_penjualan_note(){
        return $this->brg_penjualan_note;
    }
    public function get_brg_penjualan_status(){
        return $this->brg_penjualan_note;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
    public function get_id_fk_barang(){
        return $this->id_fk_barang;
    }
}