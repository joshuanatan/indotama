<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_user extends ci_model{
    private $columns = array();
    private $tbl_name = "mstr_user";
    private $id_pk_user;
    private $user_name;
    private $user_pass;
    private $user_email;
    private $user_status;
    private $id_fk_role;
    private $user_last_modified;
    private $user_create_date;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->set_column("user_name","username","required");
        $this->set_column("user_email","email","required");
        $this->set_column("jabatan_nama","role","required");
        $this->set_column("user_status","status","required");
        $this->set_column("user_last_modified","last modified","required");

        $this->user_last_modified = date("y-m-d h:i:s");
        $this->user_create_date = date("y-m-d h:i:s");
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
    public function install(){
        $sql = "
        drop table if exists mstr_user;
        create table mstr_user(
            id_pk_user int primary key auto_increment,
            user_name varchar(50),
            user_pass varchar(200),
            user_email varchar(100),
            user_status varchar(15),
            id_fk_role int,
            user_last_modified datetime,
            user_create_date datetime,
            id_create_date int,
            id_last_modified int
        );
        drop table if exists mstr_user_log;
        create table mstr_user_log(
            id_pk_user_log int primary key auto_increment,
            executed_function varchar(40),
            id_pk_user int,
            user_name varchar(50),
            user_pass varchar(200),
            user_email varchar(100),
            user_status varchar(15),
            id_fk_role int,
            user_last_modified datetime,
            user_create_date datetime,
            id_create_date int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_user;
        delimiter $$
        create trigger trg_after_insert_user
        after insert on mstr_user
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at',' ', new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after insert',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_user;
        delimiter $$
        create trigger trg_after_update_user
        after update on mstr_user
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after update',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executesql($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_user like '%".$search_key."%' or 
                user_name like '%".$search_key."%' or
                user_pass like '%".$search_key."%' or
                user_email like '%".$search_key."%' or
                user_status like '%".$search_key."%' or
                id_fk_role like '%".$search_key."%' or
                user_last_modified like '%".$search_key."%' or
                user_create_date like '%".$search_key."%' or
                id_create_data like '%".$search_key."%' or
                id_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_user,user_name,user_email,user_status,id_fk_role,user_last_modified,user_create_date,jabatan_nama
        from ".$this->tbl_name." 
        inner join mstr_jabatan on mstr_jabatan.id_pk_jabatan = ".$this->tbl_name.".id_fk_role
        where user_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_user
        from ".$this->tbl_name." 
        where user_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function menu(){
        $sql = "
        select menu_name,menu_display,menu_icon,menu_category
        from mstr_user
        inner join mstr_jabatan on mstr_jabatan.id_pk_jabatan = mstr_user.id_fk_role
        inner join tbl_hak_akses on tbl_hak_akses.id_fk_jabatan = mstr_jabatan.id_pk_jabatan
        inner join mstr_menu on mstr_menu.id_pk_menu = tbl_hak_akses.id_fk_menu
        where menu_status = 'AKTIF' and hak_akses_status = 'AKTIF' and jabatan_status = 'AKTIF' and id_pk_user = ?
        order by menu_category,menu_display";
        $args = array(
            $this->session->id_user
        );
        return executequery($sql,$args);
    }
    public function list(){
        $where = array(
            "user_status" => "AKTIF"
        );
        $field = array(
            "id_pk_user","user_name","user_email","user_status","id_fk_role","user_last_modified","user_create_date"
        );
        $result = selectrow($this->tbl_name,$where,$field);
        return $result;
    }
    public function detail_by_name(){
        $field = array(
            "id_pk_user","user_name","user_pass","user_email","user_status"
        );
        $where = array(
            "user_name" => $this->user_name
        );
        return selectrow($this->tbl_name,$where,$field);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "user_name" => $this->user_name,
                "user_pass" => $this->user_pass,
                "user_email" => $this->user_email,
                "user_status" => $this->user_status,
                "id_fk_role" => $this->id_fk_role,
                "user_create_date" => $this->user_create_date,
                "user_last_modified" => $this->user_last_modified,
                "id_create_date" => $this->id_create_data,
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
                "id_pk_user != " => $this->id_pk_user,
                "user_name" => $this->user_name,
                "user_email" => $this->user_email,
                "id_fk_role" => $this->id_fk_role,
                "user_status" => "AKTIF",
            );
            if(!isexistsintable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_user" => $this->id_pk_user
                );
                $data = array(
                    "user_name" => $this->user_name,
                    "user_email" => $this->user_email,
                    "id_fk_role" => $this->id_fk_role,
                    "id_last_modified" => $this->id_last_modified,
                    "user_last_modified" => $this->user_last_modified
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
    public function update_password(){
        if($this->check_update_password()){
            $where = array(
                "id_pk_user" => $this->id_pk_user
            );
            $data = array(
                "user_pass" => md5($this->user_pass),
                "id_last_modified" => $this->id_last_modified,
                "user_last_modified" => $this->user_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_user" => $this->id_pk_user
            );
            $data = array(
                "user_status" => "NONAKTIF",
                "id_last_modified" => $this->id_last_modified,
                "user_last_modified" => $this->user_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function login(){
        if($this->check_login()){
            $where = array(
                "user_name" => $this->user_name,
                "user_status" => "AKTIF",
                "user_pass" => $this->user_pass
            );
            $field = array(
                "id_pk_user","user_name","user_email","id_fk_role","user_status"
            );
            $result = selectrow($this->tbl_name,$where,$field);
            if($result->num_rows() > 0){
                $result = $result->result_array();
                $data = array(
                    "id" => $result[0]["id_pk_user"],
                    "name" => $result[0]["user_name"],
                    "email" => $result[0]["user_email"],
                    "role" => $result[0]["id_fk_role"],
                    "status" => $result[0]["user_status"],
                );
                return $data;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }
    public function set_insert($user_name,$user_pass,$user_email,$user_status,$id_fk_role){
        if(!$this->set_user_name($user_name)){
            return false;
        }
        if(!$this->set_user_pass($user_pass)){
            return false;
        }
        if(!$this->set_user_email($user_email)){
            return false;
        }
        if(!$this->set_user_status($user_status)){
            return false;
        }
        if(!$this->set_id_fk_role($id_fk_role)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_user,$user_name,$user_email,$id_fk_role){
        if(!$this->set_id_pk_user($id_pk_user)){
            return false;
        }
        if(!$this->set_user_email($user_email)){
            return false;
        }
        if(!$this->set_user_name($user_name)){
            return false;
        }
        if(!$this->set_id_fk_role($id_fk_role)){
            return false;
        }
        return true;
    }
    public function set_update_password($id_pk_user,$user_pass,$user_email){
        if(!$this->set_id_pk_user($id_pk_user)){
            return false;
        }
        if(!$this->set_user_email($user_email)){
            return false;
        }
        if(!$this->set_user_pass($user_pass)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_user){
        if(!$this->set_id_pk_user($id_pk_user)){
            return false;
        }
        else{
            return true;
        }
    }
    public function set_login($user_name,$user_pass){
        if(!$this->set_user_name($user_name)){
            return false;
        }
        if(!$this->set_user_pass($user_pass)){
            return false;
        }
        return true;
    }
    public function check_insert(){
        if($this->user_name != "" && $this->user_pass != "" && $this->user_email != "" && $this->user_status != "" && $this->id_fk_role != "" && $this->user_last_modified != "" && $this->user_create_date != "" && $this->id_create_data != "" && $this->id_last_modified != ""){
            return true;
        }
        else{
            return false;
        }
    }
    public function check_update(){
        if($this->id_pk_user != "" && $this->user_name != "" && $this->user_email != "" && $this->id_fk_role != "" && $this->user_last_modified != "" && $this->id_last_modified != ""){
            return true;
        }
        else{
            return false;
        }
    }
    public function check_update_password(){
        if($this->id_pk_user != "" && $this->user_pass != "" && $this->user_last_modified != "" && $this->id_last_modified != ""){
            return true;
        }
        else{
            return false;
        }
    }
    public function check_delete(){
        if($this->id_pk_user != "" && $this->user_last_modified != "" && $this->id_last_modified != ""){
            return true;
        }
        else{
            return false;
        }
    }
    public function check_login(){
        if($this->user_name != "" && $this->user_pass != ""){
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_pk_user($id_pk_user){
        if($id_pk_user != ""){
            $this->id_pk_user = $id_pk_user;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_user_name($user_name){
        if($user_name != ""){
            $this->user_name = $user_name;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_user_pass($user_pass){
        if($user_pass != ""){
            $this->user_pass = $user_pass;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_user_email($user_email){
        if($user_email != ""){
            $this->user_email = $user_email;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_user_status($user_status){
        if($user_status != ""){
            $this->user_status = $user_status;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_fk_role($id_fk_role){
        if($id_fk_role != ""){
            $this->id_fk_role = $id_fk_role;
            return true;
        }
        else{
            return false;
        }
    }
    public function get_id_pk_user(){
        return $this->id_pk_user;
    }
    public function get_user_name(){
        return $this->user_name;
    }
    public function get_user_pass(){
        return $this->user_pass;
    }
    public function get_user_email(){
        return $this->user_email;
    }
    public function get_id_fk_role(){
        return $this->id_fk_role;
    }
    public function get_user_status(){
        return $this->user_status;
    }
}