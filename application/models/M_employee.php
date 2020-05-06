<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_employee extends CI_Model{
    private $columns = array();
    private $tbl_name = "MSTR_EMPLOYEE";
    private $id_pk_employee;
    private $emp_nama;
    private $emp_npwp;
    private $emp_ktp;
    private $emp_hp;
    private $emp_alamat;
    private $emp_kode_pos;
    private $emp_foto_npwp;
    private $emp_foto_ktp;
    private $emp_foto_lain;
    private $emp_foto;
    private $emp_gaji;
    private $emp_startdate;
    private $emp_enddate;
    private $emp_rek;
    private $emp_gender;
    private $emp_suff;
    private $emp_status;
    private $id_fk_toko;
    private $emp_create_date;
    private $emp_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->columns = array(
            array(
                "col_name" => "menu_controller",
                "col_disp" => "Controller",
                "order_by" => true
            ),
        );
        $this->emp_create_date = date("Y-m-d H:i:s");
        $this->emp_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS MSTR_EMPLOYEE;
        CREATE TABLE MSTR_EMPLOYEE(
            ID_PK_EMPLOYEE INT PRIMARY KEY AUTO_INCREMENT,
            EMP_NAMA VARCHAR(400),
            EMP_NPWP VARCHAR(25),
            EMP_KTP VARCHAR(20),
            EMP_HP VARCHAR(15),
            EMP_ALAMAT VARCHAR(300),
            EMP_KODE_POS VARCHAR(10),
            EMP_FOTO_NPWP VARCHAR(50),
            EMP_FOTO_KTP VARCHAR(50),
            EMP_FOTO_LAIN VARCHAR(50),
            EMP_FOTO VARCHAR(50),
            EMP_GAJI INT,
            EMP_STARTDATE DATETIME,
            EMP_ENDDATE DATETIME,
            EMP_REK VARCHAR(30),
            EMP_GENDER VARCHAR(6),
            EMP_SUFF VARCHAR(10),
            EMP_STATUS VARCHAR(15),
            ID_FK_TOKO INT,
            EMP_CREATE_DATE DATETIME,
            EMP_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_EMPLOYEE_LOG;
        CREATE TABLE MSTR_EMPLOYEE_LOG(
            ID_PK_EMPLOYEE_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(40),
            ID_PK_EMPLOYEE INT,
            EMP_NAMA VARCHAR(400),
            EMP_NPWP VARCHAR(25),
            EMP_KTP VARCHAR(20),
            EMP_HP VARCHAR(15),
            EMP_ALAMAT VARCHAR(300),
            EMP_KODE_POS VARCHAR(10),
            EMP_FOTO_NPWP VARCHAR(50),
            EMP_FOTO_KTP VARCHAR(50),
            EMP_FOTO_LAIN VARCHAR(50),
            EMP_FOTO VARCHAR(50),
            EMP_GAJI INT,
            EMP_STARTDATE DATETIME,
            EMP_ENDDATE DATETIME,
            EMP_REK VARCHAR(30),
            EMP_GENDER VARCHAR(6),
            EMP_SUFF VARCHAR(10),
            EMP_STATUS VARCHAR(15),
            ID_FK_TOKO INT,
            EMP_CREATE_DATE DATETIME,
            EMP_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_EMPLOYEE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_EMPLOYEE
        AFTER INSERT ON MSTR_EMPLOYEE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.EMP_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT ' , NEW.EMP_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_EMPLOYEE_LOG(EXECUTED_FUNCTION,ID_PK_EMPLOYEE,EMP_NAMA,EMP_NPWP,EMP_KTP,EMP_HP,EMP_ALAMAT,EMP_KODE_POS,EMP_FOTO_NPWP,EMP_FOTO_KTP,EMP_FOTO_LAIN,EMP_FOTO,EMP_GAJI,EMP_STARTDATE,EMP_ENDDATE,EMP_REK,EMP_GENDER,EMP_SUFF,EMP_STATUS,ID_FK_TOKO,EMP_CREATE_DATE,EMP_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_EMPLOYEE,NEW.EMP_NAMA,NEW.EMP_NPWP,NEW.EMP_KTP,NEW.EMP_HP,NEW.EMP_ALAMAT,NEW.EMP_KODE_POS,NEW.EMP_FOTO_NPWP,NEW.EMP_FOTO_KTP,NEW.EMP_FOTO_LAIN,NEW.EMP_FOTO,NEW.EMP_GAJI,NEW.EMP_STARTDATE,NEW.EMP_ENDDATE,NEW.EMP_REK,NEW.EMP_GENDER,NEW.EMP_SUFF,NEW.EMP_STATUS,NEW.ID_FK_TOKO,NEW.EMP_CREATE_DATE,NEW.EMP_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;

        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_EMPLOYEE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_EMPLOYEE
        AFTER UPDATE ON MSTR_EMPLOYEE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.EMP_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT ' , NEW.EMP_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_EMPLOYEE_LOG(EXECUTED_FUNCTION,ID_PK_EMPLOYEE,EMP_NAMA,EMP_NPWP,EMP_KTP,EMP_HP,EMP_ALAMAT,EMP_KODE_POS,EMP_FOTO_NPWP,EMP_FOTO_KTP,EMP_FOTO_LAIN,EMP_FOTO,EMP_GAJI,EMP_STARTDATE,EMP_ENDDATE,EMP_REK,EMP_GENDER,EMP_SUFF,EMP_STATUS,ID_FK_TOKO,EMP_CREATE_DATE,EMP_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_EMPLOYEE,NEW.EMP_NAMA,NEW.EMP_NPWP,NEW.EMP_KTP,NEW.EMP_HP,NEW.EMP_ALAMAT,NEW.EMP_KODE_POS,NEW.EMP_FOTO_NPWP,NEW.EMP_FOTO_KTP,NEW.EMP_FOTO_LAIN,NEW.EMP_FOTO,NEW.EMP_GAJI,NEW.EMP_STARTDATE,NEW.EMP_ENDDATE,NEW.EMP_REK,NEW.EMP_GENDER,NEW.EMP_SUFF,NEW.EMP_STATUS,NEW.ID_FK_TOKO,NEW.EMP_CREATE_DATE,NEW.EMP_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeQuery($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                id_pk_employee LIKE '%".$search_key."%' OR 
                emp_nama LIKE '%".$search_key."%' OR 
                emp_npwp LIKE '%".$search_key."%' OR 
                emp_ktp LIKE '%".$search_key."%' OR 
                emp_hp LIKE '%".$search_key."%' OR
                emp_alamat LIKE '%".$search_key."%' OR 
                emp_kode_pos LIKE '%".$search_key."%' OR 
                emp_foto_npwp LIKE '%".$search_key."%' OR 
                emp_foto_ktp LIKE '%".$search_key."%' OR 
                emp_foto_lain LIKE '%".$search_key."%' OR 
                emp_foto LIKE '%".$search_key."%' OR 
                emp_gaji LIKE '%".$search_key."%' OR 
                emp_startdate LIKE '%".$search_key."%' OR 
                emp_enddate LIKE '%".$search_key."%' OR 
                emp_rek LIKE '%".$search_key."%' OR 
                emp_gender LIKE '%".$search_key."%' OR 
                emp_suff LIKE '%".$search_key."%' OR 
                emp_status LIKE '%".$search_key."%'    
            )";
        }
        $query = "
        SELECT id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified
        FROM ".$this->tbl_name." 
        WHERE emp_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_employee
        FROM ".$this->tbl_name." 
        WHERE emp_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "emp_nama" => $this->emp_nama,
                "emp_npwp" => $this->emp_npwp,
                "emp_ktp" => $this->emp_ktp,
                "emp_hp" => $this->emp_hp,
                "emp_alamat" => $this->emp_alamat,
                "emp_kode_pos" => $this->emp_kode_pos,
                "emp_foto_npwp" => $this->emp_foto_npwp,
                "emp_foto_ktp" => $this->emp_foto_ktp,
                "emp_foto_lain" => $this->emp_foto_lain,
                "emp_foto" => $this->emp_foto,
                "emp_gaji" => $this->emp_gaji,
                "emp_startdate" => $this->emp_startdate,
                "emp_enddate" => $this->emp_enddate,
                "emp_rek" => $this->emp_rek,
                "emp_gender" => $this->emp_gender,
                "emp_suff" => $this->emp_suff,
                "emp_status" => $this->emp_status,
                "emp_create_date" => $this->emp_create_date,
                "emp_last_modified" => $this->emp_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
                "id_fk_toko" => $this->id_fk_toko,
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
                "id_pk_employee" => $this->id_pk_employee
            );
            $data = array(
                "emp_nama" => $this->emp_nama,
                "emp_npwp" => $this->emp_npwp,
                "emp_ktp" => $this->emp_ktp,
                "emp_hp" => $this->emp_hp,
                "emp_alamat" => $this->emp_alamat,
                "emp_kode_pos" => $this->emp_kode_pos,
                "emp_foto_npwp" => $this->emp_foto_npwp,
                "emp_foto_ktp" => $this->emp_foto_ktp,
                "emp_foto_lain" => $this->emp_foto_lain,
                "emp_foto" => $this->emp_foto,
                "emp_gaji" => $this->emp_gaji,
                "emp_startdate" => $this->emp_startdate,
                "emp_enddate" => $this->emp_enddate,
                "emp_rek" => $this->emp_rek,
                "emp_gender" => $this->emp_gender,
                "emp_suff" => $this->emp_suff,
                "emp_last_modified" => $this->emp_last_modified,
                "id_last_modified" => $this->id_last_modified,
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
                "id_pk_employee" => $this->id_pk_employee
            );
            $data = array(
                "emp_status" => "NONAKTIF",
                "emp_last_modified" => $this->emp_last_modified,
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
        if($this->emp_nama == ""){
            return false;
        }
        if($this->emp_npwp == ""){
            return false;
        }
        if($this->emp_ktp == ""){
            return false;
        }
        if($this->emp_hp == ""){
            return false;
        }
        if($this->emp_alamat == ""){
            return false;
        }
        if($this->emp_kode_pos == ""){
            return false;
        }
        if($this->emp_foto_npwp == ""){
            return false;
        }
        if($this->emp_foto_ktp == ""){
            return false;
        }
        if($this->emp_foto_lain == ""){
            return false;
        }
        if($this->emp_foto == ""){
            return false;
        }
        if($this->emp_gaji == ""){
            return false;
        }
        if($this->emp_startdate == ""){
            return false;
        }
        if($this->emp_enddate == ""){
            return false;
        }
        if($this->emp_rek == ""){
            return false;
        }
        if($this->emp_gender == ""){
            return false;
        }
        if($this->emp_suff == ""){
            return false;
        }
        if($this->emp_status == ""){
            return false;
        }
        if($this->emp_create_date == ""){
            return false;
        }
        if($this->emp_last_modified == ""){
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
        if($this->id_pk_employee == ""){
            return false;
        }
        if($this->emp_nama == ""){
            return false;
        }
        if($this->emp_npwp == ""){
            return false;
        }
        if($this->emp_ktp == ""){
            return false;
        }
        if($this->emp_hp == ""){
            return false;
        }
        if($this->emp_alamat == ""){
            return false;
        }
        if($this->emp_kode_pos == ""){
            return false;
        }
        if($this->emp_foto_npwp == ""){
            return false;
        }
        if($this->emp_foto_ktp == ""){
            return false;
        }
        if($this->emp_foto_lain == ""){
            return false;
        }
        if($this->emp_foto == ""){
            return false;
        }
        if($this->emp_gaji == ""){
            return false;
        }
        if($this->emp_startdate == ""){
            return false;
        }
        if($this->emp_enddate == ""){
            return false;
        }
        if($this->emp_rek == ""){
            return false;
        }
        if($this->emp_gender == ""){
            return false;
        }
        if($this->emp_suff == ""){
            return false;
        }
        if($this->emp_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_employee == ""){
            return false;
        }
        if($this->emp_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($emp_nama,$emp_npwp,$emp_ktp,$emp_hp,$emp_alamat,$emp_kode_pos,$emp_foto_npwp,$emp_foto_ktp,$emp_foto_lain,$emp_foto,$emp_gaji,$emp_startdate,$emp_enddate,$emp_rek,$emp_gender,$emp_suff,$emp_status,$id_fk_toko){
        if(!$this->set_emp_nama($emp_nama)){
            return false;
        }
        if(!$this->set_emp_npwp($emp_npwp)){
            return false;
        }
        if(!$this->set_emp_ktp($emp_ktp)){
            return false;
        }
        if(!$this->set_emp_hp($emp_hp)){
            return false;
        }
        if(!$this->set_emp_alamat($emp_alamat)){
            return false;
        }
        if(!$this->set_emp_kode_pos($emp_kode_pos)){
            return false;
        }
        if(!$this->set_emp_foto_npwp($emp_foto_npwp)){
            return false;
        }
        if(!$this->set_emp_foto_ktp($emp_foto_ktp)){
            return false;
        }
        if(!$this->set_emp_foto_lain($emp_foto_lain)){
            return false;
        }
        if(!$this->set_emp_foto($emp_foto)){
            return false;
        }
        if(!$this->set_emp_gaji($emp_gaji)){
            return false;
        }
        if(!$this->set_emp_startdate($emp_startdate)){
            return false;
        }
        if(!$this->set_emp_enddate($emp_enddate)){
            return false;
        }
        if(!$this->set_emp_rek($emp_rek)){
            return false;
        }
        if(!$this->set_emp_gender($emp_gender)){
            return false;
        }
        if(!$this->set_emp_suff($emp_suff)){
            return false;
        }
        if(!$this->set_emp_status($emp_status)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_employee,$emp_nama,$emp_npwp,$emp_ktp,$emp_hp,$emp_alamat,$emp_kode_pos,$emp_foto_npwp,$emp_foto_ktp,$emp_foto_lain,$emp_foto,$emp_gaji,$emp_startdate,$emp_enddate,$emp_rek,$emp_gender,$emp_suff){
        if(!$this->set_id_pk_employee($id_pk_employee)){
            return false;
        }
        if(!$this->set_emp_nama($emp_nama)){
            return false;
        }
        if(!$this->set_emp_npwp($emp_npwp)){
            return false;
        }
        if(!$this->set_emp_ktp($emp_ktp)){
            return false;
        }
        if(!$this->set_emp_hp($emp_hp)){
            return false;
        }
        if(!$this->set_emp_alamat($emp_alamat)){
            return false;
        }
        if(!$this->set_emp_kode_pos($emp_kode_pos)){
            return false;
        }
        if(!$this->set_emp_foto_npwp($emp_foto_npwp)){
            return false;
        }
        if(!$this->set_emp_foto_ktp($emp_foto_ktp)){
            return false;
        }
        if(!$this->set_emp_foto_lain($emp_foto_lain)){
            return false;
        }
        if(!$this->set_emp_foto($emp_foto)){
            return false;
        }
        if(!$this->set_emp_gaji($emp_gaji)){
            return false;
        }
        if(!$this->set_emp_startdate($emp_startdate)){
            return false;
        }
        if(!$this->set_emp_enddate($emp_enddate)){
            return false;
        }
        if(!$this->set_emp_rek($emp_rek)){
            return false;
        }
        if(!$this->set_emp_gender($emp_gender)){
            return false;
        }
        if(!$this->set_emp_suff($emp_suff)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_employee){
        if(!$this->set_id_pk_employee($id_pk_employee)){
            return false;
        }
        return true;
    }
    public function set_id_pk_employee($id_pk_employee){
        if($id_pk_employee != ""){
            $this->id_pk_employee = $id_pk_employee;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_nama($emp_nama){
        if($emp_nama != ""){
            $this->emp_nama = $emp_nama;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_npwp($emp_npwp){
        if($emp_npwp != ""){
            $this->emp_npwp = $emp_npwp;
        }
        else{
            $this->emp_npwp = "-";
            
        }
        return true;
    }
    public function set_emp_ktp($emp_ktp){
        if($emp_ktp != ""){
            $this->emp_ktp = $emp_ktp;
        }
        else{
            $this->emp_ktp = "-";
        }
        return true;
    }
    public function set_emp_hp($emp_hp){
        if($emp_hp != ""){
            $this->emp_hp = $emp_hp;
        }
        else{
            $this->emp_hp = "-";
        }
        return true;
    }
    public function set_emp_alamat($emp_alamat){
        if($emp_alamat != ""){
            $this->emp_alamat = $emp_alamat;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_kode_pos($emp_kode_pos){
        if($emp_kode_pos != ""){
            $this->emp_kode_pos = $emp_kode_pos;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_foto_npwp($emp_foto_npwp){
        if($emp_foto_npwp != ""){
            $this->emp_foto_npwp = $emp_foto_npwp;
        }
        else{
            $this->emp_foto_npwp = "-";
        }
        return true;
    }
    public function set_emp_foto_ktp($emp_foto_ktp){
        if($emp_foto_ktp != ""){
            $this->emp_foto_ktp = $emp_foto_ktp;
        }
        else{
            $this->emp_foto_ktp = "-";
        }
        return true;
    }
    public function set_emp_foto_lain($emp_foto_lain){
        if($emp_foto_lain != ""){
            $this->emp_foto_lain = $emp_foto_lain;
        }
        else{
            $this->emp_foto_lain = "-";
        }
        return true;
    }
    public function set_emp_foto($emp_foto){
        if($emp_foto != ""){
            $this->emp_foto = $emp_foto;
        }
        else{
            $this->emp_foto = "-";
        }
        return true;
    }
    public function set_emp_gaji($emp_gaji){
        if($emp_gaji != ""){
            $this->emp_gaji = $emp_gaji;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_startdate($emp_startdate){
        if($emp_startdate != ""){
            $this->emp_startdate = $emp_startdate;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_enddate($emp_enddate){
        if($emp_enddate != ""){
            $this->emp_enddate = $emp_enddate;
        }
        else{
            $this->emp_enddate = "0000-00-00 00:00:00";
        }
        return true;
    }
    public function set_emp_rek($emp_rek){
        if($emp_rek != ""){
            $this->emp_rek = $emp_rek;
        }
        else{
            $this->emp_rek = "-";
        }
        return true;
    }
    public function set_emp_gender($emp_gender){
        if($emp_gender != ""){
            $this->emp_gender = $emp_gender;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_suff($emp_suff){
        if($emp_suff != ""){
            $this->emp_suff = $emp_suff;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_emp_status($emp_status){
        if($emp_status != ""){
            $this->emp_status = $emp_status;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_fk_toko($id_fk_toko){
        if($id_fk_toko != ""){
            $this->id_fk_toko = $id_fk_toko;
            return true;
        }
        else{
            return false;
        }
    }
    public function get_id_pk_employee(){
        return $this->id_pk_employee;
    }
    public function get_emp_nama(){
        return $this->emp_nama;
    }
    public function get_emp_npwp(){
        return $this->emp_npwp;
    }
    public function get_emp_ktp(){
        return $this->emp_ktp;
    }
    public function get_emp_hp(){
        return $this->emp_hp;
    }
    public function get_emp_alamat(){
        return $this->emp_alamat;
    }
    public function get_emp_kode_pos(){
        return $this->emp_kode_pos;
    }
    public function get_emp_foto_npwp(){
        return $this->emp_foto_npwp;
    }
    public function get_emp_foto_ktp(){
        return $this->emp_foto_ktp;
    }
    public function get_emp_foto_lain(){
        return $this->emp_foto_lain;
    }
    public function get_emp_foto(){
        return $this->emp_foto;
    }
    public function get_emp_gaji(){
        return $this->emp_gaji;
    }
    public function get_emp_startdate(){
        return $this->emp_startdate;
    }
    public function get_emp_enddate(){
        return $this->emp_enddate;
    }
    public function get_emp_rek(){
        return $this->emp_rek;
    }
    public function get_emp_gender(){
        return $this->emp_gender;
    }
    public function get_emp_suff(){
        return $this->emp_suff;
    }
    public function get_emp_status(){
        return $this->emp_status;
    }
    public function get_id_fk_toko(){
        return $this->id_fk_toko;
    }
}