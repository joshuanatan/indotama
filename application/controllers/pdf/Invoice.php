<?php
defined("BASEPATH") or exit("No direct script");
class Invoice extends CI_Controller{
    public function __construct(){
        parent::__construct();

        $this->load->library('Pdf_invoice_asli');
        $this->load->library('Pdf_invoice_copy');
    }
    public function index($id_pk_penjualan){
        $where = array(
            "id_pk_penjualan"=>$id_pk_penjualan
        );
        $data['penjualan_main'] = executeQuery("SELECT * FROM mstr_penjualan join mstr_customer on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko WHERE mstr_penjualan.id_pk_penjualan='$id_pk_penjualan'")->result_array();

        $data['penjualan_brg'] = executeQuery("SELECT * FROM mstr_penjualan join tbl_brg_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang WHERE mstr_penjualan.id_pk_penjualan='$id_pk_penjualan'")->result_array();
        $this->load->view('_plugin_template/pdf/pdf_invoice',$data);
    }

    public function copy($id_pk_penjualan){
        $where = array(
            "id_pk_penjualan"=>$id_pk_penjualan
        );
        $data['penjualan_main'] = executeQuery("SELECT * FROM mstr_penjualan join mstr_customer on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko WHERE mstr_penjualan.id_pk_penjualan='$id_pk_penjualan'")->result_array();

        $data['penjualan_brg'] = executeQuery("SELECT * FROM mstr_penjualan join tbl_brg_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang WHERE mstr_penjualan.id_pk_penjualan='$id_pk_penjualan'")->result_array();
        $this->load->view('_plugin_template/pdf/pdf_invoice_copy',$data);
    }
}