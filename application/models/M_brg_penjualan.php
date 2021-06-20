<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_brg_penjualan extends ci_model
{
  private $tbl_name = "tbl_brg_penjualan";
  private $columns = array();
  private $id_pk_brg_penjualan;
  private $brg_penjualan_qty_real;
  private $brg_penjualan_qty;
  private $brg_penjualan_satuan;
  private $brg_penjualan_harga;
  private $brg_penjualan_note;
  private $brg_penjualan_status;
  private $id_fk_penjualan;
  private $id_fk_barang;
  private $brg_penjualan_create_date;
  private $brg_penjualan_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->brg_penjualan_create_date = date("y-m-d h:i:s");
    $this->brg_penjualan_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  public function install()
  {
    $sql = "
        drop table if exists tbl_brg_penjualan;
        create table tbl_brg_penjualan(
            id_pk_brg_penjualan int primary key auto_increment,
            brg_penjualan_qty_real double,
            brg_penjualan_satuan_real varchar(20),
            brg_penjualan_qty double,
            brg_penjualan_satuan varchar(20),
            brg_penjualan_harga int,
            brg_penjualan_note varchar(150),
            brg_penjualan_status varchar(15),
            id_fk_penjualan int,
            id_fk_barang int,
            brg_penjualan_create_date datetime,
            brg_penjualan_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_penjualan_log;
        create table tbl_brg_penjualan_log(
            id_pk_brg_penjualan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_penjualan int,
            brg_penjualan_qty_real double,
            brg_penjualan_satuan_real varchar(20),
            brg_penjualan_qty double,
            brg_penjualan_satuan varchar(20),
            brg_penjualan_harga int,
            brg_penjualan_note varchar(150),
            brg_penjualan_status varchar(15),
            id_fk_penjualan int,
            id_fk_barang int,
            brg_penjualan_create_date datetime,
            brg_penjualan_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_penjualan;
        delimiter $$
        create trigger trg_after_insert_brg_penjualan
        after insert on tbl_brg_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_brg_penjualan;
        delimiter $$
        create trigger trg_after_update_brg_penjualan
        after update on tbl_brg_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
    executequery($sql);
  }
  public function columns()
  {
    return $this->columns;
  }
  public function list_data()
  {
    $sql = "
        select id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,id_fk_penjualan,id_fk_barang,brg_nama,brg_harga,brg_harga_toko,brg_harga_grosir,brg_penjualan_create_date,brg_penjualan_last_modified, ifnull(sum(brg_pengiriman_qty),0) as jumlah_terkirim
        from " . $this->tbl_name . "
        inner join mstr_barang on mstr_barang.id_pk_brg = " . $this->tbl_name . ".id_fk_barang
        left join tbl_brg_pengiriman on tbl_brg_pengiriman.id_fk_brg_penjualan = tbl_brg_penjualan.id_pk_brg_penjualan and brg_pengiriman_qty > 0
        where brg_penjualan_status = ? and id_fk_penjualan = ? and brg_status = ?
        group by id_pk_brg_penjualan
        ";
    $args = array(
      "aktif", $this->id_fk_penjualan, "aktif"
    );
    return executequery($sql, $args);
  }
  public function insert($brg_penjualan_qty_real, $brg_penjualan_satuan_real, $brg_penjualan_qty, $brg_penjualan_satuan, $brg_penjualan_harga, $brg_penjualan_note, $brg_penjualan_status, $id_fk_penjualan, $id_fk_barang)
  {
    $data = array(
      "brg_penjualan_qty_real" => $brg_penjualan_qty_real,
      "brg_penjualan_satuan_real" => $brg_penjualan_satuan_real,
      "brg_penjualan_qty" => $brg_penjualan_qty,
      "brg_penjualan_satuan" => $brg_penjualan_satuan,
      "brg_penjualan_harga" => $brg_penjualan_harga,
      "brg_penjualan_note" => $brg_penjualan_note,
      "brg_penjualan_status" => $brg_penjualan_status,
      "id_fk_penjualan" => $id_fk_penjualan,
      "id_fk_barang" => $id_fk_barang,
      "brg_penjualan_create_date" => $this->brg_penjualan_create_date,
      "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
      "id_create_data" => $this->id_create_data,
      "id_last_modified" => $this->id_last_modified
    );

    $id_hasil_insert = insertrow($this->tbl_name, $data);

    $log_all_msg = "Data Jenis Barang baru ditambahkan. Waktu penambahan: $this->brg_penjualan_create_date";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));
    $log_all_data_changes = "[ID Barang Jenis: $id_hasil_insert][Jumlah (real): $brg_penjualan_qty_real][Satuan (real): $brg_penjualan_satuan_real][Jumlah: $brg_penjualan_qty][Satuan: $brg_penjualan_satuan][Harga: $brg_penjualan_harga][Notes: $brg_penjualan_note][Status: $brg_penjualan_status][ID Penjualan: $id_fk_penjualan][ID Barang: $id_fk_barang][Waktu Ditambahkan: $this->brg_penjualan_create_date][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_create_data;
    $log_all_tgl = $this->brg_penjualan_create_date;

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
  public function update($id_pk_brg_penjualan, $brg_penjualan_qty, $brg_penjualan_satuan, $brg_penjualan_harga, $brg_penjualan_note, $id_fk_barang)
  {
    $where = array(
      "id_pk_brg_penjualan" => $id_pk_brg_penjualan,
    );
    $data = array(
      "brg_penjualan_qty" => $brg_penjualan_qty,
      "brg_penjualan_satuan" => $brg_penjualan_satuan,
      "brg_penjualan_harga" => $brg_penjualan_harga,
      "brg_penjualan_note" => $brg_penjualan_note,
      "id_fk_barang" => $id_fk_barang,
      "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
      "id_last_modified" => $this->id_last_modified,
    );
    updaterow($this->tbl_name, $data, $where);
    $id_pk = $this->id_pk_brg_penjualan;
    $log_all_msg = "Data Barang Penjualan dengan ID: $id_pk diubah. Waktu diubah: $this->brg_penjualan_last_modified . Data berubah menjadi: ";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

    $log_all_data_changes = "[ID Barang Jenis: $id_pk][Jumlah (real): $this->brg_penjualan_qty_real][Satuan (real): $this->brg_penjualan_satuan_real][Jumlah: $this->brg_penjualan_qty][Satuan: $this->brg_penjualan_satuan][Harga: $this->brg_penjualan_harga][Notes: $this->brg_penjualan_note][ID Penjualan: $this->id_fk_penjualan][ID Barang: $this->id_fk_barang][Waktu Diubah: $this->id_last_modified][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_last_modified;
    $log_all_tgl = $this->brg_penjualan_last_modified;

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
        "id_pk_brg_penjualan" => $this->id_pk_brg_penjualan,
      );
      $data = array(
        "brg_penjualan_status" => "nonaktif",
        "brg_penjualan_last_modified" => $this->brg_penjualan_last_modified,
        "id_last_modified" => $this->id_last_modified,
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
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
  public function set_insert($brg_penjualan_qty_real, $brg_penjualan_satuan_real, $brg_penjualan_qty, $brg_penjualan_satuan, $brg_penjualan_harga, $brg_penjualan_note, $brg_penjualan_status, $id_fk_penjualan, $id_fk_barang)
  {
    $this->set_brg_penjualan_qty_real($brg_penjualan_qty_real);
    $this->set_brg_penjualan_qty($brg_penjualan_qty);
    $this->set_brg_penjualan_satuan_real($brg_penjualan_satuan_real);
    $this->set_brg_penjualan_qty($brg_penjualan_qty);
    $this->set_brg_penjualan_satuan($brg_penjualan_satuan);
    $this->set_brg_penjualan_harga($brg_penjualan_harga);
    $this->set_brg_penjualan_note($brg_penjualan_note);
    $this->set_brg_penjualan_status($brg_penjualan_status);
    $this->set_id_fk_penjualan($id_fk_penjualan);
    $this->set_id_fk_barang($id_fk_barang);
    return true;
  }
  public function set_update($id_pk_brg_penjualan, $brg_penjualan_qty_real, $brg_penjualan_satuan_real, $brg_penjualan_qty, $brg_penjualan_satuan, $brg_penjualan_harga, $brg_penjualan_note, $id_fk_barang)
  {
    $this->set_id_pk_brg_penjualan($id_pk_brg_penjualan);
    $this->set_brg_penjualan_qty_real($brg_penjualan_qty_real);
    $this->set_brg_penjualan_satuan_real($brg_penjualan_satuan_real);
    $this->set_brg_penjualan_qty($brg_penjualan_qty);
    $this->set_brg_penjualan_satuan($brg_penjualan_satuan);
    $this->set_brg_penjualan_harga($brg_penjualan_harga);
    $this->set_brg_penjualan_note($brg_penjualan_note);
    $this->set_id_fk_barang($id_fk_barang);
    return true;
  }
  public function set_delete($id_pk_brg_penjualan)
  {
    $this->set_id_pk_brg_penjualan($id_pk_brg_penjualan);
    return true;
  }
  public function set_id_pk_brg_penjualan($id_pk_brg_penjualan)
  {
    $this->id_pk_brg_penjualan = $id_pk_brg_penjualan;
    return true;
  }
  public function set_brg_penjualan_qty_real($brg_penjualan_qty_real)
  {
    $this->brg_penjualan_qty_real = $brg_penjualan_qty_real;
    return true;
  }
  public function set_brg_penjualan_satuan_real($brg_penjualan_satuan_real)
  {
    $this->brg_penjualan_satuan_real = $brg_penjualan_satuan_real;
    return true;
  }
  public function set_brg_penjualan_qty($brg_penjualan_qty)
  {
    $this->brg_penjualan_qty = $brg_penjualan_qty;
    return true;
  }
  public function set_brg_penjualan_satuan($brg_penjualan_satuan)
  {
    $this->brg_penjualan_satuan = $brg_penjualan_satuan;
    return true;
  }
  public function set_brg_penjualan_harga($brg_penjualan_harga)
  {
    $this->brg_penjualan_harga = $brg_penjualan_harga;
    return true;
  }
  public function set_brg_penjualan_note($brg_penjualan_note)
  {
    $this->brg_penjualan_note = $brg_penjualan_note;
    return true;
  }
  public function set_brg_penjualan_status($brg_penjualan_status)
  {
    $this->brg_penjualan_status = $brg_penjualan_status;
    return true;
  }
  public function set_id_fk_penjualan($id_fk_penjualan)
  {
    $this->id_fk_penjualan = $id_fk_penjualan;
    return true;
  }
  public function set_id_fk_barang($id_fk_barang)
  {
    $this->id_fk_barang = $id_fk_barang;
    return true;
  }
  public function get_nominal_brg_penjualan()
  {
    /*ambil dari sum(qty*harga)*/
    $sql = "select sum(brg_penjualan_qty*brg_penjualan_harga) as nominal_penjualan
        from tbl_brg_penjualan 
        where brg_penjualan_status = 'aktif' 
        and id_fk_penjualan = ?";
    $args = array(
      $this->id_fk_penjualan
    );
    $result = executeQuery($sql, $args);
    $result = $result->result_array();
    return $result[0]["nominal_penjualan"];
  }
}
