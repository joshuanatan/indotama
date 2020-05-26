<?php
defined("BASEPATH") or exit("No direct script");
class Pemenuhan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function gudang(){
        $this->load->model("m_brg_pemenuhan");
        $data["id_tempat_penerimaan"] = $this->session->id_warehouse;
        $data["type"] = "WAREHOUSE";
        $this->m_brg_pemenuhan->set_id_fk_warehouse($this->session->id_warehouse);
        $this->load->view("brg_pemenuhan/v_brg_pemenuhan",$data);
    }
    public function cabang(){
        $this->load->model("m_brg_pemenuhan");
        $data["id_tempat_penerimaan"] = $this->session->id_warehouse;
        $data["type"] = "CABANG";
        $this->m_brg_pemenuhan->set_id_fk_warehouse($this->session->id_cabang);
        $this->load->view("brg_pemenuhan/v_brg_pemenuhan",$data);
    }
}