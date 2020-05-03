<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_penjualan extends CI_Model{
    private $tbl_name = "TBL_BRG_PENJUALAN";
    private $columns = array();
    private $id_pk_brg_penjualan;
    private $brg_penj_qty;
    private $brg_penj_jenis_harga;
    private $brg_penj_value_jenis_harga;
    private $brg_penj_harga_final_brg;
    private $brg_penj_status;
    private $id_fk_barang;
    private $id_fk_penjualan;
    private $brg_penj_create_date;
    private $brg_penj_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->brg_penj_create_date = date("Y-m-d H:i:s");
        $this->brg_penj_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS TBL_BRG_PENJUALAN;
        CREATE TABLE TBL_BRG_PENJUALAN(
            ID_PK_BRG_PENJUALAN INT PRIMARY KEY AUTO_INCREMENT,
            BRG_PENJ_QTY DOUBLE,
            BRG_PENJ_JENIS_HARGA VARCHAR(20),
            BRG_PENJ_VALUE_JENIS_HARGA INT,
            BRG_PENJ_HARGA_FINAL_BRG INT,
            BRG_PENJ_STATUS VARCHAR(15),
            ID_FK_BARANG INT,
            ID_FK_PENJUALAN INT,
            BRG_PENJ_CREATE_DATE DATETIME,
            BRG_PENJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_PENJUALAN_LOG;
        CREATE TABLE TBL_BRG_PENJUALAN_LOG(
            ID_PK_BRG_PENJUALAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_PENJUALAN INT,
            BRG_PENJ_QTY DOUBLE,
            BRG_PENJ_JENIS_HARGA VARCHAR(20),
            BRG_PENJ_VALUE_JENIS_HARGA INT,
            BRG_PENJ_HARGA_FINAL_BRG INT,
            BRG_PENJ_STATUS VARCHAR(15),
            ID_FK_BARANG INT,
            ID_FK_PENJUALAN INT,
            BRG_PENJ_CREATE_DATE DATETIME,
            BRG_PENJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BRG_PENJUALAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BRG_PENJUALAN
        AFTER INSERT ON TBL_BRG_PENJUALAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PENJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_PENJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PENJUALAN,BRG_PENJ_QTY,BRG_PENJ_JENIS_HARGA,BRG_PENJ_VALUE_JENIS_HARGA,BRG_PENJ_HARGA_FINAL_BRG,BRG_PENJ_STATUS,ID_FK_BARANG,ID_FK_PENJUALAN,BRG_PENJ_CREATE_DATE,BRG_PENJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG_PENJUALAN,NEW.BRG_PENJ_QTY,NEW.BRG_PENJ_JENIS_HARGA,NEW.BRG_PENJ_VALUE_JENIS_HARGA,NEW.BRG_PENJ_HARGA_FINAL_BRG,NEW.BRG_PENJ_STATUS,NEW.ID_FK_BARANG,NEW.ID_FK_PENJUALAN,NEW.BRG_PENJ_CREATE_DATE,NEW.BRG_PENJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_PENJUALAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_PENJUALAN
        AFTER UPDATE ON TBL_BRG_PENJUALAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PENJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_PENJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PENJUALAN,BRG_PENJ_QTY,BRG_PENJ_JENIS_HARGA,BRG_PENJ_VALUE_JENIS_HARGA,BRG_PENJ_HARGA_FINAL_BRG,BRG_PENJ_STATUS,ID_FK_BARANG,ID_FK_PENJUALAN,BRG_PENJ_CREATE_DATE,BRG_PENJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG_PENJUALAN,NEW.BRG_PENJ_QTY,NEW.BRG_PENJ_JENIS_HARGA,NEW.BRG_PENJ_VALUE_JENIS_HARGA,NEW.BRG_PENJ_HARGA_FINAL_BRG,NEW.BRG_PENJ_STATUS,NEW.ID_FK_BARANG,NEW.ID_FK_PENJUALAN,NEW.BRG_PENJ_CREATE_DATE,NEW.BRG_PENJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_penj_qty" => $this->brg_penj_qty,
                "brg_penj_jenis_harga" => $this->brg_penj_jenis_harga,
                "brg_penj_value_jenis_harga" => $this->brg_penj_value_jenis_harga,
                "brg_penj_harga_final_brg" => $this->brg_penj_harga_final_brg,
                "brg_penj_status" => $this->brg_penj_status,
                "id_fk_barang" => $this->id_fk_barang,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "brg_penj_create_date" => $this->brg_penj_create_date,
                "brg_penj_last_modified" => $this->brg_penj_last_modified,
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
                "id_pk_brg_penjualan" => $this->id_pk_brg_penjualan
            );
            $data = array(
                "brg_penj_qty" => $this->brg_penj_qty,
                "brg_penj_jenis_harga" => $this->brg_penj_jenis_harga,
                "brg_penj_value_jenis_harga" => $this->brg_penj_value_jenis_harga,
                "brg_penj_harga_final_brg" => $this->brg_penj_harga_final_brg,
                "id_fk_barang" => $this->id_fk_barang,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "brg_penj_last_modified" => $this->brg_penj_last_modified,
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
                "id_pk_brg_penjualan" => $this->id_pk_brg_penjualan
            );
            $data = array(
                "brg_penj_status" => "NONAKTIF",
                "brg_penj_last_modified" => $this->brg_penj_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_penj_qty == ""){
            return false;
        }
        if($this->brg_penj_jenis_harga == ""){
            return false;
        }
        if($this->brg_penj_value_jenis_harga == ""){
            return false;
        }
        if($this->brg_penj_harga_final_brg == ""){
            return false;
        }
        if($this->brg_penj_status == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->brg_penj_create_date == ""){
            return false;
        }
        if($this->brg_penj_last_modified == ""){
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
        if($this->id_pk_brg_penjualan == ""){
            return false;
        }
        if($this->brg_penj_qty == ""){
            return false;
        }
        if($this->brg_penj_jenis_harga == ""){
            return false;
        }
        if($this->brg_penj_value_jenis_harga == ""){
            return false;
        }
        if($this->brg_penj_harga_final_brg == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->brg_penj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_penjualan == ""){
            return false;
        }
        if($this->brg_penj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_penj_qty,$brg_penj_jenis_harga,$brg_penj_value_jenis_harga,$brg_penj_harga_final_brg,$brg_penj_status,$id_fk_barang,$id_fk_penjualan){
        if(!$this->set_brg_penj_qty($brg_penj_qty)){
            return false;
        }
        if(!$this->set_brg_penj_jenis_harga($brg_penj_jenis_harga)){
            return false;
        }
        if(!$this->set_brg_penj_value_jenis_harga($brg_penj_value_jenis_harga)){
            return false;
        }
        if(!$this->set_brg_penj_harga_final_brg($brg_penj_harga_final_brg)){
            return false;
        }
        if(!$this->set_brg_penj_status($brg_penj_status)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_penjualan,$brg_penj_qty,$brg_penj_jenis_harga,$brg_penj_value_jenis_harga,$brg_penj_harga_final_brg,$id_fk_barang,$id_fk_penjualan){

        if(!$this->set_id_pk_brg_penjualan($id_pk_brg_penjualan)){
            return false;
        }
        if(!$this->set_brg_penj_qty($brg_penj_qty)){
            return false;
        }
        if(!$this->set_brg_penj_jenis_harga($brg_penj_jenis_harga)){
            return false;
        }
        if(!$this->set_brg_penj_value_jenis_harga($brg_penj_value_jenis_harga)){
            return false;
        }
        if(!$this->set_brg_penj_harga_final_brg($brg_penj_harga_final_brg)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_penjualan){
        if(!$this->set_id_pk_brg_penjualan($id_pk_brg_penjualan)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_penjualan($id_pk_brg_penjualan){
        if($id_pk_brg_penjualan != ""){
            $this->id_pk_brg_penjualan = $id_pk_brg_penjualan;
            return true;
        }
        return false;
    }
    public function set_brg_penj_qty($brg_penj_qty){
        if($brg_penj_qty != ""){
            $this->brg_penj_qty = $brg_penj_qty;
            return true;
        }
        return false;
    }
    public function set_brg_penj_jenis_harga($brg_penj_jenis_harga){
        if($brg_penj_jenis_harga != ""){
            $this->brg_penj_jenis_harga = $brg_penj_jenis_harga;
            return true;
        }
        return false;
    }
    public function set_brg_penj_value_jenis_harga($brg_penj_value_jenis_harga){
        if($brg_penj_value_jenis_harga != ""){
            $this->brg_penj_value_jenis_harga = $brg_penj_value_jenis_harga;
            return true;
        }
        return false;
    }
    public function set_brg_penj_harga_final_brg($brg_penj_harga_final_brg){
        if($brg_penj_harga_final_brg != ""){
            $this->brg_penj_harga_final_brg = $brg_penj_harga_final_brg;
            return true;
        }
        return false;
    }
    public function set_brg_penj_status($brg_penj_status){
        if($brg_penj_status != ""){
            $this->brg_penj_status = $brg_penj_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_barang($id_fk_barang){
        if($id_fk_barang != ""){
            $this->id_fk_barang = $id_fk_barang;
            return true;
        }
        return false;
    }
    public function set_id_fk_penjualan($id_fk_penjualan){
        if($id_fk_penjualan != ""){
            $this->id_fk_penjualan = $id_fk_penjualan;
            return true;
        }
        return false;
    }
    public function get_id_pk_brg_penjualan(){
        return $this->id_pk_brg_penjualan;
    }
    public function get_brg_penj_qty(){
        return $this->brg_penj_qty;
    }
    public function get_brg_penj_jenis_harga(){
        return $this->brg_penj_jenis_harga;
    }
    public function get_brg_penj_value_jenis_harga(){
        return $this->brg_penj_value_jenis_harga;
    }
    public function get_brg_penj_harga_final_brg(){
        return $this->brg_penj_harga_final_brg;
    }
    public function get_brg_penj_status(){
        return $this->brg_penj_status;
    }
    public function get_id_fk_barang(){
        return $this->id_fk_barang;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
}