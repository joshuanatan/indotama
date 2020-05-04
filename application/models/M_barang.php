<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_barang extends CI_Model{
    private $tbl_name = "MSTR_BARANG";
    private $columns = array();
    private $id_pk_brg;
    private $brg_kode;
    private $brg_nama;
    private $brg_stok;
    private $brg_ket;
    private $brg_minimal;
    private $brg_status;
    private $brg_harga;
    private $brg_create_date;
    private $brg_last_modified;
    private $id_create_data;
    private $id_last_modified;
    private $id_fk_brg_jenis;
    private $id_fk_brg_merk;

    public function __construct(){
        parent::__construct();
        $this->brg_create_date = date("Y-m-d H:i:s");
        $this->brg_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS MSTR_BARANG;
        CREATE TABLE MSTR_BARANG(
            ID_PK_BRG INT PRIMARY KEY AUTO_INCREMENT,
            BRG_KODE VARCHAR(50),
            BRG_NAMA VARCHAR(100),
            BRG_STOK DOUBLE,
            BRG_KET VARCHAR(200),
            BRG_MINIMAL DOUBLE,
            BRG_STATUS VARCHAR(15),
            BRG_HARGA INT,
            BRG_CREATE_DATE DATETIME,
            BRG_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_FK_BRG_JENIS INT,
            ID_FK_BRG_MERK INT
        );
        DROP TABLE IF EXISTS MSTR_BARANG_LOG;
        CREATE TABLE MSTR_BARANG_LOG(
            ID_PK_BRG_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(20),
            ID_PK_BRG INT,
            BRG_KODE VARCHAR(50),
            BRG_NAMA VARCHAR(100),
            BRG_STOK DOUBLE,
            BRG_KET VARCHAR(200),
            BRG_MINIMAL DOUBLE,
            BRG_STATUS VARCHAR(15),
            BRG_HARGA INT,
            BRG_CREATE_DATE DATETIME,
            BRG_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_FK_BRG_JENIS INT,
            ID_FK_BRG_MERK INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BARANG;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BARANG
        AFTER INSERT ON MSTR_BARANG
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_BARANG_LOG(EXECUTED_FUNCTION,
            ID_PK_BRG,BRG_KODE,BRG_NAMA,BRG_STOK,BRG_KET,BRG_MINIMAL,BRG_STATUS,BRG_HARGA,BRG_CREATE_DATE,BRG_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_FK_BRG_JENIS,ID_FK_BRG_MERK,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG,NEW.BRG_KODE,NEW.BRG_NAMA,NEW.BRG_STOK,NEW.BRG_KET,NEW.BRG_MINIMAL,NEW.BRG_STATUS,NEW.BRG_HARGA,NEW.BRG_CREATE_DATE,NEW.BRG_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,NEW.ID_FK_BRG_JENIS,NEW.ID_FK_BRG_MERK,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BARANG;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BARANG
        AFTER UPDATE ON MSTR_BARANG
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_BARANG_LOG(EXECUTED_FUNCTION,
            ID_PK_BRG,BRG_KODE,BRG_NAMA,BRG_STOK,BRG_KET,BRG_MINIMAL,BRG_STATUS,BRG_HARGA,BRG_CREATE_DATE,BRG_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_FK_BRG_JENIS,ID_FK_BRG_MERK,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG,NEW.BRG_KODE,NEW.BRG_NAMA,NEW.BRG_STOK,NEW.BRG_KET,NEW.BRG_MINIMAL,NEW.BRG_STATUS,NEW.BRG_HARGA,NEW.BRG_CREATE_DATE,NEW.BRG_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,NEW.ID_FK_BRG_JENIS,NEW.ID_FK_BRG_MERK,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_kode" => $this->brg_kode,
                "brg_nama" => $this->brg_nama,
                "brg_stok" => $this->brg_stok,
                "brg_ket" => $this->brg_ket,
                "brg_minimal" => $this->brg_minimal,
                "brg_status" => $this->brg_status,
                "brg_harga" => $this->brg_harga,
                "id_fk_brg_jenis" => $this->id_fk_brg_jenis,
                "id_fk_brg_merk" => $this->id_fk_brg_merk,
                "brg_create_date" => $this->brg_create_date,
                "brg_last_modified" => $this->brg_last_modified,
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
                "id_pk_brg !=" => $this->id_pk_brg,
                "brg_kode" => $this->brg_kode,
            );
            if(isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_brg" => $this->id_pk_brg
                );
                $data = array(
                    "brg_kode" => $this->brg_kode,
                    "brg_nama" => $this->brg_nama,
                    "brg_stok" => $this->brg_stok,
                    "brg_ket" => $this->brg_ket,
                    "brg_minimal" => $this->brg_minimal,
                    "brg_harga" => $this->brg_harga,
                    "id_fk_brg_jenis" => $this->id_fk_brg_jenis,
                    "id_fk_brg_merk" => $this->id_fk_brg_merk,
                    "brg_last_modified" => $this->brg_last_modified,
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
                "id_pk_brg" => $this->id_pk_brg
            );
            $data = array(
                "brg_status" => "NONAKTIF",
                "brg_last_modified" => $this->brg_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
    }
    public function check_insert(){
        if($this->brg_kode == ""){
            return false;
        }
        if($this->brg_nama == ""){
            return false;
        }
        if($this->brg_stok == ""){
            return false;
        }
        if($this->brg_ket == ""){
            return false;
        }
        if($this->brg_minimal == ""){
            return false;
        }
        if($this->brg_harga == ""){
            return false;
        }
        if($this->id_fk_brg_jenis == ""){
            return false;
        }
        if($this->brg_status == ""){
            return false;
        }
        if($this->id_fk_brg_merk == ""){
            return false;
        }
        if($this->brg_create_date == ""){
            return false;
        }
        if($this->brg_last_modified == ""){
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
        if($this->id_pk_brg == ""){
            return false;
        }
        if($this->brg_kode == ""){
            return false;
        }
        if($this->brg_nama == ""){
            return false;
        }
        if($this->brg_stok == ""){
            return false;
        }
        if($this->brg_ket == ""){
            return false;
        }
        if($this->brg_minimal == ""){
            return false;
        }
        if($this->brg_harga == ""){
            return false;
        }
        if($this->id_fk_brg_jenis == ""){
            return false;
        }
        if($this->id_fk_brg_merk == ""){
            return false;
        }
        if($this->brg_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg == ""){
            return false;
        }
        if($this->brg_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_kode,$brg_nama,$brg_stok,$brg_ket,$brg_minimal,$brg_status,$brg_harga,$id_fk_brg_jenis,$id_fk_brg_merk){
        if(!$this->set_brg_kode($brg_kode)){
            return false;
        }
        if(!$this->set_brg_nama($brg_nama)){
            return false;
        }
        if(!$this->set_brg_stok($brg_stok)){
            return false;
        }
        if(!$this->set_brg_ket($brg_ket)){
            return false;
        }
        if(!$this->set_brg_minimal($brg_minimal)){
            return false;
        }
        if(!$this->set_brg_status($brg_status)){
            return false;
        }
        if(!$this->set_brg_harga($brg_harga)){
            return false;
        }
        if(!$this->set_id_fk_brg_jenis($id_fk_brg_jenis)){
            return false;
        }
        if(!$this->set_id_fk_brg_merk($id_fk_brg_merk)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg,$brg_kode,$brg_nama,$brg_stok,$brg_ket,$brg_minimal,$brg_harga,$id_fk_brg_jenis,$id_fk_brg_merk){
        if(!$this->set_id_pk_brg($id_pk_brg)){
            return false;
        }
        if(!$this->set_brg_kode($brg_kode)){
            return false;
        }
        if(!$this->set_brg_nama($brg_nama)){
            return false;
        }
        if(!$this->set_brg_stok($brg_stok)){
            return false;
        }
        if(!$this->set_brg_ket($brg_ket)){
            return false;
        }
        if(!$this->set_brg_minimal($brg_minimal)){
            return false;
        }
        if(!$this->set_brg_harga($brg_harga)){
            return false;
        }
        if(!$this->set_id_fk_brg_jenis($id_fk_brg_jenis)){
            return false;
        }
        if(!$this->set_id_fk_brg_merk($id_fk_brg_merk)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg){
        if(!$this->set_id_pk_brg($id_pk_brg)){
            return false;
        }
        return true;
    }
    public function get_id_pk_brg(){
        return $this->id_pk_brg;
    }
    public function get_brg_kode(){
        return $this->brg_kode;
    }
    public function get_brg_nama(){
        return $this->brg_nama;
    }
    public function get_brg_stok(){
        return $this->brg_stok;
    }
    public function get_brg_ket(){
        return $this->brg_ket;
    }
    public function get_brg_minimal(){
        return $this->brg_minimal;
    }
    public function get_brg_status(){
        return $this->brg_status;
    }
    public function get_brg_harga(){
        return $this->brg_harga;
    }
    public function set_id_pk_brg($id_pk_brg){
        if($id_pk_brg != ""){
            $this->id_pk_brg = $id_pk_brg;
            return true;
        }
        return false;
    }
    public function set_brg_kode($brg_kode){
        if($brg_kode != ""){
            $this->brg_kode = $brg_kode;
            return true;
        }
        return false;
    }
    public function set_brg_nama($brg_nama){
        if($brg_nama != ""){
            $this->brg_nama = $brg_nama;
            return true;
        }
        return false;
    }
    public function set_brg_stok($brg_stok){
        if($brg_stok != ""){
            $this->brg_stok = $brg_stok;
            return true;
        }
        return false;
    }
    public function set_brg_ket($brg_ket){
        if($brg_ket != ""){
            $this->brg_ket = $brg_ket;
            return true;
        }
        return false;
    }
    public function set_brg_minimal($brg_minimal){
        if($brg_minimal != ""){
            $this->brg_minimal = $brg_minimal;
            return true;
        }
        return false;
    }
    public function set_brg_status($brg_status){
        if($brg_status != ""){
            $this->brg_status = $brg_status;
            return true;
        }
        return false;
    }
    public function set_brg_harga($brg_harga){
        if($brg_harga != ""){
            $this->brg_harga = $brg_harga;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg_jenis($id_fk_brg_jenis){
        if($id_fk_brg_jenis != ""){
            $this->id_fk_brg_jenis = $id_fk_brg_jenis;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg_merk($id_fk_brg_merk){
        if($id_fk_brg_merk != ""){
            $this->id_fk_brg_merk = $id_fk_brg_merk;
            return true;
        }
        return false;
    }
}