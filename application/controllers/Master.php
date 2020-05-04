<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends CI_Controller {

	public function index()
	{
		$this->load->view('master_data');
	}

	public function formAddEmp()
	{
		$this->load->view('form_add_employee');
	}

	public function formViewEmp()
	{
		$this->load->view('form_view_employee');
	}

	public function formEdtEmp()
	{
		$this->load->view('form_edt_employee');
	}

}