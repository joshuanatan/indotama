<?php
defined("BASEPATH") or exit("No direct script");
class Marketplace extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("marketplace/v_master_marketplace");
    }
}