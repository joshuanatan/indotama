<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_pembelian extends CI_Model{
    private $tbl_name = "TBL_BRG_PEMBELIAN";
    private $columns = array();
    private $id_pk_brg_pembelian;
    private $brg_pem_qty;
    private $brg_pem_satuan;
    private $brg_pem_harga;
    private $brg_pem_note;
    private $id_fk_pembelian;
    private $id_fk_barang;
    private $brg_pem_create_date;
    private $brg_pem_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->brg_pem_create_date = date("Y-m-d H:i:s");
        $this->brg_pem_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS TBL_BRG_PEMBELIAN;
        CREATE TABLE TBL_BRG_PEMBELIAN(
            ID_PK_BRG_PEMBELIAN INT PRIMARY KEY AUTO_INCREMENT,
            BRG_PEM_QTY DOUBLE,
            BRG_PEM_SATUAN VARCHAR(20),
            BRG_PEM_HARGA INT,
            BRG_PEM_NOTE VARCHAR(150),
            ID_FK_PEMBELIAN INT,
            ID_FK_BARANG INT,
            BRG_PEM_CREATE_DATE DATETIME,
            BRG_PEM_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_PEMBELIAN_LOG;
        CREATE TABLE TBL_BRG_PEMBELIAN_LOG(
            ID_PK_BRG_PEMBELIAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_PEMBELIAN INT,
            BRG_PEM_QTY DOUBLE,
            BRG_PEM_SATUAN VARCHAR(20),
            BRG_PEM_HARGA INT,
            BRG_PEM_NOTE VARCHAR(150),
            ID_FK_PEMBELIAN INT,
            ID_FK_BARANG INT,
            BRG_PEM_CREATE_DATE DATETIME,
            BRG_PEM_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BRG_PEMBELIAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BRG_PEMBELIAN
        AFTER INSERT ON TBL_BRG_PEMBELIAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PEM_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_PEM_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PEMBELIAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PEMBELIAN,BRG_PEM_QTY,BRG_PEM_SATUAN,BRG_PEM_HARGA,BRG_PEM_NOTE,ID_FK_PEMBELIAN,ID_FK_BARANG,BRG_PEM_CREATE_DATE,BRG_PEM_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG_PEMBELIAN,NEW.BRG_PEM_QTY,NEW.BRG_PEM_SATUAN,NEW.BRG_PEM_HARGA,NEW.BRG_PEM_NOTE,NEW.ID_FK_PEMBELIAN,NEW.ID_FK_BARANG,NEW.BRG_PEM_CREATE_DATE,NEW.BRG_PEM_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_PEMBELIAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_PEMBELIAN
        AFTER UPDATE ON TBL_BRG_PEMBELIAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PEM_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_PEM_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PEMBELIAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PEMBELIAN,BRG_PEM_QTY,BRG_PEM_SATUAN,BRG_PEM_HARGA,BRG_PEM_NOTE,ID_FK_PEMBELIAN,ID_FK_BARANG,BRG_PEM_CREATE_DATE,BRG_PEM_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG_PEMBELIAN,NEW.BRG_PEM_QTY,NEW.BRG_PEM_SATUAN,NEW.BRG_PEM_HARGA,NEW.BRG_PEM_NOTE,NEW.ID_FK_PEMBELIAN,NEW.ID_FK_BARANG,NEW.BRG_PEM_CREATE_DATE,NEW.BRG_PEM_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_pem_qty" => $this->brg_pem_qty,
                "brg_pem_satuan" => $this->brg_pem_satuan,
                "brg_pem_harga" => $this->brg_pem_harga,
                "brg_pem_note" => $this->brg_pem_note,
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_pem_create_date" => $this->brg_pem_create_date,
                "brg_pem_last_modified" => $this->brg_pem_last_modified,
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
                "id_pk_brg_pembelian" => $this->id_pk_brg_pembelian,
            );
            $data = array(
                "brg_pem_qty" => $this->brg_pem_qty,
                "brg_pem_satuan" => $this->brg_pem_satuan,
                "brg_pem_harga" => $this->brg_pem_harga,
                "brg_pem_note" => $this->brg_pem_note,
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "id_fk_barang" => $this->id_fk_barang,
                "brg_pem_last_modified" => $this->brg_pem_last_modified,
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
                "id_pk_brg_pembelian" => $this->id_pk_brg_pembelian,
            );
            $data = array(
                "brg_pem_last_modified" => $this->brg_pem_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;

        }
    }
    public function check_insert(){
        if($this->brg_pem_qty == ""){
            return false;
        }
        if($this->brg_pem_satuan == ""){
            return false;
        }
        if($this->brg_pem_harga == ""){
            return false;
        }
        if($this->brg_pem_note == ""){
            return false;
        }
        if($this->id_fk_pembelian == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_pem_create_date == ""){
            return false;
        }
        if($this->brg_pem_last_modified == ""){
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

        if($this->id_pk_brg_pembelian == ""){
            return false;
        }
        if($this->brg_pem_qty == ""){
            return false;
        }
        if($this->brg_pem_satuan == ""){
            return false;
        }
        if($this->brg_pem_harga == ""){
            return false;
        }
        if($this->brg_pem_note == ""){
            return false;
        }
        if($this->id_fk_pembelian == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->brg_pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_pembelian == ""){
            return false;
        }
        if($this->brg_pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_pem_qty,$brg_pem_satuan,$brg_pem_harga,$brg_pem_note,$id_fk_pembelian,$id_fk_barang){
        if(!$this->set_brg_pem_qty($brg_pem_qty)){
            return false;
        }
        if(!$this->set_brg_pem_satuan($brg_pem_satuan)){
            return false;
        }
        if(!$this->set_brg_pem_harga($brg_pem_harga)){
            return false;
        }
        if(!$this->set_brg_pem_note($brg_pem_note)){
            return false;
        }
        if(!$this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_pembelian,$brg_pem_satuan,$brg_pem_harga,$brg_pem_note,$id_fk_pembelian,$id_fk_barang){
        if($this->set_id_pk_brg_pembelian($id_pk_brg_pembelian)){
            return false;
        }
        if($this->set_brg_pem_qty($brg_pem_qty)){
            return false;
        }
        if($this->set_brg_pem_satuan($brg_pem_satuan)){
            return false;
        }
        if(!$this->set_brg_pem_harga($brg_pem_harga)){
            return false;
        }
        if($this->set_brg_pem_note($brg_pem_note)){
            return false;
        }
        if($this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        if($this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_pembelian){
        if($this->set_id_pk_brg_pembelian($id_pk_brg_pembelian)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_pembelian($id_pk_brg_pembelian){
        if($id_pk_brg_pembelian != ""){
            $this->id_pk_brg_pembelian = $id_pk_brg_pembelian;
            return true;
        }
        return false;
    }
    public function set_brg_pem_qty($brg_pem_qty){
        if($brg_pem_qty != ""){
            $this->brg_pem_qty = $brg_pem_qty;
            return true;
        }
        return false;
    }
    public function set_brg_pem_satuan($brg_pem_satuan){
        if($brg_pem_satuan != ""){
            $this->brg_pem_satuan = $brg_pem_satuan;
            return true;
        }
        return false;
    }
    public function set_brg_pem_harga($brg_pem_harga){
        if($brg_pem_harga != ""){
            $this->brg_pem_harga = $brg_pem_harga;
            return true;
        }
        return false;
    }
    public function set_brg_pem_note($brg_pem_note){
        if($brg_pem_note != ""){
            $this->brg_pem_note = $brg_pem_note;
            return true;
        }
        return false;
    }
    public function set_id_fk_pembelian($id_fk_pembelian){
        if($id_fk_pembelian != ""){
            $this->id_fk_pembelian = $id_fk_pembelian;
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
    public function get_id_pk_brg_pembelian(){
        return $this->id_pk_brg_pembelian;
    }
    public function get_brg_pem_qty(){
        return $this->brg_pem_qty;
    }
    public function get_brg_pem_satuan(){
        return $this->brg_pem_satuan;
    }
    public function get_brg_pem_harga(){
        return $this->brg_pem_harga;
    }
    public function get_brg_pem_note(){
        return $this->brg_pem_note;
    }
    public function get_id_fk_pembelian(){
        return $this->id_fk_pembelian;
    }
    public function get_id_fk_barang(){
        return $this->id_fk_barang;
    }
}