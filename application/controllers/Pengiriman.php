<?php
defined("BASEPATH") or exit("No Drect Script");
class Pengiriman extends CI_Controller
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
  public function warehouse()
  {
    $data["type"] = "warehouse";
    $data["id_tempat_pengiriman"] = $this->session->id_warehouse;
    $this->load->view("pengiriman/v_pengiriman", $data);
  }
  public function cabang()
  {
    $data["excel"] = array(
      "ctrl_model" => "m_e_pengiriman_penjualan",
      "excel_title" => "Daftar Pengiriman Penjualan"
    );
    $this->load->model("m_satuan");
    $result = $this->m_satuan->list_data();
    $data["satuan"] = $result->result_array();
    $data["type"] = "cabang";
    $data["id_tempat_pengiriman"] = $this->session->id_cabang;
    $data["tipe_pengiriman"] = "penjualan";
    $this->load->view("pengiriman/v_pengiriman", $data);
  }
  public function retur()
  {
    $data["excel"] = array(
      "ctrl_model" => "m_e_pengiriman_retur",
      "excel_title" => "Daftar Pengiriman Retur"
    );
    $this->load->model("m_satuan");
    $result = $this->m_satuan->list_data();
    $data["satuan"] = $result->result_array();

    $data["id_tempat_pengiriman"] = $this->session->id_cabang;
    $data["tipe_pengiriman"] = "retur";
    $data["type"] = "cabang";
    $this->load->view("pengiriman_retur/v_pengiriman_retur", $data);
  }
  public function permintaan()
  {
    $data["excel"] = array(
      "ctrl_model" => "m_e_pengiriman_permintaan",
      "excel_title" => "Daftar Pengiriman Permintaan"
    );

    $this->load->model("m_satuan");
    $result = $this->m_satuan->list_data();
    $data["satuan"] = $result->result_array();

    $data["id_tempat_pengiriman"] = $this->session->id_cabang;
    $data["tipe_pengiriman"] = "permintaan";
    $data["type"] = "cabang";
    $this->load->view("pengiriman_permintaan/v_pengiriman_permintaan", $data);
  }
  public function permintaan_gudang()
  {

    $this->load->model("m_satuan");
    $result = $this->m_satuan->list_data();
    $data["satuan"] = $result->result_array();

    $data["id_tempat_pengiriman"] = $this->session->id_warehouse;
    $data["tipe_pengiriman"] = "permintaan";
    $data["type"] = "warehouse";
    $this->load->view("pengiriman_permintaan/v_pengiriman_permintaan", $data);
  }

  public function view_pengiriman_asli($id_pengiriman, $status_cap)
  {
    $data['cap_status'] = $status_cap;

    $where = array(
      "id_pk_pengiriman" => $id_pengiriman
    );

    $data['pengiriman'] = selectRow("mstr_pengiriman", $where)->result_array();

    if ($data['pengiriman'][0]['id_fk_penjualan'] == 0 or $data['pengiriman'][0]['id_fk_penjualan'] == null or $data['pengiriman'][0]['id_fk_penjualan'] == '') {
      $data['jenis_pengiriman'] = 'pengiriman_retur';

      $data['pengiriman_retur'] = executeQuery("SELECT * FROM mstr_pengiriman join mstr_retur on mstr_retur.id_pk_retur = mstr_pengiriman.id_fk_retur WHERE id_pk_pengiriman=" . $id_pengiriman)->result_array();

      $id_retur = $data['pengiriman_retur'][0]['id_pk_retur'];

      $id_penjualan_retur = get1Value("id_fk_penjualan", "mstr_retur", array("id_pk_retur" => $id_retur));

      $data['customer'] = executeQuery("SELECT * FROM mstr_customer join mstr_penjualan on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust WHERE id_pk_penjualan=" . $id_penjualan_retur)->result_array();
    } else {
      $data['jenis_pengiriman'] = 'pengiriman_penjualan';

      $data['pengiriman_penjualan'] = executeQuery("SELECT * FROM mstr_pengiriman join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_pengiriman.id_fk_penjualan WHERE id_pk_pengiriman=" . $id_pengiriman)->result_array();

      $id_penjualan = $data['pengiriman_penjualan'][0]['id_pk_penjualan'];

      $data['customer'] = executeQuery("SELECT * FROM mstr_customer join mstr_penjualan on mstr_penjualan.id_fk_customer = mstr_customer.id_pk_cust WHERE id_pk_penjualan=" . $id_penjualan)->result_array();
    }



    $data['brg_pengiriman_mstr'] = selectRow("tbl_brg_pengiriman", $where)->result_array();

    if ($data['brg_pengiriman'][0]['id_fk_brg_penjualan'] != 0) {
      $data['brg_pengiriman'] = executeQuery("SELECT * FROM mstr_pengiriman join tbl_brg_penjualan on mstr_pengiriman.id_fk_brg_penjualan = tbl_brg_penjualan.id_pk_brg_penjualan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang WHERE id_pk_pengiriman=" . $id_pengiriman)->result_array();
    } else if ($data['brg_pengiriman'][0]['id_fk_brg_retur_kembali'] != 0) {
      $data['brg_pengiriman'] = executeQuery("SELECT * FROM mstr_pengiriman join tbl_brg_retur_kembali on mstr_pengiriman.id_fk_brg_retur_kembali = tbl_brg_retur_kembali.id_pk_brg_retur_kembali join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_retur_kembali.id_fk_barang WHERE id_pk_pengiriman=" . $id_pengiriman)->result_array();

      // $id_retur = $data['brg_pengiriman'][0]['id_fk_retur'];
      // $id_penjualan = get1Value("id_fk_penjualan","mstr_retur",array("id_pk_retur"=>$id_retur))->result_array();


      // $id_cabang = get1Value("id_fk_cabang","mstr_penjualan",array("id_pk_penjualan"=>$id_penjualan))->result_array();
    } else {
      $data['jenis_pengiriman'] = 'pengiriman_pemenuhan';

      $data['brg_pengiriman'] = executeQuery("SELECT * FROM mstr_pengiriman join tbl_brg_pemenuhan on mstr_pengiriman.id_fk_brg_pemenuhan = tbl_brg_pemenuhan.id_pk_brg_pemenuhan join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_pemenuhan.id_fk_brg_permintaan WHERE id_pk_pengiriman=" . $id_pengiriman)->result_array();

      if ($data['brg_pengiriman'][0]['id_fk_cabang'] == 0) {
        $id_warehouse = $data['brg_pengiriman'][0]['id_fk_warehouse'];

        $data['id_cabang_customer'] = get1Value("id_fk_cabang", "mstr_warehouse", array("id_pk_warehouse" => $id_warehouse))->result_array();
      } else {
        $data['id_cabang_customer'] = $data['brg_pengiriman'][0]['id_fk_cabang'];
      }
    }

    $data['customer_cabang'] = executeQuery("SELECT * FROM mstr_cabang join mstr_toko on mstr_cabang.id_fk_toko = mstr_toko.id_pk_toko WHERE id_pk_cabang =" . $data['id_cabang_customer'])->result_array();


    if ($data['pengiriman'][0]['id_fk_warehouse'] == null or $data['pengiriman'][0]['id_fk_warehouse'] == 0 or $data['pengiriman'][0]['id_fk_warehouse'] == '') {
      $id_cabang = $data['pengiriman'][0]['id_fk_cabang'];
    } else {
      $id_cabang = get1Value("id_fk_cabang", "mstr_warehouse", array("id_pk_warehouse" => $data['pengiriman'][0]['id_fk_warehouse']));
    }
    $data['toko_cabang'] = executeQuery("SELECT * FROM mstr_cabang join mstr_toko on mstr_cabang.id_fk_toko = mstr_toko.id_pk_toko WHERE id_pk_cabang =" . $id_cabang)->result_array();

    $this->load->view("pengiriman/pdf_pengiriman_asli", $data);
  }
}
