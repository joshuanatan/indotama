<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends CI_Controller {
	public function __construct(){
		parent::__construct();
        $this->check_session();
    }
    public function check_session(){
        if($this->session->id_user == ""){
            $this->session->set_flashdata("msg","Session expired, please login");
            redirect("login");
        }
    }
	public function index()
	{
		$this->load->view('customer/v_customer');
	}

    public function toko()
	{
        $id_toko = $this->session->id_toko;
        $where = array(
            "id_pk_toko"=>$id_toko
        );
        $data['toko'] = selectRow("mstr_toko",$where)->result_array();
		$this->load->view('customer/v_customer_toko',$data);
	}
    
}
