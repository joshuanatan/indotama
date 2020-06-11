<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_barang_kombinasi extends CI_Model{
    private $tbl_name = "tbl_barang_kombinasi";
    private $columns = array();
    private $id_pk_barang_kombinasi;
    private $id_barang_utama; /*hasil gabungan dari kedua barang itu, contoh wearpack*/
    private $id_barang_kombinasi; /*item yang jadi gabungan untuk membuat wearpack, contoh: celana panjang & baju panjang*/
    private $barang_kombinasi_qty; /*2 celana & 1 baju */
    private $barang_kombinasi_status;
    private $barang_kombinasi_create_date;
    private $barang_kombinasi_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->barang_kombinasi_create_date = date("Y-m-d H:i:s");
        $this->barang_kombinasi_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "drop table if exists tbl_barang_kombinasi;
        create table tbl_barang_kombinasi(
            id_pk_barang_kombinasi int primary key auto_increment,
            id_barang_utama int,
            id_barang_kombinasi int,
            barang_kombinasi_qty double,
            barang_kombinasi_status varchar(15),
            barang_kombinasi_create_date datetime,
            barang_kombinasi_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_barang_kombinasi_log;
        create table tbl_barang_kombinasi_log(
            id_pk_barang_kombinasi_log int primary key auto_increment,
            executed_function varchar(20),
            id_pk_barang_kombinasi int,
            id_barang_utama int,
            id_barang_kombinasi int,
            barang_kombinasi_qty double,
            barang_kombinasi_status varchar(15),
            barang_kombinasi_create_date datetime,
            barang_kombinasi_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_barang_kombinasi;
        delimiter $$
        create trigger trg_after_insert_barang_kombinasi
        after insert on tbl_barang_kombinasi
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_barang_kombinasi;
        delimiter $$
        create trigger trg_after_update_barang_kombinasi
        after update on tbl_barang_kombinasi
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
    }
    public function list(){
        $sql = "
        select id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty, brg_kombinasi.brg_nama
        from ".$this->tbl_name."
        inner join mstr_barang as brg_kombinasi on brg_kombinasi.id_pk_brg = tbl_barang_kombinasi.id_barang_kombinasi  
        where barang_kombinasi_status = ? and id_barang_utama = ? and brg_status = ?
        ";
        $args = array(
            "aktif",$this->id_barang_utama,"aktif"
        );
        return executeQuery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_barang_utama" => $this->id_barang_utama,
                "id_barang_kombinasi" => $this->id_barang_kombinasi,
                "barang_kombinasi_qty" => $this->barang_kombinasi_qty,
                "barang_kombinasi_status" => "aktif",
                "barang_kombinasi_create_date" => $this->barang_kombinasi_create_date,
                "barang_kombinasi_last_modified" => $this->barang_kombinasi_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_barang_kombinasi" => $this->id_pk_barang_kombinasi,
            );
            $data = array(
                "id_barang_kombinasi" => $this->id_barang_kombinasi,
                "barang_kombinasi_qty" => $this->barang_kombinasi_qty,
                "barang_kombinasi_last_modified" => $this->barang_kombinasi_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_barang_kombinasi" => $this->id_pk_barang_kombinasi,
            );
            $data = array(
                "barang_kombinasi_status" => "nonaktif",
                "barang_kombinasi_last_modified" => $this->barang_kombinasi_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if(!$this->id_barang_utama){
            return false;
        }
        if(!$this->id_barang_kombinasi){
            return false;
        }
        if(!$this->barang_kombinasi_qty){
            return false;
        }
        if(!$this->barang_kombinasi_status){
            return false;
        }
        if(!$this->barang_kombinasi_create_date){
            return false;
        }
        if(!$this->barang_kombinasi_last_modified){
            return false;
        }
        if(!$this->id_create_data){
            return false;
        }
        if(!$this->id_last_modified){
            return false;
        }
        return true;
    }
    public function check_update(){
        if(!$this->id_pk_barang_kombinasi){
            return false;
        }
        if(!$this->id_barang_kombinasi){
            return false;
        }
        if(!$this->barang_kombinasi_qty){
            return false;
        }
        if(!$this->barang_kombinasi_last_modified){
            return false;
        }
        if(!$this->id_last_modified){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if(!$this->id_pk_barang_kombinasi){
            return false;
        }
        if(!$this->barang_kombinasi_last_modified){
            return false;
        }
        if(!$this->id_last_modified){
            return false;
        }
        return true;
    }
    public function set_insert($id_barang_utama,$id_barang_kombinasi,$barang_kombinasi_qty,$barang_kombinasi_status){
        if(!$this->set_id_barang_utama($id_barang_utama)){
            return false;
        }
        if(!$this->set_id_barang_kombinasi($id_barang_kombinasi)){
            return false;
        }
        if(!$this->set_barang_kombinasi_qty($barang_kombinasi_qty)){
            return false;
        }
        if(!$this->set_barang_kombinasi_status($barang_kombinasi_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_barang_kombinasi,$id_barang_kombinasi,$barang_kombinasi_qty){
        if(!$this->set_id_pk_barang_kombinasi($id_pk_barang_kombinasi)){
            return false;
        }
        if(!$this->set_id_barang_kombinasi($id_barang_kombinasi)){
            return false;
        }
        if(!$this->set_barang_kombinasi_qty($barang_kombinasi_qty)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_barang_kombinasi){
        if(!$this->set_id_pk_barang_kombinasi($id_pk_barang_kombinasi)){
            return false;
        }
        return true;
    }
    public function get_id_pk_barang_kombinasi(){
        return $this->id_pk_barang_kombinasi;
    }
    public function get_id_barang_utama(){
        return $this->id_barang_utama;
    }
    public function get_id_barang_kombinasi(){
        return $this->id_barang_kombinasi;
    }
    public function get_barang_kombinasi_qty(){
        return $this->barang_kombinasi_qty;
    }
    public function get_barang_kombinasi_status(){
        return $this->barang_kombinasi_status;
    }    
    public function set_id_pk_barang_kombinasi($id_pk_barang_kombinasi){
        if($id_pk_barang_kombinasi){
            $this->id_pk_barang_kombinasi = $id_pk_barang_kombinasi;
            return true;
        }
        return false;
    }
    public function set_id_barang_utama($id_barang_utama){
        if($id_barang_utama){
            $this->id_barang_utama = $id_barang_utama;
            return true;
        }
        return false;
    }
    public function set_id_barang_kombinasi($id_barang_kombinasi){
        if($id_barang_kombinasi){
            $this->id_barang_kombinasi = $id_barang_kombinasi;
            return true;
        }
        return false;
    }
    public function set_barang_kombinasi_qty($barang_kombinasi_qty){
        if($barang_kombinasi_qty){
            $this->barang_kombinasi_qty = $barang_kombinasi_qty;
            return true;
        }
        return false;
    }
    public function set_barang_kombinasi_status($barang_kombinasi_status){
        if($barang_kombinasi_status){
            $this->barang_kombinasi_status = $barang_kombinasi_status;
            return true;
        }
        return false;
    }
}