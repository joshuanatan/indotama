<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_supplier extends ci_model{
    private $tbl_name = "mstr_supplier";
    private $columns = array();
    private $id_pk_sup;
    private $sup_nama;

    private $sup_no_npwp;
    private $sup_foto_npwp;
    private $sup_foto_kartu_nama;
    private $sup_badan_usaha;
    private $sup_no_rekening;

    private $sup_suff;
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
        $this->set_column("sup_nama","pic",true);
        $this->set_column("sup_perusahaan","supplier",false);
        $this->set_column("sup_email","email",false);
        $this->set_column("sup_telp","no telp",false);
        $this->set_column("sup_hp","no hp",false);
        $this->set_column("sup_alamat","alamat",false);
        $this->set_column("sup_keterangan","keterangan",false);
        $this->set_column("sup_status","status",false);
        $this->set_column("sup_last_modified","last modified",false);
        $this->sup_create_date = date("y-m-d h:i:s"); 
        $this->sup_last_modified = date("y-m-d h:i:s"); 
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
        drop table if exists mstr_supplier;
        create table mstr_supplier(
            id_pk_sup int primary key auto_increment,
            sup_nama varchar(100),
            sup_no_npwp varchar(100),
            sup_foto_npwp varchar(100),
            sup_foto_kartu_nama varchar(100),
            sup_badan_usaha varchar(100),
            sup_no_rekening varchar(100),
            sup_suff varchar(10),
            sup_perusahaan varchar(100),
            sup_email varchar(100),
            sup_telp varchar(30),
            sup_hp varchar(30),
            sup_alamat varchar(150),
            sup_keterangan varchar(150),
            sup_status varchar(15),
            sup_create_date datetime,
            sup_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_supplier_log;
        create table mstr_supplier_log(
            id_pk_sup_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_sup int,
            sup_nama varchar(100),
            sup_no_npwp varchar(100),
            sup_foto_npwp varchar(100),
            sup_foto_kartu_nama varchar(100),
            sup_badan_usaha varchar(100),
            sup_no_rekening varchar(100),
            sup_suff varchar(10),
            sup_perusahaan varchar(100),
            sup_email varchar(100),
            sup_telp varchar(30),
            sup_hp varchar(30),
            sup_alamat varchar(150),
            sup_keterangan varchar(150),
            sup_status varchar(15),
            sup_create_date datetime,
            sup_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_supplier;
        delimiter $$
        create trigger trg_after_insert_supplier
        after insert on mstr_supplier
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sup_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.sup_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_supplier_log(executed_function,id_pk_sup,sup_nama,sup_no_npwp,sup_foto_npwp,sup_foto_kartu_nama,sup_badan_usaha,sup_no_rekening,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_create_date,sup_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_sup,new.sup_nama,new.sup_no_npwp,new.sup_foto_npwp,new.sup_foto_kartu_nama,new.sup_badan_usaha,new.sup_no_rekening,new.sup_suff,new.sup_perusahaan,new.sup_email,new.sup_telp,new.sup_hp,new.sup_alamat,new.sup_keterangan,new.sup_status,new.sup_create_date,new.sup_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_supplier;
        delimiter $$
        create trigger trg_after_update_supplier
        after update on mstr_supplier
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sup_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.sup_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_supplier_log(executed_function,id_pk_sup,sup_nama,sup_no_npwp,sup_foto_npwp,sup_foto_kartu_nama,sup_badan_usaha,sup_no_rekening,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_create_date,sup_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_sup,new.sup_nama,new.sup_no_npwp,new.sup_foto_npwp,new.sup_foto_kartu_nama,new.sup_badan_usaha,new.sup_no_rekening,new.sup_suff,new.sup_perusahaan,new.sup_email,new.sup_telp,new.sup_hp,new.sup_alamat,new.sup_keterangan,new.sup_status,new.sup_create_date,new.sup_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                sup_nama like '%".$search_key."%' or
                sup_no_npwp like '%".$search_key."%' or
                sup_foto_npwp like '%".$search_key."%' or
                sup_foto_kartu_nama like '%".$search_key."%' or
                sup_badan_usaha like '%".$search_key."%' or
                sup_no_rekening like '%".$search_key."%' or
                sup_perusahaan like '%".$search_key."%' or
                sup_email like '%".$search_key."%' or
                sup_telp like '%".$search_key."%' or
                sup_hp like '%".$search_key."%' or
                sup_alamat like '%".$search_key."%' or
                sup_keterangan like '%".$search_key."%' or
                sup_status like '%".$search_key."%' or
                sup_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_sup,sup_nama,sup_no_npwp,sup_foto_npwp,sup_foto_kartu_nama,sup_badan_usaha,sup_no_rekening,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_last_modified
        from ".$this->tbl_name." 
        where sup_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_sup
        from ".$this->tbl_name." 
        where sup_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list_data(){
        $sql = "select id_pk_sup,sup_nama,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_last_modified
        from ".$this->tbl_name." 
        where sup_status = ?  
        order by sup_perusahaan asc";
        $args = array(
            "aktif"
        );
        return executequery($sql,$args);
    }
    public function detail_by_perusahaan(){
        $where = array(
            "sup_perusahaan" => $this->sup_perusahaan,
            "sup_status" => "aktif"
        );
        $field = array(
            "id_pk_sup","sup_nama","sup_no_npwp","sup_foto_npwp","sup_foto_kartu_nama","sup_badan_usaha","sup_no_rekening","sup_suff","sup_perusahaan","sup_email","sup_telp","sup_hp","sup_alamat","sup_keterangan","sup_status","sup_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "sup_nama" => $this->sup_nama,
                "sup_no_npwp" => $this->sup_no_npwp,
                "sup_foto_npwp" => $this->sup_foto_npwp,
                "sup_foto_kartu_nama" => $this->sup_foto_kartu_nama,
                "sup_badan_usaha" => $this->sup_badan_usaha,
                "sup_no_rekening" => $this->sup_no_rekening,
                "sup_suff" => $this->sup_suff,
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
            $id_hasil_insert = insertrow($this->tbl_name, $data);

            $log_all_msg = "Data Supplier baru ditambahkan. Waktu penambahan: $this->sup_create_date";
            $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

            $log_all_data_changes = "[ID Supplier: $id_hasil_insert][Nama: $this->sup_nama][NPWP: $this->sup_no_npwp][Foto NPWP: $this->sup_foto_npwp][Foto Kartu Nama: $this->sup_foto_kartu_nama][Badan Usaha: $this->sup_badan_usaha][Rekening: $this->sup_no_rekening][Panggilan: $this->sup_suff][Perusahaan: $this->sup_perusahaan][Email: $this->sup_email][Telepon: $this->sup_telp][No HP: $this->sup_hp][Alamat: $this->sup_alamat][Keterangan: $this->sup_keterangan][Status: $this->sup_status][Waktu Ditambahkan: $this->sup_create_date][Oleh: $nama_user]";
            $log_all_it = "";
            $log_all_user = $this->id_create_data;
            $log_all_tgl = $this->sup_create_date;

            $data_log = array(
                "log_all_msg" => $log_all_msg,
                "log_all_data_changes" => $log_all_data_changes,
                "log_all_it" => $log_all_it,
                "log_all_user" => $log_all_user,
                "log_all_tgl" => $log_all_tgl
            );
            insertrow("log_all", $data_log);

            return $id_hasil_insert;
        }
        return false;
    }
    public function short_insert(){
        $data = array(
            "sup_perusahaan" => $this->sup_perusahaan,
            "sup_status" => "aktif",
            "sup_create_date" => $this->sup_create_date,
            "sup_last_modified" => $this->sup_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified
        );
        return insertrow($this->tbl_name,$data);
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_sup" => $this->id_pk_sup
            );
            $data = array(
                "sup_nama" => $this->sup_nama,
                "sup_no_npwp" => $this->sup_no_npwp,
                "sup_foto_npwp" => $this->sup_foto_npwp,
                "sup_foto_kartu_nama" => $this->sup_foto_kartu_nama,
                "sup_badan_usaha" => $this->sup_badan_usaha,
                "sup_no_rekening" => $this->sup_no_rekening,
                "sup_suff" => $this->sup_suff,
                "sup_perusahaan" => $this->sup_perusahaan,
                "sup_email" => $this->sup_email,
                "sup_telp" => $this->sup_telp,
                "sup_hp" => $this->sup_hp,
                "sup_alamat" => $this->sup_alamat,
                "sup_keterangan" => $this->sup_keterangan,
                "sup_last_modified" => $this->sup_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name, $data, $where);
        $id_pk = $this->id_pk_sup;
        $log_all_msg = "Data Supplier dengan ID: $id_pk diubah. Waktu diubah: $this->sup_last_modified . Data berubah menjadi: ";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

        $log_all_data_changes = "[ID Supplier: $id_pk][Nama: $this->sup_nama][NPWP: $this->sup_no_npwp][Foto NPWP: $this->sup_foto_npwp][Foto Kartu Nama: $this->sup_foto_kartu_nama][Badan Usaha: $this->sup_badan_usaha][Rekening: $this->sup_no_rekening][Panggilan: $this->sup_suff][Perusahaan: $this->sup_perusahaan][Email: $this->sup_email][Telepon: $this->sup_telp][No HP: $this->sup_hp][Alamat: $this->sup_alamat][Keterangan: $this->sup_keterangan][Waktu Diedit: $this->sup_create_date][Oleh: $nama_user]";
        $log_all_it = "";
        $log_all_user = $this->id_last_modified;
        $log_all_tgl = $this->sup_last_modified;

        $data_log = array(
          "log_all_msg" => $log_all_msg,
          "log_all_data_changes" => $log_all_data_changes,
          "log_all_it" => $log_all_it,
          "log_all_user" => $log_all_user,
          "log_all_tgl" => $log_all_tgl
        );
        insertrow("log_all", $data_log);
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
                "sup_status" => "nonaktif",
                "sup_last_modified" => $this->sup_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->sup_nama == ""){
            return false;
        }
        if($this->sup_no_npwp == ""){
            return false;
        }
        if($this->sup_foto_npwp == ""){
            return false;
        }
        if($this->sup_foto_kartu_nama == ""){
            return false;
        }
        if($this->sup_badan_usaha == ""){
            return false;
        }
        if($this->sup_no_rekening == ""){
            return false;
        }
        if($this->sup_suff == ""){
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
        if($this->sup_no_npwp == ""){
            return false;
        }
        if($this->sup_foto_npwp == ""){
            return false;
        }
        if($this->sup_foto_kartu_nama == ""){
            return false;
        }
        if($this->sup_badan_usaha == ""){
            return false;
        }
        if($this->sup_no_rekening == ""){
            return false;
        }
        if($this->sup_suff == ""){
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
    public function set_insert($sup_nama,$sup_suff,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan,$sup_status,$sup_no_npwp,$sup_foto_npwp,$sup_foto_kartu_nama,$sup_badan_usaha,$sup_no_rekening){
        if(!$this->set_sup_nama($sup_nama)){
            return false;
        }
        if(!$this->set_sup_no_npwp($sup_no_npwp)){
            return false;
        }
        if(!$this->set_sup_foto_npwp($sup_foto_npwp)){
            return false;
        }
        if(!$this->set_sup_foto_kartu_nama($sup_foto_kartu_nama)){
            return false;
        }
        if(!$this->set_sup_badan_usaha($sup_badan_usaha)){
            return false;
        }
        if(!$this->set_sup_no_rekening($sup_no_rekening)){
            return false;
        }
        if(!$this->set_sup_suff($sup_suff)){
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
    public function set_update($id_pk_sup,$sup_nama,$sup_suff,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan,$sup_no_npwp,$sup_foto_npwp,$sup_foto_kartu_nama,$sup_badan_usaha,$sup_no_rekening){
        if(!$this->set_id_pk_sup($id_pk_sup)){
            return false;
        }
        if(!$this->set_sup_nama($sup_nama)){
            return false;
        }
        if(!$this->set_sup_no_npwp($sup_no_npwp)){
            return false;
        }
        if(!$this->set_sup_foto_npwp($sup_foto_npwp)){
            return false;
        }
        if(!$this->set_sup_foto_kartu_nama($sup_foto_kartu_nama)){
            return false;
        }
        if(!$this->set_sup_badan_usaha($sup_badan_usaha)){
            return false;
        }
        if(!$this->set_sup_no_rekening($sup_no_rekening)){
            return false;
        }
        if(!$this->set_sup_suff($sup_suff)){
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
    public function set_sup_no_npwp($sup_no_npwp){
        if($sup_no_npwp != ""){
            $this->sup_no_npwp = $sup_no_npwp;
            return true;
        }
        return false;
    }
    public function set_sup_foto_npwp($sup_foto_npwp){
        if($sup_foto_npwp != ""){
            $this->sup_foto_npwp = $sup_foto_npwp;
            return true;
        }
        return false;
    }
    public function set_sup_foto_kartu_nama($sup_foto_kartu_nama){
        if($sup_foto_kartu_nama != ""){
            $this->sup_foto_kartu_nama = $sup_foto_kartu_nama;
            return true;
        }
        return false;
    }
    public function set_sup_badan_usaha($sup_badan_usaha){
        if($sup_badan_usaha != ""){
            $this->sup_badan_usaha = $sup_badan_usaha;
            return true;
        }
        return false;
    }
    public function set_sup_no_rekening($sup_no_rekening){
        if($sup_no_rekening != ""){
            $this->sup_no_rekening = $sup_no_rekening;
            return true;
        }
        return false;
    }
    public function set_sup_suff($sup_suff){
        if($sup_suff != ""){
            $this->sup_suff = $sup_suff;
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
    public function data_excel(){
        $sql = "select id_pk_sup,sup_nama,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_last_modified
        from ".$this->tbl_name." 
        where sup_status = ?  
        order by sup_perusahaan asc";
        $args = array(
            "aktif"
        );
        return executequery($sql,$args);
    }
    public function columns_excel(){
        $this->columns = array();
        $this->set_column("sup_nama","pic",true);
        $this->set_column("sup_perusahaan","supplier",false);
        $this->set_column("sup_email","email",false);
        $this->set_column("sup_telp","no telp",false);
        $this->set_column("sup_hp","no hp",false);
        $this->set_column("sup_alamat","alamat",false);
        $this->set_column("sup_keterangan","keterangan",false);
        $this->set_column("sup_status","status",false);
        $this->set_column("sup_last_modified","last modified",false);
        return $this->columns;
    }
}
?>