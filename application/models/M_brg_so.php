<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_so extends CI_Model{
    private $tbl_name = "TBL_BRG_SO";
    private $columns = array();
    private $id_pk_so_brg;
    private $brg_so_result;
    private $brg_so_notes;
    private $id_fk_stock_opname;
    private $id_fk_brg;
    private $brg_so_create_date;
    private $brg_so_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->brg_so_create_date = date("Y-m-d H:i:s");
        $this->brg_so_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS TBL_BRG_SO;
        CREATE TABLE TBL_BRG_SO(
            ID_PK_SO_BRG INT PRIMARY KEY AUTO_INCREMENT,
            BRG_SO_RESULT DOUBLE,
            BRG_SO_NOTES VARCHAR(200),
            ID_FK_STOCK_OPNAME INT,
            ID_FK_BRG INT,
            BRG_SO_CREATE_DATE DATETIME,
            BRG_SO_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_SO_LOG;
        CREATE TABLE TBL_BRG_SO_LOG(
            ID_PK_SO_BRG_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_SO_BRG INT,
            BRG_SO_RESULT DOUBLE,
            BRG_SO_NOTES VARCHAR(200),
            ID_FK_STOCK_OPNAME INT,
            ID_FK_BRG INT,
            BRG_SO_CREATE_DATE DATETIME,
            BRG_SO_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BRG_SO;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BRG_SO
        AFTER INSERT ON TBL_BRG_SO
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_SO_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_SO_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_SO_LOG(EXECUTED_FUNCTION,ID_PK_SO_BRG,BRG_SO_RESULT,BRG_SO_NOTES,ID_FK_STOCK_OPNAME,ID_FK_BRG,BRG_SO_CREATE_DATE,BRG_SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SO_BRG,NEW.BRG_SO_RESULT,NEW.BRG_SO_NOTES,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_BRG,NEW.BRG_SO_CREATE_DATE,NEW.BRG_SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_SO;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_SO
        AFTER UPDATE ON TBL_BRG_SO
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_SO_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_SO_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_SO_LOG(EXECUTED_FUNCTION,ID_PK_SO_BRG,BRG_SO_RESULT,BRG_SO_NOTES,ID_FK_STOCK_OPNAME,ID_FK_BRG,BRG_SO_CREATE_DATE,BRG_SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SO_BRG,NEW.BRG_SO_RESULT,NEW.BRG_SO_NOTES,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_BRG,NEW.BRG_SO_CREATE_DATE,NEW.BRG_SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_so_result" => $this->brg_so_result,
                "brg_so_notes" => $this->brg_so_notes,
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_brg" => $this->id_fk_brg,
                "brg_so_create_date" => $this->brg_so_create_date,
                "brg_so_last_modified" => $this->brg_so_last_modified,
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
                "id_pk_so_brg" => $this->id_pk_so_brg
            );
            $data = array(
                "brg_so_result" => $this->brg_so_result,
                "brg_so_notes" => $this->brg_so_notes,
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_brg" => $this->id_fk_brg,
                "brg_so_last_modified" => $this->brg_so_last_modified,
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
                "id_pk_so_brg" => $this->id_pk_so_brg
            );
            $data = array(
                "brg_so_last_modified" => $this->brg_so_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_so_result == ""){
            return false;
        }
        if($this->brg_so_notes == ""){
            return false;
        }
        if($this->id_fk_stock_opname == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->brg_so_create_date == ""){
            return false;
        }
        if($this->brg_so_last_modified == ""){
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
        if($this->id_pk_so_brg == ""){
            return false;
        }
        if($this->brg_so_result == ""){
            return false;
        }
        if($this->brg_so_notes == ""){
            return false;
        }
        if($this->id_fk_stock_opname == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->brg_so_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_so_brg == ""){
            return false;
        }

        if($this->brg_so_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_so_result,$brg_so_notes,$id_fk_stock_opname,$id_fk_brg){
        if(!$this->set_brg_so_result($brg_so_result)){
            return false;
        }
        if(!$this->set_brg_so_notes($brg_so_notes)){
            return false;
        }
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_so_brg,$brg_so_result,$brg_so_notes,$id_fk_stock_opname,$id_fk_brg){
        if(!$this->set_id_pk_so_brg($id_pk_so_brg)){
            return false;
        }
        if(!$this->set_brg_so_result($brg_so_result)){
            return false;
        }
        if(!$this->set_brg_so_notes($brg_so_notes)){
            return false;
        }
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_so_brg){
        if(!$this->set_id_pk_so_brg($id_pk_so_brg)){
            return false;
        }

        return true;
    }
    public function set_id_pk_so_brg($id_pk_so_brg){
        if($id_pk_so_brg != ""){
            $this->id_pk_so_brg  = $id_pk_so_brg;
            return true;
        }
        return false;
    }
    public function set_brg_so_result($brg_so_result){
        if($brg_so_result != ""){
            $this->brg_so_result  = $brg_so_result;
            return true;
        }
        return false;
    }
    public function set_brg_so_notes($brg_so_notes){
        if($brg_so_notes != ""){
            $this->brg_so_notes  = $brg_so_notes;
            return true;
        }
        return false;
    }
    public function set_id_fk_stock_opname($id_fk_stock_opname){
        if($id_fk_stock_opname != ""){
            $this->id_fk_stock_opname  = $id_fk_stock_opname;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg($id_fk_brg){
        if($id_fk_brg != ""){
            $this->id_fk_brg  = $id_fk_brg;
            return true;
        }
        return false;
    }
    public function set_brg_so_create_date($brg_so_create_date){
        if($brg_so_create_date != ""){
            $this->brg_so_create_date  = $brg_so_create_date;
            return true;
        }
        return false;
    }
    public function set_brg_so_last_modified($brg_so_last_modified){
        if($brg_so_last_modified != ""){
            $this->brg_so_last_modified  = $brg_so_last_modified;
            return true;
        }
        return false;
    }
    public function set_id_create_data($id_create_data){
        if($id_create_data != ""){
            $this->id_create_data  = $id_create_data;
            return true;
        }
        return false;
    }
    public function set_id_last_modified($id_last_modified){
        if($id_last_modified != ""){
            $this->id_last_modified  = $id_last_modified;
            return true;
        }
        return false;
    }    
    public function get_id_pk_so_brg(){
        return $this->id_pk_so_brg;
    }
    public function get_brg_so_result(){
        return $this->brg_so_result;
    }
    public function get_brg_so_notes(){
        return $this->brg_so_notes;
    }
    public function get_id_fk_stock_opname(){
        return $this->id_fk_stock_opname;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
    public function get_brg_so_create_date(){
        return $this->brg_so_create_date;
    }
    public function get_brg_so_last_modified(){
        return $this->brg_so_last_modified;
    }
    public function get_id_create_data(){
        return $this->id_create_data;
    }
    public function get_id_last_modified(){
        return $this->id_last_modified;
    }
}