<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class Pengiriman_permintaan extends CI_Controller
{
  #class ini dibuat untuk tidak memusingkan proses insert yang berbeda dengan pengiriman & penerimaan yang lain.
  #ada potensi memusingkan karena proses pemberian itu langsung brg_pemberian ga ada master pemberian. oleh karena itu waktu pengiriman dan penerimaan ga punya id_pemberian yang bisa jadi acuan
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_t_pengiriman_permintaan");
    $columns = $this->m_t_pengiriman_permintaan->columns();
    if (count($columns) > 0) {
      for ($a = 0; $a < count($columns); $a++) {
        $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function content()
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;
    $pengiriman_tempat = $this->input->get("type");
    if (strtolower($pengiriman_tempat) == "cabang") {
      $id_tempat_pengiriman = $this->session->id_cabang;
    } else if (strtolower($pengiriman_tempat) == "warehouse") {
      $id_tempat_pengiriman = $this->session->id_warehouse;
    }
    $this->load->model("m_t_pengiriman_permintaan");

    $result = $this->m_t_pengiriman_permintaan->content($page, $order_by, $order_direction, $search_key, $data_per_page, $pengiriman_tempat, $id_tempat_pengiriman);
    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg_pemenuhan"];
        $response["content"][$a]["id_pengiriman"] = $result["data"][$a]["id_pk_pengiriman"];
        $response["content"][$a]["nama_brg"] = $result["data"][$a]["brg_nama"];
        $response["content"][$a]["pemenuhan_qty_brg"] = number_format($result["data"][$a]["brg_pemenuhan_qty"], 2, ",", ".");
        $response["content"][$a]["daerah_cabang"] = $result["data"][$a]["cabang_daerah"];
        $response["content"][$a]["nama_toko"] = $result["data"][$a]["toko_nama"];
        $response["content"][$a]["kode_toko"] = $result["data"][$a]["toko_kode"];
        $response["content"][$a]["logo_toko"] = $result["data"][$a]["toko_logo"];
        $response["content"][$a]["permintaan_status_brg"] = $result["data"][$a]["brg_permintaan_status"];
        $response["content"][$a]["status"] = $result["data"][$a]["brg_pemenuhan_status"];
        $response["content"][$a]["tgl_pengiriman"] = $result["data"][$a]["pengiriman_tgl"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["pengiriman_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "nama_brg",
      "pemenuhan_qty_brg",
      "nama_toko",
      "daerah_cabang",
      "status",
      "tgl_pengiriman",
      "last_modified"
    );
    echo json_encode($response);
  }
  public function histori_tgl()
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();
    $tgl = $this->input->get("tgl_buat_permintaan");

    $this->load->model("m_t_pengiriman_permintaan");

    $result = $this->m_t_pengiriman_permintaan->histori_tgl($tgl);
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_brg_pemenuhan"];
        $response["content"][$a]["id_pengiriman"] = $result[$a]["id_pk_pengiriman"];
        $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
        $response["content"][$a]["pemenuhan_qty_brg"] = number_format($result[$a]["brg_pemenuhan_qty"], "2", ",", ".");
        $response["content"][$a]["daerah_cabang"] = $result[$a]["cabang_daerah"];
        $response["content"][$a]["notes"] = $result[$a]["brg_pengiriman_note"];
        $response["content"][$a]["nama_toko"] = $result[$a]["toko_nama"];
        $response["content"][$a]["kode_toko"] = $result[$a]["toko_kode"];
        $response["content"][$a]["logo_toko"] = $result[$a]["toko_logo"];
        $response["content"][$a]["permintaan_status_brg"] = $result[$a]["brg_permintaan_status"];
        $response["content"][$a]["status_brg_pemenuhan"] = $result[$a]["brg_pemenuhan_status"];
        $response["content"][$a]["tgl_pengiriman"] = $result[$a]["pengiriman_tgl"];
        $response["content"][$a]["last_modified"] = $result[$a]["pengiriman_last_modified"];

        switch (strtolower($result[$a]["brg_pemenuhan_status"])) {
          case "aktif":
            $response["content"][$a]["status"] = "MENUNGGU PENGIRIMAN";
            $response["content"][$a]["status_code"] = "warning";
            break;
          case "diterima":
            $response["content"][$a]["status"] = "DITERIMA";
            $response["content"][$a]["status_code"] = "success";
            break;
          case "nonaktif":
            $response["content"][$a]["status"] = "DIBATALKAN";
            $response["content"][$a]["status_code"] = "danger";
            break;
          case "perjalanan":
            $response["content"][$a]["status"] = "DALAM PERJALANAN";
            $response["content"][$a]["status_code"] = "default";
            break;
        }
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "SUCCESS";
    $pengiriman_tgl = date("Y-m-d H:i:s");
    $pengiriman_status = "aktif";
    $pengiriman_tempat = $this->input->post("type");
    $pengiriman_tipe = $this->input->post("tipe_pengiriman");
    $id_tempat_pengiriman = $this->input->post("id_tempat_pengiriman");

    $this->load->model("m_pengiriman");

    $id_fk_cabang = $this->session->id_cabang;
    $pengiriman_no = $this->m_pengiriman->get_pengiriman_nomor($id_fk_cabang, "pengiriman", $pengiriman_tgl);

    if ($this->m_pengiriman->set_insert($pengiriman_no, $pengiriman_tgl, $pengiriman_status, $pengiriman_tipe, "", $pengiriman_tempat, $id_tempat_pengiriman, "")) {
      $id_pengiriman = $this->m_pengiriman->insert();
      if ($id_pengiriman) {
        $this->load->model("m_brg_pengiriman");
        $brg_pengiriman_qty = $this->input->post("brg_pengiriman_qty");
        $brg_pengiriman_note = "-";
        $id_fk_pengiriman = $id_pengiriman;
        $id_fk_brg_pemenuhan = $this->input->post("id");

        $this->load->model("m_satuan");
        $result = $this->m_satuan->list_data();
        if ($result->num_rows() > 0) {
          $result = $result->result_array();
          $where = array(
            "satuan_rumus" => "1"
          );
          $field = array(
            "id_pk_satuan"
          );
          $result = selectRow("mstr_satuan", $where, $field);
          $result = $result->result_array();
          $id_fk_satuan = $result[0]["id_pk_satuan"];
        } else {
          $satuan_nama = "Pcs";
          $satuan_status = "aktif";
          $satuan_rumus = "1";
          $this->m_satuan->set_insert($satuan_nama, $satuan_status, $satuan_rumus);
          $this->m_satuan->insert();
        }

        if ($this->m_brg_pengiriman->set_insert($brg_pengiriman_qty, $brg_pengiriman_note, $id_fk_pengiriman, "", $id_fk_satuan, "", $id_fk_brg_pemenuhan)) {
          if ($this->m_brg_pengiriman->insert()) {
            $this->load->model("m_brg_pemenuhan");
            $this->m_brg_pemenuhan->set_id_pk_brg_pemenuhan($id_fk_brg_pemenuhan);
            $this->m_brg_pemenuhan->set_brg_pemenuhan_status("Perjalanan");
            $this->m_brg_pemenuhan->update_status();
          } else {
            $response["status"] = "ERROR";
            $response["msg"] = "Insert item function error";
          }
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Setter item error";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Insert function error";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Setter error";
    }

    echo json_encode($response);
  }
  public function delete()
  {
    $response["status"] = "SUCCESS";
    $id = $this->input->get("id");
    if ($id != "" && is_numeric($id)) {
      $this->load->model("m_pengiriman");
      if ($this->m_pengiriman->set_delete($id)) {
        if ($this->m_pengiriman->delete()) {
          $this->load->model("m_brg_pengiriman");
          $this->m_brg_pengiriman->set_id_fk_pengiriman($id);
          $this->m_brg_pengiriman->delete_brg_pengiriman();
          $response["msg"] = "Data is deleted from database";

          $id_fk_brg_pemenuhan = $this->input->get("id_brg");
          $this->load->model("m_brg_pemenuhan");
          $this->m_brg_pemenuhan->set_id_pk_brg_pemenuhan($id_fk_brg_pemenuhan);
          $this->m_brg_pemenuhan->set_brg_pemenuhan_status("Aktif");
          $this->m_brg_pemenuhan->update_status();
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Delete function error";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Setter function error";
      }
    }
    echo json_encode($response);
  }
}
