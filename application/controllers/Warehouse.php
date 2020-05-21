<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse extends CI_Controller {

	public function index()
	{
		$this->load->view('warehouse/V_warehouse');
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
