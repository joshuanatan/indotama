<?php
defined("BASEPATH") or exit("No direct script");
class Toko extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_toko");
        $columns = $this->m_toko->columns();
        if(count($columns) > 0){
            for($a = 0; $a<count($columns); $a++){
                $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        echo json_encode($response);
    }
    public function content(){
        $response["status"] = "SUCCESS";
        $response["content"] = array();

        $order_by = $this->input->get("orderBy");
        $order_direction = $this->input->get("orderDirection");
        $page = $this->input->get("page");
        $search_key = $this->input->get("searchKey");
        $data_per_page = 20;
        
        $this->load->model("m_toko");
        $result = $this->m_toko->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_toko"];
                $response["content"][$a]["nama"] = $result["data"][$a]["toko_nama"];
                $response["content"][$a]["kode"] = $result["data"][$a]["toko_kode"];
                $response["content"][$a]["status"] = $result["data"][$a]["toko_status"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["toko_create_date"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["toko_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nama",
            "kode",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("nama","toko_nama","required");
        $this->form_validation->set_rules("kode","toko_kode","required");
        if($this->form_validation->run()){
            $this->load->model("m_toko");
            $toko_nama = $this->input->post("nama");
            $toko_kode = $this->input->post("kode");
            $toko_status = "AKTIF";
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
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id","id","required");
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("kode","kode","required");
        if($this->form_validation->run()){
            $this->load->model("m_toko");
            $id_pk_toko = $this->input->post("id");
            $toko_nama = $this->input->post("nama");
            $toko_kode = $this->input->post("kode");
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
        $response["status"] = "SUCCESS";
        $id_toko = $this->input->get("id");
        if($id_toko != "" && is_numeric($id_toko)){
            $id_pk_toko = $id_toko;
            $this->load->model("m_toko");
            if($this->m_toko->set_delete($id_pk_toko)){
                if($this->m_toko->delete()){
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