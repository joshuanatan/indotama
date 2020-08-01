<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_marketplace extends ci_model{
    private $tbl_name = "mstr_marketplace";
    private $columns = array();
    private $id_pk_marketplace;
    private $marketplace_nama;
    private $marketplace_ket;
    private $marketplace_biaya;
    private $marketplace_status;
    private $marketplace_create_date;
    private $marketplace_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("marketplace_nama","nama",false);
        $this->set_column("marketplace_ket","keterangan",false);
        $this->set_column("marketplace_biaya","biaya",false);
        $this->set_column("marketplace_status","status",false);
        $this->set_column("marketplace_last_modified","last modified",false);

        $this->marketplace_create_date = date("y-m-d h:i:s");
        $this->marketplace_last_modified = date("y-m-d h:i:s");
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
        drop table if exists mstr_marketplace;
        create table mstr_marketplace(
            id_pk_marketplace int primary key auto_increment,
            marketplace_nama varchar(100),
            marketplace_ket varchar(200),
            marketplace_biaya int,
            marketplace_status varchar(15),
            marketplace_create_date datetime,
            marketplace_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_marketplace_log;
        create table mstr_marketplace_log(
            id_pk_marketplace_log int primary key auto_increment,
            executed_function varchar(20),
            id_pk_marketplace int,
            marketplace_nama varchar(100),
            marketplace_ket varchar(200),
            marketplace_biaya int,
            marketplace_status varchar(15),
            marketplace_create_date datetime,
            marketplace_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_marketplace;
        delimiter $$
        create trigger trg_after_insert_marketplace
        after insert on mstr_marketplace
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.marketplace_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.marketplace_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_marketplace_log(executed_function,
            id_pk_marketplace,marketplace_nama,marketplace_ket,marketplace_biaya,marketplace_status,marketplace_create_date,marketplace_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_marketplace,new.marketplace_nama,new.marketplace_ket,new.marketplace_biaya,new.marketplace_status,new.marketplace_create_date,new.marketplace_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_marketplace;
        delimiter $$
        create trigger trg_after_update_marketplace
        after update on mstr_marketplace
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.marketplace_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.marketplace_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_marketplace_log(executed_function,
            id_pk_marketplace,marketplace_nama,marketplace_ket,marketplace_biaya,marketplace_status,marketplace_create_date,marketplace_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_marketplace,new.marketplace_nama,new.marketplace_ket,new.marketplace_biaya,new.marketplace_status,new.marketplace_create_date,new.marketplace_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
                marketplace_nama like '%".$search_key."%' or
                marketplace_ket like '%".$search_key."%' or
                marketplace_status like '%".$search_key."%' or
                marketplace_biaya like '%".$search_key."%' or
                marketplace_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_marketplace,marketplace_nama,marketplace_ket,marketplace_status,marketplace_last_modified,marketplace_biaya
        from ".$this->tbl_name." 
        where marketplace_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        //echo $this->db->last_query();
        $query = "
        select id_pk_marketplace
        from ".$this->tbl_name." 
        where marketplace_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list_data(){
        $sql = "select id_pk_marketplace,marketplace_nama,marketplace_ket,marketplace_status,marketplace_last_modified,marketplace_biaya
        from ".$this->tbl_name." 
        where marketplace_status = ?  
        order by marketplace_nama asc"; 
        $args = array(
            "aktif"
        );
        return executequery($sql,$args);
    }
    public function detail_by_name(){
        $where = array(
            "marketplace_nama" => $this->marketplace_nama,
            "marketplace_status" => "aktif"
        );
        $field = array(
            "id_pk_marketplace","marketplace_nama","marketplace_ket","marketplace_biaya","marketplace_status","marketplace_create_date","marketplace_last_modified","id_create_data","id_last_modified",
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function short_insert(){
        $data = array(
            "marketplace_nama" => $this->marketplace_nama,
            "marketplace_status" => "aktif",
            "marketplace_create_date" => $this->marketplace_create_date,
            "marketplace_last_modified" => $this->marketplace_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified
        );
        return insertrow($this->tbl_name,$data);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "marketplace_nama" => $this->marketplace_nama,
                "marketplace_ket" => $this->marketplace_ket,
                "marketplace_status" => $this->marketplace_status,
                "marketplace_biaya" => $this->marketplace_biaya,
                "marketplace_create_date" => $this->marketplace_create_date,
                "marketplace_last_modified" => $this->marketplace_last_modified,
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
                "id_pk_marketplace !=" => $this->id_pk_marketplace,
                "marketplace_nama" => $this->marketplace_nama,
                "marketplace_status" => "aktif"
            );
            if(!isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_marketplace" => $this->id_pk_marketplace
                );
                $data = array(
                    "marketplace_nama" => $this->marketplace_nama,
                    "marketplace_ket" => $this->marketplace_ket,
                    "marketplace_biaya" => $this->marketplace_biaya,
                    "marketplace_last_modified" => $this->marketplace_last_modified,
                    "id_last_modified" => $this->id_last_modified
                );
                updateRow($this->tbl_name,$data,$where);
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
                "id_pk_marketplace" => $this->id_pk_marketplace
            );
            $data = array(
                "marketplace_status" => "nonaktif",
                "marketplace_last_modified" => $this->marketplace_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
    }
    public function check_insert(){
        if($this->marketplace_nama == ""){
            return false;
        }
        if($this->marketplace_ket == ""){
            return false;
        }
        if($this->marketplace_status == ""){
            return false;
        }
        if($this->marketplace_biaya == ""){
            return false;
        }
        if($this->marketplace_create_date == ""){
            return false;
        }
        if($this->marketplace_last_modified == ""){
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
        if($this->id_pk_marketplace == ""){
            return false;
        }
        if($this->marketplace_nama == ""){
            return false;
        }
        if($this->marketplace_ket == ""){
            return false;
        }
        if($this->marketplace_biaya == ""){
            return false;
        }
        if($this->marketplace_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_marketplace == ""){
            return false;
        }
        if($this->marketplace_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($marketplace_nama,$marketplace_ket,$marketplace_status,$marketplace_biaya){
        if(!$this->set_marketplace_nama($marketplace_nama)){
            return false;
        }
        if(!$this->set_marketplace_ket($marketplace_ket)){
            return false;
        }
        if(!$this->set_marketplace_biaya($marketplace_biaya)){
            return false;
        }
        if(!$this->set_marketplace_status($marketplace_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_marketplace,$marketplace_nama,$marketplace_ket,$marketplace_biaya){
        if(!$this->set_id_pk_marketplace($id_pk_marketplace)){
            return false;
        }
        if(!$this->set_marketplace_nama($marketplace_nama)){
            return false;
        }
        if(!$this->set_marketplace_ket($marketplace_ket)){
            return false;
        }
        if(!$this->set_marketplace_biaya($marketplace_biaya)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_marketplace){
        if(!$this->set_id_pk_marketplace($id_pk_marketplace)){
            return false;
        }
        return true;
    }
    public function get_id_pk_marketplace(){
        return $this->id_pk_marketplace;
    }
    public function get_marketplace_nama(){
        return $this->marketplace_nama;
    }
    public function get_marketplace_ket(){
        return $this->marketplace_ket;
    }
    public function get_marketplace_biaya(){
        return $this->marketplace_biaya;
    }
    public function get_marketplace_status(){
        return $this->marketplace_status;
    }
    public function set_id_pk_marketplace($id_pk_marketplace){
        if($id_pk_marketplace != ""){
            $this->id_pk_marketplace = $id_pk_marketplace;
            return true;
        }
        return false;
    }
    public function set_marketplace_nama($marketplace_nama){
        if($marketplace_nama != ""){
            $this->marketplace_nama = $marketplace_nama;
            return true;
        }
        return false;
    }
    public function set_marketplace_ket($marketplace_ket){
        if($marketplace_ket != ""){
            $this->marketplace_ket = $marketplace_ket;
            return true;
        }
        return false;
    }
    public function set_marketplace_biaya($marketplace_biaya){
        if($marketplace_biaya != ""){
            $this->marketplace_biaya = $marketplace_biaya;
            return true;
        }
        return false;
    }
    public function set_marketplace_status($marketplace_status){
        if($marketplace_status != ""){
            $this->marketplace_status = $marketplace_status;
            return true;
        }
        return false;
    }
    public function data_excel(){
        $sql = "select id_pk_marketplace,marketplace_nama,marketplace_ket,marketplace_status,marketplace_last_modified,marketplace_biaya
        from ".$this->tbl_name." 
        where marketplace_status = ?  
        order by marketplace_nama asc"; 
        $args = array(
            "aktif"
        );
        return executequery($sql,$args);
    }
    public function columns_excel(){
        $this->columns = array();
        $this->set_column("marketplace_nama","nama",false);
        $this->set_column("marketplace_ket","keterangan",false);
        $this->set_column("marketplace_biaya","biaya",false);
        $this->set_column("marketplace_status","status",false);
        $this->set_column("marketplace_last_modified","last modified",false);
        return $this->columns;
    }
}