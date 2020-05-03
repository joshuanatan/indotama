<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_surat_jalan extends CI_Model{
    private $tbl_name = "MSTR_SURAT_JALAN";
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
        $this->sj_create_date = date("Y-m-d H:i:s");
        $this->sj_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS MSTR_SURAT_JALAN;
        CREATE TABLE MSTR_SURAT_JALAN(
            ID_PK_SURAT_JALAN INT PRIMARY KEY AUTO_INCREMENT,
            SJ_NOMOR VARCHAR(30),
            SJ_TGL DATETIME,
            SJ_PENERIMA VARCHAR(100),
            SJ_PENGIRIM VARCHAR(100),
            SJ_ACC VARCHAR(50),
            SJ_NOTE VARCHAR(150),
            SJ_NO_PENJUALAN VARCHAR(100),
            SJ_JMLH_ITEM DOUBLE,
            SJ_TUJUAN VARCHAR(150),
            SJ_ALAMAT VARCHAR(150),
            SJ_STATUS VARCHAR(15),
            ID_FK_PENJUALAN INT,
            SJ_CREATE_DATE DATETIME,
            SJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_SURAT_JALAN_LOG;
        CREATE TABLE MSTR_SURAT_JALAN_LOG(
            ID_PK_SURAT_JALAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_SURAT_JALAN INT,
            SJ_NOMOR VARCHAR(30),
            SJ_TGL DATETIME,
            SJ_PENERIMA VARCHAR(100),
            SJ_PENGIRIM VARCHAR(100),
            SJ_ACC VARCHAR(50),
            SJ_NOTE VARCHAR(150),
            SJ_NO_PENJUALAN VARCHAR(100),
            SJ_JMLH_ITEM DOUBLE,
            SJ_TUJUAN VARCHAR(150),
            SJ_ALAMAT VARCHAR(150),
            SJ_STATUS VARCHAR(15),
            ID_FK_PENJUALAN INT,
            SJ_CREATE_DATE DATETIME,
            SJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_SURAT_JALAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_SURAT_JALAN
        AFTER INSERT ON MSTR_SURAT_JALAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_SURAT_JALAN_LOG(EXECUTED_FUNCTION,ID_PK_SURAT_JALAN,SJ_NOMOR,SJ_TGL,SJ_PENERIMA,SJ_PENGIRIM,SJ_ACC,SJ_NOTE,SJ_NO_PENJUALAN,SJ_JMLH_ITEM,SJ_TUJUAN,SJ_ALAMAT,SJ_STATUS,ID_FK_PENJUALAN,SJ_CREATE_DATE,SJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SURAT_JALAN,NEW.SJ_NOMOR,NEW.SJ_TGL,NEW.SJ_PENERIMA,NEW.SJ_PENGIRIM,NEW.SJ_ACC,NEW.SJ_NOTE,NEW.SJ_NO_PENJUALAN,NEW.SJ_JMLH_ITEM,NEW.SJ_TUJUAN,NEW.SJ_ALAMAT,NEW.SJ_STATUS,NEW.ID_FK_PENJUALAN,NEW.SJ_CREATE_DATE,NEW.SJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_SURAT_JALAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_SURAT_JALAN
        AFTER UPDATE ON MSTR_SURAT_JALAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_SURAT_JALAN_LOG(EXECUTED_FUNCTION,ID_PK_SURAT_JALAN,SJ_NOMOR,SJ_TGL,SJ_PENERIMA,SJ_PENGIRIM,SJ_ACC,SJ_NOTE,SJ_NO_PENJUALAN,SJ_JMLH_ITEM,SJ_TUJUAN,SJ_ALAMAT,SJ_STATUS,ID_FK_PENJUALAN,SJ_CREATE_DATE,SJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SURAT_JALAN,NEW.SJ_NOMOR,NEW.SJ_TGL,NEW.SJ_PENERIMA,NEW.SJ_PENGIRIM,NEW.SJ_ACC,NEW.SJ_NOTE,NEW.SJ_NO_PENJUALAN,NEW.SJ_JMLH_ITEM,NEW.SJ_TUJUAN,NEW.SJ_ALAMAT,NEW.SJ_STATUS,NEW.ID_FK_PENJUALAN,NEW.SJ_CREATE_DATE,NEW.SJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
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
