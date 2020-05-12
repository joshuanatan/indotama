<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_pembelian extends CI_Model{
    private $tbl_name = "MSTR_PEMBELIAN";
    private $columns = array();
    private $id_pk_pembelian;
    private $pem_pk_nomor;
    private $pem_tgl;
    private $pem_status;
    private $id_fk_supp;
    private $pem_totalall;
    private $pem_status_bayar;
    private $pem_create_date;
    private $pem_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->pem_create_date = date("Y-m-d H:i:s");
        $this->pem_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE MSTR_PEMBELIAN;
        CREATE TABLE MSTR_PEMBELIAN(
            ID_PK_PEMBELIAN INT PRIMARY KEY AUTO_INCREMENT,
            PEM_PK_NOMOR VARCHAR(30),
            PEM_TGL DATE,
            PEM_STATUS VARCHAR(15),
            ID_FK_SUPP INT,
            PEM_TOTALALL INT,
            PEM_STATUS_BAYAR VARCHAR(15),
            PEM_CREATE_DATE DATETIME,
            PEM_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE MSTR_PEMBELIAN_LOG;
        CREATE TABLE MSTR_PEMBELIAN_LOG(
            ID_PK_PEMBELIAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_PEMBELIAN INT,
            PEM_PK_NOMOR VARCHAR(30),
            PEM_TGL DATE,
            PEM_STATUS VARCHAR(15),
            ID_FK_SUPP INT,
            PEM_TOTALALL INT,
            PEM_STATUS_BAYAR VARCHAR(15),
            PEM_CREATE_DATE DATETIME,
            PEM_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_PEMBELIAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_PEMBELIAN
        AFTER INSERT ON MSTR_PEMBELIAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PEM_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.PEM_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PEMBELIAN_LOG(EXECUTED_FUNCTION,ID_PK_PEMBELIAN,PEM_PK_NOMOR,PEM_TGL,PEM_STATUS,ID_FK_SUPP,PEM_TOTALALL,PEM_STATUS_BAYAR,PEM_CREATE_DATE,PEM_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_PEMBELIAN,NEW.PEM_PK_NOMOR,NEW.PEM_TGL,NEW.PEM_STATUS,NEW.ID_FK_SUPP,NEW.PEM_TOTALALL,NEW.PEM_STATUS_BAYAR,NEW.PEM_CREATE_DATE,NEW.PEM_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_PEMBELIAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_PEMBELIAN
        AFTER UPDATE ON MSTR_PEMBELIAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PEM_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.PEM_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PEMBELIAN_LOG(EXECUTED_FUNCTION,ID_PK_PEMBELIAN,PEM_PK_NOMOR,PEM_TGL,PEM_STATUS,ID_FK_SUPP,PEM_TOTALALL,PEM_STATUS_BAYAR,PEM_CREATE_DATE,PEM_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_PEMBELIAN,NEW.PEM_PK_NOMOR,NEW.PEM_TGL,NEW.PEM_STATUS,NEW.ID_FK_SUPP,NEW.PEM_TOTALALL,NEW.PEM_STATUS_BAYAR,NEW.PEM_CREATE_DATE,NEW.PEM_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "pem_pk_nomor" => $this->pem_pk_nomor,
                "pem_tgl" => $this->pem_tgl,
                "pem_tgl_bayar" => $this->pem_tgl_bayar,
                "pem_jenis_bayar" => $this->pem_jenis_bayar,
                "pem_status_bayar" => $this->pem_status_bayar,
                "pem_totalall" => $this->pem_totalall,
                "pem_supp_name" => $this->pem_supp_name,
                "pem_jmlh_item" => $this->pem_jmlh_item,
                "pem_status" => $this->pem_status,
                "id_fk_supp" => $this->id_fk_supp,
                "id_fk_toko" => $this->id_fk_toko,
                "pem_create_date" => $this->pem_create_date,
                "pem_last_modified" => $this->pem_last_modified,
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
                "id_pk_pembelian" => $this->id_pk_pembelian,
            );
            $data = array(
                "pem_pk_nomor" => $this->pem_pk_nomor,
                "pem_tgl" => $this->pem_tgl,
                "pem_tgl_bayar" => $this->pem_tgl_bayar,
                "pem_jenis_bayar" => $this->pem_jenis_bayar,
                "pem_status_bayar" => $this->pem_status_bayar,
                "pem_totalall" => $this->pem_totalall,
                "pem_supp_name" => $this->pem_supp_name,
                "pem_jmlh_item" => $this->pem_jmlh_item,
                "id_fk_supp" => $this->id_fk_supp,
                "id_fk_toko" => $this->id_fk_toko,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
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
                "id_pk_pembelian" => $this->id_pk_pembelian,
            );
            $data = array(
                "pem_status" => "NONAKTIF",
                "id_create_data" => $this->id_create_data,
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
        if($this->pem_pk_nomor == ""){
            return false;
        }
        if($this->pem_tgl == ""){
            return false;
        }
        if($this->pem_tgl_bayar == ""){
            return false;
        }
        if($this->pem_jenis_bayar == ""){
            return false;
        }
        if($this->pem_status_bayar == ""){
            return false;
        }
        if($this->pem_totalall == ""){
            return false;
        }
        if($this->pem_supp_name == ""){
            return false;
        }
        if($this->pem_jmlh_item == ""){
            return false;
        }
        if($this->pem_status == ""){
            return false;
        }
        if($this->id_fk_supp == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->pem_create_date == ""){
            return false;
        }
        if($this->pem_last_modified == ""){
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
        if($this->id_pk_pembelian == ""){
            return false;
        }
        if($this->pem_pk_nomor == ""){
            return false;
        }
        if($this->pem_tgl == ""){
            return false;
        }
        if($this->pem_tgl_bayar == ""){
            return false;
        }
        if($this->pem_jenis_bayar == ""){
            return false;
        }
        if($this->pem_status_bayar == ""){
            return false;
        }
        if($this->pem_totalall == ""){
            return false;
        }
        if($this->pem_supp_name == ""){
            return false;
        }
        if($this->pem_jmlh_item == ""){
            return false;
        }
        if($this->id_fk_supp == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_pembelian == ""){
            return false;
        }
        if($this->pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($pem_pk_nomor,$pem_tgl,$pem_tgl_bayar,$pem_jenis_bayar,$pem_status_bayar,$pem_totalall,$pem_supp_name,$pem_jmlh_item,$pem_status,$id_fk_supp,$id_fk_toko){
        if(!$this->set_pem_pk_nomor($pem_pk_nomor)){
            return false;
        }
        if(!$this->set_pem_tgl($pem_tgl)){
            return false;
        }
        if(!$this->set_pem_tgl_bayar($pem_tgl_bayar)){
            return false;
        }
        if(!$this->set_pem_jenis_bayar($pem_jenis_bayar)){
            return false;
        }
        if(!$this->set_pem_status_bayar($pem_status_bayar)){
            return false;
        }
        if(!$this->set_pem_totalall($pem_totalall)){
            return false;
        }
        if(!$this->set_pem_supp_name($pem_supp_name)){
            return false;
        }
        if(!$this->set_pem_jmlh_item($pem_jmlh_item)){
            return false;
        }
        if(!$this->set_pem_status($pem_status)){
            return false;
        }
        if(!$this->set_id_fk_supp($id_fk_supp)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_pembelian,$pem_pk_nomor,$pem_tgl,$pem_tgl_bayar,$pem_jenis_bayar,$pem_status_bayar,$pem_totalall,$pem_supp_name,$pem_jmlh_item,$id_fk_supp,$id_fk_toko){
        if(!$this->set_id_pk_pembelian($id_pk_pembelian)){
            return false;
        }
        if(!$this->set_pem_pk_nomor($pem_pk_nomor)){
            return false;
        }
        if(!$this->set_pem_tgl($pem_tgl)){
            return false;
        }
        if(!$this->set_pem_tgl_bayar($pem_tgl_bayar)){
            return false;
        }
        if(!$this->set_pem_jenis_bayar($pem_jenis_bayar)){
            return false;
        }
        if(!$this->set_pem_status_bayar($pem_status_bayar)){
            return false;
        }
        if(!$this->set_pem_totalall($pem_totalall)){
            return false;
        }
        if(!$this->set_pem_supp_name($pem_supp_name)){
            return false;
        }
        if(!$this->set_pem_jmlh_item($pem_jmlh_item)){
            return false;
        }
        if(!$this->set_id_fk_supp($id_fk_supp)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_pembelian){
        if(!$this->set_id_pk_pembelian($id_pk_pembelian)){
            return false;
        }
        return true;
    }
    public function set_id_pk_pembelian($id_pk_pembelian){
        if($id_pk_pembelian != ""){
            $this->id_pk_pembelian = $id_pk_pembelian;
            return true;
        }
        return false;
    }
    public function set_pem_pk_nomor($pem_pk_nomor){
        if($pem_pk_nomor != ""){
            $this->pem_pk_nomor = $pem_pk_nomor;
            return true;
        }
        return false;
    }
    public function set_pem_tgl($pem_tgl){
        if($pem_tgl != ""){
            $this->pem_tgl = $pem_tgl;
            return true;
        }
        return false;
    }
    public function set_pem_tgl_bayar($pem_tgl_bayar){
        if($pem_tgl_bayar != ""){
            $this->pem_tgl_bayar = $pem_tgl_bayar;
            return true;
        }
        return false;
    }
    public function set_pem_jenis_bayar($pem_jenis_bayar){
        if($pem_jenis_bayar != ""){
            $this->pem_jenis_bayar = $pem_jenis_bayar;
            return true;
        }
        return false;
    }
    public function set_pem_status_bayar($pem_status_bayar){
        if($pem_status_bayar != ""){
            $this->pem_status_bayar = $pem_status_bayar;
            return true;
        }
        return false;
    }
    public function set_pem_totalall($pem_totalall){
        if($pem_totalall != ""){
            $this->pem_totalall = $pem_totalall;
            return true;
        }
        return false;
    }
    public function set_pem_supp_name($pem_supp_name){
        if($pem_supp_name != ""){
            $this->pem_supp_name = $pem_supp_name;
            return true;
        }
        return false;
    }
    public function set_pem_jmlh_item($pem_jmlh_item){
        if($pem_jmlh_item != ""){
            $this->pem_jmlh_item = $pem_jmlh_item;
            return true;
        }
        return false;
    }
    public function set_pem_status($pem_status){
        if($pem_status != ""){
            $this->pem_status = $pem_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_supp($id_fk_supp){
        if($id_fk_supp != ""){
            $this->id_fk_supp = $id_fk_supp;
            return true;
        }
        return false;
    }
    public function set_id_fk_toko($id_fk_toko){
        if($id_fk_toko != ""){
            $this->id_fk_toko = $id_fk_toko;
            return true;
        }
        return false;
    }
    public function get_id_pk_pembelian(){
        return $this->id_pk_pembelian;
    }
    public function get_pem_pk_nomor(){
        return $this->pem_pk_nomor;
    }
    public function get_pem_tgl(){
        return $this->pem_tgl;
    }
    public function get_pem_tgl_bayar(){
        return $this->pem_tgl_bayar;
    }
    public function get_pem_jenis_bayar(){
        return $this->pem_jenis_bayar;
    }
    public function get_pem_status_bayar(){
        return $this->pem_status_bayar;
    }
    public function get_pem_totalall(){
        return $this->pem_totalall;
    }
    public function get_pem_supp_name(){
        return $this->pem_supp_name;
    }
    public function get_pem_jmlh_item(){
        return $this->pem_jmlh_item;
    }
    public function get_pem_status(){
        return $this->pem_status;
    }
    public function get_id_fk_supp(){
        return $this->id_fk_supp;
    }
    public function get_id_fk_toko(){
        return $this->id_fk_toko;
    }
}