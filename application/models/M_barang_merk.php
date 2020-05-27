<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_barang_merk extends ci_model{
    private $tbl_name = "mstr_barang_merk";
    private $columns = array();
    private $id_pk_brg_merk;
    private $brg_merk_nama;
    private $brg_merk_status;
    private $brg_merk_create_date;
    private $brg_merk_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_merk_nama","jenis barang","required");
        $this->set_column("brg_merk_status","status","required");
        $this->set_column("brg_merk_last_modified","last modified","required");

        $this->brg_merk_create_date = date("y-m-d h:i:s");
        $this->brg_merk_last_modified = date("y-m-d h:i:s");
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
        create table mstr_barang_merk(
            id_pk_brg_merk int primary key auto_increment,
            brg_merk_nama varchar(100),
            brg_merk_status varchar(15),
            brg_merk_create_date datetime,
            brg_merk_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        create table mstr_barang_merk_log(
            id_pk_brg_merk_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_merk int,
            brg_merk_nama varchar(100),
            brg_merk_status varchar(15),
            brg_merk_create_date datetime,
            brg_merk_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_barang_merk;
        delimiter $$
        create trigger trg_after_insert_barang_merk
        after insert on mstr_barang_merk
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_merk_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_merk_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_merk_log(executed_function,id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_create_date,brg_merk_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_merk,new.brg_merk_nama,new.brg_merk_status,new.brg_merk_create_date,new.brg_merk_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_barang_merk;
        delimiter $$
        create trigger trg_after_update_barang_merk
        after update on mstr_barang_merk
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_merk_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_merk_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_merk_log(executed_function,id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_create_date,brg_merk_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_merk,new.brg_merk_nama,new.brg_merk_status,new.brg_merk_create_date,new.brg_merk_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
                id_pk_brg_merk like '%".$search_key."%' or
                brg_merk_nama like '%".$search_key."%' or
                brg_merk_status like '%".$search_key."%' or
                brg_merk_last_modified like '%".$search_key."%' or
                id_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_last_modified,id_last_modified
        from ".$this->tbl_name." 
        where brg_merk_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_brg_merk
        from ".$this->tbl_name." 
        where brg_merk_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list(){
        $where = array(
            "brg_merk_status" => "aktif"
        );
        $field = array(
            "id_pk_brg_merk",
            "brg_merk_nama",
            "brg_merk_status",
            "brg_merk_create_date",
            "brg_merk_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function detail_by_name(){
        $where = array(
            "id_pk_brg_merk" => $this->brg_merk_nama
        );
        $field = array(
            "id_pk_brg_merk",
            "brg_merk_nama",
            "brg_merk_status",
            "brg_merk_create_date",
            "brg_merk_last_modified",
            "id_create_data",
            "id_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_merk_nama" => $this->brg_merk_nama,
                "brg_merk_status" => $this->brg_merk_status,
                "brg_merk_create_date" => $this->brg_merk_create_date,
                "brg_merk_last_modified" => $this->brg_merk_last_modified,
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
                "id_pk_brg_merk !=" => $this->id_pk_brg_merk,
                "brg_merk_nama" => $this->brg_merk_nama,
                "brg_merk_status" => "aktif",
            );
            if(!isexistsintable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_brg_merk" => $this->id_pk_brg_merk
                );
                $data = array(
                    "brg_merk_nama" => $this->brg_merk_nama,
                    "brg_merk_last_modified" => $this->brg_merk_last_modified,
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
                "id_pk_brg_merk" => $this->id_pk_brg_merk
            );
            $data = array(
                "brg_merk_status" => "nonaktif",
                "brg_merk_last_modified" => $this->brg_merk_last_modified,
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
        if($this->brg_merk_nama == ""){
            return false;
        }
        if($this->brg_merk_status == ""){
            return false;
        }
        if($this->brg_merk_create_date == ""){
            return false;
        }
        if($this->brg_merk_last_modified == ""){
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
        if($this->id_pk_brg_merk == ""){
            return false;
        }
        if($this->brg_merk_nama == ""){
            return false;
        }
        if($this->brg_merk_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_merk == ""){
            return false;
        }
        if($this->brg_merk_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_merk_nama,$brg_merk_status){
        if(!$this->set_brg_merk_nama($brg_merk_nama)){
            return false;
        }
        if(!$this->set_brg_merk_status($brg_merk_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_merk,$brg_merk_nama){
        if(!$this->set_id_pk_brg_merk($id_pk_brg_merk)){
            return false;
        }
        if(!$this->set_brg_merk_nama($brg_merk_nama)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_merk){
        if(!$this->set_id_pk_brg_merk($id_pk_brg_merk)){
            return false;
        }
        return true;
    }
    public function get_id_pk_brg_merk(){
        return $this->id_pk_brg_merk;
    }
    public function get_brg_merk_nama(){
        return $this->brg_merk_nama;
    }
    public function get_brg_merk_status(){
        return $this->brg_merk_status;
    }
    public function set_id_pk_brg_merk($id_pk_brg_merk){
        if($id_pk_brg_merk != ""){
            $this->id_pk_brg_merk = $id_pk_brg_merk;
            return true;
        }
        return false;
    }
    public function set_brg_merk_nama($brg_merk_nama){
        if($brg_merk_nama != ""){
            $this->brg_merk_nama = $brg_merk_nama;
            return true;
        }
        return false;
    }
    public function set_brg_merk_status($brg_merk_status){
        if($brg_merk_status != ""){
            $this->brg_merk_status = $brg_merk_status;
            return true;
        }
        return false;
    }
}