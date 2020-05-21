<?php
defined("BASEPATH") or exit("No Direct Script");
class Pembelian extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("pembelian/v_pembelian");
    }
}