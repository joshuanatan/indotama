<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_penjualan_custom extends CI_Model{
    private $tbl_name = "tbl_penjualan_custom";
    private $columns = array();
    private $id_pk_penjualan_custom;
    private $id_fk_brg_awal;
    private $id_fk_brg_rubah;
    private $id_fk_penjualan;
    private $penjualan_custom_qty;
    private $status_penjualan_custom;
    private $penjualan_custom_create_date;
    private $penjualan_custom_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("barang_awal","Barang Awal",true);
        $this->set_column("barang_ubah","Barang Ubah",true);
        $this->set_column("penjualan_custom_qty","Jumlah",true);
        $this->penjualan_custom_create_date = date("Y-m-d H:i:s");
        $this->penjualan_custom_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists tbl_penjualan_custom;
        create table tbl_penjualan_custom(
            id_pk_penjualan_custom int primary key auto_increment,
            id_fk_brg_awal int,
            id_fk_brg_rubah int,
            id_fk_penjualan int,
            penjualan_custom_qty double,
            status_penjualan_custom varchar(15),
            penjualan_custom_create_date datetime,
            penjualan_custom_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_penjualan_custom_log;
        create table tbl_penjualan_custom_log(
            id_pk_penjualan_custom_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_penjualan_custom int,
            id_fk_brg_awal int,
            id_fk_brg_rubah int,
            id_fk_penjualan int,
            penjualan_custom_qty double,
            status_penjualan_custom varchar(15),
            penjualan_custom_create_date datetime,
            penjualan_custom_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_penjualan_custom;
        delimiter $$
        create trigger trg_after_insert_penjualan_custom
        after insert on tbl_penjualan_custom
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_custom_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penjualan_custom_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_custom_log(executed_function,id_pk_penjualan_custom,id_fk_brg_awal,id_fk_brg_rubah,id_fk_penjualan,penjualan_custom_qty,status_penjualan_custom,penjualan_custom_create_date,penjualan_custom_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',NEW.id_pk_penjualan_custom,NEW.id_fk_brg_awal,NEW.id_fk_brg_rubah,NEW.id_fk_penjualan,NEW.penjualan_custom_qty,NEW.status_penjualan_custom,NEW.penjualan_custom_create_date,NEW.penjualan_custom_last_modified,NEW.id_create_data,NEW.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_penjualan_custom;
        delimiter $$
        create trigger trg_after_update_penjualan_custom
        after update on tbl_penjualan_custom
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_custom_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penjualan_custom_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_custom_log(executed_function,id_pk_penjualan_custom,id_fk_brg_awal,id_fk_brg_rubah,id_fk_penjualan,penjualan_custom_qty,status_penjualan_custom,penjualan_custom_create_date,penjualan_custom_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',NEW.id_pk_penjualan_custom,NEW.id_fk_brg_awal,NEW.id_fk_brg_rubah,NEW.id_fk_penjualan,NEW.penjualan_custom_qty,NEW.status_penjualan_custom,NEW.penjualan_custom_create_date,NEW.penjualan_custom_last_modified,NEW.id_create_data,NEW.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executeQuery($sql);
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function columns(){
        return $this->columns;
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            (
                id_pk_penerimaan like '%".$search_key."%' or
                penerimaan_tgl like '%".$search_key."%' or
                penerimaan_status like '%".$search_key."%' or
                id_fk_pembelian like '%".$search_key."%' or
                penerimaan_tempat like '%".$search_key."%' or
                penerimaan_last_modified like '%".$search_key."%'
            )";
        }
        if(strtolower($this->penerimaan_tempat) == "cabang"){
            $query = "
            select id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_pembelian,penerimaan_tempat,".$this->tbl_name.".id_fk_warehouse,".$this->tbl_name.".id_fk_cabang,penerimaan_last_modified,pem_pk_nomor
            from ".$this->tbl_name." 
            inner join mstr_pembelian on mstr_pembelian.id_pk_pembelian = ".$this->tbl_name.".id_fk_pembelian
            inner join mstr_supplier on mstr_supplier.id_pk_sup = mstr_pembelian.id_fk_supp
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            where penerimaan_status = ? and sup_status = ? and cabang_status = ? and toko_status = ? and ".$this->tbl_name.".id_fk_cabang = ? ".$search_query."  
            order by ".$order_by." ".$order_direction." 
            limit 20 offset ".($page-1)*$data_per_page;
            $args = array(
                "aktif","aktif","aktif","aktif",$this->id_fk_cabang
            );
            $result["data"] = executequery($query,$args);
            $query = "
            select id_pk_penerimaan
            from ".$this->tbl_name." 
            inner join mstr_pembelian on mstr_pembelian.id_pk_pembelian = ".$this->tbl_name.".id_fk_pembelian
            inner join mstr_supplier on mstr_supplier.id_pk_sup = mstr_pembelian.id_fk_supp
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            where penerimaan_status = ? and sup_status = ? and cabang_status = ? and toko_status = ? and ".$this->tbl_name.".id_fk_cabang = ? ".$search_query."  
            order by ".$order_by." ".$order_direction;
            $result["total_data"] = executequery($query,$args)->num_rows();
        }
        else{
            $query = "
            select id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_pembelian,penerimaan_tempat,".$this->tbl_name.".id_fk_warehouse,".$this->tbl_name.".id_fk_cabang,penerimaan_last_modified,pem_pk_nomor
            from ".$this->tbl_name." 
            inner join mstr_pembelian on mstr_pembelian.id_pk_pembelian = ".$this->tbl_name.".id_fk_pembelian
            inner join mstr_supplier on mstr_supplier.id_pk_sup = mstr_pembelian.id_fk_supp
            inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = ".$this->tbl_name.".id_fk_warehouse
            where penerimaan_status = ? and sup_status = ? and ".$this->tbl_name.".id_fk_warehouse = ? ".$search_query." 
            order by ".$order_by." ".$order_direction." 
            limit 20 offset ".($page-1)*$data_per_page;
            $args = array(
                "aktif","aktif",$this->id_fk_warehouse
            );
            $result["data"] = executequery($query,$args);
            $query = "
            select id_pk_pembelian
            from ".$this->tbl_name." 
            inner join mstr_pembelian on mstr_pembelian.id_pk_pembelian = ".$this->tbl_name.".id_fk_pembelian
            inner join mstr_supplier on mstr_supplier.id_pk_sup = mstr_pembelian.id_fk_supp
            inner join mstr_warehouse on mstr_warehouse.id_pk_warehouse = ".$this->tbl_name.".id_fk_warehouse
            where penerimaan_status = ? and sup_status = ? and ".$this->tbl_name.".id_fk_warehouse = ? ".$search_query." 
            order by ".$order_by." ".$order_direction;
            $result["total_data"] = executequery($query,$args)->num_rows();
        }
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_brg_awal" => $this->id_fk_brg_awal,
                "id_fk_brg_rubah" => $this->id_fk_brg_rubah,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "penjualan_custom_qty" => $this->penjualan_custom_qty,
                "status_penjualan_custom" => $this->status_penjualan_custom,
                "penjualan_custom_create_date" => $this->penjualan_custom_create_date,
                "penjualan_custom_last_modified" => $this->penjualan_custom_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
            );
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_penjualan_custom" => $this->id_pk_penjualan_custom
            );
            $data = array(
                "id_fk_brg_awal" => $this->id_fk_brg_awal,
                "id_fk_brg_rubah" => $this->id_fk_brg_rubah,
                "penjualan_custom_qty" => $this->penjualan_custom_qty,
                "penjualan_custom_last_modified" => $this->penjualan_custom_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true; 
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_fk_brg_awal" => $this->id_fk_brg_awal,
            );
            $data = array(
                "status_penjualan_custom" => "NONAKTIF",
                "penjualan_custom_last_modified" => $this->penjualan_custom_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_brg_awal == ""){
            return false;
        }
        if($this->id_fk_brg_rubah == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->penjualan_custom_qty == ""){
            return false;
        }
        if($this->status_penjualan_custom == ""){
            return false;
        }
        if($this->penjualan_custom_create_date == ""){
            return false;
        }
        if($this->penjualan_custom_last_modified == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_update(){
        if($this->id_pk_penjualan_custom == ""){
            return false;
        }
        if($this->id_fk_brg_awal == ""){
            return false;
        }
        if($this->id_fk_brg_rubah == ""){
            return false;
        }
        if($this->penjualan_custom_qty == ""){
            return false;
        }
        if($this->penjualan_custom_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_penjualan_custom == ""){
            return false;
        }
        if($this->penjualan_custom_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_brg_awal,$id_fk_brg_rubah,$id_fk_penjualan,$penjualan_custom_qty,$status_penjualan_custom){
        if(!$this->set_id_fk_brg_awal($id_fk_brg_awal)){
            return false;
        }
        if(!$this->set_id_fk_brg_rubah($id_fk_brg_rubah)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        if(!$this->set_penjualan_custom_qty($penjualan_custom_qty)){
            return false;
        }
        if(!$this->set_status_penjualan_custom($status_penjualan_custom)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_penjualan_custom,$id_fk_brg_awal,$id_fk_brg_rubah,$penjualan_custom_qty){
        if(!$this->set_id_pk_penjualan_custom($id_pk_penjualan_custom)){
            return false;
        }
        if(!$this->set_id_fk_brg_awal($id_fk_brg_awal)){
            return false;
        }
        if(!$this->set_id_fk_brg_rubah($id_fk_brg_rubah)){
            return false;
        }
        if(!$this->set_penjualan_custom_qty($penjualan_custom_qty)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_penjualan_custom){
        if(!$this->set_id_pk_penjualan_custom($id_pk_penjualan_custom)){
            return false;
        }
        return true;
    }
    public function set_id_pk_penjualan_custom($id_pk_penjualan_custom){
        if($id_pk_penjualan_custom != ""){
            $this->id_pk_penjualan_custom = $id_pk_penjualan_custom;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg_awal($id_fk_brg_awal){
        if($id_fk_brg_awal != ""){
            $this->id_fk_brg_awal = $id_fk_brg_awal;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg_rubah($id_fk_brg_rubah){
        if($id_fk_brg_rubah != ""){
            $this->id_fk_brg_rubah = $id_fk_brg_rubah;
            return true;
        }
        return false;
    }
    public function set_id_fk_penjualan($id_fk_penjualan){
        if($id_fk_penjualan != ""){
            $this->id_fk_penjualan = $id_fk_penjualan;
            return true;
        }
        return false;
    }
    public function set_penjualan_custom_qty($penjualan_custom_qty){
        if($penjualan_custom_qty != ""){
            $this->penjualan_custom_qty = $penjualan_custom_qty;
            return true;
        }
        return false;
    }
    public function set_status_penjualan_custom($status_penjualan_custom){
        if($status_penjualan_custom != ""){
            $this->status_penjualan_custom = $status_penjualan_custom;
            return true;
        }
        return false;
    }
    public function get_id_pk_penjualan_custom(){
        return $this->id_pk_penjualan_custom;
    }
    public function get_id_fk_brg_awal(){
        return $this->id_fk_brg_awal;
    }
    public function get_id_fk_brg_rubah(){
        return $this->id_fk_brg_rubah;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
    public function get_penjualan_custom_qty(){
        return $this->penjualan_custom_qty;
    }
    public function get_status_penjualan_custom(){
        return $this->status_penjualan_custom;
    }
}