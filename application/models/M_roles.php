<?php
defined("BASEPATH") or exit("No direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_roles extends CI_Model{
    private $tbl_name = "MSTR_JABATAN";
    private $columns = array();
    private $id_pk_jabatan;
    private $jabatan_nama;
    private $jabatan_status;
    private $jabatan_create_date;
    private $jabatan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("jabatan_nama","Nama","required");
        $this->set_column("jabatan_status","Status","required");
        $this->set_column("jabatan_last_modified","Last Modified","required");
        $this->jabatan_create_date = date("Y-m-d H:i:s");
        $this->jabatan_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS MSTR_JABATAN;
        CREATE TABLE MSTR_JABATAN(
            ID_PK_JABATAN INT PRIMARY KEY AUTO_INCREMENT,
            JABATAN_NAMA VARCHAR(100),
            JABATAN_STATUS VARCHAR(15),
            JABATAN_CREATE_DATE DATETIME,
            JABATAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_JABATAN_LOG;
        CREATE TABLE MSTR_JABATAN_LOG(
            ID_PK_JABATAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_JABATAN INT,
            JABATAN_NAMA VARCHAR(100),
            JABATAN_STATUS VARCHAR(15),
            JABATAN_CREATE_DATE DATETIME,
            JABATAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_JABATAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_JABATAN
        AFTER INSERT ON MSTR_JABATAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.JABATAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT ' , NEW.JABATAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_JABATAN_LOG(EXECUTED_FUNCTION,ID_PK_JABATAN,JABATAN_NAMA,JABATAN_STATUS,JABATAN_CREATE_DATE,JABATAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES('AFTER INSERT',NEW.ID_PK_JABATAN,NEW.JABATAN_NAMA,NEW.JABATAN_STATUS,NEW.JABATAN_CREATE_DATE,NEW.JABATAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);

            /* INSERT NEW JABATAN TO ALL HAK AKSES*/
            SET @ID_JABATAN = NEW.ID_PK_JABATAN;
            INSERT INTO TBL_HAK_AKSES(ID_FK_JABATAN,ID_FK_MENU,HAK_AKSES_STATUS,HAK_AKSES_CREATE_DATE,HAK_AKSES_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED)
            SELECT @ID_JABATAN,ID_PK_MENU,'NONAKTIF',@TGL_ACTION,@TGL_ACTION,@ID_USER,@ID_USER FROM MSTR_MENU;
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_JABATAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_JABATAN
        AFTER UPDATE ON MSTR_JABATAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.JABATAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT ' , NEW.JABATAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_JABATAN_LOG(EXECUTED_FUNCTION,ID_PK_JABATAN,JABATAN_NAMA,JABATAN_STATUS,JABATAN_CREATE_DATE,JABATAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES('AFTER UPDATE',NEW.ID_PK_JABATAN,NEW.JABATAN_NAMA,NEW.JABATAN_STATUS,NEW.JABATAN_CREATE_DATE,NEW.JABATAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                id_pk_jabatan LIKE '%".$search_key."%' OR
                jabatan_nama LIKE '%".$search_key."%' OR
                jabatan_status LIKE '%".$search_key."%' OR
                jabatan_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_last_modified
        FROM ".$this->tbl_name." 
        WHERE jabatan_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_jabatan
        FROM ".$this->tbl_name." 
        WHERE jabatan_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function columns(){
        return $this->columns;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "jabatan_nama" => $this->jabatan_nama,
                "jabatan_status" => $this->jabatan_status,
                "jabatan_create_date" => $this->jabatan_create_date,
                "jabatan_last_modified" => $this->jabatan_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertRow($this->tbl_name,$data);
        }
        else{
            return false;
        }
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "ID_PK_JABATAN !=" => $this->id_pk_jabatan,
                "JABATAN_NAMA" => $this->jabatan_nama,
                "JABATAN_STATUS" => "AKTIF",
            );
            if(!isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "ID_PK_JABATAN" => $this->id_pk_jabatan
                );
                $data = array(
                    "JABATAN_NAMA" => $this->jabatan_nama,
                    "JABATAN_LAST_MODIFIED" => $this->jabatan_last_modified,
                    "ID_LAST_MODIFIED" => $this->id_last_modified,
                );
                updateRow($this->tbl_name,$data,$where);
                return true;
            }
            else{
                return false;
            }
            
        }
        else{
            return false;
        }
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "ID_PK_JABATAN" => $this->id_pk_jabatan
            );
            $data = array(
                "JABATAN_STATUS" => "NONAKTIF",
                "JABATAN_LAST_MODIFIED" => $this->jabatan_last_modified,
                "ID_LAST_MODIFIED" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }else{
            return false;
        }
    }
    public function check_insert(){
        if($this->jabatan_nama == ""){
            return false;
        }
        if($this->jabatan_status == ""){
            return false;
        }
        if($this->jabatan_create_date == ""){
            return false;
        }
        if($this->jabatan_last_modified == ""){
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
        if($this->id_pk_jabatan == ""){
            return false;
        }
        if($this->jabatan_nama == ""){
            return false;
        }
        if($this->jabatan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_jabatan == ""){
            return false;
        }
        if($this->jabatan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($jabatan_nama,$jabatan_status){
        if(!$this->set_jabatan_nama($jabatan_nama)){
            return false;
        }
        if(!$this->set_jabatan_status($jabatan_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_jabatan,$jabatan_nama){
        if(!$this->set_id_pk_jabatan($id_pk_jabatan)){
            return false;
        }
        if(!$this->set_jabatan_nama($jabatan_nama)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_jabatan){
        if(!$this->set_id_pk_jabatan($id_pk_jabatan)){
            return false;
        }
        return true;
    }
    public function get_id_pk_jabatan(){
        return $this->id_pk_jabatan;
    }
    public function get_jabatan_nama(){
        return $this->jabatan_nama;
    }
    public function get_jabatan_status(){
        return $this->jabatan_status;
    }
    public function set_id_pk_jabatan($id_pk_jabatan){
        if($id_pk_jabatan != ""){
            $this->id_pk_jabatan = $id_pk_jabatan;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_jabatan_nama($jabatan_nama){
        if($jabatan_nama != ""){
            $this->jabatan_nama = $jabatan_nama;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_jabatan_status($jabatan_status){
        if($jabatan_status != ""){
            $this->jabatan_status = $jabatan_status;
            return true;
        }
        else{
            return false;
        }
    }
}