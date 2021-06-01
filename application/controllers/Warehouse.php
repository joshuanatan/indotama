<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Warehouse extends CI_Controller
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
    $this->load->view('warehouse/v_warehouse');
  }
  public function admin($id_warehouse)
  {

    $this->load->model("m_warehouse");
    $this->m_warehouse->set_id_pk_warehouse($id_warehouse);
    $result = $this->m_warehouse->detail_by_id();
    $data["warehouse"] = $result->result_array();

    $this->load->view('warehouse_admin/v_master_warehouse_admin', $data);
  }
  public function daftar_akses_gudang()
  {
    $this->load->view('warehouse/v_list_warehouse_admin');
  }
  public function activate_warehouse_manajemen($id_warehouse)
  {
    $this->load->model("m_warehouse");
    $this->m_warehouse->set_id_pk_warehouse($id_warehouse);
    $result = $this->m_warehouse->detail_by_id();
    $result = $result->result_array();
    $this->session->id_warehouse = $result[0]["id_pk_warehouse"];
    $this->session->nama_warehouse = $result[0]["warehouse_nama"];

    redirect("warehouse/daftar_akses_gudang");
  }
  public function brg_warehouse($id_warehouse = "")
  {
    if ($id_warehouse == "") {
      $id_warehouse = $this->session->id_warehouse;
    }
    $data["id_warehouse"] = $id_warehouse;

    $this->load->model("m_warehouse");
    $this->m_warehouse->set_id_pk_warehouse($id_warehouse);
    $result = $this->m_warehouse->detail_by_id();
    $data["warehouse"] = $result->result_array();

    $this->load->view('brg_warehouse/v_brg_warehouse', $data);
  }
  public function pengaturan_warehouse()
  {
    $this->load->view("warehouse/v_pengaturan_warehouse");
  }
}
