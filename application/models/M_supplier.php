<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_supplier extends CI_Model{
    private $tbl_name = "MSTR_SUPPLIER";
    private $columns = array();
    private $id_pk_sup;
    private $sup_nama;
    private $sup_perusahaan;
    private $sup_email;
    private $sup_telp;
    private $sup_hp;
    private $sup_alamat;
    private $sup_keterangan;
    private $sup_status;
    private $sup_create_date;
    private $sup_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("sup_nama","PIC",true);
        $this->set_column("sup_perusahaan","Supplier",false);
        $this->set_column("sup_email","Email",false);
        $this->set_column("sup_telp","No Telp",false);
        $this->set_column("sup_hp","No HP",false);
        $this->set_column("sup_alamat","Alamat",false);
        $this->set_column("sup_keterangan","Keterangan",false);
        $this->set_column("sup_status","Status",false);
        $this->set_column("sup_last_modified","Last Modified",false);
        $this->sup_create_date = date("Y-m-d H:i:s"); 
        $this->sup_last_modified = date("Y-m-d H:i:s"); 
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS MSTR_SUPPLIER;
        CREATE TABLE MSTR_SUPPLIER(
            ID_PK_SUP INT PRIMARY KEY AUTO_INCREMENT,
            SUP_NAMA VARCHAR(100),
            SUP_PERUSAHAAN VARCHAR(100),
            SUP_EMAIL VARCHAR(100),
            SUP_TELP VARCHAR(30),
            SUP_HP VARCHAR(30),
            SUP_ALAMAT VARCHAR(150),
            SUP_KETERANGAN VARCHAR(150),
            SUP_STATUS VARCHAR(15),
            SUP_CREATE_DATE DATETIME,
            SUP_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_SUPPLIER_LOG;
        CREATE TABLE MSTR_SUPPLIER_LOG(
            ID_PK_SUP_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_SUP INT,
            SUP_NAMA VARCHAR(100),
            SUP_PERUSAHAAN VARCHAR(100),
            SUP_EMAIL VARCHAR(100),
            SUP_TELP VARCHAR(30),
            SUP_HP VARCHAR(30),
            SUP_ALAMAT VARCHAR(150),
            SUP_KETERANGAN VARCHAR(150),
            SUP_STATUS VARCHAR(15),
            SUP_CREATE_DATE DATETIME,
            SUP_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_SUPPLIER;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_SUPPLIER
        AFTER INSERT ON MSTR_SUPPLIER
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SUP_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SUP_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_SUPPLIER_LOG(EXECUTED_FUNCTION,ID_PK_SUP,SUP_NAMA,SUP_PERUSAHAAN,SUP_EMAIL,SUP_TELP,SUP_HP,SUP_ALAMAT,SUP_KETERANGAN,SUP_STATUS,SUP_CREATE_DATE,SUP_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SUP,NEW.SUP_NAMA,NEW.SUP_PERUSAHAAN,NEW.SUP_EMAIL,NEW.SUP_TELP,NEW.SUP_HP,NEW.SUP_ALAMAT,NEW.SUP_KETERANGAN,NEW.SUP_STATUS,NEW.SUP_CREATE_DATE,NEW.SUP_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;

        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_SUPPLIER;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_SUPPLIER
        AFTER UPDATE ON MSTR_SUPPLIER
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.SUP_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SUP_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_SUPPLIER_LOG(EXECUTED_FUNCTION,ID_PK_SUP,SUP_NAMA,SUP_PERUSAHAAN,SUP_EMAIL,SUP_TELP,SUP_HP,SUP_ALAMAT,SUP_KETERANGAN,SUP_STATUS,SUP_CREATE_DATE,SUP_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SUP,NEW.SUP_NAMA,NEW.SUP_PERUSAHAAN,NEW.SUP_EMAIL,NEW.SUP_TELP,NEW.SUP_HP,NEW.SUP_ALAMAT,NEW.SUP_KETERANGAN,NEW.SUP_STATUS,NEW.SUP_CREATE_DATE,NEW.SUP_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                sup_nama LIKE '%".$search_key."%' OR
                sup_perusahaan LIKE '%".$search_key."%' OR
                sup_email LIKE '%".$search_key."%' OR
                sup_telp LIKE '%".$search_key."%' OR
                sup_hp LIKE '%".$search_key."%' OR
                sup_alamat LIKE '%".$search_key."%' OR
                sup_keterangan LIKE '%".$search_key."%' OR
                sup_status LIKE '%".$search_key."%' OR
                sup_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_sup,sup_nama,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_last_modified
        FROM ".$this->tbl_name." 
        WHERE sup_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_sup
        FROM ".$this->tbl_name." 
        WHERE sup_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_perusahaan(){
        $where = array(
            "sup_perusahaan" => $this->sup_perusahaan
        );
        $field = array(
            "id_pk_sup","sup_nama","sup_perusahaan","sup_email","sup_telp","sup_hp","sup_alamat","sup_keterangan","sup_status","sup_last_modified"
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "sup_nama" => $this->sup_nama,
                "sup_perusahaan" => $this->sup_perusahaan,
                "sup_email" => $this->sup_email,
                "sup_telp" => $this->sup_telp,
                "sup_hp" => $this->sup_hp,
                "sup_alamat" => $this->sup_alamat,
                "sup_keterangan" => $this->sup_keterangan,
                "sup_status" => $this->sup_status,
                "sup_create_date" => $this->sup_create_date,
                "sup_last_modified" => $this->sup_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function short_insert(){
        $data = array(
            "sup_perusahaan" => $this->sup_perusahaan,
            "sup_status" => "AKTIF",
            "sup_create_date" => $this->sup_create_date,
            "sup_last_modified" => $this->sup_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified
        );
        return insertRow($this->tbl_name,$data);
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_sup" => $this->id_pk_sup
            );
            $data = array(
                "sup_nama" => $this->sup_nama,
                "sup_perusahaan" => $this->sup_perusahaan,
                "sup_email" => $this->sup_email,
                "sup_telp" => $this->sup_telp,
                "sup_hp" => $this->sup_hp,
                "sup_alamat" => $this->sup_alamat,
                "sup_keterangan" => $this->sup_keterangan,
                "sup_last_modified" => $this->sup_last_modified,
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
                "id_pk_sup" => $this->id_pk_sup
            );
            $data = array(
                "sup_status" => "NONAKTIF",
                "sup_last_modified" => $this->sup_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->sup_nama == ""){
            return false;
        }
        if($this->sup_perusahaan == ""){
            return false;
        }
        if($this->sup_email == ""){
            return false;
        }
        if($this->sup_telp == ""){
            return false;
        }
        if($this->sup_hp == ""){
            return false;
        }
        if($this->sup_alamat == ""){
            return false;
        }
        if($this->sup_keterangan == ""){
            return false;
        }
        if($this->sup_status == ""){
            return false;
        }
        if($this->sup_create_date == ""){
            return false;
        }
        if($this->sup_last_modified == ""){
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
        if($this->id_pk_sup == ""){
            return false;
        }
        if($this->sup_nama == ""){
            return false;
        }
        if($this->sup_perusahaan == ""){
            return false;
        }
        if($this->sup_email == ""){
            return false;
        }
        if($this->sup_telp == ""){
            return false;
        }
        if($this->sup_hp == ""){
            return false;
        }
        if($this->sup_alamat == ""){
            return false;
        }
        if($this->sup_keterangan == ""){
            return false;
        }
        if($this->sup_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_sup == ""){
            return false;
        }
        if($this->sup_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($sup_nama,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan,$sup_status){
        if(!$this->set_sup_nama($sup_nama)){
            return false;
        }
        if(!$this->set_sup_perusahaan($sup_perusahaan)){
            return false;
        }
        if(!$this->set_sup_email($sup_email)){
            return false;
        }
        if(!$this->set_sup_telp($sup_telp)){
            return false;
        }
        if(!$this->set_sup_hp($sup_hp)){
            return false;
        }
        if(!$this->set_sup_alamat($sup_alamat)){
            return false;
        }
        if(!$this->set_sup_keterangan($sup_keterangan)){
            return false;
        }
        if(!$this->set_sup_status($sup_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_sup,$sup_nama,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan){
        if(!$this->set_id_pk_sup($id_pk_sup)){
            return false;
        }
        if(!$this->set_sup_nama($sup_nama)){
            return false;
        }
        if(!$this->set_sup_perusahaan($sup_perusahaan)){
            return false;
        }
        if(!$this->set_sup_email($sup_email)){
            return false;
        }
        if(!$this->set_sup_telp($sup_telp)){
            return false;
        }
        if(!$this->set_sup_hp($sup_hp)){
            return false;
        }
        if(!$this->set_sup_alamat($sup_alamat)){
            return false;
        }
        if(!$this->set_sup_keterangan($sup_keterangan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_sup){
        if(!$this->set_id_pk_sup($id_pk_sup)){
            return false;
        }
        return true;
    }
    public function set_id_pk_sup($id_pk_sup){
        if($id_pk_sup != ""){
            $this->id_pk_sup = $id_pk_sup;
            return true;
        }
        return false;
    }
    public function set_sup_nama($sup_nama){
        if($sup_nama != ""){
            $this->sup_nama = $sup_nama;
            return true;
        }
        return false;
    }
    public function set_sup_perusahaan($sup_perusahaan){
        if($sup_perusahaan != ""){
            $this->sup_perusahaan = $sup_perusahaan;
            return true;
        }
        return false;
    }
    public function set_sup_email($sup_email){
        if($sup_email != ""){
            $this->sup_email = $sup_email;
            return true;
        }
        return false;
    }
    public function set_sup_telp($sup_telp){
        if($sup_telp != ""){
            $this->sup_telp = $sup_telp;
            return true;
        }
        return false;
    }
    public function set_sup_hp($sup_hp){
        if($sup_hp != ""){
            $this->sup_hp = $sup_hp;
            return true;
        }
        return false;
    }
    public function set_sup_alamat($sup_alamat){
        if($sup_alamat != ""){
            $this->sup_alamat = $sup_alamat;
            return true;
        }
        return false;
    }
    public function set_sup_keterangan($sup_keterangan){
        if($sup_keterangan != ""){
            $this->sup_keterangan = $sup_keterangan;
            return true;
        }
        return false;
    }
    public function set_sup_status($sup_status){
        if($sup_status != ""){
            $this->sup_status = $sup_status;
            return true;
        }
        return false;
    }
    public function get_id_pk_sup(){
        return $this->id_pk_sup;
    }
    public function get_sup_nama(){
        return $this->sup_nama;
    }
    public function get_sup_perusahaan(){
        return $this->sup_perusahaan;
    }
    public function get_sup_email(){
        return $this->sup_email;
    }
    public function get_sup_telp(){
        return $this->sup_telp;
    }
    public function get_sup_hp(){
        return $this->sup_hp;
    }
    public function get_sup_alamat(){
        return $this->sup_alamat;
    }
    public function get_sup_keterangan(){
        return $this->sup_keterangan;
    }
    public function get_sup_status(){
        return $this->sup_status;
    }
}
?>