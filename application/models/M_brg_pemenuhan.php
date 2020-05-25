<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_pemenuhan extends CI_Model{
    private $tbl_name = "TBL_BRG_PEMENUHAN";
    private $columns = array();
    private $id_pk_brg_pemenuhan;
    private $brg_pemenuhan_qty;
    private $id_fk_brg_permintaan;
    private $id_fk_cabang;
    private $id_fk_warehouse;
    private $brg_pemenuhan_tipe;
    private $brg_pemenuhan_create_date;
    private $brg_pemenuhan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        //BELOM
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
        DROP TABLE IF EXISTS TBL_BRG_PEMENUHAN;
        CREATE TABLE TBL_BRG_PEMENUHAN(
            ID_PK_BRG_PEMENUHAN INT PRIMARY KEY AUTO_INCREMENT,
            BRG_PEMENUHAN_QTY INT,
            BRG_PEMENUHAN_TIPE VARCHAR(9) COMMENT 'WAREHOUSE/CABANG',
            ID_FK_BRG_PERMINTAAN INT,
            ID_FK_CABANG INT,
            ID_FK_WAREHOUSE INT,
            BRG_PEMENUHAN_CREATE_DATE DATETIME,
            BRG_PEMENUHAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_BRG_PEMENUHAN_LOG;
        CREATE TABLE TBL_BRG_PEMENUHAN_LOG(
            ID_PK_BRG_PEMENUHAN_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_BRG_PEMENUHAN INT,
            BRG_PEMENUHAN_QTY INT,
            BRG_PEMENUHAN_TIPE VARCHAR(9) COMMENT 'WAREHOUSE/CABANG',
            ID_FK_BRG_PERMINTAAN INT,
            ID_FK_CABANG INT,
            ID_FK_WAREHOUSE INT,
            BRG_PEMENUHAN_CREATE_DATE DATETIME,
            BRG_PEMENUHAN_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT 
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_BRG_PEMENUHAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_BRG_PEMENUHAN
        AFTER INSERT ON TBL_BRG_PEMENUHAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PEMENUHAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT ' , NEW.BRG_PEMENUHAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PEMENUHAN_LOG(EXECUTED_FUNCTION,
            ID_PK_BRG_PEMENUHAN,
            BRG_PEMENUHAN_QTY,
            BRG_PEMENUHAN_TIPE,
            ID_FK_BRG_PERMINTAAN,
            ID_FK_CABANG,
            ID_FK_WAREHOUSE,
            BRG_PEMENUHAN_CREATE_DATE,
            BRG_PEMENUHAN_LAST_MODIFIED,
            ID_CREATE_DATA,
            ID_LAST_MODIFIED,
            ID_LOG_ALL) VALUES ('AFTER INSERT',
            NEW.ID_PK_BRG_PEMENUHAN,
            NEW.BRG_PEMENUHAN_QTY,
            NEW.BRG_PEMENUHAN_TIPE,
            NEW.ID_FK_BRG_PERMINTAAN,
            NEW.ID_FK_CABANG,
            NEW.ID_FK_WAREHOUSE,
            NEW.BRG_PEMENUHAN_CREATE_DATE,
            NEW.BRG_PEMENUHAN_LAST_MODIFIED,
            NEW.ID_CREATE_DATA,
            NEW.ID_LAST_MODIFIED
            ,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_BRG_PEMENUHAN;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_BRG_PEMENUHAN
        AFTER UPDATE ON TBL_BRG_PEMENUHAN
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.BRG_PEMENUHAN_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT ' , NEW.BRG_PEMENUHAN_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_BRG_PEMENUHAN_LOG(EXECUTED_FUNCTION,
            ID_PK_BRG_PEMENUHAN,
            BRG_PEMENUHAN_QTY,
            BRG_PEMENUHAN_TIPE,
            ID_FK_BRG_PERMINTAAN,
            ID_FK_CABANG,
            ID_FK_WAREHOUSE,
            BRG_PEMENUHAN_CREATE_DATE,
            BRG_PEMENUHAN_LAST_MODIFIED,
            ID_CREATE_DATA,
            ID_LAST_MODIFIED,
            ID_LOG_ALL) VALUES ('AFTER INSERT',
            NEW.ID_PK_BRG_PEMENUHAN,
            NEW.BRG_PEMENUHAN_QTY,
            NEW.BRG_PEMENUHAN_TIPE,
            NEW.ID_FK_BRG_PERMINTAAN,
            NEW.ID_FK_CABANG,
            NEW.ID_FK_WAREHOUSE,
            NEW.BRG_PEMENUHAN_CREATE_DATE,
            NEW.BRG_PEMENUHAN_LAST_MODIFIED,
            NEW.ID_CREATE_DATA,
            NEW.ID_LAST_MODIFIED
            ,@ID_LOG_ALL);
        END$$
        DELIMITER ;"
        executeQuery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        //BELOM
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
                "brg_pemenuhan_qty" => $this->brg_pemenuhan_qty,
                "id_fk_brg_permintaan" => $this->id_fk_brg_permintaan,
                "brg_pemenuhan_tipe" => $this->brg_pemenuhan_tipe,
                "brg_pemenuhan_create_date" => $this->brg_pemenuhan_create_date,
                "brg_pemenuhan_last_modified" => $this->brg_pemenuhan_last_modified,
                "id_create_data" => $this->id_create_data
                "id_last_modified" => $this->id_last_modified
            );
            if(strtoupper($this->brg_pemenuhan_tipe) == "WAREHOUSE"){
                $data["id_fk_warehouse"] = $this->id_fk_warehouse;
            }
            else if(strtoupper($this->brg_pemenuhan_tipe) == "CABANG"){
                $data["id_fk_cabang"] = $this->id_fk_cabang;
            }
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_brg_pemenuhan" => $this->id_pk_brg_pemenuhan
            );
            $data = array(
                "brg_pemenuhan_qty" => $this->brg_pemenuhan_qty,
                "brg_pemenuhan_last_modified" => $this->brg_pemenuhan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    /*public function delete(){
        //BELOM
        if($this->check_delete()){
            $where = array(
                "id_pk_brg_pemenuhan" => $this->id_pk_brg_pemenuhan
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
    }*/
    public function check_insert(){

        if($this->brg_pemenuhan_qty == ""){
            return false;
        }
        if($this->id_fk_brg_permintaan == ""){
            return false;
        }
        if($this->brg_pemenuhan_tipe == ""){
            return false;
        }
        if($this->brg_pemenuhan_create_date == ""){
            return false;
        }
        
        if(strtoupper($this->brg_pemenuhan_tipe) == "WAREHOUSE"){
            if($this->id_fk_warehouse == ""){
                return false;
            }
        }
        else if(strtoupper($this->brg_pemenuhan_tipe) == "CABANG"){
            if($this->id_fk_cabang == ""){
                return false;
            }
        }
        if($this->brg_pemenuhan_last_modified == ""){
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
        //BELOM
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
        if($this->id_pk_brg_pemenuhan == ""){
            return false;
        }
        if($this->brg_pemenuhan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($brg_pemenuhan_qty,$id_fk_brg_permintaan,$brg_pemenuhan_tipe){
        //CEKLAGI
        if(!$this->set_brg_pemenuhan_qty($brg_pemenuhan_qty)){
            return false;
        }
        if(!$this->set_id_fk_brg_permintaan($id_fk_brg_permintaan)){
            return false;
        }
        if(!$this->set_brg_pemenuhan_tipe($brg_pemenuhan_tipe)){
            return false;
        }
        if(strtoupper($brg_pemenuhan_tipe) == "WAREHOUSE"){
            if(!$this->set_id_fk_warehouse($id_fk_warehouse)){
                return false;
            }
        }
        else if(strtoupper($brg_pemenuhan_tipe) == "CABANG"){
            if(!$this->set_id_fk_cabang($id_fk_cabang)){
                return false;
            }
        }
        return true;
    }
    public function set_update($id_pk_penerimaan,$penerimaan_tgl){
        //BELOM
        if(!$this->set_id_pk_penerimaan($id_pk_penerimaan)){
            return false;
        }
        if(!$this->set_penerimaan_tgl($penerimaan_tgl)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_pemenuhan){
        if(!$this->set_id_pk_brg_pemenuhan($id_pk_brg_pemenuhan)){
            return false;
        }

        return true;
    }

    public function set_brg_pemenuhan_qty($brg_pemenuhan_qty){
        if($brg_pemenuhan_qty != ""){
            $this->brg_pemenuhan_qty = $brg_pemenuhan_qty;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg_permintaan($id_fk_brg_permintaan){
        if($id_fk_brg_permintaan != ""){
            $this->id_fk_brg_permintaan = $id_fk_brg_permintaan;
            return true;
        }
        return false;
    }
    public function set_brg_pemenuhan_tipe($brg_pemenuhan_tipe){
        if($brg_pemenuhan_tipe != ""){
            $this->brg_pemenuhan_tipe = $brg_pemenuhan_tipe;
            return true;
        }
        return false;
    }
    
    public function get_brg_pemenuhan_qty(){
        return $this->brg_pemenuhan_qty;
    }
    public function get_id_fk_brg_permintaan(){
        return $this->id_fk_brg_permintaan;
    }
    public function get_brg_pemenuhan_tipe(){
        return $this->brg_pemenuhan_tipe;
    }
}