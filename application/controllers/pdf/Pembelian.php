<?php
defined("BASEPATH") or exit("No direct script");
class Pembelian extends CI_Controller{
    public function __construct(){
        parent::__construct();

        $this->load->library('Pdf_pembelian');
    }
    public function index($id_pk_pembelian){
        $where = array(
            "id_pk_pembelian"=>$id_pk_pembelian
        );
        $data['pembelian_main'] = executeQuery("SELECT * FROM mstr_pembelian join mstr_supplier on mstr_pembelian.id_fk_supp = mstr_supplier.id_pk_sup join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_pembelian.id_fk_cabang join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko WHERE mstr_pembelian.id_pk_pembelian='$id_pk_pembelian'")->result_array();

        $data['pembelian_barang'] = executeQuery("SELECT * FROM mstr_pembelian join tbl_brg_pembelian on mstr_pembelian.id_pk_pembelian = tbl_brg_pembelian.id_fk_pembelian join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_pembelian.id_fk_barang WHERE mstr_pembelian.id_pk_pembelian='$id_pk_pembelian'")->result_array();
        $this->load->view('_plugin_template/pdf/pdf_pembelian',$data);
    }
}