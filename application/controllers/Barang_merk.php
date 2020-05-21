<?php
defined("BASEPATH") or exit("No Direct Script");
class Barang_merk extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("barang_merk/v_master_barang_merk");
    }   
}