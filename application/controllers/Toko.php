<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Toko extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}
	public function index(){
		$this->session->unset_userdata("id_toko");
		$this->session->unset_userdata("toko_nama");
        $data["menu"] = $this->get_menu()->result_array();
		$this->load->view('toko/v_master_toko',$data);
	}
	public function get_menu(){
		$this->load->model("m_user");
		$this->m_user->set_id_pk_user($this->session->id_user);
		$result = $this->m_user->menu();
		return $result;
	}
	public function cabang($id_toko){
		$this->session->unset_userdata("id_toko");
		$this->session->id_toko = $id_toko;
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		$this->session->unset_userdata("toko_nama");
		$this->session->toko_nama = $detail_toko[0]["toko_nama"];
        $data["menu"] = $this->get_menu()->result_array();
		
		$data["toko"] = $detail_toko;
		$data["id_toko"] = $id_toko;
		
		$this->load->view('cabang/v_master_toko_cabang',$data);
	}
	public function brg_cabang($id_cabang){
		$this->session->id_cabang = $id_cabang;
		$data["id_cabang"] = $id_cabang;

		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang);
		$result = $this->m_cabang->detail_by_id();
		$data["cabang"] = $result->result_array();
        $data["menu"] = $this->get_menu()->result_array();
		
		$this->load->view('brg_cabang/v_brg_cabang',$data);
	}
	public function admin($id_toko){
		$this->session->unset_userdata("id_toko");
		$this->session->id_toko = $id_toko;
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		$this->session->unset_userdata("toko_nama");
		$this->session->toko_nama = $detail_toko[0]["toko_nama"];
        $data["menu"] = $this->get_menu()->result_array();
		
		$data["toko"] = $detail_toko;
		$data["id_toko"] = $id_toko;
		
		$this->load->view('toko_admin/v_master_toko_admin',$data);
	}
	public function admin_cabang($id_cabang){
		$this->session->unset_userdata("id_cabang");
		$this->session->id_cabang = $id_cabang;
		
		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang);
		$result = $this->m_cabang->detail_by_id();
		$detail_cabang = $result->result_array();
		$this->session->unset_userdata("cabang_daerah");
		$this->session->cabang_daerah = $detail_cabang[0]["cabang_daerah"];
        $data["menu"] = $this->get_menu()->result_array();
		
		$this->load->view('cabang_admin/v_master_cabang_admin',$data);
	}
}