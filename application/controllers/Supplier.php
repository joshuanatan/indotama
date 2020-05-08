<?php
defined("BASEPATH") or exit("No Direct Script");
class Supplier extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("v_supplier");
    }
}