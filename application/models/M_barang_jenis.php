<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_barang_jenis extends CI_Model{
    private $tbl_name = "MSTR_BARANG_JENIS";
    private $columns = array();
    private $id_pk_brg_jenis;
    private $brg_jenis_nama;
    private $brg_jenis_status;
    private $brg_jenis_create_date;
    private $brg_jenis_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_jenis_nama","Jenis Barang",true);
        $this->set_column("brg_jenis_status","Status",false);
        $this->set_column("brg_jenis_last_modified","Last Modified",false);

        $this->brg_jenis_create_date = date("Y-m-d H:i:s");
        $this->brg_jenis_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS MSTR_BARANG_JENIS;
        CREATE TABLE MSTR_BARANG_JENIS(
            ID_PK_BRG_JENIS INT PRIMARY KEY AUTO_INCREMENT,
            BRG_JENIS_NAMA VARCHAR(100),
            BRG_JENIS_STATUS VARCHAR(15),
            BRG_JENIS_CREATE_DATE DATETIME,
            BRG_JENIS_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_BARANG_JENIS_LOG;
        CREATE TABLE MSTR_BARANG_JENIS_LOG(
            ID_PK_BRG_JENIS_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(20),
            ID_PK_BRG_JENIS INT,
            BRG_JENIS_NAMA VARCHAR(100),
            BRG_JENIS_STATUS VARCHAR(15),
            BRG_JENIS_CREATE_DATE DATETIME,
            BRG_JENIS_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BARANG_JENIS;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BARANG_JENIS
        AFTER INSERT ON MSTR_BARANG_JENIS
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_JENIS_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_JENIS_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_BARANG_JENIS_LOG(EXECUTED_FUNCTION,ID_PK_BRG_JENIS,BRG_JENIS_NAMA,BRG_JENIS_STATUS,BRG_JENIS_CREATE_DATE,BRG_JENIS_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG_JENIS,NEW.BRG_JENIS_NAMA,NEW.BRG_JENIS_STATUS,NEW.BRG_JENIS_CREATE_DATE,NEW.BRG_JENIS_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;

        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BARANG_JENIS;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BARANG_JENIS
        AFTER UPDATE ON MSTR_BARANG_JENIS
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_JENIS_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_JENIS_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_BARANG_JENIS_LOG(EXECUTED_FUNCTION,ID_PK_BRG_JENIS,BRG_JENIS_NAMA,BRG_JENIS_STATUS,BRG_JENIS_CREATE_DATE,BRG_JENIS_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG_JENIS,NEW.BRG_JENIS_NAMA,NEW.BRG_JENIS_STATUS,NEW.BRG_JENIS_CREATE_DATE,NEW.BRG_JENIS_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                id_pk_brg_jenis LIKE '%".$search_key."%' OR
                brg_jenis_nama LIKE '%".$search_key."%' OR
                brg_jenis_status LIKE '%".$search_key."%' OR
                brg_jenis_last_modified LIKE '%".$search_key."%' OR
                id_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_last_modified,id_last_modified
        FROM ".$this->tbl_name." 
        WHERE brg_jenis_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_brg_jenis
        FROM ".$this->tbl_name." 
        WHERE brg_jenis_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_name(){
        $where = array(
            "brg_jenis_nama" => $this->brg_jenis_nama
        );
        $field = array(
            "id_pk_brg_jenis",
            "brg_jenis_nama",
            "brg_jenis_status",
            "brg_jenis_create_date",
            "brg_jenis_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function list(){
        $where = array(
            "brg_jenis_status" => "AKTIF"
        );
        $field = array(
            "id_pk_brg_jenis",
            "brg_jenis_nama",
            "brg_jenis_status",
            "brg_jenis_create_date",
            "brg_jenis_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_jenis_nama" => $this->brg_jenis_nama,
                "brg_jenis_status" => $this->brg_jenis_status,
                "brg_jenis_create_date" => $this->brg_jenis_create_date,
                "brg_jenis_last_modified" => $this->brg_jenis_last_modified,
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
                "id_pk_brg_jenis !=" => $this->id_pk_brg_jenis,
                "brg_jenis_nama" => $this->brg_jenis_nama,
                "brg_jenis_status" => "AKTIF",
            );
            if(!isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_brg_jenis" => $this->id_pk_brg_jenis
                );
                $data = array(
                    "brg_jenis_nama" => $this->brg_jenis_nama,
                    "brg_jenis_last_modified" => $this->brg_jenis_last_modified,
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
                "id_pk_brg_jenis" => $this->id_pk_brg_jenis
            );
            $data = array(
                "brg_jenis_status" => "NONAKTIF",
                "brg_jenis_last_modified" => $this->brg_jenis_last_modified,
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
        if($this->brg_jenis_nama == ""){
            return false;
        }
        if($this->brg_jenis_status == ""){
            return false;
        }
        if($this->brg_jenis_create_date == ""){
            return false;
        }
        if($this->brg_jenis_last_modified == ""){
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
        if($this->id_pk_brg_jenis == ""){
            return false;
        }
        if($this->brg_jenis_nama == ""){
            return false;
        }
        if($this->brg_jenis_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_jenis == ""){
            return false;
        }
        if($this->brg_jenis_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($brg_jenis_nama,$brg_jenis_status){
        if(!$this->set_brg_jenis_nama($brg_jenis_nama)){
            return false;
        }
        if(!$this->set_brg_jenis_status($brg_jenis_status)){
            return false;
        }
        else return true;
    }
    public function set_update($id_pk_brg_jenis,$brg_jenis_nama){
        if(!$this->set_id_pk_brg_jenis($id_pk_brg_jenis)){
            return false;
        }
        if(!$this->set_brg_jenis_nama($brg_jenis_nama)){
            return false;
        }
        else return true;
    }
    public function set_delete($id_pk_brg_jenis){
        if(!$this->set_id_pk_brg_jenis($id_pk_brg_jenis)){
            return false;
        }
        else return true;
    }
    public function get_id_pk_brg_jenis(){
        return $this->id_pk_brg_jenis;
    }
    public function get_brg_jenis_nama(){
        return $this->brg_jenis_nama;
    }
    public function get_brg_jenis_status(){
        return $this->brg_jenis_status;
    }
    public function set_id_pk_brg_jenis($id_pk_brg_jenis){
        if($id_pk_brg_jenis != ""){
            $this->id_pk_brg_jenis = $id_pk_brg_jenis;
            return true;
        }
        return false;
    }
    public function set_brg_jenis_nama($brg_jenis_nama){
        if($brg_jenis_nama != ""){
            $this->brg_jenis_nama = $brg_jenis_nama;
            return true;
        }
        return false;
    }
    public function set_brg_jenis_status($brg_jenis_status){
        if($brg_jenis_status != ""){
            $this->brg_jenis_status = $brg_jenis_status;
            return true;
        }
        return false;
    }
}