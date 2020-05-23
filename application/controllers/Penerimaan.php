<?php
defined("BASEPATH") or exit("No direct script");
class Penerimaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function gudang(){
        $data["type"] = "WAREHOUSE";
        $this->load->view("penerimaan/v_penerimaan",$data);
    }
    public function cabang(){
        $this->load->model("m_satuan");
        $result = $this->m_satuan->list();
        $data["satuan"] = $result->result_array();
        $data["type"] = "CABANG";
        $this->load->view("penerimaan/v_penerimaan",$data);
    }
}