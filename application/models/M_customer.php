<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_customer extends ci_model{
    private $tbl_name = "mstr_customer";
    private $columns = array();
    private $id_pk_cust;
    private $cust_name;

    private $cust_no_npwp;
    private $cust_foto_npwp;
    private $cust_foto_kartu_nama;
    private $cust_badan_usaha;
    private $cust_no_rekening;

    private $cust_suff;
    private $cust_sapaan;
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
        $this->set_column("cust_name","name",true);
        $this->set_column("cust_perusahaan","perusahaan",false);
        $this->set_column("cust_email","email",false);
        $this->set_column("cust_telp","telp",false);
        $this->set_column("cust_hp","hp",false);
        $this->set_column("cust_alamat","alamat",false);
        $this->set_column("cust_keterangan","keterangan",false);
        $this->set_column("cust_status","status",false);
        $this->set_column("cust_last_modified","last modified",false);
        $this->cust_create_date = date("y-m-d h:i:s");
        $this->cust_last_modified = date("y-m-d h:i:s");
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
        drop table if exists mstr_customer;
        create table mstr_customer(
            id_pk_cust int primary key auto_increment,
            cust_name varchar(100),
            cust_no_npwp varchar(100),
            cust_foto_npwp varchar(100),
            cust_foto_kartu_nama varchar(100),
            cust_badan_usaha varchar(100),
            cust_no_rekening varchar(100),
            cust_suff varchar(10),
            cust_perusahaan varchar(100),
            cust_email varchar(100),
            cust_telp varchar(30),
            cust_hp varchar(30),
            cust_alamat varchar(150),
            cust_keterangan varchar(150),
            id_fk_toko int,
            cust_status varchar(15),
            cust_create_date datetime,
            cust_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_customer_log;
        create table mstr_customer_log(
            id_pk_cust_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_cust int,
            cust_name varchar(100),
            cust_no_npwp varchar(100),
            cust_foto_npwp varchar(100),
            cust_foto_kartu_nama varchar(100),
            cust_badan_usaha varchar(100),
            cust_no_rekening varchar(100),
            cust_suff varchar(10),
            cust_perusahaan varchar(100),
            cust_email varchar(100),
            cust_telp varchar(30),
            cust_hp varchar(30),
            cust_alamat varchar(150),
            cust_keterangan varchar(150),
            id_fk_toko int,
            cust_status varchar(15),
            cust_create_date datetime,
            cust_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_customer;
        delimiter $$
        create trigger trg_after_insert_customer
        after insert on mstr_customer
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_cust,new.cust_name,new.cust_no_npwp,new.cust_foto_npwp,new.cust_foto_kartu_nama,new.cust_badan_usaha,new.cust_no_rekening,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_customer;
        delimiter $$
        create trigger trg_after_update_customer
        after update on mstr_customer
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_cust,new.cust_name,new.cust_no_npwp,new.cust_foto_npwp,new.cust_foto_kartu_nama,new.cust_badan_usaha,new.cust_no_rekening,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_cust like '%".$search_key."%' or
                cust_name like '%".$search_key."%' or
                cust_perusahaan like '%".$search_key."%' or
                cust_email like '%".$search_key."%' or
                cust_telp like '%".$search_key."%' or
                cust_hp like '%".$search_key."%' or
                cust_alamat like '%".$search_key."%' or
                cust_keterangan like '%".$search_key."%' or
                cust_status like '%".$search_key."%' or
                cust_no_npwp like '%".$search_key."%' or
                cust_foto_npwp like '%".$search_key."%' or
                cust_foto_kartu_nama like '%".$search_key."%' or
                cust_badan_usaha like '%".$search_key."%' or
                cust_no_rekening like '%".$search_key."%' or
                cust_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_cust,cust_name,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_last_modified,cust_status
        from ".$this->tbl_name." 
        where cust_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_cust
        from ".$this->tbl_name." 
        where cust_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list_data(){
        $where = array(
            "cust_status" => "aktif"
        );
        $field = array(
            "id_pk_cust",
            "cust_name",
            "cust_suff",
            "cust_perusahaan",
            "cust_email",
            "cust_telp",
            "cust_hp",
            "cust_alamat",
            "cust_keterangan",
            "cust_no_npwp",
            "cust_foto_npwp",
            "cust_foto_kartu_nama",
            "cust_badan_usaha",
            "cust_no_rekening",
            "cust_last_modified",
            "cust_status"  
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function detail_by_perusahaan(){
        $where = array(
            "cust_perusahaan" => $this->cust_perusahaan,
            "cust_status" => "aktif"
        );
        $field = array(
            "id_pk_cust",
            "cust_name",
            "cust_no_npwp",
            "cust_foto_npwp",
            "cust_foto_kartu_nama",
            "cust_badan_usaha",
            "cust_no_rekening",
            "cust_suff",
            "cust_perusahaan",
            "cust_email",
            "cust_telp",
            "cust_hp",
            "cust_alamat",
            "cust_keterangan",
            "cust_status",
            "cust_create_date",
            "cust_last_modified",
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function short_insert(){
        $data = array(
            "cust_perusahaan" => $this->cust_perusahaan,
            "cust_status" => "aktif",
            "cust_create_date" => $this->cust_create_date,
            "cust_last_modified" => $this->cust_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified
        );
        return insertRow($this->tbl_name,$data);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "cust_name" => $this->cust_name,
                "cust_no_npwp" => $this->cust_no_npwp,
                "cust_foto_npwp" => $this->cust_foto_npwp,
                "cust_foto_kartu_nama" => $this->cust_foto_kartu_nama,
                "cust_badan_usaha" => $this->cust_badan_usaha,
                "cust_no_rekening" => $this->cust_no_rekening,
                "cust_suff" => $this->cust_suff,
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
                "cust_no_npwp" => $this->cust_no_npwp,
                "cust_foto_npwp" => $this->cust_foto_npwp,
                "cust_foto_kartu_nama" => $this->cust_foto_kartu_nama,
                "cust_badan_usaha" => $this->cust_badan_usaha,
                "cust_no_rekening" => $this->cust_no_rekening,
                "cust_suff" => $this->cust_suff,
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
                "cust_status" => "nonaktif",
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
        if($this->cust_no_npwp == ""){
            return false;
        }
        if($this->cust_foto_npwp == ""){
            return false;
        }
        if($this->cust_foto_kartu_nama == ""){
            return false;
        }
        if($this->cust_badan_usaha == ""){
            return false;
        }
        if($this->cust_no_rekening == ""){
            return false;
        }
        if($this->cust_suff == ""){
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
        if($this->cust_no_npwp == ""){
            return false;
        }
        if($this->cust_foto_npwp == ""){
            return false;
        }
        if($this->cust_foto_kartu_nama == ""){
            return false;
        }
        if($this->cust_badan_usaha == ""){
            return false;
        }
        if($this->cust_no_rekening == ""){
            return false;
        }
        if($this->cust_suff == ""){
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
    public function set_insert($cust_name,$cust_suff,$cust_perusahaan,$cust_email,$cust_telp,$cust_hp,$cust_alamat,$cust_keterangan,$cust_status,$cust_no_npwp,$cust_foto_npwp,$cust_foto_kartu_nama,$cust_badan_usaha,$cust_no_rekening){
        if(!$this->set_cust_name($cust_name)){
            return false;
        }
        if(!$this->set_cust_no_npwp($cust_no_npwp)){
            return false;
        }
        if(!$this->set_cust_foto_npwp($cust_foto_npwp)){
            return false;
        }
        if(!$this->set_cust_foto_kartu_nama($cust_foto_kartu_nama)){
            return false;
        }
        if(!$this->set_cust_badan_usaha($cust_badan_usaha)){
            return false;
        }
        if(!$this->set_cust_no_rekening($cust_no_rekening)){
            return false;
        }
        if(!$this->set_cust_suff($cust_suff)){
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
    public function set_update($id_pk_cust,$cust_name,$cust_suff,$cust_perusahaan,$cust_email,$cust_telp,$cust_hp,$cust_alamat,$cust_keterangan,$cust_no_npwp,$cust_foto_npwp,$cust_foto_kartu_nama,$cust_badan_usaha,$cust_no_rekening){
        if(!$this->set_id_pk_cust($id_pk_cust)){
            return false;
        }
        if(!$this->set_cust_name($cust_name)){
            return false;
        }
        if(!$this->set_cust_no_npwp($cust_no_npwp)){
            return false;
        }
        if(!$this->set_cust_foto_npwp($cust_foto_npwp)){
            return false;
        }
        if(!$this->set_cust_foto_kartu_nama($cust_foto_kartu_nama)){
            return false;
        }
        if(!$this->set_cust_badan_usaha($cust_badan_usaha)){
            return false;
        }
        if(!$this->set_cust_no_rekening($cust_no_rekening)){
            return false;
        }
        if(!$this->set_cust_suff($cust_suff)){
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
    public function set_cust_no_npwp($cust_no_npwp){
        if($cust_no_npwp != ""){
            $this->cust_no_npwp = $cust_no_npwp;
            return true;
        }
        return false;
    }
    public function set_cust_foto_npwp($cust_foto_npwp){
        if($cust_foto_npwp != ""){
            $this->cust_foto_npwp = $cust_foto_npwp;
            return true;
        }
        return false;
    }
    public function set_cust_foto_kartu_nama($cust_foto_kartu_nama){
        if($cust_foto_kartu_nama != ""){
            $this->cust_foto_kartu_nama = $cust_foto_kartu_nama;
            return true;
        }
        return false;
    }
    public function set_cust_badan_usaha($cust_badan_usaha){
        if($cust_badan_usaha != ""){
            $this->cust_badan_usaha = $cust_badan_usaha;
            return true;
        }
        return false;
    }
    public function set_cust_no_rekening($cust_no_rekening){
        if($cust_no_rekening != ""){
            $this->cust_no_rekening = $cust_no_rekening;
            return true;
        }
        return false;
    }
    public function set_cust_suff($cust_suff){
        if($cust_suff != ""){
            $this->cust_suff = $cust_suff;
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
    public function data_excel(){
        $where = array(
            "cust_status" => "aktif"
        );
        $field = array(
            "id_pk_cust",
            "cust_name",
            "cust_suff",
            "cust_perusahaan",
            "cust_email",
            "cust_telp",
            "cust_hp",
            "cust_alamat",
            "cust_keterangan",
            "cust_no_npwp",
            "cust_foto_npwp",
            "cust_foto_kartu_nama",
            "cust_badan_usaha",
            "cust_no_rekening",
            "cust_last_modified",
            "cust_status"  
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function columns_excel(){
        $this->columns = array();
        $this->set_column("cust_name","name",true);
        $this->set_column("cust_perusahaan","perusahaan",false);
        $this->set_column("cust_email","email",false);
        $this->set_column("cust_telp","telp",false);
        $this->set_column("cust_hp","hp",false);
        $this->set_column("cust_alamat","alamat",false);
        $this->set_column("cust_keterangan","keterangan",false);
        $this->set_column("cust_status","status",false);
        $this->set_column("cust_last_modified","last modified",false);
        return $this->columns;
    }
}