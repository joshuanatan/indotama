<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_so_pj extends CI_Model{
    private $tbl_name = "TBL_SO_PJ";
    private $columns = array();
    private $id_pk_so_pj;
    private $id_fk_stock_opname;
    private $id_fk_emp;
    private $so_pj_create_date;
    private $so_pj_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->so_pj_create_date = date("Y-m-d H:i:s");
        $this->so_pj_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS TBL_SO_PJ;
        CREATE TABLE TBL_SO_PJ(
            ID_PK_SO_PJ INT PRIMARY KEY AUTO_INCREMENT,
            ID_FK_STOCK_OPNAME INT,
            ID_FK_EMP INT,
            SO_PJ_CREATE_DATE DATETIME,
            SO_PJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_SO_PJ_LOG;
        CREATE TABLE TBL_SO_PJ_LOG(
            ID_PK_SO_PJ_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_SO_PJ INT,
            ID_FK_STOCK_OPNAME INT,
            ID_FK_EMP INT,
            SO_PJ_CREATE_DATE DATETIME,
            SO_PJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_SO_PJ;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_SO_PJ
        AFTER INSERT ON TBL_SO_PJ
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SO_PJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SO_PJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_SO_PJ_LOG(EXECUTED_FUNCTION,ID_PK_SO_PJ,ID_FK_STOCK_OPNAME,ID_FK_EMP,SO_PJ_CREATE_DATE,SO_PJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SO_PJ,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_EMP,NEW.SO_PJ_CREATE_DATE,NEW.SO_PJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_SO_PJ;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_SO_PJ
        AFTER UPDATE ON TBL_SO_PJ
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SO_PJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SO_PJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_SO_PJ_LOG(EXECUTED_FUNCTION,ID_PK_SO_PJ,ID_FK_STOCK_OPNAME,ID_FK_EMP,SO_PJ_CREATE_DATE,SO_PJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SO_PJ,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_EMP,NEW.SO_PJ_CREATE_DATE,NEW.SO_PJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_emp" => $this->id_fk_emp,
                "so_pj_create_date" => $this->so_pj_create_date,
                "so_pj_last_modified" => $this->so_pj_last_modified,
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
                "id_pk_so_pj" => $this->id_pk_so_pj
            );
            $data = array(
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_emp" => $this->id_fk_emp,
                "so_pj_last_modified" => $this->so_pj_last_modified,
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
                "id_pk_so_pj" => $this->id_pk_so_pj
            );
            $data = array(
                "so_pj_last_modified" => $this->so_pj_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_stock_opname == ""){
            return false;
        }
        if($this->id_fk_emp == ""){
            return false;
        }
        if($this->so_pj_create_date == ""){
            return false;
        }
        if($this->so_pj_last_modified == ""){
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
        if($this->id_pk_so_pj != ""){
            return false;
        }
        if($this->id_fk_stock_opname != ""){
            return false;
        }
        if($this->id_fk_emp != ""){
            return false;
        }
        if($this->so_pj_last_modified != ""){
            return false;
        }
        if($this->id_last_modified != ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_so_pj != ""){
            return false;
        }
        if($this->so_pj_last_modified != ""){
            return false;
        }
        if($this->id_last_modified != ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_stock_opname,$id_fk_emp){
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_emp($id_fk_emp)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_so_pj,$id_fk_stock_opname,$id_fk_emp){
        if(!$this->set_id_pk_so_pj($id_pk_so_pj)){
            return false;
        }
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_emp($id_fk_emp)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_so_pj){
        if(!$this->set_id_pk_so_pj($id_pk_so_pj)){
            return false;
        }

        return true;
    }
    public function set_id_pk_so_pj($id_pk_so_pj){
        if($id_pk_so_pj != ""){
            $this->id_pk_so_pj = $id_pk_so_pj;
            return true;
        }
        return false;
    }
    public function set_id_fk_stock_opname($id_fk_stock_opname){
        if($id_fk_stock_opname != ""){
            $this->id_fk_stock_opname = $id_fk_stock_opname;
            return true;
        }
        return false;
    }
    public function set_id_fk_emp($id_fk_emp){
        if($id_fk_emp != ""){
            $this->id_fk_emp = $id_fk_emp;
            return true;
        }
        return false;
    }
    public function get_id_pk_so_pj(){
        return $this->id_pk_so_pj;
    }
    public function get_id_fk_stock_opname(){
        return $this->id_fk_stock_opname;
    }
    public function get_id_fk_emp(){
        return $this->id_fk_emp;
    }
}