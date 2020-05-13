<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_barang extends CI_Model{
    private $tbl_name = "MSTR_BARANG";
    private $columns = array();
    private $id_pk_brg;
    private $brg_kode;
    private $brg_nama;
    private $brg_ket;
    private $brg_minimal;
    private $brg_status;
    private $brg_satuan;
    private $brg_image;
    private $brg_create_date;
    private $brg_last_modified;
    private $id_create_data;
    private $id_last_modified;
    private $id_fk_brg_jenis;
    private $id_fk_brg_merk;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_kode","Kode",true);
        $this->set_column("brg_jenis_nama","Jenis",false);
        $this->set_column("brg_nama","Nama",false);
        $this->set_column("brg_ket","Keterangan",false);
        $this->set_column("brg_merk_nama","Merk",false);
        $this->set_column("brg_minimal","Minimal",false);
        $this->set_column("brg_satuan","Satuan",false);
        $this->set_column("brg_status","Status",false);
        $this->set_column("brg_last_modified","Last Modified",false);

        $this->brg_create_date = date("Y-m-d H:i:s");
        $this->brg_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS MSTR_BARANG;
        CREATE TABLE MSTR_BARANG(
            ID_PK_BRG INT PRIMARY KEY AUTO_INCREMENT,
            BRG_KODE VARCHAR(50),
            BRG_NAMA VARCHAR(100),
            BRG_KET VARCHAR(200),
            BRG_MINIMAL DOUBLE,
            BRG_SATUAN VARCHAR(30),
            BRG_IMAGE VARCHAR(100),
            BRG_STATUS VARCHAR(15),
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
            BRG_KET VARCHAR(200),
            BRG_MINIMAL DOUBLE,
            BRG_SATUAN VARCHAR(30),
            BRG_IMAGE VARCHAR(100),
            BRG_STATUS VARCHAR(15),
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
            ID_PK_BRG,BRG_KODE,BRG_NAMA,BRG_KET,BRG_MINIMAL,BRG_SATUAN,BRG_IMAGE,BRG_STATUS,BRG_CREATE_DATE,BRG_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_FK_BRG_JENIS,ID_FK_BRG_MERK,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_BRG,NEW.BRG_KODE,NEW.BRG_NAMA,NEW.BRG_KET,NEW.BRG_MINIMAL,NEW.BRG_SATUAN,NEW.BRG_IMAGE,NEW.BRG_STATUS,NEW.BRG_CREATE_DATE,NEW.BRG_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,NEW.ID_FK_BRG_JENIS,NEW.ID_FK_BRG_MERK,@ID_LOG_ALL);
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
            ID_PK_BRG,BRG_KODE,BRG_NAMA,BRG_KET,BRG_MINIMAL,BRG_SATUAN,BRG_IMAGE,BRG_STATUS,BRG_CREATE_DATE,BRG_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_FK_BRG_JENIS,ID_FK_BRG_MERK,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_BRG,NEW.BRG_KODE,NEW.BRG_NAMA,NEW.BRG_KET,NEW.BRG_MINIMAL,NEW.BRG_SATUAN,NEW.BRG_IMAGE,NEW.BRG_STATUS,NEW.BRG_CREATE_DATE,NEW.BRG_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,NEW.ID_FK_BRG_JENIS,NEW.ID_FK_BRG_MERK,@ID_LOG_ALL);
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
                BRG_KODE LIKE '%".$search_key."%' OR
                BRG_NAMA LIKE '%".$search_key."%' OR
                BRG_KET LIKE '%".$search_key."%' OR
                BRG_MINIMAL LIKE '%".$search_key."%' OR
                BRG_STATUS LIKE '%".$search_key."%' OR
                BRG_SATUAN LIKE '%".$search_key."%' OR
                BRG_IMAGE LIKE '%".$search_key."%' OR
                BRG_MERK_NAMA LIKE '%".$search_key."%' OR
                BRG_JENIS_NAMA LIKE '%".$search_key."%' OR
                BRG_LAST_MODIFIED LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_status,brg_satuan,brg_image,brg_last_modified,brg_merk_nama,brg_jenis_nama,GROUP_CONCAT(tbl_barang_ukuran.UKURAN SEPARATOR ',') as ukuran
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_BARANG_JENIS ON MSTR_BARANG_JENIS.ID_PK_BRG_JENIS = ".$this->tbl_name.".ID_FK_BRG_JENIS
        INNER JOIN MSTR_BARANG_MERK ON MSTR_BARANG_MERK.ID_PK_BRG_MERK = ".$this->tbl_name.".ID_FK_BRG_MERK
        LEFT JOIN TBL_BARANG_UKURAN ON TBL_BARANG_UKURAN.ID_FK_BARANG = ".$this->tbl_name.".ID_PK_BRG
        WHERE BRG_STATUS = ? AND BRG_JENIS_STATUS = ? AND BRG_MERK_STATUS = ?".$search_query."  
        GROUP BY id_pk_brg 
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF","AKTIF","AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        //echo $this->db->last_query();
        $query = "
        SELECT id_pk_brg
        FROM ".$this->tbl_name." 
        INNER JOIN MSTR_BARANG_JENIS ON MSTR_BARANG_JENIS.ID_PK_BRG_JENIS = ".$this->tbl_name.".ID_FK_BRG_JENIS
        INNER JOIN MSTR_BARANG_MERK ON MSTR_BARANG_MERK.ID_PK_BRG_MERK = ".$this->tbl_name.".ID_FK_BRG_MERK
        WHERE BRG_STATUS = ? AND BRG_JENIS_STATUS = ? AND BRG_MERK_STATUS = ?".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_name(){
        $where = array(
            "brg_nama" => $this->brg_nama
        );
        $field = array(
            "id_pk_brg","brg_kode","brg_nama","brg_ket","brg_minimal","brg_status","brg_satuan","brg_image","brg_create_date","brg_last_modified","id_create_data","id_last_modified","id_fk_brg_jenis","id_fk_brg_merk"
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function short_insert(){
        $data = array(
            "brg_nama" => $this->brg_nama,
            "brg_status" => "AKTIF",
            "brg_create_date" => $this->brg_create_date,
            "brg_last_modified" => $this->brg_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified
        );
        return insertRow($this->tbl_name,$data);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_kode" => $this->brg_kode,
                "brg_nama" => $this->brg_nama,
                "brg_ket" => $this->brg_ket,
                "brg_minimal" => $this->brg_minimal,
                "brg_status" => $this->brg_status,
                "brg_satuan" => $this->brg_satuan,
                "brg_image" => $this->brg_image,
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
                "brg_status" => "AKTIF"
            );
            if(!isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_brg" => $this->id_pk_brg
                );
                $data = array(
                    "brg_kode" => $this->brg_kode,
                    "brg_nama" => $this->brg_nama,
                    "brg_ket" => $this->brg_ket,
                    "brg_minimal" => $this->brg_minimal,
                    "brg_satuan" => $this->brg_satuan,
                    "brg_image" => $this->brg_image,
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
        if($this->brg_ket == ""){
            return false;
        }
        if($this->brg_minimal == ""){
            return false;
        }
        if($this->id_fk_brg_jenis == ""){
            return false;
        }
        if($this->brg_status == ""){
            return false;
        }
        if($this->brg_satuan == ""){
            return false;
        }
        if($this->brg_image == ""){
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
        if($this->brg_ket == ""){
            return false;
        }
        if($this->brg_minimal == ""){
            return false;
        }
        if($this->brg_satuan == ""){
            return false;
        }
        if($this->brg_image == ""){
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
    public function set_insert($brg_kode,$brg_nama,$brg_ket,$brg_minimal,$brg_satuan,$brg_image,$brg_status,$id_fk_brg_jenis,$id_fk_brg_merk){
        if(!$this->set_brg_kode($brg_kode)){
            return false;
        }
        if(!$this->set_brg_nama($brg_nama)){
            return false;
        }
        if(!$this->set_brg_ket($brg_ket)){
            return false;
        }
        if(!$this->set_brg_minimal($brg_minimal)){
            return false;
        }
        if(!$this->set_brg_satuan($brg_satuan)){
            return false;
        }
        if(!$this->set_brg_image($brg_image)){
            return false;
        }
        if(!$this->set_brg_status($brg_status)){
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
    public function set_update($id_pk_brg,$brg_kode,$brg_nama,$brg_ket,$brg_minimal,$brg_satuan,$brg_image,$id_fk_brg_jenis,$id_fk_brg_merk){
        if(!$this->set_id_pk_brg($id_pk_brg)){
            return false;
        }
        if(!$this->set_brg_kode($brg_kode)){
            return false;
        }
        if(!$this->set_brg_nama($brg_nama)){
            return false;
        }
        if(!$this->set_brg_ket($brg_ket)){
            return false;
        }
        if(!$this->set_brg_minimal($brg_minimal)){
            return false;
        }
        if(!$this->set_brg_satuan($brg_satuan)){
            return false;
        }
        if(!$this->set_brg_image($brg_image)){
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
    public function get_brg_ket(){
        return $this->brg_ket;
    }
    public function get_brg_minimal(){
        return $this->brg_minimal;
    }
    public function get_brg_satuan(){
        return $this->brg_satuan;
    }
    public function get_brg_image(){
        return $this->brg_image;
    }
    public function get_brg_status(){
        return $this->brg_status;
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
    public function set_brg_satuan($brg_satuan){
        if($brg_satuan != ""){
            $this->brg_satuan = $brg_satuan;
            return true;
        }
        return false;
    }
    public function set_brg_image($brg_image){
        if($brg_image != ""){
            $this->brg_image = $brg_image;
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