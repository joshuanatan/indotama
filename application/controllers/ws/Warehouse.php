<?php
defined("BASEPATH") or exit("No direct script");
class Warehouse extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_warehouse");
        $columns = $this->m_warehouse->columns();
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
        
        $this->load->model("m_warehouse");
        $result = $this->m_warehouse->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_warehouse"];
                $response["content"][$a]["nama"] = $result["data"][$a]["warehouse_nama"];
                $response["content"][$a]["alamat"] = $result["data"][$a]["warehouse_alamat"];
                $response["content"][$a]["notelp"] = $result["data"][$a]["warehouse_notelp"];
                $response["content"][$a]["desc"] = $result["data"][$a]["warehouse_desc"];
                $response["content"][$a]["status"] = $result["data"][$a]["warehouse_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["warehouse_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nama",
            "alamat",
            "notelp",
            "desc",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("warehouse_nama","Nama Warehouse","required");
        $this->form_validation->set_rules("warehouse_alamat","Alamat","required");
        $this->form_validation->set_rules("warehouse_notelp","No Telepon","required");
        $this->form_validation->set_rules("warehouse_desc","Deskripsi","required");
        if($this->form_validation->run()){
            $this->load->model("m_warehouse");
            $warehouse_nama = $this->input->post("warehouse_nama");
            $warehouse_alamat = $this->input->post("warehouse_alamat");
            $warehouse_notelp = $this->input->post("warehouse_notelp");
            $warehouse_desc = $this->input->post("warehouse_desc");
            $warehouse_status = "AKTIF";
            if($this->m_warehouse->set_insert($warehouse_nama,$warehouse_alamat,$warehouse_notelp,$warehouse_desc,$warehouse_status)){
                if($this->m_warehouse->insert()){
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
		$this->form_validation->set_rules("warehouse_nama","Nama Warehouse","required");
        $this->form_validation->set_rules("warehouse_alamat","Alamat","required");
        $this->form_validation->set_rules("warehouse_notelp","No Telepon","required");
        $this->form_validation->set_rules("warehouse_desc","Deskripsi","required");
        if($this->form_validation->run()){
            $this->load->model("m_warehouse");
            $id_pk_warehouse = $this->input->post("id");
            $warehouse_nama = $this->input->post("warehouse_nama");
            $warehouse_alamat = $this->input->post("warehouse_alamat");
            $warehouse_notelp = $this->input->post("warehouse_notelp");
            $warehouse_desc = $this->input->post("warehouse_desc");
            if($this->m_warehouse->set_update($id_pk_warehouse,$warehouse_nama,$warehouse_alamat,$warehouse_notelp,$warehouse_desc)){
                if($this->m_warehouse->update()){
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
        $id_warehouse = $this->input->get("id");
        if($id_warehouse != "" && is_numeric($id_warehouse)){
            $id_pk_warehouse = $id_warehouse;
            $this->load->model("m_warehouse");
            if($this->m_warehouse->set_delete($id_pk_warehouse)){
                if($this->m_warehouse->delete()){
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