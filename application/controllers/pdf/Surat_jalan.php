<?php
defined("BASEPATH") or exit("No direct script");
class Surat_jalan extends CI_Controller{
    public function __construct(){
        parent::__construct();

        $this->load->library('Pdf_surat_jalan');
    }
    public function index($id_pk_pengiriman){
        $where = array(
            "id_pk_pengiriman"=>$id_pk_pengiriman
        );
        $data['pengiriman_main'] = executeQuery("SELECT * FROM mstr_pengiriman join mstr_penjualan on mstr_pengiriman.id_fk_penjualan = mstr_penjualan.id_pk_penjualan join mstr_customer on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko WHERE mstr_pengiriman.id_pk_pengiriman = '$id_pk_pengiriman'")->result_array();

        $data['pengiriman_brg'] = executeQuery("SELECT * FROM mstr_pengiriman join mstr_penjualan on mstr_pengiriman.id_fk_penjualan = mstr_penjualan.id_pk_penjualan join tbl_brg_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_pengiriman.id_fk_brg_penjualan join mstr_satuan on mstr_satuan.id_pk_satuan = tbl_brg_pengiriman.id_fk_satuan WHERE mstr_pengiriman.id_pk_pengiriman = '$id_pk_pengiriman'")->result_array();
        $this->load->view('_plugin_template/pdf/pdf_surat_jalan',$data);
    }
}