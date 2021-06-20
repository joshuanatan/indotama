<?php
defined("BASEPATH") or exit("no direct script");
class Pembelian extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_pembelian");
    $columns = $this->m_pembelian->columns();
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
    $id_cabang = $this->input->get("id_cabang");
    $this->load->model("m_pembelian");
    $this->m_pembelian->set_id_fk_cabang($id_cabang);
    $result = $this->m_pembelian->content($page, $order_by, $order_direction, $search_key, $data_per_page);

    if ($result["data"]->num_rows() > 0) {
      $result["data"] = $result["data"]->result_array();
      for ($a = 0; $a < count($result["data"]); $a++) {
        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_pembelian"];
        $response["content"][$a]["nomor"] = $result["data"][$a]["pem_pk_nomor"];
        $response["content"][$a]["tgl"] = $result["data"][$a]["pem_tgl"];
        $response["content"][$a]["status"] = $result["data"][$a]["pem_status"];
        $response["content"][$a]["supplier"] = $result["data"][$a]["sup_perusahaan"];
        $response["content"][$a]["last_modified"] = $result["data"][$a]["pem_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    $response["page"] = $this->pagination->generate_pagination_rules($page, $result["total_data"], $data_per_page);
    $response["key"] = array(
      "nomor",
      "tgl",
      "supplier",
      "status",
      "last_modified"
    );
    echo json_encode($response);
  }
  public function brg_pembelian()
  {
    $response["status"] = "SUCCESS";
    $id_pembelian = $this->input->get("id");
    if ($id_pembelian != "" && is_numeric($id_pembelian)) {

      $this->load->model("m_brg_pembelian");
      $this->m_brg_pembelian->set_id_fk_pembelian($id_pembelian);
      $result = $this->m_brg_pembelian->list_data();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        for ($a = 0; $a < count($result); $a++) {
          $response["content"][$a]["id"] = $result[$a]["id_pk_brg_pembelian"];
          $response["content"][$a]["qty"] = number_format($result[$a]["brg_pem_qty"], 0, ",", ".");
          $response["content"][$a]["satuan"] = $result[$a]["brg_pem_satuan"];
          $response["content"][$a]["harga"] = number_format($result[$a]["brg_pem_harga"], 0, ",", ".");
          $response["content"][$a]["note"] = $result[$a]["brg_pem_note"];
          $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
          $response["content"][$a]["last_modified"] = $result[$a]["brg_pem_last_modified"];
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "No Data";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Invalid ID";
    }
    echo json_encode($response);
  }
  public function tmbhn_pembelian()
  {
    $response["status"] = "SUCCESS";
    $id_pembelian = $this->input->get("id");
    if ($id_pembelian != "" && is_numeric($id_pembelian)) {

      $this->load->model("m_tambahan_pembelian");
      $this->m_tambahan_pembelian->set_id_fk_pembelian($id_pembelian);
      $result = $this->m_tambahan_pembelian->list_data();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        for ($a = 0; $a < count($result); $a++) {
          $response["content"][$a]["id"] = $result[$a]["id_pk_tmbhn"];
          $response["content"][$a]["tmbhn"] = $result[$a]["tmbhn"];
          $response["content"][$a]["jumlah"] = number_format($result[$a]["tmbhn_jumlah"], 2, ",", ".");
          $response["content"][$a]["satuan"] = $result[$a]["tmbhn_satuan"];
          $response["content"][$a]["harga"] = number_format($result[$a]["tmbhn_harga"], 0, ",", ".");
          $response["content"][$a]["notes"] = $result[$a]["tmbhn_notes"];
          $response["content"][$a]["status"] = $result[$a]["tmbhn_status"];
          $response["content"][$a]["last_modified"] = $result[$a]["tmbhn_last_modified"];
        }
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "No Data";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Invalid ID";
    }
    echo json_encode($response);
  }
  public function remove_brg_pembelian()
  {
    $response["status"] = "SUCCESS";
    $id_brg_pembelian = $this->input->get("id");
    $id_pembelian = $this->input->get("id_pembelian");
    if ($id_brg_pembelian != "" && is_numeric($id_brg_pembelian)) {
      if (!$this->is_allow_to_update($id_pembelian)) {
        $response["status"] = "ERROR";
        $response["msg"] = "Data tidak bisa diupdate";
        echo json_encode($response);
        return 0;
      }

      $this->load->model("m_brg_pembelian");
      $this->m_brg_pembelian->set_delete($id_brg_pembelian, $id_pembelian);
      if ($this->m_brg_pembelian->delete()) {
        $response["msg"] = "Data is deleted from database";
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Delete function error";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "ID Invalid";
    }
    echo json_encode($response);
  }
  public function remove_tmbhn_pembelian()
  {
    $response["status"] = "SUCCESS";
    $id_tmbhn_pembelian = $this->input->get("id");
    $id_pembelian = $this->input->get("id_pembelian");
    if ($id_tmbhn_pembelian != "" && is_numeric($id_tmbhn_pembelian)) {
      if (!$this->is_allow_to_update($id_pembelian)) {
        $response["status"] = "ERROR";
        $response["msg"] = "Data tidak bisa dihapus";
        echo json_encode($response);
        return 0;
      }
      $this->load->model("m_tambahan_pembelian");
      $this->m_tambahan_pembelian->set_delete($id_tmbhn_pembelian, $id_pembelian);
      if ($this->m_tambahan_pembelian->delete()) {
        $response["msg"] = "Data is deleted from database";
      } else {
        $response["status"] = "ERROR";
        $response["msg"] = "Delete function error";
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "ID Invalid";
    }
    echo json_encode($response);
  }
  public function register()
  {
    $response["status"] = "SUCCESS";
    //$this->form_validation->set_rules("nomor","nomor","required");
    $this->form_validation->set_rules("tgl", "tgl", "required");
    $this->form_validation->set_rules("supplier", "supplier", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_pembelian");

      $pem_tgl = $this->input->post("tgl");
      $pem_status = "AKTIF";
      $sup_perusahaan = $this->input->post("supplier");
      $this->load->model("m_supplier");
      $this->m_supplier->set_sup_perusahaan($sup_perusahaan);
      $result = $this->m_supplier->detail_by_perusahaan();

      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        $id_fk_supp = $result[0]["id_pk_sup"];
      } else {
        $this->load->model("m_supplier");
        $this->m_supplier->set_sup_perusahaan($sup_perusahaan);
        $id_fk_supp = $this->m_supplier->short_insert();
      }
      $id_fk_cabang = $this->input->post("id_cabang");
      $pem_pk_nomor = $this->m_pembelian->get_pem_nomor($id_fk_cabang, "pembelian", $pem_tgl);

      if ($this->m_pembelian->set_insert($pem_pk_nomor, $pem_tgl, $pem_status, $id_fk_supp, $id_fk_cabang)) {
        $id_pembelian = $this->m_pembelian->insert();

        if ($id_pembelian) {
          $response["msg"] = "Data is recorded to database";

          $check = $this->input->post("check");
          if ($check != "") {
            $counter = 0;
            foreach ($check as $a) {
              $this->form_validation->set_rules("brg" . $a, "brg", "required");
              $this->form_validation->set_rules("brg_qty" . $a, "brg_qty", "required");
              $this->form_validation->set_rules("brg_price" . $a, "brg_price", "required");
              $this->form_validation->set_rules("brg_notes" . $a, "brg_notes", "required");
              if ($this->form_validation->run()) {
                $brg_qty = $this->input->post("brg_qty" . $a);
                $brg_qty = explode(" ", $brg_qty);
                if (count($brg_qty) > 1) {
                  $brg_pem_qty = $brg_qty[0];
                  $brg_pem_satuan = $brg_qty[1];
                } else {
                  $brg_pem_qty = $brg_qty[0];
                  $brg_pem_satuan = "Pcs";
                }
                $brg_pem_harga = $this->input->post("brg_price" . $a);
                $brg_pem_note = $this->input->post("brg_notes" . $a);
                $brg_pem_status = "AKTIF";
                $id_fk_pembelian = $id_pembelian;
                $barang = $this->input->post("brg" . $a);
                $this->load->model("m_barang");
                $this->m_barang->set_brg_nama($barang);
                $result = $this->m_barang->detail_by_name();
                if ($result->num_rows() > 0) {
                  $result = $result->result_array();
                  $id_fk_barang = $result[0]["id_pk_brg"];
                } else {
                  $this->load->model("m_barang");
                  $this->m_barang->set_brg_nama($barang);
                  $id_fk_barang = $this->m_barang->short_insert();
                }
                $this->load->model("m_brg_pembelian");
                if ($this->m_brg_pembelian->set_insert($brg_pem_qty, $brg_pem_satuan, $brg_pem_harga, $brg_pem_note, $brg_pem_status, $id_fk_pembelian, $id_fk_barang)) {
                  if ($this->m_brg_pembelian->insert()) {
                    $this->load->model("m_brg_cabang");
                    $this->m_brg_cabang->set_id_fk_brg($id_fk_barang);
                    $this->m_brg_cabang->set_id_fk_cabang($id_fk_cabang);
                    $this->m_brg_cabang->set_brg_cabang_last_price($brg_pem_harga);
                    $this->m_brg_cabang->update_last_price();

                    $response["itmsts"][$counter] = "SUCCESS";
                    $response["itmmsg"][$counter] = "Data is recorded to database";
                  } else {
                    $response["itmsts"][$counter] = "ERROR";
                    $response["itmmsg"][$counter] = "Insert function error";
                  }
                } else {
                  $response["itmsts"][$counter] = "ERROR";
                  $response["itmmsg"][$counter] = "Setter function error";
                }
              } else {
                $response["itmsts"][$counter] = "ERROR";
                $response["itmmsg"][$counter] = validation_errors();
              }
              $counter++;
            }
          } else {
            $response["itmstsall"] = "ERROR";
            $response["itmmsgall"] = "No Checks on Item";
          }

          $tambahan = $this->input->post("tambahan");
          if ($tambahan != "") {
            $counter = 0;
            foreach ($tambahan as $a) {
              $this->load->library("form_validation");
              $this->form_validation->set_rules("tmbhn" . $a, "tmbhn", "required");
              $this->form_validation->set_rules("tmbhn_jumlah" . $a, "tmbhn_jumlah", "required");
              $this->form_validation->set_rules("tmbhn_harga" . $a, "tmbhn_harga", "required");
              $this->form_validation->set_rules("tmbhn_notes" . $a, "tmbhn_notes", "required");
              if ($this->form_validation->run()) {
                $tmbhn = $this->input->post("tmbhn" . $a);
                $qty = $this->input->post("tmbhn_jumlah" . $a);
                $qty = explode(" ", $qty);
                if (count($qty) > 1) {
                  $tmbhn_jumlah = $qty[0];
                  $tmbhn_satuan = $qty[1];
                } else {
                  $tmbhn_jumlah = $qty[0];
                  $tmbhn_satuan = "Pcs";
                }
                $tmbhn_harga = $this->input->post("tmbhn_harga" . $a);
                $tmbhn_notes = $this->input->post("tmbhn_notes" . $a);
                $tmbhn_status = "AKTIF";
                $id_fk_pembelian = $id_pembelian;

                $this->load->model("m_tambahan_pembelian");
                if ($this->m_tambahan_pembelian->set_insert($tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes, $tmbhn_status, $id_fk_pembelian)) {
                  if ($this->m_tambahan_pembelian->insert()) {
                    $response["tmbhnsts"][$counter] = "SUCCESS";
                    $response["tmbhnmsg"][$counter] = "Data is recorded to database";
                  } else {
                    $response["tmbhnsts"][$counter] = "ERROR";
                    $response["tmbhnmsg"][$counter] = "Insert function error";
                  }
                } else {
                  $response["tmbhnsts"][$counter] = "ERROR";
                  $response["tmbhnmsg"][$counter] = "Setter function error";
                }
              } else {
                $response["tmbhnsts"][$counter] = "ERROR";
                $response["tmbhnmsg"][$counter] = validation_errors();
              }
            }
          } else {
            $response["tmbhnsts"] = "ERROR";
            $response["tmbhnmsg"] = "No Checks on Tambahan";
          }
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
    $this->form_validation->set_rules("nomor", "nomor", "required");
    $this->form_validation->set_rules("tgl", "tgl", "required");
    $this->form_validation->set_rules("supplier", "supplier", "required");
    if ($this->form_validation->run()) {
      $this->load->model("m_pembelian");
      $id_pk_pembelian = $this->input->post("id");
      if (!$this->is_allow_to_update($id_pk_pembelian)) {
        $response["status"] = "ERROR";
        $response["msg"] = "Data tidak bisa diupdate";
        echo json_encode($response);
        return 0;
      }

      $pem_pk_nomor = $this->input->post("nomor");
      $pem_tgl = $this->input->post("tgl");

      $sup_perusahaan = $this->input->post("supplier");
      $this->load->model("m_supplier");
      $this->m_supplier->set_sup_perusahaan($sup_perusahaan);
      $result = $this->m_supplier->detail_by_perusahaan();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        $id_fk_supp = $result[0]["id_pk_sup"];
      } else {
        $this->load->model("m_supplier");
        $this->m_supplier->set_sup_perusahaan($sup_perusahaan);
        $id_fk_supp = $this->m_supplier->short_insert();
      }

      if ($this->m_pembelian->set_update($id_pk_pembelian, $pem_pk_nomor, $pem_tgl, $id_fk_supp)) {
        if ($this->m_pembelian->update()) {
          $data["msg"] = "Data is updated to database";
          $brg_edit = $this->input->post("brg_pem_edit");
          if ($brg_edit != "") {
            $counter = 0;
            foreach ($brg_edit as $a) {
              $this->form_validation->reset_validation();
              $this->form_validation->set_rules("id_brg_pem_edit" . $a, "id", "required");
              $this->form_validation->set_rules("brg_edit" . $a, "brg", "required");
              $this->form_validation->set_rules("brg_qty_edit" . $a, "brg_qty", "required");
              $this->form_validation->set_rules("brg_price_edit" . $a, "brg_price", "required");
              $this->form_validation->set_rules("brg_notes_edit" . $a, "brg_notes", "required");
              if ($this->form_validation->run()) {
                $id_pk_brg_pembelian = $this->input->post("id_brg_pem_edit" . $a);
                $brg_qty = $this->input->post("brg_qty_edit" . $a);
                $brg_qty = explode(" ", $brg_qty);
                if (count($brg_qty) > 1) {
                  $brg_pem_qty = $brg_qty[0];
                  $brg_pem_satuan = $brg_qty[1];
                } else {
                  $brg_pem_qty = $brg_qty[0];
                  $brg_pem_satuan = "Pcs";
                }
                $brg_pem_harga = $this->input->post("brg_price_edit" . $a);
                $brg_pem_note = $this->input->post("brg_notes_edit" . $a);
                $barang = $this->input->post("brg_edit" . $a);

                $this->load->model("m_barang");
                $this->m_barang->set_brg_nama($barang);
                $result = $this->m_barang->detail_by_name();
                if ($result->num_rows() > 0) {
                  $result = $result->result_array();
                  $id_fk_barang = $result[0]["id_pk_brg"];
                } else {
                  $this->load->model("m_barang");
                  $this->m_barang->set_brg_nama($barang);
                  $id_fk_barang = $this->m_barang->short_insert();
                }
                $this->load->model("m_brg_pembelian");
                if ($this->m_brg_pembelian->set_update($id_pk_brg_pembelian, $brg_pem_qty, $brg_pem_satuan, $brg_pem_harga, $brg_pem_note, $id_fk_barang)) {
                  if ($this->m_brg_pembelian->update()) {
                    $response["itmstsupdate"][$counter] = "SUCCESS";
                    $response["itmmsgupdate"][$counter] = "Data is updated to database";
                  } else {
                    $response["itmstsupdate"][$counter] = "ERROR";
                    $response["itmmsgupdate"][$counter] = "update function error";
                  }
                } else {
                  $response["itmstsupdate"][$counter] = "ERROR";
                  $response["itmmsgupdate"][$counter] = "Setter function error";
                }
              } else {
                $response["itmstsupdate"][$counter] = "ERROR";
                $response["itmmsgupdate"][$counter] = validation_errors();
              }
              $counter++;
            }
          }
          $check = $this->input->post("check");
          if ($check != "") {
            $counter = 0;
            foreach ($check as $a) {
              $this->form_validation->reset_validation();
              $this->form_validation->set_rules("brg" . $a, "brg", "required");
              $this->form_validation->set_rules("brg_qty" . $a, "brg_qty", "required");
              $this->form_validation->set_rules("brg_price" . $a, "brg_price", "required");
              $this->form_validation->set_rules("brg_notes" . $a, "brg_notes", "required");
              if ($this->form_validation->run()) {
                $brg_qty = $this->input->post("brg_qty" . $a);
                $brg_qty = explode(" ", $brg_qty);
                if (count($brg_qty) > 1) {
                  $brg_pem_qty = $brg_qty[0];
                  $brg_pem_satuan = $brg_qty[1];
                } else {
                  $brg_pem_qty = $brg_qty[0];
                  $brg_pem_satuan = "Pcs";
                }
                $brg_pem_harga = $this->input->post("brg_price" . $a);
                $brg_pem_note = $this->input->post("brg_notes" . $a);
                $brg_pem_status = "AKTIF";
                $id_fk_pembelian = $id_pk_pembelian;
                $barang = $this->input->post("brg" . $a);
                $this->load->model("m_barang");
                $this->m_barang->set_brg_nama($barang);
                $result = $this->m_barang->detail_by_name();
                if ($result->num_rows() > 0) {
                  $result = $result->result_array();
                  $id_fk_barang = $result[0]["id_pk_brg"];
                } else {
                  $this->load->model("m_barang");
                  $this->m_barang->set_brg_nama($barang);
                  $id_fk_barang = $this->m_barang->short_insert();
                }
                $this->load->model("m_brg_pembelian");
                if ($this->m_brg_pembelian->set_insert($brg_pem_qty, $brg_pem_satuan, $brg_pem_harga, $brg_pem_note, $brg_pem_status, $id_fk_pembelian, $id_fk_barang)) {
                  if ($this->m_brg_pembelian->insert()) {
                    $response["itmsts"][$counter] = "SUCCESS";
                    $response["itmmsg"][$counter] = "Data is recorded to database";
                  } else {
                    $response["itmsts"][$counter] = "ERROR";
                    $response["itmmsg"][$counter] = "Insert function error";
                  }
                } else {
                  $response["itmsts"][$counter] = "ERROR";
                  $response["itmmsg"][$counter] = "Setter function error";
                }
              } else {
                $response["itmsts"][$counter] = "ERROR";
                $response["itmmsg"][$counter] = validation_errors();
              }
              $counter++;
            }
          } else {
            $response["itmstsall"] = "ERROR";
            $response["itmmsgall"] = "No Checks on Item";
          }
          $tambahan_edit = $this->input->post("tambahan_edit");
          if ($tambahan_edit != "") {
            $counter = 0;
            foreach ($tambahan_edit as $a) {
              $this->form_validation->reset_validation();
              $this->form_validation->set_rules("id_tmbhn_pem_edit" . $a, "id", "required");
              $this->form_validation->set_rules("tmbhn_edit" . $a, "tmbhn_edit", "required");
              $this->form_validation->set_rules("tmbhn_jumlah_edit" . $a, "tmbhn_jumlah_edit", "required");
              $this->form_validation->set_rules("tmbhn_harga_edit" . $a, "tmbhn_harga_edit", "required");
              $this->form_validation->set_rules("tmbhn_notes_edit" . $a, "tmbhn_notes_edit", "required");
              if ($this->form_validation->run()) {
                $id_pk_tmbhn = $this->input->post("id_tmbhn_pem_edit" . $a);
                $tmbhn = $this->input->post("tmbhn_edit" . $a);
                $qty = $this->input->post("tmbhn_jumlah_edit" . $a);
                $qty = explode(" ", $qty);
                if (count($qty) > 1) {
                  $tmbhn_jumlah = $qty[0];
                  $tmbhn_satuan = $qty[1];
                } else {
                  $tmbhn_jumlah = $qty[0];
                  $tmbhn_satuan = "Pcs";
                }
                $tmbhn_harga = $this->input->post("tmbhn_harga_edit" . $a);
                $tmbhn_notes = $this->input->post("tmbhn_notes_edit" . $a);

                $this->load->model("m_tambahan_pembelian");
                if ($this->m_tambahan_pembelian->set_update($id_pk_tmbhn, $tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes)) {
                  if ($this->m_tambahan_pembelian->update()) {
                    $response["tmbhnstsupdate"][$counter] = "SUCCESS";
                    $response["tmbhnmsgupdate"][$counter] = "Data is update to database";
                  } else {
                    $response["tmbhnstsupdate"][$counter] = "ERROR";
                    $response["tmbhnmsgupdate"][$counter] = "update function error";
                  }
                } else {
                  $response["tmbhnstsupdate"][$counter] = "ERROR";
                  $response["tmbhnmsgupdate"][$counter] = "Setter function error";
                }
              } else {
                $response["tmbhnstsupdate"][$counter] = "ERROR";
                $response["tmbhnmsgupdate"][$counter] = validation_errors();
              }
            }
          } else {
            $response["tmbhnsts"] = "ERROR";
            $response["tmbhnmsg"] = "No Checks on Tambahan";
          }

          $tambahan = $this->input->post("tambahan");
          if ($tambahan != "") {
            $counter = 0;
            foreach ($tambahan as $a) {
              $this->form_validation->reset_validation();
              $this->form_validation->set_rules("tmbhn" . $a, "tmbhn_add", "required");
              $this->form_validation->set_rules("tmbhn_jumlah" . $a, "tmbhn_jumlah_add", "required");
              $this->form_validation->set_rules("tmbhn_harga" . $a, "tmbhn_harga_add", "required");
              $this->form_validation->set_rules("tmbhn_notes" . $a, "tmbhn_notes_add", "required");
              if ($this->form_validation->run()) {
                $tmbhn = $this->input->post("tmbhn" . $a);
                $qty = $this->input->post("tmbhn_jumlah" . $a);
                $qty = explode(" ", $qty);
                if (count($qty) > 1) {
                  $tmbhn_jumlah = $qty[0];
                  $tmbhn_satuan = $qty[1];
                } else {
                  $tmbhn_jumlah = $qty[0];
                  $tmbhn_satuan = "Pcs";
                }
                $tmbhn_harga = $this->input->post("tmbhn_harga" . $a);
                $tmbhn_notes = $this->input->post("tmbhn_notes" . $a);
                $tmbhn_status = "AKTIF";
                $id_fk_pembelian = $id_pk_pembelian;

                $this->load->model("m_tambahan_pembelian");
                if ($this->m_tambahan_pembelian->set_insert($tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes, $tmbhn_status, $id_fk_pembelian)) {
                  if ($this->m_tambahan_pembelian->insert()) {
                    $response["tmbhnsts"][$counter] = "SUCCESS";
                    $response["tmbhnmsg"][$counter] = "Data is recorded to database";
                  } else {
                    $response["tmbhnsts"][$counter] = "ERROR";
                    $response["tmbhnmsg"][$counter] = "Insert function error";
                  }
                } else {
                  $response["tmbhnsts"][$counter] = "ERROR";
                  $response["tmbhnmsg"][$counter] = "Setter function error";
                }
              } else {
                $response["tmbhnsts"][$counter] = "ERROR";
                $response["tmbhnmsg"][$counter] = validation_errors();
              }
            }
          } else {
            $response["tmbhnsts"] = "ERROR";
            $response["tmbhnmsg"] = "No Checks on Tambahan";
          }
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
  public function selesai()
  {
    $response["status"] = "SUCCESS";
    $id_pk_pembelian = $this->input->get("id");
    if ($id_pk_pembelian != "" && is_numeric($id_pk_pembelian)) {
      $this->load->model("m_pembelian");
      if ($this->m_pembelian->set_update_status($id_pk_pembelian, "selesai")) {
        if ($this->m_pembelian->update_status()) {
          $response["msg"] = "Data is updated to database";
        } else {
          $response["status"] = "ERROR";
          $response["msg"] = "Update status function error";
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
  public function delete()
  {
    $response["status"] = "SUCCESS";
    $id_pk_pembelian = $this->input->get("id");
    if ($id_pk_pembelian != "" && is_numeric($id_pk_pembelian)) {

      if (!$this->is_allow_to_update($id_pk_pembelian)) {
        $response["status"] = "ERROR";
        $response["msg"] = "Data tidak bisa diupdate";
        echo json_encode($response);
        return 0;
      }

      $this->load->model("m_pembelian");
      if ($this->m_pembelian->set_delete($id_pk_pembelian)) {
        if ($this->m_pembelian->delete()) {
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
  public function detail($no_pembelian)
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_pembelian");
    $this->m_pembelian->set_pem_pk_nomor($no_pembelian);
    $result = $this->m_pembelian->detail_by_no();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["data"][$a]["id"] = $result[$a]["id_pk_pembelian"];
        $response["data"][$a]["nomor"] = $result[$a]["pem_pk_nomor"];
        $response["data"][$a]["tgl"] = $result[$a]["pem_tgl"];
        $response["data"][$a]["status"] = $result[$a]["pem_status"];
        $response["data"][$a]["supplier"] = $result[$a]["sup_perusahaan"];
        $response["data"][$a]["last_modified"] = $result[$a]["pem_last_modified"];
        $response["data"][$a]["daerah_cabang"] = $result[$a]["cabang_daerah"];
        $response["data"][$a]["notelp_cabang"] = $result[$a]["cabang_notelp"];
        $response["data"][$a]["alamat_cabang"] = $result[$a]["cabang_alamat"];
        $response["data"][$a]["nama_toko"] = $result[$a]["toko_nama"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Detail data untuk nomor terkait tidak ada";
    }
    echo json_encode($response);
  }
  public function list_pembelian()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_pembelian");
    $this->m_pembelian->set_id_fk_cabang($this->session->id_cabang);
    $result = $this->m_pembelian->list_data();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_pembelian"];
        $response["content"][$a]["nomor"] = $result[$a]["pem_pk_nomor"];
        $response["content"][$a]["tgl"] = $result[$a]["pem_tgl"];
        $response["content"][$a]["status"] = $result[$a]["pem_status"];
        $response["content"][$a]["perusahaan_sup"] = $result[$a]["sup_perusahaan"];
        $response["content"][$a]["last_modified"] = $result[$a]["pem_last_modified"];
        $response["content"][$a]["nama_toko"] = $result[$a]["toko_nama"];
        $response["content"][$a]["daerah_cabang"] = $result[$a]["cabang_daerah"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No Data";
    }
    echo json_encode($response);
  }
  public function list_pembelian_all()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_pembelian");
    $result = $this->m_pembelian->list_data();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_pembelian"];
        $response["content"][$a]["nomor"] = $result[$a]["pem_pk_nomor"];
        $response["content"][$a]["tgl"] = $result[$a]["pem_tgl"];
        $response["content"][$a]["status"] = $result[$a]["pem_status"];
        $response["content"][$a]["perusahaan_sup"] = $result[$a]["sup_perusahaan"];
        $response["content"][$a]["last_modified"] = $result[$a]["pem_last_modified"];
        $response["content"][$a]["nama_toko"] = $result[$a]["toko_nama"];
        $response["content"][$a]["daerah_cabang"] = $result[$a]["cabang_daerah"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No Data";
    }
    echo json_encode($response);
  }
  public function list_pembelian_toko()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_pembelian");
    $result = $this->m_pembelian->list_data_toko();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_pembelian"];
        $response["content"][$a]["nomor"] = $result[$a]["pem_pk_nomor"];
        $response["content"][$a]["tgl"] = $result[$a]["pem_tgl"];
        $response["content"][$a]["status"] = $result[$a]["pem_status"];
        $response["content"][$a]["perusahaan_sup"] = $result[$a]["sup_perusahaan"];
        $response["content"][$a]["last_modified"] = $result[$a]["pem_last_modified"];
        $response["content"][$a]["nama_toko"] = $result[$a]["toko_nama"];
        $response["content"][$a]["daerah_cabang"] = $result[$a]["cabang_daerah"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "No Data";
    }
    echo json_encode($response);
  }
  private function is_allow_to_update($id_pk_pembelian)
  {
    $this->load->model("m_pembelian");
    $this->m_pembelian->set_id_pk_pembelian($id_pk_pembelian);
    $result = $this->m_pembelian->detail_by_id();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      if (strtolower($result[0]["pem_status"]) != "aktif") {
        return false;
      }
      return true;
    }
  }
}
