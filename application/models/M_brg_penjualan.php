<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_penjualan extends CI_Model{
    private $tbl_name = "TBL_BRG_PENJUALAN";
    private $columns = array();
    private $id_pk_brg_penjualan;
    private $brg_penjualan_qty;
    private $brg_penjualan_satuan;
    private $brg_penjualan_harga;
    private $brg_penjualan_note;
    private $brg_penjualan_status;
    private $id_fk_penjualan;
    private $id_fk_barang;
    private $brg_penjualan_create_date;
    private $brg_penjualan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->brg_penjualan_create_date = date("Y-m-d H:i:s");
        $this->brg_penjualan_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS TBL_BRG_PENJUALAN;
        CREATE TABLE TBL_BRG_PENJUALAN(
            ID_PK_BRG_PENJUALAN INT PRIMARY KEY AUTO_INCREMENT,
            BRG_PENJUALAN_QTY DOUBLE,
            BRG_PENJUALAN_SATUAN VARCHAR(20),
            BRG_PENJUALAN_HARGA INT,
            BRG_PENJUALAN_NOTE VARCHAR(150),
            BRG_PENJUALAN_STATUS VARCHAR(15),
            ID_FK_PENJUALAN INT,
            ID_FK_BARANG INT,
            BRG_PENJUALAN_CREATE_DATE DATETIME,
            BRG_PENJUALAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_PENJUALAN_LOG;
        CREATE TABLE TBL_BRG_PENJUALAN_LOG(
            ID_PK_BRG_PENJUALAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_PENJUALAN INT,
            BRG_PENJUALAN_QTY DOUBLE,
            BRG_PENJUALAN_SATUAN VARCHAR(20),
            BRG_PENJUALAN_HARGA INT,
            BRG_PENJUALAN_NOTE VARCHAR(150),
            BRG_PENJUALAN_STATUS VARCHAR(15),
            ID_FK_PENJUALAN INT,
            ID_FK_BARANG INT,
            BRG_PENJUALAN_CREATE_DATE DATETIME,
            BRG_PENJUALAN_LAST_MODIFIED DATETIME,
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
            SET @TGL_ACTION = NEW.BRG_PENJUALAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_PENJUALAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PENJUALAN,BRG_PENJUALAN_QTY,BRG_PENJUALAN_SATUAN,BRG_PENJUALAN_HARGA,BRG_PENJUALAN_NOTE,BRG_PENJUALAN_STATUS,ID_FK_PENJUALAN,ID_FK_BARANG,BRG_PENJUALAN_CREATE_DATE,BRG_PENJUALAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG_PENJUALAN,NEW.BRG_PENJUALAN_QTY,NEW.BRG_PENJUALAN_SATUAN,NEW.BRG_PENJUALAN_HARGA,NEW.BRG_PENJUALAN_NOTE,NEW.BRG_PENJUALAN_STATUS,NEW.ID_FK_PENJUALAN,NEW.ID_FK_BARANG,NEW.BRG_PENJUALAN_CREATE_DATE,NEW.BRG_PENJUALAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_PENJUALAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_PENJUALAN
        AFTER UPDATE ON TBL_BRG_PENJUALAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PENJUALAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_PENJUALAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PENJUALAN,BRG_PENJUALAN_QTY,BRG_PENJUALAN_SATUAN,BRG_PENJUALAN_HARGA,BRG_PENJUALAN_NOTE,BRG_PENJUALAN_STATUS,ID_FK_PENJUALAN,ID_FK_BARANG,BRG_PENJUALAN_CREATE_DATE,BRG_PENJUALAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG_PENJUALAN,NEW.BRG_PENJUALAN_QTY,NEW.BRG_PENJUALAN_SATUAN,NEW.BRG_PENJUALAN_HARGA,NEW.BRG_PENJUALAN_NOTE,NEW.BRG_PENJUALAN_STATUS,NEW.ID_FK_PENJUALAN,NEW.ID_FK_BARANG,NEW.BRG_PENJUALAN_CREATE_DATE,NEW.BRG_PENJUALAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function list(){
        $sql = "
        SELECT id_pk_brg_penjualan,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,id_fk_penjualan,id_fk_barang,brg_nama,brg_penjualan_create_date,brg_penjualan_last_modified
        FROM ".$this->tbl_name."
        INNER JOIN MSTR_BARANG ON MSTR_BARANG.ID_PK_BRG = ".$this->tbl_name.".ID_FK_BARANG
        WHERE BRG_PENJUALAN_STATUS = ? AND ID_FK_PENJUALAN = ?
        ";
        $args = array(
            "AKTIF",$this->id_fk_penjualan
        );
        return executeQuery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_penjualan_qty" => $this->brg_penjualan_qty,
                "brg_penjualan_satuan" => $this->brg_penjualan_satuan,
                "brg_penjualan_harga" => $this->brg_penjualan_harga,
                "brg_penjualan_note" => $this->brg_penjualan_note,
                "brg_penjualan_status" => $this->brg_penjualan_status,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_penjualan_create_date" => $this->brg_penjualan_create_date,
                "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
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
                "id_pk_brg_penjualan" => $this->id_pk_brg_penjualan,
            );
            $data = array(
                "brg_penjualan_qty" => $this->brg_penjualan_qty,
                "brg_penjualan_satuan" => $this->brg_penjualan_satuan,
                "brg_penjualan_harga" => $this->brg_penjualan_harga,
                "brg_penjualan_note" => $this->brg_penjualan_note,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_brg_penjualan" => $this->id_pk_brg_penjualan,
            );
            $data = array(
                "brg_penjualan_status" => "NONAKTIF",
                "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;

        }
    }
    public function check_insert(){
        if($this->brg_penjualan_qty == ""){
            return false;
        }
        if($this->brg_penjualan_satuan == ""){
            return false;
        }
        if($this->brg_penjualan_harga == ""){
            return false;
        }
        if($this->brg_penjualan_note == ""){
            return false;
        }
        if($this->brg_penjualan_status == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_penjualan_create_date == ""){
            return false;
        }
        if($this->brg_penjualan_last_modified == ""){
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
        if($this->brg_penjualan_qty == ""){
            return false;
        }
        if($this->brg_penjualan_satuan == ""){
            return false;
        }
        if($this->brg_penjualan_harga == ""){
            return false;
        }
        if($this->brg_penjualan_note == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_penjualan_last_modified == ""){
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
        if($this->brg_penjualan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_penjualan_qty,$brg_penjualan_satuan,$brg_penjualan_harga,$brg_penjualan_note,$brg_penjualan_status,$id_fk_penjualan,$id_fk_barang){
        if(!$this->set_brg_penjualan_qty($brg_penjualan_qty)){
            return false;
        }
        if(!$this->set_brg_penjualan_satuan($brg_penjualan_satuan)){
            return false;
        }
        if(!$this->set_brg_penjualan_harga($brg_penjualan_harga)){
            return false;
        }
        if(!$this->set_brg_penjualan_note($brg_penjualan_note)){
            return false;
        }
        if(!$this->set_brg_penjualan_status($brg_penjualan_status)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_penjualan,$brg_penjualan_qty,$brg_penjualan_satuan,$brg_penjualan_harga,$brg_penjualan_note,$id_fk_barang){
        if(!$this->set_id_pk_brg_penjualan($id_pk_brg_penjualan)){
            return false;
        }
        if(!$this->set_brg_penjualan_qty($brg_penjualan_qty)){
            return false;
        }
        if(!$this->set_brg_penjualan_satuan($brg_penjualan_satuan)){
            return false;
        }
        if(!$this->set_brg_penjualan_harga($brg_penjualan_harga)){
            return false;
        }
        if(!$this->set_brg_penjualan_note($brg_penjualan_note)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_penjualan){
        if($this->set_id_pk_brg_penjualan($id_pk_brg_penjualan)){
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
    public function set_brg_penjualan_qty($brg_penjualan_qty){
        if($brg_penjualan_qty != ""){
            $this->brg_penjualan_qty = $brg_penjualan_qty;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_satuan($brg_penjualan_satuan){
        if($brg_penjualan_satuan != ""){
            $this->brg_penjualan_satuan = $brg_penjualan_satuan;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_harga($brg_penjualan_harga){
        if($brg_penjualan_harga != ""){
            $this->brg_penjualan_harga = $brg_penjualan_harga;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_note($brg_penjualan_note){
        if($brg_penjualan_note != ""){
            $this->brg_penjualan_note = $brg_penjualan_note;
            return true;
        }
        return false;
    }
    public function set_brg_penjualan_status($brg_penjualan_status){
        if($brg_penjualan_status != ""){
            $this->brg_penjualan_status = $brg_penjualan_status;
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
    public function set_id_fk_barang($id_fk_barang){
        if($id_fk_barang != ""){
            $this->id_fk_barang = $id_fk_barang;
            return true;
        }
        return false;
    }
    public function get_id_pk_brg_penjualan(){
        return $this->id_pk_brg_penjualan;
    }
    public function get_brg_penjualan_qty(){
        return $this->brg_penjualan_qty;
    }
    public function get_brg_penjualan_satuan(){
        return $this->brg_penjualan_satuan;
    }
    public function get_brg_penjualan_harga(){
        return $this->brg_penjualan_harga;
    }
    public function get_brg_penjualan_note(){
        return $this->brg_penjualan_note;
    }
    public function get_brg_penjualan_status(){
        return $this->brg_penjualan_note;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
    public function get_id_fk_barang(){
        return $this->id_fk_barang;
    }
}