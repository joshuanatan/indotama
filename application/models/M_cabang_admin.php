<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");

class M_cabang_admin extends CI_Model{
    private $tbl_name = "tbl_cabang_admin";
    private $columns = array();
    private $id_pk_cabang_admin;
    private $id_fk_cabang;
    private $id_fk_user;
    private $cabang_admin_status;
    private $cabang_admin_create_date;
    private $cabang_admin_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->columns = array();
        $this->set_column("user_name","User Name","required");
        $this->set_column("user_email","Email","required");
        $this->set_column("cabang_admin_status","Status","required");
        $this->set_column("cabang_admin_last_modified","Last Modified","required");
        $this->cabang_admin_create_date = date("Y-m-d H:i:s");
        $this->cabang_admin_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS TBL_CABANG_ADMIN;
        CREATE TABLE TBL_CABANG_ADMIN(
            ID_PK_CABANG_ADMIN INT PRIMARY KEY AUTO_INCREMENT,
            ID_FK_CABANG INT,
            ID_FK_USER INT,
            CABANG_ADMIN_STATUS VARCHAR(15),
            CABANG_ADMIN_CREATE_DATE DATETIME,
            CABANG_ADMIN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_CABANG_ADMIN_LOG;
        CREATE TABLE TBL_CABANG_ADMIN_LOG(
            ID_PK_CABANG_ADMIN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_CABANG_ADMIN INT,
            ID_FK_CABANG INT,
            ID_FK_USER INT,
            CABANG_ADMIN_STATUS VARCHAR(15),
            CABANG_ADMIN_CREATE_DATE DATETIME,
            CABANG_ADMIN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_CABANG_ADMIN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_CABANG_ADMIN
        AFTER INSERT ON TBL_CABANG_ADMIN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.CABANG_ADMIN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.CABANG_ADMIN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_CABANG_ADMIN_LOG(EXECUTED_FUNCTION,ID_PK_CABANG_ADMIN,ID_FK_CABANG,ID_FK_USER,CABANG_ADMIN_STATUS,CABANG_ADMIN_CREATE_DATE,CABANG_ADMIN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_CABANG_ADMIN,NEW.ID_FK_CABANG,NEW.ID_FK_USER,NEW.CABANG_ADMIN_STATUS,NEW.CABANG_ADMIN_CREATE_DATE,NEW.CABANG_ADMIN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_CABANG_ADMIN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_CABANG_ADMIN
        AFTER UPDATE ON TBL_CABANG_ADMIN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.CABANG_ADMIN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.CABANG_ADMIN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_CABANG_ADMIN_LOG(EXECUTED_FUNCTION,ID_PK_CABANG_ADMIN,ID_FK_CABANG,ID_FK_USER,CABANG_ADMIN_STATUS,CABANG_ADMIN_CREATE_DATE,CABANG_ADMIN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_CABANG_ADMIN,NEW.ID_FK_CABANG,NEW.ID_FK_USER,NEW.CABANG_ADMIN_STATUS,NEW.CABANG_ADMIN_CREATE_DATE,NEW.CABANG_ADMIN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = 20){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                id_pk_cabang_admin LIKE '%".$search_key."%' OR
                id_fk_cabang LIKE '%".$search_key."%' OR
                id_fk_user LIKE '%".$search_key."%' OR
                cabang_admin_status LIKE '%".$search_key."%' OR
                cabang_admin_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_last_modified,user_name,user_email
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_USER ON MSTR_USER.ID_PK_USER = ".$this->tbl_name.".ID_FK_USER
        INNER JOIN MSTR_CABANG ON MSTR_CABANG.ID_PK_CABANG = ".$this->tbl_name.".ID_FK_CABANG
        WHERE CABANG_ADMIN_STATUS = ? AND ID_FK_CABANG = ? AND USER_STATUS = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF",$this->id_fk_cabang,"AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_cabang_admin
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_USER ON MSTR_USER.ID_PK_USER = ".$this->tbl_name.".ID_FK_USER
        INNER JOIN MSTR_CABANG ON MSTR_CABANG.ID_PK_CABANG = ".$this->tbl_name.".ID_FK_CABANG
        WHERE CABANG_ADMIN_STATUS = ? AND ID_FK_CABANG = ? AND USER_STATUS = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function set_cabang_admin_columns(){
        $this->columns = array();
        $this->set_column("toko_nama","Toko",true);
        $this->set_column("cabang_daerah","Daerah",false);
        $this->set_column("cabang_notelp","No Telp",false);
        $this->set_column("cabang_alamat","Alamat",false);
        $this->set_column("cabang_status","Status",false);
        $this->set_column("cabang_last_modified","Last Modified",false);
    }
    public function list_cabang_admin($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = 20){
        $this->set_cabang_admin_columns();
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                id_pk_cabang LIKE '%".$search_key."%' OR 
                cabang_daerah LIKE '%".$search_key."%' OR 
                cabang_notelp LIKE '%".$search_key."%' OR 
                cabang_alamat LIKE '%".$search_key."%' OR 
                cabang_status LIKE '%".$search_key."%' OR 
                cabang_create_date LIKE '%".$search_key."%' OR 
                cabang_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_cabang,toko_nama,cabang_daerah,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_CABANG ON MSTR_CABANG.ID_PK_CABANG = ".$this->tbl_name.".ID_FK_CABANG
        INNER JOIN MSTR_TOKO ON MSTR_TOKO.ID_PK_TOKO = MSTR_CABANG.ID_FK_TOKO
        WHERE CABANG_STATUS = ? AND ID_FK_USER = ? AND CABANG_ADMIN_STATUS = ? AND TOKO_STATUS = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF",$this->id_fk_user,"AKTIF","AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_cabang
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_CABANG ON MSTR_CABANG.ID_PK_CABANG = ".$this->tbl_name.".ID_FK_CABANG
        INNER JOIN MSTR_TOKO ON MSTR_TOKO.ID_PK_TOKO = MSTR_CABANG.ID_FK_TOKO
        WHERE CABANG_STATUS = ? AND ID_FK_USER = ? AND CABANG_ADMIN_STATUS = ? AND TOKO_STATUS = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_cabang" => $this->id_fk_cabang,
                "id_fk_user" => $this->id_fk_user,
                "cabang_admin_status" => $this->cabang_admin_status,
                "cabang_admin_create_date" => $this->cabang_admin_create_date,
                "cabang_admin_last_modified" => $this->cabang_admin_last_modified,
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
                "id_pk_cabang_admin" => $this->id_pk_cabang_admin
            );
            $data = array(
                "id_fk_cabang" => $this->id_fk_cabang,
                "id_fk_user" => $this->id_fk_user,
                "cabang_admin_last_modified" => $this->cabang_admin_last_modified,
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
                "id_pk_cabang_admin" => $this->id_pk_cabang_admin
            );
            $data = array(
                "cabang_admin_status" => "NONAKTIF",
                "cabang_admin_last_modified" => $this->cabang_admin_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->id_fk_user == ""){
            return false;
        }
        if($this->cabang_admin_status == ""){
            return false;
        }
        if($this->cabang_admin_create_date == ""){
            return false;
        }
        if($this->cabang_admin_last_modified == ""){
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
        if($this->id_pk_cabang_admin == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->id_fk_user == ""){
            return false;
        }
        if($this->cabang_admin_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){

        if($this->id_pk_cabang_admin == ""){
            return false;
        }
        if($this->cabang_admin_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_cabang,$id_fk_user,$cabang_admin_status){
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        if(!$this->set_id_fk_user($id_fk_user)){
            return false;
        }
        if(!$this->set_cabang_admin_status($cabang_admin_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_cabang_admin,$id_fk_cabang,$id_fk_user){
        if(!$this->set_id_pk_cabang_admin($id_pk_cabang_admin)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        if(!$this->set_id_fk_user($id_fk_user)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_cabang_admin){
        if(!$this->set_id_pk_cabang_admin($id_pk_cabang_admin)){
            return false;
        }
        return true;
    }
    public function get_id_pk_cabang_admin(){
        return $this->id_pk_cabang_admin;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
    public function get_id_fk_user(){
        return $this->id_fk_user;
    }
    public function get_cabang_admin_status(){
        return $this->cabang_admin_status;
    }
    public function set_id_pk_cabang_admin($id_pk_cabang_admin){
        if($id_pk_cabang_admin != ""){
            $this->id_pk_cabang_admin = $id_pk_cabang_admin;
            return true;
        }
        return false;
    }
    public function set_id_fk_cabang($id_fk_cabang){
        if($id_fk_cabang != ""){
            $this->id_fk_cabang = $id_fk_cabang;
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
    public function set_cabang_admin_status($cabang_admin_status){
        if($cabang_admin_status != ""){
            $this->cabang_admin_status = $cabang_admin_status;
            return true;
        }
        return false;
    }
}