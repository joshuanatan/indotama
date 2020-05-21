<?php
defined("BASEPATH") or exit("No direct script");
date_default_timezone_set("Asia/Jakarta");
class M_customer extends CI_Model{
    private $tbl_name = "MSTR_CUSTOMER";
    private $columns = array();
    private $id_pk_cust;
    private $cust_name;
    private $cust_perusahaan;
    private $cust_email;
    private $cust_telp;
    private $cust_hp;
    private $cust_alamat;
    private $cust_keterangan;
    private $cust_status;
    private $cust_create_date;
    private $cust_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("cust_name","Name",true);
        $this->set_column("cust_perusahaan","Perusahaan",false);
        $this->set_column("cust_email","Email",false);
        $this->set_column("cust_telp","Telp",false);
        $this->set_column("cust_hp","Hp",false);
        $this->set_column("cust_alamat","Alamat",false);
        $this->set_column("cust_keterangan","Keterangan",false);
        $this->set_column("cust_status","Status",false);
        $this->set_column("cust_last_modified","Last Modified",false);
        $this->cust_create_date = date("Y-m-d H:i:s");
        $this->cust_last_modified = date("Y-m-d H:i:s");
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
        DROP TABLE IF EXISTS MSTR_CUSTOMER;
        CREATE TABLE MSTR_CUSTOMER(
            ID_PK_CUST INT PRIMARY KEY AUTO_INCREMENT,
            CUST_NAME VARCHAR(100),
            CUST_PERUSAHAAN VARCHAR(100),
            CUST_EMAIL VARCHAR(100),
            CUST_TELP VARCHAR(30),
            CUST_HP VARCHAR(30),
            CUST_ALAMAT VARCHAR(150),
            CUST_KETERANGAN VARCHAR(150),
            ID_FK_TOKO INT,
            CUST_STATUS VARCHAR(15),
            CUST_CREATE_DATE DATETIME,
            CUST_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_CUSTOMER_LOG;
        CREATE TABLE MSTR_CUSTOMER_LOG(
            ID_PK_CUST_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_CUST INT,
            CUST_NAME VARCHAR(100),
            CUST_PERUSAHAAN VARCHAR(100),
            CUST_EMAIL VARCHAR(100),
            CUST_TELP VARCHAR(30),
            CUST_HP VARCHAR(30),
            CUST_ALAMAT VARCHAR(150),
            CUST_KETERANGAN VARCHAR(150),
            ID_FK_TOKO INT,
            CUST_STATUS VARCHAR(15),
            CUST_CREATE_DATE DATETIME,
            CUST_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_CUSTOMER;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_CUSTOMER
        AFTER INSERT ON MSTR_CUSTOMER
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.CUST_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT ' , NEW.CUST_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_CUSTOMER_LOG(EXECUTED_FUNCTION,ID_PK_CUST,CUST_NAME,CUST_PERUSAHAAN,CUST_EMAIL,CUST_TELP,CUST_HP,CUST_ALAMAT,CUST_KETERANGAN,ID_FK_TOKO,CUST_STATUS,CUST_CREATE_DATE,CUST_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_CUST,NEW.CUST_NAME,NEW.CUST_PERUSAHAAN,NEW.CUST_EMAIL,NEW.CUST_TELP,NEW.CUST_HP,NEW.CUST_ALAMAT,NEW.CUST_KETERANGAN,NEW.ID_FK_TOKO,NEW.CUST_STATUS,NEW.CUST_CREATE_DATE,NEW.CUST_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_CUSTOMER;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_CUSTOMER
        AFTER UPDATE ON MSTR_CUSTOMER
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.CUST_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT ' , NEW.CUST_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_CUSTOMER_LOG(EXECUTED_FUNCTION,ID_PK_CUST,CUST_NAME,CUST_PERUSAHAAN,CUST_EMAIL,CUST_TELP,CUST_HP,CUST_ALAMAT,CUST_KETERANGAN,ID_FK_TOKO,CUST_STATUS,CUST_CREATE_DATE,CUST_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_CUST,NEW.CUST_NAME,NEW.CUST_PERUSAHAAN,NEW.CUST_EMAIL,NEW.CUST_TELP,NEW.CUST_HP,NEW.CUST_ALAMAT,NEW.CUST_KETERANGAN,NEW.ID_FK_TOKO,NEW.CUST_STATUS,NEW.CUST_CREATE_DATE,NEW.CUST_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                id_pk_cust LIKE '%".$search_key."%' OR
                cust_name LIKE '%".$search_key."%' OR
                cust_perusahaan LIKE '%".$search_key."%' OR
                cust_email LIKE '%".$search_key."%' OR
                cust_telp LIKE '%".$search_key."%' OR
                cust_hp LIKE '%".$search_key."%' OR
                cust_alamat LIKE '%".$search_key."%' OR
                cust_keterangan LIKE '%".$search_key."%' OR
                cust_status LIKE '%".$search_key."%' OR
                cust_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_cust,cust_name,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,cust_status,cust_last_modified
        FROM ".$this->tbl_name." 
        WHERE cust_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_cust
        FROM ".$this->tbl_name." 
        WHERE cust_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "cust_name" => $this->cust_name,
                "cust_perusahaan" => $this->cust_perusahaan,
                "cust_email" => $this->cust_email,
                "cust_telp" => $this->cust_telp,
                "cust_hp" => $this->cust_hp,
                "cust_alamat" => $this->cust_alamat,
                "cust_keterangan" => $this->cust_keterangan,
                "cust_status" => $this->cust_status,
                "cust_create_date" => $this->cust_create_date,
                "cust_last_modified" => $this->cust_last_modified,
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
                "id_pk_cust" => $this->id_pk_cust
            );
            $data = array(
                "cust_name" => $this->cust_name,
                "cust_perusahaan" => $this->cust_perusahaan,
                "cust_email" => $this->cust_email,
                "cust_telp" => $this->cust_telp,
                "cust_hp" => $this->cust_hp,
                "cust_alamat" => $this->cust_alamat,
                "cust_keterangan" => $this->cust_keterangan,
                "cust_last_modified" => $this->cust_last_modified,
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
                "id_pk_cust" => $this->id_pk_cust
            );
            $data = array(
                "cust_status" => "NONAKTIF",
                "cust_last_modified" => $this->cust_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->cust_name == ""){
            return false;
        }
        if($this->cust_perusahaan == ""){
            return false;
        }
        if($this->cust_email == ""){
            return false;
        }
        if($this->cust_telp == ""){
            return false;
        }
        if($this->cust_hp == ""){
            return false;
        }
        if($this->cust_alamat == ""){
            return false;
        }
        if($this->cust_keterangan == ""){
            return false;
        }
        if($this->cust_status == ""){
            return false;
        }
        if($this->cust_create_date == ""){
            return false;
        }
        if($this->cust_last_modified == ""){
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
        if($this->id_pk_cust == ""){
            return false;
        }
        if($this->cust_name == ""){
            return false;
        }
        if($this->cust_perusahaan == ""){
            return false;
        }
        if($this->cust_email == ""){
            return false;
        }
        if($this->cust_telp == ""){
            return false;
        }
        if($this->cust_hp == ""){
            return false;
        }
        if($this->cust_alamat == ""){
            return false;
        }
        if($this->cust_keterangan == ""){
            return false;
        }
        if($this->cust_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_cust == ""){
            return false;
        }
        if($this->cust_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($cust_name,$cust_perusahaan,$cust_email,$cust_telp,$cust_hp,$cust_alamat,$cust_keterangan,$cust_status){
        if(!$this->set_cust_name($cust_name)){
            return false;
        }
        if(!$this->set_cust_perusahaan($cust_perusahaan)){
            return false;
        }
        if(!$this->set_cust_email($cust_email)){
            return false;
        }
        if(!$this->set_cust_telp($cust_telp)){
            return false;
        }
        if(!$this->set_cust_hp($cust_hp)){
            return false;
        }
        if(!$this->set_cust_alamat($cust_alamat)){
            return false;
        }
        if(!$this->set_cust_keterangan($cust_keterangan)){
            return false;
        }
        if(!$this->set_cust_status($cust_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_cust,$cust_name,$cust_perusahaan,$cust_email,$cust_telp,$cust_hp,$cust_alamat,$cust_keterangan){
        if(!$this->set_id_pk_cust($id_pk_cust)){
            return false;
        }
        if(!$this->set_cust_name($cust_name)){
            return false;
        }
        if(!$this->set_cust_perusahaan($cust_perusahaan)){
            return false;
        }
        if(!$this->set_cust_email($cust_email)){
            return false;
        }
        if(!$this->set_cust_telp($cust_telp)){
            return false;
        }
        if(!$this->set_cust_hp($cust_hp)){
            return false;
        }
        if(!$this->set_cust_alamat($cust_alamat)){
            return false;
        }
        if(!$this->set_cust_keterangan($cust_keterangan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_cust){
        if(!$this->set_id_pk_cust($id_pk_cust)){
            return false;
        }
        return true;
    }
    public function set_id_pk_cust($id_pk_cust){
        if($id_pk_cust != ""){
            $this->id_pk_cust = $id_pk_cust;
            return true;
        }
        return false;
    }
    public function set_cust_name($cust_name){
        if($cust_name != ""){
            $this->cust_name = $cust_name;
            return true;
        }
        return false;
    }
    public function set_cust_perusahaan($cust_perusahaan){
        if($cust_perusahaan != ""){
            $this->cust_perusahaan = $cust_perusahaan;
            return true;
        }
        return false;
    }
    public function set_cust_email($cust_email){
        if($cust_email != ""){
            $this->cust_email = $cust_email;
            return true;
        }
        return false;
    }
    public function set_cust_telp($cust_telp){
        if($cust_telp != ""){
            $this->cust_telp = $cust_telp;
            return true;
        }
        return false;
    }
    public function set_cust_hp($cust_hp){
        if($cust_hp != ""){
            $this->cust_hp = $cust_hp;
            return true;
        }
        return false;
    }
    public function set_cust_alamat($cust_alamat){
        if($cust_alamat != ""){
            $this->cust_alamat = $cust_alamat;
            return true;
        }
        return false;
    }
    public function set_cust_keterangan($cust_keterangan){
        if($cust_keterangan != ""){
            $this->cust_keterangan = $cust_keterangan;
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
    public function set_cust_status($cust_status){
        if($cust_status != ""){
            $this->cust_status = $cust_status;
            return true;
        }
        return false;
    }
    public function get_id_pk_cust(){
        return $this->id_pk_cust;
    }
    public function get_cust_name(){
        return $this->cust_name;
    }
    public function get_cust_perusahaan(){
        return $this->cust_perusahaan;
    }
    public function get_cust_email(){
        return $this->cust_email;
    }
    public function get_cust_telp(){
        return $this->cust_telp;
    }
    public function get_cust_hp(){
        return $this->cust_hp;
    }
    public function get_cust_alamat(){
        return $this->cust_alamat;
    }
    public function get_cust_keterangan(){
        return $this->cust_keterangan;
    }
    public function get_id_fk_toko(){
        return $this->id_fk_toko;
    }
    public function get_cust_status(){
        return $this->cust_status;
    }
}