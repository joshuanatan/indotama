<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");

class M_retur extends CI_Model{
    private $tbl_name = "mstr_retur";
    private $columns = array();
    private $id_pk_retur;
    private $id_fk_penjualan;
    private $retur_no;
    private $retur_tgl;
    private $retur_tipe;
    private $retur_status;
    private $retur_create_date;
    private $retur_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("retur_no","No Retur",true);
        $this->set_column("retur_tgl","Tanggal Retur",false);
        $this->set_column("retur_tipe","Tipe Retur",false);
        $this->set_column("retur_status","Status",false);
        $this->set_column("retur_last_modified","Last Modified",false);
        $this->retur_create_date = date("y-m-d h:i:s");
        $this->retur_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists mstr_retur;
        create table mstr_retur(
            id_pk_retur int primary key auto_increment,
            id_fk_penjualan int,
            retur_no varchar(100),
            retur_tgl datetime,
            retur_tipe varchar(15),
            retur_status varchar(15),
            retur_create_date datetime,
            retur_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_retur_log;
        create table mstr_retur_log(
            id_pk_retur_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_retur int,
            id_fk_penjualan int,
            retur_no varchar(100),
            retur_tgl datetime,
            retur_tipe varchar(15),
            retur_status varchar(15),
            retur_create_date datetime,
            retur_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_retur;
        delimiter $$
        create trigger trg_after_insert_retur
        after insert on mstr_retur
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_retur_log(executed_function,id_pk_retur,id_fk_penjualan,retur_no,retur_tgl,retur_tipe,retur_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_retur,new.id_fk_penjualan,new.retur_no,new.retur_tgl,new.retur_tipe,new.retur_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_retur;
        delimiter $$
        create trigger trg_after_update_retur
        after update on mstr_retur
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_retur_log(executed_function,id_pk_retur,id_fk_penjualan,retur_no,retur_tgl,retur_tipe,retur_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_retur,new.id_fk_penjualan,new.retur_no,new.retur_tgl,new.retur_tipe,new.retur_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                retur_no like '%".$search_key."%' or
                retur_tgl like '%".$search_key."%' or
                retur_status like '%".$search_key."%' or
                retur_tipe like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_retur,id_fk_penjualan,retur_no,retur_tgl,retur_status,retur_tipe,retur_create_date,retur_last_modified,penj_nomor
        from ".$this->tbl_name." 
        inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = ".$this->tbl_name.".id_fk_penjualan
        where retur_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_retur
        from ".$this->tbl_name." 
        where retur_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list(){
        $where = array(
            "retur_status" => "aktif"
        );
        $field = array(
            "id_pk_retur",
            "id_fk_penjualan",
            "retur_no",
            "retur_tgl",
            "retur_status",
            "retur_tipe",
            "retur_create_date",
            "retur_last_modified"
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function columns(){
        return $this->columns;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "retur_no" => $this->retur_no,
                "retur_tgl" => $this->retur_tgl,
                "retur_tipe" => $this->retur_tipe,
                "retur_status" => $this->retur_status,
                "retur_create_date" => $this->retur_create_date,
                "retur_last_modified" => $this->retur_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
            );
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_retur" => $this->id_pk_retur
            );
            $data = array(
                "retur_no" => $this->retur_no,
                "retur_tgl" => $this->retur_tgl,
                "retur_tipe" => $this->retur_tipe,
                "retur_last_modified" => $this->retur_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_retur" => $this->id_pk_retur
            );
            $data = array(
                "retur_status" => "nonaktif",
                "retur_last_modified" => $this->retur_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->retur_no == ""){
            return false;
        }
        if($this->retur_tgl == ""){
            return false;
        }
        if($this->retur_status == ""){
            return false;
        }
        if($this->retur_tipe == ""){
            return false;
        }
        if($this->retur_create_date == ""){
            return false;
        }
        if($this->retur_last_modified == ""){
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
        if($this->id_pk_retur == ""){
            return false;
        }
        if($this->retur_no == ""){
            return false;
        }
        if($this->retur_tgl == ""){
            return false;
        }
        if($this->retur_tipe == ""){
            return false;
        }
        if($this->retur_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_retur == ""){
            return false;
        }
        if($this->retur_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_penjualan,$retur_no,$retur_tgl,$retur_status,$retur_tipe){
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        if(!$this->set_retur_no($retur_no)){
            return false;
        }
        if(!$this->set_retur_tgl($retur_tgl)){
            return false;
        }
        if(!$this->set_retur_status($retur_status)){
            return false;
        }
        if(!$this->set_retur_tipe($retur_tipe)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_retur,$retur_no,$retur_tgl,$retur_tipe){
        if(!$this->set_id_pk_retur($id_pk_retur)){
            return false;
        }
        if(!$this->set_retur_no($retur_no)){
            return false;
        }
        if(!$this->set_retur_tgl($retur_tgl)){
            return false;
        }
        if(!$this->set_retur_tipe($retur_tipe)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_retur){
        if(!$this->set_id_pk_retur($id_pk_retur)){
            return false;
        }
        return true;
    }
    public function set_id_pk_retur($id_pk_retur){
        if($id_pk_retur != ""){
            $this->id_pk_retur = $id_pk_retur;
            return true;
        }
        return false;
    }
    public function set_id_fk_penjualan($id_fk_penjualan){
        if($id_fk_penjualan != ""){
            $this->id_fk_penjualan = $id_fk_penjualan;
            return true;
        }
        return false;
    }
    public function set_retur_no($retur_no){
        if($retur_no != ""){
            $this->retur_no = $retur_no;
            return true;
        }
        return false;
    }
    public function set_retur_tgl($retur_tgl){
        if($retur_tgl != ""){
            $this->retur_tgl = $retur_tgl;
            return true;
        }
        return false;
    }
    public function set_retur_tipe($retur_tipe){
        if($retur_tipe != ""){
            $this->retur_tipe = $retur_tipe;
            return true;
        }
        return false;
    }
    public function set_retur_status($retur_status){
        if($retur_status != ""){
            $this->retur_status = $retur_status;
            return true;
        }
        return false;
    }
    public function get_id_pk_retur(){
        return $this->id_pk_retur;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
    public function get_retur_tgl(){
        return $this->retur_tgl;
    }
    public function get_retur_no(){
        return $this->retur_no;
    }
    public function get_retur_status(){
        return $this->retur_status;
    }
    public function get_retur_tipe(){
        return $this->retur_tipe;
    }
}