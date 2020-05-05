<?php
defined("BASEPATH") or exit("No direct script");
class Toko extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function insert(){
        $this->form_validation->set_rules("toko_nama","toko_nama","required");
        $this->form_validation->set_rules("toko_kode","toko_kode","required");
        if($this->form_validation->run()){
            $this->load->model("m_toko");
            $toko_nama = $this->input->post("nama");
            $toko_kode = $this->input->post("kode");
            $toko_status = $this->input->post("status");
            if($this->m_toko->set_insert($toko_nama,$toko_kode,$toko_status)){
                if($this->m_toko->insert()){
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
        $this->form_validation->set_rules("kode","kode","required");
        if($this->form_validation->run()){
            $this->load->model("m_toko");
            $id_pk_toko = $this->input->post("id");
            $toko_nama = $this->input->post("nama");
            $toko_kode = $this->input->post("kode");
            $toko_status = $this->input->post("status");
            if($this->m_toko->set_update($id_pk_toko,$toko_nama,$toko_kode)){
                if($this->m_toko->update()){
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
        $id_toko = $this->input->get("id");
        if($id_toko != "" && is_numeric($id_toko)){
            $id_pk_toko = $id_toko;
            if($this->m_toko->set_delete($id_pk_toko)){
                if($this->m_toko->update()){
                    $response["msg"] = "Data is removed to database";
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
            $response["msg"] = "Invalid ID";
        }
        echo json_encode($response);
    }
}