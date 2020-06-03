<?php
defined("BASEPATH") or exit("No Drect Script");
class Pengiriman extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function warehouse(){
        $data["type"] = "warehouse";
        $data["id_tempat_pengiriman"] = $this->session->id_warehouse; 
        $this->load->view("pengiriman/v_pengiriman",$data);
    }
    public function cabang(){
        $data["type"] = "cabang";
        $data["id_tempat_pengiriman"] = $this->session->id_cabang; 
        $this->load->view("pengiriman/v_pengiriman",$data);
    }
}