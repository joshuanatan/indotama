<?php
defined("BASEPATH") or exit("No direct script");
class Marketplace extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_marketplace");
        $columns = $this->m_marketplace->columns();
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
        
        $this->load->model("m_marketplace");
        $result = $this->m_marketplace->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_marketplace"];
                $response["content"][$a]["nama"] = $result["data"][$a]["marketplace_nama"];
                $response["content"][$a]["ket"] = $result["data"][$a]["marketplace_ket"];
                $response["content"][$a]["status"] = $result["data"][$a]["marketplace_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["marketplace_last_modified"];
                $response["content"][$a]["biaya"] = $result["data"][$a]["marketplace_biaya"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nama",
            "ket",
            "biaya",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_marketplace");
        $result = $this->m_marketplace->list();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_marketplace"];
                $response["content"][$a]["nama"] = $result["data"][$a]["marketplace_nama"];
                $response["content"][$a]["ket"] = $result["data"][$a]["marketplace_ket"];
                $response["content"][$a]["status"] = $result["data"][$a]["marketplace_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["marketplace_last_modified"];
                $response["content"][$a]["biaya"] = $result["data"][$a]["marketplace_biaya"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Marketplace List";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("keterangan","ket","required");
        $this->form_validation->set_rules("biaya","biaya","required");
        
        if($this->form_validation->run()){
            $this->load->model("m_marketplace");
            $marketplace_nama = $this->input->post("nama");
            $marketplace_ket = $this->input->post("keterangan");
            $marketplace_biaya = $this->input->post("biaya");
            $marketplace_status = "AKTIF";
            
            if($this->m_marketplace->set_insert($marketplace_nama,$marketplace_ket,$marketplace_status,$marketplace_biaya)){
                $id_marketplace = $this->m_marketplace->insert();
                if($id_marketplace){
                    $response["msg"] = "Data is recorded to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Insert function is error";
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
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("keterangan","ket","required");
        $this->form_validation->set_rules("biaya","biaya","required");
        
        if($this->form_validation->run()){
            $this->load->model("m_marketplace");
            $id_pk_marketplace = $this->input->post("id");
            $marketplace_nama = $this->input->post("nama");
            $marketplace_ket = $this->input->post("keterangan");
            $marketplace_biaya = $this->input->post("biaya");

            if($this->m_marketplace->set_update($id_pk_marketplace,$marketplace_nama,$marketplace_ket,$marketplace_biaya)){
                if($this->m_marketplace->update()){
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
        $id_pk_marketplace = $this->input->get("id");
        if($id_pk_marketplace != "" && is_numeric($id_pk_marketplace)){
            $this->load->model("m_marketplace");
            if($this->m_marketplace->set_delete($id_pk_marketplace)){
                if($this->m_marketplace->delete()){
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