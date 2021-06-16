<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_t_penerimaan_permintaan extends ci_model
{
  private $columns = array();
  private $id_fk_cabang;
  private $id_fk_warehouse;
  private $penerimaan_tempat;
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $this->column_penerimaan_permintaan();
    return $this->columns;
  }
  private function column_penerimaan_permintaan()
  {
    $this->columns = array();
    $this->set_column("brg_nama", "Nama Barang", true);
    $this->set_column("brg_pengiriman_qty", "Jumlah Barang", false);
    $this->set_column("toko_nama", "Toko Pengirim", false);
    $this->set_column("cabang_daerah", "Cabang Pengirim", false);
    $this->set_column("brg_pemenuhan_status", "status barang", false); /* status ini yang keubah2 waktu dikirim dan diteirma*/
    $this->set_column("pengiriman_tgl", "tanggal pengiriman", false);
    $this->set_column("penerimaan_tgl", "Tanggal penerimaan", false);/* - kalau dia belom pernah terima */
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
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "", $penerimaan_tempat = "", $id_penerima = "")
  {
    $this->penerimaan_tempat = $penerimaan_tempat;
    if (strtolower($penerimaan_tempat) == "cabang") {
      $this->id_fk_cabang = $id_penerima;
    }
    $this->column_penerimaan_permintaan();
    $order_by = $this->columns[$order_by]["col_name"];
    $result = $this->content_permintaan($page, $order_by, $order_direction, $search_key, $data_per_page);
    return $result;
  }
  private function content_permintaan($page, $order_by, $order_direction, $search_key, $data_per_page)
  {
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            (
                id_pk_brg_pengiriman like '%" . $search_key . "%' or
                brg_pengiriman_qty like '%" . $search_key . "%' or
                brg_pengiriman_note like '%" . $search_key . "%' or
                brg_pemenuhan_status like '%" . $search_key . "%' or
                pengiriman_tgl like '%" . $search_key . "%' or
                brg_nama like '%" . $search_key . "%' or
                brg_kode like '%" . $search_key . "%' or
                pengiriman_tgl like '%" . $search_key . "%' or
                penerimaan_tgl like '%" . $search_key . "%'
            )";
    }
    if (strtolower($this->penerimaan_tempat) == "cabang") {
      $query = "
            select id_pk_penerimaan,id_pk_brg_pemenuhan,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,brg_pemenuhan_status,pengiriman_tgl,cabang_daerah,toko_nama,toko_kode,brg_nama,brg_kode,
            ifnull(penerimaan_tgl,'-') as penerimaan_tgl
            from tbl_brg_pengiriman
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman and mstr_pengiriman.pengiriman_status = 'aktif' #harus yang jadi terkirim bukan yang dicancel
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_pengiriman.id_fk_cabang #dapetin daerah cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko #dapetin nama toko
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan #cuman untuk dapetin id_cabang_minta & id_barang
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan and tbl_brg_permintaan.id_fk_cabang = ? #menentukan peminta barang yang akan jadi penerima
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg #dapetin detail barang
            left join tbl_brg_penerimaan on tbl_brg_penerimaan.id_fk_brg_pengiriman = tbl_brg_pengiriman.id_pk_brg_pengiriman and tbl_brg_penerimaan.brg_penerimaan_qty > 0 #dianggap diterima apabila brgditerima > 0 (klo delete, ini ke 0 sendiri)
            left join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan and mstr_penerimaan.penerimaan_status = 'aktif' and mstr_penerimaan.penerimaan_tipe = 'permintaan' #dianggap diterima apabila status penerimaan > 0
            where tbl_brg_pengiriman.brg_pengiriman_qty > 0 #dianggap terkirim apabila tidak di cancel (klo cancel, qty jadi 0)
            " . $search_query . "
            union
            
            select id_pk_penerimaan,id_pk_brg_pemenuhan,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,brg_pemenuhan_status,pengiriman_tgl,warehouse_alamat as cabang_daerah,warehouse_nama as toko_nama, '-' as toko_kode,brg_nama,brg_kode,
            ifnull(penerimaan_tgl,'-') as penerimaan_tgl
            from tbl_brg_pengiriman
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman and mstr_pengiriman.pengiriman_status = 'aktif' #harus yang jadi terkirim bukan yang dicancel
            inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = mstr_pengiriman.id_fk_warehouse
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan #cuman untuk dapetin id_cabang_minta & id_barang
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan and tbl_brg_permintaan.id_fk_cabang = ? #menentukan peminta barang yang akan jadi penerima
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg #dapetin detail barang
            left join tbl_brg_penerimaan on tbl_brg_penerimaan.id_fk_brg_pengiriman = tbl_brg_pengiriman.id_pk_brg_pengiriman and tbl_brg_penerimaan.brg_penerimaan_qty > 0 #dianggap diterima apabila brgditerima > 0 (klo delete, ini ke 0 sendiri)
            left join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan and mstr_penerimaan.penerimaan_status = 'aktif' and mstr_penerimaan.penerimaan_tipe = 'permintaan' #dianggap diterima apabila status penerimaan > 0
            where tbl_brg_pengiriman.brg_pengiriman_qty > 0 #dianggap terkirim apabila tidak di cancel (klo cancel, qty jadi 0)
            " . $search_query . "
            order by " . $order_by . " " . $order_direction . " 
            limit 20 offset " . ($page - 1) * $data_per_page;
      $args = array(
        $this->id_fk_cabang, $this->id_fk_cabang
      );
      $result["data"] = executequery($query, $args);
      //echo $this->db->last_query();
      $query = "
            select id_pk_penerimaan
            from tbl_brg_pengiriman
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman and mstr_pengiriman.pengiriman_status = 'aktif' #harus yang jadi terkirim bukan yang dicancel
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_pengiriman.id_fk_cabang #dapetin daerah cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko #dapetin nama toko
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan #cuman untuk dapetin id_cabang_minta & id_barang
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan and tbl_brg_permintaan.id_fk_cabang = ? #menentukan peminta barang yang akan jadi penerima
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg #dapetin detail barang
            left join tbl_brg_penerimaan on tbl_brg_penerimaan.id_fk_brg_pengiriman = tbl_brg_pengiriman.id_pk_brg_pengiriman and tbl_brg_penerimaan.brg_penerimaan_qty > 0 #dianggap diterima apabila brgditerima > 0 (klo delete, ini ke 0 sendiri)
            left join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan and mstr_penerimaan.penerimaan_status = 'aktif' and mstr_penerimaan.penerimaan_tipe = 'permintaan' #dianggap diterima apabila status penerimaan > 0
            where tbl_brg_pengiriman.brg_pengiriman_qty > 0 #dianggap terkirim apabila tidak di cancel (klo cancel, qty jadi 0)
            " . $search_query . "
            union
            select id_pk_penerimaan
            from tbl_brg_pengiriman
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman and mstr_pengiriman.pengiriman_status = 'aktif' #harus yang jadi terkirim bukan yang dicancel
            inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = mstr_pengiriman.id_fk_warehouse
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan #cuman untuk dapetin id_cabang_minta & id_barang
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan and tbl_brg_permintaan.id_fk_cabang = ? #menentukan peminta barang yang akan jadi penerima
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg #dapetin detail barang
            left join tbl_brg_penerimaan on tbl_brg_penerimaan.id_fk_brg_pengiriman = tbl_brg_pengiriman.id_pk_brg_pengiriman and tbl_brg_penerimaan.brg_penerimaan_qty > 0 #dianggap diterima apabila brgditerima > 0 (klo delete, ini ke 0 sendiri)
            left join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan and mstr_penerimaan.penerimaan_status = 'aktif' and mstr_penerimaan.penerimaan_tipe = 'permintaan' #dianggap diterima apabila status penerimaan > 0
            where tbl_brg_pengiriman.brg_pengiriman_qty > 0 #dianggap terkirim apabila tidak di cancel (klo cancel, qty jadi 0)
            " . $search_query;
      $result["total_data"] = executequery($query, $args)->num_rows();
    }
    return $result;
  }
  public function content_pengiriman_otw($id_cabang)
  {
    #dipake di perjalanan graphic
    $query = "
        select id_pk_penerimaan,id_pk_brg_pemenuhan,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,brg_pemenuhan_status,pengiriman_tgl,cabang_daerah,toko_nama,toko_kode,brg_nama,brg_kode,ifnull(penerimaan_tgl,'-') as penerimaan_tgl
        from tbl_brg_pengiriman
        inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman and mstr_pengiriman.pengiriman_status = 'aktif' #harus yang jadi terkirim bukan yang dicancel
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_pengiriman.id_fk_cabang #dapetin daerah cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko #dapetin nama toko
        inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan #cuman untuk dapetin id_cabang_minta & id_barang
        inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan and tbl_brg_permintaan.id_fk_cabang = ? #menentukan peminta barang yang akan jadi penerima
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg #dapetin detail barang
        left join tbl_brg_penerimaan on tbl_brg_penerimaan.id_fk_brg_pengiriman = tbl_brg_pengiriman.id_pk_brg_pengiriman and tbl_brg_penerimaan.brg_penerimaan_qty > 0 #dianggap diterima apabila brgditerima > 0 (klo delete, ini ke 0 sendiri)
        left join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan and mstr_penerimaan.penerimaan_status = 'aktif' and mstr_penerimaan.penerimaan_tipe = 'permintaan' #dianggap diterima apabila status penerimaan > 0
        where tbl_brg_pengiriman.brg_pengiriman_qty > 0 #dianggap terkirim apabila tidak di cancel (klo cancel, qty jadi 0)
        and brg_pemenuhan_status = 'perjalanan'
        union
        select id_pk_penerimaan,id_pk_brg_pemenuhan,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,brg_pemenuhan_status,pengiriman_tgl,warehouse_alamat as cabang_daerah,warehouse_nama as toko_nama,'-' as toko_kode,brg_nama,brg_kode,ifnull(penerimaan_tgl,'-') as penerimaan_tgl
        from tbl_brg_pengiriman
        inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman and mstr_pengiriman.pengiriman_status = 'aktif' #harus yang jadi terkirim bukan yang dicancel
        inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = mstr_pengiriman.id_fk_warehouse
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_pengiriman.id_fk_cabang #dapetin daerah cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko #dapetin nama toko
        inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan #cuman untuk dapetin id_cabang_minta & id_barang
        inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan and tbl_brg_permintaan.id_fk_cabang = ? #menentukan peminta barang yang akan jadi penerima
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg #dapetin detail barang
        left join tbl_brg_penerimaan on tbl_brg_penerimaan.id_fk_brg_pengiriman = tbl_brg_pengiriman.id_pk_brg_pengiriman and tbl_brg_penerimaan.brg_penerimaan_qty > 0 #dianggap diterima apabila brgditerima > 0 (klo delete, ini ke 0 sendiri)
        left join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan and mstr_penerimaan.penerimaan_status = 'aktif' and mstr_penerimaan.penerimaan_tipe = 'permintaan' #dianggap diterima apabila status penerimaan > 0
        where tbl_brg_pengiriman.brg_pengiriman_qty > 0 #dianggap terkirim apabila tidak di cancel (klo cancel, qty jadi 0)
        and brg_pemenuhan_status = 'perjalanan'
        order by pengiriman_tgl DESC";
    $args = array(
      $id_cabang, $id_cabang
    );
    //executeQuery($query,$args); echo $this->db->last_query();
    return executeQuery($query, $args);
  }
}
