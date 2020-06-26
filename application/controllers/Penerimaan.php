<?php
defined("BASEPATH") or exit("No direct script");
class Penerimaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function gudang(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        $data["id_tempat_penerimaan"] = $this->session->id_warehouse;
        $data["type"] = "WAREHOUSE";
        
        $this->load->model("m_pembelian");
        $this->load->view("penerimaan/v_penerimaan",$data);
    }
    public function cabang(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        $data["id_tempat_penerimaan"] = $this->session->id_cabang;
        $data["type"] = "CABANG";

        $this->load->model("m_pembelian");
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
        $this->load->view("penerimaan_retur/v_penerimaan_retur",$data);
    }
}