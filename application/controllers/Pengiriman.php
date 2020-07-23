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
        $data["excel"] = array(
            "ctrl_model" => "m_e_pengiriman_penjualan",
            "excel_title" => "Daftar Pengiriman Penjualan"
        );
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        $data["type"] = "cabang";
        $data["id_tempat_pengiriman"] = $this->session->id_cabang; 
        $data["tipe_pengiriman"] = "penjualan";
        $this->load->view("pengiriman/v_pengiriman",$data);
    }
    public function retur(){
        $data["excel"] = array(
            "ctrl_model" => "m_e_pengiriman_retur",
            "excel_title" => "Daftar Pengiriman Retur"
        );
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        
        $data["id_tempat_pengiriman"] = $this->session->id_cabang;
        $data["tipe_pengiriman"] = "retur";
        $data["type"] = "cabang";
        $this->load->view("pengiriman_retur/v_pengiriman_retur",$data);
    }
    public function permintaan(){
        $data["excel"] = array(
            "ctrl_model" => "m_e_pengiriman_permintaan",
            "excel_title" => "Daftar Pengiriman Permintaan"
        );

        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        
        $data["id_tempat_pengiriman"] = $this->session->id_cabang;
        $data["tipe_pengiriman"] = "permintaan";
        $data["type"] = "cabang";
        $this->load->view("pengiriman_permintaan/v_pengiriman_permintaan",$data);
    }
    public function permintaan_gudang(){

        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        
        $data["id_tempat_pengiriman"] = $this->session->id_warehouse;
        $data["tipe_pengiriman"] = "permintaan";
        $data["type"] = "warehouse";
        $this->load->view("pengiriman_permintaan/v_pengiriman_permintaan",$data);
    }
}