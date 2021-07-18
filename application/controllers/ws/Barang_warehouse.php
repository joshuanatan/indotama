<?php
defined("BASEPATH") or exit("no direct script");
class Barang_warehouse extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->register_unregistered_anggota_kombinasi_warehouse($origin = "construct");
    $this->stock_adjustment();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_brg_warehouse");
    $columns = $this->m_brg_warehouse->columns();
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
    $id_warehouse = $this->input->get("id_warehouse");
    $this->load->model("m_brg_warehouse");
    $this->m_brg_warehouse->set_id_fk_warehouse($id_warehouse);
    $result = $this->m_brg_warehouse->content($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg_warehouse"];
        $response["content"][$a]["qty"] = number_format($result["data"][$a]["brg_warehouse_qty"], 0, ",", ".");
        $response["content"][$a]["notes"] = $result["data"][$a]["brg_warehouse_notes"];
        $response["content"][$a]["status"] = $result["data"][$a]["brg_warehouse_status"];
        $response["content"][$a]["id_brg"] = $result["data"][$a]["id_fk_brg"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_warehouse_last_modified"];
        $response["content"][$a]["nama_brg"] = $result["data"][$a]["brg_nama"];
        $response["content"][$a]["kode_brg"] = $result["data"][$a]["brg_kode"];
        $response["content"][$a]["ket_brg"] = $result["data"][$a]["brg_ket"];
        $response["content"][$a]["minimal_brg"] = $result["data"][$a]["brg_minimal"];
        $response["content"][$a]["satuan_brg"] = $result["data"][$a]["brg_satuan"];
        $response["content"][$a]["image_brg"] = $result["data"][$a]["brg_image"];
        $response["content"][$a]["tipe"] = $result["data"][$a]["brg_tipe"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "kode_brg",
      "nama_brg",
      "ket_brg",
      "qty",
      "notes",
      "tipe",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "SUCCESS";
    $check = $this->input->post("check");
    if ($check != "") {
      $id_fk_warehouse = $this->input->post("id_warehouse");
      $counter = 0;
      foreach ($check as $a) {
        $this->form_validation->set_rules("brg" . $a, "brg", "required");
        $this->form_validation->set_rules("brg_qty" . $a, "brg_qty", "required");
        $this->form_validation->set_rules("brg_notes" . $a, "brg_notes", "required");
        if ($this->form_validation->run()) {
          $brg_warehouse_qty = $this->input->post("brg_qty" . $a);
          $brg_warehouse_notes = $this->input->post("brg_notes" . $a);
          $brg_warehouse_status = "AKTIF";

          $barang = $this->input->post("brg" . $a);
          $this->load->model("m_barang");
          $this->m_barang->set_brg_nama($barang);
          $result = $this->m_barang->detail_by_name();

          if ($result->num_rows() > 0) {
            $result = $result->result_array();
            $id_fk_brg = $result[0]["id_pk_brg"];
            $this->load->model("m_brg_warehouse");
            if ($this->m_brg_warehouse->set_insert($brg_warehouse_qty, $brg_warehouse_notes, $brg_warehouse_status, $id_fk_brg, $id_fk_warehouse)) {
              if ($this->m_brg_warehouse->insert()) {

                $this->register_unregistered_anggota_kombinasi_warehouse();

                #penting karena bisa jadi dia masuk sebagai kombinasi yang anggotanya sudah terdaftar sebelumnya sehingga harus diupdate menurut kedatangan kombinasi ini.
                executeQuery("call update_stok_kombinasi_anggota_warehouse(" . $id_fk_brg . "," . $brg_warehouse_qty . ",0," . $id_fk_warehouse . ")");

                $response["itmsts"][$counter] = "SUCCESS";
                $response["itmmsg"][$counter] = "Data is recorded to database";

                $msg = "Nama barang: {$barang} Jumlah barang: {$brg_warehouse_qty}, Catatan :{$brg_warehouse_notes}";
                $title = "Penambahan data barang " . $this->session->nama_warehouse. " ".$this->session->nama_cabang." cabang " . $this->session->nama_toko;
                $id_user = $this->session->id_user;
                executeQuery("call insert_log_all('{$id_user}','{$title}','{$msg}','-')");
              } else {
                $response["status"] = "ERROR";
                $response["itmsts"][$counter] = "ERROR";
                $response["itmmsg"][$counter] = "Insert function error";
              }
            } else {
              $response["status"] = "ERROR";
              $response["itmsts"][$counter] = "ERROR";
              $response["itmmsg"][$counter] = "Setter function error";
            }
          }
        } else {
          $response["status"] = "ERROR";
          $response["itmsts"][$counter] = "ERROR";
          $response["itmmsg"][$counter] = validation_errors();
        }
        $counter++;
      }
    } else {
      $response["itmstsall"] = "ERROR";
      $response["itmmsgall"] = "No Checks on Item";
    }
    echo json_encode($response);
  }
  public function update()
  {
    $response["status"] = "SUCCESS";
    $this->form_validation->set_rules("id", "id", "required");
    $this->form_validation->set_rules("brg", "brg", "required");
    $this->form_validation->set_rules("stok", "stok", "required");
    $this->form_validation->set_rules("notes", "notes", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_brg_warehouse");
      $id_pk_brg_warehouse = $this->input->post("id");
      $brg_warehouse_qty = $this->input->post("stok");
      $brg_warehouse_notes = $this->input->post("notes");

      $barang = $this->input->post("brg");
      $this->load->model("m_barang");
      $this->m_barang->set_brg_nama($barang);
      $result = $this->m_barang->detail_by_name();

      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        $id_fk_brg = $result[0]["id_pk_brg"];
        $this->load->model("m_brg_warehouse");
        if ($this->m_brg_warehouse->set_update($id_pk_brg_warehouse, $brg_warehouse_qty, $brg_warehouse_notes, $id_fk_brg)) {
          if ($this->m_brg_warehouse->update()) {
            $data["msg"] = "Data is updated to database";
          } else {
            $response["status"] = "ERROR";
            $response["msg"] = "Update function error";
          }
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Setter function error";
        }
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
    $id_brg_warehouse = $this->input->get("id");
    if ($id_brg_warehouse != "" && is_numeric($id_brg_warehouse)) {
      $this->load->model("m_brg_warehouse");
      if ($this->m_brg_warehouse->set_delete($id_brg_warehouse)) {
        if ($this->m_brg_warehouse->delete()) {
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
  private function register_unregistered_anggota_kombinasi_warehouse($origin = "insert")
  {

    #cari anggota kombinasi yang (belom ada) dan lakukan insert. literally do that, cari semua yang merupakan anggota kombinasi tapi belom ada di daftar barang warehouse
    #jadi klo udah ada itu ga kepanggil lagi.
    #tujuan fungsi ini untuk memastiakn setiap barang anggota kombinasi telah terdaftar diwarehouse, bukan untuk stok adjustment


    #usecases:
    # 1. insert barang kombinasi 1 (barang1,3,5). klo  1,3,5 ga ada, select, insert
    # 2. kalau udah ada barang kombinasi 1 terdaftar, trus daftarin kombinasi 2(2,3,5), maka hanya 2 yang keambil, 3 dan 5 belom [butuh stock adjustment]
    # 3. kalau ada barang kombinasi 1(1,3,5) dan kombinasi 2(2,3,5), kemudian yang 5 dihapus maka hasilnya akan mengeluarkan 5,5 (untuk kombinasi 1 dan 2). fungsi akan melakukan insert pertama (insert) dan insert kedua (update) karena sudah ada dari hasil insert yang pertama
    $this->load->model("m_brg_warehouse");
    $this->m_brg_warehouse->set_id_fk_warehouse($this->session->id_warehouse);
    $result_kombinasi = $this->m_brg_warehouse->list_not_exists_brg_kombinasi();
    if ($result_kombinasi->num_rows() > 0) {
      $result_kombinasi = $result_kombinasi->result_array();
      //print_r($result_kombinasi);
      for ($b = 0; $b < count($result_kombinasi); $b++) {
        $this->load->model("m_brg_warehouse");
        if ($this->m_brg_warehouse->set_insert($result_kombinasi[$b]["add_qty"], "Auto insert from checking construct", "aktif", $result_kombinasi[$b]["id_barang_kombinasi"], $this->session->id_warehouse)) {
          if ($this->m_brg_warehouse->insert_adjustment()) {
          }
        }
      }
    }
  }
  private function stock_adjustment()
  {
    #update master kombinasi based on stok
    executeQuery("call update_stok_kombinasi_master_warehouse();");
  }
}
