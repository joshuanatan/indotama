<?php
defined("BASEPATH") or exit("No direct script");
class Penawaran extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_penawaran");
    $columns = $this->m_penawaran->columns();
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
    $this->load->model("m_penawaran");
    $this->m_penawaran->set_id_fk_cabang($this->session->id_cabang);
    $result = $this->m_penawaran->content($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $response["content"] = $result["data"]->result_array();
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "refrensi",
      "tgl",
      "subject",
      "content",
      "notes",
      "file_html",
      "status",
      "last_modified",
    );
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "success";
    $this->form_validation->set_rules("penawar", "penawar", "required");
    $this->form_validation->set_rules("tgl", "tgl", "required");
    $this->form_validation->set_rules("subjek", "subjek", "required");
    $this->form_validation->set_rules("content", "content", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_penawaran");
      $penawaran_refrensi = $this->input->post("penawar");
      $this->input->post("customer");
      $this->load->model("m_customer");
      $this->m_customer->set_cust_perusahaan($penawaran_refrensi);
      $result = $this->m_customer->detail_by_perusahaan();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        $penawaran_refrensi = $result[0]["id_pk_cust"];
      } else {
        $penawaran_refrensi = $this->m_customer->short_insert();
      }

      $penawaran_subject = $this->input->post("subjek");
      $penawaran_content = $this->input->post("content");
      $penawaran_notes = $this->input->post("notes");
      $penawaran_tgl = $this->input->post("tgl");
      $penawaran_status = "AKTIF";
      $id_fk_cabang = $this->session->id_cabang;

      $id_penawaran = $this->m_penawaran->insert($penawaran_subject, $penawaran_content, $penawaran_notes, $penawaran_refrensi, $penawaran_tgl, $penawaran_status, $id_fk_cabang);
      if ($id_penawaran) {
        $response["msg"] = "Data is recorded to database";
        $check = $this->input->post("check");
        if($check != ""){
          foreach($check as $a){
            $nama_barang = $this->input->post("nama_barang".$a);
            $this->load->model("m_barang");
            $this->m_barang->set_brg_nama($nama_barang);
            $result = $this->m_barang->detail_by_name();
            if ($result->num_rows() > 0) {
              $result = $result->result_array();
              $id_fk_barang = $result[0]["id_pk_brg"];
              
              $jumlah_barang = $this->input->post("jumlah_barang".$a);
              $brg_qty = explode(" ", $jumlah_barang);
              if (count($brg_qty) > 1) {
                $brg_penawaran_qty = $brg_qty[0];
                $brg_penawaran_satuan = $brg_qty[1];
              } else {
                $brg_penawaran_qty = $brg_qty[0];
                $brg_penawaran_satuan = "Pcs";
              }
            }
            $brg_penawaran_price = $this->input->post("harga_barang".$a);
            $brg_penawaran_notes = $this->input->post("notes_barang".$a);
            $brg_penawaran_status = "aktif";

            $this->load->model("m_brg_penawaran");
            $this->m_brg_penawaran->insert($id_fk_barang,$brg_penawaran_qty,$brg_penawaran_satuan,$brg_penawaran_price,$brg_penawaran_notes,$brg_penawaran_status,$id_penawaran);
          }
        }
      } 
      else {
        $response["status"] = false;
        $response["msg"] = "Insert function error";
      }
    } 
    else {
      $response["status"] = false;
      $response["msg"] = validation_errors();
    }
    echo json_encode($response);
  }
  public function update()
  {
    $response["status"] = "success";
    $this->form_validation->set_rules("id_pk_penawaran", "ID penawaran", "required");
    $this->form_validation->set_rules("penawar", "penawar", "required");
    $this->form_validation->set_rules("tgl", "tgl", "required");
    $this->form_validation->set_rules("subjek", "subjek", "required");
    $this->form_validation->set_rules("content", "content", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_penawaran");
      $penawaran_refrensi = $this->input->post("penawar");
      $this->input->post("customer");
      $this->load->model("m_customer");
      $this->m_customer->set_cust_perusahaan($penawaran_refrensi);
      $result = $this->m_customer->detail_by_perusahaan();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        $penawaran_refrensi = $result[0]["id_pk_cust"];
      } else {
        $penawaran_refrensi = $this->m_customer->short_insert();
      }

      $id_pk_penawaran = $this->input->post("id_pk_penawaran");
      $penawaran_subject = $this->input->post("subjek");
      $penawaran_content = $this->input->post("content");
      $penawaran_notes = $this->input->post("notes");
      $penawaran_tgl = $this->input->post("tgl");

      $this->m_penawaran->update($id_pk_penawaran, $penawaran_subject, $penawaran_content, $penawaran_notes, $penawaran_refrensi, $penawaran_tgl);
      $response["msg"] = "Data is updated to database";


      $check = $this->input->post("edit_check");
      if($check != ""){
        foreach($check as $a){
          $id_pk_brg_penawaran = $this->input->post("id_pk_brg_penawaran".$a);
          $nama_barang = $this->input->post("nama_barang".$a);
          $this->load->model("m_barang");
          $this->m_barang->set_brg_nama($nama_barang);
          $result = $this->m_barang->detail_by_name();
          if ($result->num_rows() > 0) {
            $result = $result->result_array();
            $id_fk_barang = $result[0]["id_pk_brg"];
            
            $jumlah_barang = $this->input->post("jumlah_barang".$a);
            $brg_qty = explode(" ", $jumlah_barang);
            if (count($brg_qty) > 1) {
              $brg_penawaran_qty = $brg_qty[0];
              $brg_penawaran_satuan = $brg_qty[1];
            } else {
              $brg_penawaran_qty = $brg_qty[0];
              $brg_penawaran_satuan = "Pcs";
            }
          }
          $brg_penawaran_price = $this->input->post("harga_barang".$a);
          $brg_penawaran_notes = $this->input->post("notes_barang".$a);
          $brg_penawaran_status = "aktif";

          $this->load->model("m_brg_penawaran");
          $this->m_brg_penawaran->update($id_pk_brg_penawaran, $id_fk_barang,$brg_penawaran_qty,$brg_penawaran_satuan,$brg_penawaran_price,$brg_penawaran_notes);
        }
      }

      $check = $this->input->post("check");
      if($check != ""){
        foreach($check as $a){
          $nama_barang = $this->input->post("nama_barang".$a);
          $this->load->model("m_barang");
          $this->m_barang->set_brg_nama($nama_barang);
          $result = $this->m_barang->detail_by_name();
          if ($result->num_rows() > 0) {
            $result = $result->result_array();
            $id_fk_barang = $result[0]["id_pk_brg"];
            
            $jumlah_barang = $this->input->post("jumlah_barang".$a);
            $brg_qty = explode(" ", $jumlah_barang);
            if (count($brg_qty) > 1) {
              $brg_penawaran_qty = $brg_qty[0];
              $brg_penawaran_satuan = $brg_qty[1];
            } else {
              $brg_penawaran_qty = $brg_qty[0];
              $brg_penawaran_satuan = "Pcs";
            }
          }
          $brg_penawaran_price = $this->input->post("harga_barang".$a);
          $brg_penawaran_notes = $this->input->post("notes_barang".$a);
          $brg_penawaran_status = "aktif";

          $this->load->model("m_brg_penawaran");
          $this->m_brg_penawaran->insert($id_fk_barang,$brg_penawaran_qty,$brg_penawaran_satuan,$brg_penawaran_price,$brg_penawaran_notes,$brg_penawaran_status,$id_pk_penawaran);
        }
      }
    } 
    else {
      $response["status"] = false;
      $response["msg"] = validation_errors();
    }
    echo json_encode($response);
  }
  public function delete()
  {
    $response["status"] = "SUCCESS";
    $id_pk_penawaran = $this->input->get("id");
    if ($id_pk_penawaran != "" && is_numeric($id_pk_penawaran)) {
      $this->load->model("m_penawaran");
      if ($this->m_penawaran->set_delete($id_pk_penawaran)) {
        if ($this->m_penawaran->delete()) {
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
  public function brg_penawaran($id_pk_penawaran){
    $this->load->model("m_brg_penawaran");
    $result = $this->m_brg_penawaran->get_brg_penawaran($id_pk_penawaran);
    if($result->num_rows() > 0){
      $response["status"] = "success";
      $response["data"] = $result->result_array();
    }
    else{
      $response["status"] = false;
    }
    echo json_encode($response);
  }
  public function delete_brg_penawaran($id_pk_brg_penawaran){
    $this->load->model("m_brg_penawaran");
    $this->m_brg_penawaran->delete($id_pk_brg_penawaran);
    $response["status"] = true;
    echo json_encode($response);
  }
}
