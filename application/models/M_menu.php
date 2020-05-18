<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_menu extends CI_Model{
    private $tbl_name = "MSTR_MENU";
    private $columns = array();
    private $id_pk_menu;
    private $menu_name;
    private $menu_display;
    private $menu_icon;
    private $menu_category;
    private $menu_status;
    private $menu_create_date;
    private $menu_last_modified;
    private $id_create_data;
    private $id_last_modified; 

    public function __construct(){
        parent::__construct();
        $this->set_column("menu_display","Display",false);
        $this->set_column("menu_name","Controller Function",false);
        $this->set_column("menu_icon","Icon",false);
        $this->set_column("menu_category","Kategori",false);
        $this->set_column("menu_status","Status",false);
        $this->set_column("menu_last_modified","Last Modified",false);
        $this->menu_create_date = date("Y-m-d H:i:s");
        $this->menu_last_modified = date("Y-m-d H:i:s");
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
        $sql = "DROP TABLE IF EXISTS MSTR_MENU;
        CREATE TABLE MSTR_MENU(
            ID_PK_MENU INT PRIMARY KEY AUTO_INCREMENT,
            MENU_NAME VARCHAR(100),
            MENU_DISPLAY VARCHAR(100),
            MENU_ICON VARCHAR(100),
            MENU_CATEGORY VARCHAR(100),
            MENU_STATUS VARCHAR(15),
            MENU_CREATE_DATE DATETIME,
            MENU_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS MSTR_MENU_LOG;
        CREATE TABLE MSTR_MENU_LOG(
            ID_PK_MENU_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_MENU INT,
            MENU_NAME VARCHAR(100),
            MENU_DISPLAY VARCHAR(100),
            MENU_ICON VARCHAR(100),
            MENU_CATEGORY VARCHAR(100),
            MENU_STATUS VARCHAR(15),
            MENU_CREATE_DATE DATETIME,
            MENU_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_MENU;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_MENU
        AFTER INSERT ON MSTR_MENU
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.MENU_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.MENU_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_MENU_LOG(EXECUTED_FUNCTION,ID_PK_MENU,MENU_NAME,MENU_DISPLAY,MENU_ICON,MENU_CATEGORY,MENU_STATUS,MENU_CREATE_DATE,MENU_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_MENU,NEW.MENU_NAME,NEW.MENU_DISPLAY,NEW.MENU_ICON,NEW.MENU_CATEGORY,NEW.MENU_STATUS,NEW.MENU_CREATE_DATE,NEW.MENU_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
            
            /* INSERT NEW MENU TO ALL HAK AKSES*/
            SET @ID_MENU = NEW.ID_PK_MENU;
            INSERT INTO TBL_HAK_AKSES(ID_FK_JABATAN,ID_FK_MENU,HAK_AKSES_STATUS,HAK_AKSES_CREATE_DATE,HAK_AKSES_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED)
            SELECT ID_PK_JABATAN,@ID_MENU,'NONAKTIF',@TGL_ACTION,@TGL_ACTION,@ID_USER,@ID_USER FROM MSTR_JABATAN;
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_MENU;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_MENU
        AFTER UPDATE ON MSTR_MENU
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.MENU_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.MENU_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO MSTR_MENU_LOG(EXECUTED_FUNCTION,ID_PK_MENU,MENU_NAME,MENU_DISPLAY,MENU_ICON,MENU_CATEGORY,MENU_STATUS,MENU_CREATE_DATE,MENU_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_MENU,NEW.MENU_NAME,NEW.MENU_DISPLAY,NEW.MENU_ICON,NEW.MENU_CATEGORY,NEW.MENU_STATUS,NEW.MENU_CREATE_DATE,NEW.MENU_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function list(){
        $where = array(
            "menu_status" => "AKTIF"
        );
        $field = array(
            "id_pk_menu","menu_name","menu_display","menu_icon","menu_category","menu_status","menu_last_modified"
        );
        return selectRow($this->tbl_name,$where,$field);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "ASC", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "AND
            (
                menu_name LIKE '%".$search_key."%' OR 
                menu_display LIKE '%".$search_key."%' OR 
                menu_icon LIKE '%".$search_key."%' OR 
                menu_status LIKE '%".$search_key."%' OR 
                menu_category LIKE '%".$search_key."%' OR 
                menu_last_modified LIKE '%".$search_key."%'
            )";
        }
        $query = "
        SELECT id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_last_modified
        FROM ".$this->tbl_name." 
        WHERE menu_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_menu
        FROM ".$this->tbl_name." 
        WHERE menu_status = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "menu_name" => $this->menu_name,
                "menu_display" => $this->menu_display,
                "menu_icon" => $this->menu_icon,
                "menu_category" => $this->menu_category,
                "menu_status" => $this->menu_status,
                "menu_create_date" => $this->menu_create_date,
                "menu_last_modified" => $this->menu_last_modified,
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
                "id_pk_menu" => $this->id_pk_menu
            );
            $data = array(
                "menu_name" => $this->menu_name, 
                "menu_display" => $this->menu_display, 
                "menu_icon" => $this->menu_icon, 
                "menu_category" => $this->menu_category, 
                "menu_last_modified" => $this->menu_last_modified, 
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
                "id_pk_menu" => $this->id_pk_menu
            );
            $data = array(
                "menu_status" => "NONAKTIF", 
                "menu_last_modified" => $this->menu_last_modified, 
                "id_last_modified" => $this->id_last_modified, 
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->menu_name == ""){
            return false;
        }
        if($this->menu_display == ""){
            return false;
        }
        if($this->menu_icon == ""){
            return false;
        }
        if($this->menu_status == ""){
            return false;
        }
        if($this->menu_category == ""){
            return false;
        }
        if($this->menu_create_date == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->menu_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_update(){
        if($this->id_pk_menu == ""){
            return false;
        }
        if($this->menu_name == ""){
            return false;
        }
        if($this->menu_display == ""){
            return false;
        }
        if($this->menu_icon == ""){
            return false;
        }
        if($this->menu_category == ""){
            return false;
        }
        if($this->menu_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_menu == ""){
            return false;
        }
        if($this->menu_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($menu_name,$menu_display,$menu_icon,$menu_status,$menu_category){
        if(!$this->set_menu_name($menu_name)){
            return false;
        }
        if(!$this->set_menu_display($menu_display)){
            return false;
        }
        if(!$this->set_menu_icon($menu_icon)){
            return false;
        }
        if(!$this->set_menu_status($menu_status)){
            return false;
        }
        if(!$this->set_menu_category($menu_category)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_menu,$menu_name,$menu_display,$menu_icon,$menu_category){
        if(!$this->set_id_pk_menu($id_pk_menu)){
            return false;
        }
        if(!$this->set_menu_display($menu_display)){
            return false;
        }
        if(!$this->set_menu_icon($menu_icon)){
            return false;
        }
        if(!$this->set_menu_name($menu_name)){
            return false;
        }
        if(!$this->set_menu_category($menu_category)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_menu){
        if(!$this->set_id_pk_menu($id_pk_menu)){
            return false;
        }
        return true;
    }
    public function set_id_pk_menu($id_pk_menu){
        if($id_pk_menu != ""){
            $this->id_pk_menu = $id_pk_menu;
            return true;
        }
        return false;
    }
    public function set_menu_name($menu_name){
        if($menu_name != ""){
            $this->menu_name = $menu_name;
            return true;
        }
        return false;
    }
    public function set_menu_display($menu_display){
        if($menu_display != ""){
            $this->menu_display = $menu_display;
            return true;
        }
        return false;
    }
    public function set_menu_icon($menu_icon){
        if($menu_icon != ""){
            $this->menu_icon = $menu_icon;
            return true;
        }
        return false;
    }
    public function set_menu_status($menu_status){
        if($menu_status != ""){
            $this->menu_status = $menu_status;
            return true;
        }
        return false;
    }
    public function set_menu_category($menu_category){
        if($menu_category != ""){
            $this->menu_category = $menu_category;
            return true;
        }
        return false;
    }
    public function get_menu_name(){
        return $this->menu_name;
    }
    public function get_menu_display(){
        return $this->menu_display;
    }
    public function get_menu_icon(){
        return $this->menu_icon;
    }
    public function get_menu_status(){
        return $this->menu_status;
    }
    public function get_menu_category(){
        return $this->menu_category;
    }
}