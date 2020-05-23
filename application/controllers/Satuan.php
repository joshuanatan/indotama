<?php
defined("BASEPATH") or exit("No Direct Script");
class Satuan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("satuan/v_master_satuan");
    }   
}