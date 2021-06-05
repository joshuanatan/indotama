<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_menu extends ci_model
{
  private $tbl_name = "mstr_menu";
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

  public function __construct()
  {
    parent::__construct();
    $this->set_column("menu_display", "display", false);
    $this->set_column("menu_name", "controller function", false);
    $this->set_column("menu_icon", "icon", false);
    $this->set_column("menu_category", "kategori", false);
    $this->set_column("menu_status", "status", false);
    $this->set_column("menu_last_modified", "last modified", false);
    $this->menu_create_date = date("y-m-d h:i:s");
    $this->menu_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  private function set_column($col_name, $col_disp, $order_by)
  {
    $array = array(
      "col_name" => $col_name,
      "col_disp" => $col_disp,
      "order_by" => $order_by
    );
    $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
  }
  public function columns()
  {
    return $this->columns;
  }
  public function install()
  {
    $sql = "drop table if exists mstr_menu;
        create table mstr_menu(
            id_pk_menu int primary key auto_increment,
            menu_name varchar(100),
            menu_display varchar(100),
            menu_icon varchar(100),
            menu_category varchar(100),
            menu_status varchar(15),
            menu_create_date datetime,
            menu_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_menu_log;
        create table mstr_menu_log(
            id_pk_menu_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_menu int,
            menu_name varchar(100),
            menu_display varchar(100),
            menu_icon varchar(100),
            menu_category varchar(100),
            menu_status varchar(15),
            menu_create_date datetime,
            menu_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_menu;
        delimiter $$
        create trigger trg_after_insert_menu
        after insert on mstr_menu
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.menu_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.menu_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_menu_log(executed_function,id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_create_date,menu_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_menu,new.menu_name,new.menu_display,new.menu_icon,new.menu_category,new.menu_status,new.menu_create_date,new.menu_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_menu;
        delimiter $$
        create trigger trg_after_update_menu
        after update on mstr_menu
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.menu_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.menu_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_menu_log(executed_function,id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_create_date,menu_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_menu,new.menu_name,new.menu_display,new.menu_icon,new.menu_category,new.menu_status,new.menu_create_date,new.menu_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        delimiter $$
        create trigger trg_insert_new_menu_to_all_hak_akses
        after insert on mstr_menu
        for each row
        begin
            /* insert new menu to all hak akses*/
            set @id_menu = new.id_pk_menu;
            insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
            select id_pk_jabatan,@id_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_jabatan;
        end $$";
    executequery($sql);
  }
  public function list_data()
  {
    $where = array(
      "menu_status" => "aktif"
    );
    $field = array(
      "id_pk_menu", "menu_name", "menu_display", "menu_icon", "menu_category", "menu_status", "menu_last_modified"
    );
    return selectrow($this->tbl_name, $where, $field);
  }
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "")
  {
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            (
                menu_name like '%" . $search_key . "%' or 
                menu_display like '%" . $search_key . "%' or 
                menu_icon like '%" . $search_key . "%' or 
                menu_status like '%" . $search_key . "%' or 
                menu_category like '%" . $search_key . "%' or 
                menu_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_last_modified
        from " . $this->tbl_name . " 
        where menu_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif"
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_menu
        from ".$this->tbl_name." 
        where menu_status = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
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

            $id_hasil_insert = insertrow($this->tbl_name, $data);

            $log_all_msg = "Data Barang baru ditambahkan. Waktu penambahan: $this->menu_create_date";
            $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

            $log_all_data_changes = "[ID Barang: $id_hasil_insert][Nama: $this->menu_name][Display: $this->menu_display][Icon: $this->menu_icon][Kategori: $this->menu_category][Status: $this->menu_status][Waktu Ditambahkan: $this->menu_create_date][Oleh: $nama_user]";
            $log_all_it = "";
            $log_all_user = $this->id_last_modified;
            $log_all_tgl = $this->menu_create_date;

            $data_log = array(
                "log_all_msg" => $log_all_msg,
                "log_all_data_changes" => $log_all_data_changes,
                "log_all_it" => $log_all_it,
                "log_all_user" => $log_all_user,
                "log_all_tgl" => $log_all_tgl
            );
            insertrow("log_all", $data_log);


            return $id_hasil_insert;
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
            updaterow($this->tbl_name,$data,$where);
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
        "menu_status" => "nonaktif",
        "menu_last_modified" => $this->menu_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    if ($this->menu_name == "") {
      return false;
    }
    if ($this->menu_display == "") {
      return false;
    }
    if ($this->menu_icon == "") {
      return false;
    }
    if ($this->menu_status == "") {
      return false;
    }
    if ($this->menu_category == "") {
      return false;
    }
    if ($this->menu_create_date == "") {
      return false;
    }
    if ($this->id_create_data == "") {
      return false;
    }
    if ($this->menu_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_update()
  {
    if ($this->id_pk_menu == "") {
      return false;
    }
    if ($this->menu_name == "") {
      return false;
    }
    if ($this->menu_display == "") {
      return false;
    }
    if ($this->menu_icon == "") {
      return false;
    }
    if ($this->menu_category == "") {
      return false;
    }
    if ($this->menu_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {
    if ($this->id_pk_menu == "") {
      return false;
    }
    if ($this->menu_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($menu_name, $menu_display, $menu_icon, $menu_status, $menu_category)
  {
    if (!$this->set_menu_name($menu_name)) {
      return false;
    }
    if (!$this->set_menu_display($menu_display)) {
      return false;
    }
    if (!$this->set_menu_icon($menu_icon)) {
      return false;
    }
    if (!$this->set_menu_status($menu_status)) {
      return false;
    }
    if (!$this->set_menu_category($menu_category)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_menu, $menu_name, $menu_display, $menu_icon, $menu_category)
  {
    if (!$this->set_id_pk_menu($id_pk_menu)) {
      return false;
    }
    if (!$this->set_menu_display($menu_display)) {
      return false;
    }
    if (!$this->set_menu_icon($menu_icon)) {
      return false;
    }
    if (!$this->set_menu_name($menu_name)) {
      return false;
    }
    if (!$this->set_menu_category($menu_category)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_menu)
  {
    if (!$this->set_id_pk_menu($id_pk_menu)) {
      return false;
    }
    return true;
  }
  public function set_id_pk_menu($id_pk_menu)
  {
    if ($id_pk_menu != "") {
      $this->id_pk_menu = $id_pk_menu;
      return true;
    }
    return false;
  }
  public function set_menu_name($menu_name)
  {
    if ($menu_name != "") {
      $this->menu_name = $menu_name;
      return true;
    }
    return false;
  }
  public function set_menu_display($menu_display)
  {
    if ($menu_display != "") {
      $this->menu_display = $menu_display;
      return true;
    }
    return false;
  }
  public function set_menu_icon($menu_icon)
  {
    if ($menu_icon != "") {
      $this->menu_icon = $menu_icon;
      return true;
    }
    return false;
  }
  public function set_menu_status($menu_status)
  {
    if ($menu_status != "") {
      $this->menu_status = $menu_status;
      return true;
    }
    return false;
  }
  public function set_menu_category($menu_category)
  {
    if ($menu_category != "") {
      $this->menu_category = $menu_category;
      return true;
    }
    return false;
  }
  public function data_excel()
  {
    $where = array(
      "menu_status" => "aktif"
    );
    $field = array(
      "id_pk_menu", "menu_name", "menu_display", "menu_icon", "menu_category", "menu_status", "menu_last_modified"
    );
    return selectrow($this->tbl_name, $where, $field);
  }
  public function columns_excel()
  {
    $this->columns = array();
    $this->set_column("menu_display", "display", false);
    $this->set_column("menu_name", "controller function", false);
    $this->set_column("menu_icon", "icon", false);
    $this->set_column("menu_category", "kategori", false);
    $this->set_column("menu_status", "status", false);
    $this->set_column("menu_last_modified", "last modified", false);
    return $this->columns;
  }
}
