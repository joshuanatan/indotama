<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_toko extends CI_Model{
    private $tbl_name = "MSTR_TOKO";
    private $columns = array();
    private $id_pk_toko;
    private $toko_nama;
    private $toko_kode;
    private $toko_status;
    private $toko_create_date;
    private $toko_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->toko_create_date = date("Y-m-d H:i:s");
        $this->toko_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;        
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS MSTR_TOKO;
        CREATE TABLE MSTR_TOKO(
            ID_PK_TOKO INT PRIMARY KEY AUTO_INCREMENT,
            TOKO_NAMA VARCHAR(100),
            TOKO_KODE VARCHAR(20),
            TOKO_STATUS VARCHAR(15),
            TOKO_CREATE_DATE DATETIME,
            TOKO_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_TOKO_LOG;
        CREATE TABLE MSTR_TOKO_LOG(
            ID_PK_TOKO_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_TOKO INT,
            TOKO_NAMA VARCHAR(100),
            TOKO_KODE VARCHAR(20),
            TOKO_STATUS VARCHAR(15),
            TOKO_CREATE_DATE DATETIME,
            TOKO_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_TOKO;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_TOKO
        AFTER INSERT ON MSTR_TOKO
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.TOKO_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.TOKO_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_TOKO_LOG(EXECUTED_FUNCTION,ID_PK_TOKO,TOKO_NAMA,TOKO_KODE,TOKO_STATUS,TOKO_CREATE_DATE,TOKO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_TOKO,NEW.TOKO_NAMA,NEW.TOKO_KODE,NEW.TOKO_STATUS,NEW.TOKO_CREATE_DATE,NEW.TOKO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_TOKO;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_TOKO
        AFTER UPDATE ON MSTR_TOKO
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.TOKO_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.TOKO_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_TOKO_LOG(EXECUTED_FUNCTION,ID_PK_TOKO,TOKO_NAMA,TOKO_KODE,TOKO_STATUS,TOKO_CREATE_DATE,TOKO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_TOKO,NEW.TOKO_NAMA,NEW.TOKO_KODE,NEW.TOKO_STATUS,NEW.TOKO_CREATE_DATE,NEW.TOKO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "toko_nama" => $this->toko_nama, 
                "toko_kode" => $this->toko_kode, 
                "toko_status" => $this->toko_status, 
                "toko_create_date" => $this->toko_create_date, 
                "toko_last_modified" => $this->toko_last_modified, 
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
                "id_pk_toko" => $this->id_pk_toko
            );
            $data = array(
                "toko_nama" => $this->toko_nama, 
                "toko_kode" => $this->toko_kode, 
                "toko_last_modified" => $this->toko_last_modified, 
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
                "id_pk_toko" => $this->id_pk_toko
            );
            $data = array(
                "toko_status" => "NONAKTIF", 
                "toko_last_modified" => $this->toko_last_modified, 
                "id_last_modified" => $this->id_last_modified, 
            );
            updateRow($this->tbl_name,$data,$where);
        }
        return false;
    }
    public function check_insert(){
        if($this->toko_nama == ""){
            return false;
        }
        if($this->toko_kode == ""){
            return false;
        }
        if($this->toko_status == ""){
            return false;
        }
        if($this->toko_create_date == ""){
            return false;
        }
        if($this->toko_last_modified == ""){
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
        if($this->toko_nama == ""){
            return false;
        }
        if($this->toko_kode == ""){
            return false;
        }
        if($this->toko_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->toko_nama == ""){
            return false;
        }
        if($this->toko_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($toko_nama,$toko_kode,$toko_status){
        if(!$this->set_toko_nama($toko_nama)){
            return false;
        }
        if(!$this->set_toko_kode($toko_kode)){
            return false;
        }
        if(!$this->set_toko_status($toko_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_toko,$toko_nama,$toko_kode){
        if(!$this->set_id_pk_toko($id_pk_toko)){
            return false;
        }
        if(!$this->set_toko_nama($toko_nama)){
            return false;
        }
        if(!$this->set_toko_kode($toko_kode)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_toko){
        if(!$this->set_id_pk_toko($id_pk_toko)){
            return false;
        }
        return true;
    }
    public function get_id_pk_toko(){
        return $this->id_pk_toko;
    }
    public function get_toko_nama(){
        return $this->toko_nama;
    }
    public function get_toko_kode(){
        return $this->toko_kode;
    }
    public function get_toko_status(){
        return $this->toko_status;
    }
    public function set_id_pk_toko($id_pk_toko){
        if($id_pk_toko != ""){
            $this->id_pk_toko = $id_pk_toko;
            return true;
        }
        return false;
    }
    public function set_toko_nama($toko_nama){
        if($toko_nama != ""){
            $this->toko_nama = $toko_nama;
            return true;
        }
        return false;
    }
    public function set_toko_kode($toko_kode){
        if($toko_kode != ""){
            $this->toko_kode = $toko_kode;
            return true;
        }
        return false;
    }
    public function set_toko_status($toko_status){
        if($toko_status != ""){
            $this->toko_status = $toko_status;
            return true;
        }
        return false;
    }
}