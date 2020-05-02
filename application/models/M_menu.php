<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_menu extends CI_Model{
    private $columns = array();
    private $tbl_name = "MSTR_MENU";    
    private $id_pk_menu;
    private $menu_controller;
    private $menu_display;
    private $menu_icon;
    private $status_menu;
    private $menu_create_date;
    private $menu_last_modified;
    private $id_create_data;
    private $id_last_modified;
    private $id_fk_brg_jenis;
    private $id_fk_brg_merk;

    public function __construct(){
        parent::__construct();
        $this->columns = array(
            array(
                "col_name" => "menu_controller",
                "col_disp" => "Controller",
                "order_by" => true
            ),
            array(
                "col_name" => "menu_display",
                "col_disp" => "Display",
                "order_by" => true
            ),
            array(
                "col_name" => "menu_icon",
                "col_disp" => "Icon",
                "order_by" => true
            ),
            array(
                "col_name" => "status_menu",
                "col_disp" => "Status",
                "order_by" => true
            ),
            array(
                "col_name" => "menu_create_date",
                "col_disp" => "Created Date",
                "order_by" => true
            ),
            array(
                "col_name" => "menu_last_modified",
                "col_disp" => "Last Modified",
                "order_by" => true
            ),
        );
        $this->menu_create_date = date("Y-m-d H:i:s");
        $this->menu_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        DROP TABLE IF EXISTS MSTR_MENU;
        CREATE TABLE MSTR_MENU(
            ID_PK_MENU INT PRIMARY KEY AUTO_INCREMENT,
            MENU_CONTROLLER VARCHAR(100),
            MENU_DISPLAY VARCHAR(100),
            MENU_ICON VARCHAR(20),
            STATUS_MENU VARCHAR(15),
            MENU_CREATE_DATE DATETIME,
            MENU_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
        );
        DROP TABLE IF EXISTS MSTR_MENU_LOG;
        CREATE TABLE MSTR_MENU_LOG(
            ID_PK_MENU_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(40), 
            ID_PK_MENU INT,
            MENU_CONTROLLER VARCHAR(100),
            MENU_DISPLAY VARCHAR(100),
            MENU_ICON VARCHAR(20),
            STATUS_MENU VARCHAR(15),
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
            INSERT INTO MSTR_MENU_LOG(EXECUTED_FUNCTION,ID_PK_MENU,MENU_CONTROLLER,MENU_DISPLAY,MENU_ICON,STATUS_MENU,MENU_CREATE_DATE,MENU_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_MENU,NEW.MENU_CONTROLLER,NEW.MENU_DISPLAY,NEW.MENU_ICON,NEW.STATUS_MENU,NEW.MENU_CREATE_DATE,NEW.MENU_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
            INSERT INTO MSTR_MENU_LOG(EXECUTED_FUNCTION,ID_PK_MENU,MENU_CONTROLLER,MENU_DISPLAY,MENU_ICON,STATUS_MENU,MENU_CREATE_DATE,MENU_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_MENU,NEW.MENU_CONTROLLER,NEW.MENU_DISPLAY,NEW.MENU_ICON,NEW.STATUS_MENU,NEW.MENU_CREATE_DATE,NEW.MENU_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
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
                id_pk_menu LIKE '%".$search_key."%' OR 
                menu_controller LIKE '%".$search_key."%' OR 
                menu_display LIKE '%".$search_key."%' OR 
                menu_icon LIKE '%".$search_key."%' OR 
                status_menu LIKE '%".$search_key."%' OR 
                menu_create_date LIKE '%".$search_key."%' OR 
                menu_last_modified LIKE '%".$search_key."%' 
            )";
        }
        $query = "
        SELECT id_pk_menu,menu_controller,menu_display,menu_icon,status_menu,menu_create_date,menu_last_modified
        FROM ".$this->tbl_name." 
        WHERE status_menu = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction." 
        LIMIT 20 OFFSET ".($page-1)*$data_per_page;
        $args = array(
            "AKTIF"
        );
        $result["data"] = executeQuery($query,$args);
        
        $query = "
        SELECT id_pk_menu
        FROM ".$this->tbl_name." 
        WHERE status_menu = ? ".$search_query."  
        ORDER BY ".$order_by." ".$order_direction;
        $result["total_data"] = executeQuery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "menu_controller" => $this->menu_controller,
                "menu_display" => $this->menu_display,
                "menu_icon" => $this->menu_icon,
                "status_menu" => $this->status_menu,
                "menu_create_date" => $this->menu_create_date,
                "menu_last_modified" => $this->menu_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
                "id_fk_brg_jenis" => $this->id_fk_brg_jenis,
                "id_fk_brg_merk" => $this->id_fk_brg_merk
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
                "id_pk_menu !=" => $this->id_pk_menu,
                "menu_controller" => $this->menu_controller,
                "status_menu" => "AKTIF"
            );
            if(isExistsInTable($this->tbl_name,$where)){
                $where = array(
                    "id_pk_menu !=" => $this->id_pk_menu,
                );
                $data = array(
                    "menu_controller" => $this->menu_controller,
                    "menu_display" => $this->menu_display,
                    "menu_icon" => $this->menu_icon,
                    "menu_last_modified" => $this->menu_last_modified,
                    "id_last_modified" => $this->id_last_modified,
                    "id_fk_brg_jenis" => $this->id_fk_brg_jenis,
                    "id_fk_brg_merk" => $this->id_fk_brg_merk
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
                "id_pk_menu !=" => $this->id_pk_menu,
            );
            $data = array(
                "status_menu" => "NONAKTIF",
                "menu_last_modified" => $this->menu_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function check_insert(){
        if($this->menu_controller == ""){
            return false;
        }
        if($this->menu_display == ""){
            return false;
        }
        if($this->menu_icon == ""){
            return false;
        }
        if($this->status_menu == ""){
            return false;
        }
        if($this->menu_create_date == ""){
            return false;
        }
        if($this->menu_last_modified == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        if($this->id_fk_brg_jenis == ""){
            return false;
        }
        if($this->id_fk_brg_merk == ""){
            return false;
        }
        return true;
    }
    public function check_update(){
        if($this->id_pk_menu == ""){
            return false;
        }
        if($this->menu_controller == ""){
            return false;
        }
        if($this->menu_display == ""){
            return false;
        }
        if($this->menu_icon == ""){
            return false;
        }
        if($this->menu_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        if($this->id_fk_brg_jenis == ""){
            return false;
        }
        if($this->id_fk_brg_merk == ""){
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
    public function set_insert($menu_controller,$menu_display,$menu_icon,$status_menu,$id_fk_brg_jenis,$id_fk_brg_merk){
        if(!$this->set_menu_controller($menu_controller)){
            return false;
        }
        if(!$this->set_menu_display($menu_display)){
            return false;
        }
        if(!$this->set_menu_icon($menu_icon)){
            return false;
        }
        if(!$this->set_status_menu($status_menu)){
            return false;
        }
        if(!$this->set_id_fk_brg_jenis($id_fk_brg_jenis)){
            return false;
        }
        if(!$this->set_id_fk_brg_merk($id_fk_brg_merk)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_menu,$menu_controller,$menu_display,$menu_icon,$id_fk_brg_jenis,$id_fk_brg_merk){
        if(!$this->set_id_pk_menu($id_pk_menu)){
            return false;
        }
        if(!$this->set_menu_controller($menu_controller)){
            return false;
        }
        if(!$this->set_menu_display($menu_display)){
            return false;
        }
        if(!$this->set_menu_icon($menu_icon)){
            return false;
        }
        if(!$this->set_id_fk_brg_jenis($id_fk_brg_jenis)){
            return false;
        }
        if(!$this->set_id_fk_brg_merk($id_fk_brg_merk)){
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
        else{
            return false;
        }
    }
    public function set_menu_controller($menu_controller){
        if($menu_controller != ""){
            $this->menu_controller = $menu_controller;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_menu_display($menu_display){
        if($menu_display != ""){
            $this->menu_display = $menu_display;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_menu_icon($menu_icon){
        if($menu_icon != ""){
            $this->menu_icon = $menu_icon;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_status_menu($status_menu){
        if($status_menu != ""){
            $this->status_menu = $status_menu;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_fk_brg_jenis($id_fk_brg_jenis){
        if($id_fk_brg_jenis != ""){
            $this->id_fk_brg_jenis = $id_fk_brg_jenis;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_fk_brg_merk($id_fk_brg_merk){
        if($id_fk_brg_merk != ""){
            $this->id_fk_brg_merk = $id_fk_brg_merk;
            return true;
        }
        else{
            return false;
        }
    }
    public function get_id_pk_menu(){
        return $this->id_pk_menu;
    }
    public function get_menu_controller(){
        return $this->menu_controller;
    }
    public function get_menu_display(){
        return $this->menu_display;
    }
    public function get_menu_icon(){
        return $this->menu_icon;
    }
    public function get_status_menu(){
        return $this->status_menu;
    }
    public function get_id_fk_brg_jenis(){
        return $this->id_fk_brg_jenis;
    }
    public function get_id_fk_brg_merk(){
        return $this->id_fk_brg_merk;
    }
}
?>