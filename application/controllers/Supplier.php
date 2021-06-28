<?php
defined("BASEPATH") or exit("No Direct Script");
class Supplier extends CI_Controller
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
    $this->load->view("supplier/v_master_supplier");
  }
  public function detail($id_pk_supplier)
  {
    $data = array(
      "id_pk_supplier" => $id_pk_supplier
    );
    $this->load->view("supplier/v_supplier_detail", $data);
  }

  public function table_pembelian_detail_supplier($id_pk_supplier)
  {
    $data = array(
      "id_pk_supplier" => $id_pk_supplier,
    );
    $this->load->view("supplier/f-detail-supplier", $data);
  }

  public function table_brg_pembelian_detail_supplier($id_pk_supplier)
  {
    $data = array(
      "id_pk_supplier" => $id_pk_supplier
    );
    $this->load->view("supplier/f-detail-supplier-barang", $data);
  }
}
