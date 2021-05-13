<?php
defined("BASEPATH") or exit("no direct script");
class Penjualan extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }
  public function columns()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_penjualan");
    $columns = $this->m_penjualan->columns();
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
    $tipe_pembayaran = $this->input->get("tipe_pemb");

    $this->load->model("m_penjualan");
    $this->m_penjualan->set_id_fk_cabang($id_cabang);
    $this->m_penjualan->set_penj_tipe_pembayaran($tipe_pembayaran);

    $result = $this->m_penjualan->content($page, $order_by, $order_direction, $search_key, $data_per_page);

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
    $response["key"] = array(
      "nomor",
      "tgl",
      "cust_display",
      "tipe_pembayaran",
      "jenis",
      "status",
      "status_pembayaran",
      "user_last_modified",
    );
    echo json_encode($response);
  }
  public function brg_penjualan()
  {
    $response["status"] = "SUCCESS";
    $id_penjualan = $this->input->get("id");
    if ($id_penjualan != "" && is_numeric($id_penjualan)) {
      $this->load->model("m_brg_penjualan");
      $this->m_brg_penjualan->set_id_fk_penjualan($id_penjualan);
      $result = $this->m_brg_penjualan->list_data();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        for ($a = 0; $a < count($result); $a++) {
          $response["content"][$a]["id"] = $result[$a]["id_pk_brg_penjualan"];
          $response["content"][$a]["qty"] = $result[$a]["brg_penjualan_qty"];
          $response["content"][$a]["satuan"] = $result[$a]["brg_penjualan_satuan"];
          $response["content"][$a]["harga"] = $result[$a]["brg_penjualan_harga"];
          $response["content"][$a]["harga_stok"] = $result[$a]["brg_harga"];
          $response["content"][$a]["note"] = $result[$a]["brg_penjualan_note"];
          $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
          $response["content"][$a]["last_modified"] = $result[$a]["brg_penjualan_last_modified"];
          $response["content"][$a]["jmlh_terkirim"] = $result[$a]["jumlah_terkirim"];
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
  public function tmbhn_penjualan()
  {
    $response["status"] = "SUCCESS";
    $id_penjualan = $this->input->get("id");
    if ($id_penjualan != "" && is_numeric($id_penjualan)) {
      $this->load->model("m_tambahan_penjualan");
      $this->m_tambahan_penjualan->set_id_fk_penjualan($id_penjualan);
      $result = $this->m_tambahan_penjualan->list_data();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        for ($a = 0; $a < count($result); $a++) {
          $response["content"][$a]["id"] = $result[$a]["id_pk_tmbhn"];
          $response["content"][$a]["tmbhn"] = $result[$a]["tmbhn"];
          $response["content"][$a]["jumlah"] = $result[$a]["tmbhn_jumlah"];
          $response["content"][$a]["satuan"] = $result[$a]["tmbhn_satuan"];
          $response["content"][$a]["harga"] = $result[$a]["tmbhn_harga"];
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
  public function penjualan_online()
  {

    $response["status"] = "SUCCESS";
    $id_penjualan = $this->input->get("id");
    $this->load->model("m_penjualan_online");
    $this->m_penjualan_online->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_penjualan_online->detail();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_penjualan_online"];
        $response["content"][$a]["marketplace"] = $result[$a]["penj_on_marketplace"];
        $response["content"][$a]["no_resi"] = $result[$a]["penj_on_no_resi"];
        $response["content"][$a]["kurir"] = $result[$a]["penj_on_kurir"];
        $response["content"][$a]["status"] = $result[$a]["penj_on_status"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function brg_pindah_penjualan()
  {
    $response["status"] = "SUCCESS";
    $id_penjualan = $this->input->get("id");
    $this->load->model("m_brg_pindah");
    $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_penjualan);
    $this->m_brg_pindah->set_brg_pindah_sumber("penjualan");
    $result = $this->m_brg_pindah->list_data();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_brg_pindah"];
        $response["content"][$a]["brg_pindah_qty"] = $result[$a]["brg_pindah_qty"];
        $response["content"][$a]["brg_pindah_status"] = $result[$a]["brg_pindah_status"];
        $response["content"][$a]["brg_awal"] = $result[$a]["brg_awal"];
        $response["content"][$a]["brg_akhir"] = $result[$a]["brg_akhir"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function pembayaran_penjualan()
  {
    $response["status"] = "SUCCESS";
    $id_penjualan = $this->input->get("id");
    $this->load->model("m_penjualan_pembayaran");
    $this->m_penjualan_pembayaran->set_id_fk_penjualan($id_penjualan);
    $result = $this->m_penjualan_pembayaran->list_data();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["content"][$a]["id"] = $result[$a]["id_pk_penjualan_pembayaran"];
        $response["content"][$a]["nama"] = $result[$a]["penjualan_pmbyrn_nama"];
        $response["content"][$a]["persen"] = $result[$a]["penjualan_pmbyrn_persen"];
        $response["content"][$a]["nominal"] = $result[$a]["penjualan_pmbyrn_nominal"];
        $response["content"][$a]["notes"] = $result[$a]["penjualan_pmbyrn_notes"];
        $response["content"][$a]["dateline"] = $result[$a]["penjualan_pmbyrn_dateline"];
        $response["content"][$a]["status"] = strtoupper($result[$a]["penjualan_pmbyrn_status"]);
        $response["content"][$a]["last_modified"] = $result[$a]["penjualan_pmbyrn_last_modified"];
      }
    } else {
      $response["status"] = "ERROR";
    }
    echo json_encode($response);
  }
  public function remove_brg_penjualan()
  {
    $response["status"] = "SUCCESS";
    $id_pk_brg_penjualan = $this->input->get("id");
    if ($id_pk_brg_penjualan != "" && is_numeric($id_pk_brg_penjualan)) {
      $this->load->model("m_brg_penjualan");
      $this->m_brg_penjualan->set_delete($id_pk_brg_penjualan);
      if ($this->m_brg_penjualan->delete()) {
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
  public function remove_tmbhn_penjualan()
  {
    $response["status"] = "SUCCESS";
    $id_pk_tmbhn = $this->input->get("id");
    if ($id_pk_tmbhn != "" && is_numeric($id_pk_tmbhn)) {
      $this->load->model("m_tambahan_penjualan");
      $this->m_tambahan_penjualan->set_delete($id_pk_tmbhn);
      if ($this->m_tambahan_penjualan->delete()) {
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
  public function remove_pembayaran_penjualan()
  {
    $response["status"] = "SUCCESS";
    $id_pk_penjualan_pembayaran = $this->input->get("id");
    if ($id_pk_penjualan_pembayaran != "" && is_numeric($id_pk_penjualan_pembayaran)) {
      $this->load->model("m_penjualan_pembayaran");
      $this->m_penjualan_pembayaran->set_delete($id_pk_penjualan_pembayaran);
      if ($this->m_penjualan_pembayaran->delete()) {
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

    $this->form_validation->set_rules("tgl", "tgl", "required");
    $this->form_validation->set_rules("dateline", "dateline", "required");
    $this->form_validation->set_rules("customer", "customer", "required");
    $this->form_validation->set_rules("jenis_penjualan", "jenis_penjualan", "required");
    #$this->form_validation->set_rules("jenis_pembayaran", "jenis_pembayaran", "required");
    if ($this->form_validation->run()) {
      $penj_tgl = $this->input->post("tgl");
      $penj_dateline_tgl = $this->input->post("dateline");
      $penj_jenis = $this->input->post("jenis_penjualan");
      $customer = $this->input->post("customer");
      $id_fk_cabang = $this->input->post("id_cabang");
      $ppn_check = $this->input->post("ppn_check");
      $penj_status = "AKTIF";

      if($ppn_check != ""){
        $ppn_check = 1;
      }
      else{
        $ppn_check = 0;
      }
      $this->input->post("customer");
      $this->load->model("m_customer");
      $this->m_customer->set_cust_perusahaan($customer);
      $result = $this->m_customer->detail_by_perusahaan();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        $id_fk_customer = $result[0]["id_pk_cust"];
      } else {
        $id_fk_customer = $this->m_customer->short_insert();
      }
      $this->load->model("m_penjualan");
      $penj_nomor = $this->m_penjualan->get_penj_nomor($id_fk_cabang, "penjualan", $penj_tgl);

      #asumsinya kita akan tetep masukin harga dasarnya, waktu load edit nanti, kita akan check apakah ppn atau enggak, kalau iya ppn, kita akan hold nominalnya hidden dan display yg di modify.
      $id_penjualan = $this->m_penjualan->insert($penj_nomor, $penj_tgl, $penj_dateline_tgl, $penj_jenis, $ppn_check, $id_fk_customer, $id_fk_cabang, $penj_status);
      if ($id_penjualan) {
        $nominal_penjualan = 0;
        $penj_on_marketplace = $this->input->post("marketplace");
        $penj_on_no_resi = $this->input->post("no_resi");
        $penj_on_kurir = $this->input->post("kurir");
        if($penj_on_kurir == ""){
          $penj_on_kurir = "-";
        }
        $penj_on_status = "AKTIF";
        $id_fk_penjualan = $id_penjualan;;
        $this->load->model("m_penjualan_online");
        if ($this->m_penjualan_online->insert($penj_on_marketplace,$penj_on_no_resi,$penj_on_kurir,$penj_on_status,$id_fk_penjualan)) {
          $response["pnjonlinests"] = "SUCCESS";
          $response["pnjonlinemsg"] = "Data is recorded to database";
        } else {
          $response["pnjonlinests"] = "ERROR";
          $response["pnjonlinemsg"] = "Insert function error";
        }
        $response["msg"] = "Data is recorded to database";

        $check = $this->input->post("check");
        if ($check != "") {
          $counter = 0;
          foreach ($check as $a) {
            $this->form_validation->set_rules("brg" . $a, "brg", "required");
            $this->form_validation->set_rules("brg_qty_real" . $a, "brg_qty_real", "required");
            $this->form_validation->set_rules("brg_qty" . $a, "brg_qty", "required");
            $this->form_validation->set_rules("brg_price" . $a, "brg_price", "required");
            if ($this->form_validation->run()) {
              $brg_qty = $this->input->post("brg_qty" . $a);
              $brg_qty = explode(" ", $brg_qty);
              if (count($brg_qty) > 1) {
                $brg_penjualan_qty = $brg_qty[0];
                $brg_penjualan_satuan = $brg_qty[1];
              } else {
                $brg_penjualan_qty = $brg_qty[0];
                $brg_penjualan_satuan = "Pcs";
              }

              $brg_qty = $this->input->post("brg_qty_real" . $a);
              $brg_qty = explode(" ", $brg_qty);
              if (count($brg_qty) > 1) {
                $brg_penjualan_qty_real = $brg_qty[0];
                $brg_penjualan_satuan_real = $brg_qty[1];
              } else {
                $brg_penjualan_qty_real = $brg_qty[0];
                $brg_penjualan_satuan_real = "Pcs";
              }

              $brg_penjualan_harga = $this->input->post("brg_price" . $a);
              $brg_penjualan_note = $this->input->post("brg_notes" . $a);
              $brg_penjualan_status = "AKTIF";
              $id_fk_penjualan = $id_penjualan;

              $barang = $this->input->post("brg" . $a);
              $this->load->model("m_barang");
              $this->m_barang->set_brg_nama($barang);
              $result = $this->m_barang->detail_by_name();
              if ($result->num_rows() > 0) {
                $result = $result->result_array();
                $id_fk_barang = $result[0]["id_pk_brg"];

                $this->load->model("m_brg_penjualan");
                if ($this->m_brg_penjualan->insert($brg_penjualan_qty_real, $brg_penjualan_satuan_real, $brg_penjualan_qty, $brg_penjualan_satuan, $brg_penjualan_harga, $brg_penjualan_note, $brg_penjualan_status, $id_fk_penjualan, $id_fk_barang)) {
                  $response["itmsts"][$counter] = "SUCCESS";
                  $response["itmmsg"][$counter] = "Data is recorded to database";
                } else {
                  $response["itmsts"][$counter] = "ERROR";
                  $response["itmmsg"][$counter] = "Insert function error";
                }
              } else {
                $response["itmsts"][$counter] = "ERROR";
                $response["itmmsg"][$counter] = "BARANG TIDAK TERDAFTAR";
              }
            } else {
              $response["status"] = "ERROR";
              $response["itmsts"][$counter] = "ERROR";
              $response["itmmsg"][$counter] = validation_errors();
            }
            $counter++;
          }
          $this->load->model("m_brg_penjualan");
          $this->m_brg_penjualan->set_id_fk_penjualan($id_penjualan);
          $nominal_penjualan += $this->m_brg_penjualan->get_nominal_brg_penjualan();
        } else {
          $response["itmsts"] = "ERROR";
          $response["itmmsg"] = "No Checks on Item";
        }

        $tambahan = $this->input->post("tambahan");
        if ($tambahan != "") {
          $counter = 0;
          foreach ($tambahan as $a) {
            $this->load->library("form_validation");
            $this->form_validation->set_rules("tmbhn" . $a, "tmbhn", "required");
            $this->form_validation->set_rules("tmbhn_jumlah" . $a, "tmbhn_jumlah", "required");
            $this->form_validation->set_rules("tmbhn_harga" . $a, "tmbhn_harga", "required");
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
              $id_fk_penjualan = $id_penjualan;

              $this->load->model("m_tambahan_penjualan");
              if ($this->m_tambahan_penjualan->insert($tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes, $tmbhn_status, $id_fk_penjualan)) {
                $response["tmbhnsts"][$counter] = "SUCCESS";
                $response["tmbhnmsg"][$counter] = "Data is recorded to database";
              } else {
                $response["status"] = "ERROR";
                $response["tmbhnsts"][$counter] = "ERROR";
                $response["tmbhnmsg"][$counter] = "Insert function error";
              }
            } else {
              $response["status"] = "ERROR";
              $response["tmbhnsts"][$counter] = "ERROR";
              $response["tmbhnmsg"][$counter] = validation_errors();
            }
            $counter++;
          }
          $this->load->model("m_tambahan_penjualan");
          $this->m_tambahan_penjualan->set_id_fk_penjualan($id_penjualan);
          $nominal_penjualan += $this->m_tambahan_penjualan->get_nominal_tambahan();
        } else {
          $response["tmbhnsts"] = "ERROR";
          $response["tmbhnmsg"] = "No Checks on Tambahan";
        }

        $nominal_pembayaran = 0;
        $pembayaran = $this->input->post("pembayaran");
        if ($pembayaran != "") {
          $counter = 0;
          foreach ($pembayaran as $a) {
            $this->load->library("form_validation");
            $this->form_validation->set_rules("pmbyrn_nama" . $a, "pmbyrn_nama", "required");
            $this->form_validation->set_rules("pmbyrn_persen" . $a, "pmbyrn_persen", "required");
            $this->form_validation->set_rules("pmbyrn_nominal" . $a, "pmbyrn_nominal", "required");
            $this->form_validation->set_rules("pmbyrn_dateline" . $a, "pmbyrn_dateline", "required");

            if ($this->form_validation->run()) {
              $id_fk_penjualan = $id_penjualan;
              $penjualan_pmbyrn_nama = $this->input->post("pmbyrn_nama" . $a);
              $penjualan_pmbyrn_persen = $this->input->post("pmbyrn_persen" . $a);
              $penjualan_pmbyrn_nominal = $this->input->post("pmbyrn_nominal" . $a);
              $penjualan_pmbyrn_notes = $this->input->post("pmbyrn_notes" . $a);
              $penjualan_pmbyrn_dateline = $this->input->post("pmbyrn_dateline" . $a);
              $penjualan_pmbyrn_status = $this->input->post("pmbyrn_status" . $a);

              $this->load->model("m_penjualan_pembayaran");
              if ($this->m_penjualan_pembayaran->insert($id_fk_penjualan, $penjualan_pmbyrn_nama, $penjualan_pmbyrn_persen, $penjualan_pmbyrn_nominal, $penjualan_pmbyrn_notes, $penjualan_pmbyrn_dateline, $penjualan_pmbyrn_status)) {
                $response["pmbyrnsts"][$counter] = "SUCCESS";
                $response["pmbyrnmsg"][$counter] = "Data is recorded to database";
              } else {
                $response["status"] = "ERROR";
                $response["pmbyrnsts"][$counter] = "ERROR";
                $response["pmbyrnmsg"][$counter] = "Insert function error";
              }
            } else {
              $response["status"] = "ERROR";
              $response["pmbyrnsts"][$counter] = "ERROR";
              $response["pmbyrnmsg"][$counter] = validation_errors();
            }
            $counter++;
          }
        } else {
          $response["pmbyrnsts"] = "ERROR";
          $response["pmbyrnmsg"] = "No Checks on Pembayaran";
        }

        $this->load->model("m_penjualan_pembayaran");
        $this->m_penjualan_pembayaran->set_id_fk_penjualan($id_penjualan);
        $nominal_pembayaran += $this->m_penjualan_pembayaran->get_nominal_pembayaran();

        $brg_custom = $this->input->post("brg_custom");
        if ($brg_custom != "") {
          $counter = 0;
          foreach ($brg_custom as $a) {
            $id_brg_custom = $this->input->post("id_brg_custom" . $a);
            $this->load->model("m_brg_pindah");
            $this->m_brg_pindah->set_id_pk_brg_pindah($id_brg_custom);
            $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_penjualan);
            $this->m_brg_pindah->update_id_fk_refrensi_sumber();

            $response["brgcustomsts"][$counter] = "SUCCESS";
            $response["brgcustommsg"][$counter] = "Data is recorded to database";
            $counter++;
          }
        }

        $this->load->model("m_penjualan");
        $this->m_penjualan->set_id_pk_penjualan($id_penjualan);
        $this->m_penjualan->update_nominal($nominal_penjualan);
        $this->m_penjualan->update_nominal_byr($nominal_pembayaran);
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

    $this->form_validation->set_rules("id_pk_penjualan", "ID penjualan", "required");
    $this->form_validation->set_rules("no_penjualan", "No penjualan", "required");
    $this->form_validation->set_rules("tgl", "tgl", "required");
    $this->form_validation->set_rules("dateline", "dateline", "required");
    $this->form_validation->set_rules("customer", "customer", "required");
    $this->form_validation->set_rules("jenis_penjualan", "jenis_penjualan", "required");
    #$this->form_validation->set_rules("jenis_pembayaran", "jenis_pembayaran", "required");
    if ($this->form_validation->run()) {
      $id_pk_penjualan = $this->input->post("id_pk_penjualan");
      $penj_nomor = $this->input->post("no_penjualan");
      $penj_tgl = $this->input->post("tgl");
      $penj_dateline_tgl = $this->input->post("dateline");
      $penj_jenis = $this->input->post("jenis_penjualan");
      $customer = $this->input->post("customer");
      $ppn_check = $this->input->post("ppn_check");

      if($ppn_check != ""){
        $ppn_check = 1;
      }
      else{
        $ppn_check = 0;
      }
      $this->input->post("customer");
      $this->load->model("m_customer");
      $this->m_customer->set_cust_perusahaan($customer);
      $result = $this->m_customer->detail_by_perusahaan();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        $id_fk_customer = $result[0]["id_pk_cust"];
      } else {
        $id_fk_customer = $this->m_customer->short_insert();
      }
      $this->load->model("m_penjualan");

      #asumsinya kita akan tetep masukin harga dasarnya, waktu load edit nanti, kita akan check apakah ppn atau enggak, kalau iya ppn, kita akan hold nominalnya hidden dan display yg di modify.
      $this->m_penjualan->update($id_pk_penjualan, $penj_nomor, $penj_dateline_tgl, $penj_jenis, $ppn_check, $penj_tgl, $id_fk_customer);
      $nominal_penjualan = 0;
      $penj_on_marketplace = $this->input->post("marketplace");
      $penj_on_no_resi = $this->input->post("no_resi");
      $penj_on_kurir = $this->input->post("kurir");
      if($penj_on_kurir == ""){
        $penj_on_kurir = "-";
      }

      $this->load->model("m_penjualan_online");
      $this->m_penjualan_online->update($penj_on_marketplace, $penj_on_no_resi, $penj_on_kurir, $id_pk_penjualan);
      $response["pnjonlinests"] = "SUCCESS";
      $response["pnjonlinemsg"] = "Data is recorded to database";

      $check = $this->input->post("edit_check");
      if ($check != "") {
        $counter = 0;
        foreach ($check as $a) {
          $this->form_validation->set_rules("id_pk_brg_penjualan" . $a, "id_pk_brg_penjualan", "required");
          $this->form_validation->set_rules("brg" . $a, "brg", "required");
          $this->form_validation->set_rules("brg_qty_real" . $a, "brg_qty_real", "required");
          $this->form_validation->set_rules("brg_qty" . $a, "brg_qty", "required");
          $this->form_validation->set_rules("brg_price" . $a, "brg_price", "required");
          if ($this->form_validation->run()) {
            $id_pk_brg_penjualan = $this->input->post("id_pk_brg_penjualan".$a);
            $brg_qty = $this->input->post("brg_qty" . $a);
            $brg_qty = explode(" ", $brg_qty);
            if (count($brg_qty) > 1) {
              $brg_penjualan_qty = $brg_qty[0];
              $brg_penjualan_satuan = $brg_qty[1];
            } 
            else {
              $brg_penjualan_qty = $brg_qty[0];
              $brg_penjualan_satuan = "Pcs";
            }
            $brg_penjualan_harga = $this->input->post("brg_price" . $a);
            $brg_penjualan_note = $this->input->post("brg_notes" . $a);

            $barang = $this->input->post("brg" . $a);
            $this->load->model("m_barang");
            $this->m_barang->set_brg_nama($barang);
            $result = $this->m_barang->detail_by_name();
            if ($result->num_rows() > 0) {
              $result = $result->result_array();
              $id_fk_barang = $result[0]["id_pk_brg"];

              $this->load->model("m_brg_penjualan");
              if ($this->m_brg_penjualan->update($id_pk_brg_penjualan, $brg_penjualan_qty, $brg_penjualan_satuan, $brg_penjualan_harga, $brg_penjualan_note, $id_fk_barang)) {
                $response["itmsts"][$counter] = "SUCCESS";
                $response["itmmsg"][$counter] = "Data is recorded to database";
              } else {
                $response["itmsts"][$counter] = "ERROR";
                $response["itmmsg"][$counter] = "Insert function error";
              }
            } else {
              $response["itmsts"][$counter] = "ERROR";
              $response["itmmsg"][$counter] = "BARANG TIDAK TERDAFTAR";
            }
          } else {
            $response["status"] = "ERROR";
            $response["itmsts"][$counter] = "ERROR";
            $response["itmmsg"][$counter] = validation_errors();
          }
          $counter++;
        }
        $this->load->model("m_brg_penjualan");
        $this->m_brg_penjualan->set_id_fk_penjualan($id_pk_penjualan);
        $nominal_penjualan += $this->m_brg_penjualan->get_nominal_brg_penjualan();
      } 
      else {
        $response["itmsts"] = "ERROR";
        $response["itmmsg"] = "No Checks on Item";
      }

      $tambahan = $this->input->post("edit_tambahan");
      if ($tambahan != "") {
        $counter = 0;
        foreach ($tambahan as $a) {
          $this->load->library("form_validation");
          $this->form_validation->set_rules("id_pk_tmbhn" . $a, "id_pk_tmbhn", "required");
          $this->form_validation->set_rules("tmbhn" . $a, "tmbhn", "required");
          $this->form_validation->set_rules("tmbhn_jumlah" . $a, "tmbhn_jumlah", "required");
          $this->form_validation->set_rules("tmbhn_harga" . $a, "tmbhn_harga", "required");
          if ($this->form_validation->run()) {
            $id_pk_tmbhn = $this->input->post("id_pk_tmbhn" . $a);
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

            $this->load->model("m_tambahan_penjualan");
            if ($this->m_tambahan_penjualan->update($id_pk_tmbhn, $tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes)) {
              $response["tmbhnsts"][$counter] = "SUCCESS";
              $response["tmbhnmsg"][$counter] = "Data is recorded to database";
            } 
            else {
              $response["status"] = "ERROR";
              $response["tmbhnsts"][$counter] = "ERROR";
              $response["tmbhnmsg"][$counter] = "Insert function error";
            }
          } 
          else {
            $response["status"] = "ERROR";
            $response["tmbhnsts"][$counter] = "ERROR";
            $response["tmbhnmsg"][$counter] = validation_errors();
          }
          $counter++;
        }
        $this->load->model("m_tambahan_penjualan");
        $this->m_tambahan_penjualan->set_id_fk_penjualan($id_pk_penjualan);
        $nominal_penjualan += $this->m_tambahan_penjualan->get_nominal_tambahan();
      } 
      else {
        $response["tmbhnsts"] = "ERROR";
        $response["tmbhnmsg"] = "No Checks on Tambahan";
      }

      $pembayaran = $this->input->post("edit_pembayaran");
      if ($pembayaran != "") {
        $counter = 0;
        foreach ($pembayaran as $a) {
          $this->load->library("form_validation");
          $this->form_validation->set_rules("id_pk_penjualan_pembayaran" . $a, "id_pk_penjualan_pembayaran", "required");
          $this->form_validation->set_rules("pmbyrn_nama" . $a, "pmbyrn_nama", "required");
          $this->form_validation->set_rules("pmbyrn_persen" . $a, "pmbyrn_persen", "required");
          $this->form_validation->set_rules("pmbyrn_nominal" . $a, "pmbyrn_nominal", "required");
          $this->form_validation->set_rules("pmbyrn_dateline" . $a, "pmbyrn_dateline", "required");
          $this->form_validation->set_rules("pmbyrn_status" . $a, "pmbyrn_status", "required");

          if ($this->form_validation->run()) {
            $id_pk_penjualan_pembayaran = $this->input->post("id_pk_penjualan_pembayaran" . $a);
            $penjualan_pmbyrn_nama = $this->input->post("pmbyrn_nama" . $a);
            $penjualan_pmbyrn_persen = $this->input->post("pmbyrn_persen" . $a);
            $penjualan_pmbyrn_nominal = $this->input->post("pmbyrn_nominal" . $a);
            $penjualan_pmbyrn_notes = $this->input->post("pmbyrn_notes" . $a);
            $penjualan_pmbyrn_status = $this->input->post("pmbyrn_status" . $a);
            $penjualan_pmbyrn_dateline = $this->input->post("pmbyrn_dateline" . $a);

            $this->load->model("m_penjualan_pembayaran");
            if ($this->m_penjualan_pembayaran->update($id_pk_penjualan_pembayaran, $penjualan_pmbyrn_nama, $penjualan_pmbyrn_persen, $penjualan_pmbyrn_nominal, $penjualan_pmbyrn_notes, $penjualan_pmbyrn_dateline, $penjualan_pmbyrn_status)) {
              $response["pmbyrnsts"][$counter] = "SUCCESS";
              $response["pmbyrnmsg"][$counter] = "Data is recorded to database";
            } else {
              $response["status"] = "ERROR";
              $response["pmbyrnsts"][$counter] = "ERROR";
              $response["pmbyrnmsg"][$counter] = "Insert function error";
            }
          } else {
            $response["status"] = "ERROR";
            $response["pmbyrnsts"][$counter] = "ERROR";
            $response["pmbyrnmsg"][$counter] = validation_errors();
          }
          $counter++;
        }
      } 
      else {
        $response["pmbyrnsts"] = "ERROR";
        $response["pmbyrnmsg"] = "No Checks on Pembayaran";
      }

      $check = $this->input->post("check");
      if ($check != "") {
        $counter = 0;
        foreach ($check as $a) {
          $this->form_validation->set_rules("brg" . $a, "brg", "required");
          $this->form_validation->set_rules("brg_qty_real" . $a, "brg_qty_real", "required");
          $this->form_validation->set_rules("brg_qty" . $a, "brg_qty", "required");
          $this->form_validation->set_rules("brg_price" . $a, "brg_price", "required");
          if ($this->form_validation->run()) {
            $brg_qty = $this->input->post("brg_qty" . $a);
            $brg_qty = explode(" ", $brg_qty);
            if (count($brg_qty) > 1) {
              $brg_penjualan_qty = $brg_qty[0];
              $brg_penjualan_satuan = $brg_qty[1];
            } else {
              $brg_penjualan_qty = $brg_qty[0];
              $brg_penjualan_satuan = "Pcs";
            }

            $brg_qty = $this->input->post("brg_qty_real" . $a);
            $brg_qty = explode(" ", $brg_qty);
            if (count($brg_qty) > 1) {
              $brg_penjualan_qty_real = $brg_qty[0];
              $brg_penjualan_satuan_real = $brg_qty[1];
            } else {
              $brg_penjualan_qty_real = $brg_qty[0];
              $brg_penjualan_satuan_real = "Pcs";
            }

            $brg_penjualan_harga = $this->input->post("brg_price" . $a);
            $brg_penjualan_note = $this->input->post("brg_notes" . $a);
            $brg_penjualan_status = "AKTIF";
            $id_fk_penjualan = $id_pk_penjualan;

            $barang = $this->input->post("brg" . $a);
            $this->load->model("m_barang");
            $this->m_barang->set_brg_nama($barang);
            $result = $this->m_barang->detail_by_name();
            if ($result->num_rows() > 0) {
              $result = $result->result_array();
              $id_fk_barang = $result[0]["id_pk_brg"];

              $this->load->model("m_brg_penjualan");
              if ($this->m_brg_penjualan->insert($brg_penjualan_qty_real, $brg_penjualan_satuan_real, $brg_penjualan_qty, $brg_penjualan_satuan, $brg_penjualan_harga, $brg_penjualan_note, $brg_penjualan_status, $id_fk_penjualan, $id_fk_barang)) {
                $response["itmsts"][$counter] = "SUCCESS";
                $response["itmmsg"][$counter] = "Data is recorded to database";
              } else {
                $response["itmsts"][$counter] = "ERROR";
                $response["itmmsg"][$counter] = "Insert function error";
              }
            } else {
              $response["itmsts"][$counter] = "ERROR";
              $response["itmmsg"][$counter] = "BARANG TIDAK TERDAFTAR";
            }
          } else {
            $response["status"] = "ERROR";
            $response["itmsts"][$counter] = "ERROR";
            $response["itmmsg"][$counter] = validation_errors();
          }
          $counter++;
        }
        $this->load->model("m_brg_penjualan");
        $this->m_brg_penjualan->set_id_fk_penjualan($id_pk_penjualan);
        $nominal_penjualan += $this->m_brg_penjualan->get_nominal_brg_penjualan();
      } 
      else {
        $response["itmsts"] = "ERROR";
        $response["itmmsg"] = "No Checks on Item";
      }

      $tambahan = $this->input->post("tambahan");
      if ($tambahan != "") {
        $counter = 0;
        foreach ($tambahan as $a) {
          $this->load->library("form_validation");
          $this->form_validation->set_rules("tmbhn" . $a, "tmbhn", "required");
          $this->form_validation->set_rules("tmbhn_jumlah" . $a, "tmbhn_jumlah", "required");
          $this->form_validation->set_rules("tmbhn_harga" . $a, "tmbhn_harga", "required");
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
            $id_fk_penjualan = $id_pk_penjualan;

            $this->load->model("m_tambahan_penjualan");
            if ($this->m_tambahan_penjualan->insert($tmbhn, $tmbhn_jumlah, $tmbhn_satuan, $tmbhn_harga, $tmbhn_notes, $tmbhn_status, $id_fk_penjualan)) {
              $response["tmbhnsts"][$counter] = "SUCCESS";
              $response["tmbhnmsg"][$counter] = "Data is recorded to database";
            } else {
              $response["status"] = "ERROR";
              $response["tmbhnsts"][$counter] = "ERROR";
              $response["tmbhnmsg"][$counter] = "Insert function error";
            }
          } else {
            $response["status"] = "ERROR";
            $response["tmbhnsts"][$counter] = "ERROR";
            $response["tmbhnmsg"][$counter] = validation_errors();
          }
          $counter++;
        }
        $this->load->model("m_tambahan_penjualan");
        $this->m_tambahan_penjualan->set_id_fk_penjualan($id_pk_penjualan);
        $nominal_penjualan += $this->m_tambahan_penjualan->get_nominal_tambahan();
      } 
      else {
        $response["tmbhnsts"] = "ERROR";
        $response["tmbhnmsg"] = "No Checks on Tambahan";
      }

      $pembayaran = $this->input->post("pembayaran");
      if ($pembayaran != "") {
        $counter = 0;
        foreach ($pembayaran as $a) {
          $this->load->library("form_validation");
          $this->form_validation->set_rules("pmbyrn_nama" . $a, "pmbyrn_nama", "required");
          $this->form_validation->set_rules("pmbyrn_persen" . $a, "pmbyrn_persen", "required");
          $this->form_validation->set_rules("pmbyrn_nominal" . $a, "pmbyrn_nominal", "required");
          $this->form_validation->set_rules("pmbyrn_dateline" . $a, "pmbyrn_dateline", "required");

          if ($this->form_validation->run()) {
            $id_fk_penjualan = $id_pk_penjualan;
            $penjualan_pmbyrn_nama = $this->input->post("pmbyrn_nama" . $a);
            $penjualan_pmbyrn_persen = $this->input->post("pmbyrn_persen" . $a);
            $penjualan_pmbyrn_nominal = $this->input->post("pmbyrn_nominal" . $a);
            $penjualan_pmbyrn_notes = $this->input->post("pmbyrn_notes" . $a);
            $penjualan_pmbyrn_dateline = $this->input->post("pmbyrn_dateline" . $a);
            $penjualan_pmbyrn_status = $this->input->post("pmbyrn_status" . $a);

            $this->load->model("m_penjualan_pembayaran");
            if ($this->m_penjualan_pembayaran->insert($id_fk_penjualan, $penjualan_pmbyrn_nama, $penjualan_pmbyrn_persen, $penjualan_pmbyrn_nominal, $penjualan_pmbyrn_notes, $penjualan_pmbyrn_dateline, $penjualan_pmbyrn_status)) {
              $response["pmbyrnsts"][$counter] = "SUCCESS";
              $response["pmbyrnmsg"][$counter] = "Data is recorded to database";
            } else {
              $response["status"] = "ERROR";
              $response["pmbyrnsts"][$counter] = "ERROR";
              $response["pmbyrnmsg"][$counter] = "Insert function error";
            }
          } else {
            $response["status"] = "ERROR";
            $response["pmbyrnsts"][$counter] = "ERROR";
            $response["pmbyrnmsg"][$counter] = validation_errors();
          }
          $counter++;
        }
      } 
      else {
        $response["pmbyrnsts"] = "ERROR";
        $response["pmbyrnmsg"] = "No Checks on Pembayaran";
      }
      $brg_custom = $this->input->post("brg_custom");
      if ($brg_custom != "") {
        $counter = 0;
        foreach ($brg_custom as $a) {
          $id_brg_custom = $this->input->post("id_brg_custom" . $a);
          $this->load->model("m_brg_pindah");
          $this->m_brg_pindah->set_id_pk_brg_pindah($id_brg_custom);
          $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_pk_penjualan);
          $this->m_brg_pindah->update_id_fk_refrensi_sumber();

          $response["brgcustomsts"][$counter] = "SUCCESS";
          $response["brgcustommsg"][$counter] = "Data is recorded to database";
          $counter++;
        }
      }

      $this->load->model("m_penjualan_pembayaran");
      $this->m_penjualan_pembayaran->set_id_fk_penjualan($id_pk_penjualan);
      $nominal_pembayaran = $this->m_penjualan_pembayaran->get_nominal_pembayaran();

      $this->load->model("m_penjualan");
      $this->m_penjualan->set_id_pk_penjualan($id_pk_penjualan);
      $this->m_penjualan->update_nominal($nominal_penjualan);
      $this->m_penjualan->update_nominal_byr($nominal_pembayaran);
    } 
    else {
      $response["status"] = "ERROR";
      $response["msg"] = validation_errors();
    }
    echo json_encode($response);
  }
  public function delete()
  {
    $response["status"] = "SUCCESS";
    $id_pk_penjualan = $this->input->get("id");
    if ($id_pk_penjualan != "" && is_numeric($id_pk_penjualan)) {

      if (!$this->is_allow_to_update($id_pk_penjualan)) {
        $response["status"] = "ERROR";
        $response["msg"] = " Data tidak dapat diubah";
        echo json_encode($response);
        return 0;
      }

      $this->load->model("m_penjualan");
      if ($this->m_penjualan->set_delete($id_pk_penjualan)) {
        if ($this->m_penjualan->delete()) {
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
  public function list_data()
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_penjualan");
    $cabang = $this->input->get("id_cabang");
    if ($cabang && is_numeric($cabang)) {
      $this->m_penjualan->set_id_fk_cabang($cabang);
      $result = $this->m_penjualan->list_data();
      if ($result->num_rows() > 0) {
        $result = $result->result_array();
        for ($a = 0; $a < count($result); $a++) {
          $response["content"][$a]["id"] = $result[$a]["id_pk_penjualan"];
          $response["content"][$a]["nomor"] = $result[$a]["penj_nomor"];
          $response["content"][$a]["tgl"] = explode(" ", $result[$a]["penj_tgl"])[0];
          $response["content"][$a]["dateline_tgl"] = explode(" ", $result[$a]["penj_dateline_tgl"])[0];
          $response["content"][$a]["status"] = $result[$a]["penj_status"];
          $response["content"][$a]["jenis"] = $result[$a]["penj_jenis"];
          $response["content"][$a]["tipe_pembayaran"] = $result[$a]["penj_tipe_pembayaran"];
          $response["content"][$a]["last_modified"] = $result[$a]["penj_last_modified"];
          $response["content"][$a]["perusahaan_cust"] = ucwords($result[$a]["cust_perusahaan"]);
          $response["content"][$a]["name_cust"] = ucwords($result[$a]["cust_name"]);
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
  public function detail($no_penjualan)
  {
    $response["status"] = "SUCCESS";
    $this->load->model("m_penjualan");
    $this->m_penjualan->set_penj_nomor($no_penjualan);
    $result = $this->m_penjualan->detail_by_penj_nomor();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      for ($a = 0; $a < count($result); $a++) {
        $response["data"][$a]["id"] = $result[$a]["id_pk_penjualan"];
        $response["data"][$a]["nomor"] = $result[$a]["penj_nomor"];
        $response["data"][$a]["tgl"] = $result[$a]["penj_tgl"];
        $response["data"][$a]["dateline_tgl"] = $result[$a]["penj_dateline_tgl"];
        $response["data"][$a]["status"] = $result[$a]["penj_status"];
        $response["data"][$a]["jenis"] = $result[$a]["penj_jenis"];
        $response["data"][$a]["tipe_pembayaran"] = $result[$a]["penj_tipe_pembayaran"];
        $response["data"][$a]["last_modified"] = $result[$a]["penj_last_modified"];
        $response["data"][$a]["cust_perusahaan"] = strtoupper($result[$a]["cust_perusahaan"]);
        $response["data"][$a]["name_cust"] = strtoupper($result[$a]["cust_name"]);
        $response["data"][$a]["suff_cust"] = strtoupper($result[$a]["cust_suff"]);
        $response["data"][$a]["email_cust"] = $result[$a]["cust_email"];
        $response["data"][$a]["telp_cust"] = $result[$a]["cust_telp"];
        $response["data"][$a]["hp_cust"] = $result[$a]["cust_hp"];
        $response["data"][$a]["alamat_cust"] = $result[$a]["cust_alamat"];
        $response["data"][$a]["keterangan_cust"] = $result[$a]["cust_keterangan"];
      }
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Detail data untuk nomor terkait tidak ada";
    }
    echo json_encode($response);
  }
  public function selesai()
  {
    $response["status"] = "SUCCESS";
    $id_pk_penjualan = $this->input->get("id");
    if ($id_pk_penjualan != "" && is_numeric($id_pk_penjualan)) {
      $this->load->model("m_penjualan");
      $this->m_penjualan->set_id_pk_penjualan($id_pk_penjualan);
      $this->m_penjualan->set_penj_status("selesai");
      $this->m_penjualan->update_status();
    } else {
      $response["status"] = "ERROR";
      $response["msg"] = "Invalid ID Supplier";
    }
    echo json_encode($response);
  }
  private function is_allow_to_update($id_pk_penjualan)
  {
    $this->load->model("m_penjualan");
    $this->m_penjualan->set_id_pk_penjualan($id_pk_penjualan);
    $result = $this->m_penjualan->detail_by_id();
    if ($result->num_rows() > 0) {
      $result = $result->result_array();
      if (strtolower($result[0]["penj_status"]) == "aktif") {
        return true;
      }
      return false;
    }
  }
}
