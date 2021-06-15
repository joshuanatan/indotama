<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_brg_pindah extends CI_Model
{
  private $tbl_name = "tbl_brg_pindah";
  private $columns = array();
  private $id_pk_brg_pindah;
  private $brg_pindah_sumber; /*warehouse/penjualan/...*/
  private $id_fk_refrensi_sumber; /* id_warehouse/id_penjualan */
  private $id_brg_awal; /*klo dia warehouse, stok warehouse, cabang pake stok cabang*/
  private $id_brg_tujuan;
  private $id_fk_cabang;
  private $brg_pindah_qty;
  private $brg_pindah_status;
  private $brg_pindah_create_date;
  private $brg_pindah_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->brg_pindah_create_date = date("Y-m-d H:i:s");
    $this->brg_pindah_last_modified = date("Y-m-d H:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
    $this->id_fk_cabang = $this->session->id_cabang;
  }
  public function install()
  {
    $sql = "
        drop table if exists tbl_brg_pindah;
        create table tbl_brg_pindah(
            id_pk_brg_pindah int primary key auto_increment,
            brg_pindah_sumber varchar(50) comment 'warehouse/penjualan/...',
            id_fk_refrensi_sumber int comment 'id_warehouse/id_penjualan/...',
            id_brg_awal int,
            id_brg_tujuan int,
            id_fk_cabang int,
            brg_pindah_qty double,
            brg_pindah_status varchar(15),
            brg_pindah_create_date datetime,
            brg_pindah_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_pindah_log;
        create table tbl_brg_pindah_log(
            id_pk_brg_pindah_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_pindah int,
            brg_pindah_sumber varchar(50) comment 'warehouse/penjualan/...',
            id_fk_refrensi_sumber int comment 'id_warehouse/id_penjualan/...',
            id_brg_awal int,
            id_brg_tujuan int,
            id_fk_cabang int,
            brg_pindah_qty double,
            brg_pindah_status varchar(15),
            brg_pindah_create_date datetime,
            brg_pindah_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_pindah;
        delimiter $$
        create trigger trg_after_insert_brg_pindah
        after insert on tbl_brg_pindah
        for each row
        begin
            insert into tbl_brg_pindah_log(executed_function,id_pk_brg_pindah,brg_pindah_sumber,id_fk_refrensi_sumber,id_brg_awal,id_brg_tujuan,id_fk_cabang,brg_pindah_qty,brg_pindah_status,brg_pindah_create_date,brg_pindah_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pindah,new.brg_pindah_sumber,new.id_fk_refrensi_sumber,new.id_brg_awal,new.id_brg_tujuan,new.id_fk_cabang,new.brg_pindah_qty,new.brg_pindah_status,new.brg_pindah_create_date,new.brg_pindah_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
        end$$
        delimiter ;

        delimiter $$
        create trigger trg_update_brg_cabang_after_insert_brg_pindah
        after insert on tbl_brg_pindah
        for each row
        begin
            /*update barang cabang*/
            select id_pk_satuan into @id_satuan from mstr_satuan where mstr_satuan.satuan_rumus = 1;            
            call update_stok_barang_cabang(new.id_brg_awal,new.id_fk_cabang,0,0,new.brg_pindah_qty,@id_satuan);
            call update_stok_barang_cabang(new.id_brg_tujuan,new.id_fk_cabang,new.brg_pindah_qty,@id_satuan,0,0);
        end $$

        drop trigger if exists trg_after_update_brg_pindah;
        delimiter $$
        create trigger trg_after_update_brg_pindah
        after update on tbl_brg_pindah
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pindah_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pindah_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pindah_log(executed_function,id_pk_brg_pindah,brg_pindah_sumber,id_fk_refrensi_sumber,id_brg_awal,id_brg_tujuan,id_fk_cabang,brg_pindah_qty,brg_pindah_status,brg_pindah_create_date,brg_pindah_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pindah,new.brg_pindah_sumber,new.id_fk_refrensi_sumber,new.id_brg_awal,new.id_brg_tujuan,new.id_fk_cabang,new.brg_pindah_qty,new.brg_pindah_status,new.brg_pindah_create_date,new.brg_pindah_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
    executeQuery($sql);
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
  public function list_data()
  {
    $sql = "
        select id_pk_brg_pindah,brg_pindah_sumber,id_fk_refrensi_sumber,id_brg_awal,id_brg_tujuan,brg_pindah_qty,brg_pindah_status,brg_awal.brg_nama as brg_awal, brg_akhir.brg_nama as brg_akhir from tbl_brg_pindah
        inner join mstr_barang as brg_awal on brg_awal.id_pk_brg = tbl_brg_pindah.id_brg_awal 
        inner join mstr_barang as brg_akhir on brg_akhir.id_pk_brg = tbl_brg_pindah.id_brg_tujuan
        where brg_awal.brg_status = ? and brg_akhir.brg_status = ? and brg_pindah_status = ? and id_fk_refrensi_sumber = ? and brg_pindah_sumber = ?
        ";
    $args = array(
      "AKTIF", "AKTIF", "AKTIF", $this->id_fk_refrensi_sumber, $this->brg_pindah_sumber
    );
    return executeQuery($sql, $args);
  }
  public function insert()
  {
    if ($this->check_insert()) {
      $data = array(
        "brg_pindah_sumber" => $this->brg_pindah_sumber,
        "id_fk_refrensi_sumber" => $this->id_fk_refrensi_sumber,
        "id_brg_awal" => $this->id_brg_awal,
        "id_brg_tujuan" => $this->id_brg_tujuan,
        "id_fk_cabang" => $this->id_fk_cabang,
        "brg_pindah_qty" => $this->brg_pindah_qty,
        "brg_pindah_status" => $this->brg_pindah_status,
        "brg_pindah_create_date" => $this->brg_pindah_create_date,
        "brg_pindah_last_modified" => $this->brg_pindah_last_modified,
        "id_create_data" => $this->id_create_data,
        "id_last_modified" => $this->id_last_modified,
      );



      $id_hasil_insert = insertrow($this->tbl_name, $data);

      $log_all_msg = "Data Barang Pindah baru ditambahkan. Waktu penambahan: $this->brg_pindah_create_date";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

      $log_all_data_changes = "[ID Barang Pindah: $this->id_pk_brg_pindah][Sumber Barang Pindah: $this->brg_pindah_sumber][ID Sumber: $this->id_fk_refrensi_sumber][ID Barang Awal: $this->id_brg_awal][ID Barang Tujuan: $this->id_brg_tujuan][ID Cabang: $this->id_fk_cabang][Jumlah: $this->brg_pindah_qty][Status: $this->brg_pindah_status][Waktu Ditambahkan: $this->brg_pindah_create_date][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_create_data;
      $log_all_tgl = $this->brg_pindah_create_date;

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
        "id_pk_brg_pindah" => $this->id_pk_brg_pindah,
      );
      $data = array(
        "id_brg_awal" => $this->id_brg_awal,
        "id_brg_tujuan" => $this->id_brg_tujuan,
        "brg_pindah_qty" => $this->brg_pindah_qty,
        "brg_pindah_last_modified" => $this->brg_pindah_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updateRow($this->tbl_name, $data, $where);
        $id_pk = $this->id_pk_brg_pindah;
        $log_all_msg = "Data Barang Pindah dengan ID: $id_pk diubah. Waktu diubah: $this->brg_pindah_last_modified . Data berubah menjadi: ";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

        $log_all_data_changes = "[ID Barang Pindah: $this->id_pk_brg_pindah][Sumber Barang Pindah: $this->brg_pindah_sumber][ID Sumber: $this->id_fk_refrensi_sumber][ID Barang Awal: $this->id_brg_awal][ID Barang Tujuan: $this->id_brg_tujuan][ID Cabang: $this->id_fk_cabang][Jumlah: $this->brg_pindah_qty][Waktu Diubah: $this->brg_pindah_last_modified][Oleh: $nama_user]";

        $log_all_it = "";
        $log_all_user = $this->id_last_modified;
        $log_all_tgl = $this->brg_pindah_last_modified;

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
  public function update_id_fk_refrensi_sumber()
  {
    $where = array(
      "id_pk_brg_pindah" => $this->id_pk_brg_pindah
    );
    $data = array(
      "id_fk_refrensi_sumber" => $this->id_fk_refrensi_sumber
    );
    updateRow($this->tbl_name, $data, $where);
    return true;
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_brg_pindah" => $this->id_pk_brg_pindah,
      );
      $data = array(
        "brg_pindah_status" => "NONAKTIF",
        "brg_pindah_last_modified" => $this->brg_pindah_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updateRow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    if ($this->brg_pindah_sumber == "") {
      return false;
    }
    if ($this->id_fk_refrensi_sumber == "") {
      return false;
    }
    if ($this->id_brg_awal == "") {
      return false;
    }
    if ($this->id_brg_tujuan == "") {
      return false;
    }
    if ($this->brg_pindah_qty == "") {
      return false;
    }
    if ($this->brg_pindah_status == "") {
      return false;
    }
    if ($this->brg_pindah_create_date == "") {
      return false;
    }
    if ($this->brg_pindah_last_modified == "") {
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
    if ($this->id_pk_brg_pindah == "") {
      return false;
    }
    if ($this->id_brg_awal == "") {
      return false;
    }
    if ($this->id_brg_tujuan == "") {
      return false;
    }
    if ($this->brg_pindah_qty == "") {
      return false;
    }
    if ($this->brg_pindah_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {
    if ($this->id_pk_brg_pindah == "") {
      return false;
    }
    if ($this->brg_pindah_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($brg_pindah_sumber, $id_fk_refrensi_sumber, $id_brg_awal, $id_brg_tujuan, $brg_pindah_qty, $brg_pindah_status)
  {
    if (!$this->set_brg_pindah_sumber($brg_pindah_sumber)) {
      return false;
    }
    if (!$this->set_id_fk_refrensi_sumber($id_fk_refrensi_sumber)) {
      return false;
    }
    if (!$this->set_id_brg_awal($id_brg_awal)) {
      return false;
    }
    if (!$this->set_id_brg_tujuan($id_brg_tujuan)) {
      return false;
    }
    if (!$this->set_brg_pindah_qty($brg_pindah_qty)) {
      return false;
    }
    if (!$this->set_brg_pindah_status($brg_pindah_status)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_brg_pindah, $id_brg_awal, $id_brg_tujuan, $brg_pindah_qty)
  {
    if (!$this->set_id_pk_brg_pindah($id_pk_brg_pindah)) {
      return false;
    }
    if (!$this->set_id_brg_awal($id_brg_awal)) {
      return false;
    }
    if (!$this->set_id_brg_tujuan($id_brg_tujuan)) {
      return false;
    }
    if (!$this->set_brg_pindah_qty($brg_pindah_qty)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_brg_pindah)
  {
    if (!$this->set_id_pk_brg_pindah($id_pk_brg_pindah)) {
      return false;
    }
    return true;
  }
  public function get_id_pk_brg_pindah()
  {
    return $this->id_pk_brg_pindah;
  }
  public function get_brg_pindah_sumber()
  {
    return $this->brg_pindah_sumber;
  }
  public function get_id_fk_refrensi_sumber()
  {
    return $this->id_fk_refrensi_sumber;
  }
  public function get_id_brg_awal()
  {
    return $this->id_brg_awal;
  }
  public function get_id_brg_tujuan()
  {
    return $this->id_brg_tujuan;
  }
  public function get_brg_pindah_qty()
  {
    return $this->brg_pindah_qty;
  }
  public function get_brg_pindah_status()
  {
    return $this->brg_pindah_status;
  }
  public function set_id_pk_brg_pindah($id_pk_brg_pindah)
  {
    if ($id_pk_brg_pindah != "") {
      $this->id_pk_brg_pindah = $id_pk_brg_pindah;
      return true;
    }
    return false;
  }
  public function set_brg_pindah_sumber($brg_pindah_sumber)
  {
    if ($brg_pindah_sumber != "") {
      $this->brg_pindah_sumber = $brg_pindah_sumber;
      return true;
    }
    return false;
  }
  public function set_id_fk_refrensi_sumber($id_fk_refrensi_sumber)
  {
    if ($id_fk_refrensi_sumber != "") {
      $this->id_fk_refrensi_sumber = $id_fk_refrensi_sumber;
      return true;
    }
    return false;
  }
  public function set_id_brg_awal($id_brg_awal)
  {
    if ($id_brg_awal != "") {
      $this->id_brg_awal = $id_brg_awal;
      return true;
    }
    return false;
  }
  public function set_id_brg_tujuan($id_brg_tujuan)
  {
    if ($id_brg_tujuan != "") {
      $this->id_brg_tujuan = $id_brg_tujuan;
      return true;
    }
    return false;
  }
  public function set_brg_pindah_qty($brg_pindah_qty)
  {
    if ($brg_pindah_qty != "") {
      $this->brg_pindah_qty = $brg_pindah_qty;
      return true;
    }
    return false;
  }
  public function set_brg_pindah_status($brg_pindah_status)
  {
    if ($brg_pindah_status != "") {
      $this->brg_pindah_status = $brg_pindah_status;
      return true;
    }
    return false;
  }
}
