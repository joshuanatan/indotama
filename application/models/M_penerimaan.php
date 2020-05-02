<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_penerimaan extends CI_Model{
    private $tbl_name = "MSTR_PENERIMAAN";
    private $columns = array();
    private $id_pk_penerimaan;
    private $penerimaan_tgl;
    private $penerimaan_status;
    private $id_fk_pembelian;
    private $penerimaan_create_date;
    private $penerimaan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->columns = array();
        $penerimaan_create_date = date("Y-m-d H:i:s");
        $penerimaan_last_modified = date("Y-m-d H:i:s");
        $id_create_data = $this->session->id_user;
        $id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS MSTR_PENERIMAAN;
        CREATE TABLE MSTR_PENERIMAAN(
            ID_PK_PENERIMAAN INT PRIMARY KEY AUTO_INCREMENT,
            PENERIMAAN_TGL DATETIME,
            PENERIMAAN_STATUS VARCHAR(15),
            ID_FK_PENERIMAAN INT,
            PENERIMAAN_CREATE_DATE DATETIME,
            PENERIMAAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_PENERIMAAN_LOG;
        CREATE TABLE MSTR_PENERIMAAN_LOG(
            ID_PK_PENERIMAAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_PENERIMAAN INT,
            PENERIMAAN_TGL DATETIME,
            PENERIMAAN_STATUS VARCHAR(15),
            ID_FK_PENERIMAAN INT,
            PENERIMAAN_CREATE_DATE DATETIME,
            PENERIMAAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_PENERIMAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_PENERIMAAN
        AFTER INSERT ON MSTR_PENERIMAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENERIMAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.PENERIMAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PENERIMAAN_LOG(EXECUTED_FUNCTION,ID_PK_PENERIMAAN,PENERIMAAN_TGL,PENERIMAAN_STATUS,ID_FK_PENERIMAAN,PENERIMAAN_CREATE_DATE,PENERIMAAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_PENERIMAAN,NEW.PENERIMAAN_TGL,NEW.PENERIMAAN_STATUS,NEW.ID_FK_PENERIMAAN,NEW.PENERIMAAN_CREATE_DATE,NEW.PENERIMAAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_PENERIMAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_PENERIMAAN
        AFTER UPDATE ON MSTR_PENERIMAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENERIMAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.PENERIMAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PENERIMAAN_LOG(EXECUTED_FUNCTION,ID_PK_PENERIMAAN,PENERIMAAN_TGL,PENERIMAAN_STATUS,ID_FK_PENERIMAAN,PENERIMAAN_CREATE_DATE,PENERIMAAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_PENERIMAAN,NEW.PENERIMAAN_TGL,NEW.PENERIMAAN_STATUS,NEW.ID_FK_PENERIMAAN,NEW.PENERIMAAN_CREATE_DATE,NEW.PENERIMAAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penerimaan_tgl" => $this->penerimaan_tgl,
                "penerimaan_status" => $this->penerimaan_status,
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "penerimaan_create_date" => $this->penerimaan_create_date,
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
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
                "id_pk_penerimaan" => $this->id_pk_penerimaan
            );
            $data = array(
                "penerimaan_tgl" => $this->penerimaan_tgl,
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
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
                "id_pk_penerimaan" => $this->id_pk_penerimaan
            );
            $data = array(
                "penerimaan_status" => "NONAKTIF",
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->penerimaan_tgl == ""){
            return false;
        }
        if($this->penerimaan_status == ""){
            return false;
        }
        if($this->id_fk_pembelian == ""){
            return false;
        }
        if($this->penerimaan_create_date == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
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
        if($this->id_pk_penerimaan == ""){
            return false;
        }
        if($this->penerimaan_tgl == ""){
            return false;
        }
        if($this->id_fk_pembelian == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_penerimaan == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($penerimaan_tgl,$penerimaan_status,$id_fk_pembelian){
        if(!$this->set_penerimaan_tgl($penerimaan_tgl)){
            return false;
        }
        if(!$this->set_penerimaan_status($penerimaan_status)){
            return false;
        }
        if(!$this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_penerimaan,$penerimaan_tgl,$id_fk_pembelian){
        if(!$this->set_id_pk_penerimaan($id_pk_penerimaan)){
            return false;
        }
        if(!$this->set_penerimaan_tgl($penerimaan_tgl)){
            return false;
        }
        if(!$this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_penerimaan){
        if(!$this->set_id_pk_penerimaan($id_pk_penerimaan)){
            return false;
        }

        return true;
    }
    public function set_id_pk_penerimaan($id_pk_penerimaan){
        if($id_pk_penerimaan != ""){
            $this->id_pk_penerimaan = $id_pk_penerimaan;
            return true;
        }
        return false;
    }
    public function set_penerimaan_tgl($penerimaan_tgl){
        if($penerimaan_tgl != ""){
            $this->penerimaan_tgl = $penerimaan_tgl;
            return true;
        }
        return false;
    }
    public function set_penerimaan_status($penerimaan_status){
        if($penerimaan_status != ""){
            $this->penerimaan_status = $penerimaan_status;
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
    public function get_id_pk_penerimaan(){
        return $this->id_pk_penerimaan;
    }
    public function get_penerimaan_tgl(){
        return $this->penerimaan_tgl;
    }
    public function get_penerimaan_status(){
        return $this->penerimaan_status;
    }
    public function get_id_fk_pembelian(){
        return $this->id_fk_pembelian;
    }
}