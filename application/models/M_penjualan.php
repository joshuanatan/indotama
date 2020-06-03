<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_penjualan extends ci_model{
    private $tbl_name = "mstr_penjualan";
    private $columns = array();
    private $id_pk_penjualan;
    private $penj_nomor;
    private $penj_tgl;
    private $penj_dateline_tgl;/*supaya tau pas pengiriman mana yang urgent*/
    private $penj_status;
    private $penj_jenis; /*online/offline*/
    private $penj_tipe_pembayaran; /*full/dp/trial/dkk*/
    private $id_fk_customer;
    private $id_fk_cabang;
    private $penj_create_date;
    private $penj_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("penj_nomor","nomor penjualan",true);
        $this->set_column("penj_tgl","tanggal penjualan",false);
        $this->set_column("penj_dateline_tgl","dateline",false);
        $this->set_column("penj_jenis","jenis penjualan",false);
        $this->set_column("tipe_pembayaran","tipe pembayaran",false);
        $this->set_column("cust_name","customer",false);
        $this->set_column("penj_status","status",false);
        $this->set_column("penj_last_modified","last modified",false);
        $this->penj_create_date = date("y-m-d h:i:s");
        $this->penj_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
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
    public function list(){
        $sql = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_perusahaan,cust_name from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        where id_fk_cabang = ? and penj_status = ?";
        $args = array(
            $this->id_fk_cabang,"AKTIF"
        );
        return executeQuery($sql,$args);
    }
    public function detail_by_penj_nomor(){
        $sql = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_perusahaan,cust_name,cust_suff,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan 
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        where penj_nomor = ?";
        $args = array(
            $this->penj_nomor
        );
        return executeQuery($sql,$args);
    }
    public function detail_by_id_pk_penjualan(){
        $sql = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_perusahaan,cust_name,cust_suff,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan 
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        where id_pk_penjualan = ?";
        $args = array(
            $this->id_pk_penjualan
        );
        return executeQuery($sql,$args);
    }
    public function install(){
        $sql = "
        drop table if exists mstr_penjualan;
        create table mstr_penjualan(
            id_pk_penjualan int primary key auto_increment,
            penj_nomor varchar(30),
            penj_tgl datetime,
            penj_dateline_tgl datetime,
            penj_jenis varchar(50),
            penj_tipe_pembayaran varchar(50),
            penj_status varchar(15),
            id_fk_customer int,
            id_fk_cabang int,
            penj_create_date datetime,
            penj_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_penjualan_log;
        create table mstr_penjualan_log(
            id_pk_penjualan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_penjualan int,
            penj_nomor varchar(30),
            penj_tgl datetime,
            penj_dateline_tgl datetime,
            penj_jenis varchar(50),
            penj_tipe_pembayaran varchar(50),
            penj_status varchar(15),
            id_fk_customer int,
            id_fk_cabang int,
            penj_create_date datetime,
            penj_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_penjualan;
        delimiter $$
        create trigger trg_after_insert_penjualan
        after insert on mstr_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan,new.penj_nomor,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_penjualan;
        delimiter $$
        create trigger trg_after_update_penjualan
        after update on mstr_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan,new.penj_nomor,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_pk_penjualan like '%".$search_key."%' or
                penj_nomor like '%".$search_key."%' or
                penj_tgl like '%".$search_key."%' or
                penj_dateline_tgl like '%".$search_key."%' or
                penj_status like '%".$search_key."%' or
                penj_jenis like '%".$search_key."%' or
                penj_tipe_pembayaran like '%".$search_key."%' or
                penj_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan
        from ".$this->tbl_name." 
        inner join mstr_customer on mstr_customer.id_pk_cust = ".$this->tbl_name.".id_fk_customer
        where penj_status = ? and cust_status = ? and id_fk_cabang = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "aktif","aktif",$this->id_fk_cabang
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_penjualan
        from ".$this->tbl_name." 
        inner join mstr_customer on mstr_customer.id_pk_cust = ".$this->tbl_name.".id_fk_customer
        where penj_status = ? and cust_status = ? and id_fk_cabang = ? ".$search_query."  
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penj_nomor" => $this->penj_nomor,
                "penj_tgl" => $this->penj_tgl,
                "penj_status" => $this->penj_status,
                "penj_dateline_tgl" => $this->penj_dateline_tgl,
                "penj_jenis" => $this->penj_jenis,
                "penj_tipe_pembayaran" => $this->penj_tipe_pembayaran,
                "id_fk_customer" => $this->id_fk_customer,
                "id_fk_cabang" => $this->id_fk_cabang,
                "penj_create_date" => $this->penj_create_date,
                "penj_last_modified" => $this->penj_last_modified,
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
                "id_pk_penjualan" => $this->id_pk_penjualan
            );
            $data = array(
                "penj_nomor" => $this->penj_nomor,
                "penj_jenis" => $this->penj_jenis,
                "penj_dateline_tgl" => $this->penj_dateline_tgl,
                "penj_tgl" => $this->penj_tgl,
                "penj_tipe_pembayaran" => $this->penj_tipe_pembayaran,
                "id_fk_customer" => $this->id_fk_customer,
                "penj_last_modified" => $this->penj_last_modified,
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
                "id_pk_penjualan" => $this->id_pk_penjualan
            );
            $data = array(
                "penj_status" => "nonaktif",
                "penj_last_modified" => $this->penj_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->penj_nomor == ""){
            return false;
        }
        if($this->penj_tgl == ""){
            return false;
        }
        if($this->penj_dateline_tgl == ""){
            return false;
        }
        if($this->penj_jenis == ""){
            return false;
        }
        if($this->penj_tipe_pembayaran == ""){
            return false;
        }
        if($this->penj_status == ""){
            return false;
        }
        if($this->id_fk_customer == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->penj_create_date == ""){
            return false;
        }
        if($this->penj_last_modified == ""){
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
        if($this->id_pk_penjualan == ""){
            return false;
        }
        if($this->penj_nomor == ""){
            return false;
        }
        if($this->penj_dateline_tgl == ""){
            return false;
        }
        if($this->penj_jenis == ""){
            return false;
        }
        if($this->penj_tipe_pembayaran == ""){
            return false;
        }
        if($this->penj_tgl == ""){
            return false;
        }
        if($this->id_fk_customer == ""){
            return false;
        }
        if($this->penj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_penjualan == ""){
            return false;
        }
        if($this->penj_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($penj_nomor,$penj_tgl,$penj_dateline_tgl,$penj_jenis,$penj_tipe_pembayaran,$id_fk_customer,$id_fk_cabang,$penj_status){
        if(!$this->set_penj_nomor($penj_nomor)){
            return false;
        }
        if(!$this->set_penj_dateline_tgl($penj_dateline_tgl)){
            return false;
        }
        if(!$this->set_penj_jenis($penj_jenis)){
            return false;
        }
        if(!$this->set_penj_tipe_pembayaran($penj_tipe_pembayaran)){
            return false;
        }
        if(!$this->set_penj_tgl($penj_tgl)){
            return false;
        }
        if(!$this->set_penj_status($penj_status)){
            return false;
        }
        if(!$this->set_id_fk_customer($id_fk_customer)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_penjualan,$penj_nomor,$penj_dateline_tgl,$penj_jenis,$penj_tipe_pembayaran,$penj_tgl,$id_fk_customer){
        if(!$this->set_id_pk_penjualan($id_pk_penjualan)){
            return false;
        }
        if(!$this->set_penj_nomor($penj_nomor)){
            return false;
        }
        if(!$this->set_penj_dateline_tgl($penj_dateline_tgl)){
            return false;
        }
        if(!$this->set_penj_jenis($penj_jenis)){
            return false;
        }
        if(!$this->set_penj_tipe_pembayaran($penj_tipe_pembayaran)){
            return false;
        }
        if(!$this->set_penj_tgl($penj_tgl)){
            return false;
        }
        if(!$this->set_id_fk_customer($id_fk_customer)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_penjualan){
        if(!$this->set_id_pk_penjualan($id_pk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_id_pk_penjualan($id_pk_penjualan){
        if($id_pk_penjualan != ""){
            $this->id_pk_penjualan = $id_pk_penjualan;
            return true;
        }
        return false;
    }
    public function set_penj_nomor($penj_nomor){
        if($penj_nomor != ""){
            $this->penj_nomor = $penj_nomor;
            return true;
        }
        return false;
    }
    public function set_penj_dateline_tgl($penj_dateline_tgl){
        if($penj_dateline_tgl != ""){
            $this->penj_dateline_tgl = $penj_dateline_tgl;
            return true;
        }
        return false;
    }
    public function set_penj_jenis($penj_jenis){
        if($penj_jenis != ""){
            $this->penj_jenis = $penj_jenis;
            return true;
        }
        return false;
    }
    public function set_penj_tipe_pembayaran($penj_tipe_pembayaran){
        if($penj_tipe_pembayaran != ""){
            $this->penj_tipe_pembayaran = $penj_tipe_pembayaran;
            return true;
        }
        return false;
    }
    public function set_penj_tgl($penj_tgl){
        if($penj_tgl != ""){
            $this->penj_tgl = $penj_tgl;
            return true;
        }
        return false;
    }
    public function set_penj_status($penj_status){
        if($penj_status != ""){
            $this->penj_status = $penj_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_customer($id_fk_customer){
        if($id_fk_customer != ""){
            $this->id_fk_customer = $id_fk_customer;
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
    public function get_id_pk_penjualan(){
        return $this->id_pk_penjualan;
    }
    public function get_penj_nomor(){
        return $this->penj_nomor;
    }
    public function get_penj_dateline_tgl(){
        return $this->penj_dateline_tgl;
    }
    public function get_penj_jenis(){
        return $this->penj_jenis;
    }
    public function get_penj_tipe_pembayaran(){
        return $this->penj_tipe_pembayaran;
    }
    public function get_penj_tgl(){
        return $this->penj_tgl;
    }
    public function get_penj_status(){
        return $this->penj_status;
    }
    public function get_id_fk_customer(){
        return $this->id_fk_customer;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
}