<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_penjualan extends CI_Model{
    private $tbl_name = "MSTR_PENJUALAN";
    private $columns = array();
    private $id_pk_penjualan;
    private $penj_nomor;
    private $penj_tgl;
    private $penj_dateline_tgl;/*SUPAYA TAU PAS PENGIRIMAN MANA YANG URGENT*/
    private $penj_status;
    private $penj_jenis; /*ONLINE/OFFLINE*/
    private $penj_tipe_pembayaran; /*FULL/DP/TRIAL/DKK*/
    private $id_fk_customer;
    private $id_fk_cabang;
    private $penj_create_date;
    private $penj_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("penj_nomor","Nomor Penjualan",true);
        $this->set_column("penj_tgl","Tanggal Penjualan",false);
        $this->set_column("penj_dateline_tgl","Dateline",false);
        $this->set_column("penj_jenis","Jenis Penjualan",false);
        $this->set_column("tipe_pembayaran","Tipe Pembayaran",false);
        $this->set_column("cust_name","Customer",false);
        $this->set_column("penj_status","Status",false);
        $this->set_column("penj_last_modified","Last Modified",false);
        $this->penj_create_date = date("Y-m-d H:i:s");
        $this->penj_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS MSTR_PENJUALAN;
        CREATE TABLE MSTR_PENJUALAN(
            ID_PK_PENJUALAN INT PRIMARY KEY AUTO_INCREMENT,
            PENJ_NOMOR VARCHAR(30),
            PENJ_TGL DATETIME,
            PENJ_DATELINE_TGL DATETIME,
            PENJ_JENIS VARCHAR(50),
            PENJ_TIPE_PEMBAYARAN VARCHAR(50),
            PENJ_STATUS VARCHAR(15),
            ID_FK_CUSTOMER INT,
            ID_FK_CABANG INT,
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
            PENJ_NOMOR VARCHAR(30),
            PENJ_TGL DATETIME,
            PENJ_DATELINE_TGL DATETIME,
            PENJ_JENIS VARCHAR(50),
            PENJ_TIPE_PEMBAYARAN VARCHAR(50),
            PENJ_STATUS VARCHAR(15),
            ID_FK_CUSTOMER INT,
            ID_FK_CABANG INT,
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
            
            INSERT INTO MSTR_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN,PENJ_NOMOR,PENJ_TGL,PENJ_DATELINE_TGL,PENJ_JENIS,PENJ_TIPE_PEMBAYARAN,PENJ_STATUS,ID_FK_CUSTOMER,ID_FK_CABANG,PENJ_CREATE_DATE,PENJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_PENJUALAN,NEW.PENJ_NOMOR,NEW.PENJ_TGL,NEW.PENJ_DATELINE_TGL,NEW.PENJ_JENIS,NEW.PENJ_TIPE_PEMBAYARAN,NEW.PENJ_STATUS,NEW.ID_FK_CUSTOMER,NEW.ID_FK_CABANG,NEW.PENJ_CREATE_DATE,NEW.PENJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
            
            INSERT INTO MSTR_PENJUALAN_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN,PENJ_NOMOR,PENJ_TGL,PENJ_DATELINE_TGL,PENJ_JENIS,PENJ_TIPE_PEMBAYARAN,PENJ_STATUS,ID_FK_CUSTOMER,ID_FK_CABANG,PENJ_CREATE_DATE,PENJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_PENJUALAN,NEW.PENJ_NOMOR,NEW.PENJ_TGL,NEW.PENJ_DATELINE_TGL,NEW.PENJ_JENIS,NEW.PENJ_TIPE_PEMBAYARAN,NEW.PENJ_STATUS,NEW.ID_FK_CUSTOMER,NEW.ID_FK_CABANG,NEW.PENJ_CREATE_DATE,NEW.PENJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                id_pk_penjualan LIKE '%".$search_key."%' OR
                penj_nomor LIKE '%".$search_key."%' OR
                penj_tgl LIKE '%".$search_key."%' OR
                penj_dateline_tgl LIKE '%".$search_key."%' OR
                penj_status LIKE '%".$search_key."%' OR
                penj_jenis LIKE '%".$search_key."%' OR
                penj_tipe_pembayaran LIKE '%".$search_key."%' OR
                penj_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_CUSTOMER ON MSTR_CUSTOMER.ID_PK_CUST = ".$this->tbl_name.".ID_FK_CUSTOMER
        WHERE PENJ_STATUS = ? AND CUST_STATUS = ? AND ID_FK_CABANG = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF","AKTIF",$this->id_fk_cabang
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_penjualan
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_CUSTOMER ON MSTR_CUSTOMER.ID_PK_CUST = ".$this->tbl_name.".ID_FK_CUSTOMER
        WHERE PENJ_STATUS = ? AND CUST_STATUS = ? AND ID_FK_CABANG = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penj_nomor" => $this->penj_nomor,
                "penj_tgl" => $this->penj_tgl,
                "penj_status" => $this->penj_status,
                "penj_dateline_tgl" => $this->penj_dateline_tgl,
                "penj_jenis" => $this->penj_jenis,
                "penj_tipe_pembayaran" => $this->penj_tipe_pembayaran,
                "id_fk_customer" => $this->id_fk_customer,
                "id_fk_cabang" => $this->id_fk_cabang,
                "penj_create_date" => $this->penj_create_date,
                "penj_last_modified" => $this->penj_last_modified,
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
                "id_pk_penjualan" => $this->id_pk_penjualan
            );
            $data = array(
                "penj_nomor" => $this->penj_nomor,
                "penj_jenis" => $this->penj_jenis,
                "penj_dateline_tgl" => $this->penj_dateline_tgl,
                "penj_jenis" => $this->penj_jenis,
                "penj_tipe_pembayaran" => $this->penj_tipe_pembayaran,
                "id_fk_customer" => $this->id_fk_customer,
                "penj_last_modified" => $this->penj_last_modified,
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
                "id_pk_penjualan" => $this->id_pk_penjualan
            );
            $data = array(
                "penj_status" => "NONAKTIF",
                "penj_last_modified" => $this->penj_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->penj_nomor == ""){
            return false;
        }
        if($this->penj_tgl == ""){
            return false;
        }
        if($this->penj_dateline_tgl == ""){
            return false;
        }
        if($this->penj_jenis == ""){
            return false;
        }
        if($this->penj_tipe_pembayaran == ""){
            return false;
        }
        if($this->penj_status == ""){
            return false;
        }
        if($this->id_fk_customer == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
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
        if($this->penj_nomor == ""){
            return false;
        }
        if($this->penj_dateline_tgl == ""){
            return false;
        }
        if($this->penj_jenis == ""){
            return false;
        }
        if($this->penj_tipe_pembayaran == ""){
            return false;
        }
        if($this->penj_tgl == ""){
            return false;
        }
        if($this->id_fk_customer == ""){
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
    public function set_insert($penj_nomor,$penj_tgl,$penj_dateline_tgl,$penj_jenis,$penj_tipe_pembayaran,$id_fk_customer,$id_fk_cabang,$penj_status){
        if(!$this->set_penj_nomor($penj_nomor)){
            return false;
        }
        if(!$this->set_penj_dateline_tgl($penj_dateline_tgl)){
            return false;
        }
        if(!$this->set_penj_jenis($penj_jenis)){
            return false;
        }
        if(!$this->set_penj_tipe_pembayaran($penj_tipe_pembayaran)){
            return false;
        }
        if(!$this->set_penj_tgl($penj_tgl)){
            return false;
        }
        if(!$this->set_penj_status($penj_status)){
            return false;
        }
        if(!$this->set_id_fk_customer($id_fk_customer)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_penjualan,$penj_nomor,$penj_dateline_tgl,$penj_jenis,$penj_tipe_pembayaran,$penj_tgl,$id_fk_customer){
        if(!$this->set_id_pk_penjualan($id_pk_penjualan)){
            return false;
        }
        if(!$this->set_penj_nomor($penj_nomor)){
            return false;
        }
        if(!$this->set_penj_dateline_tgl($penj_dateline_tgl)){
            return false;
        }
        if(!$this->set_penj_jenis($penj_jenis)){
            return false;
        }
        if(!$this->set_penj_tipe_pembayaran($penj_tipe_pembayaran)){
            return false;
        }
        if(!$this->set_penj_tgl($penj_tgl)){
            return false;
        }
        if(!$this->set_id_fk_customer($id_fk_customer)){
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
    public function set_penj_nomor($penj_nomor){
        if($penj_nomor != ""){
            $this->penj_nomor = $penj_nomor;
            return true;
        }
        return false;
    }
    public function set_penj_dateline_tgl($penj_dateline_tgl){
        if($penj_dateline_tgl != ""){
            $this->penj_dateline_tgl = $penj_dateline_tgl;
            return true;
        }
        return false;
    }
    public function set_penj_jenis($penj_jenis){
        if($penj_jenis != ""){
            $this->penj_jenis = $penj_jenis;
            return true;
        }
        return false;
    }
    public function set_penj_tipe_pembayaran($penj_tipe_pembayaran){
        if($penj_tipe_pembayaran != ""){
            $this->penj_tipe_pembayaran = $penj_tipe_pembayaran;
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
    public function set_penj_status($penj_status){
        if($penj_status != ""){
            $this->penj_status = $penj_status;
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
    public function set_id_fk_cabang($id_fk_cabang){
        if($id_fk_cabang != ""){
            $this->id_fk_cabang = $id_fk_cabang;
            return true;
        }
        return false;
    }
    public function get_id_pk_penjualan(){
        return $this->id_pk_penjualan;
    }
    public function get_penj_nomor(){
        return $this->penj_nomor;
    }
    public function get_penj_dateline_tgl(){
        return $this->penj_dateline_tgl;
    }
    public function get_penj_jenis(){
        return $this->penj_jenis;
    }
    public function get_penj_tipe_pembayaran(){
        return $this->penj_tipe_pembayaran;
    }
    public function get_penj_tgl(){
        return $this->penj_tgl;
    }
    public function get_penj_status(){
        return $this->penj_status;
    }
    public function get_id_fk_customer(){
        return $this->id_fk_customer;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
}