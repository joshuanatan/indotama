<?php
defined("BASEPATH") or exit("No direct script");
class Retur extends CI_Controller{
    public function __construct(){
        parent::__construct();

        $this->load->library('Pdf_retur');
    }
    public function index($id_pk_retur){
        $data['retur_main'] = executeQuery("SELECT * FROM mstr_retur join mstr_penjualan on mstr_retur.id_fk_penjualan = mstr_penjualan.id_pk_penjualan join mstr_customer on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko WHERE mstr_retur.id_pk_retur = '$id_pk_retur'")->result_array();

        $data['retur_barang'] = executeQuery("SELECT * FROM mstr_retur join mstr_penjualan on mstr_retur.id_fk_penjualan = mstr_penjualan.id_pk_penjualan join tbl_brg_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang WHERE mstr_retur.id_pk_retur = '$id_pk_retur'")->result_array();
        $this->load->view('_plugin_template/pdf/pdf_retur',$data);
    }
}