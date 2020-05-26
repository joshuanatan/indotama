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
        $data["pembelian"] = $this->m_pembelian->list()->result_array();
        $this->load->view("penerimaan/v_penerimaan",$data);
    }
    public function cabang(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        $data["id_tempat_penerimaan"] = $this->session->id_cabang;
        $data["type"] = "CABANG";

        $this->load->model("m_pembelian");
        $this->m_pembelian->set_id_fk_cabang($this->session->id_cabang);
        $data["pembelian"] = $this->m_pembelian->list()->result_array();
        $this->load->view("penerimaan/v_penerimaan",$data);
    }
}