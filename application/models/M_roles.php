<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_roles extends ci_model{
    private $tbl_name = "mstr_jabatan";
    private $columns = array();
    private $id_pk_jabatan;
    private $jabatan_nama;
    private $jabatan_status;
    private $jabatan_create_date;
    private $jabatan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("jabatan_nama","nama","required");
        $this->set_column("jabatan_status","status","required");
        $this->set_column("jabatan_last_modified","last modified","required");
        $this->jabatan_create_date = date("y-m-d h:i:s");
        $this->jabatan_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "drop table if exists mstr_jabatan;
        create table mstr_jabatan(
            id_pk_jabatan int primary key auto_increment,
            jabatan_nama varchar(100),
            jabatan_status varchar(15),
            jabatan_create_date datetime,
            jabatan_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_jabatan_log;
        create table mstr_jabatan_log(
            id_pk_jabatan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_jabatan int,
            jabatan_nama varchar(100),
            jabatan_status varchar(15),
            jabatan_create_date datetime,
            jabatan_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_jabatan;
        delimiter $$
        create trigger trg_after_insert_jabatan
        after insert on mstr_jabatan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.jabatan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.jabatan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_jabatan_log(executed_function,id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_create_date,jabatan_last_modified,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_jabatan,new.jabatan_nama,new.jabatan_status,new.jabatan_create_date,new.jabatan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_jabatan;
        delimiter $$
        create trigger trg_after_update_jabatan
        after update on mstr_jabatan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.jabatan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.jabatan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_jabatan_log(executed_function,id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_create_date,jabatan_last_modified,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_jabatan,new.jabatan_nama,new.jabatan_status,new.jabatan_create_date,new.jabatan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        delimiter $$
        create trigger trg_insert_new_jabatan_to_all_hak_akses
        after insert on mstr_jabatan
        for each row
        begin
            /* insert new jabatan to all hak akses*/
            set @id_jabatan = new.id_pk_jabatan;
            insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
            select @id_jabatan,id_pk_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_menu;
        end $$
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
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_jabatan like '%".$search_key."%' or
                jabatan_nama like '%".$search_key."%' or
                jabatan_status like '%".$search_key."%' or
                jabatan_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_last_modified
        from ".$this->tbl_name." 
        where jabatan_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_jabatan
        from ".$this->tbl_name." 
        where jabatan_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list_data(){
        $where = array(
            "jabatan_status" => "aktif"
        );
        $field = array(
            "id_pk_jabatan",
            "jabatan_nama",
            "jabatan_status",
            "jabatan_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function columns(){
        return $this->columns;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "jabatan_nama" => $this->jabatan_nama,
                "jabatan_status" => $this->jabatan_status,
                "jabatan_create_date" => $this->jabatan_create_date,
                "jabatan_last_modified" => $this->jabatan_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            $id_hasil_insert = insertrow($this->tbl_name, $data);

            $log_all_msg = "Data Jabatan baru ditambahkan. Waktu penambahan: $this->jabatan_create_date";
            $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

            $log_all_data_changes = "[ID Jabatan: $id_hasil_insert][Nama: $this->jabatan_nama][Status: $this->jabatan_status][Waktu Ditambahkan: $this->jabatan_create_date][Oleh: $nama_user]";
            $log_all_it = "";
            $log_all_user = $this->id_create_data;
            $log_all_tgl = $this->jabatan_create_date;

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
                "id_pk_jabatan !=" => $this->id_pk_jabatan,
                "jabatan_nama" => $this->jabatan_nama,
                "jabatan_status" => "aktif",
            );
            if(!isexistsintable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_jabatan" => $this->id_pk_jabatan
                );
                $data = array(
                    "jabatan_nama" => $this->jabatan_nama,
                    "jabatan_last_modified" => $this->jabatan_last_modified,
                    "id_last_modified" => $this->id_last_modified,
                );
                updateRow($this->tbl_name, $data, $where);
        $id_pk = $this->id_pk_brg_merk;
        $log_all_msg = "Data Jabatan dengan ID: $id_pk diubah. Waktu diubah: $this->jabatan_last_modified . Data berubah menjadi: ";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

        $log_all_data_changes = "[ID Jabatan: $id_pk][Nama: $this->jabatan_nama][Waktu Diedit: $this->jabatan_last_modified][Oleh: $nama_user]";
        $log_all_it = "";
        $log_all_user = $this->id_last_modified;
        $log_all_tgl = $this->jabatan_last_modified;

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
                "id_pk_jabatan" => $this->id_pk_jabatan
            );
            $data = array(
                "jabatan_status" => "nonaktif",
                "jabatan_last_modified" => $this->jabatan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }else{
            return false;
        }
    }
    public function check_insert(){
        if($this->jabatan_nama == ""){
            return false;
        }
        if($this->jabatan_status == ""){
            return false;
        }
        if($this->jabatan_create_date == ""){
            return false;
        }
        if($this->jabatan_last_modified == ""){
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
        if($this->id_pk_jabatan == ""){
            return false;
        }
        if($this->jabatan_nama == ""){
            return false;
        }
        if($this->jabatan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_jabatan == ""){
            return false;
        }
        if($this->jabatan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($jabatan_nama,$jabatan_status){
        if(!$this->set_jabatan_nama($jabatan_nama)){
            return false;
        }
        if(!$this->set_jabatan_status($jabatan_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_jabatan,$jabatan_nama){
        if(!$this->set_id_pk_jabatan($id_pk_jabatan)){
            return false;
        }
        if(!$this->set_jabatan_nama($jabatan_nama)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_jabatan){
        if(!$this->set_id_pk_jabatan($id_pk_jabatan)){
            return false;
        }
        return true;
    }
    public function get_id_pk_jabatan(){
        return $this->id_pk_jabatan;
    }
    public function get_jabatan_nama(){
        return $this->jabatan_nama;
    }
    public function get_jabatan_status(){
        return $this->jabatan_status;
    }
    public function set_id_pk_jabatan($id_pk_jabatan){
        if($id_pk_jabatan != ""){
            $this->id_pk_jabatan = $id_pk_jabatan;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_jabatan_nama($jabatan_nama){
        if($jabatan_nama != ""){
            $this->jabatan_nama = $jabatan_nama;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_jabatan_status($jabatan_status){
        if($jabatan_status != ""){
            $this->jabatan_status = $jabatan_status;
            return true;
        }
        else{
            return false;
        }
    }
}