<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_retur_kembali extends ci_model{
    private $tbl_name = "tbl_retur_kembali";
    private $columns = array();
    private $id_pk_retur_kembali;
    private $id_fk_retur;
    private $id_fk_brg;
    private $retur_kembali_qty_real;
    private $retur_kembali_qty;
    private $retur_kembali_satuan;
    private $retur_kembali_harga;
    private $retur_kembali_note;
    private $retur_kembali_status;
    private $retur_kembali_create_date;
    private $retur_kembali_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->retur_kembali_create_date = date("y-m-d h:i:s");
        $this->retur_kembali_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists tbl_retur_kembali;
        create table tbl_retur_kembali(
            id_pk_retur_kembali int primary key auto_increment,
            retur_kembali_qty_real double,
            retur_kembali_satuan_real varchar(20),
            retur_kembali_qty double,
            retur_kembali_satuan varchar(20),
            retur_kembali_harga int,
            retur_kembali_note varchar(150),
            retur_kembali_status varchar(15),
            id_fk_retur int,
            id_fk_brg int,
            retur_kembali_create_date datetime,
            retur_kembali_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_retur_kembali_log;
        create table tbl_retur_kembali_log(
            id_pk_retur_kembali_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_retur_kembali int,
            retur_kembali_qty_real double,
            retur_kembali_satuan_real varchar(20),
            retur_kembali_qty double,
            retur_kembali_satuan varchar(20),
            retur_kembali_harga int,
            retur_kembali_note varchar(150),
            retur_kembali_status varchar(15),
            id_fk_retur int,
            id_fk_brg int,
            retur_kembali_create_date datetime,
            retur_kembali_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_retur_kembali;
        delimiter $$
        create trigger trg_after_insert_retur_kembali
        after insert on tbl_retur_kembali
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty_real,retur_kembali_satuan_real,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_retur_kembali,new.retur_kembali_qty_real,new.retur_kembali_satuan_real,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_retur_kembali;
        delimiter $$
        create trigger trg_after_update_retur_kembali
        after update on tbl_retur_kembali
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty_real,retur_kembali_satuan_real,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_retur_kembali,new.retur_kembali_qty_real,new.retur_kembali_satuan_real,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function list(){
        $sql = "
        select id_pk_retur_kembali,retur_kembali_qty_real,retur_kembali_satuan_real,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,id_fk_retur,id_fk_brg,brg_nama,brg_harga,retur_kembali_create_date,retur_kembali_last_modified
        from ".$this->tbl_name."
        inner join mstr_barang on mstr_barang.id_pk_brg = ".$this->tbl_name.".id_fk_brg
        inner join mstr_retur on mstr_retur.id_pk_retur = ".$this->tbl_name.".id_fk_retur
        where retur_kembali_status = ? and id_fk_retur = ? and retur_tipe = 'barang'
        ";
        $args = array(
            "aktif",$this->id_fk_retur
        );
        return executequery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "retur_kembali_qty_real" => $this->retur_kembali_qty_real,
                "retur_kembali_satuan_real" => $this->retur_kembali_satuan_real,
                "retur_kembali_qty" => $this->retur_kembali_qty,
                "retur_kembali_satuan" => $this->retur_kembali_satuan,
                "retur_kembali_harga" => $this->retur_kembali_harga,
                "retur_kembali_note" => $this->retur_kembali_note,
                "retur_kembali_status" => $this->retur_kembali_status,
                "id_fk_retur" => $this->id_fk_retur,
                "id_fk_brg" => $this->id_fk_brg,
                "retur_kembali_create_date" => $this->retur_kembali_create_date,
                "retur_kembali_last_modified" => $this->retur_kembali_last_modified,
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
                "id_pk_retur_kembali" => $this->id_pk_retur_kembali,
            );
            $data = array(
                "retur_kembali_qty_real" => $this->retur_kembali_qty_real,
                "retur_kembali_satuan_real" => $this->retur_kembali_satuan_real,
                "retur_kembali_qty" => $this->retur_kembali_qty,
                "retur_kembali_satuan" => $this->retur_kembali_satuan,
                "retur_kembali_harga" => $this->retur_kembali_harga,
                "retur_kembali_note" => $this->retur_kembali_note,
                "id_fk_brg" => $this->id_fk_brg,
                "retur_kembali_last_modified" => $this->retur_kembali_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_retur_kembali" => $this->id_pk_retur_kembali,
            );
            $data = array(
                "retur_kembali_status" => "nonaktif",
                "retur_kembali_last_modified" => $this->retur_kembali_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->retur_kembali_qty_real == ""){
            return false;
        }
        if($this->retur_kembali_satuan_real == ""){
            return false;
        }
        if($this->retur_kembali_qty == ""){
            return false;
        }
        if($this->retur_kembali_satuan == ""){
            return false;
        }
        if($this->retur_kembali_harga == ""){
            return false;
        }
        if($this->retur_kembali_note == ""){
            return false;
        }
        if($this->retur_kembali_status == ""){
            return false;
        }
        if($this->id_fk_retur == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->retur_kembali_create_date == ""){
            return false;
        }
        if($this->retur_kembali_last_modified == ""){
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

        if($this->id_pk_retur_kembali == ""){
            return false;
        }
        if($this->retur_kembali_qty_real == ""){
            return false;
        }
        if($this->retur_kembali_satuan_real == ""){
            return false;
        }
        if($this->retur_kembali_qty == ""){
            return false;
        }
        if($this->retur_kembali_satuan == ""){
            return false;
        }
        if($this->retur_kembali_harga == ""){
            return false;
        }
        if($this->retur_kembali_note == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->retur_kembali_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_retur_kembali == ""){
            return false;
        }
        if($this->retur_kembali_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($retur_kembali_qty_real,$retur_kembali_satuan_real,$retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$retur_kembali_status,$id_fk_retur,$id_fk_brg){
        if(!$this->set_retur_kembali_qty_real($retur_kembali_qty_real)){
            return false;
        }
        if(!$this->set_retur_kembali_qty($retur_kembali_qty)){
            return false;
        }
        if(!$this->set_retur_kembali_satuan_real($retur_kembali_satuan_real)){
            return false;
        }
        if(!$this->set_retur_kembali_qty($retur_kembali_qty)){
            return false;
        }
        if(!$this->set_retur_kembali_satuan($retur_kembali_satuan)){
            return false;
        }
        if(!$this->set_retur_kembali_harga($retur_kembali_harga)){
            return false;
        }
        if(!$this->set_retur_kembali_note($retur_kembali_note)){
            return false;
        }
        if(!$this->set_retur_kembali_status($retur_kembali_status)){
            return false;
        }
        if(!$this->set_id_fk_retur($id_fk_retur)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_retur_kembali,$retur_kembali_qty_real,$retur_kembali_satuan_real,$retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$id_fk_brg){
        if(!$this->set_id_pk_retur_kembali($id_pk_retur_kembali)){
            return false;
        }
        if(!$this->set_retur_kembali_qty_real($retur_kembali_qty_real)){
            return false;
        }
        if(!$this->set_retur_kembali_satuan_real($retur_kembali_satuan_real)){
            return false;
        }
        if(!$this->set_retur_kembali_qty($retur_kembali_qty)){
            return false;
        }
        if(!$this->set_retur_kembali_satuan($retur_kembali_satuan)){
            return false;
        }
        if(!$this->set_retur_kembali_harga($retur_kembali_harga)){
            return false;
        }
        if(!$this->set_retur_kembali_note($retur_kembali_note)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_retur_kembali){
        if($this->set_id_pk_retur_kembali($id_pk_retur_kembali)){
            return false;
        }
        return true;
    }
    public function set_id_pk_retur_kembali($id_pk_retur_kembali){
        if($id_pk_retur_kembali != ""){
            $this->id_pk_retur_kembali = $id_pk_retur_kembali;
            return true;
        }
        return false;
    }
    public function set_retur_kembali_qty_real($retur_kembali_qty_real){
        if($retur_kembali_qty_real != ""){
            $this->retur_kembali_qty_real = $retur_kembali_qty_real;
            return true;
        }
        return false;
    }
    public function set_retur_kembali_satuan_real($retur_kembali_satuan_real){
        if($retur_kembali_satuan_real != ""){
            $this->retur_kembali_satuan_real = $retur_kembali_satuan_real;
            return true;
        }
        return false;
    }
    public function set_retur_kembali_qty($retur_kembali_qty){
        if($retur_kembali_qty != ""){
            $this->retur_kembali_qty = $retur_kembali_qty;
            return true;
        }
        return false;
    }
    public function set_retur_kembali_satuan($retur_kembali_satuan){
        if($retur_kembali_satuan != ""){
            $this->retur_kembali_satuan = $retur_kembali_satuan;
            return true;
        }
        return false;
    }
    public function set_retur_kembali_harga($retur_kembali_harga){
        if($retur_kembali_harga != ""){
            $this->retur_kembali_harga = $retur_kembali_harga;
            return true;
        }
        return false;
    }
    public function set_retur_kembali_note($retur_kembali_note){
        if($retur_kembali_note != ""){
            $this->retur_kembali_note = $retur_kembali_note;
            return true;
        }
        return false;
    }
    public function set_retur_kembali_status($retur_kembali_status){
        if($retur_kembali_status != ""){
            $this->retur_kembali_status = $retur_kembali_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_retur($id_fk_retur){
        if($id_fk_retur != ""){
            $this->id_fk_retur = $id_fk_retur;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg($id_fk_brg){
        if($id_fk_brg != ""){
            $this->id_fk_brg = $id_fk_brg;
            return true;
        }
        return false;
    }
    public function get_id_pk_retur_kembali(){
        return $this->id_pk_retur_kembali;
    }
    public function get_retur_kembali_qty_real(){
        return $this->retur_kembali_qty_real;
    }
    public function get_retur_kembali_satuan_real(){
        return $this->retur_kembali_satuan_real;
    }
    public function get_retur_kembali_qty(){
        return $this->retur_kembali_qty;
    }
    public function get_retur_kembali_satuan(){
        return $this->retur_kembali_satuan;
    }
    public function get_retur_kembali_harga(){
        return $this->retur_kembali_harga;
    }
    public function get_retur_kembali_note(){
        return $this->retur_kembali_note;
    }
    public function get_retur_kembali_status(){
        return $this->retur_kembali_note;
    }
    public function get_id_fk_retur(){
        return $this->id_fk_retur;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
}