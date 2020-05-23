<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_penerimaan extends CI_Model{
    private $tbl_name = "MSTR_PENERIMAAN";
    private $columns = array();
    private $id_pk_penerimaan;
    private $penerimaan_tgl;
    private $penerimaan_status;
    private $id_fk_pembelian;
    private $penerimaan_tempat;
    private $id_fk_warehouse;
    private $id_fk_cabang;
    private $penerimaan_create_date;
    private $penerimaan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("penerimaan_tgl","Tanggal Penerimaan",true);
        $this->set_column("pem_pk_nomor","Nomor Pembelian",false);
        $this->set_column("penerimaan_status","Status",false);
        $this->set_column("penerimaan_last_modified","Last Modified",false);
        $this->penerimaan_create_date = date("Y-m-d H:i:s");
        $this->penerimaan_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS MSTR_PENERIMAAN;
        CREATE TABLE MSTR_PENERIMAAN(
            ID_PK_PENERIMAAN INT PRIMARY KEY AUTO_INCREMENT,
            PENERIMAAN_TGL DATETIME, 
            PENERIMAAN_STATUS VARCHAR(15), 
            ID_FK_PEMBELIAN INT, 
            PENERIMAAN_TEMPAT VARCHAR(30) COMMENT 'WAREHOUSE/CABANG', 
            ID_FK_WAREHOUSE INT, 
            ID_FK_CABANG INT, 
            PENERIMAAN_CREATE_DATE DATETIME, 
            PENERIMAAN_LAST_MODIFIED DATETIME, 
            ID_CREATE_DATA INT, 
            ID_LAST_MODIFIED INT 
        );
        DROP TABLE IF EXISTS MSTR_PENERIMAAN_LOG;
        CREATE TABLE MSTR_PENERIMAAN_LOG(
            ID_PK_PENERIMAAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_PENERIMAAN INT,
            PENERIMAAN_TGL DATETIME, 
            PENERIMAAN_STATUS VARCHAR(15), 
            ID_FK_PEMBELIAN INT, 
            PENERIMAAN_TEMPAT VARCHAR(30) COMMENT 'WAREHOUSE/CABANG', 
            ID_FK_WAREHOUSE INT, 
            ID_FK_CABANG INT, 
            PENERIMAAN_CREATE_DATE DATETIME, 
            PENERIMAAN_LAST_MODIFIED DATETIME, 
            ID_CREATE_DATA INT, 
            ID_LAST_MODIFIED INT, 
            ID_LOG_ALL INT 
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_PENERIMAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_PENERIMAAN
        AFTER INSERT ON MSTR_PENERIMAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENERIMAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.PENERIMAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PENERIMAAN_LOG(EXECUTED_FUNCTION,ID_PK_PENERIMAAN,PENERIMAAN_TGL,PENERIMAAN_STATUS,ID_FK_PEMBELIAN,PENERIMAAN_TEMPAT,ID_FK_WAREHOUSE,ID_FK_CABANG,PENERIMAAN_CREATE_DATE,PENERIMAAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_PENERIMAAN,NEW.PENERIMAAN_TGL,NEW.PENERIMAAN_STATUS,NEW.ID_FK_PEMBELIAN,NEW.PENERIMAAN_TEMPAT,NEW.ID_FK_WAREHOUSE,NEW.ID_FK_CABANG,NEW.PENERIMAAN_CREATE_DATE,NEW.PENERIMAAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_PENERIMAAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_PENERIMAAN
        AFTER UPDATE ON MSTR_PENERIMAAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENERIMAAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.PENERIMAAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_PENERIMAAN_LOG(EXECUTED_FUNCTION,ID_PK_PENERIMAAN,PENERIMAAN_TGL,PENERIMAAN_STATUS,ID_FK_PEMBELIAN,PENERIMAAN_TEMPAT,ID_FK_WAREHOUSE,ID_FK_CABANG,PENERIMAAN_CREATE_DATE,PENERIMAAN_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_PENERIMAAN,NEW.PENERIMAAN_TGL,NEW.PENERIMAAN_STATUS,NEW.ID_FK_PEMBELIAN,NEW.PENERIMAAN_TEMPAT,NEW.ID_FK_WAREHOUSE,NEW.ID_FK_CABANG,NEW.PENERIMAAN_CREATE_DATE,NEW.PENERIMAAN_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            (
                id_pk_penerimaan LIKE '%".$search_key."%' OR
                penerimaan_tgl LIKE '%".$search_key."%' OR
                penerimaan_status LIKE '%".$search_key."%' OR
                id_fk_pembelian LIKE '%".$search_key."%' OR
                penerimaan_tempat LIKE '%".$search_key."%' OR
                penerimaan_last_modified LIKE '%".$search_key."%'
            )";
        }
        if($this->penerimaan_tempat == "CABANG"){
            $query = "
            SELECT id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_pembelian,penerimaan_tempat,".$this->tbl_name.".id_fk_warehouse,".$this->tbl_name.".id_fk_cabang,penerimaan_last_modified,pem_pk_nomor
            FROM ".$this->tbl_name." 
            INNER JOIN MSTR_PEMBELIAN ON MSTR_PEMBELIAN.ID_PK_PEMBELIAN = ".$this->tbl_name.".ID_FK_PEMBELIAN
            INNER JOIN MSTR_SUPPLIER ON MSTR_SUPPLIER.ID_PK_SUP = MSTR_PEMBELIAN.ID_FK_SUPP
            INNER JOIN MSTR_CABANG ON MSTR_CABANG.ID_PK_CABANG = ".$this->tbl_name.".ID_FK_CABANG
            INNER JOIN MSTR_TOKO ON MSTR_TOKO.ID_PK_TOKO = MSTR_CABANG.ID_FK_TOKO
            WHERE PENERIMAAN_STATUS = ? AND SUP_STATUS = ? AND CABANG_STATUS = ? AND TOKO_STATUS = ? AND ".$this->tbl_name.".ID_FK_CABANG = ? ".$search_query."  
            ORDER BY ".$order_by." ".$order_direction." 
            LIMIT 20 OFFSET ".($page-1)*$data_per_page;
            $args = array(
                "AKTIF","AKTIF","AKTIF","AKTIF",$this->id_fk_cabang
            );
            $result["data"] = executeQuery($query,$args);
            $query = "
            SELECT id_pk_penerimaan
            FROM ".$this->tbl_name." 
            INNER JOIN MSTR_PEMBELIAN ON MSTR_PEMBELIAN.ID_PK_PEMBELIAN = ".$this->tbl_name.".ID_FK_PEMBELIAN
            INNER JOIN MSTR_SUPPLIER ON MSTR_SUPPLIER.ID_PK_SUP = MSTR_PEMBELIAN.ID_FK_SUPP
            INNER JOIN MSTR_CABANG ON MSTR_CABANG.ID_PK_CABANG = ".$this->tbl_name.".ID_FK_CABANG
            INNER JOIN MSTR_TOKO ON MSTR_TOKO.ID_PK_TOKO = MSTR_CABANG.ID_FK_TOKO
            WHERE PENERIMAAN_STATUS = ? AND SUP_STATUS = ? AND CABANG_STATUS = ? AND TOKO_STATUS = ? AND ".$this->tbl_name.".ID_FK_CABANG = ? ".$search_query."  
            ORDER BY ".$order_by." ".$order_direction;
            $result["total_data"] = executeQuery($query,$args)->num_rows();
        }
        else{
            $query = "
            SELECT id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_pembelian,penerimaan_tempat,".$this->tbl_name.".id_fk_warehouse,".$this->tbl_name.".id_fk_cabang,penerimaan_last_modified,pem_pk_nomor
            FROM ".$this->tbl_name." 
            INNER JOIN MSTR_PEMBELIAN ON MSTR_PEMBELIAN.ID_PK_PEMBELIAN = ".$this->tbl_name.".ID_FK_PEMBELIAN
            INNER JOIN MSTR_SUPPLIER ON MSTR_SUPPLIER.ID_PK_SUP = MSTR_PEMBELIAN.ID_FK_SUPP
            INNER JOIN MSTR_WAREHOUSE ON MSTR_WAREHOUSE.ID_PK_WAREHOUSE = ".$this->tbl_name.".ID_FK_WAREHOUSE
            WHERE PENERIMAAN_STATUS = ? AND SUP_STATUS = ? AND ".$this->tbl_name.".ID_FK_WAREHOUSE = ? ".$search_query." 
            ORDER BY ".$order_by." ".$order_direction." 
            LIMIT 20 OFFSET ".($page-1)*$data_per_page;
            $args = array(
                "AKTIF","AKTIF",$this->id_fk_warehouse
            );
            $result["data"] = executeQuery($query,$args);
            $query = "
            SELECT id_pk_pembelian
            FROM ".$this->tbl_name." 
            INNER JOIN MSTR_PEMBELIAN ON MSTR_PEMBELIAN.ID_PK_PEMBELIAN = ".$this->tbl_name.".ID_FK_PEMBELIAN
            INNER JOIN MSTR_SUPPLIER ON MSTR_SUPPLIER.ID_PK_SUP = MSTR_PEMBELIAN.ID_FK_SUPP
            INNER JOIN MSTR_WAREHOUSE ON MSTR_WAREHOUSE.ID_PK_WAREHOUSE = ".$this->tbl_name.".ID_FK_WAREHOUSE
            WHERE PENERIMAAN_STATUS = ? AND SUP_STATUS = ? AND ".$this->tbl_name.".ID_FK_WAREHOUSE = ? ".$search_query." 
            ORDER BY ".$order_by." ".$order_direction;
            $result["total_data"] = executeQuery($query,$args)->num_rows();
        }
        
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penerimaan_tgl" => $this->penerimaan_tgl,
                "penerimaan_status" => $this->penerimaan_status,
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "penerimaan_tempat" => $this->penerimaan_tempat,
                "penerimaan_create_date" => $this->penerimaan_create_date,
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            if(strtoupper($this->penerimaan_tempat) == "WAREHOUSE"){
                $data["id_fk_warehouse"] = $this->id_fk_warehouse;
            }
            else if(strtoupper($this->penerimaan_tempat) == "CABANG"){
                $data["id_fk_cabang"] = $this->id_fk_cabang;
            }
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_penerimaan" => $this->id_pk_penerimaan
            );
            $data = array(
                "penerimaan_tgl" => $this->penerimaan_tgl,
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
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
                "id_pk_penerimaan" => $this->id_pk_penerimaan
            );
            $data = array(
                "penerimaan_status" => "NONAKTIF",
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->penerimaan_tgl == ""){
            return false;
        }
        if($this->penerimaan_status == ""){
            return false;
        }
        if($this->id_fk_pembelian == ""){
            return false;
        }
        if($this->penerimaan_tempat == ""){
            return false;
        }
        
        if(strtoupper($this->penerimaan_tempat) == "WAREHOUSE"){
            if($this->id_fk_warehouse == ""){
                return false;
            }
        }
        else if(strtoupper($this->penerimaan_tempat) == "CABANG"){
            if($this->id_fk_cabang == ""){
                return false;
            }
        }
        if($this->penerimaan_create_date == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
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
        if($this->id_pk_penerimaan == ""){
            return false;
        }
        if($this->penerimaan_tgl == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_penerimaan == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($penerimaan_tgl,$penerimaan_status,$id_fk_pembelian,$penerimaan_tempat,$id_tempat_penerimaan){
        if(!$this->set_penerimaan_tgl($penerimaan_tgl)){
            return false;
        }
        if(!$this->set_penerimaan_status($penerimaan_status)){
            return false;
        }
        if(!$this->set_id_fk_pembelian($id_fk_pembelian)){
            return false;
        }
        if(!$this->set_penerimaan_tempat($penerimaan_tempat)){
            return false;
        }
        if(strtoupper($penerimaan_tempat) == "WAREHOUSE"){
            if(!$this->set_id_fk_warehouse($id_tempat_penerimaan)){
                return false;
            }
        }
        else if(strtoupper($penerimaan_tempat) == "CABANG"){
            if(!$this->set_id_fk_cabang($id_tempat_penerimaan)){
                return false;
            }
        }
        return true;
    }
    public function set_update($id_pk_penerimaan,$penerimaan_tgl){
        if(!$this->set_id_pk_penerimaan($id_pk_penerimaan)){
            return false;
        }
        if(!$this->set_penerimaan_tgl($penerimaan_tgl)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_penerimaan){
        if(!$this->set_id_pk_penerimaan($id_pk_penerimaan)){
            return false;
        }

        return true;
    }
    public function set_id_pk_penerimaan($id_pk_penerimaan){
        if($id_pk_penerimaan != ""){
            $this->id_pk_penerimaan = $id_pk_penerimaan;
            return true;
        }
        return false;
    }
    public function set_penerimaan_tgl($penerimaan_tgl){
        if($penerimaan_tgl != ""){
            $this->penerimaan_tgl = $penerimaan_tgl;
            return true;
        }
        return false;
    }
    public function set_penerimaan_status($penerimaan_status){
        if($penerimaan_status != ""){
            $this->penerimaan_status = $penerimaan_status;
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
    public function set_penerimaan_tempat($penerimaan_tempat){
        if($penerimaan_tempat != ""){
            $this->penerimaan_tempat = $penerimaan_tempat;
            return true;
        }
        return false;
    }
    public function set_id_fk_warehouse($id_fk_warehouse){
        if($id_fk_warehouse != ""){
            $this->id_fk_warehouse = $id_fk_warehouse;
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
    public function get_id_pk_penerimaan(){
        return $this->id_pk_penerimaan;
    }
    public function get_penerimaan_tgl(){
        return $this->penerimaan_tgl;
    }
    public function get_penerimaan_status(){
        return $this->penerimaan_status;
    }
    public function get_id_fk_pembelian(){
        return $this->id_fk_pembelian;
    }
    public function get_penerimaan_tempat(){
        return $this->penerimaan_tempat;
    }
    public function get_id_fk_warehouse(){
        return $this->id_fk_warehouse;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
}