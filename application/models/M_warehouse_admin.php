<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");

class M_warehouse_admin extends CI_Model{
    private $tbl_name = "tbl_warehouse_admin";
    private $columns = array();
    private $id_pk_warehouse_admin;
    private $id_fk_warehouse;
    private $id_fk_user;
    private $warehouse_admin_status;
    private $warehouse_admin_create_date;
    private $warehouse_admin_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->columns = array();
        $this->set_column("user_name","User Name","required");
        $this->set_column("user_email","Email","required");
        $this->set_column("warehouse_admin_status","Status","required");
        $this->set_column("warehouse_admin_last_modified","Last Modified","required");
        $this->warehouse_admin_create_date = date("Y-m-d H:i:s");
        $this->warehouse_admin_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS TBL_WAREHOUSE_ADMIN;
        CREATE TABLE TBL_WAREHOUSE_ADMIN(
            ID_PK_WAREHOUSE_ADMIN INT PRIMARY KEY AUTO_INCREMENT,
            ID_FK_WAREHOUSE INT,
            ID_FK_USER INT,
            WAREHOUSE_ADMIN_STATUS VARCHAR(15),
            WAREHOUSE_ADMIN_CREATE_DATE DATETIME,
            WAREHOUSE_ADMIN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_WAREHOUSE_ADMIN_LOG;
        CREATE TABLE TBL_WAREHOUSE_ADMIN_LOG(
            ID_PK_WAREHOUSE_ADMIN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_WAREHOUSE_ADMIN INT,
            ID_FK_WAREHOUSE INT,
            ID_FK_USER INT,
            WAREHOUSE_ADMIN_STATUS VARCHAR(15),
            WAREHOUSE_ADMIN_CREATE_DATE DATETIME,
            WAREHOUSE_ADMIN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_WAREHOUSE_ADMIN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_WAREHOUSE_ADMIN
        AFTER INSERT ON TBL_WAREHOUSE_ADMIN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.WAREHOUSE_ADMIN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.WAREHOUSE_ADMIN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_WAREHOUSE_ADMIN_LOG(EXECUTED_FUNCTION,ID_PK_WAREHOUSE_ADMIN,ID_FK_WAREHOUSE,ID_FK_USER,WAREHOUSE_ADMIN_STATUS,WAREHOUSE_ADMIN_CREATE_DATE,WAREHOUSE_ADMIN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_WAREHOUSE_ADMIN,NEW.ID_FK_WAREHOUSE,NEW.ID_FK_USER,NEW.WAREHOUSE_ADMIN_STATUS,NEW.WAREHOUSE_ADMIN_CREATE_DATE,NEW.WAREHOUSE_ADMIN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_WAREHOUSE_ADMIN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_WAREHOUSE_ADMIN
        AFTER UPDATE ON TBL_WAREHOUSE_ADMIN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.WAREHOUSE_ADMIN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.WAREHOUSE_ADMIN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_WAREHOUSE_ADMIN_LOG(EXECUTED_FUNCTION,ID_PK_WAREHOUSE_ADMIN,ID_FK_WAREHOUSE,ID_FK_USER,WAREHOUSE_ADMIN_STATUS,WAREHOUSE_ADMIN_CREATE_DATE,WAREHOUSE_ADMIN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_WAREHOUSE_ADMIN,NEW.ID_FK_WAREHOUSE,NEW.ID_FK_USER,NEW.WAREHOUSE_ADMIN_STATUS,NEW.WAREHOUSE_ADMIN_CREATE_DATE,NEW.WAREHOUSE_ADMIN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
    public function columns(){
        return $this->columns;
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                id_pk_warehouse_admin LIKE '%".$search_key."%' OR
                id_fk_warehouse LIKE '%".$search_key."%' OR
                id_fk_user LIKE '%".$search_key."%' OR
                warehouse_admin_status LIKE '%".$search_key."%' OR
                warehouse_admin_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_last_modified,user_name,user_email
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_USER ON MSTR_USER.ID_PK_USER = ".$this->tbl_name.".ID_FK_USER
        INNER JOIN MSTR_WAREHOUSE ON MSTR_WAREHOUSE.ID_PK_WAREHOUSE = ".$this->tbl_name.".ID_FK_WAREHOUSE
        WHERE WAREHOUSE_ADMIN_STATUS = ? AND ID_FK_WAREHOUSE = ? AND USER_STATUS = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF",$this->id_fk_warehouse,"AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_warehouse_admin
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_USER ON MSTR_USER.ID_PK_USER = ".$this->tbl_name.".ID_FK_USER
        INNER JOIN MSTR_WAREHOUSE ON MSTR_WAREHOUSE.ID_PK_WAREHOUSE = ".$this->tbl_name.".ID_FK_WAREHOUSE
        WHERE WAREHOUSE_ADMIN_STATUS = ? AND ID_FK_WAREHOUSE = ? AND USER_STATUS = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_warehouse" => $this->id_fk_warehouse,
                "id_fk_user" => $this->id_fk_user,
                "warehouse_admin_status" => $this->warehouse_admin_status,
                "warehouse_admin_create_date" => $this->warehouse_admin_create_date,
                "warehouse_admin_last_modified" => $this->warehouse_admin_last_modified,
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
                "id_pk_warehouse_admin" => $this->id_pk_warehouse_admin
            );
            $data = array(
                "id_fk_warehouse" => $this->id_fk_warehouse,
                "id_fk_user" => $this->id_fk_user,
                "warehouse_admin_last_modified" => $this->warehouse_admin_last_modified,
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
                "id_pk_warehouse_admin" => $this->id_pk_warehouse_admin
            );
            $data = array(
                "warehouse_admin_status" => "NONAKTIF",
                "warehouse_admin_last_modified" => $this->warehouse_admin_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_warehouse == ""){
            return false;
        }
        if($this->id_fk_user == ""){
            return false;
        }
        if($this->warehouse_admin_status == ""){
            return false;
        }
        if($this->warehouse_admin_create_date == ""){
            return false;
        }
        if($this->warehouse_admin_last_modified == ""){
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
        if($this->id_pk_warehouse_admin == ""){
            return false;
        }
        if($this->id_fk_warehouse == ""){
            return false;
        }
        if($this->id_fk_user == ""){
            return false;
        }
        if($this->warehouse_admin_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){

        if($this->id_pk_warehouse_admin == ""){
            return false;
        }
        if($this->warehouse_admin_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_warehouse,$id_fk_user,$warehouse_admin_status){
        if(!$this->set_id_fk_warehouse($id_fk_warehouse)){
            return false;
        }
        if(!$this->set_id_fk_user($id_fk_user)){
            return false;
        }
        if(!$this->set_warehouse_admin_status($warehouse_admin_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_warehouse_admin,$id_fk_warehouse,$id_fk_user){
        if(!$this->set_id_pk_warehouse_admin($id_pk_warehouse_admin)){
            return false;
        }
        if(!$this->set_id_fk_warehouse($id_fk_warehouse)){
            return false;
        }
        if(!$this->set_id_fk_user($id_fk_user)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_warehouse_admin){
        if(!$this->set_id_pk_warehouse_admin($id_pk_warehouse_admin)){
            return false;
        }
        return true;
    }
    public function get_id_pk_warehouse_admin(){
        return $this->id_pk_warehouse_admin;
    }
    public function get_id_fk_warehouse(){
        return $this->id_fk_warehouse;
    }
    public function get_id_fk_user(){
        return $this->id_fk_user;
    }
    public function get_warehouse_admin_status(){
        return $this->warehouse_admin_status;
    }
    public function set_id_pk_warehouse_admin($id_pk_warehouse_admin){
        if($id_pk_warehouse_admin != ""){
            $this->id_pk_warehouse_admin = $id_pk_warehouse_admin;
            return true;
        }
        return false;
    }
    public function set_id_fk_warehouse($id_fk_warehouse){
        if($id_fk_warehouse != ""){
            $this->id_fk_warehouse = $id_fk_warehouse;
            return true;
        }
        return false;
    }
    public function set_id_fk_user($id_fk_user){
        if($id_fk_user != ""){
            $this->id_fk_user = $id_fk_user;
            return true;
        }
        return false;
    }
    public function set_warehouse_admin_status($warehouse_admin_status){
        if($warehouse_admin_status != ""){
            $this->warehouse_admin_status = $warehouse_admin_status;
            return true;
        }
        return false;
    }
}