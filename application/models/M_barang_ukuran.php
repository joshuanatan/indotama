<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_barang_ukuran extends ci_model{
    private $tbl_name = "tbl_barang_ukuran";
    private $columns = array();
    private $id_pk_barang_ukuran;
    private $id_fk_barang;
    private $ukuran;
    private $brg_ukuran_status;
    private $brg_ukuran_create_date;
    private $brg_ukuran_last_modified;
    private $id_create_date;
    private $id_last_modified;

    public function install(){
        $sql = "
        create table `tbl_barang_ukuran` (
            `id_pk_barang_ukuran` int primary key auto_increment,
            `id_fk_barang` int(11) default null,
            `ukuran` varchar(10) default null,
            `brg_ukuran_status` varchar(15) default null,
            `brg_ukuran_create_date` datetime default null,
            `brg_ukuran_last_modified` datetime default null,
            `id_create_date` int(11) default null,
            `id_last_modified` int(11) default null
        )";
    }
    public function __construct(){
        parent::__construct();
        $this->brg_ukuran_create_date = date("y-m-d h:i:s");
        $this->brg_ukuran_last_modified = date("y-m-d h:i:s");
        $this->id_create_date = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_barang" => $this->id_fk_barang,
                "ukuran" => $this->ukuran,
                "brg_ukuran_status" => $this->brg_ukuran_status,
                "brg_ukuran_create_date" => $this->brg_ukuran_create_date,
                "brg_ukuran_last_modified" => $this->brg_ukuran_last_modified,
                "id_create_date" => $this->id_create_date,
                "id_last_modified" => $this->id_last_modified
            );
            return insertrow($this->tbl_name,$data);
        }
        return false;
    }
    public function remove(){
        $where = array(
            "id_fk_barang" => $this->id_fk_barang
        );
        deleterow($this->tbl_name,$where);
        return true;
    }
    public function check_insert(){
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->ukuran == ""){
            return false;
        }
        if($this->brg_ukuran_status == ""){
            return false;
        }
        if($this->brg_ukuran_create_date == ""){
            return false;
        }
        if($this->brg_ukuran_last_modified == ""){
            return false;
        }
        if($this->id_create_date == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_update(){
        if($this->id_pk_barang_ukuran == ""){
            return false;
        }
        if($this->id_fk_barang == ""){
            return false;
        }
        if($this->ukuran == ""){
            return false;
        }
        if($this->brg_ukuran_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_barang_ukuran == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_barang,$ukuran,$brg_ukuran_status){
        if(!$this->set_id_fk_barang($id_fk_barang)){
            return false;
        }
        if(!$this->set_ukuran($ukuran)){
            return false;
        }
        if(!$this->set_brg_ukuran_status($brg_ukuran_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_barang_ukuran,$ukuran){
        if(!$this->set_id_pk_barang_ukuran($id_pk_barang_ukuran)){
            return false;
        }
        if(!$this->set_ukuran($ukuran)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_barang_ukuran){
        if(!$this->set_id_pk_barang_ukuran($id_pk_barang_ukuran)){
            return false;
        }
        return true;
    }
    public function set_id_pk_barang_ukuran($id_pk_barang_ukuran){
        if($id_pk_barang_ukuran != ""){
            $this->id_pk_barang_ukuran = $id_pk_barang_ukuran;
            return true;
        }
        return true;
    }
    public function set_id_fk_barang($id_fk_barang){
        if($id_fk_barang != ""){
            $this->id_fk_barang = $id_fk_barang;
            return true;
        }
        return true;
    }
    public function set_ukuran($ukuran){
        if($ukuran != ""){
            $this->ukuran = $ukuran;
            return true;
        }
        return true;
    }
    public function set_brg_ukuran_status($brg_ukuran_status){
        if($brg_ukuran_status != ""){
            $this->brg_ukuran_status = $brg_ukuran_status;
            return true;
        }
        return true;
    }
    public function get_id_pk_barang_ukuran(){
        return $this->id_pk_barang_ukuran;
    }
    public function get_id_fk_barang(){
        return $this->id_fk_barang;
    }
    public function get_ukuran(){
        return $this->ukuran;
    }
    public function get_brg_ukuran_status(){
        return $this->brg_ukuran_status;
    }

}