<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function index()
	{
		$this->load->model("m_user");
		$this->m_user->set_id_pk_user($this->session->id_user);
		$result = $this->m_user->menu();
		$data = array(
			"menu" => $result->result_array()
		);
		$this->load->view('welcome_message',$data);
    }
    
}