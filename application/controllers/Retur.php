<?php
defined("BASEPATH") or exit("No direct script");
class Retur extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("retur/v_retur");
    }
}