<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_cabang extends ci_model{
    private $tbl_name = "mstr_cabang";
    private $columns = array();
    private $id_pk_cabang;
    private $cabang_daerah;
    
    private $cabang_kop_surat;
    private $cabang_nonpkp;
    private $cabang_pernyataan_rek;

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
        $this->set_column("cabang_daerah","daerah",true);
        $this->set_column("cabang_notelp","no telp",false);
        $this->set_column("cabang_alamat","alamat",false);
        $this->set_column("cabang_status","status",false);
        $this->set_column("cabang_last_modified","last modified",false);

        $this->cabang_create_date = date("y-m-d h:i:s");
        $this->cabang_last_modified = date("y-m-d h:i:s");
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
        $sql = "drop table if exists mstr_cabang;
        create table mstr_cabang(
            id_pk_cabang int primary key auto_increment,
            cabang_daerah varchar(50),
            cabang_kop_surat varchar(100),
            cabang_nonpkp varchar(100),
            cabang_pernyataan_rek varchar(100),
            cabang_notelp varchar(30),
            cabang_alamat varchar(100),
            cabang_status varchar(15),
            cabang_create_date datetime,
            cabang_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_fk_toko int
        );
        drop table if exists mstr_cabang_log;
        create table mstr_cabang_log(
            id_pk_cabang_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_cabang int,
            cabang_daerah varchar(50),
            cabang_kop_surat varchar(100),
            cabang_nonpkp varchar(100),
            cabang_pernyataan_rek varchar(100),
            cabang_notelp varchar(30),
            cabang_alamat varchar(100),
            cabang_status varchar(15),
            cabang_create_date datetime,
            cabang_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_fk_toko int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_cabang;
        delimiter $$
        create trigger trg_after_insert_cabang
        after insert on mstr_cabang
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_cabang_log(executed_function,id_pk_cabang,cabang_daerah,cabang_kop_surat,cabang_nonpkp,cabang_pernyataan_rek,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_create_data,id_last_modified,id_fk_toko,id_log_all) values ('after insert',new.id_pk_cabang,new.cabang_daerah,new.cabang_kop_surat,new.cabang_nonpkp,new.cabang_pernyataan_rek,new.cabang_notelp,new.cabang_alamat,new.cabang_status,new.cabang_create_date,new.cabang_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_toko,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_cabang;
        delimiter $$
        create trigger trg_after_update_cabang
        after update on mstr_cabang
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_cabang_log(executed_function,id_pk_cabang,cabang_daerah,cabang_kop_surat,cabang_nonpkp,cabang_pernyataan_rek,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_create_data,id_last_modified,id_fk_toko,id_log_all) values ('after update',new.id_pk_cabang,new.cabang_daerah,new.cabang_kop_surat,new.cabang_nonpkp,new.cabang_pernyataan_rek,new.cabang_notelp,new.cabang_alamat,new.cabang_status,new.cabang_create_date,new.cabang_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_toko,@id_log_all);
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
                id_pk_cabang like '%".$search_key."%' or 
                cabang_daerah like '%".$search_key."%' or 
                cabang_kop_surat like '%".$search_key."%' or 
                cabang_nonpkp like '%".$search_key."%' or 
                cabang_pernyataan_rek like '%".$search_key."%' or 
                cabang_notelp like '%".$search_key."%' or 
                cabang_alamat like '%".$search_key."%' or 
                cabang_status like '%".$search_key."%' or 
                cabang_create_date like '%".$search_key."%' or 
                cabang_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_cabang,cabang_daerah,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,cabang_kop_surat,cabang_nonpkp,cabang_pernyataan_rek
        from ".$this->tbl_name." 
        where cabang_status = ? and id_fk_toko = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif",$this->id_fk_toko
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_cabang
        from ".$this->tbl_name." 
        where cabang_status = ? and id_fk_toko = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_id(){
        $where = array(
            "id_pk_cabang" => $this->id_pk_cabang
        );
        $field = array(
            "id_pk_cabang",
            "cabang_daerah",
            "cabang_notelp",
            "cabang_alamat",
            "cabang_status",
            "cabang_last_modified",
            "id_fk_toko",
            "cabang_kop_surat",
            "cabang_nonpkp",
            "cabang_pernyataan_rek"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "cabang_daerah" => $this->cabang_daerah,
                "cabang_kop_surat" => $this->cabang_kop_surat,
                "cabang_nonpkp" => $this->cabang_nonpkp,
                "cabang_pernyataan_rek" => $this->cabang_pernyataan_rek,
                "cabang_notelp" => $this->cabang_notelp,
                "cabang_alamat" => $this->cabang_alamat,
                "cabang_status" => $this->cabang_status,
                "cabang_create_date" => $this->cabang_create_date,
                "cabang_last_modified" => $this->cabang_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
                "id_fk_toko" => $this->id_fk_toko
            );
            return insertrow($this->tbl_name,$data);
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
                "cabang_kop_surat" => $this->cabang_kop_surat,
                "cabang_nonpkp" => $this->cabang_nonpkp,
                "cabang_pernyataan_rek" => $this->cabang_pernyataan_rek,
                "cabang_notelp" => $this->cabang_notelp,
                "cabang_alamat" => $this->cabang_alamat,
                "cabang_last_modified" => $this->cabang_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
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
                "cabang_status" => "nonaktif",
                "cabang_last_modified" => $this->cabang_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->cabang_daerah == ""){
            return false;
        }
        if($this->cabang_kop_surat == ""){
            return false;
        }
        if($this->cabang_nonpkp == ""){
            return false;
        }
        if($this->cabang_pernyataan_rek == ""){
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
        if($this->cabang_kop_surat == ""){
            return false;
        }
        if($this->cabang_nonpkp == ""){
            return false;
        }
        if($this->cabang_pernyataan_rek == ""){
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
    public function set_insert($cabang_daerah,$cabang_notelp,$cabang_status,$cabang_alamat,$id_fk_toko,$cabang_kop_surat,$cabang_nonpkp,$cabang_pernyataan_rek){
        if(!$this->set_cabang_daerah($cabang_daerah)){
            return false;
        }
        if(!$this->set_cabang_kop_surat($cabang_kop_surat)){
            return false;
        }
        if(!$this->set_cabang_nonpkp($cabang_nonpkp)){
            return false;
        }
        if(!$this->set_cabang_pernyataan_rek($cabang_pernyataan_rek)){
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
    public function set_update($id_pk_cabang,$cabang_daerah,$cabang_notelp,$cabang_alamat,$cabang_kop_surat,$cabang_nonpkp,$cabang_pernyataan_rek){
        if(!$this->set_id_pk_cabang($id_pk_cabang)){
            return false;
        }
        if(!$this->set_cabang_daerah($cabang_daerah)){
            return false;
        }
        if(!$this->set_cabang_kop_surat($cabang_kop_surat)){
            return false;
        }
        if(!$this->set_cabang_nonpkp($cabang_nonpkp)){
            return false;
        }
        if(!$this->set_cabang_pernyataan_rek($cabang_pernyataan_rek)){
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
    public function set_cabang_kop_surat($cabang_kop_surat){
        if($cabang_kop_surat != ""){
            $this->cabang_kop_surat = $cabang_kop_surat;
            return true;
        }
        return false;
    }
    public function set_cabang_nonpkp($cabang_nonpkp){
        if($cabang_nonpkp != ""){
            $this->cabang_nonpkp = $cabang_nonpkp;
            return true;
        }
        return false;
    }
    public function set_cabang_pernyataan_rek($cabang_pernyataan_rek){
        if($cabang_pernyataan_rek != ""){
            $this->cabang_pernyataan_rek = $cabang_pernyataan_rek;
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