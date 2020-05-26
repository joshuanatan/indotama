<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_permintaan extends CI_Model{
    private $tbl_name = "TBL_BRG_PERMINTAAN";
    private $columns = array();
    private $id_pk_brg_permintaan;
    private $brg_permintaan_qty;
    private $brg_permintaan_notes;
    private $brg_permintaan_deadline;
    private $brg_permintaan_status;
    private $id_fk_brg;
    private $id_fk_cabang;
    private $brg_permintaan_create_date;
    private $brg_permintaan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_permintaan_create_date","Tanggal Permintaan",true);
        $this->set_column("brg_nama","Nama Barang",false);
        $this->set_column("brg_permintaan_pemenuhan_qty","Jumlah terpenuhi",false);
        $this->set_column("brg_permintaan_qty","Total Permintaan",false);
        $this->set_column("brg_permintaan_status","Status Permintaan",false);
        $this->brg_permintaan_create_date = date("Y-m-d H:i:s");
        $this->brg_permintaan_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS TBL_BRG_PERMINTAAN;
        CREATE TABLE TBL_BRG_PERMINTAAN(
            ID_PK_BRG_PERMINTAAN INT PRIMARY KEY AUTO_INCREMENT,
            BRG_PERMINTAAN_QTY INT,
            BRG_PERMINTAAN_NOTES TEXT,
            BRG_PERMINTAAN_DEADLINE DATE,
            BRG_PERMINTAAN_STATUS VARCHAR(6),
            ID_FK_BRG INT,
            ID_FK_CABANG INT,
            BRG_PERMINTAAN_CREATE_DATE DATETIME,
            BRG_PERMINTAAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_PERMINTAAN_LOG;
        CREATE TABLE TBL_BRG_PERMINTAAN_LOG(
            ID_PK_PENERIMAAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_PERMINTAAN INT,
            BRG_PERMINTAAN_QTY INT,
            BRG_PERMINTAAN_NOTES TEXT,
            BRG_PERMINTAAN_DEADLINE DATE,
            BRG_PERMINTAAN_STATUS VARCHAR(6),
            ID_FK_BRG INT,
            ID_FK_CABANG INT,
            BRG_PERMINTAAN_CREATE_DATE DATETIME,
            BRG_PERMINTAAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT 
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BRG_PERMINTAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BRG_PERMINTAAN
        AFTER INSERT ON TBL_BRG_PERMINTAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PERMINTAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT ' , NEW.BRG_PERMINTAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PERMINTAAN_LOG(EXECUTED_FUNCTION,
            ID_PK_BRG_PERMINTAAN,
            BRG_PERMINTAAN_QTY,
            BRG_PERMINTAAN_NOTES,
            BRG_PERMINTAAN_DEADLINE,
            BRG_PERMINTAAN_STATUS,
            ID_FK_BRG,
            ID_FK_CABANG,
            BRG_PERMINTAAN_CREATE_DATE,
            BRG_PERMINTAAN_LAST_MODIFIED,
            ID_CREATE_DATA,
            ID_LAST_MODIFIED,
            ID_LOG_ALL) VALUES ('AFTER INSERT',
            NEW.ID_PK_BRG_PERMINTAAN,
            NEW.BRG_PERMINTAAN_QTY,
            NEW.BRG_PERMINTAAN_NOTES,
            NEW.BRG_PERMINTAAN_DEADLINE,
            NEW.BRG_PERMINTAAN_STATUS,
            NEW.ID_FK_BRG,
            NEW.ID_FK_CABANG,
            NEW.BRG_PERMINTAAN_CREATE_DATE,
            NEW.BRG_PERMINTAAN_LAST_MODIFIED,
            NEW.ID_CREATE_DATA,
            NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_PERMINTAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_PERMINTAAN
        AFTER UPDATE ON TBL_BRG_PERMINTAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PERMINTAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT ' , NEW.BRG_PERMINTAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PERMINTAAN_LOG(EXECUTED_FUNCTION,
            ID_PK_BRG_PERMINTAAN,
            BRG_PERMINTAAN_QTY,
            BRG_PERMINTAAN_NOTES,
            BRG_PERMINTAAN_DEADLINE,
            BRG_PERMINTAAN_STATUS,
            ID_FK_BRG,
            ID_FK_CABANG,
            BRG_PERMINTAAN_CREATE_DATE,
            BRG_PERMINTAAN_LAST_MODIFIED,
            ID_CREATE_DATA,
            ID_LAST_MODIFIED,
            ID_LOG_ALL) VALUES ('AFTER INSERT',
            NEW.ID_PK_BRG_PERMINTAAN,
            NEW.BRG_PERMINTAAN_QTY,
            NEW.BRG_PERMINTAAN_NOTES,
            NEW.BRG_PERMINTAAN_DEADLINE,
            NEW.BRG_PERMINTAAN_STATUS,
            NEW.ID_FK_BRG,
            NEW.ID_FK_CABANG,
            NEW.BRG_PERMINTAAN_CREATE_DATE,
            NEW.BRG_PERMINTAAN_LAST_MODIFIED,
            NEW.ID_CREATE_DATA,
            NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                id_pk_brg_permintaan LIKE '%".$search_key."%' OR
                brg_permintaan_qty LIKE '%".$search_key."%' OR
                brg_permintaan_notes LIKE '%".$search_key."%' OR
                brg_permintaan_deadline LIKE '%".$search_key."%' OR
                brg_permintaan_status LIKE '%".$search_key."%' OR
                id_fk_brg LIKE '%".$search_key."%' OR
                id_fk_cabang LIKE '%".$search_key."%' OR
                brg_permintaan_create_date LIKE '%".$search_key."%' OR
                brg_permintaan_last_modified LIKE '%".$search_key."%' OR
                brg_nama LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_brg_permintaan, brg_permintaan_qty, brg_nama, brg_permintaan_notes, brg_permintaan_deadline, brg_permintaan_status, tbl_brg_permintaan.id_fk_brg, tbl_brg_permintaan.id_fk_cabang, brg_permintaan_create_date, brg_permintaan_last_modified, sum(tbl_brg_pemenuhan.BRG_PEMENUHAN_QTY) as qty_pemenuhan, cabang_daerah FROM tbl_brg_permintaan JOIN mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg JOIN mstr_cabang on mstr_cabang.id_pk_cabang =tbl_brg_permintaan.id_fk_cabang left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan WHERE tbl_brg_permintaan.ID_FK_CABANG = ? AND tbl_brg_permintaan.brg_permintaan_status!='BATAL' group by id_pk_brg_permintaan  ".$search_query." 
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            $this->session->id_cabang
        );
        $result["data"] = executeQuery($query,$args);
        
        
        $query = "
        SELECT id_pk_brg_permintaan, brg_permintaan_qty, brg_nama, brg_permintaan_notes, brg_permintaan_deadline, brg_permintaan_status, tbl_brg_permintaan.id_fk_brg, tbl_brg_permintaan.id_fk_cabang, brg_permintaan_create_date, brg_permintaan_last_modified, sum(tbl_brg_pemenuhan.BRG_PEMENUHAN_QTY) as qty_pemenuhan, cabang_daerah FROM tbl_brg_permintaan JOIN mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg JOIN mstr_cabang on mstr_cabang.id_pk_cabang =tbl_brg_permintaan.id_fk_cabang left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan WHERE tbl_brg_permintaan.ID_FK_CABANG = ? AND tbl_brg_permintaan.brg_permintaan_status!='BATAL' group by id_pk_brg_permintaan  ".$search_query." 
        ORDER BY ".$order_by." ".$order_direction;
        $args = array(
            $this->session->id_cabang
        );
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_permintaan_qty" => $this->brg_permintaan_qty,
                "brg_permintaan_notes" => $this->brg_permintaan_notes,
                "brg_permintaan_deadline" => $this->brg_permintaan_deadline,
                "brg_permintaan_status" => $this->brg_permintaan_status,
                "id_fk_brg" => $this->id_fk_brg,
                "id_fk_cabang" => $this->id_fk_cabang,
                "brg_permintaan_create_date" => $this->brg_permintaan_create_date,
                "brg_permintaan_last_modified" => $this->brg_permintaan_last_modified,
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
                "id_pk_brg_permintaan" => $this->id_pk_brg_permintaan
            );
            $data = array(
                //ceklagi
                "brg_permintaan_qty" => $this->brg_permintaan_qty,
                "brg_permintaan_notes" => $this->brg_permintaan_notes,
                "brg_permintaan_deadline" => $this->brg_permintaan_deadline,
                "brg_permintaan_last_modified" => $this->brg_permintaan_last_modified,
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
                "id_pk_brg_permintaan" => $this->id_pk_brg_permintaan
            );
            $data = array(
                "brg_permintaan_status" => "BATAL",
                "brg_permintaan_last_modified" => $this->brg_permintaan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_permintaan_qty == ""){
            return false;
        }
        if($this->brg_permintaan_notes == ""){
            return false;
        }
        if($this->brg_permintaan_deadline == ""){
            return false;
        }
        if($this->brg_permintaan_status == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->brg_permintaan_create_date == ""){
            return false;
        }
        if($this->brg_permintaan_last_modified == ""){
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
        //ceklagi
        if($this->brg_permintaan_qty == ""){
            return false;
        }
        if($this->brg_permintaan_notes == ""){
            return false;
        }
        if($this->brg_permintaan_deadline == ""){
            return false;
        }
        if($this->brg_permintaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_permintaan == ""){
            return false;
        }
        if($this->brg_permintaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($brg_permintaan_qty,$brg_permintaan_notes,$brg_permintaan_deadline,$brg_permintaan_status,$id_fk_brg,$id_fk_cabang){
        if(!$this->set_brg_permintaan_qty($brg_permintaan_qty)){
            return false;
        }
        if(!$this->set_brg_permintaan_notes($brg_permintaan_notes)){
            return false;
        }
        if(!$this->set_brg_permintaan_deadline($brg_permintaan_deadline)){
            return false;
        }
        if(!$this->set_brg_permintaan_status($brg_permintaan_status)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        return true;
    }
    public function set_update($brg_permintaan_qty,$brg_permintaan_notes,$brg_permintaan_deadline,$id_pk_brg_permintaan){
        //ceklagi
        if(!$this->set_brg_permintaan_qty($brg_permintaan_qty)){
            return false;
        }
        if(!$this->set_brg_permintaan_notes($brg_permintaan_notes)){
            return false;
        }
        if(!$this->set_brg_permintaan_deadline($brg_permintaan_deadline)){
            return false;
        }
        if(!$this->set_id_pk_brg_permintaan($id_pk_brg_permintaan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_permintaan){
        if(!$this->set_id_pk_brg_permintaan($id_pk_brg_permintaan)){
            return false;
        }

        return true;
    }
    public function set_brg_permintaan_qty($brg_permintaan_qty){
        if($brg_permintaan_qty != ""){
            $this->brg_permintaan_qty = $brg_permintaan_qty;
            return true;
        }
        return false;
    }
    public function set_brg_permintaan_notes($brg_permintaan_notes){
        if($brg_permintaan_notes != ""){
            $this->brg_permintaan_notes = $brg_permintaan_notes;
            return true;
        }
        return false;
    }
    public function set_brg_permintaan_deadline($brg_permintaan_deadline){
        if($brg_permintaan_deadline != ""){
            $this->brg_permintaan_deadline = $brg_permintaan_deadline;
            return true;
        }
        return false;
    }
    public function set_brg_permintaan_status($brg_permintaan_status){
        if($brg_permintaan_status != ""){
            $this->brg_permintaan_status = $brg_permintaan_status;
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
    public function set_id_fk_cabang($id_fk_cabang){
        if($id_fk_cabang != ""){
            $this->id_fk_cabang = $id_fk_cabang;
            return true;
        }
        return false;
    }
    public function set_id_pk_brg_permintaan($id_pk_brg_permintaan){
        if($id_pk_brg_permintaan != ""){
            $this->id_pk_brg_permintaan = $id_pk_brg_permintaan;
            return true;
        }
        return false;
    }
    public function get_id_pk_brg_permintaan(){
        return $this->id_pk_brg_permintaan;
    }
    public function get_brg_permintaan_qty(){
        return $this->brg_permintaan_qty;
    }
    public function get_brg_permintaan_notes(){
        return $this->brg_permintaan_notes;
    }
    public function get_brg_permintaan_deadline(){
        return $this->brg_permintaan_deadline;
    }
    public function get_brg_permintaan_status(){
        return $this->brg_permintaan_status;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
}