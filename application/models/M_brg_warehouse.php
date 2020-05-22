<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_warehouse extends CI_Model{
    private $tbl_name = "TBL_BRG_WAREHOUSE";
    private $columns = array();
    private $id_pk_brg_warehouse;
    private $brg_warehouse_qty;
    private $brg_warehouse_notes;
    private $brg_warehouse_status;
    private $id_fk_warehouse;
    private $id_fk_brg;
    private $brg_warehouse_create_date;
    private $brg_warehouse_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_kode","Kode Barang","required");
        $this->set_column("brg_nama","Nama Barang","required");
        $this->set_column("brg_ket","Keterangan","required");
        $this->set_column("brg_warehouse_qty","Qty","required");
        $this->set_column("brg_warehouse_notes","Notes","required");
        $this->set_column("brg_warehouse_status","Status","required");
        $this->set_column("brg_warehouse_last_modified","Last Modified","required");
        $this->brg_warehouse_create_date = date("Y-m-d H:i:s");
        $this->brg_warehouse_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS TBL_BRG_WAREHOUSE;
        CREATE TABLE TBL_BRG_WAREHOUSE(
            ID_PK_BRG_WAREHOUSE INT PRIMARY KEY AUTO_INCREMENT,
            BRG_WAREHOUSE_QTY INT,
            BRG_WAREHOUSE_NOTES VARCHAR(200),
            BRG_WAREHOUSE_STATUS VARCHAR(15),
            ID_FK_BRG INT,
            ID_FK_WAREHOUSE INT,
            BRG_WAREHOUSE_CREATE_DATE DATETIME,
            BRG_WAREHOUSE_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_WAREHOUSE_LOG;
        CREATE TABLE TBL_BRG_WAREHOUSE_LOG(
            ID_PK_BRG_WAREHOUSE_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_WAREHOUSE INT,
            BRG_WAREHOUSE_QTY INT,
            BRG_WAREHOUSE_NOTES VARCHAR(200),
            BRG_WAREHOUSE_STATUS VARCHAR(15),
            ID_FK_BRG INT,
            ID_FK_WAREHOUSE INT,
            BRG_WAREHOUSE_CREATE_DATE DATETIME,
            BRG_WAREHOUSE_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BRG_WAREHOUSE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BRG_WAREHOUSE
        AFTER INSERT ON TBL_BRG_WAREHOUSE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_WAREHOUSE_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT ' , NEW.BRG_WAREHOUSE_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_WAREHOUSE_LOG(EXECUTED_FUNCTION,ID_PK_BRG_WAREHOUSE,BRG_WAREHOUSE_QTY,BRG_WAREHOUSE_NOTES,BRG_WAREHOUSE_STATUS,ID_FK_BRG,ID_FK_WAREHOUSE,BRG_WAREHOUSE_CREATE_DATE,BRG_WAREHOUSE_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG_WAREHOUSE,NEW.BRG_WAREHOUSE_QTY,NEW.BRG_WAREHOUSE_NOTES,NEW.BRG_WAREHOUSE_STATUS,NEW.ID_FK_BRG,NEW.ID_FK_WAREHOUSE,NEW.BRG_WAREHOUSE_CREATE_DATE,NEW.BRG_WAREHOUSE_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;

        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_WAREHOUSE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_WAREHOUSE
        AFTER UPDATE ON TBL_BRG_WAREHOUSE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_WAREHOUSE_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT ' , NEW.BRG_WAREHOUSE_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_WAREHOUSE_LOG(EXECUTED_FUNCTION,ID_PK_BRG_WAREHOUSE,BRG_WAREHOUSE_QTY,BRG_WAREHOUSE_NOTES,BRG_WAREHOUSE_STATUS,ID_FK_BRG,ID_FK_WAREHOUSE,BRG_WAREHOUSE_CREATE_DATE,BRG_WAREHOUSE_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG_WAREHOUSE,NEW.BRG_WAREHOUSE_QTY,NEW.BRG_WAREHOUSE_NOTES,NEW.BRG_WAREHOUSE_STATUS,NEW.ID_FK_BRG,NEW.ID_FK_WAREHOUSE,NEW.BRG_WAREHOUSE_CREATE_DATE,NEW.BRG_WAREHOUSE_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                id_pk_brg_warehouse LIKE '%".$search_key."%' OR 
                brg_warehouse_qty LIKE '%".$search_key."%' OR 
                brg_warehouse_notes LIKE '%".$search_key."%' OR 
                brg_warehouse_status LIKE '%".$search_key."%' OR 
                id_fk_brg LIKE '%".$search_key."%' OR 
                brg_warehouse_last_modified LIKE '%".$search_key."%' OR 
                brg_nama LIKE '%".$search_key."%' OR 
                brg_kode LIKE '%".$search_key."%' OR 
                brg_ket LIKE '%".$search_key."%' OR 
                brg_minimal LIKE '%".$search_key."%' OR 
                brg_satuan LIKE '%".$search_key."%' OR 
                brg_image LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,brg_warehouse_last_modified,brg_nama,brg_kode,brg_ket,brg_minimal,brg_satuan,brg_image
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_BARANG ON MSTR_BARANG.ID_PK_BRG = ".$this->tbl_name.".ID_FK_BRG
        WHERE BRG_WAREHOUSE_STATUS = ? AND BRG_STATUS = ? AND ID_FK_WAREHOUSE = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF","AKTIF",$this->id_fk_warehouse
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,brg_warehouse_last_modified,brg_nama,brg_kode,brg_ket,brg_minimal,brg_satuan,brg_image
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_BARANG ON MSTR_BARANG.ID_PK_BRG = ".$this->tbl_name.".ID_FK_BRG
        WHERE BRG_WAREHOUSE_STATUS = ? AND BRG_STATUS = ? AND ID_FK_WAREHOUSE = ?".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_warehouse_qty" => $this->brg_warehouse_qty,
                "brg_warehouse_notes" => $this->brg_warehouse_notes,
                "brg_warehouse_status" => $this->brg_warehouse_status,
                "id_fk_brg" => $this->id_fk_brg,
                "id_fk_warehouse" => $this->id_fk_warehouse,
                "brg_warehouse_create_date" => $this->brg_warehouse_create_date,
                "brg_warehouse_last_modified" => $this->brg_warehouse_last_modified,
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
                "id_pk_brg_warehouse" => $this->id_pk_brg_warehouse   
            );
            $data = array(
                "brg_warehouse_qty" => $this->brg_warehouse_qty,
                "brg_warehouse_notes" => $this->brg_warehouse_notes,
                "id_fk_brg" => $this->id_fk_brg,
                "brg_warehouse_last_modified" => $this->brg_warehouse_last_modified,
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
                "id_pk_brg_warehouse" => $this->id_pk_brg_warehouse   
            );
            $data = array(
                "brg_warehouse_status" => "NONAKTIF",
                "brg_warehouse_last_modified" => $this->brg_warehouse_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true; 
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_warehouse_qty == ""){
            return false;
        }
        if($this->brg_warehouse_notes == ""){
            return false;
        }
        if($this->brg_warehouse_status == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->id_fk_warehouse == ""){
            return false;
        }
        if($this->brg_warehouse_create_date == ""){
            return false;
        }
        if($this->brg_warehouse_last_modified == ""){
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
        if($this->id_pk_brg_warehouse == ""){
            return false;
        }
        if($this->brg_warehouse_qty == ""){
            return false;
        }
        if($this->brg_warehouse_notes == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->brg_warehouse_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_warehouse == ""){
            return false;
        }
        if($this->brg_warehouse_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_warehouse_qty,$brg_warehouse_notes,$brg_warehouse_status,$id_fk_brg,$id_fk_warehouse){
        if(!$this->set_brg_warehouse_qty($brg_warehouse_qty)){
            return false;
        }
        if(!$this->set_brg_warehouse_notes($brg_warehouse_notes)){
            return false;
        }
        if(!$this->set_brg_warehouse_status($brg_warehouse_status)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        if(!$this->set_id_fk_warehouse($id_fk_warehouse)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_warehouse,$brg_warehouse_qty,$brg_warehouse_notes,$id_fk_brg){
        if(!$this->set_id_pk_brg_warehouse($id_pk_brg_warehouse)){
            return false;
        }
        if(!$this->set_brg_warehouse_qty($brg_warehouse_qty)){
            return false;
        }
        if(!$this->set_brg_warehouse_notes($brg_warehouse_notes)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_warehouse){
        if(!$this->set_id_pk_brg_warehouse($id_pk_brg_warehouse)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_warehouse($id_pk_brg_warehouse){
        if($id_pk_brg_warehouse != ""){
            $this->id_pk_brg_warehouse = $id_pk_brg_warehouse;
            return true;
        }
        return false;
    }
    public function set_brg_warehouse_qty($brg_warehouse_qty){
        if($brg_warehouse_qty != ""){
            $this->brg_warehouse_qty = $brg_warehouse_qty;
            return true;
        }
        return false;
    }
    public function set_brg_warehouse_notes($brg_warehouse_notes){
        if($brg_warehouse_notes != ""){
            $this->brg_warehouse_notes = $brg_warehouse_notes;
            return true;
        }
        return false;
    }
    public function set_brg_warehouse_status($brg_warehouse_status){
        if($brg_warehouse_status != ""){
            $this->brg_warehouse_status = $brg_warehouse_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg($id_fk_brg){
        if($id_fk_brg != ""){
            $this->id_fk_brg = $id_fk_brg;
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
    public function get_id_pk_brg_warehouse(){
        return $this->id_pk_brg_warehouse;
    }
    public function get_brg_warehouse_qty(){
        return $this->brg_warehouse_qty;
    }
    public function get_brg_warehouse_notes(){
        return $this->brg_warehouse_notes;
    }
    public function get_brg_warehouse_status(){
        return $this->brg_warehouse_status;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
    public function get_id_fk_warehouse(){
        return $this->id_fk_warehouse;
    }
}