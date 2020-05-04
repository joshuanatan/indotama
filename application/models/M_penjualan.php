<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_penjualan extends CI_Model{
    private $tbl_name = "MSTR_PENJUALAN";
    private $columns = array();
    private $id_pk_penjualan;
    private $penj_pk_nomor;
    private $penj_tgl;
    private $penj_tgl_jatuhtempo;
    private $penj_status;
    private $penj_totalall;
    private $penj_ppn;
    private $penj_progress;
    private $id_fk_customer;
    private $id_fk_toko;
    private $id_fk_sj;
    private $penj_create_date;
    private $penj_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->penj_create_date = date("Y-m-d H:i:s");
        $this->penj_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS MSTR_PENJUALAN;
        CREATE TABLE MSTR_PENJUALAN(
            ID_PK_PENJUALAN INT PRIMARY KEY AUTO_INCREMENT,
            PENJ_PK_NOMOR VARCHAR(100),
            PENJ_TGL DATETIME,
            PENJ_TGL_JATUHTEMPO DATETIME,
            PENJ_STATUS VARCHAR(15),
            PENJ_TOTALALL INT,
            PENJ_PPN INT,
            PENJ_PROGRESS VARCHAR(120),
            ID_FK_CUSTOMER INT,
            ID_FK_TOKO INT,
            ID_FK_SJ INT,
            PENJ_CREATE_DATE DATETIME,
            PENJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_PENJUALAN_LOG;
        CREATE TABLE MSTR_PENJUALAN_LOG(
            ID_PK_PENJUALAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_PENJUALAN INT,
            PENJ_PK_NOMOR VARCHAR(100),
            PENJ_TGL DATETIME,
            PENJ_TGL_JATUHTEMPO DATETIME,
            PENJ_STATUS VARCHAR(15),
            PENJ_TOTALALL INT,
            PENJ_PPN INT,
            PENJ_PROGRESS VARCHAR(120),
            ID_FK_CUSTOMER INT,
            ID_FK_TOKO INT,
            ID_FK_SJ INT,
            PENJ_CREATE_DATE DATETIME,
            PENJ_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_PENJUALAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_PENJUALAN
        AFTER INSERT ON MSTR_PENJUALAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.PENJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN,PENJ_PK_NOMOR,PENJ_TGL,PENJ_TGL_JATUHTEMPO,PENJ_STATUS,PENJ_TOTALALL,PENJ_PPN,PENJ_PROGRESS,ID_FK_CUSTOMER,ID_FK_TOKO,ID_FK_SJ,PENJ_CREATE_DATE,PENJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_PENJUALAN,NEW.PENJ_PK_NOMOR,NEW.PENJ_TGL,NEW.PENJ_TGL_JATUHTEMPO,NEW.PENJ_STATUS,NEW.PENJ_TOTALALL,NEW.PENJ_PPN,NEW.PENJ_PROGRESS,NEW.ID_FK_CUSTOMER,NEW.ID_FK_TOKO,NEW.ID_FK_SJ,NEW.PENJ_CREATE_DATE,NEW.PENJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_PENJUALAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_PENJUALAN
        AFTER UPDATE ON MSTR_PENJUALAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENJ_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.PENJ_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN,PENJ_PK_NOMOR,PENJ_TGL,PENJ_TGL_JATUHTEMPO,PENJ_STATUS,PENJ_TOTALALL,PENJ_PPN,PENJ_PROGRESS,ID_FK_CUSTOMER,ID_FK_TOKO,ID_FK_SJ,PENJ_CREATE_DATE,PENJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_PENJUALAN,NEW.PENJ_PK_NOMOR,NEW.PENJ_TGL,NEW.PENJ_TGL_JATUHTEMPO,NEW.PENJ_STATUS,NEW.PENJ_TOTALALL,NEW.PENJ_PPN,NEW.PENJ_PROGRESS,NEW.ID_FK_CUSTOMER,NEW.ID_FK_TOKO,NEW.ID_FK_SJ,NEW.PENJ_CREATE_DATE,NEW.PENJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penj_pk_nomor" => $this->penj_pk_nomor,
                "penj_tgl" => $this->penj_tgl,
                "penj_tgl_jatuhtempo" => $this->penj_tgl_jatuhtempo,
                "penj_status" => $this->penj_status,
                "penj_totalall" => $this->penj_totalall,
                "penj_ppn" => $this->penj_ppn,
                "penj_progress" => $this->penj_progress,
                "id_fk_customer" => $this->id_fk_customer,
                "id_fk_toko" => $this->id_fk_toko,
                "id_fk_sj" => $this->id_fk_sj,
                "penj_create_date" => $this->penj_create_date,
                "penj_last_modified" => $this->penj_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
            );
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_penjualan" => $this->id_pk_penjualan
            );
            $data = array(
                "penj_pk_nomor" => $this->penj_pk_nomor,
                "penj_tgl" => $this->penj_tgl,
                "penj_tgl_jatuhtempo" => $this->penj_tgl_jatuhtempo,
                "penj_totalall" => $this->penj_totalall,
                "penj_ppn" => $this->penj_ppn,
                "penj_progress" => $this->penj_progress,
                "id_fk_customer" => $this->id_fk_customer,
                "id_fk_toko" => $this->id_fk_toko,
                "id_fk_sj" => $this->id_fk_sj,
                "penj_last_modified" => $this->penj_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_penjualan" => $this->id_pk_penjualan
            );
            $data = array(
                "penj_status" => "NONAKTIF",
                "penj_last_modified" => $this->penj_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->penj_pk_nomor == ""){
            return false;
        }
        if($this->penj_tgl == ""){
            return false;
        }
        if($this->penj_tgl_jatuhtempo == ""){
            return false;
        }
        if($this->penj_status == ""){
            return false;
        }
        if($this->penj_totalall == ""){
            return false;
        }
        if($this->penj_ppn == ""){
            return false;
        }
        if($this->penj_progress == ""){
            return false;
        }
        if($this->id_fk_customer == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->id_fk_sj == ""){
            return false;
        }
        if($this->penj_create_date == ""){
            return false;
        }
        if($this->penj_last_modified == ""){
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
        if($this->id_pk_penjualan == ""){
            return false;
        }
        if($this->penj_pk_nomor == ""){
            return false;
        }
        if($this->penj_tgl == ""){
            return false;
        }
        if($this->penj_tgl_jatuhtempo == ""){
            return false;
        }
        if($this->penj_totalall == ""){
            return false;
        }
        if($this->penj_ppn == ""){
            return false;
        }
        if($this->penj_progress == ""){
            return false;
        }
        if($this->id_fk_customer == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->id_fk_sj == ""){
            return false;
        }
        if($this->penj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_penjualan == ""){
            return false;
        }
        if($this->penj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($penj_pk_nomor,$penj_tgl,$penj_tgl_jatuhtempo,$penj_status,$penj_totalall,$penj_ppn,$penj_progress,$id_fk_customer,$id_fk_toko,$id_fk_sj){
        if(!$this->set_penj_pk_nomor($penj_pk_nomor)){
            return false;
        }
        if(!$this->set_penj_tgl($penj_tgl)){
            return false;
        }
        if(!$this->set_penj_tgl_jatuhtempo($penj_tgl_jatuhtempo)){
            return false;
        }
        if(!$this->set_penj_status($penj_status)){
            return false;
        }
        if(!$this->set_penj_totalall($penj_totalall)){
            return false;
        }
        if(!$this->set_penj_ppn($penj_ppn)){
            return false;
        }
        if(!$this->set_penj_progress($penj_progress)){
            return false;
        }
        if(!$this->set_id_fk_customer($id_fk_customer)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        if(!$this->set_id_fk_sj($id_fk_sj)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_penjualan,$penj_pk_nomor,$penj_tgl,$penj_tgl_jatuhtempo,$penj_totalall,$penj_ppn,$penj_progress,$id_fk_customer,$id_fk_toko,$id_fk_sj){
        if(!$this->set_id_pk_penjualan($id_pk_penjualan)){
            return false;
        }
        if(!$this->set_penj_pk_nomor($penj_pk_nomor)){
            return false;
        }
        if(!$this->set_penj_tgl($penj_tgl)){
            return false;
        }
        if(!$this->set_penj_tgl_jatuhtempo($penj_tgl_jatuhtempo)){
            return false;
        }
        if(!$this->set_penj_totalall($penj_totalall)){
            return false;
        }
        if(!$this->set_penj_ppn($penj_ppn)){
            return false;
        }
        if(!$this->set_penj_progress($penj_progress)){
            return false;
        }
        if(!$this->set_id_fk_customer($id_fk_customer)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        if(!$this->set_id_fk_sj($id_fk_sj)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_penjualan){
        if(!$this->set_id_pk_penjualan($id_pk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_id_pk_penjualan($id_pk_penjualan){
        if($id_pk_penjualan != ""){
            $this->id_pk_penjualan = $id_pk_penjualan;
            return true;
        }
        return false;
    }
    public function set_penj_pk_nomor($penj_pk_nomor){
        if($penj_pk_nomor != ""){
            $this->penj_pk_nomor = $penj_pk_nomor;
            return true;
        }
        return false;
    }
    public function set_penj_tgl($penj_tgl){
        if($penj_tgl != ""){
            $this->penj_tgl = $penj_tgl;
            return true;
        }
        return false;
    }
    public function set_penj_tgl_jatuhtempo($penj_tgl_jatuhtempo){
        if($penj_tgl_jatuhtempo != ""){
            $this->penj_tgl_jatuhtempo = $penj_tgl_jatuhtempo;
            return true;
        }
        return false;
    }
    public function set_penj_status($penj_status){
        if($penj_status != ""){
            $this->penj_status = $penj_status;
            return true;
        }
        return false;
    }
    public function set_penj_totalall($penj_totalall){
        if($penj_totalall != ""){
            $this->penj_totalall = $penj_totalall;
            return true;
        }
        return false;
    }
    public function set_penj_ppn($penj_ppn){
        if($penj_ppn != ""){
            $this->penj_ppn = $penj_ppn;
            return true;
        }
        return false;
    }
    public function set_penj_progress($penj_progress){
        if($penj_progress != ""){
            $this->penj_progress = $penj_progress;
            return true;
        }
        return false;
    }
    public function set_id_fk_customer($id_fk_customer){
        if($id_fk_customer != ""){
            $this->id_fk_customer = $id_fk_customer;
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
    public function set_id_fk_sj($id_fk_sj){
        if($id_fk_sj != ""){
            $this->id_fk_sj = $id_fk_sj;
            return true;
        }
        return false;
    }
    public function get_id_pk_penjualan(){
        return $this->id_pk_penjualan;
    }
    public function get_penj_pk_nomor(){
        return $this->penj_pk_nomor;
    }
    public function get_penj_tgl(){
        return $this->penj_tgl;
    }
    public function get_penj_tgl_jatuhtempo(){
        return $this->penj_tgl_jatuhtempo;
    }
    public function get_penj_status(){
        return $this->penj_status;
    }
    public function get_penj_totalall(){
        return $this->penj_totalall;
    }
    public function get_penj_ppn(){
        return $this->penj_ppn;
    }
    public function get_penj_progress(){
        return $this->penj_progress;
    }
    public function get_id_fk_customer(){
        return $this->id_fk_customer;
    }
    public function get_id_fk_toko(){
        return $this->id_fk_toko;
    }
    public function get_id_fk_sj(){
        return $this->id_fk_sj;
    }
}