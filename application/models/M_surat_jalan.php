<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_surat_jalan extends ci_model{
    private $tbl_name = "mstr_surat_jalan";
    private $columns = array();
    private $id_pk_surat_jalan;
    private $sj_nomor;
    private $sj_tgl;
    private $sj_penerima;
    private $sj_pengirim;
    private $sj_acc;
    private $sj_note;
    private $sj_no_penjualan;
    private $sj_jmlh_item;
    private $sj_tujuan;
    private $sj_alamat;
    private $sj_status;
    private $id_fk_penjualan;
    private $sj_create_date;
    private $sj_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->sj_create_date = date("y-m-d h:i:s");
        $this->sj_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "drop table if exists mstr_surat_jalan;
        create table mstr_surat_jalan(
            id_pk_surat_jalan int primary key auto_increment,
            sj_nomor varchar(30),
            sj_tgl datetime,
            sj_penerima varchar(100),
            sj_pengirim varchar(100),
            sj_acc varchar(50),
            sj_note varchar(150),
            sj_no_penjualan varchar(100),
            sj_jmlh_item double,
            sj_tujuan varchar(150),
            sj_alamat varchar(150),
            sj_status varchar(15),
            id_fk_penjualan int,
            sj_create_date datetime,
            sj_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_surat_jalan_log;
        create table mstr_surat_jalan_log(
            id_pk_surat_jalan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_surat_jalan int,
            sj_nomor varchar(30),
            sj_tgl datetime,
            sj_penerima varchar(100),
            sj_pengirim varchar(100),
            sj_acc varchar(50),
            sj_note varchar(150),
            sj_no_penjualan varchar(100),
            sj_jmlh_item double,
            sj_tujuan varchar(150),
            sj_alamat varchar(150),
            sj_status varchar(15),
            id_fk_penjualan int,
            sj_create_date datetime,
            sj_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_surat_jalan;
        delimiter $$
        create trigger trg_after_insert_surat_jalan
        after insert on mstr_surat_jalan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.sj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_surat_jalan_log(executed_function,id_pk_surat_jalan,sj_nomor,sj_tgl,sj_penerima,sj_pengirim,sj_acc,sj_note,sj_no_penjualan,sj_jmlh_item,sj_tujuan,sj_alamat,sj_status,id_fk_penjualan,sj_create_date,sj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_surat_jalan,new.sj_nomor,new.sj_tgl,new.sj_penerima,new.sj_pengirim,new.sj_acc,new.sj_note,new.sj_no_penjualan,new.sj_jmlh_item,new.sj_tujuan,new.sj_alamat,new.sj_status,new.id_fk_penjualan,new.sj_create_date,new.sj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_surat_jalan;
        delimiter $$
        create trigger trg_after_update_surat_jalan
        after update on mstr_surat_jalan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.sj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_surat_jalan_log(executed_function,id_pk_surat_jalan,sj_nomor,sj_tgl,sj_penerima,sj_pengirim,sj_acc,sj_note,sj_no_penjualan,sj_jmlh_item,sj_tujuan,sj_alamat,sj_status,id_fk_penjualan,sj_create_date,sj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_surat_jalan,new.sj_nomor,new.sj_tgl,new.sj_penerima,new.sj_pengirim,new.sj_acc,new.sj_note,new.sj_no_penjualan,new.sj_jmlh_item,new.sj_tujuan,new.sj_alamat,new.sj_status,new.id_fk_penjualan,new.sj_create_date,new.sj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function insert(){
        if($this->sj_nomor == ""){
            return false;
        }
        if($this->sj_tgl == ""){
            return false;
        }
        if($this->sj_penerima == ""){
            return false;
        }
        if($this->sj_pengirim == ""){
            return false;
        }
        if($this->sj_acc == ""){
            return false;
        }
        if($this->sj_note == ""){
            return false;
        }
        if($this->sj_no_penjualan == ""){
            return false;
        }
        if($this->sj_jmlh_item == ""){
            return false;
        }
        if($this->sj_tujuan == ""){
            return false;
        }
        if($this->sj_alamat == ""){
            return false;
        }
        if($this->sj_status == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->sj_create_date == ""){
            return false;
        }
        if($this->sj_last_modified == ""){
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
    public function update(){
        if($this->id_pk_surat_jalan == ""){
            return false;
        }
        if($this->sj_nomor == ""){
            return false;
        }
        if($this->sj_tgl == ""){
            return false;
        }
        if($this->sj_penerima == ""){
            return false;
        }
        if($this->sj_pengirim == ""){
            return false;
        }
        if($this->sj_acc == ""){
            return false;
        }
        if($this->sj_note == ""){
            return false;
        }
        if($this->sj_no_penjualan == ""){
            return false;
        }
        if($this->sj_jmlh_item == ""){
            return false;
        }
        if($this->sj_tujuan == ""){
            return false;
        }
        if($this->sj_alamat == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->sj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function delete(){
        if($this->id_pk_surat_jalan == ""){
            return false;
        }
        if($this->sj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_insert(){}
    public function check_update(){}
    public function check_delete(){}
    public function set_insert($sj_nomor,$sj_tgl,$sj_penerima,$sj_pengirim,$sj_acc,$sj_note,$sj_no_penjualan,$sj_jmlh_item,$sj_tujuan,$sj_alamat,$sj_status,$id_fk_penjualan){
        if(!$this->set_sj_nomor($sj_nomor)){
            return false;
        }
        if(!$this->set_sj_tgl($sj_tgl)){
            return false;
        }
        if(!$this->set_sj_penerima($sj_penerima)){
            return false;
        }
        if(!$this->set_sj_pengirim($sj_pengirim)){
            return false;
        }
        if(!$this->set_sj_acc($sj_acc)){
            return false;
        }
        if(!$this->set_sj_note($sj_note)){
            return false;
        }
        if(!$this->set_sj_no_penjualan($sj_no_penjualan)){
            return false;
        }
        if(!$this->set_sj_jmlh_item($sj_jmlh_item)){
            return false;
        }
        if(!$this->set_sj_tujuan($sj_tujuan)){
            return false;
        }
        if(!$this->set_sj_alamat($sj_alamat)){
            return false;
        }
        if(!$this->set_sj_status($sj_status)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_surat_jalan,$sj_nomor,$sj_tgl,$sj_penerima,$sj_pengirim,$sj_acc,$sj_note,$sj_no_penjualan,$sj_jmlh_item,$sj_tujuan,$sj_alamat,$id_fk_penjualan){
        if(!$this->set_id_pk_surat_jalan($id_pk_surat_jalan)){
            return false;
        }
        if(!$this->set_sj_nomor($sj_nomor)){
            return false;
        }
        if(!$this->set_sj_tgl($sj_tgl)){
            return false;
        }
        if(!$this->set_sj_penerima($sj_penerima)){
            return false;
        }
        if(!$this->set_sj_pengirim($sj_pengirim)){
            return false;
        }
        if(!$this->set_sj_acc($sj_acc)){
            return false;
        }
        if(!$this->set_sj_note($sj_note)){
            return false;
        }
        if(!$this->set_sj_no_penjualan($sj_no_penjualan)){
            return false;
        }
        if(!$this->set_sj_jmlh_item($sj_jmlh_item)){
            return false;
        }
        if(!$this->set_sj_tujuan($sj_tujuan)){
            return false;
        }
        if(!$this->set_sj_alamat($sj_alamat)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_surat_jalan){
        if(!$this->set_id_pk_surat_jalan($id_pk_surat_jalan)){
            return false;
        }
        return true;
    }
    public function get_id_pk_surat_jalan(){
        return $this->id_pk_surat_jalan;
    }
    public function get_sj_nomor(){
        return $this->sj_nomor;
    }
    public function get_sj_tgl(){
        return $this->sj_tgl;
    }
    public function get_sj_penerima(){
        return $this->sj_penerima;
    }
    public function get_sj_pengirim(){
        return $this->sj_pengirim;
    }
    public function get_sj_acc(){
        return $this->sj_acc;
    }
    public function get_sj_note(){
        return $this->sj_note;
    }
    public function get_sj_no_penjualan(){
        return $this->sj_no_penjualan;
    }
    public function get_sj_jmlh_item(){
        return $this->sj_jmlh_item;
    }
    public function get_sj_tujuan(){
        return $this->sj_tujuan;
    }
    public function get_sj_alamat(){
        return $this->sj_alamat;
    }
    public function get_sj_status(){
        return $this->sj_status;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
    public function set_id_pk_surat_jalan($id_pk_surat_jalan){
        if($id_pk_surat_jalan != ""){
            $this->id_pk_surat_jalan = $id_pk_surat_jalan;
            return true;
        }
        return false;
    }
    public function set_sj_nomor($sj_nomor){
        if($sj_nomor != ""){
            $this->sj_nomor = $sj_nomor;
            return true;
        }
        return false;
    }
    public function set_sj_tgl($sj_tgl){
        if($sj_tgl != ""){
            $this->sj_tgl = $sj_tgl;
            return true;
        }
        return false;
    }
    public function set_sj_penerima($sj_penerima){
        if($sj_penerima != ""){
            $this->sj_penerima = $sj_penerima;
            return true;
        }
        return false;
    }
    public function set_sj_pengirim($sj_pengirim){
        if($sj_pengirim != ""){
            $this->sj_pengirim = $sj_pengirim;
            return true;
        }
        return false;
    }
    public function set_sj_acc($sj_acc){
        if($sj_acc != ""){
            $this->sj_acc = $sj_acc;
            return true;
        }
        return false;
    }
    public function set_sj_note($sj_note){
        if($sj_note != ""){
            $this->sj_note = $sj_note;
            return true;
        }
        return false;
    }
    public function set_sj_no_penjualan($sj_no_penjualan){
        if($sj_no_penjualan != ""){
            $this->sj_no_penjualan = $sj_no_penjualan;
            return true;
        }
        return false;
    }
    public function set_sj_jmlh_item($sj_jmlh_item){
        if($sj_jmlh_item != ""){
            $this->sj_jmlh_item = $sj_jmlh_item;
            return true;
        }
        return false;
    }
    public function set_sj_tujuan($sj_tujuan){
        if($sj_tujuan != ""){
            $this->sj_tujuan = $sj_tujuan;
            return true;
        }
        return false;
    }
    public function set_sj_alamat($sj_alamat){
        if($sj_alamat != ""){
            $this->sj_alamat = $sj_alamat;
            return true;
        }
        return false;
    }
    public function set_sj_status($sj_status){
        if($sj_status != ""){
            $this->sj_status = $sj_status;
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
}
