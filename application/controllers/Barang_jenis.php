<?php
defined("BASEPATH") or exit("No Direct Script");
class Barang_jenis extends CI_Controller{
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
        $this->load->view("barang_jenis/v_master_barang_jenis");
    }   
}