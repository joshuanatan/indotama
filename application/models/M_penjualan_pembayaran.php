<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_penjualan_pembayaran extends CI_Model
{
  private $tbl_name = "tbl_penjualan_pembayaran";
  private $columns = array();
  private $id_pk_penjualan_pembayaran;
  private $id_fk_penjualan;
  private $penjualan_pmbyrn_nama;
  private $penjualan_pmbyrn_persen;
  private $penjualan_pmbyrn_nominal;
  private $penjualan_pmbyrn_notes;
  private $penjualan_pmbyrn_dateline;
  private $penjualan_pmbyrn_status;
  private $penjualan_pmbyrn_create_date;
  private $penjualan_pmbyrn_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->penjualan_pmbyrn_create_date = date("y-m-d h:i:s");
    $this->penjualan_pmbyrn_last_modified = date("y-m-d h:i:s");
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
  public function install()
  {
    $sql = "
        drop table if exists tbl_penjualan_pembayaran;
        create table tbl_penjualan_pembayaran(
            id_pk_penjualan_pembayaran int primary key auto_increment,
            id_fk_penjualan int,
            penjualan_pmbyrn_nama varchar(100),
            penjualan_pmbyrn_persen double,
            penjualan_pmbyrn_nominal int,
            penjualan_pmbyrn_notes varchar(200),
            penjualan_pmbyrn_dateline datetime,
            penjualan_pmbyrn_status varchar(15),
            penjualan_pmbyrn_create_date datetime,
            penjualan_pmbyrn_last_modified datetime,
            id_create_data int,
            id_last_modified int                   
        );
        drop table if exists tbl_penjualan_pembayaran_log;
        create table tbl_penjualan_pembayaran_log(
            id_pk_penjualan_pembayaran_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_penjualan_pembayaran int,
            id_fk_penjualan int,
            penjualan_pmbyrn_nama varchar(100),
            penjualan_pmbyrn_persen double,
            penjualan_pmbyrn_nominal int,
            penjualan_pmbyrn_notes varchar(200),
            penjualan_pmbyrn_dateline datetime,
            penjualan_pmbyrn_status varchar(15),
            penjualan_pmbyrn_create_date datetime,
            penjualan_pmbyrn_last_modified datetime,
            id_create_data int,
            id_last_modified int,                   
            id_log_all int                   
        );
        drop trigger if exists trg_after_insert_penjualan_pembayaran;
        delimiter $$
        create trigger trg_after_insert_penjualan_pembayaran
        after insert on tbl_penjualan_pembayaran
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_pmbyrn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penjualan_pmbyrn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_pembayaran_log(executed_function,id_pk_penjualan_pembayaran,id_fk_penjualan,penjualan_pmbyrn_nama,penjualan_pmbyrn_persen,penjualan_pmbyrn_nominal,penjualan_pmbyrn_notes,penjualan_pmbyrn_dateline,penjualan_pmbyrn_status,penjualan_pmbyrn_create_date,penjualan_pmbyrn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan_pembayaran,new.id_fk_penjualan,new.penjualan_pmbyrn_nama,new.penjualan_pmbyrn_persen,new.penjualan_pmbyrn_nominal,new.penjualan_pmbyrn_notes,new.penjualan_pmbyrn_dateline,new.penjualan_pmbyrn_status,new.penjualan_pmbyrn_create_date,new.penjualan_pmbyrn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_penjualan_pembayaran;
        delimiter $$
        create trigger trg_after_update_penjualan_pembayaran
        after update on tbl_penjualan_pembayaran
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_pmbyrn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penjualan_pmbyrn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_pembayaran_log(executed_function,id_pk_penjualan_pembayaran,id_fk_penjualan,penjualan_pmbyrn_nama,penjualan_pmbyrn_persen,penjualan_pmbyrn_nominal,penjualan_pmbyrn_notes,penjualan_pmbyrn_dateline,penjualan_pmbyrn_status,penjualan_pmbyrn_create_date,penjualan_pmbyrn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan_pembayaran,new.id_fk_penjualan,new.penjualan_pmbyrn_nama,new.penjualan_pmbyrn_persen,new.penjualan_pmbyrn_nominal,new.penjualan_pmbyrn_notes,new.penjualan_pmbyrn_dateline,new.penjualan_pmbyrn_status,new.penjualan_pmbyrn_create_date,new.penjualan_pmbyrn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
  }
  public function list_data()
  {
    $where = array(
      "id_fk_penjualan" => $this->id_fk_penjualan,
      "penjualan_pmbyrn_status !=" => "nonaktif"
    );
    $field = array(
      "id_pk_penjualan_pembayaran",
      "penjualan_pmbyrn_nama",
      "penjualan_pmbyrn_persen",
      "penjualan_pmbyrn_nominal",
      "penjualan_pmbyrn_notes",
      "penjualan_pmbyrn_dateline",
      "penjualan_pmbyrn_status",
      "penjualan_pmbyrn_last_modified",
      "id_last_modified"
    );
    return selectRow($this->tbl_name, $where, $field);
  }
  public function insert($id_fk_penjualan, $penjualan_pmbyrn_nama, $penjualan_pmbyrn_persen, $penjualan_pmbyrn_nominal, $penjualan_pmbyrn_notes, $penjualan_pmbyrn_dateline, $penjualan_pmbyrn_status)
  {
    $data = array(
      "id_fk_penjualan" => $id_fk_penjualan,
      "penjualan_pmbyrn_nama" => $penjualan_pmbyrn_nama,
      "penjualan_pmbyrn_persen" => $penjualan_pmbyrn_persen,
      "penjualan_pmbyrn_nominal" => $penjualan_pmbyrn_nominal,
      "penjualan_pmbyrn_notes" => $penjualan_pmbyrn_notes,
      "penjualan_pmbyrn_dateline" => $penjualan_pmbyrn_dateline,
      "penjualan_pmbyrn_status" => $penjualan_pmbyrn_status,
      "penjualan_pmbyrn_create_date" => $this->penjualan_pmbyrn_create_date,
      "penjualan_pmbyrn_last_modified" => $this->penjualan_pmbyrn_last_modified,
      "id_create_data" => $this->id_create_data,
      "id_last_modified" => $this->id_last_modified
    );
    $id_hasil_insert = insertrow($this->tbl_name, $data);

    $log_all_msg = "Data Pembayaran Penjualan baru ditambahkan. Waktu penambahan: $this->penjualan_pmbyrn_create_date";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

    $log_all_data_changes = "[ID Pembayaran Penjualan: $id_hasil_insert][ID Penjualan: $id_fk_penjualan][Nama: $penjualan_pmbyrn_nama][Persen Pembayaran: $penjualan_pmbyrn_persen][Nominal Pembayaran: $penjualan_pmbyrn_nominal][Notes: $penjualan_pmbyrn_notes][Dateline: $penjualan_pmbyrn_dateline][Status: $penjualan_pmbyrn_status][Waktu Ditambahkan: $this->penjualan_pmbyrn_create_date][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_create_data;
    $log_all_tgl = $this->penjualan_pmbyrn_create_date;

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
  public function update($id_pk_penjualan_pembayaran, $penjualan_pmbyrn_nama, $penjualan_pmbyrn_persen, $penjualan_pmbyrn_nominal, $penjualan_pmbyrn_notes, $penjualan_pmbyrn_dateline, $penjualan_pmbyrn_status)
  {
    $where = array(
      "id_pk_penjualan_pembayaran" => $id_pk_penjualan_pembayaran,
    );
    $data = array(
      "penjualan_pmbyrn_nama" => $penjualan_pmbyrn_nama,
      "penjualan_pmbyrn_persen" => $penjualan_pmbyrn_persen,
      "penjualan_pmbyrn_nominal" => $penjualan_pmbyrn_nominal,
      "penjualan_pmbyrn_notes" => $penjualan_pmbyrn_notes,
      "penjualan_pmbyrn_dateline" => $penjualan_pmbyrn_dateline,
      "penjualan_pmbyrn_last_modified" => $this->penjualan_pmbyrn_last_modified,
      "penjualan_pmbyrn_status" => $penjualan_pmbyrn_status,
      "id_last_modified" => $this->id_last_modified
    );
    updateRow($this->tbl_name, $data, $where);
        $id_pk = $this->id_pk_penjualan_pembayaran;
        $log_all_msg = "Data Pembayaran Penjualan dengan ID: $id_pk diubah. Waktu diubah: $this->penjualan_pmbyrn_last_modified . Data berubah menjadi: ";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

        $log_all_data_changes = "[ID Pembayaran Penjualan: $id_pk][Nama: $penjualan_pmbyrn_nama][Persen Pembayaran: $penjualan_pmbyrn_persen][Nominal Pembayaran: $penjualan_pmbyrn_nominal][Notes: $penjualan_pmbyrn_notes][Dateline: $penjualan_pmbyrn_dateline][Status: $penjualan_pmbyrn_status][Waktu Diedit: $this->penjualan_pmbyrn_last_modified][Oleh: $nama_user]";
        $log_all_it = "";
        $log_all_user = $this->id_last_modified;
        $log_all_tgl = $this->penjualan_pmbyrn_last_modified;

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
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_penjualan_pembayaran" => $this->id_pk_penjualan_pembayaran,
      );
      $data = array(
        "penjualan_pmbyrn_status" => "nonaktif",
        "penjualan_pmbyrn_last_modified" => $this->penjualan_pmbyrn_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updateRow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    if ($this->id_fk_penjualan == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_nama == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_persen == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_nominal == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_notes == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_dateline == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_status == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_create_date == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_last_modified == "") {
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
    if ($this->id_pk_penjualan_pembayaran == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_nama == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_persen == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_nominal == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_notes == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_dateline == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {
    if ($this->id_pk_penjualan_pembayaran == "") {
      return false;
    }
    if ($this->penjualan_pmbyrn_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($id_fk_penjualan, $penjualan_pmbyrn_nama, $penjualan_pmbyrn_persen, $penjualan_pmbyrn_nominal, $penjualan_pmbyrn_notes, $penjualan_pmbyrn_dateline, $penjualan_pmbyrn_status)
  {
    if (!$this->set_id_fk_penjualan($id_fk_penjualan)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_nama($penjualan_pmbyrn_nama)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_persen($penjualan_pmbyrn_persen)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_nominal($penjualan_pmbyrn_nominal)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_notes($penjualan_pmbyrn_notes)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_dateline($penjualan_pmbyrn_dateline)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_status($penjualan_pmbyrn_status)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_penjualan_pembayaran, $penjualan_pmbyrn_nama, $penjualan_pmbyrn_persen, $penjualan_pmbyrn_nominal, $penjualan_pmbyrn_notes, $penjualan_pmbyrn_dateline, $penjualan_pmbyrn_status)
  {
    if (!$this->set_id_pk_penjualan_pembayaran($id_pk_penjualan_pembayaran)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_nama($penjualan_pmbyrn_nama)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_persen($penjualan_pmbyrn_persen)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_nominal($penjualan_pmbyrn_nominal)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_notes($penjualan_pmbyrn_notes)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_dateline($penjualan_pmbyrn_dateline)) {
      return false;
    }
    if (!$this->set_penjualan_pmbyrn_status($penjualan_pmbyrn_status)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_penjualan_pembayaran)
  {
    if (!$this->set_id_pk_penjualan_pembayaran($id_pk_penjualan_pembayaran)) {
      return false;
    }
    return true;
  }
  public function set_id_pk_penjualan_pembayaran($id_pk_penjualan_pembayaran)
  {
    if ($id_pk_penjualan_pembayaran != "") {
      $this->id_pk_penjualan_pembayaran = $id_pk_penjualan_pembayaran;
      return true;
    }
    return false;
  }
  public function set_id_fk_penjualan($id_fk_penjualan)
  {
    if ($id_fk_penjualan != "") {
      $this->id_fk_penjualan = $id_fk_penjualan;
      return true;
    }
    return false;
  }
  public function set_penjualan_pmbyrn_nama($penjualan_pmbyrn_nama)
  {
    if ($penjualan_pmbyrn_nama != "") {
      $this->penjualan_pmbyrn_nama = $penjualan_pmbyrn_nama;
      return true;
    }
    return false;
  }
  public function set_penjualan_pmbyrn_persen($penjualan_pmbyrn_persen)
  {
    if ($penjualan_pmbyrn_persen != "") {
      $this->penjualan_pmbyrn_persen = $penjualan_pmbyrn_persen;
      return true;
    }
    return false;
  }
  public function set_penjualan_pmbyrn_nominal($penjualan_pmbyrn_nominal)
  {
    if ($penjualan_pmbyrn_nominal != "") {
      $this->penjualan_pmbyrn_nominal = $penjualan_pmbyrn_nominal;
      return true;
    }
    return false;
  }
  public function set_penjualan_pmbyrn_notes($penjualan_pmbyrn_notes)
  {
    if ($penjualan_pmbyrn_notes != "") {
      $this->penjualan_pmbyrn_notes = $penjualan_pmbyrn_notes;
      return true;
    }
    return false;
  }
  public function set_penjualan_pmbyrn_dateline($penjualan_pmbyrn_dateline)
  {
    if ($penjualan_pmbyrn_dateline != "") {
      $this->penjualan_pmbyrn_dateline = $penjualan_pmbyrn_dateline;
      return true;
    }
    return false;
  }
  public function set_penjualan_pmbyrn_status($penjualan_pmbyrn_status)
  {
    if ($penjualan_pmbyrn_status != "") {
      $this->penjualan_pmbyrn_status = $penjualan_pmbyrn_status;
      return true;
    }
    return false;
  }
  public function get_id_pk_penjualan_pembayaran()
  {
    return $this->id_pk_penjualan_pembayaran;
  }
  public function get_id_fk_penjualan()
  {
    return $this->id_fk_penjualan;
  }
  public function get_penjualan_pmbyrn_nama()
  {
    return $this->penjualan_pmbyrn_nama;
  }
  public function get_penjualan_pmbyrn_persen()
  {
    return $this->penjualan_pmbyrn_persen;
  }
  public function get_penjualan_pmbyrn_nominal()
  {
    return $this->penjualan_pmbyrn_nominal;
  }
  public function get_penjualan_pmbyrn_notes()
  {
    return $this->penjualan_pmbyrn_notes;
  }
  public function get_penjualan_pmbyrn_dateline()
  {
    return $this->penjualan_pmbyrn_dateline;
  }
  public function get_penjualan_pmbyrn_status()
  {
    return $this->penjualan_pmbyrn_status;
  }
  public function get_nominal_pembayaran()
  {
    $sql = "select sum(penjualan_pmbyrn_nominal) as total_pembayaran from tbl_penjualan_pembayaran
        where penjualan_pmbyrn_status = 'aktif' and id_fk_penjualan = ?";
    $args = array(
      $this->id_fk_penjualan
    );
    $result = executeQuery($sql, $args);
    $result = $result->result_array();
    return $result[0]["total_pembayaran"];
  }
}
