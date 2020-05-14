<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_tambahan_pembelian extends CI_Model{
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
        $this->tmbhn_create_date = date("Y-m-d H:i:s");
        $this->tmbhn_last_modified = $this->session->id_user;
        $this->id_create_data = date("Y-m-d H:i:s");
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS TBL_TAMBAHAN_PEMBELIAN;
        CREATE TABLE TBL_TAMBAHAN_PEMBELIAN(
            ID_PK_TMBHN INT PRIMARY KEY AUTO_INCREMENT,
            TMBHN VARCHAR(100),
            TMBHN_JUMLAH DOUBLE,
            TMBHN_SATUAN VARCHAR(20),
            TMBHN_HARGA INT,
            TMBHN_NOTES VARCHAR(200),
            TMBHN_STATUS VARCHAR(15),
            ID_FK_PEMBELIAN INT,
            TMBHN_CREATE_DATE DATETIME,
            TMBHN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_TAMBAHAN_PEMBELIAN_LOG;
        CREATE TABLE TBL_TAMBAHAN_PEMBELIAN_LOG(
            ID_PK_TMBHN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_TMBHN INT,
            TMBHN VARCHAR(100),
            TMBHN_JUMLAH DOUBLE,
            TMBHN_SATUAN VARCHAR(20),
            TMBHN_HARGA INT,
            TMBHN_NOTES VARCHAR(200),
            TMBHN_STATUS VARCHAR(15),
            ID_FK_PEMBELIAN INT,
            TMBHN_CREATE_DATE DATETIME,
            TMBHN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_TAMBAHAN_PEMBELIAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_TAMBAHAN_PEMBELIAN
        AFTER INSERT ON TBL_TAMBAHAN_PEMBELIAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.TMBHN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.TMBHN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_TAMBAHAN_PEMBELIAN_LOG(EXECUTED_FUNCTION,ID_PK_TMBHN,TMBHN,TMBHN_JUMLAH,TMBHN_SATUAN,TMBHN_HARGA,TMBHN_NOTES,TMBHN_STATUS,ID_FK_PEMBELIAN,TMBHN_CREATE_DATE,TMBHN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_TMBHN,NEW.TMBHN,NEW.TMBHN_JUMLAH,NEW.TMBHN_SATUAN,NEW.TMBHN_HARGA,NEW.TMBHN_NOTES,NEW.TMBHN_STATUS,NEW.ID_FK_PEMBELIAN,NEW.TMBHN_CREATE_DATE,NEW.TMBHN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_TAMBAHAN_PEMBELIAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_TAMBAHAN_PEMBELIAN
        AFTER UPDATE ON TBL_TAMBAHAN_PEMBELIAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.TMBHN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.TMBHN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_TAMBAHAN_PEMBELIAN_LOG(EXECUTED_FUNCTION,ID_PK_TMBHN,TMBHN,TMBHN_JUMLAH,TMBHN_SATUAN,TMBHN_HARGA,TMBHN_NOTES,TMBHN_STATUS,ID_FK_PEMBELIAN,TMBHN_CREATE_DATE,TMBHN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_TMBHN,NEW.TMBHN,NEW.TMBHN_JUMLAH,NEW.TMBHN_SATUAN,NEW.TMBHN_HARGA,NEW.TMBHN_NOTES,NEW.TMBHN_STATUS,NEW.ID_FK_PEMBELIAN,NEW.TMBHN_CREATE_DATE,NEW.TMBHN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    public function list(){
        $sql = "
        SELECT id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,tmbhn_last_modified
        FROM ".$this->tbl_name."
        WHERE TMBHN_STATUS = ? AND ID_FK_PEMBELIAN = ?";
        $args = array(
            "AKTIF",$this->id_fk_pembelian
        );
        return executeQuery($sql,$args);
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
            return insertRow($this->tbl_name,$data);
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
            updateRow($this->tbl_name,$data,$where);
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
                "tmbhn_status" => "NONAKTIF", 
                "tmbhn_last_modified" => $this->tmbhn_last_modified, 
                "id_last_modified" => $this->id_last_modified, 
            );
            updateRow($this->tbl_name,$data,$where);
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
        return true;}
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