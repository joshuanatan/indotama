<?php
defined("BASEPATH") or exit("No direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_barang_merk extends CI_Model{
    private $tbl_name = "MSTR_BARANG_MERK";
    private $columns = array();
    private $id_pk_brg_merk;
    private $brg_merk_nama;
    private $brg_merk_status;
    private $brg_merk_create_date;
    private $brg_merk_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_merk_nama","Jenis Barang","required");
        $this->set_column("brg_merk_status","Status","required");
        $this->set_column("brg_merk_last_modified","Last Modified","required");

        $this->brg_merk_create_date = date("Y-m-d H:i:s");
        $this->brg_merk_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
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
    public function install(){
        $sql = "
        CREATE TABLE MSTR_BARANG_MERK(
            ID_PK_BRG_MERK INT PRIMARY KEY AUTO_INCREMENT,
            BRG_MERK_NAMA VARCHAR(100),
            BRG_MERK_STATUS VARCHAR(15),
            BRG_MERK_CREATE_DATE DATETIME,
            BRG_MERK_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        CREATE TABLE MSTR_BARANG_MERK_LOG(
            ID_PK_BRG_MERK_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_MERK INT,
            BRG_MERK_NAMA VARCHAR(100),
            BRG_MERK_STATUS VARCHAR(15),
            BRG_MERK_CREATE_DATE DATETIME,
            BRG_MERK_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BARANG_MERK;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BARANG_MERK
        AFTER INSERT ON MSTR_BARANG_MERK
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_MERK_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_MERK_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_BARANG_MERK_LOG(EXECUTED_FUNCTION,ID_PK_BRG_MERK,BRG_MERK_NAMA,BRG_MERK_STATUS,BRG_MERK_CREATE_DATE,BRG_MERK_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG_MERK,NEW.BRG_MERK_NAMA,NEW.BRG_MERK_STATUS,NEW.BRG_MERK_CREATE_DATE,NEW.BRG_MERK_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BARANG_MERK;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BARANG_MERK
        AFTER UPDATE ON MSTR_BARANG_MERK
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_MERK_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_MERK_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_BARANG_MERK_LOG(EXECUTED_FUNCTION,ID_PK_BRG_MERK,BRG_MERK_NAMA,BRG_MERK_STATUS,BRG_MERK_CREATE_DATE,BRG_MERK_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG_MERK,NEW.BRG_MERK_NAMA,NEW.BRG_MERK_STATUS,NEW.BRG_MERK_CREATE_DATE,NEW.BRG_MERK_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                id_pk_brg_merk LIKE '%".$search_key."%' OR
                brg_merk_nama LIKE '%".$search_key."%' OR
                brg_merk_status LIKE '%".$search_key."%' OR
                brg_merk_last_modified LIKE '%".$search_key."%' OR
                id_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_last_modified,id_last_modified
        FROM ".$this->tbl_name." 
        WHERE brg_merk_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_brg_merk
        FROM ".$this->tbl_name." 
        WHERE brg_merk_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_merk_nama" => $this->brg_merk_nama,
                "brg_merk_status" => $this->brg_merk_status,
                "brg_merk_create_date" => $this->brg_merk_create_date,
                "brg_merk_last_modified" => $this->brg_merk_last_modified,
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
                "id_pk_brg_merk !=" => $this->id_pk_brg_merk,
                "brg_merk_nama" => $this->brg_merk_nama,
                "brg_merk_status" => "AKTIF",
            );
            if(!isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_brg_merk" => $this->id_pk_brg_merk
                );
                $data = array(
                    "brg_merk_nama" => $this->brg_merk_nama,
                    "brg_merk_last_modified" => $this->brg_merk_last_modified,
                    "id_last_modified" => $this->id_last_modified
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
                "id_pk_brg_merk" => $this->id_pk_brg_merk
            );
            $data = array(
                "brg_merk_status" => "NONAKTIF",
                "brg_merk_last_modified" => $this->brg_merk_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function check_insert(){
        if($this->brg_merk_nama == ""){
            return false;
        }
        if($this->brg_merk_status == ""){
            return false;
        }
        if($this->brg_merk_create_date == ""){
            return false;
        }
        if($this->brg_merk_last_modified == ""){
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
        if($this->id_pk_brg_merk == ""){
            return false;
        }
        if($this->brg_merk_nama == ""){
            return false;
        }
        if($this->brg_merk_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_merk == ""){
            return false;
        }
        if($this->brg_merk_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_merk_nama,$brg_merk_status){
        if(!$this->set_brg_merk_nama($brg_merk_nama)){
            return false;
        }
        if(!$this->set_brg_merk_status($brg_merk_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_merk,$brg_merk_nama){
        if(!$this->set_id_pk_brg_merk($id_pk_brg_merk)){
            return false;
        }
        if(!$this->set_brg_merk_nama($brg_merk_nama)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_merk){
        if(!$this->set_id_pk_brg_merk($id_pk_brg_merk)){
            return false;
        }
        return true;
    }
    public function get_id_pk_brg_merk(){
        return $this->id_pk_brg_merk;
    }
    public function get_brg_merk_nama(){
        return $this->brg_merk_nama;
    }
    public function get_brg_merk_status(){
        return $this->brg_merk_status;
    }
    public function set_id_pk_brg_merk($id_pk_brg_merk){
        if($id_pk_brg_merk != ""){
            $this->id_pk_brg_merk = $id_pk_brg_merk;
            return true;
        }
        return false;
    }
    public function set_brg_merk_nama($brg_merk_nama){
        if($brg_merk_nama != ""){
            $this->brg_merk_nama = $brg_merk_nama;
            return true;
        }
        return false;
    }
    public function set_brg_merk_status($brg_merk_status){
        if($brg_merk_status != ""){
            $this->brg_merk_status = $brg_merk_status;
            return true;
        }
        return false;
    }
}