<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_pembelian extends ci_model{
    private $tbl_name = "mstr_pembelian";
    private $columns = array();
    private $id_pk_pembelian;
    private $pem_pk_nomor;
    private $pem_tgl;
    private $pem_status;
    private $id_fk_supp;
    private $id_fk_cabang;
    private $pem_create_date;
    private $pem_last_modified;
    private $id_create_data;
    private $id_last_modified;
    private $no_control;
    private $bln_control;
    private $thn_control;

    public function __construct(){
        parent::__construct();
        $this->set_column("pem_pk_nomor","nomor pembelian",true);
        $this->set_column("pem_tgl","tanggal pembelian",false);
        $this->set_column("sup_perusahaan","supplier",false);
        $this->set_column("pem_status","status",false);
        $this->set_column("pem_last_modified","last modified",false);
        $this->pem_create_date = date("y-m-d h:i:s");
        $this->pem_last_modified = date("y-m-d h:i:s");
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
    public function install(){
        $sql = "
        drop table mstr_pembelian;
        create table mstr_pembelian(
            id_pk_pembelian int primary key auto_increment,
            pem_pk_nomor varchar(30),
            pem_tgl date,
            pem_status varchar(15),
            id_fk_supp int,
            id_fk_cabang int,
            pem_create_date datetime,
            pem_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            no_control int comment 'untuk tau udah nomor berapa untuk penomoran',
            bln_control int,
            thn_control int
        );
        drop table mstr_pembelian_log;
        create table mstr_pembelian_log(
            id_pk_pembelian_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_pembelian int,
            pem_pk_nomor varchar(30),
            pem_tgl date,
            pem_status varchar(15),
            id_fk_supp int,
            id_fk_cabang int,
            pem_create_date datetime,
            pem_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_pembelian;
        delimiter $$
        create trigger trg_after_insert_pembelian
        after insert on mstr_pembelian
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pembelian_log(executed_function,id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,id_fk_supp,id_fk_cabang,pem_create_date,pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_pembelian,new.pem_pk_nomor,new.pem_tgl,new.pem_status,new.id_fk_supp,new.id_fk_cabang,new.pem_create_date,new.pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_pembelian;
        delimiter $$
        create trigger trg_after_update_pembelian
        after update on mstr_pembelian
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pembelian_log(executed_function,id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,id_fk_supp,id_fk_cabang,pem_create_date,pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_pembelian,new.pem_pk_nomor,new.pem_tgl,new.pem_status,new.id_fk_supp,new.id_fk_cabang,new.pem_create_date,new.pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                pem_pk_nomor like '%".$search_key."%' or
                pem_tgl like '%".$search_key."%' or
                pem_status like '%".$search_key."%' or
                id_fk_supp like '%".$search_key."%' or
                pem_create_date like '%".$search_key."%' or
                pem_last_modified like '%".$search_key."%' or
                id_create_data like '%".$search_key."%' or
                id_last_modified like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,sup_perusahaan,pem_last_modified
        from ".$this->tbl_name." 
        inner join mstr_supplier on mstr_supplier.id_pk_sup = ".$this->tbl_name.".id_fk_supp
        where pem_status != ? and sup_status = ? and id_fk_cabang = ? ".$search_query."  
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            "nonaktif","aktif",$this->id_fk_cabang
        );
        $result["data"] = executequery($query,$args);
        
        $query = "
        select id_pk_pembelian
        from ".$this->tbl_name." 
        inner join mstr_supplier on mstr_supplier.id_pk_sup = ".$this->tbl_name.".id_fk_supp
        where pem_status != ? and sup_status = ? and id_fk_cabang = ? ".$search_query."
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query,$args)->num_rows();
        return $result;
    }
    public function columns(){
        return $this->columns;
    }
    public function list(){
        $query = "
        select id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,sup_perusahaan,pem_last_modified,toko_nama,cabang_daerah
        from ".$this->tbl_name." 
        inner join mstr_supplier on mstr_supplier.id_pk_sup = ".$this->tbl_name.".id_fk_supp
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where pem_status = ? and sup_status = ? and cabang_status = ? and toko_status = ?";
        $args = array(
            "aktif","aktif","aktif","aktif"
        );

        if($this->id_fk_cabang != ""){
            $query .= " and id_fk_cabang = ?";
            array_push($args,$this->id_fk_cabang);
        }
        return executequery($query,$args);
    }
    public function detail_by_no(){
        $sql = "
        select id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,sup_perusahaan,pem_last_modified,cabang_daerah,cabang_notelp,cabang_alamat,toko_nama
        from ".$this->tbl_name." 
        inner join mstr_supplier on mstr_supplier.id_pk_sup = ".$this->tbl_name.".id_fk_supp
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where pem_status = ? and sup_status = ? and cabang_status = ? and toko_status = ? and pem_pk_nomor = ?";
        $args = array(
            "aktif","aktif","aktif","aktif",$this->pem_pk_nomor
        );
        return executequery($sql,$args);
    }
    public function detail_by_id(){
        $sql = "
        select id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,sup_perusahaan,pem_last_modified,cabang_daerah,cabang_notelp,cabang_alamat,toko_nama
        from ".$this->tbl_name." 
        inner join mstr_supplier on mstr_supplier.id_pk_sup = ".$this->tbl_name.".id_fk_supp
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where pem_status = ? and sup_status = ? and cabang_status = ? and toko_status = ? and id_pk_pembelian = ?";
        $args = array(
            "aktif","aktif","aktif","aktif",$this->id_pk_pembelian
        );
        return executequery($sql,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "pem_pk_nomor" => $this->pem_pk_nomor,
                "pem_tgl" => $this->pem_tgl,
                "pem_status" => $this->pem_status,
                "id_fk_supp" => $this->id_fk_supp,
                "id_fk_cabang" => $this->id_fk_cabang,
                "pem_create_date" => $this->pem_create_date,
                "pem_last_modified" => $this->pem_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified,
                "no_control" => $this->no_control,
                "bln_control" => explode("-",$this->pem_tgl)[1],
                "thn_control" => explode("-",$this->pem_tgl)[0]
            );
            return insertrow($this->tbl_name,$data);
        }
        else{
            return false;
        }
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_pembelian" => $this->id_pk_pembelian,
            );
            $data = array(
                "pem_pk_nomor" => $this->pem_pk_nomor,
                "pem_tgl" => $this->pem_tgl,
                "id_fk_supp" => $this->id_fk_supp,
                "pem_last_modified" => $this->pem_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function update_status(){
        if($this->check_update_status()){
            $where = array(
                "id_pk_pembelian" => $this->id_pk_pembelian,
            );
            $data = array(
                "pem_status" => $this->pem_status,
                "pem_last_modified" => $this->pem_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_pembelian" => $this->id_pk_pembelian,
            );
            $data = array(
                "pem_status" => "nonaktif",
                "pem_last_modified" => $this->pem_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        else{
            return false;
        }
    }
    public function check_insert(){
        if($this->pem_pk_nomor == ""){
            return false;
        }
        if($this->pem_tgl == ""){
            return false;
        }
        if($this->pem_status == ""){
            return false;
        }
        if($this->id_fk_supp == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->pem_create_date == ""){
            return false;
        }
        if($this->pem_last_modified == ""){
            return false;
        }
        if($this->id_create_data == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_update(){
        if($this->id_pk_pembelian == ""){
            return false;
        }
        if($this->pem_pk_nomor == ""){
            return false;
        }
        if($this->pem_tgl == ""){
            return false;
        }
        if($this->id_fk_supp == ""){
            return false;
        }
        if($this->pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_pembelian == ""){
            return false;
        }
        if($this->pem_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_update_status(){
        if($this->id_pk_pembelian == ""){
            return false;
        }
        if($this->pem_last_modified == ""){
            return false;
        }
        if($this->pem_status == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($pem_pk_nomor,$pem_tgl,$pem_status,$id_fk_supp,$id_fk_cabang){
        if(!$this->set_pem_pk_nomor($pem_pk_nomor)){
            return false;
        }
        if(!$this->set_pem_tgl($pem_tgl)){
            return false;
        }
        if(!$this->set_pem_status($pem_status)){
            return false;
        }
        if(!$this->set_id_fk_supp($id_fk_supp)){
            return false;
        }
        if(!$this->set_id_fk_cabang($id_fk_cabang)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_pembelian,$pem_pk_nomor,$pem_tgl,$id_fk_supp){
        if(!$this->set_id_pk_pembelian($id_pk_pembelian)){
            return false;
        }
        if(!$this->set_pem_pk_nomor($pem_pk_nomor)){
            return false;
        }
        if(!$this->set_pem_tgl($pem_tgl)){
            return false;
        }
        if(!$this->set_id_fk_supp($id_fk_supp)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_pembelian){
        if(!$this->set_id_pk_pembelian($id_pk_pembelian)){
            return false;
        }
        return true;
    }
    public function set_update_status($id_pk_pembelian,$status){
        if(!$this->set_id_pk_pembelian($id_pk_pembelian)){
            return false;
        }
        if(!$this->set_pem_status($status)){
            return false;
        }
        return true;
    }
    public function set_id_pk_pembelian($id_pk_pembelian){
        if($id_pk_pembelian != ""){
            $this->id_pk_pembelian = $id_pk_pembelian;
            return true;
        }
        return false;
    }
    public function set_pem_tgl($pem_tgl){
        if($pem_tgl != ""){
            $this->pem_tgl = $pem_tgl;
            return true;
        }
        return false;
    }
    public function set_pem_pk_nomor($pem_pk_nomor){
        if($pem_pk_nomor != ""){
            $this->pem_pk_nomor = $pem_pk_nomor;
            return true;
        }
        return false;
    }
    public function set_pem_status($pem_status){
        if($pem_status != ""){
            $this->pem_status = $pem_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_supp($id_fk_supp){
        if($id_fk_supp != ""){
            $this->id_fk_supp = $id_fk_supp;
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
    public function get_pem_nomor($id_fk_cabang,$jenis_transaksi,$custom_tgl = "-"){
        $this->db->trans_start();
        executeQuery("call generate_trans_no(".$id_fk_cabang.",'".$jenis_transaksi."','".$custom_tgl."',@transno,@latest_no);");
        $result = executeQuery("select @transno,@latest_no;");
        $this->db->trans_complete(); 
        $result = $result->result_array();
        $this->no_control = $result[0]["@latest_no"];
        return $result[0]["@transno"];
    }
    public function data_excel(){
        $query = "
        select id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,sup_perusahaan,pem_last_modified,toko_nama,cabang_daerah
        from ".$this->tbl_name." 
        inner join mstr_supplier on mstr_supplier.id_pk_sup = ".$this->tbl_name.".id_fk_supp
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where pem_status = ? and sup_status = ? and cabang_status = ? and toko_status = ? and id_fk_cabang = ?";
        $args = array(
            "aktif","aktif","aktif","aktif",$this->session->id_cabang
        );
        return executequery($query,$args);
    }
    public function columns_excel(){
        $this->columns = array();
        $this->set_column("pem_pk_nomor","nomor pembelian",true);
        $this->set_column("pem_tgl","tanggal pembelian",false);
        $this->set_column("pem_status","status",false);
        $this->set_column("sup_perusahaan","supplier",false);
        $this->set_column("pem_last_modified","last modified",false);
        return $this->columns;
         
    }
}