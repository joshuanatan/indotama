<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Toko extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	public function index(){
		$this->load->view('toko/v_master_toko');
	}
	public function cabang($id_toko){
		$data["id_toko"] = $id_toko;
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko);
		$result = $this->m_toko->detail_by_id();
		$data["toko"] = $result->result_array();
		
		$this->load->view('cabang/v_master_toko_cabang',$data);
	}

}