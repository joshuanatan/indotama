<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Toko extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	public function index(){
		$this->load->view('v_master_toko');
	}
	public function cabang($id_toko){
		$data["id_toko"] = $id_toko;
		$this->load->view('v_master_toko_cabang',$data);
	}

}