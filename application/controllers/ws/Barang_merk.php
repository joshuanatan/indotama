<?php
defined("BASEPATH") or exit("No Direct Script");
class Barang_merk extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_barang_merk");
        $columns = $this->m_barang_merk->columns();
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
        
        $this->load->model("m_barang_merk");
        $result = $this->m_barang_merk->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg_merk"];
                $response["content"][$a]["nama"] = $result["data"][$a]["brg_merk_nama"];
                $response["content"][$a]["status"] = $result["data"][$a]["brg_merk_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_merk_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nama",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function list_data(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_barang_merk");
        $result = $this->m_barang_merk->list_data();
        if($result->num_rows()){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_merk"];
                $response["content"][$a]["nama"] = $result[$a]["brg_merk_nama"];
                $response["content"][$a]["status"] = $result[$a]["brg_merk_status"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_merk_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No data is recorded in database";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("nama","nama","required");
        if($this->form_validation->run()){
            $brg_merk_nama = $this->input->post("nama");
            $brg_merk_status = "AKTIF";
            $this->load->model("m_barang_merk");
            if($this->m_barang_merk->set_insert($brg_merk_nama,$brg_merk_status)){
                if($this->m_barang_merk->insert()){
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
        if($this->form_validation->run()){
            $id_pk_brg_merk = $this->input->post("id");
            $brg_merk_nama = $this->input->post("nama");
            $this->load->model("m_barang_merk");
            if($this->m_barang_merk->set_update($id_pk_brg_merk,$brg_merk_nama)){
                if($this->m_barang_merk->update()){
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
            $this->load->model("m_barang_merk");
            if($this->m_barang_merk->set_delete($id)){
                if($this->m_barang_merk->delete()){
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