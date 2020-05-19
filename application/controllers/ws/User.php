<?php
defined("BASEPATH") or exit("No direct script");
class User extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_user");
        $columns = $this->m_user->columns();
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
        
        $this->load->model("m_user");
        $result = $this->m_user->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_user"];
                $response["content"][$a]["name"] = $result["data"][$a]["user_name"];
                $response["content"][$a]["email"] = $result["data"][$a]["user_email"];
                $response["content"][$a]["status"] = $result["data"][$a]["user_status"];
                $response["content"][$a]["id_role"] = $result["data"][$a]["id_fk_role"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["user_last_modified"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["user_create_date"];
                $response["content"][$a]["jabatan"] = $result["data"][$a]["jabatan_nama"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "name",
            "email",
            "jabatan",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("name","name","required");
        $this->form_validation->set_rules("pass","pass","required");
        $this->form_validation->set_rules("email","email","required");
        $this->form_validation->set_rules("id_role","id_role","required");
        if($this->form_validation->run()){
            $this->load->model("m_user");
            $user_name = $this->input->post("name");
            $user_pass = $this->input->post("pass");
            $user_email = $this->input->post("email");
            $user_status = "AKTIF";
            $id_fk_role = $this->input->post("id_role");
            
            if($this->m_user->set_insert($user_name,$user_pass,$user_email,$user_status,$id_fk_role)){
                if($this->m_user->insert()){
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
        $this->form_validation->set_rules("name","name","required");
        $this->form_validation->set_rules("email","email","required");
        $this->form_validation->set_rules("id_role","id_role","required");
        if($this->form_validation->run()){
            $this->load->model("m_user");
            $id_pk_user = $this->input->post("id");
            $user_name = $this->input->post("name");
            $user_email = $this->input->post("email");
            $id_fk_role = $this->input->post("id_role");
            if($this->m_user->set_update($id_pk_user,$user_name,$user_email,$id_fk_role)){
                if($this->m_user->update()){
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
        $id_user = $this->input->get("id");
        if($id_user != "" && is_numeric($id_user)){
            $id_user = $id_user;
            $this->load->model("m_user");
            if($this->m_user->set_delete($id_user)){
                if($this->m_user->delete()){
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
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_user");
        $result = $this->m_user->list();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_user"];
                $response["content"][$a]["name"] = $result[$a]["user_name"];
                $response["content"][$a]["email"] = $result[$a]["user_email"];
                $response["content"][$a]["status"] = $result[$a]["user_status"];
                $response["content"][$a]["id_role"] = $result[$a]["id_fk_role"];
                $response["content"][$a]["last_modified"] = $result[$a]["user_last_modified"];
                $response["content"][$a]["create_date"] = $result[$a]["user_create_date"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No User List";
        }
        echo json_encode($response);
    }
}