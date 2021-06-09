<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_penawaran extends ci_model
{
  private $tbl_name = "mstr_penawaran";
  private $columns = array();
  private $id_pk_penawaran;
  private $penawaran_subject;
  private $penawaran_content;
  private $penawaran_notes;
  private $penawaran_file;
  private $penawaran_refrensi;
  private $penawaran_tgl;
  private $penawaran_status;
  private $id_fk_cabang;
  private $penawaran_create_date;
  private $penawaran_last_modified;
  private $id_create_date;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->set_column("cust_perusahaan", "Customer", false);
    $this->set_column("penawaran_subject", "Penawaran", false);
    $this->set_column("penawaran_content", "Penawaran Detil", true);
    $this->set_column("penawaran_notes", "Catatan Penawaran", false);
    $this->set_column("penawaran_tgl", "Tanggal Penawaran", false);
    $this->set_column("penawaran_status", "Status", false);

    $this->penawaran_create_date = date("y-m-d h:i:s");
    $this->penawaran_last_modified = date("y-m-d h:i:s");
    $this->id_create_date = $this->session->id_user;
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
    $sql = "
        drop table if exists mstr_penawaran;
        create table mstr_penawaran(
            id_pk_penawaran int primary key auto_increment,
            penawaran_subject varchar(100),
            penawaran_content varchar(100),
            penawaran_notes varchar(100),
            penawaran_file varchar(100),
            penawaran_tgl datetime,
            penawaran_refrensi varchar(100),
            penawaran_status varchar(30),
            id_fk_cabang int,
            penawaran_create_date datetime,
            penawaran_last_modified datetime,
            id_create_date int,
            id_last_modified int
        );
        drop table if exists mstr_penawaran_log;
        create table mstr_penawaran_log(
            id_pk_penawaran_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_penawaran int,
            penawaran_subject varchar(100),
            penawaran_content varchar(100),
            penawaran_notes varchar(100),
            penawaran_file varchar(100),
            penawaran_tgl datetime,
            penawaran_refrensi varchar(100),
            penawaran_status varchar(30),
            id_fk_cabang int,
            penawaran_create_date datetime,
            penawaran_last_modified datetime,
            id_create_date int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_penawaran;
        delimiter $$
        create trigger trg_after_insert_penawaran
        after insert on mstr_penawaran
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penawaran_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penawaran_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penawaran_log(executed_function,id_pk_penawaran,penawaran_subject,penawaran_content,penawaran_notes,penawaran_file,penawaran_tgl,penawaran_refrensi,penawaran_status,id_fk_cabang,penawaran_create_date,penawaran_last_modified,id_create_date,id_last_modified,id_log_all) values ('after insert',new.id_pk_penawaran,new.penawaran_subject,new.penawaran_content,new.penawaran_notes,new.penawaran_file,new.penawaran_tgl,new.penawaran_refrensi,new.penawaran_status,new.id_fk_cabang,new.penawaran_create_date,new.penawaran_last_modified,new.id_create_date,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_penawaran;
        delimiter $$
        create trigger trg_after_update_penawaran
        after update on mstr_penawaran
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penawaran_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penawaran_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penawaran_log(executed_function,id_pk_penawaran,penawaran_subject,penawaran_content,penawaran_notes,penawaran_file,penawaran_tgl,penawaran_refrensi,penawaran_status,id_fk_cabang,penawaran_create_date,penawaran_last_modified,id_create_date,id_last_modified,id_log_all) values ('after update',new.id_pk_penawaran,new.penawaran_subject,new.penawaran_content,new.penawaran_notes,new.penawaran_file,new.penawaran_tgl,new.penawaran_refrensi,new.penawaran_status,new.id_fk_cabang,new.penawaran_create_date,new.penawaran_last_modified,new.id_create_date,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
    executequery($sql);
  }
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "")
  {
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
        (
          id_pk_penawaran like '%" . $search_key . "%' or 
          penawaran_subject like '%" . $search_key . "%' or 
          penawaran_content like '%" . $search_key . "%' or 
          penawaran_notes like '%" . $search_key . "%' or 
          penawaran_tgl like '%" . $search_key . "%' or 
          penawaran_refrensi like '%" . $search_key . "%' or 
          penawaran_status like '%" . $search_key . "%' or 
          cust_name like '%" . $search_key . "%' or 
          cust_perusahaan like '%" . $search_key . "%' or 
          id_fk_cabang like '%" . $search_key . "%' 
        )";
    }
    $query = "
      select id_pk_penawaran, penawaran_subject, penawaran_content, penawaran_notes, penawaran_tgl, penawaran_refrensi, penawaran_status, id_fk_cabang,cust_perusahaan
      from mstr_penawaran 
      inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penawaran.penawaran_refrensi
      where penawaran_status = ? and id_fk_cabang = ? " . $search_query . "  
      order by " . $order_by . " " . $order_direction . " 
      limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif", $this->session->id_cabang
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_penawaran
        from mstr_penawaran 
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penawaran.penawaran_refrensi
        where penawaran_status = ? and id_fk_cabang = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function insert($penawaran_subject, $penawaran_content, $penawaran_notes, $penawaran_refrensi, $penawaran_tgl, $penawaran_status, $id_fk_cabang)
  {
    $data = array(
      "penawaran_subject" => $penawaran_subject,
      "penawaran_content" => $penawaran_content,
      "penawaran_notes" => $penawaran_notes,
      "penawaran_refrensi" => $penawaran_refrensi,
      "penawaran_tgl" => $penawaran_tgl,
      "penawaran_status" => $penawaran_status,
      "id_fk_cabang" => $id_fk_cabang,
      "penawaran_create_date" => $this->penawaran_create_date,
      "penawaran_last_modified" => $this->penawaran_last_modified,
      "id_create_date" => $this->id_create_date,
      "id_last_modified" => $this->id_last_modified
    );

    $id_hasil_insert = insertrow($this->tbl_name, $data);

    $log_all_msg = "Data Penawaran baru ditambahkan. Waktu penambahan: $this->penawaran_create_date";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

    $log_all_data_changes = "[ID Penawaran: $id_hasil_insert][Subject: $this->penawaran_subject][Content: $this->penawaran_content][Notes: $this->penawaran_notes][Referensi: $this->penawaran_refrensi][Tanggal: $this->penawaran_tgl][Status: $this->penawaran_status][ID Cabang: $this->id_fk_cabang][Waktu Ditambahkan: $this->penawaran_create_date[Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_last_modified;
    $log_all_tgl = $this->penawaran_create_date;

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
  public function update($id_pk_penawaran, $penawaran_subject, $penawaran_content, $penawaran_notes, $penawaran_refrensi, $penawaran_tgl)
  {
    $where = array(
      "id_pk_penawaran" => $id_pk_penawaran,
    );
    $data = array(
      "penawaran_subject" => $penawaran_subject,
      "penawaran_content" => $penawaran_content,
      "penawaran_notes" => $penawaran_notes,
      "penawaran_refrensi" => $penawaran_refrensi,
      "penawaran_tgl" => $penawaran_tgl,
      "penawaran_last_modified" => $this->penawaran_last_modified,
      "id_last_modified" => $this->id_last_modified
    );
    updateRow($this->tbl_name, $data, $where);
    return true;
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_penawaran" => $this->id_pk_penawaran,
      );
      $data = array(
        "penawaran_status" => "nonaktif",
        "penawaran_last_modified" => $this->penawaran_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    if ($this->penawaran_subject == "") {
      return false;
    }
    if ($this->penawaran_content == "") {
      return false;
    }
    if ($this->penawaran_notes == "") {
      return false;
    }
    if ($this->penawaran_file == "") {
      return false;
    }
    if ($this->penawaran_refrensi == "") {
      return false;
    }
    if ($this->penawaran_tgl == "") {
      return false;
    }
    if ($this->penawaran_status == "") {
      return false;
    }
    if ($this->id_fk_cabang == "") {
      return false;
    }
    if ($this->penawaran_create_date == "") {
      return false;
    }
    if ($this->penawaran_last_modified == "") {
      return false;
    }
    if ($this->id_create_date == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_update()
  {
    if ($this->id_pk_penawaran == "") {
      return false;
    }
    if ($this->penawaran_subject == "") {
      return false;
    }
    if ($this->penawaran_content == "") {
      return false;
    }
    if ($this->penawaran_notes == "") {
      return false;
    }
    if ($this->penawaran_file == "") {
      return false;
    }
    if ($this->penawaran_tgl == "") {
      return false;
    }
    if ($this->penawaran_refrensi == "") {
      return false;
    }
    if ($this->penawaran_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {
    if ($this->id_pk_penawaran == "") {
      return false;
    }
    if ($this->penawaran_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($penawaran_subject, $penawaran_content, $penawaran_notes, $penawaran_file, $penawaran_refrensi, $penawaran_tgl, $penawaran_status, $id_fk_cabang)
  {
    if (!$this->set_penawaran_subject($penawaran_subject)) {
      return false;
    }
    if (!$this->set_penawaran_content($penawaran_content)) {
      return false;
    }
    if (!$this->set_penawaran_notes($penawaran_notes)) {
      return false;
    }
    if (!$this->set_penawaran_file($penawaran_file)) {
      return false;
    }
    if (!$this->set_penawaran_refrensi($penawaran_refrensi)) {
      return false;
    }
    if (!$this->set_penawaran_tgl($penawaran_tgl)) {
      return false;
    }
    if (!$this->set_penawaran_status($penawaran_status)) {
      return false;
    }
    if (!$this->set_id_fk_cabang($id_fk_cabang)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_penawaran, $penawaran_subject, $penawaran_content, $penawaran_notes, $penawaran_file, $penawaran_refrensi, $penawaran_tgl)
  {
    if (!$this->set_id_pk_penawaran($id_pk_penawaran)) {
      return false;
    }
    if (!$this->set_penawaran_subject($penawaran_subject)) {
      return false;
    }
    if (!$this->set_penawaran_content($penawaran_content)) {
      return false;
    }
    if (!$this->set_penawaran_notes($penawaran_notes)) {
      return false;
    }
    if (!$this->set_penawaran_file($penawaran_file)) {
      return false;
    }
    if (!$this->set_penawaran_refrensi($penawaran_refrensi)) {
      return false;
    }
    if (!$this->set_penawaran_tgl($penawaran_tgl)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_penawaran)
  {
    if (!$this->set_id_pk_penawaran($id_pk_penawaran)) {
      return false;
    }
    return true;
  }
  public function set_id_pk_penawaran($id_pk_penawaran)
  {
    if ($id_pk_penawaran != "") {
      $this->id_pk_penawaran = $id_pk_penawaran;
      return true;
    }
    return false;
  }
  public function set_penawaran_subject($penawaran_subject)
  {
    if ($penawaran_subject != "") {
      $this->penawaran_subject = $penawaran_subject;
      return true;
    }
    return false;
  }
  public function set_penawaran_content($penawaran_content)
  {
    if ($penawaran_content != "") {
      $this->penawaran_content = $penawaran_content;
      return true;
    }
    return false;
  }
  public function set_penawaran_notes($penawaran_notes)
  {
    if ($penawaran_notes != "") {
      $this->penawaran_notes = $penawaran_notes;
      return true;
    }
    return false;
  }
  public function set_penawaran_file($penawaran_file)
  {
    if ($penawaran_file != "") {
      $this->penawaran_file = $penawaran_file;
      return true;
    }
    return false;
  }
  public function set_penawaran_refrensi($penawaran_refrensi)
  {
    if ($penawaran_refrensi != "") {
      $this->penawaran_refrensi = $penawaran_refrensi;
      return true;
    }
    return false;
  }
  public function set_penawaran_tgl($penawaran_tgl)
  {
    if ($penawaran_tgl != "") {
      $this->penawaran_tgl = $penawaran_tgl;
      return true;
    }
    return false;
  }
  public function set_penawaran_status($penawaran_status)
  {
    if ($penawaran_status != "") {
      $this->penawaran_status = $penawaran_status;
      return true;
    }
    return false;
  }
  public function set_id_fk_cabang($id_fk_cabang)
  {
    if ($id_fk_cabang != "") {
      $this->id_fk_cabang = $id_fk_cabang;
      return true;
    }
    return false;
  }
}
