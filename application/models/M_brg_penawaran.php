<?php
date_default_timezone_set("Asia/Jakarta");
class M_brg_penawaran extends CI_Model
{
  public function insert($id_fk_brg, $brg_penawaran_qty, $brg_penawaran_satuan, $brg_penawaran_price, $brg_penawaran_notes, $brg_penawaran_status, $id_fk_penawaran)
  {
    $data = array(
      "id_fk_brg" => $id_fk_brg,
      "brg_penawaran_qty" => $brg_penawaran_qty,
      "brg_penawaran_satuan" => $brg_penawaran_satuan,
      "brg_penawaran_price" => $brg_penawaran_price,
      "brg_penawaran_notes" => $brg_penawaran_notes,
      "brg_penawaran_status" => $brg_penawaran_status,
      "id_fk_penawaran" => $id_fk_penawaran,
      "brg_penawaran_id_create" => $this->session->id_user,
      "brg_penawaran_tgl_create" => date("Y-m-d H:i:s")
    );

    $id_hasil_insert = insertRow("tbl_brg_penawaran", $data);

    $log_all_msg = "Data Barang Penawaran baru ditambahkan. Waktu penambahan: " . date("Y-m-d H:i:s");
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->session->id_user));
    $log_all_data_changes = "[ID Barang Penawaran: $id_hasil_insert][Jumlah: $brg_penawaran_qty][Satuan: $brg_penawaran_satuan][Harga: $brg_penawaran_price][Notes: $brg_penawaran_notes][Status: $brg_penawaran_status][ID Penawaran: $id_fk_penawaran][Waktu Ditambahkan: " . date('Y-m-d H:i:s') . "][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->session->id_user;
    $log_all_tgl = date("Y-m-d H:i:s");

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
  public function update($id_pk_brg_penawaran, $id_fk_brg, $brg_penawaran_qty, $brg_penawaran_satuan, $brg_penawaran_price, $brg_penawaran_notes)
  {
    $where = array(
      "id_pk_brg_penawaran" => $id_pk_brg_penawaran
    );
    $data = array(
      "id_fk_brg" => $id_fk_brg,
      "brg_penawaran_qty" => $brg_penawaran_qty,
      "brg_penawaran_satuan" => $brg_penawaran_satuan,
      "brg_penawaran_price" => $brg_penawaran_price,
      "brg_penawaran_notes" => $brg_penawaran_notes,
      "brg_penawaran_id_update" => $this->session->id_user,
      "brg_penawaran_tgl_update" => date("Y-m-d H:i:s")
    );
    updateRow("tbl_brg_penawaran", $data, $where);
    $id_pk = $id_pk_brg_penawaran;
    $log_all_msg = "Data Barang Penawaran dengan ID: $id_pk diubah. Waktu diubah: date('Y-m-d H:i:s') . Data berubah menjadi: ";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->session->id_user));

    $log_all_data_changes = "[ID Barang Penawaran: $id_pk][Jumlah: $brg_penawaran_qty][Satuan: $brg_penawaran_satuan][Harga: $brg_penawaran_price][Notes: $brg_penawaran_notes][Waktu Diedit: " . date('Y-m-d H:i:s') . "][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->session->id_user;
    $log_all_tgl = date("Y-m-d H:i:s");

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
  public function delete($id_pk_brg_penawaran)
  {
    $where = array(
      "id_pk_brg_penawaran" => $id_pk_brg_penawaran
    );
    $data = array(
      "brg_penawaran_status" => "nonaktif",
      "brg_penawaran_id_delete" => $this->session->id_user,
      "brg_penawaran_tgl_delete" => date("Y-m-d H:i:s")
    );
    updateRow("tbl_brg_penawaran", $data, $where);
    return true;
  }
  public function get_brg_penawaran($id_pk_penawaran)
  {
    $sql = "
      select id_pk_brg_penawaran, brg_nama,brg_penawaran_qty, brg_penawaran_satuan, brg_penawaran_price, brg_penawaran_notes, brg_harga, brg_harga_toko, brg_harga_grosir 
      from tbl_brg_penawaran 
      inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penawaran.id_fk_brg
      where id_fk_penawaran = ? and brg_penawaran_status = 'aktif'
      ";
    $args = array(
      $id_pk_penawaran
    );
    return executeQuery($sql, $args);
  }
}
