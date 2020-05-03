<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_penjualan_add extends CI_Model{
    private $tbl_name = "TBL_PENJUALAN_ADD";
    private $columns = array();
    private $id_pk_penjualan_add;
    private $penj_add_attr;
    private $penj_add_harga;
    private $penj_add_status;
    private $penj_add_create_date;
    private $penj_add_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->penj_add_create_date = date("Y-m-d H:i:s");
        $this->penj_add_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS TBL_PENJUALAN_ADD;
        CREATE TABLE TBL_PENJUALAN_ADD(
            ID_PK_PENJUALAN_ADD INT PRIMARY KEY AUTO_INCREMENT,
            PENJ_ADD_ATTR VARCHAR(100),
            PENJ_ADD_HARGA INT,
            PENJ_ADD_STATUS VARCHAR(15),
            PENJ_ADD_CREATE_DATE DATETIME,
            PENJ_ADD_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_PENJUALAN_ADD_LOG;
        CREATE TABLE TBL_PENJUALAN_ADD_LOG(
            ID_PK_PENJUALAN_ADD_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_PENJUALAN_ADD INT,
            PENJ_ADD_ATTR VARCHAR(100),
            PENJ_ADD_HARGA INT,
            PENJ_ADD_STATUS VARCHAR(15),
            PENJ_ADD_CREATE_DATE DATETIME,
            PENJ_ADD_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_PENJUALAN_ADD;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_PENJUALAN_ADD
        AFTER INSERT ON TBL_PENJUALAN_ADD
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENJ_ADD_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.PENJ_ADD_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_PENJUALAN_ADD_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN_ADD,PENJ_ADD_ATTR,PENJ_ADD_HARGA,PENJ_ADD_STATUS,PENJ_ADD_CREATE_DATE,PENJ_ADD_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_PENJUALAN_ADD,NEW.PENJ_ADD_ATTR,NEW.PENJ_ADD_HARGA,NEW.PENJ_ADD_STATUS,NEW.PENJ_ADD_CREATE_DATE,NEW.PENJ_ADD_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_PENJUALAN_ADD;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_PENJUALAN_ADD
        AFTER UPDATE ON TBL_PENJUALAN_ADD
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENJ_ADD_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.PENJ_ADD_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_PENJUALAN_ADD_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN_ADD,PENJ_ADD_ATTR,PENJ_ADD_HARGA,PENJ_ADD_STATUS,PENJ_ADD_CREATE_DATE,PENJ_ADD_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_PENJUALAN_ADD,NEW.PENJ_ADD_ATTR,NEW.PENJ_ADD_HARGA,NEW.PENJ_ADD_STATUS,NEW.PENJ_ADD_CREATE_DATE,NEW.PENJ_ADD_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penj_add_attr" => $this->penj_add_attr,
                "penj_add_harga" => $this->penj_add_harga,
                "penj_add_status" => $this->penj_add_status,
                "penj_add_create_date" => $this->penj_add_create_date,
                "penj_add_last_modified" => $this->penj_add_last_modified,
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
                "id_pk_penjualan_add" => $this->id_pk_penjualan_add
            );
            $data = array(
                "penj_add_attr" => $this->penj_add_attr,
                "penj_add_harga" => $this->penj_add_harga,
                "penj_add_last_modified" => $this->penj_add_last_modified,
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
                "id_pk_penjualan_add" => $this->id_pk_penjualan_add
            );
            $data = array(
                "penj_add_status" => "NONAKTIF",
                "penj_add_last_modified" => $this->penj_add_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->penj_add_attr == ""){
            return false;
        }
        if($this->penj_add_harga == ""){
            return false;
        }
        if($this->penj_add_status == ""){
            return false;
        }
        if($this->penj_add_create_date == ""){
            return false;
        }
        if($this->penj_add_last_modified == ""){
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

        if($this->id_pk_penjualan_add == ""){
            return false;
        }
        if($this->penj_add_attr == ""){
            return false;
        }
        if($this->penj_add_harga == ""){
            return false;
        }
        if($this->penj_add_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_penjualan_add == ""){
            return false;
        }
        if($this->penj_add_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($penj_add_attr,$penj_add_harga,$penj_add_status){
        if(!$this->set_penj_add_attr($penj_add_attr)){
            return false;
        }
        if(!$this->set_penj_add_harga($penj_add_harga)){
            return false;
        }
        if(!$this->set_penj_add_status($penj_add_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_penjualan_add,$penj_add_attr,$penj_add_harga){
        if(!$this->set_id_pk_penjualan_add($id_pk_penjualan_add)){
            return false;
        }
        if(!$this->set_penj_add_attr($penj_add_attr)){
            return false;
        }
        if(!$this->set_penj_add_harga($penj_add_harga)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_penjualan_add){
        if(!$this->set_id_pk_penjualan_add($id_pk_penjualan_add)){
            return false;
        }
        return true;}
    public function get_id_pk_penjualan_add(){
        return $this->id_pk_penjualan_add;
    }
    public function get_penj_add_attr(){
        return $this->penj_add_attr;
    }
    public function get_penj_add_harga(){
        return $this->penj_add_harga;
    }
    public function get_penj_add_status(){
        return $this->penj_add_status;
    }
    public function set_id_pk_penjualan_add($id_pk_penjualan_add){
        if($id_pk_penjualan_add != ""){
            $this->id_pk_penjualan_add = $id_pk_penjualan_add;
            return true;
        }
        return false;
    }
    public function set_penj_add_attr($penj_add_attr){
        if($penj_add_attr != ""){
            $this->penj_add_attr = $penj_add_attr;
            return true;
        }
        return false;
    }
    public function set_penj_add_harga($penj_add_harga){
        if($penj_add_harga != ""){
            $this->penj_add_harga = $penj_add_harga;
            return true;
        }
        return false;
    }
    public function set_penj_add_status($penj_add_status){
        if($penj_add_status != ""){
            $this->penj_add_status = $penj_add_status;
            return true;
        }
        return false;
    }
}