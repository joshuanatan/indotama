<?php
defined("BASEPATH") or exit("No direct script");
class Penawaran extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("penawaran/v_master_penawaran");
    }
}