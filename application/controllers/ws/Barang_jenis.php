<?php
defined("BASEPATH") or exit("No Direct Script");
class Barang_jenis extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function insert(){
        $this->form_validation->set_rules("nama","nama","required");
        if($this->form_validation->run()){
            $brg_jenis_nama = $this->input->post("nama");
            $brg_jenis_status = "AKTIF";
            $this->load->model("m_barang_jenis");
            if($this->m_barang_jenis->set_insert($brg_jenis_nama,$brg_jenis_status)){
                if($this->m_barang_jenis->insert()){
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
        $this->form_validation->set_rules("id","id","required");
        $this->form_validation->set_rules("nama","nama","required");
        if($this->form_validation->run()){
            $id_pk_brg_jenis = $this->input->post("id");
            $brg_jenis_nama = $this->input->post("nama");
            $this->load->model("m_barang_jenis");
            if($this->m_barang_jenis->set_update($id_pk_brg_jenis,$brg_jenis_nama)){
                if($this->m_barang_jenis->update()){
                    $response["msg"] = "Data is recorded to database";
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
        $id = $this->input->get("id");
        if($id != "" && is_numeric($id)){
            $this->load->model("m_barang_jenis");
            if($this->m_barang_jenis->set_delete($id)){
                if($this->m_barang_jenis->delete()){
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
}