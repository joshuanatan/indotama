<?php
defined("BASEPATH") or exit("No direct script");
class Barang extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("barang/v_master_barang");
    }
    public function katalog(){
        $this->load->view("barang/v_master_barang_katalog");
    }
}