<?php
defined("BASEPATH") or exit("No direct script");
class Penawaran extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->check_session();
  }
  public function check_session()
  {
    if ($this->session->id_user == "") {
      $this->session->set_flashdata("msg", "Session expired, please login");
      redirect("login");
    }
  }
  public function index()
  {
    $this->load->view("penawaran/v_master_penawaran");
  }
  
  public function pdf($id_penawaran){
    $where = array(
      "id_pk_penawaran"=>$id_penawaran
    );
    $data['penawaran'] = selectRow("mstr_penawaran",$where)->result_array();
    $data['customer'] = executeQuery("SELECT * FROM mstr_customer join mstr_penawaran on mstr_penawaran.penawaran_refrensi = mstr_customer.id_pk_cust WHERE id_pk_penawaran=" . $id_penawaran)->result_array();
    $data['brg_penawaran'] = executeQuery("SELECT * FROM tbl_brg_penawaran join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penawaran.id_fk_brg WHERE id_fk_penawaran=".$id_penawaran)->result_array();
    
    $id_cabang = $data['penawaran'][0]['id_fk_cabang'];

    $data['toko_cabang'] = executeQuery("SELECT * FROM mstr_cabang join mstr_toko on mstr_cabang.id_fk_toko = mstr_toko.id_pk_toko WHERE id_pk_cabang =".$id_cabang)->result_array();

    $this->load->view("penawaran/pdf_penawaran",$data);
  }
}
