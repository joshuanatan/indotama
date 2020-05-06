<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Toko extends CI_Controller {

	public function index()
	{
		$this->load->view('v_master_toko');
	}

	public function formAddEmp()
	{
		$this->load->view('V_form_add_employee');
	}

	public function formViewEmp()
	{
		$this->load->view('V_form_view_employee');
	}

	public function formEdtEmp()
	{
		$this->load->view('V_form_edt_employee');
	}

}