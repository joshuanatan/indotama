<?php
defined("BASEPATH") or exit("No direct script");
class Excel extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function pengiriman_penjualan_cabang(){
        $this->load->model("m_pengiriman");
        $this->m_pengiriman->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_pengiriman->list_pengiriman_penjualan();
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Pengiriman Penjualan";

        $columns = $this->m_pengiriman->columns("penjualan");
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=pengiriman_cabang.xls");
        echo $response["response"];
    }

    public function pengiriman_retur_cabang(){
        $this->load->model("m_pengiriman");
        $this->m_pengiriman->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_pengiriman->list_pengiriman_retur();
        
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Pengiriman Retur";

        $columns = $this->m_pengiriman->columns("retur");
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=pengiriman_cabang.xls");
        echo $response["response"];
    }
    public function pengiriman_permintaan_cabang(){
        $this->load->model("m_t_pengiriman_permintaan");
        $result = $this->m_t_pengiriman_permintaan->list_pengiriman_permintaan();
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Pengiriman Permintaan";

        $columns = $this->m_t_pengiriman_permintaan->columns();
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=pengiriman_cabang.xls");
        echo $response["response"];
    }
    public function get(){
        $ctrl_model = $this->input->get("ctrl_model");
        $title = $this->input->get("title");
        $namafile = str_replace(" ","_",strtolower($title));
        $namafile .= "_".str_replace("-","",strval(date("d-m-Y")));

        $this->load->model($ctrl_model,"control_model");
        $result = $this->control_model->data_excel();
        $data["data"] = $result->result_array();
        $data["title"] = $title;

        $columns = $this->control_model->columns_excel();
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        
        
        $response = $this->load->view("_plugin_template/excel/excel",$data,true);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=".$namafile.".xls");
        echo $response;
    }
    public function generate(){
        $title = $this->input->post("title");
        $header = $this->input->post("header");
        $data = $this->input->post("data");
        $filename = $this->input->post("filename");
        $access_key = $this->input->post("access_key");
        $data = array(
            "filename" => $filename,
            "title" => $title,
            "header" => $header,
            "data" => $data,
            "access_key" => $access_key
        );
        $this->load->view("_plugin_template/excel/excel",$data);
    }
}