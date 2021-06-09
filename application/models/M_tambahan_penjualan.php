<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_tambahan_penjualan extends ci_model
{
  private $tbl_name = "tbl_tambahan_penjualan";
  private $columns = array();
  private $id_pk_penjualan_add;
  private $tmbhn_attr;
  private $tmbhn_harga;
  private $tmbhn_status;
  private $tmbhn_notes;
  private $id_fk_penjualan;
  private $tmbhn_create_date;
  private $tmbhn_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->tmbhn_create_date = date("y-m-d h:i:s");
    $this->tmbhn_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  public function columns()
  {
    return $this->columns;
  }
  public function install()
  {
    $sql = "
        drop table if exists tbl_tambahan_penjualan;
        create table tbl_tambahan_penjualan(
            id_pk_tmbhn int primary key auto_increment,
            tmbhn varchar(100),
            tmbhn_jumlah double,
            tmbhn_satuan varchar(20),
            tmbhn_harga int,
            tmbhn_notes varchar(200),
            tmbhn_status varchar(15),
            id_fk_penjualan int,
            tmbhn_create_date datetime,
            tmbhn_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_tambahan_penjualan_log;
        create table tbl_tambahan_penjualan_log(
            id_pk_tmbhn_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_tmbhn int,
            tmbhn varchar(100),
            tmbhn_jumlah double,
            tmbhn_satuan varchar(20),
            tmbhn_harga int,
            tmbhn_notes varchar(200),
            tmbhn_status varchar(15),
            id_fk_penjualan int,
            tmbhn_create_date datetime,
            tmbhn_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_tambahan_penjualan;
        delimiter $$
        create trigger trg_after_insert_tambahan_penjualan
        after insert on tbl_tambahan_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_penjualan_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_penjualan,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_penjualan,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_tambahan_penjualan;
        delimiter $$
        create trigger trg_after_update_tambahan_penjualan
        after update on tbl_tambahan_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_penjualan_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_penjualan,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_penjualan,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
    executequery($sql);
  }
  public function list_data()
  {
    $sql = "
        select id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,tmbhn_last_modified
        from " . $this->tbl_name . "
        where tmbhn_status = ? and id_fk_penjualan = ?";
    $args = array(
      "aktif", $this->id_fk_penjualan
    );
    return executequery($sql, $args);
  }
  public function insert($tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes, $tmbhn_status, $id_fk_penjualan)
  {
    $data = array(
      "tmbhn" => $tmbhn,
      "tmbhn_jumlah" => $tmbhn_jumlah,
      "tmbhn_satuan" => $tmbhn_satuan,
      "tmbhn_harga" => $tmbhn_harga,
      "tmbhn_notes" => $tmbhn_notes,
      "tmbhn_status" => $tmbhn_status,
      "id_fk_penjualan" => $id_fk_penjualan,
      "tmbhn_create_date" => $this->tmbhn_create_date,
      "tmbhn_last_modified" => $this->tmbhn_last_modified,
      "id_create_data" => $this->id_create_data,
      "id_last_modified" => $this->id_last_modified,
    );
    $id_hasil_insert = insertrow($this->tbl_name, $data);

    $log_all_msg = "Data Tambahan Penjualan baru ditambahkan. Waktu penambahan: $this->tmbhn_create_date";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

    $log_all_data_changes = "[ID Tambahan Penjualan: $id_hasil_insert][Tambahan: $this->tmbhn][Jumlah: $this->tmbhn_jumlah][Satuan: $this->tmbhn_satuan][Harga: $this->tmbhn_harga][Notes: $this->tmbhn_notes][Status: $this->tmbhn_status][ID Penjualan: $this->id_fk_penjualan][Waktu Ditambahkan: $this->tmbhn_create_date][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_last_modified;
    $log_all_tgl = $this->tmbhn_create_date;

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
  public function update($id_pk_tmbhn, $tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes)
  {
    $where = array(
      "id_pk_tmbhn" => $id_pk_tmbhn
    );
    $data = array(
      "tmbhn" => $tmbhn,
      "tmbhn_jumlah" => $tmbhn_jumlah,
      "tmbhn_satuan" => $tmbhn_satuan,
      "tmbhn_harga" => $tmbhn_harga,
      "tmbhn_notes" => $tmbhn_notes,
      "tmbhn_last_modified" => $this->tmbhn_last_modified,
      "id_last_modified" => $this->id_last_modified,
    );
    updaterow($this->tbl_name, $data, $where);
    return true;
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_tmbhn" => $this->id_pk_tmbhn
      );
      $data = array(
        "tmbhn_status" => "nonaktif",
        "tmbhn_last_modified" => $this->tmbhn_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    if ($this->tmbhn == "") {
      return false;
    }
    if ($this->tmbhn_jumlah == "") {
      return false;
    }
    if ($this->tmbhn_satuan == "") {
      return false;
    }
    if ($this->tmbhn_harga == "") {
      return false;
    }
    if ($this->tmbhn_notes == "") {
      return false;
    }
    if ($this->tmbhn_status == "") {
      return false;
    }
    if ($this->id_fk_penjualan == "") {
      return false;
    }
    if ($this->tmbhn_create_date == "") {
      return false;
    }
    if ($this->tmbhn_last_modified == "") {
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
    if ($this->id_pk_tmbhn == "") {
      return false;
    }
    if ($this->tmbhn == "") {
      return false;
    }
    if ($this->tmbhn_jumlah == "") {
      return false;
    }
    if ($this->tmbhn_satuan == "") {
      return false;
    }
    if ($this->tmbhn_harga == "") {
      return false;
    }
    if ($this->tmbhn_notes == "") {
      return false;
    }
    if ($this->tmbhn_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {
    if ($this->id_pk_tmbhn == "") {
      return false;
    }
    if ($this->tmbhn_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes, $tmbhn_status, $id_fk_penjualan)
  {
    if (!$this->set_tmbhn($tmbhn)) {
      return false;
    }
    if (!$this->set_tmbhn_jumlah($tmbhn_jumlah)) {
      return false;
    }
    if (!$this->set_tmbhn_satuan($tmbhn_satuan)) {
      return false;
    }
    if (!$this->set_tmbhn_harga($tmbhn_harga)) {
      return false;
    }
    if (!$this->set_tmbhn_notes($tmbhn_notes)) {
      return false;
    }
    if (!$this->set_tmbhn_status($tmbhn_status)) {
      return false;
    }
    if (!$this->set_id_fk_penjualan($id_fk_penjualan)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_tmbhn, $tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes)
  {
    if (!$this->set_id_pk_tmbhn($id_pk_tmbhn)) {
      return false;
    }
    if (!$this->set_tmbhn($tmbhn)) {
      return false;
    }
    if (!$this->set_tmbhn_jumlah($tmbhn_jumlah)) {
      return false;
    }
    if (!$this->set_tmbhn_satuan($tmbhn_satuan)) {
      return false;
    }
    if (!$this->set_tmbhn_harga($tmbhn_harga)) {
      return false;
    }
    if (!$this->set_tmbhn_notes($tmbhn_notes)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_tmbhn)
  {
    if (!$this->set_id_pk_tmbhn($id_pk_tmbhn)) {
      return false;
    }
    return true;
  }
  public function set_id_pk_tmbhn($id_pk_tmbhn)
  {
    if ($id_pk_tmbhn != "") {
      $this->id_pk_tmbhn = $id_pk_tmbhn;
      return true;
    }
    return false;
  }
  public function set_tmbhn($tmbhn)
  {
    if ($tmbhn != "") {
      $this->tmbhn = $tmbhn;
      return true;
    }
    return false;
  }
  public function set_tmbhn_jumlah($tmbhn_jumlah)
  {
    if ($tmbhn_jumlah != "") {
      $this->tmbhn_jumlah = $tmbhn_jumlah;
      return true;
    }
    return false;
  }
  public function set_tmbhn_satuan($tmbhn_satuan)
  {
    if ($tmbhn_satuan != "") {
      $this->tmbhn_satuan = $tmbhn_satuan;
      return true;
    }
    return false;
  }
  public function set_tmbhn_harga($tmbhn_harga)
  {
    if ($tmbhn_harga != "") {
      $this->tmbhn_harga = $tmbhn_harga;
      return true;
    }
    return false;
  }
  public function set_tmbhn_notes($tmbhn_notes)
  {
    if ($tmbhn_notes != "") {
      $this->tmbhn_notes = $tmbhn_notes;
      return true;
    }
    return false;
  }
  public function set_tmbhn_status($tmbhn_status)
  {
    if ($tmbhn_status != "") {
      $this->tmbhn_status = $tmbhn_status;
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
  public function get_id_pk_tmbhn()
  {
    return $this->id_pk_tmbhn;
  }
  public function get_tmbhn()
  {
    return $this->tmbhn;
  }
  public function get_tmbhn_jumlah()
  {
    return $this->tmbhn_jumlah;
  }
  public function get_tmbhn_satuan()
  {
    return $this->tmbhn_satuan;
  }
  public function get_tmbhn_harga()
  {
    return $this->tmbhn_harga;
  }
  public function get_tmbhn_notes()
  {
    return $this->tmbhn_notes;
  }
  public function get_tmbhn_status()
  {
    return $this->tmbhn_status;
  }
  public function get_id_fk_penjualan()
  {
    return $this->id_fk_penjualan;
  }
  public function get_nominal_tambahan()
  {
    $sql = "select sum(tmbhn_jumlah*tmbhn_harga) as nominal_tambahan 
        from tbl_tambahan_penjualan
        where tmbhn_status = 'aktif' and id_fk_penjualan = ?";
    $args = array(
      $this->id_fk_penjualan
    );
    $result = executeQuery($sql, $args);
    $result = $result->result_array();
    return $result[0]["nominal_tambahan"];
  }
}
