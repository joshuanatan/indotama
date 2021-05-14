<?php
date_default_timezone_set("Asia/Jakarta");
class M_brg_penawaran extends CI_Model{
  public function insert($id_fk_brg,$brg_penawaran_qty,$brg_penawaran_satuan,$brg_penawaran_price,$brg_penawaran_notes,$brg_penawaran_status,$id_fk_penawaran){
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
    return insertRow("tbl_brg_penawaran",$data);
  }
  public function update($id_pk_brg_penawaran, $id_fk_brg,$brg_penawaran_qty,$brg_penawaran_satuan,$brg_penawaran_price,$brg_penawaran_notes){
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
    updateRow("tbl_brg_penawaran",$data,$where);
    return true;
  }
  public function delete($id_pk_brg_penawaran){
    $where = array(
      "id_pk_brg_penawaran" => $id_pk_brg_penawaran
    );
    $data = array(
      "brg_penawaran_status" => "nonaktif",
      "brg_penawaran_id_delete" => $this->session->id_user,
      "brg_penawaran_tgl_delete" => date("Y-m-d H:i:s")
    );
    updateRow("tbl_brg_penawaran",$data,$where);
    return true;
  }
  public function get_brg_penawaran($id_pk_penawaran){
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