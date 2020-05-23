<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_satuan extends CI_Model{
    private $tbl_name = "MSTR_SATUAN";
    private $columns = array();
    private $id_pk_satuan;
    private $satuan_nama;
    private $satuan_rumus;
    private $satuan_status;
    private $satuan_create_date;
    private $satuan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("satuan_nama","Satuan",true);
        $this->set_column("satuan_rumus","Rumus",true);
        $this->set_column("satuan_status","Status",false);
        $this->set_column("satuan_last_modified","Last Modified",false);

        $this->satuan_create_date = date("Y-m-d H:i:s");
        $this->satuan_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS MSTR_SATUAN;
        CREATE TABLE MSTR_SATUAN(
            ID_PK_SATUAN INT PRIMARY KEY AUTO_INCREMENT,
            SATUAN_NAMA VARCHAR(100),
            SATUAN_RUMUS VARCHAR(100),
            SATUAN_STATUS VARCHAR(15),
            SATUAN_CREATE_DATE DATETIME,
            SATUAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_SATUAN_LOG;
        CREATE TABLE MSTR_SATUAN_LOG(
            ID_PK_SATUAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(20),
            ID_PK_SATUAN INT,
            SATUAN_NAMA VARCHAR(100),
            SATUAN_RUMUS VARCHAR(100),
            SATUAN_STATUS VARCHAR(15),
            SATUAN_CREATE_DATE DATETIME,
            SATUAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_SATUAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_SATUAN
        AFTER INSERT ON MSTR_SATUAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SATUAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SATUAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_SATUAN_LOG(EXECUTED_FUNCTION,ID_PK_SATUAN,SATUAN_NAMA,SATUAN_RUMUS,SATUAN_STATUS,SATUAN_CREATE_DATE,SATUAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SATUAN,NEW.SATUAN_NAMA,NEW.SATUAN_STATUS,NEW.SATUAN_RUMUS,NEW.SATUAN_CREATE_DATE,NEW.SATUAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;

        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_SATUAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_SATUAN
        AFTER UPDATE ON MSTR_SATUAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SATUAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SATUAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_SATUAN_LOG(EXECUTED_FUNCTION,ID_PK_SATUAN,SATUAN_NAMA,SATUAN_RUMUS,SATUAN_STATUS,SATUAN_CREATE_DATE,SATUAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SATUAN,NEW.SATUAN_NAMA,NEW.SATUAN_STATUS,NEW.SATUAN_RUMUS,NEW.SATUAN_CREATE_DATE,NEW.SATUAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                id_pk_satuan LIKE '%".$search_key."%' OR
                satuan_nama LIKE '%".$search_key."%' OR
                satuan_rumus LIKE '%".$search_key."%' OR
                satuan_status LIKE '%".$search_key."%' OR
                satuan_last_modified LIKE '%".$search_key."%' OR
                id_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_last_modified,id_last_modified
        FROM ".$this->tbl_name." 
        WHERE satuan_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_satuan
        FROM ".$this->tbl_name." 
        WHERE satuan_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_name(){
        $where = array(
            "satuan_nama" => $this->satuan_nama
        );
        $field = array(
            "id_pk_satuan",
            "satuan_nama",
            "satuan_rumus",
            "satuan_status",
            "satuan_create_date",
            "satuan_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function list(){
        $where = array(
            "satuan_status" => "AKTIF"
        );
        $field = array(
            "id_pk_satuan",
            "satuan_nama",
            "satuan_rumus",
            "satuan_status",
            "satuan_create_date",
            "satuan_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "satuan_nama" => $this->satuan_nama,
                "satuan_status" => $this->satuan_status,
                "satuan_rumus" => $this->satuan_rumus,
                "satuan_create_date" => $this->satuan_create_date,
                "satuan_last_modified" => $this->satuan_last_modified,
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
                "id_pk_satuan !=" => $this->id_pk_satuan,
                "satuan_nama" => $this->satuan_nama,
                "satuan_status" => "AKTIF",
            );
            if(!isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_satuan" => $this->id_pk_satuan
                );
                $data = array(
                    "satuan_nama" => $this->satuan_nama,
                    "satuan_rumus" => $this->satuan_rumus,
                    "satuan_last_modified" => $this->satuan_last_modified,
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
                "id_pk_satuan" => $this->id_pk_satuan
            );
            $data = array(
                "satuan_status" => "NONAKTIF",
                "satuan_last_modified" => $this->satuan_last_modified,
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
        if($this->satuan_nama == ""){
            return false;
        }
        if($this->satuan_rumus == ""){
            return false;
        }
        if($this->satuan_status == ""){
            return false;
        }
        if($this->satuan_create_date == ""){
            return false;
        }
        if($this->satuan_last_modified == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_update(){
        if($this->id_pk_satuan == ""){
            return false;
        }
        if($this->satuan_nama == ""){
            return false;
        }
        if($this->satuan_rumus == ""){
            return false;
        }
        if($this->satuan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_satuan == ""){
            return false;
        }
        if($this->satuan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($satuan_nama,$satuan_status,$satuan_rumus){
        if(!$this->set_satuan_nama($satuan_nama)){
            return false;
        }
        if(!$this->set_satuan_rumus($satuan_rumus)){
            return false;
        }
        if(!$this->set_satuan_status($satuan_status)){
            return false;
        }
        else return true;
    }
    public function set_update($id_pk_satuan,$satuan_nama,$satuan_rumus){
        if(!$this->set_id_pk_satuan($id_pk_satuan)){
            return false;
        }
        if(!$this->set_satuan_nama($satuan_nama)){
            return false;
        }
        if(!$this->set_satuan_rumus($satuan_rumus)){
            return false;
        }
        else return true;
    }
    public function set_delete($id_pk_satuan){
        if(!$this->set_id_pk_satuan($id_pk_satuan)){
            return false;
        }
        else return true;
    }
    public function get_id_pk_satuan(){
        return $this->id_pk_satuan;
    }
    public function get_satuan_nama(){
        return $this->satuan_nama;
    }
    public function get_satuan_rumus(){
        return $this->satuan_rumus;
    }
    public function get_satuan_status(){
        return $this->satuan_status;
    }
    public function set_id_pk_satuan($id_pk_satuan){
        if($id_pk_satuan != ""){
            $this->id_pk_satuan = $id_pk_satuan;
            return true;
        }
        return false;
    }
    public function set_satuan_nama($satuan_nama){
        if($satuan_nama != ""){
            $this->satuan_nama = $satuan_nama;
            return true;
        }
        return false;
    }
    public function set_satuan_rumus($satuan_rumus){
        if($satuan_rumus != ""){
            $this->satuan_rumus = $satuan_rumus;
            return true;
        }
        return false;
    }
    public function set_satuan_status($satuan_status){
        if($satuan_status != ""){
            $this->satuan_status = $satuan_status;
            return true;
        }
        return false;
    }
}