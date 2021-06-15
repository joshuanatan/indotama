<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");

class m_warehouse_admin extends ci_model
{
  private $tbl_name = "tbl_warehouse_admin";
  private $columns = array();
  private $id_pk_warehouse_admin;
  private $id_fk_warehouse;
  private $id_fk_user;
  private $warehouse_admin_status;
  private $warehouse_admin_create_date;
  private $warehouse_admin_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->columns = array();
    $this->set_column("user_name", "user name", "required");
    $this->set_column("user_email", "email", "required");
    $this->set_column("warehouse_admin_status", "status", "required");
    $this->set_column("warehouse_admin_last_modified", "last modified", "required");
    $this->warehouse_admin_create_date = date("y-m-d h:i:s");
    $this->warehouse_admin_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  public function install()
  {
    $sql = "
        drop table if exists tbl_warehouse_admin;
        create table tbl_warehouse_admin(
            id_pk_warehouse_admin int primary key auto_increment,
            id_fk_warehouse int,
            id_fk_user int,
            warehouse_admin_status varchar(15),
            warehouse_admin_create_date datetime,
            warehouse_admin_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_warehouse_admin_log;
        create table tbl_warehouse_admin_log(
            id_pk_warehouse_admin_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_warehouse_admin int,
            id_fk_warehouse int,
            id_fk_user int,
            warehouse_admin_status varchar(15),
            warehouse_admin_create_date datetime,
            warehouse_admin_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_warehouse_admin;
        delimiter $$
        create trigger trg_after_insert_warehouse_admin
        after insert on tbl_warehouse_admin
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.warehouse_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_warehouse_admin_log(executed_function,id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_create_date,warehouse_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_warehouse_admin,new.id_fk_warehouse,new.id_fk_user,new.warehouse_admin_status,new.warehouse_admin_create_date,new.warehouse_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_warehouse_admin;
        delimiter $$
        create trigger trg_after_update_warehouse_admin
        after update on tbl_warehouse_admin
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.warehouse_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_warehouse_admin_log(executed_function,id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_create_date,warehouse_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_warehouse_admin,new.id_fk_warehouse,new.id_fk_user,new.warehouse_admin_status,new.warehouse_admin_create_date,new.warehouse_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
    executequery($sql);
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
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = 20)
  {
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            ( 
                id_pk_warehouse_admin like '%" . $search_key . "%' or
                id_fk_warehouse like '%" . $search_key . "%' or
                id_fk_user like '%" . $search_key . "%' or
                warehouse_admin_status like '%" . $search_key . "%' or
                warehouse_admin_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_last_modified,user_name,user_email
        from " . $this->tbl_name . " 
        inner join mstr_user on mstr_user.id_pk_user = " . $this->tbl_name . ".id_fk_user
        inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = " . $this->tbl_name . ".id_fk_warehouse
        where warehouse_admin_status = ? and id_fk_warehouse = ? and user_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif", $this->id_fk_warehouse, "aktif"
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_warehouse_admin
        from " . $this->tbl_name . " 
        inner join mstr_user on mstr_user.id_pk_user = " . $this->tbl_name . ".id_fk_user
        inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = " . $this->tbl_name . ".id_fk_warehouse
        where warehouse_admin_status = ? and id_fk_warehouse = ? and user_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function list_gudang_admin($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = 20)
  {
    $this->columns = array();
    $this->set_column("warehouse_nama", "nama warehouse", "required");
    $this->set_column("warehouse_alamat", "alamat", "required");
    $this->set_column("warehouse_notelp", "no telpon", "required");
    $this->set_column("warehouse_desc", "deskripsi", "required");
    $this->set_column("warehouse_status", "status", "required");
    $this->set_column("warehouse_last_modified", "last modified", "required");

    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            ( 
                warehouse_nama like '%" . $search_key . "%' or 
                warehouse_alamat like '%" . $search_key . "%' or 
                warehouse_notelp like '%" . $search_key . "%' or 
                warehouse_desc like '%" . $search_key . "%' or 
                warehouse_status like '%" . $search_key . "%' or 
                warehouse_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_last_modified
        from " . $this->tbl_name . " 
        inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = " . $this->tbl_name . ".id_fk_warehouse
        where warehouse_status = ? and id_fk_user = ? and warehouse_admin_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif", $this->id_fk_user, "aktif"
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_last_modified
        from " . $this->tbl_name . " 
        inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = " . $this->tbl_name . ".id_fk_warehouse
        where warehouse_status = ? and id_fk_user = ? and warehouse_admin_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function insert()
  {
    if ($this->check_insert()) {
      $data = array(
        "id_fk_warehouse" => $this->id_fk_warehouse,
        "id_fk_user" => $this->id_fk_user,
        "warehouse_admin_status" => $this->warehouse_admin_status,
        "warehouse_admin_create_date" => $this->warehouse_admin_create_date,
        "warehouse_admin_last_modified" => $this->warehouse_admin_last_modified,
        "id_create_data" => $this->id_create_data,
        "id_last_modified" => $this->id_last_modified,
      );
      $id_hasil_insert = insertrow($this->tbl_name, $data);

      $log_all_msg = "Data Admin Warehouse baru ditambahkan. Waktu penambahan: $this->warehouse_admin_create_date";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

      $log_all_data_changes = "[ID Admin Warehouse: $id_hasil_insert][ID Warehouse: $this->id_fk_warehouse][ID User: $this->id_fk_user][Status: $this->warehouse_admin_status][Waktu Ditambahkan: $this->warehouse_admin_create_date][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_create_data;
      $log_all_tgl = $this->warehouse_admin_create_date;

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
  public function update()
  {
    if ($this->check_update()) {
      $where = array(
        "id_pk_warehouse_admin" => $this->id_pk_warehouse_admin
      );
      $data = array(
        "id_fk_warehouse" => $this->id_fk_warehouse,
        "id_fk_user" => $this->id_fk_user,
        "warehouse_admin_last_modified" => $this->warehouse_admin_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updaterow($this->tbl_name, $data, $where);
        $id_pk = $this->id_pk_warehouse_admin;
        $log_all_msg = "Data Admin warehouse dengan ID: $id_pk diubah. Waktu diubah: $this->warehouse_admin_last_modified . Data berubah menjadi: ";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

        $log_all_data_changes = "[ID Admin Warehouse: $id_pk][ID Warehouse: $this->id_fk_warehouse][ID User: $this->id_fk_user][Waktu Ditambahkan: $this->warehouse_admin_create_date][Oleh: $nama_user]";
        $log_all_it = "";
        $log_all_user = $this->id_last_modified;
        $log_all_tgl = $this->warehouse_admin_last_modified;

        $data_log = array(
          "log_all_msg" => $log_all_msg,
          "log_all_data_changes" => $log_all_data_changes,
          "log_all_it" => $log_all_it,
          "log_all_user" => $log_all_user,
          "log_all_tgl" => $log_all_tgl
        );
        insertrow("log_all", $data_log);
      return true;
    }
    return false;
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_warehouse_admin" => $this->id_pk_warehouse_admin
      );
      $data = array(
        "warehouse_admin_status" => "nonaktif",
        "warehouse_admin_last_modified" => $this->warehouse_admin_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    if ($this->id_fk_warehouse == "") {
      return false;
    }
    if ($this->id_fk_user == "") {
      return false;
    }
    if ($this->warehouse_admin_status == "") {
      return false;
    }
    if ($this->warehouse_admin_create_date == "") {
      return false;
    }
    if ($this->warehouse_admin_last_modified == "") {
      return false;
    }
    if ($this->id_create_data == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_update()
  {
    if ($this->id_pk_warehouse_admin == "") {
      return false;
    }
    if ($this->id_fk_warehouse == "") {
      return false;
    }
    if ($this->id_fk_user == "") {
      return false;
    }
    if ($this->warehouse_admin_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {

    if ($this->id_pk_warehouse_admin == "") {
      return false;
    }
    if ($this->warehouse_admin_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($id_fk_warehouse, $id_fk_user, $warehouse_admin_status)
  {
    if (!$this->set_id_fk_warehouse($id_fk_warehouse)) {
      return false;
    }
    if (!$this->set_id_fk_user($id_fk_user)) {
      return false;
    }
    if (!$this->set_warehouse_admin_status($warehouse_admin_status)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_warehouse_admin, $id_fk_warehouse, $id_fk_user)
  {
    if (!$this->set_id_pk_warehouse_admin($id_pk_warehouse_admin)) {
      return false;
    }
    if (!$this->set_id_fk_warehouse($id_fk_warehouse)) {
      return false;
    }
    if (!$this->set_id_fk_user($id_fk_user)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_warehouse_admin)
  {
    if (!$this->set_id_pk_warehouse_admin($id_pk_warehouse_admin)) {
      return false;
    }
    return true;
  }
  public function get_id_pk_warehouse_admin()
  {
    return $this->id_pk_warehouse_admin;
  }
  public function get_id_fk_warehouse()
  {
    return $this->id_fk_warehouse;
  }
  public function get_id_fk_user()
  {
    return $this->id_fk_user;
  }
  public function get_warehouse_admin_status()
  {
    return $this->warehouse_admin_status;
  }
  public function set_id_pk_warehouse_admin($id_pk_warehouse_admin)
  {
    if ($id_pk_warehouse_admin != "") {
      $this->id_pk_warehouse_admin = $id_pk_warehouse_admin;
      return true;
    }
    return false;
  }
  public function set_id_fk_warehouse($id_fk_warehouse)
  {
    if ($id_fk_warehouse != "") {
      $this->id_fk_warehouse = $id_fk_warehouse;
      return true;
    }
    return false;
  }
  public function set_id_fk_user($id_fk_user)
  {
    if ($id_fk_user != "") {
      $this->id_fk_user = $id_fk_user;
      return true;
    }
    return false;
  }
  public function set_warehouse_admin_status($warehouse_admin_status)
  {
    if ($warehouse_admin_status != "") {
      $this->warehouse_admin_status = $warehouse_admin_status;
      return true;
    }
    return false;
  }
}
