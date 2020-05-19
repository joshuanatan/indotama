<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_warehouse extends CI_Model{
    private $tbl_name = "MSTR_WAREHOUSE";
    private $columns = array();
    private $id_pk_warehouse;
    private $warehouse_nama;
    private $warehouse_alamat;
    private $warehouse_notelp;
    private $warehouse_desc;
    private $warehouse_status;
    private $warehouse_create_date;
    private $warehouse_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->set_column("warehouse_nama","Nama Warehouse","required");
        $this->set_column("warehouse_alamat","Alamat","required");
        $this->set_column("warehouse_notelp","No Telpon","required");
        $this->set_column("warehouse_desc","Deskripsi","required");
        $this->set_column("warehouse_status","Status","required");
        $this->set_column("warehouse_last_modified","Last Modified","required");
        $this->warehouse_create_date = date("Y-m-d H:i:s");
        $this->warehouse_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS MSTR_WAREHOUSE;
        CREATE TABLE MSTR_WAREHOUSE(
            ID_PK_WAREHOUSE INT PRIMARY KEY AUTO_INCREMENT,
            WAREHOUSE_NAMA VARCHAR(100),
            WAREHOUSE_ALAMAT VARCHAR(200),
            WAREHOUSE_NOTELP VARCHAR(30),
            WAREHOUSE_DESC VARCHAR(150),
            WAREHOUSE_STATUS VARCHAR(15),
            WAREHOUSE_CREATE_DATE DATETIME,
            WAREHOUSE_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_WAREHOUSE_LOG;
        CREATE TABLE MSTR_WAREHOUSE_LOG(
            ID_PK_WAREHOUSE_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_WAREHOUSE INT,
            WAREHOUSE_NAMA VARCHAR(100),
            WAREHOUSE_ALAMAT VARCHAR(200),
            WAREHOUSE_NOTELP VARCHAR(30),
            WAREHOUSE_DESC VARCHAR(150),
            WAREHOUSE_STATUS VARCHAR(15),
            WAREHOUSE_CREATE_DATE DATETIME,
            WAREHOUSE_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_WAREHOUSE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_WAREHOUSE
        AFTER INSERT ON MSTR_WAREHOUSE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.WAREHOUSE_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.WAREHOUSE_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_WAREHOUSE_LOG(EXECUTED_FUNCTION,ID_PK_WAREHOUSE,WAREHOUSE_NAMA,WAREHOUSE_ALAMAT,WAREHOUSE_NOTELP,WAREHOUSE_DESC,WAREHOUSE_STATUS,WAREHOUSE_CREATE_DATE,WAREHOUSE_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_WAREHOUSE,NEW.WAREHOUSE_NAMA,NEW.WAREHOUSE_ALAMAT,NEW.WAREHOUSE_NOTELP,NEW.WAREHOUSE_DESC,NEW.WAREHOUSE_STATUS,NEW.WAREHOUSE_CREATE_DATE,NEW.WAREHOUSE_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;

        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_WAREHOUSE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_WAREHOUSE
        AFTER UPDATE ON MSTR_WAREHOUSE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.WAREHOUSE_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.WAREHOUSE_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_WAREHOUSE_LOG(EXECUTED_FUNCTION,ID_PK_WAREHOUSE,WAREHOUSE_NAMA,WAREHOUSE_ALAMAT,WAREHOUSE_NOTELP,WAREHOUSE_DESC,WAREHOUSE_STATUS,WAREHOUSE_CREATE_DATE,WAREHOUSE_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_WAREHOUSE,NEW.WAREHOUSE_NAMA,NEW.WAREHOUSE_ALAMAT,NEW.WAREHOUSE_NOTELP,NEW.WAREHOUSE_DESC,NEW.WAREHOUSE_STATUS,NEW.WAREHOUSE_CREATE_DATE,NEW.WAREHOUSE_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                warehouse_nama LIKE '%".$search_key."%' OR 
                warehouse_alamat LIKE '%".$search_key."%' OR 
                warehouse_notelp LIKE '%".$search_key."%' OR 
                warehouse_desc LIKE '%".$search_key."%' OR 
                warehouse_status LIKE '%".$search_key."%' OR 
                warehouse_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_last_modified
        FROM ".$this->tbl_name." 
        WHERE warehouse_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_warehouse
        FROM ".$this->tbl_name." 
        WHERE warehouse_status = ? ".$search_query." 
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_id(){
        $field = array(
            "id_pk_warehouse",
            "warehouse_nama",
            "warehouse_alamat",
            "warehouse_notelp",
            "warehouse_desc",
            "warehouse_status",
            "warehouse_last_modified"
        );
        $where = array(
            "id_pk_warehouse" => $this->id_pk_warehouse
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "warehouse_nama" => $this->warehouse_nama,
                "warehouse_alamat" => $this->warehouse_alamat,
                "warehouse_notelp" => $this->warehouse_notelp,
                "warehouse_desc" => $this->warehouse_desc,
                "warehouse_status" => $this->warehouse_status,
                "warehouse_create_date" => $this->warehouse_create_date,
                "warehouse_last_modified" => $this->warehouse_last_modified,
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
                "id_pk_warehouse" => $this->id_pk_warehouse
            );
            $data = array(
                "warehouse_nama" => $this->warehouse_nama,
                "warehouse_alamat" => $this->warehouse_alamat,
                "warehouse_notelp" => $this->warehouse_notelp,
                "warehouse_desc" => $this->warehouse_desc,
                "warehouse_last_modified" => $this->warehouse_last_modified,
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
                "id_pk_warehouse" => $this->id_pk_warehouse
            );
            $data = array(
                "warehouse_status" => "NONAKTIF",
                "warehouse_last_modified" => $this->warehouse_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->warehouse_nama == ""){
            return false;
        }
        if($this->warehouse_alamat == ""){
            return false;
        }
        if($this->warehouse_notelp == ""){
            return false;
        }
        if($this->warehouse_desc == ""){
            return false;
        }
        if($this->warehouse_status == ""){
            return false;
        }
        if($this->warehouse_create_date == ""){
            return false;
        }
        if($this->warehouse_last_modified == ""){
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
        if($this->id_pk_warehouse == ""){
            return false;
        }
        if($this->warehouse_nama == ""){
            return false;
        }
        if($this->warehouse_alamat == ""){
            return false;
        }
        if($this->warehouse_notelp == ""){
            return false;
        }
        if($this->warehouse_desc == ""){
            return false;
        }
        if($this->warehouse_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_warehouse == ""){
            return false;
        }
        if($this->warehouse_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($warehouse_nama,$warehouse_alamat,$warehouse_notelp,$warehouse_desc,$warehouse_status){
        if(!$this->set_warehouse_nama($warehouse_nama)){
            return false;
        }
        if(!$this->set_warehouse_alamat($warehouse_alamat)){
            return false;
        }
        if(!$this->set_warehouse_notelp($warehouse_notelp)){
            return false;
        }
        if(!$this->set_warehouse_desc($warehouse_desc)){
            return false;
        }
        if(!$this->set_warehouse_status($warehouse_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_warehouse,$warehouse_nama,$warehouse_alamat,$warehouse_notelp,$warehouse_desc){
        if(!$this->set_id_pk_warehouse($id_pk_warehouse)){
            return false;
        }
        if(!$this->set_warehouse_nama($warehouse_nama)){
            return false;
        }
        if(!$this->set_warehouse_alamat($warehouse_alamat)){
            return false;
        }
        if(!$this->set_warehouse_notelp($warehouse_notelp)){
            return false;
        }
        if(!$this->set_warehouse_desc($warehouse_desc)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_warehouse){
        if(!$this->set_id_pk_warehouse($id_pk_warehouse)){
            return false;
        }
        return true;}
    public function set_id_pk_warehouse($id_pk_warehouse){
        if($id_pk_warehouse != ""){
            $this->id_pk_warehouse = $id_pk_warehouse;
            return true;
        }
        return false;
    }
    public function set_warehouse_nama($warehouse_nama){
        if($warehouse_nama != ""){
            $this->warehouse_nama = $warehouse_nama;
            return true;
        }
        return false;
    }
    public function set_warehouse_alamat($warehouse_alamat){
        if($warehouse_alamat != ""){
            $this->warehouse_alamat = $warehouse_alamat;
            return true;
        }
        return false;
    }
    public function set_warehouse_notelp($warehouse_notelp){
        if($warehouse_notelp != ""){
            $this->warehouse_notelp = $warehouse_notelp;
            return true;
        }
        return false;
    }
    public function set_warehouse_desc($warehouse_desc){
        if($warehouse_desc != ""){
            $this->warehouse_desc = $warehouse_desc;
            return true;
        }
        return false;
    }
    public function set_warehouse_status($warehouse_status){
        if($warehouse_status != ""){
            $this->warehouse_status = $warehouse_status;
            return true;
        }
        return false;
    }
    public function get_id_pk_warehouse(){
        return $this->id_pk_warehouse;
    }
    public function get_warehouse_nama(){
        return $this->warehouse_nama;
    }
    public function get_warehouse_alamat(){
        return $this->warehouse_alamat;
    }
    public function get_warehouse_notelp(){
        return $this->warehouse_notelp;
    }
    public function get_warehouse_desc(){
        return $this->warehouse_desc;
    }
    public function get_warehouse_status(){
        return $this->warehouse_status;
    }
}