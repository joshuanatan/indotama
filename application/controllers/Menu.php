<?php
defined("BASEPATH") or exit("No Direct Script");
class Menu extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->load->view("menu/v_master_menu");
    }
}