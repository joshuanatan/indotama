<?php
defined("BASEPATH") or exit("No direct script");
class Barang extends CI_Controller
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
    $where = array(
      "brg_jenis_status" => "aktif"
    );
    $data['daftar_jenis_barang'] = selectRow("mstr_barang_jenis", $where)->result_array();
    $this->load->view("barang/v_master_barang", $data);
  }
  public function katalog()
  {
    $this->load->view("barang/v_master_barang_katalog");
  }
}
