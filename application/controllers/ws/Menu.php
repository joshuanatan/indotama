<?php
defined("BASEPATH") or exit("No direct script");
class Menu extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_menu");
    $columns = $this->m_menu->columns();
    if (count($columns) > 0) {
      for ($a = 0; $a < count($columns); $a++) {
        $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }

  public function menubar()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_warehouse_admin");
    $this->m_warehouse_admin->set_id_fk_user($this->session->id_user);
    $result = $this->m_warehouse_admin->list_gudang_admin();
    if ($result["data"]->num_rows() > 0) {
      $this->session->access_gudang = true;
      if ($result["data"]->num_rows() > 1) {
        $this->session->multiple_warehouse_access = true;
      }
      if ($this->session->id_warehouse == "") {
        $result = $result["data"]->result_array();
        $this->session->id_warehouse = $result[0]["id_pk_warehouse"];
        $this->session->nama_warehouse = $result[0]["warehouse_nama"];
      }
    }

    $this->load->model("m_toko_admin");
    $this->m_toko_admin->set_id_fk_user($this->session->id_user);
    $result = $this->m_toko_admin->list_toko_admin();
    if ($result["data"]->num_rows() > 0) {
      $this->session->access_toko = true;
      if ($result["data"]->num_rows() > 1) {
        $this->session->multiple_toko_access = true;
      }
      if ($this->session->id_toko == "") {
        $result = $result["data"]->result_array();
        $this->session->id_toko = $result[0]["id_pk_toko"];
        $this->session->nama_toko = $result[0]["toko_nama"];
      }
    }

    $this->load->model("m_cabang_admin");
    $this->m_cabang_admin->set_id_fk_user($this->session->id_user);
    $result = $this->m_cabang_admin->list_cabang_admin();
    if ($result["data"]->num_rows() > 0) {
      $this->session->access_cabang = true;
      if ($result["data"]->num_rows() > 1) {
        $this->session->multiple_cabang_access = true;
      }
      /*biar langsung defaultnya kepilih*/
      if ($this->session->id_cabang == "") {
        $result = $result["data"]->result_array();
        $this->session->id_toko = $result[0]["id_pk_toko"];
        $this->session->id_cabang = $result[0]["id_pk_cabang"];
        $this->session->daerah_cabang = $result[0]["cabang_daerah"];
        $this->session->nama_toko_cabang = $result[0]["toko_nama"];
      }
    }
    /*ini untuk tampilin di bawah profile*/
    $this->session->disp_nama_toko_cabang = $this->session->nama_toko . " " . $this->session->daerah_cabang;
    $this->load->model("m_user");
    $this->m_user->set_id_pk_user($this->session->id_user);
    $response["data"] = $this->m_user->menu()->result_array();
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

    $this->load->model("m_menu");
    $result = $this->m_menu->content($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_menu"];
        $response["content"][$a]["controller"] = $result["data"][$a]["menu_name"];
        $response["content"][$a]["display"] = $result["data"][$a]["menu_display"];
        $response["content"][$a]["icon"] = $result["data"][$a]["menu_icon"];
        $response["content"][$a]["kategori"] = $result["data"][$a]["menu_category"];
        $response["content"][$a]["status"] = $result["data"][$a]["menu_status"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["menu_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "display",
      "controller",
      "icon",
      "kategori",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }
  public function list_data()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_menu");
    $result = $this->m_menu->list_data();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_menu"];
        $response["content"][$a]["controller"] = $result[$a]["menu_name"];
        $response["content"][$a]["display"] = $result[$a]["menu_display"];
        $response["content"][$a]["icon"] = $result[$a]["menu_icon"];
        $response["content"][$a]["kategori"] = $result[$a]["menu_category"];
        $response["content"][$a]["status"] = $result[$a]["menu_status"];
        $response["content"][$a]["last_modified"] = $result[$a]["menu_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No Data";
    }
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "SUCCESS";
    $this->form_validation->set_rules("controller", "controller", "required");
    $this->form_validation->set_rules("display", "display", "required");
    $this->form_validation->set_rules("icon", "icon", "required");
    $this->form_validation->set_rules("kategori", "kategori", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_menu");
      $menu_name = $this->input->post("controller");
      $menu_display = $this->input->post("display");
      $menu_icon = $this->input->post("icon");
      $menu_kategori = $this->input->post("kategori");
      $menu_status = "AKTIF";
      if ($this->m_menu->set_insert($menu_name, $menu_display, $menu_icon, $menu_status, $menu_kategori)) {
        if ($this->m_menu->insert()) {
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
    $this->form_validation->set_rules("controller", "controller", "required");
    $this->form_validation->set_rules("display", "display", "required");
    $this->form_validation->set_rules("icon", "icon", "required");
    $this->form_validation->set_rules("kategori", "kategori", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_menu");
      $id_pk_menu = $this->input->post("id");
      $menu_name = $this->input->post("controller");
      $menu_display = $this->input->post("display");
      $menu_icon = $this->input->post("icon");
      $menu_kategori = $this->input->post("kategori");
      if ($this->m_menu->set_update($id_pk_menu, $menu_name, $menu_display, $menu_icon, $menu_kategori)) {
        if ($this->m_menu->update()) {
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
    $id_toko = $this->input->get("id");
    if ($id_toko != "" && is_numeric($id_toko)) {
      $id_pk_toko = $id_toko;
      $this->load->model("m_menu");
      if ($this->m_menu->set_delete($id_pk_toko)) {
        if ($this->m_menu->delete()) {
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
}
