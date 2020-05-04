<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_sj_item extends CI_Model{
    private $tbl_name = "TBL_SJ_ITEM";
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
        $this->sj_item_create_date = date("Y-m-d H:i:s");
        $this->sj_item_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS TBL_SJ_ITEM;
        CREATE TABLE TBL_SJ_ITEM(
            ID_PK_SJ_ITEM INT PRIMARY KEY AUTO_INCREMENT,
            SJ_ITEM_QTY DOUBLE,
            SJ_ITEM_NOTE VARCHAR(150),
            SJ_ITEM_STATUS VARCHAR(15),
            ID_FK_SATUAN INT,
            ID_FK_SURAT_JALAN INT,
            ID_FK_BRG_PENJUALAN INT,
            SJ_ITEM_CREATE_DATE DATETIME,
            SJ_ITEM_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_SJ_ITEM_LOG;
        CREATE TABLE TBL_SJ_ITEM_LOG(
            ID_PK_SJ_ITEM_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_SJ_ITEM INT,
            SJ_ITEM_QTY DOUBLE,
            SJ_ITEM_NOTE VARCHAR(150),
            SJ_ITEM_STATUS VARCHAR(15),
            ID_FK_SATUAN INT,
            ID_FK_SURAT_JALAN INT,
            ID_FK_BRG_PENJUALAN INT,
            SJ_ITEM_CREATE_DATE DATETIME,
            SJ_ITEM_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_SJ_ITEM;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_SJ_ITEM
        AFTER INSERT ON TBL_SJ_ITEM
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SJ_ITEM_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SJ_ITEM_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_SJ_ITEM_LOG(EXECUTED_FUNCTION,ID_PK_SJ_ITEM,SJ_ITEM_QTY,SJ_ITEM_NOTE,SJ_ITEM_STATUS,ID_FK_SATUAN,ID_FK_SURAT_JALAN,ID_FK_BRG_PENJUALAN,SJ_ITEM_CREATE_DATE,SJ_ITEM_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SJ_ITEM,NEW.SJ_ITEM_QTY,NEW.SJ_ITEM_NOTE,NEW.SJ_ITEM_STATUS,NEW.ID_FK_SATUAN,NEW.ID_FK_SURAT_JALAN,NEW.ID_FK_BRG_PENJUALAN,NEW.SJ_ITEM_CREATE_DATE,NEW.SJ_ITEM_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_SJ_ITEM;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_SJ_ITEM
        AFTER UPDATE ON TBL_SJ_ITEM
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SJ_ITEM_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SJ_ITEM_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_SJ_ITEM_LOG(EXECUTED_FUNCTION,ID_PK_SJ_ITEM,SJ_ITEM_QTY,SJ_ITEM_NOTE,SJ_ITEM_STATUS,ID_FK_SATUAN,ID_FK_SURAT_JALAN,ID_FK_BRG_PENJUALAN,SJ_ITEM_CREATE_DATE,SJ_ITEM_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SJ_ITEM,NEW.SJ_ITEM_QTY,NEW.SJ_ITEM_NOTE,NEW.SJ_ITEM_STATUS,NEW.ID_FK_SATUAN,NEW.ID_FK_SURAT_JALAN,NEW.ID_FK_BRG_PENJUALAN,NEW.SJ_ITEM_CREATE_DATE,NEW.SJ_ITEM_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
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
            return insertRow($this->tbl_name,$data);
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
            updateRow($this->tbl_name,$data,$where);
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
                "sj_item_status" => "NONAKTIF",
                "sj_item_last_modified" => $this->sj_item_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
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