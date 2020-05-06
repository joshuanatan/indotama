<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_cabang extends CI_Model{
    private $tbl_name = "MSTR_CABANG";
    private $columns = array();
    private $id_pk_cabang;
    private $cabang_daerah;
    private $cabang_notelp;
    private $cabang_alamat;
    private $cabang_status;
    private $cabang_create_date;
    private $cabang_last_modified;
    private $id_create_data;
    private $id_last_modified; 
    private $id_fk_toko; 

    public function __construct(){
        parent::__construct();
        $this->set_column("cabang_daerah","Daerah",true);
        $this->set_column("cabang_notelp","No Telp",false);
        $this->set_column("cabang_alamat","Alamat",false);
        $this->set_column("cabang_status","Status",false);
        $this->set_column("cabang_last_modified","Last Modified",false);

        $this->cabang_create_date = date("Y-m-d H:i:s");
        $this->cabang_last_modified = date("Y-m-d H:i:s");
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
        $sql = "DROP TABLE IF EXISTS MSTR_CABANG;
        CREATE TABLE MSTR_CABANG(
            ID_PK_CABANG INT PRIMARY KEY AUTO_INCREMENT,
            CABANG_DAERAH VARCHAR(50),
            CABANG_NOTELP VARCHAR(30),
            CABANG_ALAMAT VARCHAR(100),
            CABANG_STATUS VARCHAR(15),
            CABANG_CREATE_DATE DATETIME,
            CABANG_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_FK_TOKO INT
        );
        DROP TABLE IF EXISTS MSTR_CABANG_LOG;
        CREATE TABLE MSTR_CABANG_LOG(
            ID_PK_CABANG_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_CABANG INT,
            CABANG_DAERAH VARCHAR(50),
            CABANG_NOTELP VARCHAR(30),
            CABANG_ALAMAT VARCHAR(100),
            CABANG_STATUS VARCHAR(15),
            CABANG_CREATE_DATE DATETIME,
            CABANG_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_FK_TOKO INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_CABANG;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_CABANG
        AFTER INSERT ON MSTR_CABANG
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.CABANG_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.CABANG_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_CABANG_LOG(EXECUTED_FUNCTION,ID_PK_CABANG,CABANG_DAERAH,CABANG_NOTELP,CABANG_ALAMAT,CABANG_STATUS,CABANG_CREATE_DATE,CABANG_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_FK_TOKO,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_CABANG,NEW.CABANG_DAERAH,NEW.CABANG_NOTELP,NEW.CABANG_ALAMAT,NEW.CABANG_STATUS,NEW.CABANG_CREATE_DATE,NEW.CABANG_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,NEW.ID_FK_TOKO,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_CABANG;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_CABANG
        AFTER UPDATE ON MSTR_CABANG
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.CABANG_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.CABANG_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_CABANG_LOG(EXECUTED_FUNCTION,ID_PK_CABANG,CABANG_DAERAH,CABANG_NOTELP,CABANG_ALAMAT,CABANG_STATUS,CABANG_CREATE_DATE,CABANG_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_FK_TOKO,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_CABANG,NEW.CABANG_DAERAH,NEW.CABANG_NOTELP,NEW.CABANG_ALAMAT,NEW.CABANG_STATUS,NEW.CABANG_CREATE_DATE,NEW.CABANG_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,NEW.ID_FK_TOKO,@ID_LOG_ALL);
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
                id_pk_cabang LIKE '%".$search_key."%' OR 
                cabang_daerah LIKE '%".$search_key."%' OR 
                cabang_notelp LIKE '%".$search_key."%' OR 
                cabang_alamat LIKE '%".$search_key."%' OR 
                cabang_status LIKE '%".$search_key."%' OR 
                cabang_create_date LIKE '%".$search_key."%' OR 
                cabang_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_cabang,cabang_daerah,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified
        FROM ".$this->tbl_name." 
        WHERE cabang_status = ? AND id_fk_toko = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF",$this->id_fk_toko
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_cabang
        FROM ".$this->tbl_name." 
        WHERE cabang_status = ? AND id_fk_toko = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "cabang_daerah" => $this->cabang_daerah,
                "cabang_notelp" => $this->cabang_notelp,
                "cabang_alamat" => $this->cabang_alamat,
                "cabang_status" => $this->cabang_status,
                "cabang_create_date" => $this->cabang_create_date,
                "cabang_last_modified" => $this->cabang_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
                "id_fk_toko" => $this->id_fk_toko
            );
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_cabang" => $this->id_pk_cabang,
            );
            $data = array(
                "cabang_daerah" => $this->cabang_daerah,
                "cabang_notelp" => $this->cabang_notelp,
                "cabang_alamat" => $this->cabang_alamat,
                "cabang_last_modified" => $this->cabang_last_modified,
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
                "id_pk_cabang" => $this->id_pk_cabang,
            );
            $data = array(
                "cabang_status" => "NONAKTIF",
                "cabang_last_modified" => $this->cabang_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->cabang_daerah == ""){
            return false;
        }
        if($this->cabang_notelp == ""){
            return false;
        }
        if($this->cabang_status == ""){
            return false;
        }
        if($this->cabang_alamat == ""){
            return false;
        }
        if($this->cabang_create_date == ""){
            return false;
        }
        if($this->cabang_last_modified == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        return true;
    }
    public function check_update(){
        if($this->id_pk_cabang == ""){
            return false;
        }
        if($this->cabang_daerah == ""){
            return false;
        }
        if($this->cabang_notelp == ""){
            return false;
        }
        if($this->cabang_alamat == ""){
            return false;
        }
        if($this->cabang_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        
        if($this->id_pk_cabang == ""){
            return false;
        }
        if($this->cabang_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($cabang_daerah,$cabang_notelp,$cabang_status,$cabang_alamat,$id_fk_toko){
        if(!$this->set_cabang_daerah($cabang_daerah)){
            return false;
        }
        if(!$this->set_cabang_notelp($cabang_notelp)){
            return false;
        }
        if(!$this->set_cabang_status($cabang_status)){
            return false;
        }
        if(!$this->set_cabang_alamat($cabang_alamat)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_cabang,$cabang_daerah,$cabang_notelp,$cabang_alamat){
        if(!$this->set_id_pk_cabang($id_pk_cabang)){
            return false;
        }
        if(!$this->set_cabang_daerah($cabang_daerah)){
            return false;
        }
        if(!$this->set_cabang_notelp($cabang_notelp)){
            return false;
        }
        if(!$this->set_cabang_alamat($cabang_alamat)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_cabang){
        if(!$this->set_id_pk_cabang($id_pk_cabang)){
            return false;
        }
        return true;
    }
    public function get_id_pk_cabang(){
        return $this->id_pk_cabang;
    }
    public function get_cabang_daerah(){
        return $this->cabang_daerah;
    }
    public function get_cabang_notelp(){
        return $this->cabang_notelp;
    }
    public function get_cabang_status(){
        return $this->cabang_status;
    }
    public function get_cabang_alamat(){
        return $this->cabang_alamat;
    }
    public function get_id_fk_toko(){
        return $this->id_fk_toko;
    }
    public function set_id_pk_cabang($id_pk_cabang){
        if($id_pk_cabang != ""){
            $this->id_pk_cabang = $id_pk_cabang;
            return true;
        }
        return false;
    }
    public function set_cabang_daerah($cabang_daerah){
        if($cabang_daerah != ""){
            $this->cabang_daerah = $cabang_daerah;
            return true;
        }
        return false;
    }
    public function set_cabang_notelp($cabang_notelp){
        if($cabang_notelp != ""){
            $this->cabang_notelp = $cabang_notelp;
            return true;
        }
        return false;
    }
    public function set_cabang_status($cabang_status){
        if($cabang_status != ""){
            $this->cabang_status = $cabang_status;
            return true;
        }
        return false;
    }
    public function set_cabang_alamat($cabang_alamat){
        if($cabang_alamat != ""){
            $this->cabang_alamat = $cabang_alamat;
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
}