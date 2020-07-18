<?php
defined("BASEPATH") or exit("No direct script");
class Barang extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("barang/v_master_barang");
    }
    public function katalog(){
        $this->load->view("barang/v_master_barang_katalog");
    }
    public function excel(){
        $this->load->model("m_barang");
        $result = $this->m_barang->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_brg","brg_kode","brg_nama","brg_ket","brg_minimal","brg_status","brg_satuan","brg_merk_nama","brg_jenis_nama","brg_harga"
        );
        $data["title"] = "Daftar barang";
        $data["header"] = array(
            "ID","Kode Barang","Nama Barang","Keterangan","Jumlah Minimal","Status","Satuan","Nama merk","Nama Tipe","Harga Barang"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=master_barang.xls");
        echo $response["response"];
    }
}