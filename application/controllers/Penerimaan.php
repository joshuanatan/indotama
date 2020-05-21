<?php
defined("BASEPATH") or exit("No direct script");
class Penerimaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("penerimaan/v_penerimaan");
    }
}