<?php
class Page_generator{
    private $CI;
    public function __construct(){
        $this->CI =& get_instance();
    }
    public function req(){
        $this->CI->load->view("req/head");
    }
    public function head_close(){
        $this->CI->load->view("req/head-close");
        $this->CI->load->view("req/body-open");
    }
    public function navbar(){
        $this->CI->load->view("req/top-navbar");
        $this->CI->load->view("req/navbar");
    }
    public function content_open(){
        $this->CI->load->view("req/content-open");
    }
    public function close(){
        $this->CI->load->view("req/content-close");
        $this->CI->load->view("req/body-close");
        $this->CI->load->view("req/html-close");
        $this->CI->load->view("req/script");
    }
}
?>