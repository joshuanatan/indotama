<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse extends CI_Controller {

	public function index()
	{
        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('warehouse/V_warehouse',$data);
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
	public function admin($id_warehouse){
		$this->session->unset_userdata("id_warehouse");
		$this->session->id_warehouse = $id_warehouse;
		
		$this->load->model("m_warehouse");
		$this->m_warehouse->set_id_pk_warehouse($id_warehouse);
		$result = $this->m_warehouse->detail_by_id();
		$detail_warehouse = $result->result_array();
		$this->session->unset_userdata("warehouse_nama");
		$this->session->warehouse_nama = $detail_warehouse[0]["warehouse_nama"];
        $data["menu"] = $this->get_menu()->result_array();
		
		$this->load->view('warehouse_admin/v_master_warehouse_admin',$data);
	}
	public function daftar_akses_gudang(){
        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('warehouse/v_list_warehouse_admin',$data);
	}
}
