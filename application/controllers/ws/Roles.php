<?php
defined("BASEPATH") or exit("No direct script");
class Roles extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_roles");
        $columns = $this->m_roles->columns();
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
        
        $this->load->model("m_roles");
        $result = $this->m_roles->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_jabatan"];
                $response["content"][$a]["nama"] = $result["data"][$a]["jabatan_nama"];
                $response["content"][$a]["status"] = $result["data"][$a]["jabatan_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["jabatan_last_modified"];
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
    public function register(){
        $response["status"] = "SUCCESS";
		$this->form_validation->set_rules("jabatan_nama","Nama Jabatan","required");

		if($this->form_validation->run()){
            $this->load->model("m_roles");
            $jabatan_nama = $this->input->post("jabatan_nama");
            $jabatan_status = "AKTIF";

			if($this->m_roles->set_insert($jabatan_nama,$jabatan_status)){
                $id_jabatan = $this->m_roles->insert();
				if($id_jabatan){
                    $response["msg"] = "Data is recorded to database";
                    $checks = $this->input->post("check");
                    if($checks != ""){
                        foreach($checks as $a){
                            $this->load->model("m_hak_akses");
                            $this->m_hak_akses->set_id_fk_jabatan($id_jabatan);
                            $this->m_hak_akses->set_id_fk_menu($a);
                            $this->m_hak_akses->activate_hak_akses();
                        }
                    }
                }
                else{
					$response["status"] = "ERROR";
                    $response["msg"] = "Insert function is error";
				}
			}else{
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
		$this->form_validation->set_rules("id","ID Jabatan","required");
		$this->form_validation->set_rules("jabatan_nama","Nama Jabatan","required");

		if($this->form_validation->run()){
            $this->load->model("m_roles");
            $id_pk_jabatan = $this->input->post("id");
            $jabatan_nama = $this->input->post("jabatan_nama");

			if($this->m_roles->set_update($id_pk_jabatan,$jabatan_nama)){
				if($this->m_roles->update()){
                    $response["msg"] = "Data is updated";
                    $checks = $this->input->post("check");
                    if($checks != ""){
                        $this->load->model("m_hak_akses");
                        $this->m_hak_akses->set_id_fk_jabatan($id_pk_jabatan);
                        $this->m_hak_akses->reset_hak_akses();
                        foreach($checks as $a){
                            $this->load->model("m_hak_akses");
                            $this->m_hak_akses->set_id_fk_jabatan($id_pk_jabatan);
                            $this->m_hak_akses->set_id_fk_menu($a);
                            $this->m_hak_akses->activate_hak_akses();
                        }
                    }
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
        $id = $this->input->get("id");
        if($id != "" && is_numeric($id)){
            $this->load->model("m_roles");
            if($this->m_roles->set_delete($id)){
				if($this->m_roles->delete()){
					$response["msg"] = "Data is deleted";
                }
                else{
					$response["status"] = "ERROR";
                    $response["msg"] = "Delete function is error";
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
            $this->session->set_flashdata("msg",$response['msg']);
		}
        echo json_encode($response);
    }
    public function hak_akses(){
        $response["status"] = "SUCCESS";
        $id_role = $this->input->get("id");
        if($id_role != "" && is_numeric($id_role)){
            $this->load->model("m_hak_akses");
            $this->m_hak_akses->set_id_fk_jabatan($id_role);
            $result = $this->m_hak_akses->list_role_hak_akses();
            if($result->num_rows() > 0){
                $result = $result->result_array();
                for($a = 0; $a<count($result); $a++){
                    $response["content"][$a]["id_ha"] = $result[$a]["id_pk_hak_akses"];
                    $response["content"][$a]["id_jabatan"] = $result[$a]["id_fk_jabatan"];
                    $response["content"][$a]["id_menu"] = $result[$a]["id_fk_menu"];
                    $response["content"][$a]["status"] = $result[$a]["hak_akses_status"];
                    $response["content"][$a]["last_modified"] = $result[$a]["hak_akses_last_modified"];
                    $response["content"][$a]["menu_name"] = $result[$a]["menu_name"];
                    $response["content"][$a]["menu_display"] = $result[$a]["menu_display"];
                    $response["content"][$a]["menu_icon"] = $result[$a]["menu_icon"];
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "No data";
            }
        }
        else{
            $response["status"] == "ERROR";
            $response["msg"] = "Invalid ID";
        }
        echo json_encode($response);
    }
}