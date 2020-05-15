<?php
defined("BASEPATH") or exit("No direct script");
class Menu extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_menu");
        $columns = $this->m_menu->columns();
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
        
        $this->load->model("m_menu");
        $result = $this->m_menu->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_menu"];
                $response["content"][$a]["controller"] = $result["data"][$a]["menu_name"];
                $response["content"][$a]["display"] = $result["data"][$a]["menu_display"];
                $response["content"][$a]["icon"] = $result["data"][$a]["menu_icon"];
                $response["content"][$a]["status"] = $result["data"][$a]["menu_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["menu_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "controller",
            "display",
            "icon",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("controller","controller","required");
        $this->form_validation->set_rules("display","display","required");
        $this->form_validation->set_rules("icon","icon","required");
        if($this->form_validation->run()){
            $this->load->model("m_menu");
            $menu_name = $this->input->post("controller");
            $menu_display = $this->input->post("display");
            $menu_icon = $this->input->post("icon");
            $menu_status = "AKTIF";
            if($this->m_menu->set_insert($menu_name,$menu_display,$menu_icon,$menu_status)){
                if($this->m_menu->insert()){
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
        $this->form_validation->set_rules("controller","controller","required");
        $this->form_validation->set_rules("display","display","required");
        $this->form_validation->set_rules("icon","icon","required");
        if($this->form_validation->run()){
            $this->load->model("m_menu");
            $id_pk_menu = $this->input->post("id");
            $menu_name = $this->input->post("controller");
            $menu_display = $this->input->post("display");
            $menu_icon = $this->input->post("icon");
            if($this->m_menu->set_update($id_pk_menu,$menu_name,$menu_display,$menu_icon)){
                if($this->m_menu->update()){
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
            $this->load->model("m_menu");
            if($this->m_menu->set_delete($id_pk_toko)){
                if($this->m_menu->delete()){
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