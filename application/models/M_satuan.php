<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_satuan extends ci_model{
    private $tbl_name = "mstr_satuan";
    private $columns = array();
    private $id_pk_satuan;
    private $satuan_nama;
    private $satuan_rumus;
    private $satuan_status;
    private $satuan_create_date;
    private $satuan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("satuan_nama","satuan",true);
        $this->set_column("satuan_rumus","rumus",true);
        $this->set_column("satuan_status","status",false);
        $this->set_column("satuan_last_modified","last modified",false);

        $this->satuan_create_date = date("y-m-d h:i:s");
        $this->satuan_last_modified = date("y-m-d h:i:s");
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
        drop table if exists mstr_satuan;
        create table mstr_satuan(
            id_pk_satuan int primary key auto_increment,
            satuan_nama varchar(100),
            satuan_rumus varchar(100),
            satuan_status varchar(15),
            satuan_create_date datetime,
            satuan_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_satuan_log;
        create table mstr_satuan_log(
            id_pk_satuan_log int primary key auto_increment,
            executed_function varchar(20),
            id_pk_satuan int,
            satuan_nama varchar(100),
            satuan_rumus varchar(100),
            satuan_status varchar(15),
            satuan_create_date datetime,
            satuan_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_satuan;
        delimiter $$
        create trigger trg_after_insert_satuan
        after insert on mstr_satuan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.satuan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.satuan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_satuan_log(executed_function,id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_create_date,satuan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_satuan,new.satuan_nama,new.satuan_status,new.satuan_rumus,new.satuan_create_date,new.satuan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_satuan;
        delimiter $$
        create trigger trg_after_update_satuan
        after update on mstr_satuan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.satuan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.satuan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_satuan_log(executed_function,id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_create_date,satuan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_satuan,new.satuan_nama,new.satuan_status,new.satuan_rumus,new.satuan_create_date,new.satuan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop procedure if exists ubah_satuan_barang;
        delimiter //
        create procedure ubah_satuan_barang(
            in id_satuan_in int,
            inout brg_qty double
        )
        begin
            declare conversion_exp varchar(20);
            select satuan_rumus 
            into conversion_exp
            from mstr_satuan
            where id_pk_satuan = id_satuan_in;
            
            set brg_qty = conversion_exp * brg_qty;
            
        end //
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
                id_pk_satuan like '%".$search_key."%' or
                satuan_nama like '%".$search_key."%' or
                satuan_rumus like '%".$search_key."%' or
                satuan_status like '%".$search_key."%' or
                satuan_last_modified like '%".$search_key."%' or
                id_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_last_modified,id_last_modified
        from ".$this->tbl_name." 
        where satuan_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_satuan
        from ".$this->tbl_name." 
        where satuan_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_name(){
        $where = array(
            "satuan_nama" => $this->satuan_nama,
            "satuan_status" => "aktif"
        );
        $field = array(
            "id_pk_satuan",
            "satuan_nama",
            "satuan_rumus",
            "satuan_status",
            "satuan_create_date",
            "satuan_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function list_data(){
        $where = array(
            "satuan_status" => "aktif"
        );
        $field = array(
            "id_pk_satuan",
            "satuan_nama",
            "satuan_rumus",
            "satuan_status",
            "satuan_create_date",
            "satuan_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field,"","","satuan_rumus");
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "satuan_nama" => $this->satuan_nama,
                "satuan_status" => $this->satuan_status,
                "satuan_rumus" => $this->satuan_rumus,
                "satuan_create_date" => $this->satuan_create_date,
                "satuan_last_modified" => $this->satuan_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            $id_hasil_insert = insertrow($this->tbl_name, $data);

            $log_all_msg = "Data Satuan baru ditambahkan. Waktu penambahan: $this->emp_create_date";
            $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

            $log_all_data_changes = "[ID Satuan: $id_hasil_insert][Nama: $this->satuan_nama][Status: $this->satuan_status][Rumus: $this->satuan_rumus][Waktu Ditambahkan: $this->satuan_create_date][Oleh: $nama_user]";
            $log_all_it = "";
            $log_all_user = $this->id_last_modified;
            $log_all_tgl = $this->emp_create_date;

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
        else{
            return false;
        }
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_satuan !=" => $this->id_pk_satuan,
                "satuan_nama" => $this->satuan_nama,
                "satuan_status" => "aktif",
            );
            if(!isexistsintable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_satuan" => $this->id_pk_satuan
                );
                $data = array(
                    "satuan_nama" => $this->satuan_nama,
                    "satuan_rumus" => $this->satuan_rumus,
                    "satuan_last_modified" => $this->satuan_last_modified,
                    "id_last_modified" => $this->id_last_modified
                );
                updaterow($this->tbl_name,$data,$where);
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
                "id_pk_satuan" => $this->id_pk_satuan
            );
            $data = array(
                "satuan_status" => "nonaktif",
                "satuan_last_modified" => $this->satuan_last_modified,
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
        if($this->satuan_nama == ""){
            return false;
        }
        if($this->satuan_rumus == ""){
            return false;
        }
        if($this->satuan_status == ""){
            return false;
        }
        if($this->satuan_create_date == ""){
            return false;
        }
        if($this->satuan_last_modified == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_update(){
        if($this->id_pk_satuan == ""){
            return false;
        }
        if($this->satuan_nama == ""){
            return false;
        }
        if($this->satuan_rumus == ""){
            return false;
        }
        if($this->satuan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_satuan == ""){
            return false;
        }
        if($this->satuan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($satuan_nama,$satuan_status,$satuan_rumus){
        if(!$this->set_satuan_nama($satuan_nama)){
            return false;
        }
        if(!$this->set_satuan_rumus($satuan_rumus)){
            return false;
        }
        if(!$this->set_satuan_status($satuan_status)){
            return false;
        }
        else return true;
    }
    public function set_update($id_pk_satuan,$satuan_nama,$satuan_rumus){
        if(!$this->set_id_pk_satuan($id_pk_satuan)){
            return false;
        }
        if(!$this->set_satuan_nama($satuan_nama)){
            return false;
        }
        if(!$this->set_satuan_rumus($satuan_rumus)){
            return false;
        }
        else return true;
    }
    public function set_delete($id_pk_satuan){
        if(!$this->set_id_pk_satuan($id_pk_satuan)){
            return false;
        }
        else return true;
    }
    public function get_id_pk_satuan(){
        return $this->id_pk_satuan;
    }
    public function get_satuan_nama(){
        return $this->satuan_nama;
    }
    public function get_satuan_rumus(){
        return $this->satuan_rumus;
    }
    public function get_satuan_status(){
        return $this->satuan_status;
    }
    public function set_id_pk_satuan($id_pk_satuan){
        if($id_pk_satuan != ""){
            $this->id_pk_satuan = $id_pk_satuan;
            return true;
        }
        return false;
    }
    public function set_satuan_nama($satuan_nama){
        if($satuan_nama != ""){
            $this->satuan_nama = $satuan_nama;
            return true;
        }
        return false;
    }
    public function set_satuan_rumus($satuan_rumus){
        if($satuan_rumus != ""){
            $this->satuan_rumus = $satuan_rumus;
            return true;
        }
        return false;
    }
    public function set_satuan_status($satuan_status){
        if($satuan_status != ""){
            $this->satuan_status = $satuan_status;
            return true;
        }
        return false;
    }
}