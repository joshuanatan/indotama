<?php
class Penjualan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("penjualan/v_penjualan");
    }
    public function tambah(){
        $this->load->view("penjualan/f-add-penjualan");
    }
}