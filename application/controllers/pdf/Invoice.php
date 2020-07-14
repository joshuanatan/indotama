<?php
defined("BASEPATH") or exit("No direct script");
class Invoice extends CI_Controller{
    public function __construct(){
        parent::__construct();

        $this->load->library('Pdf_oc');
    }
    public function index(){
       
        $this->load->view('pdf/pdf_invoice');
    }
}