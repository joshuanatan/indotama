<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends CI_Controller {

	public function index(){
        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('roles/v_master_roles',$data);
	}
	public function get_menu(){
		$this->load->model("m_warehouse_admin");
		$this->m_warehouse_admin->set_id_fk_user($this->session->id_user);
		$result = $this->m_warehouse_admin->list_gudang_admin();
		if($result["data"]->num_rows() > 0){
			if($result["data"]->num_rows() > 1){
				$this->session->multiple_warehouse_access = true;
			}
			else{
				$result = $result["data"]->result_array();
				$this->session->id_warehouse = $result[0]["id_pk_warehouse"];
				$this->session->nama_warehouse = $result[0]["warehouse_nama"];
			}
		}

		$this->load->model("m_toko_admin");
		$this->m_toko_admin->set_id_fk_user($this->session->id_user);
		$result = $this->m_toko_admin->list_toko_admin();
		if($result["data"]->num_rows() > 0){
			if($result["data"]->num_rows() > 1){
				$this->session->multiple_toko_access = true;
			}
			else{
				$result = $result["data"]->result_array();
				$this->session->id_toko = $result[0]["id_pk_toko"];
				$this->session->nama_toko = $result[0]["toko_nama"];
			}
		}

		$this->load->model("m_cabang_admin");
		$this->m_cabang_admin->set_id_fk_user($this->session->id_user);
		$result = $this->m_cabang_admin->list_cabang_admin();
		if($result["data"]->num_rows() > 0){
			if($result["data"]->num_rows() > 1){
				$this->session->multiple_cabang_access = true;
			}
			else{
				$result = $result["data"]->result_array();
				$this->session->id_cabang = $result[0]["id_pk_cabang"];
				$this->session->daerah_cabang = $result[0]["cabang_daerah"];
				$this->session->nama_toko = $result[0]["toko_nama"];
			}
		}
		$this->load->model("m_user");
		$this->m_user->set_id_pk_user($this->session->id_user);
		$result = $this->m_user->menu();
		return $result;
	}
    
}
