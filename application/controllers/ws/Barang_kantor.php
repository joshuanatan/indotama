<?php
defined("BASEPATH") or exit("No direct script");
class Barang_kantor extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_barang_kantor");
    $columns = $this->m_barang_kantor->columns();
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

    $this->load->model("m_barang_kantor");
    $result = $this->m_barang_kantor->content($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg"];
        $response["content"][$a]["kode"] = $result["data"][$a]["brg_kode"];
        $response["content"][$a]["nama"] = $result["data"][$a]["brg_nama"];
        $response["content"][$a]["ket"] = $result["data"][$a]["brg_ket"];
        $response["content"][$a]["minimal"] = number_format($result["data"][$a]["brg_minimal"], 0, ",", ".");
        $response["content"][$a]["status"] = $result["data"][$a]["brg_status"];
        $response["content"][$a]["satuan"] = $result["data"][$a]["brg_satuan"];
        $response["content"][$a]["image"] = $result["data"][$a]["brg_image"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_last_modified"];
        $response["content"][$a]["merk"] = $result["data"][$a]["brg_merk_nama"];
        $response["content"][$a]["jenis"] = $result["data"][$a]["brg_jenis_nama"];
        $response["content"][$a]["tipe"] = $result["data"][$a]["brg_tipe"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "SUCCESS";
    $this->form_validation->set_rules("kode", "kode", "required");
    $this->form_validation->set_rules("nama", "nama", "required");
    $this->form_validation->set_rules("minimal", "minimal", "required");
    $this->form_validation->set_rules("satuan", "satuan", "required");
    $this->form_validation->set_rules("id_brg_merk", "id_brg_merk", "required");

    if ($this->form_validation->run()) {
      $this->load->model("m_barang_kantor");
      $brg_kode = $this->input->post("kode");
      $brg_nama = $this->input->post("nama");
      $brg_ket = $this->input->post("keterangan");
      $brg_minimal = $this->input->post("minimal");
      $brg_satuan = $this->input->post("satuan");
      $brg_tipe = $this->input->post("tipe");
      $brg_status = "AKTIF";

      $id_fk_brg_jenis = 0; #barang kantor

      $id_fk_brg_merk = $this->input->post("id_brg_merk");
      $this->load->model("m_barang_merk");
      if ($this->m_barang_merk->set_brg_merk_nama($id_fk_brg_merk)) {
        $result = $this->m_barang_merk->detail_by_name();
        if ($result->num_rows() > 0) {
          $result = $result->result_array();
          $id_fk_brg_merk = $result[0]["id_pk_brg_merk"];
        } else {
          $brg_merk_nama = $id_fk_brg_merk;
          $brg_merk_status = "AKTIF";
          if ($this->m_barang_merk->set_insert($brg_merk_nama, $brg_merk_status)) {
            $id_insert = $this->m_barang_merk->insert();
            if ($id_insert) {
              $id_fk_brg_merk = $id_insert;
            }
          }
        }
      }

      $config['upload_path'] = './asset/uploads/barang/';
      $config['allowed_types'] = 'gif|jpg|png';
      $config['overwrite'] = TRUE;
      $config['file_name'] = "barang_" . $brg_kode;

      $this->load->library('upload', $config);
      $brg_image = "noimage.jpg";
      if ($this->upload->do_upload('gambar')) {
        $p1 = array("upload_data" => $this->upload->data());
        $brg_image = $p1['upload_data']['file_name'];
      }
      if ($this->m_barang_kantor->set_insert($brg_kode, $brg_nama, $brg_ket, $brg_minimal, $brg_satuan, $brg_image, $brg_status, $id_fk_brg_jenis, $id_fk_brg_merk, 0, 0, 0, $brg_tipe)) {
        $id_barang = $this->m_barang_kantor->insert();
        if ($id_barang) {
          $response["msg"] = "Data is recorded to database";
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Error message: Loss Session (need re-login) / Kode ganda / Nama ganda";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Error message: Data tidak lengkap, tolong di crosscheck";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = validation_errors();
    }
    echo json_encode($response);
  }
  public function update()
  {
    $response["status"] = "SUCCESS";
    $this->form_validation->set_rules("id", "id", "required");
    $this->form_validation->set_rules("kode", "kode", "required");
    $this->form_validation->set_rules("nama", "nama", "required");
    $this->form_validation->set_rules("minimal", "minimal", "required");
    $this->form_validation->set_rules("satuan", "satuan", "required");
    $this->form_validation->set_rules("id_brg_merk", "id_brg_merk", "required");

    if ($this->form_validation->run()) {
      $this->load->model("m_barang_kantor");
      $id_pk_barang = $this->input->post("id");
      $brg_kode = $this->input->post("kode");
      $brg_nama = $this->input->post("nama");
      $brg_ket = $this->input->post("keterangan");
      $brg_minimal = $this->input->post("minimal");
      $brg_satuan = $this->input->post("satuan");
      $brg_tipe = $this->input->post("tipe");

      $id_fk_brg_jenis = 0; #barang kantor
      $id_fk_brg_merk = $this->input->post("id_brg_merk");
      $this->load->model("m_barang_merk");
      if ($this->m_barang_merk->set_brg_merk_nama($id_fk_brg_merk)) {
        $result = $this->m_barang_merk->detail_by_name();
        if ($result->num_rows() > 0) {
          $result = $result->result_array();
          $id_fk_brg_merk = $result[0]["id_pk_brg_merk"];
        } else {
          $brg_merk_nama = $id_fk_brg_merk;
          $brg_merk_status = "AKTIF";
          if ($this->m_barang_merk->set_insert($brg_merk_nama, $brg_merk_status)) {
            $id_insert = $this->m_barang_merk->insert();
            if ($id_insert) {
              $id_fk_brg_merk = $id_insert;
            }
          }
        }
      }

      $config['upload_path'] = './asset/uploads/barang/';
      $config['allowed_types'] = 'gif|jpg|png';

      $this->load->library('upload', $config);
      $brg_image = $this->input->post("gambar_current");
      if ($this->upload->do_upload('gambar')) {
        $brg_image = $this->upload->data("file_name");
      }
      if ($this->m_barang_kantor->set_update($id_pk_barang, $brg_kode, $brg_nama, $brg_ket, $brg_minimal, $brg_satuan, $brg_image, $id_fk_brg_jenis, $id_fk_brg_merk, 0, 0, 0, $brg_tipe)) {
        if ($this->m_barang_kantor->update()) {
          $response["msg"] = "Data is updated to database";
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Error message: <br/>Loss Session (need re-login) / Kode ganda / Nama ganda";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Error message: Data tidak lengkap, tolong di crosscheck";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = validation_errors();
    }
    echo json_encode($response);
  }
  public function delete()
  {
    $response["status"] = "SUCCESS";
    $id_pk_barang = $this->input->get("id");
    if ($id_pk_barang != "" && is_numeric($id_pk_barang)) {
      $this->load->model("m_barang_kantor");
      if ($this->m_barang_kantor->set_delete($id_pk_barang)) {
        if ($this->m_barang_kantor->delete()) {
          $response["msg"] = "Data is deleted from database";
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Update function is error";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Setter function is error";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "ID is invalid";
    }
    echo json_encode($response);
  }
  public function jmlh_brg_total()
  {
    $sql = "
        select id_pk_brg,brg_nama,brg_kode,ifnull(sum(jmlh_brg),0) as jmlh_brg from mstr_barang 
        left join (
        select sum(brg_cabang_qty) as jmlh_brg,id_fk_brg from tbl_brg_cabang where brg_cabang_status = 'aktif' group by id_fk_brg
        union
        select sum(brg_warehouse_qty) as jmlh_brg,id_fk_brg from tbl_brg_warehouse where brg_warehouse_status = 'aktif' group by id_fk_brg
        ) as a on a.id_fk_brg = mstr_barang.id_pk_brg where brg_status = 'aktif' and id_pk_brg = ? group by id_pk_brg";
    $args = array(
      $this->id_pk_brg
    );
    return executeQuery($sql, $args);
  }
}