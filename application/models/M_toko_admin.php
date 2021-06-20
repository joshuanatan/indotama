<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");

class m_toko_admin extends ci_model
{
  private $tbl_name = "tbl_toko_admin";
  private $columns = array();
  private $id_pk_toko_admin;
  private $id_fk_toko;
  private $id_fk_user;
  private $toko_admin_status;
  private $toko_admin_create_date;
  private $toko_admin_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->columns = array();
    $this->set_column("user_name", "user name", "required");
    $this->set_column("user_email", "email", "required");
    $this->set_column("toko_admin_status", "status", "required");
    $this->set_column("toko_admin_last_modified", "last modified", "required");
    $this->toko_admin_create_date = date("y-m-d h:i:s");
    $this->toko_admin_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  public function install()
  {
    $sql = "
        drop table if exists tbl_toko_admin;
        create table tbl_toko_admin(
            id_pk_toko_admin int primary key auto_increment,
            id_fk_toko int,
            id_fk_user int,
            toko_admin_status varchar(15),
            toko_admin_create_date datetime,
            toko_admin_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_toko_admin_log;
        create table tbl_toko_admin_log(
            id_pk_toko_admin_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_toko_admin int,
            id_fk_toko int,
            id_fk_user int,
            toko_admin_status varchar(15),
            toko_admin_create_date datetime,
            toko_admin_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_toko_admin;
        delimiter $$
        create trigger trg_after_insert_toko_admin
        after insert on tbl_toko_admin
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.toko_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_toko_admin_log(executed_function,id_pk_toko_admin,id_fk_toko,id_fk_user,toko_admin_status,toko_admin_create_date,toko_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_toko_admin,new.id_fk_toko,new.id_fk_user,new.toko_admin_status,new.toko_admin_create_date,new.toko_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_toko_admin;
        delimiter $$
        create trigger trg_after_update_toko_admin
        after update on tbl_toko_admin
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.toko_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_toko_admin_log(executed_function,id_pk_toko_admin,id_fk_toko,id_fk_user,toko_admin_status,toko_admin_create_date,toko_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_toko_admin,new.id_fk_toko,new.id_fk_user,new.toko_admin_status,new.toko_admin_create_date,new.toko_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
                id_pk_toko_admin like '%" . $search_key . "%' or
                id_fk_toko like '%" . $search_key . "%' or
                id_fk_user like '%" . $search_key . "%' or
                toko_admin_status like '%" . $search_key . "%' or
                toko_admin_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_toko_admin,id_fk_toko,id_fk_user,toko_admin_status,toko_admin_last_modified,user_name,user_email
        from " . $this->tbl_name . " 
        inner join mstr_user on mstr_user.id_pk_user = " . $this->tbl_name . ".id_fk_user
        inner join mstr_toko on mstr_toko.id_pk_toko = " . $this->tbl_name . ".id_fk_toko
        where toko_admin_status = ? and id_fk_toko = ? and user_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif", $this->id_fk_toko, "aktif"
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_toko_admin
        from " . $this->tbl_name . " 
        inner join mstr_user on mstr_user.id_pk_user = " . $this->tbl_name . ".id_fk_user
        inner join mstr_toko on mstr_toko.id_pk_toko = " . $this->tbl_name . ".id_fk_toko
        where toko_admin_status = ? and id_fk_toko = ? and user_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function set_toko_admin_columns()
  {
    $this->columns = array();
    $this->set_column("toko_nama", "nama toko", true);
    $this->set_column("toko_kode", "kode toko", false);
    $this->set_column("toko_status", "status toko", false);
    $this->set_column("toko_last_modified", "last modified", false);
  }
  public function list_toko_admin($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = 20)
  {
    $this->set_toko_admin_columns();
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            ( 
                id_pk_toko like '%" . $search_key . "%' or
                toko_nama like '%" . $search_key . "%' or
                toko_kode like '%" . $search_key . "%' or
                toko_status like '%" . $search_key . "%' or
                toko_create_date like '%" . $search_key . "%' or
                toko_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_toko,toko_nama,toko_kode,toko_status,toko_create_date,toko_last_modified
        from " . $this->tbl_name . " 
        inner join mstr_toko on mstr_toko.id_pk_toko = " . $this->tbl_name . ".id_fk_toko
        where toko_status = ? and id_fk_user = ? and toko_admin_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif", $this->id_fk_user, "aktif"
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_toko
        from " . $this->tbl_name . " 
        inner join mstr_toko on mstr_toko.id_pk_toko = " . $this->tbl_name . ".id_fk_toko
        where toko_status = ? and id_fk_user = ? and toko_admin_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function insert()
  {
    if ($this->check_insert()) {
      $data = array(
        "id_fk_toko" => $this->id_fk_toko,
        "id_fk_user" => $this->id_fk_user,
        "toko_admin_status" => $this->toko_admin_status,
        "toko_admin_create_date" => $this->toko_admin_create_date,
        "toko_admin_last_modified" => $this->toko_admin_last_modified,
        "id_create_data" => $this->id_create_data,
        "id_last_modified" => $this->id_last_modified,
      );
      $id_hasil_insert = insertrow($this->tbl_name, $data);

      $log_all_msg = "Data Admin Toko baru ditambahkan. Waktu penambahan: $this->toko_admin_create_date";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

      $log_all_data_changes = "[ID Admin Toko: $id_hasil_insert][ID Toko: $this->id_fk_toko][ID User: $this->id_fk_user][Status: $this->toko_admin_status][Waktu Ditambahkan: $this->toko_admin_create_date][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_create_data;
      $log_all_tgl = $this->toko_admin_create_date;

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
        "id_pk_toko_admin" => $this->id_pk_toko_admin
      );
      $data = array(
        "id_fk_user" => $this->id_fk_user,
        "toko_admin_last_modified" => $this->toko_admin_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updateRow($this->tbl_name, $data, $where);
      $id_pk = $this->id_pk_toko_admin;
      $log_all_msg = "Data Admin Toko dengan ID: $id_pk diubah. Waktu diubah: $this->toko_admin_last_modified . Data berubah menjadi: ";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

      $log_all_data_changes = "[ID Admin Toko: $id_pk][ID User: $this->id_fk_user][Waktu Diedit: $this->toko_admin_last_modified][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_last_modified;
      $log_all_tgl = $this->toko_admin_last_modified;

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
        "id_pk_toko_admin" => $this->id_pk_toko_admin
      );
      $data = array(
        "toko_admin_status" => "nonaktif",
        "toko_admin_last_modified" => $this->toko_admin_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    return true;
  }
  public function check_update()
  {
    return true;
  }
  public function check_delete()
  {
    return true;
  }
  public function set_insert($id_fk_toko, $id_fk_user, $toko_admin_status)
  {
    $this->set_id_fk_toko($id_fk_toko);
    $this->set_id_fk_user($id_fk_user);
    $this->set_toko_admin_status($toko_admin_status);
    return true;
  }
  public function set_update($id_pk_toko_admin, $id_fk_user)
  {
    $this->set_id_pk_toko_admin($id_pk_toko_admin);
    $this->set_id_fk_user($id_fk_user);
    return true;
  }
  public function set_delete($id_pk_toko_admin)
  {
    $this->set_id_pk_toko_admin($id_pk_toko_admin);
    return true;
  }
  public function set_id_pk_toko_admin($id_pk_toko_admin)
  {
    $this->id_pk_toko_admin = $id_pk_toko_admin;
    return true;
  }
  public function set_id_fk_toko($id_fk_toko)
  {
    $this->id_fk_toko = $id_fk_toko;
    return true;
  }
  public function set_id_fk_user($id_fk_user)
  {
    $this->id_fk_user = $id_fk_user;
    return true;
  }
  public function set_toko_admin_status($toko_admin_status)
  {
    $this->toko_admin_status = $toko_admin_status;
    return true;
  }
}
