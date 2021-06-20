<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customer extends CI_Controller
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
    $data['toko_dropdown'] = selectRow("mstr_toko", array("toko_status" => "aktif"))->result_array();
    $this->load->view('customer/v_customer', $data);
  }

  public function toko()
  {
    $id_toko = $this->session->id_toko;
    $where = array(
      "id_pk_toko" => $id_toko
    );
    $data['toko'] = selectRow("mstr_toko", $where)->result_array();
    $this->load->view('customer/v_customer_toko', $data);
  }

  public function detail_brg_penjualan_customer($id_customer)
  {
    $data['customer_brg_penjualan'] = executeQuery("SELECT * FROM mstr_customer join mstr_penjualan on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer join tbl_brg_penjualan on tbl_brg_penjualan.id_fk_penjualan = mstr_penjualan.id_pk_penjualan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang WHERE brg_penjualan_status='aktif' or brg_penjualan_status='AKTIF'")->result_array();
    echo json_encode($data);
  }

  public function detail(){
    $this->load->view("customer/v_customer_detail");
  }
}
