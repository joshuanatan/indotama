<?php
defined("BASEPATH") or exit("No direct script");
class Permintaan extends CI_Controller{
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
    public function index(){
        $this->load->view("brg_permintaan/v_brg_permintaan_graphic");
    }
    public function histori(){
        $this->load->view("brg_permintaan/v_brg_permintaan_histori_graphic");
    }
    public function data(){
        $this->load->model("m_barang");
        $result = $this->m_barang->list_data();
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