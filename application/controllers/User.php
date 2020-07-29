<?php
defined("BASEPATH") or exit("No Direct Script");
class User extends CI_Controller{
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
    public function index(){
        $this->load->model("m_roles");
        $data["roles"] = $this->m_roles->list()->result_array();
        $this->load->view("user/v_master_user",$data);
	}
}