<?php
defined("BASEPATH") or exit("No direct script");
class Barang extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function insert(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("kode","kode","required");
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("stok","stok","required");
        $this->form_validation->set_rules("ket","ket","required");
        $this->form_validation->set_rules("minimal","minimal","required");
        $this->form_validation->set_rules("harga","harga","required");
        $this->form_validation->set_rules("id_brg_jenis","id_brg_jenis","required");
        $this->form_validation->set_rules("id_brg_merk","id_brg_merk","required");
        
        if($this->form_validation->run()){
            $this->load->model("m_barang");
            $brg_kode = $this->input->post("kode");
            $brg_nama = $this->input->post("nama");
            $brg_stok = $this->input->post("stok");
            $brg_ket = $this->input->post("ket");
            $brg_minimal = $this->input->post("minimal");
            $brg_status = "AKTIF";
            $brg_harga = $this->input->post("harga");
            $id_fk_brg_jenis = $this->input->post("id_brg_jenis");
            $id_fk_brg_merk = $this->input->post("id_brg_merk");
            if($this->m_barang->set_insert($brg_kode,$brg_nama,$brg_stok,$brg_ket,$brg_minimal,$brg_status,$brg_harga,$id_fk_brg_jenis,$id_fk_brg_merk)){
                if($this->m_barang->insert()){
                    $response["msg"] = "Data is recorded to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function is error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = validation_errors();
        }
        echo json_encode($response);
    }
    public function update(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id","id","required");
        $this->form_validation->set_rules("kode","kode","required");
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("stok","stok","required");
        $this->form_validation->set_rules("ket","ket","required");
        $this->form_validation->set_rules("minimal","minimal","required");
        $this->form_validation->set_rules("harga","harga","required");
        $this->form_validation->set_rules("id_brg_jenis","id_brg_jenis","required");
        $this->form_validation->set_rules("id_brg_merk","id_brg_merk","required");

        if($this->form_validation->run()){
            $this->load->model("m_barang");
            $id_pk_brg = $this->input->post("id");
            $brg_kode = $this->input->post("kode");
            $brg_nama = $this->input->post("nama");
            $brg_stok = $this->input->post("stok");
            $brg_ket = $this->input->post("ket");
            $brg_minimal = $this->input->post("minimal");
            $brg_harga = $this->input->post("harga");
            $id_fk_brg_jenis = $this->input->post("id_brg_jenis");
            $id_fk_brg_merk = $this->input->post("id_brg_merk");
            if($this->m_barang->set_update($id_pk_brg,$brg_kode,$brg_nama,$brg_stok,$brg_ket,$brg_minimal,$brg_harga,$id_fk_brg_jenis,$id_fk_brg_merk)){
                if($this->m_barang->update()){
                    $response["msg"] = "Data is updated to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function is error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = validation_errors();
        }
        echo json_encode($response);
    }
    public function delete(){
        $response["status"] = "SUCCESS";
        $id_pk_barang = $this->input->get("id_barang");
        if($id_pk_barang != "" && is_numeric($id_pk_barang)){
            $this->load->model("m_barang");
            if($this->m_barang->set_delete($id_pk_barang)){
                if($this->m_barang->delete()){
                    $response["msg"] = "Data is deleted from database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function is error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "ID is invalid";
        }
        echo json_encode($response);
    }
}
?>