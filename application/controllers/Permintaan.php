<?php
defined("BASEPATH") or exit("No direct script");
class Permintaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("brg_permintaan/v_brg_permintaan_graphic");
    }
    public function histori(){
        $this->load->view("brg_permintaan/v_brg_permintaan_histori_graphic");
    }
    public function data(){
        $this->load->model("m_barang");
        $result = $this->m_barang->list();
        $data["barang"] = $result->result_array();
        
        $this->load->view("brg_permintaan/v_brg_permintaan",$data);
    }
    public function lain(){
        $data["id_tempat_penerimaan"] = $this->session->id_cabang;
        $data["type"] = "CABANG";
        $this->load->view("brg_pemenuhan/v_brg_pemenuhan",$data);
    }
    public function lain_gudang(){
        $data["id_tempat_penerimaan"] = $this->session->id_warehouse;
        $data["type"] = "WAREHOUSE";
        $this->load->view("brg_pemenuhan_warehouse/v_brg_pemenuhan",$data);
    }
    public function warehouse(){
        $this->load->view("brg_pemenuhan/v_brg_pemenuhan");
    }
}