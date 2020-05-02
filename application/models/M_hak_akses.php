<?php
defined("BASEPATH") OR exit("no direct script");
date_default_timezone_set("Asia/Jakarta");

class M_hak_akses extends CI_Model{
    private $tbl_name = "TBL_HAK_AKSES";
    private $columns = array();
    private $id_pk_hak_akses;
    private $id_fk_jabatan;
    private $id_fk_menu;
    private $hak_akses_create_date;
    private $hak_akses_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->hak_akses_create_date = date("Y-m-d H:i:s");
        $this->hak_akses_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        CREATE TABLE TBL_HAK_AKSES(
            ID_PK_HAK_AKSES INT PRIMARY KEY AUTO_INCREMENT,
            ID_FK_HAK_AKSES INT,
            ID_FK_MENU INT,
            HAK_AKSES_CREATE_DATE DATETIME,
            HAK_AKSES_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        CREATE TABLE TBL_HAK_AKSES_LOG(
            ID_PK_HAK_AKSES_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_HAK_AKSES INT,
            ID_FK_HAK_AKSES INT,
            ID_FK_MENU INT,
            HAK_AKSES_CREATE_DATE DATETIME,
            HAK_AKSES_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_HAK_AKSES;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_HAK_AKSES
        AFTER INSERT ON TBL_HAK_AKSES
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.HAK_AKSES_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.HAK_AKSES_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_HAK_AKSES_LOG(EXECUTED_FUNCTION,ID_PK_HAK_AKSES,ID_FK_HAK_AKSES,ID_FK_MENU,HAK_AKSES_CREATE_DATE,HAK_AKSES_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES('AFTER INSERT',NEW.ID_PK_HAK_AKSES,NEW.ID_FK_HAK_AKSES,NEW.ID_FK_MENU,NEW.HAK_AKSES_CREATE_DATE,NEW.HAK_AKSES_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_HAK_AKSES;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_HAK_AKSES
        AFTER UPDATE ON TBL_HAK_AKSES
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.HAK_AKSES_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.HAK_AKSES_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_HAK_AKSES_LOG(EXECUTED_FUNCTION,ID_PK_HAK_AKSES,ID_FK_HAK_AKSES,ID_FK_MENU,HAK_AKSES_CREATE_DATE,HAK_AKSES_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES('AFTER UPDATE',NEW.ID_PK_HAK_AKSES,NEW.ID_FK_HAK_AKSES,NEW.ID_FK_MENU,NEW.HAK_AKSES_CREATE_DATE,NEW.HAK_AKSES_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
    }
    public function insert(){
        $where = array(
            "id_fk_jabatan" => $this->id_fk_jabatan,
            "id_fk_menu" => $this->id_fk_menu,
        );
        if(isExistsInTable($this->tbl_name,$where)){
            $data = array(
                "id_fk_jabatan" => $this->id_fk_jabatan,
                "id_fk_menu" => $this->id_fk_menu,
                "hak_akses_create_date" => $this->hak_akses_create_date,
                "hak_akses_last_modified" => $this->hak_akses_last_modified,
                "hak_akses_create_date" => $this->hak_akses_create_date,
                "id_last_modified" => $this->id_last_modified,
            );
            return insertRow($this->tbl_name,$data);
        }
        else{
            return false;
        }
    }
    public function update(){
        $where = array(
            "id_pk_hak_akses !=" => $this->id_pk_hak_akses,
            "id_fk_jabatan" => $this->id_fk_jabatan,
            "id_fk_menu" => $this->id_fk_menu,
        );
        if(isExistsInTable($this->tbl_name,$where)){
            $where = array(
                "id_pk_hak_akses" => $this->id_pk_hak_akses,
            );
            $data = array(
                "id_fk_jabatan" => $this->id_fk_jabatan,
                "id_fk_menu" => $this->id_fk_menu,
                "hak_akses_last_modified" => $this->hak_akses_last_modified,
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
        #belom ditentuin fungsi deletenya mau gimana
    }
    public function check_insert(){
        if($this->id_fk_jabatan == ""){
            return false;
        }
        if($this->id_fk_menu == ""){
            return false;
        }
        if($this->hak_akses_create_date == ""){
            return false;
        }
        if($this->hak_akses_last_modified == ""){
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
        if($this->id_pk_hak_akses == ""){
            return false;
        }
        if($this->id_fk_jabatan == ""){
            return false;
        }
        if($this->id_fk_menu == ""){
            return false;
        }
        if($this->hak_akses_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_hak_akses == ""){
            return false;
        }
        if($this->hak_akses_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_jabatan,$id_fk_menu){
        if(!$this->set_id_fk_jabatan($id_fk_jabatan)){
            return false;
        }
        if(!$this->set_id_fk_menu($id_fk_menu)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_hak_akses,$id_fk_jabatan,$id_fk_menu){
        if(!$this->set_id_pk_hak_akses($id_pk_hak_akses)){
            return false;
        }
        if(!$this->set_id_fk_jabatan($id_fk_jabatan)){
            return false;
        }
        if(!$this->set_id_fk_menu($id_fk_menu)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_hak_akses){
        if(!$this->set_id_pk_hak_akses($id_pk_hak_akses)){
            return false;
        }
        return true;
    }
    public function get_id_pk_hak_akses(){
        return $this->id_pk_hak_akses;
    }
    public function get_id_fk_jabatan(){
        return $this->id_fk_jabatan;
    }
    public function get_id_fk_menu(){
        return $this->id_fk_menu;
    }
    public function get_hak_akses_create_date(){
        return $this->hak_akses_create_date;
    }
    public function get_hak_akses_last_modified(){
        return $this->hak_akses_last_modified;
    }
    public function get_id_create_data(){
        return $this->id_create_data;
    }
    public function get_id_last_modified(){
        return $this->id_last_modified;
    }
    public function set_id_pk_hak_akses($id_pk_hak_akses){
        if($id_pk_hak_akses != ""){
            $this->id_pk_hak_akses = $id_pk_hak_akses;
            return true;
        }
        else return false;
    }
    public function set_id_fk_jabatan($id_fk_jabatan){
        if($id_fk_jabatan != ""){
            $this->id_fk_jabatan = $id_fk_jabatan;
            return true;
        }
        else return false;
    }
    public function set_id_fk_menu($id_fk_menu){
        if($id_fk_menu != ""){
            $this->id_fk_menu = $id_fk_menu;
            return true;
        }
        else return false;
    }
    public function set_hak_akses_create_date($hak_akses_create_date){
        if($hak_akses_create_date != ""){
            $this->hak_akses_create_date = $hak_akses_create_date;
            return true;
        }
        else return false;
    }
    public function set_hak_akses_last_modified($hak_akses_last_modified){
        if($hak_akses_last_modified != ""){
            $this->hak_akses_last_modified = $hak_akses_last_modified;
            return true;
        }
        else return false;
    }
    public function set_id_create_data($id_create_data){
        if($id_create_data != ""){
            $this->id_create_data = $id_create_data;
            return true;
        }
        else return false;
    }
    public function set_id_last_modified($id_last_modified){
        if($id_last_modified != ""){
            $this->id_last_modified = $id_last_modified;
            return true;
        }
        else return false;
    }
}