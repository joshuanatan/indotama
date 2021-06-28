<?php
defined("BASEPATH") or exit("No direct script");
class Supplier extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_supplier");
    $columns = $this->m_supplier->columns();
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

    $this->load->model("m_supplier");
    $result = $this->m_supplier->content($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        if (file_exists(FCPATH . "asset/uploads/supplier/krt_nama/" . $result["data"][$a]["sup_foto_kartu_nama"])) {
          $response["content"][$a]["foto_kartu_nama"] = $result["data"][$a]["sup_foto_kartu_nama"];
        } else {
          $response["content"][$a]["foto_kartu_nama"] = "noimage.jpg";
        }
        if (file_exists(FCPATH . "asset/uploads/supplier/npwp/" . $result["data"][$a]["sup_foto_npwp"])) {
          $response["content"][$a]["foto_npwp"] = $result["data"][$a]["sup_foto_npwp"];
        } else {
          $response["content"][$a]["foto_npwp"] = "noimage.jpg";
        }
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_sup"];
        $response["content"][$a]["nama"] = $result["data"][$a]["sup_nama"];
        $response["content"][$a]["suff"] = $result["data"][$a]["sup_suff"];
        $response["content"][$a]["perusahaan"] = $result["data"][$a]["sup_perusahaan"];
        $response["content"][$a]["email"] = $result["data"][$a]["sup_email"];
        $response["content"][$a]["telp"] = $result["data"][$a]["sup_telp"];
        $response["content"][$a]["hp"] = $result["data"][$a]["sup_hp"];
        $response["content"][$a]["alamat"] = $result["data"][$a]["sup_alamat"];
        $response["content"][$a]["keterangan"] = $result["data"][$a]["sup_keterangan"];
        $response["content"][$a]["status"] = $result["data"][$a]["sup_status"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["sup_last_modified"];
        $response["content"][$a]["no_npwp"] = $result["data"][$a]["sup_no_npwp"];
        $response["content"][$a]["badan_usaha"] = $result["data"][$a]["sup_badan_usaha"];
        $response["content"][$a]["no_rekening"] = $result["data"][$a]["sup_no_rekening"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "nama",
      "perusahaan",
      "email",
      "telp",
      "hp",
      "alamat",
      "keterangan",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }
  public function list_data()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_supplier");
    $result = $this->m_supplier->list_data();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_sup"];
        $response["content"][$a]["nama"] = $result[$a]["sup_nama"];
        $response["content"][$a]["perusahaan"] = $result[$a]["sup_perusahaan"];
        $response["content"][$a]["email"] = $result[$a]["sup_email"];
        $response["content"][$a]["telp"] = $result[$a]["sup_telp"];
        $response["content"][$a]["hp"] = $result[$a]["sup_hp"];
        $response["content"][$a]["alamat"] = $result[$a]["sup_alamat"];
        $response["content"][$a]["keterangan"] = $result[$a]["sup_keterangan"];
        $response["content"][$a]["status"] = $result[$a]["sup_status"];
        $response["content"][$a]["last_modified"] = $result[$a]["sup_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No Barang List";
    }
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "SUCCESS";
    $this->form_validation->set_rules("nama", "nama", "required");
    $this->form_validation->set_rules("suff", "suff", "required");
    $this->form_validation->set_rules("pic", "pic", "required");
    $this->form_validation->set_rules("email", "email", "required");
    $this->form_validation->set_rules("notelp", "notelp", "required");
    $this->form_validation->set_rules("nohp", "nohp", "required");
    $this->form_validation->set_rules("alamat", "alamat", "required");
    $this->form_validation->set_rules("keterangan", "keterangan", "required");
    $this->form_validation->set_rules("sup_badan_usaha", "sup_badan_usaha", "required");
    $this->form_validation->set_rules("sup_npwp", "sup_npwp", "required");
    $this->form_validation->set_rules("sup_rek", "sup_rek", "required");

    if ($this->form_validation->run()) {

      $config1['upload_path'] = './asset/uploads/supplier/npwp/';
      $config1['allowed_types'] = 'jpg|png|jpeg';
      $this->load->library('upload', $config1);
      if (!$this->upload->do_upload('sup_foto_npwp')) {
        $error = array('error' => $this->upload->display_errors());
        $sup_foto_npwp = "noimage.jpg";
      } else {
        $sup_foto_npwp = $this->upload->data('file_name');
      }

      $config2['upload_path']          = './asset/uploads/supplier/krt_nama/';
      $config2['allowed_types']        = 'jpg|png|jpeg';
      $this->upload->initialize($config2);
      if (!$this->upload->do_upload('sup_krt_nama')) {
        $error = array('error' => $this->upload->display_errors());
        $sup_foto_kartu_nama = "noimage.jpg";
      } else {
        $sup_foto_kartu_nama = $this->upload->data('file_name');
      }

      $sup_perusahaan = $this->input->post("nama");
      $sup_suff = $this->input->post("suff");
      $sup_nama = $this->input->post("pic");
      $sup_email = $this->input->post("email");
      $sup_telp = $this->input->post("notelp");
      $sup_hp = $this->input->post("nohp");
      $sup_alamat = $this->input->post("alamat");
      $sup_keterangan = $this->input->post("keterangan");
      $sup_no_npwp = $this->input->post("sup_npwp");
      $sup_badan_usaha = $this->input->post("sup_badan_usaha");
      $sup_no_rekening = $this->input->post("sup_rek");
      $sup_status = "AKTIF";

      $this->load->model("m_supplier");
      if ($this->m_supplier->set_insert($sup_nama, $sup_suff, $sup_perusahaan, $sup_email, $sup_telp, $sup_hp, $sup_alamat, $sup_keterangan, $sup_status, $sup_no_npwp, $sup_foto_npwp, $sup_foto_kartu_nama, $sup_badan_usaha, $sup_no_rekening)) {
        if ($this->m_supplier->insert()) {
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
    $this->form_validation->set_rules("nama", "nama", "required");
    $this->form_validation->set_rules("suff", "suff", "required");
    $this->form_validation->set_rules("pic", "pic", "required");
    $this->form_validation->set_rules("email", "email", "required");
    $this->form_validation->set_rules("notelp", "notelp", "required");
    $this->form_validation->set_rules("nohp", "nohp", "required");
    $this->form_validation->set_rules("alamat", "alamat", "required");
    $this->form_validation->set_rules("keterangan", "keterangan", "required");

    if ($this->form_validation->run()) {

      $config1['upload_path'] = './asset/uploads/supplier/npwp/';
      $config1['allowed_types'] = 'jpg|png|jpeg';
      $this->load->library('upload', $config1);
      if (!$this->upload->do_upload('sup_foto_npwp')) {
        $error = array('error' => $this->upload->display_errors());
        $sup_foto_npwp = $this->input->post("sup_foto_npwp_current");
      } else {
        $sup_foto_npwp = $this->upload->data('file_name');
      }

      $config2['upload_path']          = './asset/uploads/supplier/krt_nama/';
      $config2['allowed_types']        = 'jpg|png|jpeg';
      $this->upload->initialize($config2);
      if (!$this->upload->do_upload('sup_krt_nama')) {
        $error = array('error' => $this->upload->display_errors());
        $sup_foto_kartu_nama = $this->input->post("sup_krt_nama_current");
      } else {
        $sup_foto_kartu_nama = $this->upload->data('file_name');
      }

      $id_pk_sup = $this->input->post("id");
      $sup_perusahaan = $this->input->post("nama");
      $sup_suff = $this->input->post("suff");
      $sup_nama = $this->input->post("pic");
      $sup_email = $this->input->post("email");
      $sup_telp = $this->input->post("notelp");
      $sup_hp = $this->input->post("nohp");
      $sup_alamat = $this->input->post("alamat");
      $sup_keterangan = $this->input->post("keterangan");
      $sup_no_npwp = $this->input->post("sup_npwp");
      $sup_badan_usaha = $this->input->post("sup_badan_usaha");
      $sup_no_rekening = $this->input->post("sup_rek");

      $this->load->model("m_supplier");
      if ($this->m_supplier->set_update($id_pk_sup, $sup_nama, $sup_suff, $sup_perusahaan, $sup_email, $sup_telp, $sup_hp, $sup_alamat, $sup_keterangan, $sup_no_npwp, $sup_foto_npwp, $sup_foto_kartu_nama, $sup_badan_usaha, $sup_no_rekening)) {
        if ($this->m_supplier->update()) {
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
    $id_supplier = $this->input->get("id");
    if ($id_supplier != "" && is_numeric($id_supplier)) {
      $this->load->model("m_supplier");
      if ($this->m_supplier->set_delete($id_supplier)) {
        if ($this->m_supplier->delete()) {
          $response["msg"] = "Data is deleted from database";
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
      $response["msg"] = "Invalid ID Supplier";
    }
    echo json_encode($response);
  }
  public function columns_detail_pembelian()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_supplier");
    $columns = $this->m_supplier->columns_detail_pembelian();
    if (count($columns) > 0) {
      for ($a = 0; $a < count($columns); $a++) {
        $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function detail_pembelian($id_pk_supp)
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;

    $this->load->model("m_supplier");
    $result = $this->m_supplier->detail_pembelian_table($page, $order_by, $order_direction, $search_key, $data_per_page, $id_pk_supp);

    if ($result["data"]->num_rows() > 0) {
      $response["content"] = $result["data"]->result_array();
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    echo json_encode($response);
  }
  public function columns_detail_brg_pembelian()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_supplier");
    $columns = $this->m_supplier->columns_detail_brg_pembelian();
    if (count($columns) > 0) {
      for ($a = 0; $a < count($columns); $a++) {
        $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function detail_brg_pembelian($id_pk_supp)
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;

    $this->load->model("m_supplier");
    $result = $this->m_supplier->detail_brg_pembelian_table($page, $order_by, $order_direction, $search_key, $data_per_page, $id_pk_supp);

    if ($result["data"]->num_rows() > 0) {
      $response["content"] = $result["data"]->result_array();
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    echo json_encode($response);
  }
}
