<?php
defined("BASEPATH") or exit("No Direct Script");
class Barang_pindah extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function register()
  {
    $response["content"] = false;
    $check = $this->input->post("custom");
    if ($this->input->get("id_ref")) {
      $id_fk_refrensi_sumber = $this->input->get("id_ref");
    } else {
      $id_fk_refrensi_sumber = "0";
    }
    if ($check != "") {
      $counter = 0;
      foreach ($check as $a) {
        $brg_pindah_sumber = $this->input->get("sumber");
        $brg_pindah_qty = $this->input->post("custom_brg_qty" . $a);
        $brg_pindah_status = "AKTIF";

        $barang_awal = $this->input->post("custom_brg_awal" . $a);
        $this->load->model("m_barang");
        $this->m_barang->set_brg_nama($barang_awal);
        $result = $this->m_barang->detail_by_name();
        $flag = true;
        if ($result->num_rows() > 0) {
          $result = $result->result_array();
          $id_brg_awal = $result[0]["id_pk_brg"];

          $this->load->model("m_brg_cabang");
          $this->m_brg_cabang->set_id_fk_brg($id_brg_awal);
          $this->m_brg_cabang->set_id_fk_cabang($this->session->id_cabang);
          $result = $this->m_brg_cabang->detail_by_id_barang();
          if ($result->num_rows() > 0) {
            $result = $result->result_array();
            $stok = $result[0]["brg_cabang_qty"];
            if ($stok < $brg_pindah_qty) {
              $response["brgpindahsts"] = "ERROR";
              $response["brgpindahmsg"] = "Barang dipindahkan dibawah stok";
              continue;
            }
          } else {
            $flag = false;
          }
        } else $flag = false;

        if ($flag) {
          $barang_akhir = $this->input->post("custom_brg_akhir" . $a);
          $this->load->model("m_barang");
          $this->m_barang->set_brg_nama($barang_akhir);
          $result = $this->m_barang->detail_by_name();
          if ($result->num_rows() > 0) {
            $result = $result->result_array();
            $id_brg_tujuan = $result[0]["id_pk_brg"];;
          } else {
            $flag = false;
          }
        }

        if ($flag) {
          $this->load->model("m_brg_pindah");
          if ($this->m_brg_pindah->set_insert($brg_pindah_sumber, $id_fk_refrensi_sumber, $id_brg_awal, $id_brg_tujuan, $brg_pindah_qty, $brg_pindah_status)) {
            $id_brg_pindah = $this->m_brg_pindah->insert();
            if ($id_brg_pindah) {
              $response["brgpindahsts"] = "SUCCESS";
              $response["brgpindahmsg"] = "Data is recorded to database";

              $response["content"][$counter]["id_brg_pindah"] = $id_brg_pindah;
              $response["content"][$counter]["nama_brg_awal"] = $barang_awal;
              $response["content"][$counter]["nama_brg_akhir"] = $barang_akhir;
              $response["content"][$counter]["qty"] = $brg_pindah_qty;
              $counter++;
            } else {
              $response["brgpindahsts"] = "ERROR";
              $response["brgpindahmsg"] = "Insert function error";
            }
          } else {
            $response["brgpindahsts"] = "ERROR";
            $response["brgpindahmsg"] = "Setter function error";
          }
        } else {
          $response["brgpindahsts"] = "ERROR";
          $response["brgpindahmsg"] = "Item tidak terdaftar";
        }
      }
    } else {
      $response["content"] = false;
    }
    echo json_encode($response);
  }
}
