<?php
class Penjualan extends CI_Controller{
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
        $this->load->view("penjualan/v_penjualan");
    }
    public function tambah(){
        $this->load->view("penjualan/f-add-penjualan");
    }
    public function update($id_penjualan){
        $this->load->model("m_penjualan");
        $this->m_penjualan->set_id_pk_penjualan($id_penjualan);
        $result = $this->m_penjualan->detail_by_id_pk_penjualan();
        $data["detail"] = $result->result_array();
        if(strtolower($data["detail"][0]["penj_jenis"]) == "online"){
            $this->load->model("m_penjualan_online");
            $this->m_penjualan_online->set_id_fk_penjualan($id_penjualan);
            $result = $this->m_penjualan_online->detail();
            $data["online"] = $result->result_array();
        }
        else{
            $data["online"] = false;
        }

        $this->load->model("m_brg_pindah");
        $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_penjualan);
        $this->m_brg_pindah->set_brg_pindah_sumber("penjualan");
        $result = $this->m_brg_pindah->list();
        $data["brg_custom"] = $result->result_array();

        $this->load->model("m_brg_penjualan");
        $this->m_brg_penjualan->set_id_fk_penjualan($id_penjualan);
        $result = $this->m_brg_penjualan->list();
        $data["item"] = $result->result_array();

        $this->load->model("m_tambahan_penjualan");
        $this->m_tambahan_penjualan->set_id_fk_penjualan($id_penjualan);
        $result = $this->m_tambahan_penjualan->list();
        $data["tambahan"] = $result->result_array();
        
        $this->load->model("m_penjualan_pembayaran");
        $this->m_penjualan_pembayaran->set_id_fk_penjualan($id_penjualan);
        $result = $this->m_penjualan_pembayaran->list();
        $data["pembayaran"] = $result->result_array();

        $data["id_penjualan"] = $id_penjualan;
        $this->load->view("penjualan/f-update-penjualan",$data);
    }
    public function detail($id_penjualan){
        $this->load->model("m_penjualan");
        $this->m_penjualan->set_id_pk_penjualan($id_penjualan);
        $result = $this->m_penjualan->detail_by_id_pk_penjualan();
        $data["detail"] = $result->result_array();
        if(strtolower($data["detail"][0]["penj_jenis"]) == "online"){
            $this->load->model("m_penjualan_online");
            $this->m_penjualan_online->set_id_fk_penjualan($id_penjualan);
            $result = $this->m_penjualan_online->detail();
            $data["online"] = $result->result_array();
        }
        else{
            $data["online"] = false;
        }

        $this->load->model("m_brg_pindah");
        $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_penjualan);
        $this->m_brg_pindah->set_brg_pindah_sumber("penjualan");
        $result = $this->m_brg_pindah->list();
        $data["brg_custom"] = $result->result_array();

        $this->load->model("m_brg_penjualan");
        $this->m_brg_penjualan->set_id_fk_penjualan($id_penjualan);
        $result = $this->m_brg_penjualan->list();
        $data["item"] = $result->result_array();

        $this->load->model("m_tambahan_penjualan");
        $this->m_tambahan_penjualan->set_id_fk_penjualan($id_penjualan);
        $result = $this->m_tambahan_penjualan->list();
        $data["tambahan"] = $result->result_array();
        
        $this->load->model("m_penjualan_pembayaran");
        $this->m_penjualan_pembayaran->set_id_fk_penjualan($id_penjualan);
        $result = $this->m_penjualan_pembayaran->list();
        $data["pembayaran"] = $result->result_array();

        $data["id_penjualan"] = $id_penjualan;
        $this->load->view("penjualan/f-detail-penjualan",$data);
    }
}