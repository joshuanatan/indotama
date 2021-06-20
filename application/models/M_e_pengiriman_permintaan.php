<?php
class M_e_pengiriman_permintaan extends CI_Model
{
  private $tbl_name = "mstr_pengiriman";
  private $columns = array();
  public function __construct()
  {
    parent::__construct();
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
  public function columns_excel()
  {
    $this->columns = array();
    $this->set_column("brg_nama", "Nama Barang", true);
    $this->set_column("brg_pemenuhan_qty", "Jumlah Barang", false);
    $this->set_column("toko_nama", "Toko Tujuan", false);
    $this->set_column("cabang_daerah", "Cabang Tujuan", false);
    $this->set_column("brg_pemenuhan_status", "status barang", false); /* status ini yang keubah2 waktu dikirim dan diteirma*/
    $this->set_column("pengiriman_tgl", "tanggal pengiriman", false); /* - kalau dia belom pernah kirim */
    $this->set_column("pengiriman_last_modified", "last modified", false);
    return $this->columns;
  }
  public function data_excel()
  {
    $query = "
        select id_pk_brg_pemenuhan,brg_nama,brg_pemenuhan_qty,cabang_daerah,toko_nama,toko_kode,toko_logo,brg_permintaan_status,brg_pemenuhan_status,ifnull(pengiriman_tgl,'-') as pengiriman_tgl,ifnull(pengiriman_last_modified,'-') as pengiriman_last_modified,id_pk_pengiriman 
        from tbl_brg_permintaan
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = tbl_brg_permintaan.id_fk_cabang and mstr_cabang.cabang_status = 'aktif'
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko and mstr_toko.toko_status = 'aktif'
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg and mstr_barang.brg_status = 'aktif'
        inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan and tbl_brg_pemenuhan.brg_pemenuhan_status != 'nonaktif' and tbl_brg_pemenuhan.id_fk_cabang = ?
        left join tbl_brg_pengiriman on tbl_brg_pengiriman.id_fk_brg_pemenuhan = tbl_brg_pemenuhan.id_pk_brg_pemenuhan and tbl_brg_pengiriman.brg_pengiriman_qty > 0
        left join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman and mstr_pengiriman.pengiriman_status = 'aktif' and mstr_pengiriman.pengiriman_tipe = 'permintaan' /*klo pengirimannya nonaktif, berarti gajadi dikirim, artinya belom dikirim sehingga tgl pengiriman akan null*/
        where brg_permintaan_status != 'nonaktif' /*klo dia apus, ya gajadi kirim berarti, selebihnya bisa aja done*/
        ";
    $args = array(
      $this->session->id_cabang
    );
    return executequery($query, $args);
  }
}
