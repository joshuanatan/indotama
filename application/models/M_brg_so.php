<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_brg_so extends ci_model{
    private $tbl_name = "tbl_brg_so";
    private $columns = array();
    private $id_pk_so_brg;
    private $brg_so_result;
    private $brg_so_notes;
    private $id_fk_stock_opname;
    private $id_fk_brg;
    private $brg_so_create_date;
    private $brg_so_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->brg_so_create_date = date("y-m-d h:i:s");
        $this->brg_so_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "drop table if exists tbl_brg_so;
        create table tbl_brg_so(
            id_pk_so_brg int primary key auto_increment,
            brg_so_result double,
            brg_so_notes varchar(200),
            id_fk_stock_opname int,
            id_fk_brg int,
            brg_so_create_date datetime,
            brg_so_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_so_log;
        create table tbl_brg_so_log(
            id_pk_so_brg_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_so_brg int,
            brg_so_result double,
            brg_so_notes varchar(200),
            id_fk_stock_opname int,
            id_fk_brg int,
            brg_so_create_date datetime,
            brg_so_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_so;
        delimiter $$
        create trigger trg_after_insert_brg_so
        after insert on tbl_brg_so
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_so_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_so_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_so_log(executed_function,id_pk_so_brg,brg_so_result,brg_so_notes,id_fk_stock_opname,id_fk_brg,brg_so_create_date,brg_so_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_so_brg,new.brg_so_result,new.brg_so_notes,new.id_fk_stock_opname,new.id_fk_brg,new.brg_so_create_date,new.brg_so_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_brg_so;
        delimiter $$
        create trigger trg_after_update_brg_so
        after update on tbl_brg_so
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_so_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_so_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_so_log(executed_function,id_pk_so_brg,brg_so_result,brg_so_notes,id_fk_stock_opname,id_fk_brg,brg_so_create_date,brg_so_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_so_brg,new.brg_so_result,new.brg_so_notes,new.id_fk_stock_opname,new.id_fk_brg,new.brg_so_create_date,new.brg_so_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_so_result" => $this->brg_so_result,
                "brg_so_notes" => $this->brg_so_notes,
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_brg" => $this->id_fk_brg,
                "brg_so_create_date" => $this->brg_so_create_date,
                "brg_so_last_modified" => $this->brg_so_last_modified,
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
                "id_pk_so_brg" => $this->id_pk_so_brg
            );
            $data = array(
                "brg_so_result" => $this->brg_so_result,
                "brg_so_notes" => $this->brg_so_notes,
                "id_fk_stock_opname" => $this->id_fk_stock_opname,
                "id_fk_brg" => $this->id_fk_brg,
                "brg_so_last_modified" => $this->brg_so_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_so_brg" => $this->id_pk_so_brg
            );
            $data = array(
                "brg_so_last_modified" => $this->brg_so_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_so_result == ""){
            return false;
        }
        if($this->brg_so_notes == ""){
            return false;
        }
        if($this->id_fk_stock_opname == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->brg_so_create_date == ""){
            return false;
        }
        if($this->brg_so_last_modified == ""){
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
        if($this->id_pk_so_brg == ""){
            return false;
        }
        if($this->brg_so_result == ""){
            return false;
        }
        if($this->brg_so_notes == ""){
            return false;
        }
        if($this->id_fk_stock_opname == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->brg_so_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_so_brg == ""){
            return false;
        }

        if($this->brg_so_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_so_result,$brg_so_notes,$id_fk_stock_opname,$id_fk_brg){
        if(!$this->set_brg_so_result($brg_so_result)){
            return false;
        }
        if(!$this->set_brg_so_notes($brg_so_notes)){
            return false;
        }
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_so_brg,$brg_so_result,$brg_so_notes,$id_fk_stock_opname,$id_fk_brg){
        if(!$this->set_id_pk_so_brg($id_pk_so_brg)){
            return false;
        }
        if(!$this->set_brg_so_result($brg_so_result)){
            return false;
        }
        if(!$this->set_brg_so_notes($brg_so_notes)){
            return false;
        }
        if(!$this->set_id_fk_stock_opname($id_fk_stock_opname)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_so_brg){
        if(!$this->set_id_pk_so_brg($id_pk_so_brg)){
            return false;
        }

        return true;
    }
    public function set_id_pk_so_brg($id_pk_so_brg){
        if($id_pk_so_brg != ""){
            $this->id_pk_so_brg  = $id_pk_so_brg;
            return true;
        }
        return false;
    }
    public function set_brg_so_result($brg_so_result){
        if($brg_so_result != ""){
            $this->brg_so_result  = $brg_so_result;
            return true;
        }
        return false;
    }
    public function set_brg_so_notes($brg_so_notes){
        if($brg_so_notes != ""){
            $this->brg_so_notes  = $brg_so_notes;
            return true;
        }
        return false;
    }
    public function set_id_fk_stock_opname($id_fk_stock_opname){
        if($id_fk_stock_opname != ""){
            $this->id_fk_stock_opname  = $id_fk_stock_opname;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg($id_fk_brg){
        if($id_fk_brg != ""){
            $this->id_fk_brg  = $id_fk_brg;
            return true;
        }
        return false;
    }
    public function set_brg_so_create_date($brg_so_create_date){
        if($brg_so_create_date != ""){
            $this->brg_so_create_date  = $brg_so_create_date;
            return true;
        }
        return false;
    }
    public function set_brg_so_last_modified($brg_so_last_modified){
        if($brg_so_last_modified != ""){
            $this->brg_so_last_modified  = $brg_so_last_modified;
            return true;
        }
        return false;
    }
    public function set_id_create_data($id_create_data){
        if($id_create_data != ""){
            $this->id_create_data  = $id_create_data;
            return true;
        }
        return false;
    }
    public function set_id_last_modified($id_last_modified){
        if($id_last_modified != ""){
            $this->id_last_modified  = $id_last_modified;
            return true;
        }
        return false;
    }    
    public function get_id_pk_so_brg(){
        return $this->id_pk_so_brg;
    }
    public function get_brg_so_result(){
        return $this->brg_so_result;
    }
    public function get_brg_so_notes(){
        return $this->brg_so_notes;
    }
    public function get_id_fk_stock_opname(){
        return $this->id_fk_stock_opname;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
    public function get_brg_so_create_date(){
        return $this->brg_so_create_date;
    }
    public function get_brg_so_last_modified(){
        return $this->brg_so_last_modified;
    }
    public function get_id_create_data(){
        return $this->id_create_data;
    }
    public function get_id_last_modified(){
        return $this->id_last_modified;
    }
}