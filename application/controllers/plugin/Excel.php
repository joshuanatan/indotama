<?php
defined("BASEPATH") or exit("No direct script");
class Excel extends CI_Controller{
    public function __construct(){
        parent::__construct();
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