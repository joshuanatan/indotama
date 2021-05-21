<?php
defined("BASEPATH") or exit("No direct script");
class Retur extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->check_session();
    }
    public function check_session(){
        if($this->session->id_user == ""){
            $this->session->set_flashdata("msg","Session expired, please login");
            redirect("login");
        }
    }
    public function index(){
        $this->load->view("retur/v_retur");
    }
    public function konfirmasi(){
        $this->load->view("retur/v_konfirmasi_retur");
    }

    public function view_retur_asli($id_retur,$status_cap){
        $data['cap_status'] = $status_cap;

        $where = array(
          "id_pk_retur"=>$id_retur
        );

        $data['retur'] = selectRow("mstr_retur",$where)->result_array();
        $data['retur_penjualan'] = executeQuery("SELECT * FROM mstr_retur join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan WHERE id_pk_retur=" . $id_retur)->result_array();

        $id_penjualan = $data['retur_penjualan'][0]['id_pk_penjualan'];
        $data['customer'] = executeQuery("SELECT * FROM mstr_customer join mstr_penjualan on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust WHERE id_pk_penjualan=" . $id_penjualan)->result_array();

        $data['brg_retur'] = executeQuery("SELECT * FROM tbl_retur_kembali join mstr_barang on mstr_barang.id_pk_brg = tbl_retur_kembali.id_fk_brg WHERE id_fk_retur=".$id_retur)->result_array();

        $data['brg_penjualan'] = executeQuery("SELECT * FROM tbl_brg_penjualan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang WHERE id_fk_penjualan=".$id_penjualan)->result_array();
        

        
        $id_cabang = $data['retur_penjualan'][0]['id_fk_cabang'];
    
        $data['toko_cabang'] = executeQuery("SELECT * FROM mstr_cabang join mstr_toko on mstr_cabang.id_fk_toko = mstr_toko.id_pk_toko WHERE id_pk_cabang =".$id_cabang)->result_array();
    
        $this->load->view("retur/pdf_retur_asli",$data);
      }
}