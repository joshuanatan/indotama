<?php
defined("BASEPATH") or exit("No direct script");
class Penerimaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->check_session();
    }
    public function check_session(){
        if($this->session->id_user == ""){
            $this->session->set_flashdata("msg","Session expired, please login");
            redirect("login");
        }
    }
    public function gudang(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        $data["id_tempat_penerimaan"] = $this->session->id_warehouse;
        $data["type"] = "WAREHOUSE";
        
        $this->load->view("penerimaan/v_penerimaan_warehouse",$data);
    }
    public function cabang(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        $data["id_tempat_penerimaan"] = $this->session->id_cabang;
        $data["type"] = "CABANG";

        $data["tipe_penerimaan"] = "pembelian";
        $this->load->view("penerimaan/v_penerimaan",$data);
    }
    public function pembelian(){
        redirect("penerimaan/cabang");
    } 
    public function retur(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        
        $data["id_tempat_penerimaan"] = $this->session->id_cabang;
        $data["tipe_penerimaan"] = "retur";
        $data["type"] = "CABANG";

        $this->load->model("m_pembelian");
        $this->load->view("penerimaan_retur/v_penerimaan_retur",$data);
    }
    public function permintaan(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        
        $data["id_tempat_penerimaan"] = $this->session->id_cabang;
        $data["tipe_penerimaan"] = "permintaan";
        $data["type"] = "CABANG";

        $this->load->model("m_pembelian");
        $this->load->view("penerimaan_permintaan/v_penerimaan_permintaan",$data);
    }
}