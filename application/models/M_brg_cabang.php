<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_brg_cabang extends ci_model{
    private $tbl_name = "tbl_brg_cabang";
    private $columns = array();
    private $id_pk_brg_cabang;
    private $brg_cabang_qty;
    private $brg_cabang_notes;
    private $brg_cabang_status;
    private $brg_cabang_last_price;
    private $id_fk_cabang;
    private $id_fk_brg;
    private $brg_cabang_create_date;
    private $brg_cabang_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_kode","kode barang","required");
        $this->set_column("brg_nama","nama barang","required");
        $this->set_column("brg_ket","keterangan","required");
        $this->set_column("brg_cabang_qty","qty","required");
        $this->set_column("brg_cabang_last_price","biaya terakhir","required");
        $this->set_column("brg_cabang_notes","notes","required");
        $this->set_column("brg_cabang_status","status","required");
        $this->set_column("brg_cabang_last_modified","last modified","required");
        $this->brg_cabang_create_date = date("y-m-d h:i:s");
        $this->brg_cabang_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function install(){
        $sql = "
        drop table if exists tbl_brg_cabang;
        create table tbl_brg_cabang(
            id_pk_brg_cabang int primary key auto_increment,
            brg_cabang_qty int,
            brg_cabang_notes varchar(200),
            brg_cabang_status varchar(15),
            brg_cabang_last_price int default 0,
            id_fk_brg int,
            id_fk_cabang int,
            brg_cabang_create_date datetime,
            brg_cabang_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_cabang_log;
        create table tbl_brg_cabang_log(
            id_pk_brg_cabang_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_cabang int,
            brg_cabang_qty int,
            brg_cabang_last_price int default 0,
            brg_cabang_notes varchar(200),
            brg_cabang_status varchar(15),
            id_fk_brg int,
            id_fk_cabang int,
            brg_cabang_create_date datetime,
            brg_cabang_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_cabang;
        delimiter $$
        create trigger trg_after_insert_brg_cabang
        after insert on tbl_brg_cabang
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_cabang_log(executed_function,id_pk_brg_cabang,brg_cabang_qty,brg_cabang_last_price,brg_cabang_notes,brg_cabang_status,id_fk_brg,id_fk_cabang,brg_cabang_create_date,brg_cabang_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_cabang,new.brg_cabang_last_price,new.brg_cabang_qty,new.brg_cabang_notes,new.brg_cabang_status,new.id_fk_brg,new.id_fk_cabang,new.brg_cabang_create_date,new.brg_cabang_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_brg_cabang;
        delimiter $$
        create trigger trg_after_update_brg_cabang
        after update on tbl_brg_cabang
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_cabang_log(executed_function,id_pk_brg_cabang,brg_cabang_qty,brg_cabang_last_price,brg_cabang_notes,brg_cabang_status,id_fk_brg,id_fk_cabang,brg_cabang_create_date,brg_cabang_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_cabang,new.brg_cabang_last_price,new.brg_cabang_qty,new.brg_cabang_notes,new.brg_cabang_status,new.id_fk_brg,new.id_fk_cabang,new.brg_cabang_create_date,new.brg_cabang_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop procedure if exists update_stok_barang_cabang;
        delimiter //
        create procedure update_stok_barang_cabang(
            in id_barang int,
            in id_cabang int,
            in barang_masuk double,
            in id_satuan_masuk int,
            in barang_keluar double,
            in id_satuan_keluar int
        )
        begin
            /*
            the logic is
            barang_masuk = n, barang_keluar = 0 [insert new data]
            barang_masuk = n, barang_keluar = m [update data]
            barang_masuk = 0, barang_keluar = m [delete data]
            */
            if barang_masuk != 0 then
            call ubah_satuan_barang(id_satuan_masuk, barang_masuk);
            end if;
            if barang_keluar != 0 then
            call ubah_satuan_barang(id_satuan_keluar, barang_keluar);
            end if;
            update tbl_brg_cabang 
            set brg_cabang_qty = brg_cabang_qty+barang_masuk-barang_keluar
            where id_fk_brg = id_barang and id_fk_cabang = id_cabang;
        end //
        delimiter ;";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            (
                id_pk_brg_cabang like '%".$search_key."%' or 
                brg_cabang_qty like '%".$search_key."%' or 
                brg_cabang_notes like '%".$search_key."%' or 
                brg_cabang_status like '%".$search_key."%' or 
                id_fk_brg like '%".$search_key."%' or 
                brg_cabang_last_modified like '%".$search_key."%' or 
                brg_nama like '%".$search_key."%' or 
                brg_kode like '%".$search_key."%' or 
                brg_ket like '%".$search_key."%' or 
                brg_minimal like '%".$search_key."%' or 
                brg_satuan like '%".$search_key."%' or 
                brg_image like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_brg_cabang,brg_cabang_qty,brg_cabang_last_price,brg_cabang_notes,brg_cabang_status,id_fk_brg,brg_cabang_last_modified,brg_nama,brg_kode,brg_ket,brg_minimal,brg_satuan,brg_image
        from ".$this->tbl_name." 
        inner join mstr_barang on mstr_barang.id_pk_brg = ".$this->tbl_name.".id_fk_brg
        where brg_cabang_status = ? and brg_status = ? and id_fk_cabang = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif","aktif",$this->id_fk_cabang
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_brg_cabang
        from ".$this->tbl_name." 
        inner join mstr_barang on mstr_barang.id_pk_brg = ".$this->tbl_name.".id_fk_brg
        where brg_cabang_status = ? and brg_status = ? and id_fk_cabang = ?".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function list(){
        $sql = "
        select id_pk_brg_cabang,brg_cabang_qty,brg_cabang_notes,brg_cabang_last_price,brg_cabang_status,id_fk_brg,brg_cabang_last_modified,brg_nama,brg_kode,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga
        from ".$this->tbl_name." 
        inner join mstr_barang on mstr_barang.id_pk_brg = ".$this->tbl_name.".id_fk_brg
        where brg_cabang_status = ? and brg_status = ? and id_fk_cabang = ? ";
        $args = array(
            "aktif","aktif",$this->id_fk_cabang
        );
        return executeQuery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_cabang_qty" => $this->brg_cabang_qty,
                "brg_cabang_notes" => $this->brg_cabang_notes,
                "brg_cabang_status" => $this->brg_cabang_status,
                "id_fk_brg" => $this->id_fk_brg,
                "id_fk_cabang" => $this->id_fk_cabang,
                "brg_cabang_create_date" => $this->brg_cabang_create_date,
                "brg_cabang_last_modified" => $this->brg_cabang_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertrow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_brg_cabang" => $this->id_pk_brg_cabang   
            );
            $data = array(
                "brg_cabang_qty" => $this->brg_cabang_qty,
                "brg_cabang_notes" => $this->brg_cabang_notes,
                "id_fk_brg" => $this->id_fk_brg,
                "brg_cabang_last_modified" => $this->brg_cabang_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true; 
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_brg_cabang" => $this->id_pk_brg_cabang   
            );
            $data = array(
                "brg_cabang_status" => "nonaktif",
                "brg_cabang_last_modified" => $this->brg_cabang_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true; 
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_cabang_qty == ""){
            return false;
        }
        if($this->brg_cabang_notes == ""){
            return false;
        }
        if($this->brg_cabang_status == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->brg_cabang_create_date == ""){
            return false;
        }
        if($this->brg_cabang_last_modified == ""){
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
        if($this->id_pk_brg_cabang == ""){
            return false;
        }
        if($this->brg_cabang_qty == ""){
            return false;
        }
        if($this->brg_cabang_notes == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->brg_cabang_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_cabang == ""){
            return false;
        }
        if($this->brg_cabang_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_cabang_qty,$brg_cabang_notes,$brg_cabang_status,$id_fk_brg,$id_fk_cabang){
        if(!$this->set_brg_cabang_qty($brg_cabang_qty)){
            return false;
        }
        if(!$this->set_brg_cabang_notes($brg_cabang_notes)){
            return false;
        }
        if(!$this->set_brg_cabang_status($brg_cabang_status)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_cabang,$brg_cabang_qty,$brg_cabang_notes,$id_fk_brg){
        if(!$this->set_id_pk_brg_cabang($id_pk_brg_cabang)){
            return false;
        }
        if(!$this->set_brg_cabang_qty($brg_cabang_qty)){
            return false;
        }
        if(!$this->set_brg_cabang_notes($brg_cabang_notes)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_cabang){
        if(!$this->set_id_pk_brg_cabang($id_pk_brg_cabang)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_cabang($id_pk_brg_cabang){
        if($id_pk_brg_cabang != ""){
            $this->id_pk_brg_cabang = $id_pk_brg_cabang;
            return true;
        }
        return false;
    }
    public function set_brg_cabang_qty($brg_cabang_qty){
        if($brg_cabang_qty != ""){
            $this->brg_cabang_qty = $brg_cabang_qty;
            return true;
        }
        return false;
    }
    public function set_brg_cabang_notes($brg_cabang_notes){
        if($brg_cabang_notes != ""){
            $this->brg_cabang_notes = $brg_cabang_notes;
            return true;
        }
        return false;
    }
    public function set_brg_cabang_status($brg_cabang_status){
        if($brg_cabang_status != ""){
            $this->brg_cabang_status = $brg_cabang_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg($id_fk_brg){
        if($id_fk_brg != ""){
            $this->id_fk_brg = $id_fk_brg;
            return true;
        }
        return false;
    }
    public function set_id_fk_cabang($id_fk_cabang){
        if($id_fk_cabang != ""){
            $this->id_fk_cabang = $id_fk_cabang;
            return true;
        }
        return false;
    }
    public function get_id_pk_brg_cabang(){
        return $this->id_pk_brg_cabang;
    }
    public function get_brg_cabang_qty(){
        return $this->brg_cabang_qty;
    }
    public function get_brg_cabang_notes(){
        return $this->brg_cabang_notes;
    }
    public function get_brg_cabang_status(){
        return $this->brg_cabang_status;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
}