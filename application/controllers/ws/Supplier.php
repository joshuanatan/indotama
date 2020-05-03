<?php
defined("BASEPATH") or exit("No direct script");
class Supplier extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function insert(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("perusahaan","perusahaan","required");
        $this->form_validation->set_rules("email","email","required");
        $this->form_validation->set_rules("telp","telp","required");
        $this->form_validation->set_rules("hp","hp","required");
        $this->form_validation->set_rules("alamat","alamat","required");
        $this->form_validation->set_rules("keterangan","keterangan","required");
        $this->form_validation->set_rules("id_toko","id_toko","required");

        if($this->form_validation->run()){
            $sup_nama = $this->input->post("nama");
            $sup_perusahaan = $this->input->post("perusahaan");
            $sup_email = $this->input->post("email");
            $sup_telp = $this->input->post("telp");
            $sup_hp = $this->input->post("hp");
            $sup_alamat = $this->input->post("alamat");
            $sup_keterangan = $this->input->post("keterangan");
            $sup_status = "AKTIF";
            $id_fk_toko = $this->input->post("id_toko");

            $this->load->model("m_supplier");
            if($this->m_supplier->set_insert($sup_nama,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan,$sup_status,$id_fk_toko)){
                if($this->m_supplier->insert()){
                    $response["msg"] = "Data is recorded to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Insert function error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function error";
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
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("perusahaan","perusahaan","required");
        $this->form_validation->set_rules("email","email","required");
        $this->form_validation->set_rules("telp","telp","required");
        $this->form_validation->set_rules("hp","hp","required");
        $this->form_validation->set_rules("alamat","alamat","required");
        $this->form_validation->set_rules("keterangan","keterangan","required");
        $this->form_validation->set_rules("id_toko","id_toko","required");

        if($this->form_validation->run()){
            $id_pk_sup = $this->input->post("id");
            $sup_nama = $this->input->post("nama");
            $sup_perusahaan = $this->input->post("perusahaan");
            $sup_email = $this->input->post("email");
            $sup_telp = $this->input->post("telp");
            $sup_hp = $this->input->post("hp");
            $sup_alamat = $this->input->post("alamat");
            $sup_keterangan = $this->input->post("keterangan");
            $id_fk_toko = $this->input->post("id_toko");

            $this->load->model("m_supplier");
            if($this->m_supplier->set_update($id_pk_sup,$sup_nama,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan,$id_fk_toko)){
                if($this->m_supplier->update()){
                    $response["msg"] = "Data is updated to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function error";
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
        $id_supplier = $this->input->get("id_supplier");
        if($id_supplier != "" && is_numeric($id_supplier)){
            $this->load->model("m_supplier");
            if($this->m_supplier->set_delete($id_supplier)){
                if($this->m_supplier->delete()){
                    $response["msg"] = "Data is deleted from database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Delete function error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid ID Supplier";
        }
        echo json_encode($response);
    }
    public function list(){}
}