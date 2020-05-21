<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Toko extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}
	public function index(){
		$this->session->unset_userdata("id_toko_page");
		$this->session->unset_userdata("toko_nama_page");
        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('toko/v_master_toko',$data);
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
	public function cabang($id_toko_page){
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko_page);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		$data["toko"] = $detail_toko;
		$data["id_toko_page"] = $id_toko_page;

        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('cabang/v_master_toko_cabang',$data);
	}
	public function daftar_akses_cabang(){
        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('cabang/v_list_cabang_admin',$data);
	}
	public function admin_cabang($id_cabang_page){

		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang_page);
		$result = $this->m_cabang->detail_by_id();
		$detail_cabang = $result->result_array();
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($detail_cabang[0]["id_fk_toko"]);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		
		$data["toko"] = $detail_toko;
		$data["id_cabang"] = $id_cabang_page;
		$data["cabang"] = $detail_cabang;
		$data["menu"] = $this->get_menu()->result_array();
		
		$this->load->view('cabang_admin/v_master_cabang_admin',$data);
	}
	public function brg_cabang($id_cabang_page){
		$data["id_cabang_page"] = $id_cabang_page;

		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang_page);
		$result = $this->m_cabang->detail_by_id();
		$data["cabang"] = $result->result_array();
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($data["cabang"][0]["id_fk_toko"]);
		$result = $this->m_toko->detail_by_id();
		$data["toko"] = $result->result_array();

        $data["menu"] = $this->get_menu()->result_array();
		
		$this->load->view('brg_cabang/v_brg_cabang',$data);
	}
	public function admin($id_toko_page){
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko_page);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();

		$data["toko"] = $detail_toko;
        $data["menu"] = $this->get_menu()->result_array();
		$data["id_toko_page"] = $id_toko_page;
		
		$this->load->view('toko_admin/v_master_toko_admin',$data);
	}
	public function daftar_akses_toko(){
        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('toko/v_list_toko_admin',$data);
	}
}