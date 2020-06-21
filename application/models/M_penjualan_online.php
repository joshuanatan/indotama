<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_penjualan_online extends ci_model{
    private $tbl_name = "tbl_penjualan_online";
    private $columns = array();
    private $id_pk_penjualan_online;
    private $penj_on_marketplace;
    private $penj_on_no_resi;
    private $penj_on_kurir;
    private $penj_on_status;
    private $id_fk_penjualan;
    private $penj_on_create_date;
    private $penj_on_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->penj_on_create_date = date("y-m-d h:i:s");
        $this->penj_on_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "drop table if exists tbl_penjualan_online;
        create table tbl_penjualan_online(
            id_pk_penjualan_online int primary key auto_increment,
            penj_on_marketplace varchar(40),
            penj_on_no_resi varchar(40),
            penj_on_kurir varchar(40),
            penj_on_status varchar(15),
            id_fk_penjualan int,
            penj_on_create_date datetime,
            penj_on_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_penjualan_online_log;
        create table tbl_penjualan_online_log(
            id_pk_penjualan_online_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_penjualan_online int,
            penj_on_marketplace varchar(40),
            penj_on_no_resi varchar(40),
            penj_on_kurir varchar(40),
            penj_on_status varchar(15),
            id_fk_penjualan int,
            penj_on_create_date datetime,
            penj_on_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_penjualan_online;
        delimiter $$
        create trigger trg_after_insert_penjualan_online
        after insert on tbl_penjualan_online
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_on_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penj_on_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_online_log(executed_function,id_pk_penjualan_online,penj_on_marketplace,penj_on_no_resi,penj_on_kurir,penj_on_status,id_fk_penjualan,penj_on_create_date,penj_on_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan_online,new.penj_on_marketplace,new.penj_on_no_resi,new.penj_on_kurir,new.penj_on_status,new.id_fk_penjualan,new.penj_on_create_date,new.penj_on_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_penjualan_online;
        delimiter $$
        create trigger trg_after_update_penjualan_online
        after update on tbl_penjualan_online
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_on_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penj_on_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_online_log(executed_function,id_pk_penjualan_online,penj_on_marketplace,penj_on_no_resi,penj_on_kurir,penj_on_status,id_fk_penjualan,penj_on_create_date,penj_on_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan_online,new.penj_on_marketplace,new.penj_on_no_resi,new.penj_on_kurir,new.penj_on_status,new.id_fk_penjualan,new.penj_on_create_date,new.penj_on_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function detail(){
        $field = array(
            "id_pk_penjualan_online",
            "penj_on_marketplace",
            "penj_on_no_resi",
            "penj_on_kurir",
            "penj_on_status"
        );
        $where = array(
            "id_fk_penjualan" => $this->id_fk_penjualan
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penj_on_marketplace" => $this->penj_on_marketplace,
                "penj_on_no_resi" => $this->penj_on_no_resi,
                "penj_on_kurir" => $this->penj_on_kurir,
                "penj_on_status" => $this->penj_on_status,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "penj_on_create_date" => $this->penj_on_create_date,
                "penj_on_last_modified" => $this->penj_on_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertrow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_fk_penjualan" => $this->id_fk_penjualan
            );
            $data = array(
                "penj_on_marketplace" => $this->penj_on_marketplace,
                "penj_on_no_resi" => $this->penj_on_no_resi,
                "penj_on_kurir" => $this->penj_on_kurir,
                "penj_on_last_modified" => $this->penj_on_last_modified,
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
                "id_pk_penjualan_online" => $this->id_pk_penjualan_online
            );
            $data = array(
                "penj_on_status" => "nonaktif",
                "penj_on_last_modified" => $this->penj_on_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true; 
        }
        return false;
    }
    public function check_insert(){
        if($this->penj_on_marketplace == ""){
            return false;
        }
        if($this->penj_on_no_resi == ""){
            return false;
        }
        if($this->penj_on_kurir == ""){
            return false;
        }
        if($this->penj_on_status == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->penj_on_create_date == ""){
            return false;
        }
        if($this->penj_on_last_modified == ""){
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
        if($this->penj_on_marketplace == ""){
            return false;
        }
        if($this->penj_on_no_resi == ""){
            return false;
        }
        if($this->penj_on_kurir == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->penj_on_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_penjualan_online == ""){
            return false;
        }
        return true;
    }
    public function set_insert($penj_on_marketplace,$penj_on_no_resi,$penj_on_kurir,$penj_on_status,$id_fk_penjualan){
        if(!$this->set_penj_on_marketplace($penj_on_marketplace)){
            return false;
        }
        if(!$this->set_penj_on_no_resi($penj_on_no_resi)){
            return false;
        }
        if(!$this->set_penj_on_kurir($penj_on_kurir)){
            return false;
        }
        if(!$this->set_penj_on_status($penj_on_status)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_update($penj_on_marketplace,$penj_on_no_resi,$penj_on_kurir,$id_fk_penjualan){
        if(!$this->set_penj_on_marketplace($penj_on_marketplace)){
            return false;
        }
        if(!$this->set_penj_on_no_resi($penj_on_no_resi)){
            return false;
        }
        if(!$this->set_penj_on_kurir($penj_on_kurir)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_delete($set_id_pk_penjualan_online){
        if(!$this->set_id_pk_penjualan_online($set_id_pk_penjualan_online)){
            return false;
        }
        return true;
    }
    public function get_id_pk_penjualan_online(){
        return $this->id_pk_penjualan_online;
    }
    public function get_penj_on_marketplace(){
        return $this->penj_on_marketplace;
    }
    public function get_penj_on_no_resi(){
        return $this->penj_on_no_resi;
    }
    public function get_penj_on_kurir(){
        return $this->penj_on_kurir;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
    public function get_penj_on_status(){
        return $this->penj_on_status;
    }
    public function get_penj_on_create_date(){
        return $this->penj_on_create_date;
    }
    public function get_penj_on_last_modified(){
        return $this->penj_on_last_modified;
    }
    public function get_id_create_data(){
        return $this->id_create_data;
    }
    public function get_id_last_modified(){
        return $this->id_last_modified;
    }
    public function set_id_pk_penjualan_online($id_pk_penjualan_online){
        if($id_pk_penjualan_online != ""){
            $this->id_pk_penjualan_online = $id_pk_penjualan_online;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_marketplace($penj_on_marketplace){
        if($penj_on_marketplace != ""){
            $this->penj_on_marketplace = $penj_on_marketplace;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_no_resi($penj_on_no_resi){
        if($penj_on_no_resi != ""){
            $this->penj_on_no_resi = $penj_on_no_resi;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_kurir($penj_on_kurir){
        if($penj_on_kurir != ""){
            $this->penj_on_kurir = $penj_on_kurir;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_status($penj_on_status){
        if($penj_on_status != ""){
            $this->penj_on_status = $penj_on_status;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_fk_penjualan($id_fk_penjualan){
        if($id_fk_penjualan != ""){
            $this->id_fk_penjualan = $id_fk_penjualan;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_create_date($penj_on_create_date){
        if($penj_on_create_date != ""){
            $this->penj_on_create_date = $penj_on_create_date;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_last_modified($penj_on_last_modified){
        if($penj_on_last_modified != ""){
            $this->penj_on_last_modified = $penj_on_last_modified;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_create_data($id_create_data){
        if($id_create_data != ""){
            $this->id_create_data = $id_create_data;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_last_modified($id_last_modified){
        if($id_last_modified != ""){
            $this->id_last_modified = $id_last_modified;
            return true;
        }
        else{
            return false;
        }
    }
}