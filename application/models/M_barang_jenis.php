<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_barang_jenis extends ci_model{
    private $tbl_name = "mstr_barang_jenis";
    private $columns = array();
    private $id_pk_brg_jenis;
    private $brg_jenis_nama;
    private $brg_jenis_status;
    private $brg_jenis_create_date;
    private $brg_jenis_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_jenis_nama","jenis barang",true);
        $this->set_column("brg_jenis_status","status",false);
        $this->set_column("brg_jenis_last_modified","last modified",false);

        $this->brg_jenis_create_date = date("y-m-d h:i:s");
        $this->brg_jenis_last_modified = date("y-m-d h:i:s");
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
        drop table if exists mstr_barang_jenis;
        create table mstr_barang_jenis(
            id_pk_brg_jenis int primary key auto_increment,
            brg_jenis_nama varchar(100),
            brg_jenis_status varchar(15),
            brg_jenis_create_date datetime,
            brg_jenis_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_barang_jenis_log;
        create table mstr_barang_jenis_log(
            id_pk_brg_jenis_log int primary key auto_increment,
            executed_function varchar(20),
            id_pk_brg_jenis int,
            brg_jenis_nama varchar(100),
            brg_jenis_status varchar(15),
            brg_jenis_create_date datetime,
            brg_jenis_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_barang_jenis;
        delimiter $$
        create trigger trg_after_insert_barang_jenis
        after insert on mstr_barang_jenis
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_jenis_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_jenis_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_jenis_log(executed_function,id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_create_date,brg_jenis_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_jenis,new.brg_jenis_nama,new.brg_jenis_status,new.brg_jenis_create_date,new.brg_jenis_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_barang_jenis;
        delimiter $$
        create trigger trg_after_update_barang_jenis
        after update on mstr_barang_jenis
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_jenis_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_jenis_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_jenis_log(executed_function,id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_create_date,brg_jenis_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_jenis,new.brg_jenis_nama,new.brg_jenis_status,new.brg_jenis_create_date,new.brg_jenis_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = "",$search_category = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_brg_jenis like '%".$search_key."%' or
                brg_jenis_nama like '%".$search_key."%' or
                brg_jenis_status like '%".$search_key."%' or
                brg_jenis_last_modified like '%".$search_key."%' or
                id_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_last_modified,id_last_modified
        from ".$this->tbl_name." 
        where brg_jenis_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_brg_jenis
        from ".$this->tbl_name." 
        where brg_jenis_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function detail_by_name(){
        $where = array(
            "brg_jenis_nama" => $this->brg_jenis_nama
        );
        $field = array(
            "id_pk_brg_jenis",
            "brg_jenis_nama",
            "brg_jenis_status",
            "brg_jenis_create_date",
            "brg_jenis_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function list(){
        $where = array(
            "brg_jenis_status" => "aktif"
        );
        $field = array(
            "id_pk_brg_jenis",
            "brg_jenis_nama",
            "brg_jenis_status",
            "brg_jenis_create_date",
            "brg_jenis_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_jenis_nama" => $this->brg_jenis_nama,
                "brg_jenis_status" => $this->brg_jenis_status,
                "brg_jenis_create_date" => $this->brg_jenis_create_date,
                "brg_jenis_last_modified" => $this->brg_jenis_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
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
                "id_pk_brg_jenis !=" => $this->id_pk_brg_jenis,
                "brg_jenis_nama" => $this->brg_jenis_nama,
                "brg_jenis_status" => "aktif",
            );
            if(!isexistsintable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_brg_jenis" => $this->id_pk_brg_jenis
                );
                $data = array(
                    "brg_jenis_nama" => $this->brg_jenis_nama,
                    "brg_jenis_last_modified" => $this->brg_jenis_last_modified,
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
                "id_pk_brg_jenis" => $this->id_pk_brg_jenis
            );
            $data = array(
                "brg_jenis_status" => "nonaktif",
                "brg_jenis_last_modified" => $this->brg_jenis_last_modified,
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
        if($this->brg_jenis_nama == ""){
            return false;
        }
        if($this->brg_jenis_status == ""){
            return false;
        }
        if($this->brg_jenis_create_date == ""){
            return false;
        }
        if($this->brg_jenis_last_modified == ""){
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
        if($this->id_pk_brg_jenis == ""){
            return false;
        }
        if($this->brg_jenis_nama == ""){
            return false;
        }
        if($this->brg_jenis_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_jenis == ""){
            return false;
        }
        if($this->brg_jenis_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($brg_jenis_nama,$brg_jenis_status){
        if(!$this->set_brg_jenis_nama($brg_jenis_nama)){
            return false;
        }
        if(!$this->set_brg_jenis_status($brg_jenis_status)){
            return false;
        }
        else return true;
    }
    public function set_update($id_pk_brg_jenis,$brg_jenis_nama){
        if(!$this->set_id_pk_brg_jenis($id_pk_brg_jenis)){
            return false;
        }
        if(!$this->set_brg_jenis_nama($brg_jenis_nama)){
            return false;
        }
        else return true;
    }
    public function set_delete($id_pk_brg_jenis){
        if(!$this->set_id_pk_brg_jenis($id_pk_brg_jenis)){
            return false;
        }
        else return true;
    }
    public function get_id_pk_brg_jenis(){
        return $this->id_pk_brg_jenis;
    }
    public function get_brg_jenis_nama(){
        return $this->brg_jenis_nama;
    }
    public function get_brg_jenis_status(){
        return $this->brg_jenis_status;
    }
    public function set_id_pk_brg_jenis($id_pk_brg_jenis){
        if($id_pk_brg_jenis != ""){
            $this->id_pk_brg_jenis = $id_pk_brg_jenis;
            return true;
        }
        return false;
    }
    public function set_brg_jenis_nama($brg_jenis_nama){
        if($brg_jenis_nama != ""){
            $this->brg_jenis_nama = $brg_jenis_nama;
            return true;
        }
        return false;
    }
    public function set_brg_jenis_status($brg_jenis_status){
        if($brg_jenis_status != ""){
            $this->brg_jenis_status = $brg_jenis_status;
            return true;
        }
        return false;
    }
    public function data_excel(){
        $where = array(
            "brg_jenis_status" => "aktif"
        );
        $field = array(
            "id_pk_brg_jenis",
            "brg_jenis_nama",
            "brg_jenis_status",
            "brg_jenis_create_date",
            "brg_jenis_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function columns_excel(){
        $this->columns = array();
        $this->set_column("brg_jenis_nama","jenis barang",true);
        $this->set_column("brg_jenis_status","status",false);
        $this->set_column("brg_jenis_last_modified","last modified",false);
        return $this->columns;
    }
}