<?php
defined("BASEPATH") or exit("No direct script");
class Log extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("log/v_master_log");
    }
}