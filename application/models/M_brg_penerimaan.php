<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_penerimaan extends CI_Model{
    private $tbl_name = "TBL_BRG_PENERIMAAN";
    private $columns = array();
    private $id_pk_brg_penerimaan;
    private $brg_penerimaan_qty;
    private $brg_penerimaan_note;
    private $id_fk_penerimaan;
    private $id_fk_brg_pembelian;
    private $id_fk_satuan;
    private $brg_penerimaan_create_date;
    private $brg_penerimaan_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->brg_penerimaan_create_date = date("Y-m-d H:i:s");
        $this->brg_penerimaan_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS TBL_BRG_PENERIMAAN;
        CREATE TABLE TBL_BRG_PENERIMAAN(
            ID_PK_BRG_PENERIMAAN INT PRIMARY KEY AUTO_INCREMENT,
            BRG_PENERIMAAN_QTY DOUBLE,
            BRG_PENERIMAAN_NOTE VARCHAR(200),
            ID_FK_PENERIMAAN INT,
            ID_FK_BRG_PEMBELIAN INT,
            ID_FK_SATUAN INT,
            BRG_PENERIMAAN_CREATE_DATE DATETIME,
            BRG_PENERIMAAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_PENERIMAAN_LOG;
        CREATE TABLE TBL_BRG_PENERIMAAN_LOG(
            ID_PK_BRG_PENERIMAAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_PENERIMAAN INT,
            BRG_PENERIMAAN_QTY DOUBLE,
            BRG_PENERIMAAN_NOTE VARCHAR(200),
            ID_FK_PENERIMAAN INT,
            ID_FK_BRG_PEMBELIAN INT,
            ID_FK_SATUAN INT,
            BRG_PENERIMAAN_CREATE_DATE DATETIME,
            BRG_PENERIMAAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BRG_PENERIMAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BRG_PENERIMAAN
        AFTER INSERT ON TBL_BRG_PENERIMAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PENERIMAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_PENERIMAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PENERIMAAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PENERIMAAN,BRG_PENERIMAAN_QTY,BRG_PENERIMAAN_NOTE,ID_FK_PENERIMAAN,ID_FK_BRG_PEMBELIAN,ID_FK_SATUAN,BRG_PENERIMAAN_CREATE_DATE,BRG_PENERIMAAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG_PENERIMAAN,NEW.BRG_PENERIMAAN_QTY,NEW.BRG_PENERIMAAN_NOTE,NEW.ID_FK_PENERIMAAN,NEW.ID_FK_BRG_PEMBELIAN,NEW.ID_FK_SATUAN,NEW.BRG_PENERIMAAN_CREATE_DATE,NEW.BRG_PENERIMAAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;

        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_PENERIMAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_PENERIMAAN
        AFTER UPDATE ON TBL_BRG_PENERIMAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PENERIMAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_PENERIMAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PENERIMAAN_LOG(EXECUTED_FUNCTION,ID_PK_BRG_PENERIMAAN,BRG_PENERIMAAN_QTY,BRG_PENERIMAAN_NOTE,ID_FK_PENERIMAAN,ID_FK_BRG_PEMBELIAN,ID_FK_SATUAN,BRG_PENERIMAAN_CREATE_DATE,BRG_PENERIMAAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG_PENERIMAAN,NEW.BRG_PENERIMAAN_QTY,NEW.BRG_PENERIMAAN_NOTE,NEW.ID_FK_PENERIMAAN,NEW.ID_FK_BRG_PEMBELIAN,NEW.ID_FK_SATUAN,NEW.BRG_PENERIMAAN_CREATE_DATE,NEW.BRG_PENERIMAAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
    }
    public function list(){
        $query = "
        SELECT id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_nama,satuan_nama
        FROM ".$this->tbl_name."
        INNER JOIN TBL_BRG_PEMBELIAN ON TBL_BRG_PEMBELIAN.ID_PK_BRG_PEMBELIAN = ".$this->tbl_name.".ID_FK_BRG_PEMBELIAN
        INNER JOIN MSTR_BARANG ON MSTR_BARANG.ID_PK_BRG = TBL_BRG_PEMBELIAN.ID_FK_BARANG
        INNER JOIN MSTR_SATUAN ON MSTR_SATUAN.ID_PK_SATUAN = ".$this->tbl_name.".ID_FK_SATUAN
        WHERE ID_FK_PENERIMAAN = ? AND BRG_PEM_STATUS = ? AND BRG_STATUS = ?
        ";
        $args = array(
            $this->id_fk_penerimaan,"AKTIF","AKTIF"
        );
        return executeQuery($query,$args);
    }
    public function insert(){
        $data = array(
            "brg_penerimaan_qty" => $this->brg_penerimaan_qty,
            "brg_penerimaan_note" => $this->brg_penerimaan_note,
            "id_fk_penerimaan" => $this->id_fk_penerimaan,
            "id_fk_brg_pembelian" => $this->id_fk_brg_pembelian,
            "id_fk_satuan" => $this->id_fk_satuan,
            "brg_penerimaan_create_date" => $this->brg_penerimaan_create_date,
            "brg_penerimaan_last_modified" => $this->brg_penerimaan_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified
        );
        return insertRow($this->tbl_name,$data);
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_brg_penerimaan" => $this->id_pk_brg_penerimaan
            );
            $data = array(
                "brg_penerimaan_qty" => $this->brg_penerimaan_qty,
                "brg_penerimaan_note" => $this->brg_penerimaan_note,
                "id_fk_satuan" => $this->id_fk_satuan,
                "brg_penerimaan_last_modified" => $this->brg_penerimaan_last_modified,
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
                "id_pk_brg_penerimaan" => $this->id_pk_brg_penerimaan
            );
            $data = array(
                "brg_penerimaan_last_modified" => $this->brg_penerimaan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_penerimaan_qty == ""){
            return false;
        }
        if($this->brg_penerimaan_note == ""){
            return false;
        }
        if($this->id_fk_penerimaan == ""){
            return false;
        }
        if($this->id_fk_brg_pembelian == ""){
            return false;
        }
        if($this->id_fk_satuan == ""){
            return false;
        }
        if($this->brg_penerimaan_create_date == ""){
            return false;
        }
        if($this->brg_penerimaan_last_modified == ""){
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
        if($this->id_pk_brg_penerimaan == ""){
            return false;
        }
        if($this->brg_penerimaan_qty == ""){
            return false;
        }
        if($this->brg_penerimaan_note == ""){
            return false;
        }
        if($this->id_fk_satuan == ""){
            return false;
        }
        if($this->brg_penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_penerimaan == ""){
            return false;
        }
        if($this->brg_penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_penerimaan_qty,$brg_penerimaan_note,$id_fk_penerimaan,$id_fk_brg_pembelian,$id_fk_satuan){
        if(!$this->set_brg_penerimaan_qty($brg_penerimaan_qty)){
            return false;
        }
        if(!$this->set_brg_penerimaan_note($brg_penerimaan_note)){
            return false;
        }
        if(!$this->set_id_fk_penerimaan($id_fk_penerimaan)){
            return false;
        }
        if(!$this->set_id_fk_brg_pembelian($id_fk_brg_pembelian)){
            return false;
        }
        if(!$this->set_id_fk_satuan($id_fk_satuan)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_penerimaan,$brg_penerimaan_qty,$brg_penerimaan_note,$id_fk_satuan){
        if(!$this->set_id_pk_brg_penerimaan($id_pk_brg_penerimaan)){
            return false;
        }
        if(!$this->set_brg_penerimaan_qty($brg_penerimaan_qty)){
            return false;
        }
        if(!$this->set_brg_penerimaan_note($brg_penerimaan_note)){
            return false;
        }
        if(!$this->set_id_fk_satuan($id_fk_satuan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_penerimaan){
        if(!$this->set_id_pk_brg_penerimaan($id_pk_brg_penerimaan)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_penerimaan($id_pk_brg_penerimaan){
        $this->id_pk_brg_penerimaan = $id_pk_brg_penerimaan;
        return true;
    }
    public function set_brg_penerimaan_qty($brg_penerimaan_qty){
        $this->brg_penerimaan_qty = $brg_penerimaan_qty;
        return true;
    }
    public function set_brg_penerimaan_note($brg_penerimaan_note){
        $this->brg_penerimaan_note = $brg_penerimaan_note;
        return true;
    }
    public function set_id_fk_penerimaan($id_fk_penerimaan){
        $this->id_fk_penerimaan = $id_fk_penerimaan;
        return true;
    }
    public function set_id_fk_brg_pembelian($id_fk_brg_pembelian){
        $this->id_fk_brg_pembelian = $id_fk_brg_pembelian;
        return true;
    }
    public function set_id_fk_satuan($id_fk_satuan){
        $this->id_fk_satuan = $id_fk_satuan;
        return true;
    }
    public function get_id_pk_brg_penerimaan(){
        return $this->id_pk_brg_penerimaan;
    }
    public function get_brg_penerimaan_qty(){
        return $this->brg_penerimaan_qty;
    }
    public function get_brg_penerimaan_note(){
        return $this->brg_penerimaan_note;
    }
    public function get_id_fk_penerimaan(){
        return $this->id_fk_penerimaan;
    }
    public function get_id_fk_brg_pembelian(){
        return $this->id_fk_brg_pembelian;
    }
    public function get_id_fk_satuan(){
        return $this->id_fk_satuan;
    }
}