<?php
defined("BASEPATH") or exit("No direct script");
class Warehouse extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_warehouse");
    $columns = $this->m_warehouse->columns();
    if (count($columns) > 0) {
      for ($a = 0; $a < count($columns); $a++) {
        $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function columns_warehouse_cabang()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_warehouse");
    $colums_warehouse_cabang = $this->m_warehouse->columns_warehouse_cabang();
    if (count($colums_warehouse_cabang) > 0) {
      for ($a = 0; $a < count($colums_warehouse_cabang); $a++) {
        $response["content"][$a]["col_name"] = $colums_warehouse_cabang[$a]["col_disp"];
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

    $this->load->model("m_warehouse");
    $result = $this->m_warehouse->content($page, $order_by, $order_direction, $search_key, $data_per_page);
    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_warehouse"];
        $response["content"][$a]["nama"] = $result["data"][$a]["warehouse_nama"];
        $nama_cabang = "";
        if ($result["data"][$a]["cabang_nama"] == "") {
          $nama_cabang = "-not registered-";
          $response["content"][$a]["nama_cabang"] = $nama_cabang;
        } else {
          $nama_cabang = $result["data"][$a]["cabang_nama"];
          $response["content"][$a]["nama_cabang"] = $nama_cabang;
        }
        $response["content"][$a]["alamat"] = $result["data"][$a]["warehouse_alamat"];
        $response["content"][$a]["notelp"] = $result["data"][$a]["warehouse_notelp"];
        $response["content"][$a]["desc"] = $result["data"][$a]["warehouse_desc"];
        $response["content"][$a]["status"] = $result["data"][$a]["warehouse_status"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["warehouse_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "nama",
      "alamat",
      "notelp",
      "desc",
      "nama_cabang",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }

  public function content_warehouse_cabang()
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;
    $id_cabang = $this->input->get("id_cabang");

    $this->load->model("m_warehouse");
    $result = $this->m_warehouse->content_warehouse_cabang($page, $order_by, $order_direction, $search_key, $data_per_page, $id_cabang);
    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_warehouse"];
        $response["content"][$a]["nama"] = $result["data"][$a]["warehouse_nama"];
        $response["content"][$a]["alamat"] = $result["data"][$a]["warehouse_alamat"];
        $response["content"][$a]["notelp"] = $result["data"][$a]["warehouse_notelp"];
        $response["content"][$a]["nama_cabang"] = $result["data"][$a]["cabang_nama"];
        $response["content"][$a]["desc"] = $result["data"][$a]["warehouse_desc"];
        $response["content"][$a]["status"] = $result["data"][$a]["warehouse_status"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["warehouse_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "nama",
      "alamat",
      "notelp",
      "desc",
      "nama_cabang",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }

  public function register()
  {
    $response["status"] = "SUCCESS";
    $this->form_validation->set_rules("warehouse_nama", "Nama Warehouse", "required");
    $this->form_validation->set_rules("warehouse_alamat", "Alamat", "required");
    $this->form_validation->set_rules("warehouse_notelp", "No Telepon", "required");
    $this->form_validation->set_rules("warehouse_desc", "Deskripsi", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_warehouse");
      $warehouse_nama = $this->input->post("warehouse_nama");
      $warehouse_alamat = $this->input->post("warehouse_alamat");
      $warehouse_notelp = $this->input->post("warehouse_notelp");
      $warehouse_desc = $this->input->post("warehouse_desc");
      if ($this->input->post("warehouse_cabang") != "") {
        $nama_cabang = $this->input->post("warehouse_cabang");
        $where = array(
          "cabang_nama" => $nama_cabang
        );
        if (isExistsInTable("mstr_cabang", $where)) {
          $id_cabang = get1Value("mstr_cabang", "id_pk_cabang", $where);
        } else {
          $dataa = array(
            "cabang_nama" => $nama_cabang,
            "cabang_kode" => "-",
            "cabang_daerah" => "-",
            "cabang_kop_surat" => "-",
            "cabang_nonpkp" => "-",
            "cabang_pernyataan_rek" => "-",
            "cabang_notelp" => "-",
            "cabang_alamat" => "-",
            "cabang_status" => "-",
            "cabang_create_date" => date("Y-m-d h:i:s"),
            "cabang_last_modified" => date("Y-m-d h:i:s"),
            "id_create_data" => $this->session->id_user,
            "id_last_modified" => $this->session->id_user,
            "id_fk_toko" => 0
          );
          $id_cabang = insertRow("mstr_cabang", $dataa);
        }
      } else {
        $id_cabang = "-1";
      }
      $warehouse_status = "AKTIF";
      if ($this->m_warehouse->set_insert($warehouse_nama, $warehouse_alamat, $warehouse_notelp, $warehouse_desc, $id_cabang, $warehouse_status)) {
        if ($this->m_warehouse->insert()) {
          $response["msg"] = "Data is recorded to database";
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Insert function error";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Setter function error";
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
    $this->form_validation->set_rules("warehouse_nama", "Nama Warehouse", "required");
    $this->form_validation->set_rules("warehouse_alamat", "Alamat", "required");
    $this->form_validation->set_rules("warehouse_notelp", "No Telepon", "required");
    $this->form_validation->set_rules("warehouse_desc", "Deskripsi", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_warehouse");
      $id_pk_warehouse = $this->input->post("id");
      $warehouse_nama = $this->input->post("warehouse_nama");
      $warehouse_alamat = $this->input->post("warehouse_alamat");
      $warehouse_notelp = $this->input->post("warehouse_notelp");
      $warehouse_desc = $this->input->post("warehouse_desc");


      if ($this->input->post("warehouse_cabang") != "") {
        $nama_cabang = $this->input->post("warehouse_cabang");
        $where = array(
          "cabang_nama" => $nama_cabang
        );
        if (isExistsInTable("mstr_cabang", $where)) {
          $id_cabang = get1Value("mstr_cabang", "id_pk_cabang", $where);
        } else {
          $dataa = array(
            "cabang_nama" => $nama_cabang,
            "cabang_kode" => "-",
            "cabang_daerah" => "-",
            "cabang_kop_surat" => "-",
            "cabang_nonpkp" => "-",
            "cabang_pernyataan_rek" => "-",
            "cabang_notelp" => "-",
            "cabang_alamat" => "-",
            "cabang_status" => "-",
            "cabang_create_date" => date("Y-m-d h:i:s"),
            "cabang_last_modified" => date("Y-m-d h:i:s"),
            "id_create_data" => $this->session->id_user,
            "id_last_modified" => $this->session->id_user,
            "id_fk_toko" => 0
          );
          $id_cabang = insertRow("mstr_cabang", $dataa);
        }
      } else {
        $id_cabang = "-1";
      }



      if ($this->m_warehouse->set_update($id_pk_warehouse, $warehouse_nama, $warehouse_alamat, $warehouse_notelp, $warehouse_desc, $id_cabang)) {
        if ($this->m_warehouse->update()) {
          $response["msg"] = "Data is updated to database";
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Update function error";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Setter function error";
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
    $id_warehouse = $this->input->get("id");
    if ($id_warehouse != "" && is_numeric($id_warehouse)) {
      $id_pk_warehouse = $id_warehouse;
      $this->load->model("m_warehouse");
      if ($this->m_warehouse->set_delete($id_pk_warehouse)) {
        if ($this->m_warehouse->delete()) {
          $response["msg"] = "Data is removed to database";
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Delete function error";
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Setter function error";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Invalid ID";
    }
    echo json_encode($response);
  }
  public function list_gudang_admin()
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;

    $this->load->model("m_warehouse_admin");
    $this->m_warehouse_admin->set_id_fk_user($this->session->id_user);
    $result = $this->m_warehouse_admin->list_gudang_admin($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_warehouse"];
        $response["content"][$a]["nama"] = $result["data"][$a]["warehouse_nama"];
        $response["content"][$a]["alamat"] = $result["data"][$a]["warehouse_alamat"];
        $response["content"][$a]["notelp"] = $result["data"][$a]["warehouse_notelp"];
        $response["content"][$a]["desc"] = $result["data"][$a]["warehouse_desc"];
        $response["content"][$a]["status"] = $result["data"][$a]["warehouse_status"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["warehouse_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "nama",
      "alamat",
      "notelp",
      "desc",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }
  public function pengaturan()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_warehouse");
    $this->m_warehouse->set_id_pk_warehouse($this->session->id_warehouse);
    $result = $this->m_warehouse->detail_by_id();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      $response["content"][0]["id"] = $result[0]["id_pk_warehouse"];
      $response["content"][0]["nama"] = $result[0]["warehouse_nama"];
      $response["content"][0]["alamat"] = $result[0]["warehouse_alamat"];
      $response["content"][0]["notelp"] = $result[0]["warehouse_notelp"];
      $response["content"][0]["desc"] = $result[0]["warehouse_desc"];
      $response["content"][0]["status"] = $result[0]["warehouse_status"];
      $response["content"][0]["last_modified"] = $result[0]["warehouse_last_modified"];
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No Data";
    }
    echo json_encode($response);
  }

  public function refresh_id_warehouse()
  {
    #refresh session warehouse
    #gabisa di taro di fungsi updatek karena fungsi update dipake di master warehouse juga yang ga boleh tiba2 ke assign session warehouse

    $response["status"] = "SUCCESS";
    $this->load->model("m_warehouse");
    $this->m_warehouse->set_id_pk_warehouse($this->session->id_warehouse);
    $result = $this->m_warehouse->detail_by_id();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      $this->session->id_warehouse = $result[0]["id_pk_warehouse"];
      $this->session->nama_warehouse = $result[0]["warehouse_nama"];
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Invalid Active ID";
      $this->session->unset_userdata("id_warehouse");
      $this->session->unset_userdata("nama_warehouse");
    }
    echo json_encode($response);
  }
}
