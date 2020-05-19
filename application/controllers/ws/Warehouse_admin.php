<?php
defined("BASEPATH") or exit("No direct script");
class Warehouse_admin extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_warehouse_admin");
        $columns = $this->m_warehouse_admin->columns();
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
        $id_warehouse = $this->input->get("id_warehouse");
        $this->load->model("m_warehouse_admin");
        $this->m_warehouse_admin->set_id_fk_warehouse($id_warehouse);
        $result = $this->m_warehouse_admin->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_warehouse_admin"];
                $response["content"][$a]["id_warehouse"] = $result["data"][$a]["id_fk_warehouse"];
                $response["content"][$a]["id_user"] = $result["data"][$a]["id_fk_user"];
                $response["content"][$a]["status"] = $result["data"][$a]["warehouse_admin_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["warehouse_admin_last_modified"];
                $response["content"][$a]["username"] = $result["data"][$a]["user_name"];
                $response["content"][$a]["useremail"] = $result["data"][$a]["user_email"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "username",
            "useremail",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $check = $this->input->post("check");
        if($check != ""){
            foreach($check as $a){
                $this->form_validation->set_rules("nama".$a,"nama","required");
                if($this->form_validation->run()){
                    $warehouse_nama = $this->input->post("nama".$a);
                    $this->load->model("m_user");
                    $this->m_user->set_user_name($warehouse_nama);
                    $result = $this->m_user->detail_by_name();
                    if($result->num_rows() > 0){
                        $result = $result->result_array();
                        $id_fk_user = $result[0]["id_pk_user"];
                        $id_fk_warehouse = $this->session->id_warehouse;
                        $warehouse_admin_status = "AKTIF";
            
                        $this->load->model("m_warehouse_admin");
                        if($this->m_warehouse_admin->set_insert($id_fk_warehouse,$id_fk_user,$warehouse_admin_status)){
                            if($this->m_warehouse_admin->insert()){
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
                        $response["msg"] = "User ID Not Registered";
                    }
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = validation_errors();
                }
            }
        }
        echo json_encode($response);
    }
    public function update(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id","id","required");
        $this->form_validation->set_rules("nama","nama","required");
        if($this->form_validation->run()){
            $warehouse_nama = $this->input->post("nama");
            $this->load->model("m_user");
            $this->m_user->set_user_name($warehouse_nama);
            $result = $this->m_user->detail_by_name();
            if($result->num_rows() > 0){
                $result = $result->result_array();
                $id_pk_warehouse_admin = $this->input->post("id");
                $id_fk_user = $result[0]["id_pk_user"];
    
                $this->load->model("m_warehouse_admin");
                if($this->m_warehouse_admin->set_update($id_pk_warehouse_admin,$id_fk_user)){
                    if($this->m_warehouse_admin->update()){
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
                $response["msg"] = "User ID Not Registered";
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
            $id_pk_warehouse_admin = $id;
            $this->load->model("m_warehouse_admin");
            if($this->m_warehouse_admin->set_delete($id_pk_warehouse_admin)){
                if($this->m_warehouse_admin->delete()){
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