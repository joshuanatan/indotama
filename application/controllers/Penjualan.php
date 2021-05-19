<?php
class Penjualan extends CI_Controller
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
    $this->load->view("penjualan/v_penjualan");
  }
  public function tambah()
  {
    $this->load->view("penjualan/f-add-penjualan");
  }
  public function update($id_penjualan)
  {
    $this->load->model("m_penjualan");
    $this->m_penjualan->set_id_pk_penjualan($id_penjualan);
    $result = $this->m_penjualan->detail_by_id_pk_penjualan();
    $data["content"]["detail"] = $result->result_array();
    if (strtolower($data["content"]["detail"][0]["penj_jenis"]) == "online") {
      $this->load->model("m_penjualan_online");
      $this->m_penjualan_online->set_id_fk_penjualan($id_penjualan);
      $result = $this->m_penjualan_online->detail();
      $data["content"]["online"] = $result->result_array();
    } else {
      $data["content"]["online"] = false;
    }

    $this->load->model("m_brg_pindah");
    $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_penjualan);
    $this->m_brg_pindah->set_brg_pindah_sumber("penjualan");
    $result = $this->m_brg_pindah->list_data();
    $data["content"]["brg_custom"] = $result->result_array();

    $this->load->model("m_brg_penjualan");
    $this->m_brg_penjualan->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_brg_penjualan->list_data();
    $data["content"]["item"] = $result->result_array();

    $this->load->model("m_tambahan_penjualan");
    $this->m_tambahan_penjualan->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_tambahan_penjualan->list_data();
    $data["content"]["tambahan"] = $result->result_array();

    $this->load->model("m_penjualan_pembayaran");
    $this->m_penjualan_pembayaran->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_penjualan_pembayaran->list_data();
    $data["content"]["pembayaran"] = $result->result_array();

    $data["content"]["id_penjualan"] = $id_penjualan;
    $this->load->view("penjualan/f-update-penjualan", $data);
  }
  public function detail($id_penjualan)
  {
    $this->load->model("m_penjualan");
    $this->m_penjualan->set_id_pk_penjualan($id_penjualan);
    $result = $this->m_penjualan->detail_by_id_pk_penjualan();
    $data["detail"] = $result->result_array();
    if (strtolower($data["detail"][0]["penj_jenis"]) == "online") {
      $this->load->model("m_penjualan_online");
      $this->m_penjualan_online->set_id_fk_penjualan($id_penjualan);
      $result = $this->m_penjualan_online->detail();
      $data["online"] = $result->result_array();
    } else {
      $data["online"] = false;
    }

    $this->load->model("m_brg_pindah");
    $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_penjualan);
    $this->m_brg_pindah->set_brg_pindah_sumber("penjualan");
    $result = $this->m_brg_pindah->list_data();
    $data["brg_custom"] = $result->result_array();

    $this->load->model("m_brg_penjualan");
    $this->m_brg_penjualan->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_brg_penjualan->list_data();
    $data["item"] = $result->result_array();

    $this->load->model("m_tambahan_penjualan");
    $this->m_tambahan_penjualan->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_tambahan_penjualan->list_data();
    $data["tambahan"] = $result->result_array();

    $this->load->model("m_penjualan_pembayaran");
    $this->m_penjualan_pembayaran->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_penjualan_pembayaran->list_data();
    $data["pembayaran"] = $result->result_array();

    $data["id_penjualan"] = $id_penjualan;
    $this->load->view("penjualan/f-detail-penjualan", $data);
  }

  public function view_invoice_asli($id_penjualan,$status_cap){
    $data['cap_status'] = $status_cap;
    $where = array(
      "id_pk_penjualan"=>$id_penjualan
    );
    $data['penjualan'] = selectRow("mstr_penjualan",$where)->result_array();
    $data['customer'] = executeQuery("SELECT * FROM mstr_customer join mstr_penjualan on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust WHERE id_pk_penjualan=" . $id_penjualan)->result_array();
    $data['brg_penjualan'] = executeQuery("SELECT * FROM tbl_brg_penjualan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang WHERE id_fk_penjualan=".$id_penjualan)->result_array();
    
    $id_cabang = $data['penjualan'][0]['id_fk_cabang'];

    $data['toko_cabang'] = executeQuery("SELECT * FROM mstr_cabang join mstr_toko on mstr_cabang.id_fk_toko = mstr_toko.id_pk_toko WHERE id_pk_cabang =".$id_cabang)->result_array();

    $this->load->view("penjualan/pdf_invoice_asli",$data);
  }

}
