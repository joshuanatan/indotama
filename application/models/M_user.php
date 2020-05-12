<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_user extends CI_Model{
    private $columns = array();
    private $tbl_name = "MSTR_USER";
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
        $this->columns = array(
            array(
                "col_name" => "user_name",
                "col_disp" => "User Name",
                "order_by" => true
            ),
            array(
                "col_name" => "user_status",
                "col_disp" => "Status",
                "order_by" => false
            ),
            array(
                "col_name" => "user_last_modified",
                "col_disp" => "Last Modified",
                "order_by" => false
            ),
            array(
                "col_name" => "user_create_date",
                "col_disp" => "Created Date",
                "order_by" => false
            ),
        );
        $this->user_last_modified = date("Y-m-d H:i:s");
        $this->user_create_date = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS MSTR_USER;
        CREATE TABLE MSTR_USER(
            ID_PK_USER INT PRIMARY KEY AUTO_INCREMENT,
            USER_NAME VARCHAR(50),
            USER_PASS VARCHAR(200),
            USER_EMAIL VARCHAR(100),
            USER_STATUS VARCHAR(15),
            ID_FK_ROLE INT,
            USER_LAST_MODIFIED DATETIME,
            USER_CREATE_DATE DATETIME,
            ID_CREATE_DATE INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_USER_LOG;
        CREATE TABLE MSTR_USER_LOG(
            ID_PK_USER_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(40),
            ID_PK_USER INT,
            USER_NAME VARCHAR(50),
            USER_PASS VARCHAR(200),
            USER_EMAIL VARCHAR(100),
            USER_STATUS VARCHAR(15),
            ID_FK_ROLE INT,
            USER_LAST_MODIFIED DATETIME,
            USER_CREATE_DATE DATETIME,
            ID_CREATE_DATE INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_USER;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_USER
        AFTER INSERT ON MSTR_USER
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.USER_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT',' ', NEW.USER_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_USER_LOG(EXECUTED_FUNCTION,ID_PK_USER,USER_NAME,USER_PASS,USER_EMAIL,USER_STATUS,ID_FK_ROLE,USER_LAST_MODIFIED,USER_CREATE_DATE,ID_CREATE_DATE,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES('AFTER INSERT',NEW.ID_PK_USER,NEW.USER_NAME,NEW.USER_PASS,NEW.USER_EMAIL,NEW.USER_STATUS,NEW.ID_FK_ROLE,NEW.USER_LAST_MODIFIED,NEW.USER_CREATE_DATE,NEW.ID_CREATE_DATE,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_USER;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_USER
        AFTER UPDATE ON MSTR_USER
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.USER_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT ' , NEW.USER_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_USER_LOG(EXECUTED_FUNCTION,ID_PK_USER,USER_NAME,USER_PASS,USER_EMAIL,USER_STATUS,ID_FK_ROLE,USER_LAST_MODIFIED,USER_CREATE_DATE,ID_CREATE_DATE,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES('AFTER UPDATE',NEW.ID_PK_USER,NEW.USER_NAME,NEW.USER_PASS,NEW.USER_EMAIL,NEW.USER_STATUS,NEW.ID_FK_ROLE,NEW.USER_LAST_MODIFIED,NEW.USER_CREATE_DATE,NEW.ID_CREATE_DATE,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        ";
        executeSql($sql);
    }
    public function columns(){
        return $this->columns;
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            ( 
                id_pk_user LIKE '%".$search_key."%' OR 
                user_name LIKE '%".$search_key."%' OR
                user_pass LIKE '%".$search_key."%' OR
                user_email LIKE '%".$search_key."%' OR
                user_status LIKE '%".$search_key."%' OR
                id_fk_role LIKE '%".$search_key."%' OR
                user_last_modified LIKE '%".$search_key."%' OR
                user_create_date LIKE '%".$search_key."%' OR
                id_create_data LIKE '%".$search_key."%' OR
                id_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_user,user_name,user_email,user_status,id_fk_role,user_last_modified,user_create_date
        FROM ".$this->tbl_name." 
        WHERE user_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_user
        FROM ".$this->tbl_name." 
        WHERE user_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function list(){
        $where = array(
            "user_status" => "ACTIVE"
        );
        $field = array(
            "id_pk_user","user_name","user_email","user_status","id_fk_role","user_last_modified","user_create_date"
        );
        $result = selectRow($this->tbl_name,$where,$field);
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "user_name" => $this->user_name,
                "user_pass" => password_hash($this->user_pass,PASSWORD_DEFAULT),
                "user_email" => $this->user_email,
                "user_status" => $this->user_status,
                "id_fk_role" => $this->id_fk_role,
                "user_create_date" => $this->user_create_date,
                "user_last_modified" => $this->user_last_modified,
                "id_create_date" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertRow($this->tbl_name,$data);
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
            if(!isExistsInTable($this->tbl_name,$where)){
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
    public function update_password(){
        if($this->check_update_password()){
            $where = array(
                "id_pk_user" => $this->id_pk_user
            );
            $data = array(
                "user_pass" => password_hash($this->user_pass,PASSWORD_DEFAULT),
                "id_last_modified" => $this->id_last_modified,
                "user_last_modified" => $this->user_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
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
            updateRow($this->tbl_name,$data,$where);
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
                "user_status" => "AKTIF"
            );
            $field = array(
                "id_pk_user","user_name","user_pass","user_email","id_fk_role","user_status"
            );
            $result = selectRow($this->tbl_name,$where,$field);
            if($result->num_rows() > 0){
                $result = $result->result_array();
                if (password_verify($this->user_pass, $result[0]["user_pass"])){
                    $data = array(
                        "id" => $result[0]["id_pk_user"],
                        "name" => $result[0]["user_name"],
                        "email" => $result[0]["user_email"],
                        "role" => $result[0]["id_fk_role"],
                        "status" => $result[0]["user_status"],
                    );
                    echo "salah di return data";
                    return $data;
                }
                else{
                    echo "salah di verify";
                    return false;
                }
            }
            else{
                echo "salah di user salah";
                return false;
            }
        }
        else{
            echo "salah di check";
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
    public function set_update($id_pk_user,$user_name,$user_email){
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
        if($this->id_pk_user != "" && $this->user_status != "" && $this->user_last_modified != "" && $this->id_last_modified != ""){
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