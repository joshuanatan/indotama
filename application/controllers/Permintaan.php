<?php
defined("BASEPATH") or exit("No direct script");
class Permintaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->model("m_barang");
        $result = $this->m_barang->list();
        $data["barang"] = $result->result_array();
        
        $this->load->model("m_brg_permintaan");
        $this->m_brg_permintaan->set_id_fk_cabang($this->session->id_cabang);
        $this->load->view("brg_permintaan/v_brg_permintaan",$data);
    }
}