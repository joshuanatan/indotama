<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_employee extends ci_model{
    private $columns = array();
    private $tbl_name = "mstr_employee";
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
    private $emp_create_date;
    private $emp_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->set_column("emp_foto","foto","required");
        $this->set_column("emp_nama","nama","required");
        $this->set_column("emp_ktp","ktp","required");
        $this->set_column("emp_npwp","npwp","required");
        $this->set_column("emp_hp","hp","required");
        $this->set_column("emp_alamat","alamat","required");
        $this->set_column("emp_gender","Jenis Kelamin","required");
        $this->set_column("emp_status","Status","required");
        $this->set_column("emp_last_modified","last modified","required");
        
        $this->emp_create_date = date("y-m-d h:i:s");
        $this->emp_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists mstr_employee;
        create table mstr_employee(
            id_pk_employee int primary key auto_increment,
            emp_nama varchar(400),
            emp_npwp varchar(25),
            emp_ktp varchar(20),
            emp_hp varchar(15),
            emp_alamat varchar(300),
            emp_kode_pos varchar(10),
            emp_foto_npwp varchar(50),
            emp_foto_ktp varchar(50),
            emp_foto_lain varchar(50),
            emp_foto varchar(50),
            emp_gaji int,
            emp_startdate datetime,
            emp_enddate datetime,
            emp_rek varchar(30),
            emp_gender varchar(6),
            emp_suff varchar(10),
            emp_status varchar(15),
            emp_create_date datetime,
            emp_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_employee_log;
        create table mstr_employee_log(
            id_pk_employee_log int primary key auto_increment,
            executed_function varchar(40),
            id_pk_employee int,
            emp_nama varchar(400),
            emp_npwp varchar(25),
            emp_ktp varchar(20),
            emp_hp varchar(15),
            emp_alamat varchar(300),
            emp_kode_pos varchar(10),
            emp_foto_npwp varchar(50),
            emp_foto_ktp varchar(50),
            emp_foto_lain varchar(50),
            emp_foto varchar(50),
            emp_gaji int,
            emp_startdate datetime,
            emp_enddate datetime,
            emp_rek varchar(30),
            emp_gender varchar(6),
            emp_suff varchar(10),
            emp_status varchar(15),
            emp_create_date datetime,
            emp_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_employee;
        delimiter $$
        create trigger trg_after_insert_employee
        after insert on mstr_employee
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_employee;
        delimiter $$
        create trigger trg_after_update_employee
        after update on mstr_employee
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
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
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_employee like '%".$search_key."%' or 
                emp_nama like '%".$search_key."%' or 
                emp_npwp like '%".$search_key."%' or 
                emp_ktp like '%".$search_key."%' or 
                emp_hp like '%".$search_key."%' or
                emp_alamat like '%".$search_key."%' or 
                emp_kode_pos like '%".$search_key."%' or 
                emp_foto_npwp like '%".$search_key."%' or 
                emp_foto_ktp like '%".$search_key."%' or 
                emp_foto_lain like '%".$search_key."%' or 
                emp_foto like '%".$search_key."%' or 
                emp_gaji like '%".$search_key."%' or 
                emp_startdate like '%".$search_key."%' or 
                emp_enddate like '%".$search_key."%' or 
                emp_rek like '%".$search_key."%' or 
                emp_gender like '%".$search_key."%' or 
                emp_suff like '%".$search_key."%' or 
                emp_status like '%".$search_key."%' or
                emp_last_modified like '%".$search_key."%'    
            )";
        }
        $query = "
        select id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified
        from ".$this->tbl_name."  
        where emp_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_employee
        from ".$this->tbl_name." 
        where emp_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list_employee(){
        $sql = "select id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified
        from ".$this->tbl_name." 
        where emp_status = ? ";
        $args = array(
            "aktif"
        );
        return executeQuery($sql,$args);
    }
    public function short_insert(){
        $data = array(
            "emp_nama" => $this->emp_nama,
            "emp_status" => "AKTIF",
            "emp_create_date" => $this->emp_create_date,
            "emp_last_modified" => $this->emp_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified,
        );
        return insertRow($this->tbl_name,$data);
    }
    public function detail_by_name(){
        $where = array(
            "emp_nama" => $this->emp_nama
        );
        $field = array(
            "id_pk_employee",
            "emp_nama",
            "emp_npwp",
            "emp_ktp",
            "emp_hp",
            "emp_alamat",
            "emp_kode_pos",
            "emp_foto_npwp",
            "emp_foto_ktp",
            "emp_foto_lain",
            "emp_foto",
            "emp_gaji",
            "emp_startdate",
            "emp_enddate",
            "emp_rek",
            "emp_gender",
            "emp_suff",
            "emp_status",
            "emp_create_date",
            "emp_last_modified",
        );
        return selectRow($this->tbl_name,$where,$field);
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
            );
            return insertrow($this->tbl_name,$data);
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
            updaterow($this->tbl_name,$data,$where);
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
                "emp_status" => "nonaktif",
                "emp_last_modified" => $this->emp_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
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
    public function set_insert($emp_nama,$emp_npwp,$emp_ktp,$emp_hp,$emp_alamat,$emp_kode_pos,$emp_foto_npwp,$emp_foto_ktp,$emp_foto_lain,$emp_foto,$emp_gaji,$emp_startdate,$emp_enddate,$emp_rek,$emp_gender,$emp_suff,$emp_status){
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
    public function data_excel(){
        $sql = "select id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified
        from ".$this->tbl_name." 
        where emp_status = ? ";
        $args = array(
            "aktif"
        );
        return executeQuery($sql,$args);
    }
    public function columns_excel(){
        $this->columns = array();
        $this->set_column("emp_nama","Nama",true);
        $this->set_column("emp_npwp","NPWP",true);
        $this->set_column("emp_ktp","KTP",true);
        $this->set_column("emp_hp","HP",true);
        $this->set_column("emp_alamat","Alamat",true);
        $this->set_column("emp_kode_pos","Kode Pos",true);
        $this->set_column("emp_gaji","Gaji",true);
        $this->set_column("emp_startdate","Start Date",true);
        $this->set_column("emp_enddate","End Date",true);
        $this->set_column("emp_rek","Rekening",true);
        $this->set_column("emp_gender","Jenis Kelamin",true);
        $this->set_column("emp_status","Status",true);
        return $this->columns;
    }
}