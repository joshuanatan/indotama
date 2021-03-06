<?php
defined("BASEPATH") or exit("No direct script");
class Customer extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_customer");
    $columns = $this->m_customer->columns();
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

    $this->load->model("m_customer");
    $result = $this->m_customer->content($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {

        if ($result["data"][$a]["cust_foto_npwp"]) {
          if (file_exists(FCPATH . "asset/uploads/customer/npwp/" . $result["data"][$a]["cust_foto_npwp"])) {
            $response["content"][$a]["foto_npwp"] = $result["data"][$a]["cust_foto_npwp"];
          } else {
            $response["content"][$a]["foto_npwp"] = "noimage.jpg";
          }
        } else {
          $response["content"][$a]["foto_npwp"] = "noimage.jpg";
        }
        if ($result["data"][$a]["cust_foto_kartu_nama"]) {
          if (file_exists(FCPATH . "asset/uploads/customer/krt_nama/" . $result["data"][$a]["cust_foto_kartu_nama"])) {
            $response["content"][$a]["foto_kartu_nama"] = $result["data"][$a]["cust_foto_kartu_nama"];
          } else {
            $response["content"][$a]["foto_kartu_nama"] = "noimage.jpg";
          }
        } else {
          $response["content"][$a]["foto_kartu_nama"] = "noimage.jpg";
        }
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_cust"];
        $response["content"][$a]["name"] = $result["data"][$a]["cust_name"];
        $response["content"][$a]["suff"] = $result["data"][$a]["cust_suff"];
        $response["content"][$a]["perusahaan"] = $result["data"][$a]["cust_perusahaan"];
        $response["content"][$a]["email"] = $result["data"][$a]["cust_email"];
        $response["content"][$a]["telp"] = $result["data"][$a]["cust_telp"];
        $response["content"][$a]["hp"] = $result["data"][$a]["cust_hp"];
        $response["content"][$a]["alamat"] = $result["data"][$a]["cust_alamat"];
        $response["content"][$a]["keterangan"] = $result["data"][$a]["cust_keterangan"];
        $response["content"][$a]["status"] = $result["data"][$a]["cust_status"];
        $response["content"][$a]["no_npwp"] = $result["data"][$a]["cust_no_npwp"];
        $response["content"][$a]["badan_usaha"] = $result["data"][$a]["cust_badan_usaha"];
        $response["content"][$a]["no_rekening"] = $result["data"][$a]["cust_no_rekening"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["cust_last_modified"];
        $response["content"][$a]["id_toko"] = $result["data"][$a]["id_fk_toko"];
        $response["content"][$a]["nama_toko"] = $result["data"][$a]["toko_nama"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "name",
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
  public function content_cust_toko()
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $id_toko = $this->input->get("id_toko");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;

    $this->load->model("m_customer");
    $result = $this->m_customer->content_cust_toko($page, $order_by, $order_direction, $search_key, $data_per_page, $id_toko);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {

        if ($result["data"][$a]["cust_foto_npwp"]) {
          if (file_exists(FCPATH . "asset/uploads/customer/npwp/" . $result["data"][$a]["cust_foto_npwp"])) {
            $response["content"][$a]["foto_npwp"] = $result["data"][$a]["cust_foto_npwp"];
          } else {
            $response["content"][$a]["foto_npwp"] = "noimage.jpg";
          }
        } else {
          $response["content"][$a]["foto_npwp"] = "noimage.jpg";
        }
        if ($result["data"][$a]["cust_foto_kartu_nama"]) {
          if (file_exists(FCPATH . "asset/uploads/customer/krt_nama/" . $result["data"][$a]["cust_foto_kartu_nama"])) {
            $response["content"][$a]["foto_kartu_nama"] = $result["data"][$a]["cust_foto_kartu_nama"];
          } else {
            $response["content"][$a]["foto_kartu_nama"] = "noimage.jpg";
          }
        } else {
          $response["content"][$a]["foto_kartu_nama"] = "noimage.jpg";
        }
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_cust"];
        $response["content"][$a]["name"] = $result["data"][$a]["cust_name"];
        $response["content"][$a]["suff"] = $result["data"][$a]["cust_suff"];
        $response["content"][$a]["perusahaan"] = $result["data"][$a]["cust_perusahaan"];
        $response["content"][$a]["email"] = $result["data"][$a]["cust_email"];
        $response["content"][$a]["telp"] = $result["data"][$a]["cust_telp"];
        $response["content"][$a]["hp"] = $result["data"][$a]["cust_hp"];
        $response["content"][$a]["alamat"] = $result["data"][$a]["cust_alamat"];
        $response["content"][$a]["keterangan"] = $result["data"][$a]["cust_keterangan"];
        $response["content"][$a]["status"] = $result["data"][$a]["cust_status"];
        $response["content"][$a]["no_npwp"] = $result["data"][$a]["cust_no_npwp"];
        $response["content"][$a]["badan_usaha"] = $result["data"][$a]["cust_badan_usaha"];
        $response["content"][$a]["no_rekening"] = $result["data"][$a]["cust_no_rekening"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["cust_last_modified"];
        $response["content"][$a]["id_toko"] = $result["data"][$a]["id_fk_toko"];
        $response["content"][$a]["nama_toko"] = $result["data"][$a]["toko_nama"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "name",
      "perusahaan",
      "email",
      "telp",
      "hp",
      "alamat",
      "id_toko",
      "keterangan",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }
  public function list_data()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_customer");
    $result = $this->m_customer->list_data();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_cust"];
        $response["content"][$a]["name"] = $result[$a]["cust_name"];
        $response["content"][$a]["suff"] = $result[$a]["cust_suff"];
        $response["content"][$a]["perusahaan"] = $result[$a]["cust_perusahaan"];
        $response["content"][$a]["email"] = $result[$a]["cust_email"];
        $response["content"][$a]["telp"] = $result[$a]["cust_telp"];
        $response["content"][$a]["hp"] = $result[$a]["cust_hp"];
        $response["content"][$a]["alamat"] = $result[$a]["cust_alamat"];
        $response["content"][$a]["keterangan"] = $result[$a]["cust_keterangan"];
        $response["content"][$a]["status"] = $result[$a]["cust_status"];
        $response["content"][$a]["last_modified"] = $result[$a]["cust_last_modified"];
        $response["content"][$a]["no_npwp"] = $result[$a]["cust_no_npwp"];
        $response["content"][$a]["foto_npwp"] = $result[$a]["cust_foto_npwp"];
        $response["content"][$a]["foto_kartu_nama"] = $result[$a]["cust_foto_kartu_nama"];
        $response["content"][$a]["badan_usaha"] = $result[$a]["cust_badan_usaha"];
        $response["content"][$a]["no_rekening"] = $result[$a]["cust_no_rekening"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No Customer List";
    }
    echo json_encode($response);
  }
  public function list_data_cust_toko($id_toko = 0)
  {
    if ($id_toko) {
      $response["status"] = "SUCCESS";
      $this->load->model("m_customer");
      $result = $this->m_customer->list_data_cust_toko($id_toko);
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        for ($a = 0; $a < count($result); $a++) {
          $response["content"][$a]["id"] = $result[$a]["id_pk_cust"];
          $response["content"][$a]["name"] = $result[$a]["cust_name"];
          $response["content"][$a]["suff"] = $result[$a]["cust_suff"];
          $response["content"][$a]["perusahaan"] = $result[$a]["cust_perusahaan"];
          $response["content"][$a]["email"] = $result[$a]["cust_email"];
          $response["content"][$a]["telp"] = $result[$a]["cust_telp"];
          $response["content"][$a]["hp"] = $result[$a]["cust_hp"];
          $response["content"][$a]["alamat"] = $result[$a]["cust_alamat"];
          $response["content"][$a]["keterangan"] = $result[$a]["cust_keterangan"];
          $response["content"][$a]["status"] = $result[$a]["cust_status"];
          $response["content"][$a]["last_modified"] = $result[$a]["cust_last_modified"];
          $response["content"][$a]["no_npwp"] = $result[$a]["cust_no_npwp"];
          $response["content"][$a]["foto_npwp"] = $result[$a]["cust_foto_npwp"];
          $response["content"][$a]["foto_kartu_nama"] = $result[$a]["cust_foto_kartu_nama"];
          $response["content"][$a]["badan_usaha"] = $result[$a]["cust_badan_usaha"];
          $response["content"][$a]["no_rekening"] = $result[$a]["cust_no_rekening"];
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "No Customer List";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No ID Toko is provided";
    }
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "SUCCESS";
    $this->form_validation->set_rules("cust_name", "Nama", "required");
    $this->form_validation->set_rules("cust_suff", "Panggilan", "required");
    $this->form_validation->set_rules("cust_perusahaan", "Perusahaan", "required");
    $this->form_validation->set_rules("cust_email", "Email", "required|valid_email");
    $this->form_validation->set_rules("cust_telp", "Telepon", "required");
    $this->form_validation->set_rules("cust_hp", "No HP", "required");
    $this->form_validation->set_rules("cust_alamat", "Alamat", "required");
    $this->form_validation->set_rules("id_fk_toko", "Toko", "required");
    $this->form_validation->set_rules("cust_keterangan", "Keterangan", "required");
    $this->form_validation->set_rules("cust_badan_usaha", "Badan Usaha", "required");
    $this->form_validation->set_rules("cust_npwp", "NPWP", "required");
    $this->form_validation->set_rules("cust_rek", "Nomor Rekening", "required");

    if ($this->form_validation->run()) {
      $this->load->model("m_customer");


      $config1['upload_path'] = './asset/uploads/customer/npwp/';
      $config1['allowed_types'] = 'jpg|png|jpeg';
      $this->load->library('upload', $config1);
      if (!$this->upload->do_upload('cust_foto_npwp')) {
        $error = array('error' => $this->upload->display_errors());
        $cust_foto_npwp = "noimage.jpg";
      } else {
        $cust_foto_npwp = $this->upload->data('file_name');
      }

      $config2['upload_path']          = './asset/uploads/customer/krt_nama/';
      $config2['allowed_types']        = 'jpg|png|jpeg';
      $this->upload->initialize($config2);
      if (!$this->upload->do_upload('cust_krt_nama')) {
        $error = array('error' => $this->upload->display_errors());
        $cust_foto_kartu_nama = "noimage.jpg";
      } else {
        $cust_foto_kartu_nama = $this->upload->data('file_name');
      }


      $cust_name = $this->input->post("cust_name");
      $cust_badan_usaha = $this->input->post("cust_badan_usaha");
      $cust_no_npwp = $this->input->post("cust_npwp");
      $cust_no_rekening = $this->input->post("cust_rek");
      $cust_suff = $this->input->post("cust_suff");
      $cust_perusahaan = $this->input->post("cust_perusahaan");
      $cust_email = $this->input->post("cust_email");
      $cust_telp = $this->input->post("cust_telp");
      $cust_hp = $this->input->post("cust_hp");
      $cust_alamat = $this->input->post("cust_alamat");
      $id_fk_toko = $this->input->post("id_fk_toko");
      $cust_keterangan = $this->input->post("cust_keterangan");
      $cust_status = "AKTIF";

      if ($this->m_customer->insert($cust_name, $cust_suff, $cust_perusahaan, $cust_email, $cust_telp, $cust_hp, $cust_alamat, $cust_keterangan, $cust_status, $cust_no_npwp, $cust_foto_npwp, $cust_foto_kartu_nama, $cust_badan_usaha, $cust_no_rekening, $id_fk_toko)) {
        $response["msg"] = "Data is recorded to database";
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Insert function error";
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
    $this->form_validation->set_rules("cust_name", "Nama", "required");
    $this->form_validation->set_rules("cust_suff", "Panggilan", "required");
    $this->form_validation->set_rules("cust_perusahaan", "Perusahaan", "required");
    $this->form_validation->set_rules("cust_email", "Email", "required|valid_email");
    $this->form_validation->set_rules("cust_telp", "Telepon", "required");
    $this->form_validation->set_rules("cust_hp", "No HP", "required");
    $this->form_validation->set_rules("cust_alamat", "Alamat", "required");
    $this->form_validation->set_rules("id_fk_toko", "Toko", "required");
    $this->form_validation->set_rules("cust_keterangan", "Keterangan", "required");

    if ($this->form_validation->run()) {

      $config1['upload_path'] = './asset/uploads/customer/npwp/';
      $config1['allowed_types'] = 'jpg|png|jpeg';
      $this->load->library('upload', $config1);
      if (!$this->upload->do_upload('cust_foto_npwp')) {
        $error = array('error' => $this->upload->display_errors());
        $cust_foto_npwp = $this->input->post("cust_foto_npwp_current");
      } else {
        $cust_foto_npwp = $this->upload->data('file_name');
      }

      $config2['upload_path']          = './asset/uploads/customer/krt_nama/';
      $config2['allowed_types']        = 'jpg|png|jpeg';
      $this->upload->initialize($config2);
      if (!$this->upload->do_upload('cust_krt_nama')) {
        $error = array('error' => $this->upload->display_errors());
        $cust_foto_kartu_nama = $this->input->post("cust_krt_nama_current");
      } else {
        $cust_foto_kartu_nama = $this->upload->data('file_name');
      }

      $id_pk_cust = $this->input->post("id_pk_cust");
      $cust_name = $this->input->post("cust_name");
      $cust_badan_usaha = $this->input->post("cust_badan_usaha");
      $cust_no_npwp = $this->input->post("cust_npwp");
      $cust_no_rekening = $this->input->post("cust_rek");
      $cust_suff = $this->input->post("cust_suff");
      $cust_perusahaan = $this->input->post("cust_perusahaan");
      $cust_email = $this->input->post("cust_email");
      $cust_telp = $this->input->post("cust_telp");
      $cust_hp = $this->input->post("cust_hp");
      $cust_alamat = $this->input->post("cust_alamat");
      $id_fk_toko = $this->input->post("id_fk_toko");


      $cust_keterangan = $this->input->post("cust_keterangan");

      $this->load->model("m_customer");
      if ($this->m_customer->set_update($id_pk_cust, $cust_name, $cust_suff, $cust_perusahaan, $cust_email, $cust_telp, $cust_hp, $cust_alamat, $cust_keterangan, $cust_no_npwp, $cust_foto_npwp, $cust_foto_kartu_nama, $cust_badan_usaha, $cust_no_rekening, $id_fk_toko)) {
        if ($this->m_customer->update()) {
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
    $id_pk_customer = $this->input->get("id");
    if ($id_pk_customer != "" && is_numeric($id_pk_customer)) {
      $this->load->model("m_customer");
      if ($this->m_customer->set_delete($id_pk_customer)) {
        if ($this->m_customer->delete()) {
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
  public function columns_detail_penjualan()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_customer");
    $columns = $this->m_customer->columns_detail_penjualan();
    if (count($columns) > 0) {
      for ($a = 0; $a < count($columns); $a++) {
        $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function detail_penjualan($id_pk_customer)
  {
    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;

    $this->load->model("m_customer");
    $result = $this->m_customer->detail_penjualan_table($page, $order_by, $order_direction, $search_key, $data_per_page, $id_pk_customer);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["cust_email"] = $result["data"][$a]["cust_email"];
        $response["content"][$a]["id_pk_penjualan"] = $result["data"][$a]["id_pk_penjualan"];
        $response["content"][$a]["penj_nomor"] = $result["data"][$a]["penj_nomor"];
        $response["content"][$a]["penj_nominal"] = $result["data"][$a]["penj_nominal"];
        $response["content"][$a]["penj_nominal_byr"] = $result["data"][$a]["penj_nominal_byr"];
        $response["content"][$a]["penj_tgl"] = $result["data"][$a]["penj_tgl"];
        $response["content"][$a]["penj_dateline_tgl"] = $result["data"][$a]["penj_dateline_tgl"];
        $response["content"][$a]["penj_status"] = $result["data"][$a]["penj_status"];
        $response["content"][$a]["penj_jenis"] = $result["data"][$a]["penj_jenis"];
        $response["content"][$a]["penj_tipe_pembayaran"] = $result["data"][$a]["penj_tipe_pembayaran"];
        $response["content"][$a]["penj_last_modified"] = $result["data"][$a]["penj_last_modified"];
        $response["content"][$a]["cust_name"] = $result["data"][$a]["cust_name"];
        $response["content"][$a]["cust_perusahaan"] = $result["data"][$a]["cust_perusahaan"];
        $response["content"][$a]["status_pembayaran"] = $result["data"][$a]["status_pembayaran"];
        $response["content"][$a]["list_jenis_pembayaran"] = $result["data"][$a]["list_jenis_pembayaran"];
        $response["content"][$a]["selisih_tanggal"] = $result["data"][$a]["selisih_tanggal"];
        $response["content"][$a]["cust_display"] = $result["data"][$a]["cust_perusahaan"] . " - " . $result["data"][$a]["cust_name"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    echo json_encode($response);
  }
  public function columns_detail_brg_penjualan()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_customer");
    $columns = $this->m_customer->columns_detail_brg_penjualan();
    if (count($columns) > 0) {
      for ($a = 0; $a < count($columns); $a++) {
        $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function detail_brg_penjualan($id_pk_customer)
  {

    $response["status"] = "SUCCESS";
    $response["content"] = array();

    $order_by = $this->input->get("orderBy");
    $order_direction = $this->input->get("orderDirection");
    $page = $this->input->get("page");
    $search_key = $this->input->get("searchKey");
    $data_per_page = 20;

    $this->load->model("m_customer");
    $result = $this->m_customer->detail_brg_penjualan_table($page, $order_by, $order_direction, $search_key, $data_per_page, $id_pk_customer);

    if ($result["data"]->num_rows() > 0) {
      $response["content"] = $result["data"]->result_array();
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    echo json_encode($response);
  }
}
