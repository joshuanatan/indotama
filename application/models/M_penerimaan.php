<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_penerimaan extends ci_model{
    private $tbl_name = "mstr_penerimaan";
    private $columns = array();
    private $id_pk_penerimaan;
    private $penerimaan_tgl;
    private $penerimaan_status;
    private $id_fk_pembelian;
    private $id_fk_retur;
    private $penerimaan_tempat;
    private $id_fk_warehouse;
    private $id_fk_cabang;
    private $penerimaan_create_date;
    private $penerimaan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->penerimaan_create_date = date("y-m-d h:i:s");
        $this->penerimaan_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists mstr_penerimaan;
        create table mstr_penerimaan(
            id_pk_penerimaan int primary key auto_increment,
            penerimaan_tgl datetime, 
            penerimaan_status varchar(15), 
            id_fk_pembelian int, 
            id_fk_retur int, 
            penerimaan_tempat varchar(30) comment 'warehouse/cabang', 
            id_fk_warehouse int, 
            id_fk_cabang int, 
            penerimaan_create_date datetime, 
            penerimaan_last_modified datetime, 
            id_create_data int, 
            id_last_modified int 
        );
        drop table if exists mstr_penerimaan_log;
        create table mstr_penerimaan_log(
            id_pk_penerimaan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_penerimaan int,
            penerimaan_tgl datetime, 
            penerimaan_status varchar(15), 
            id_fk_pembelian int, 
            id_fk_retur int, 
            penerimaan_tempat varchar(30) comment 'warehouse/cabang', 
            id_fk_warehouse int, 
            id_fk_cabang int, 
            penerimaan_create_date datetime, 
            penerimaan_last_modified datetime, 
            id_create_data int, 
            id_last_modified int, 
            id_log_all int 
        );
        drop trigger if exists trg_after_insert_penerimaan;
        delimiter $$
        create trigger trg_after_insert_penerimaan
        after insert on mstr_penerimaan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penerimaan_log(executed_function,id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_retur,id_fk_pembelian,penerimaan_tempat,id_fk_warehouse,id_fk_cabang,penerimaan_create_date,penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penerimaan,new.penerimaan_tgl,new.penerimaan_status,new.id_fk_retur,new.id_fk_pembelian,new.penerimaan_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.penerimaan_create_date,new.penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_penerimaan;
        delimiter $$
        create trigger trg_after_update_penerimaan
        after update on mstr_penerimaan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penerimaan_log(executed_function,id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_retur,id_fk_pembelian,penerimaan_tempat,id_fk_warehouse,id_fk_cabang,penerimaan_create_date,penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penerimaan,new.penerimaan_tgl,new.penerimaan_status,new.id_fk_retur,new.id_fk_pembelian,new.penerimaan_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.penerimaan_create_date,new.penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    public function columns($tipe = "pembelian"){
        if($tipe == "pembelian"){
            $this->column_penerimaan_pembelian();
        }
        else if($tipe == "retur"){
            $this->column_penerimaan_retur();
        }
        return $this->columns;
    }
    private function column_penerimaan_pembelian(){
        $this->columns = array();
        $this->set_column("penerimaan_tgl","tanggal penerimaan",true);
        $this->set_column("pem_pk_nomor","nomor pembelian",false);
        $this->set_column("penerimaan_status","status",false);
        $this->set_column("penerimaan_last_modified","last modified",false);
    }
    private function column_penerimaan_retur(){
        $this->columns = array();
        $this->set_column("penerimaan_tgl","tanggal penerimaan",true);
        $this->set_column("retur_no","nomor retur",false);
        $this->set_column("penerimaan_status","status",false);
        $this->set_column("penerimaan_last_modified","last modified",false);
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = "",$tipe_penerimaan = "pembelian"){
        if($tipe_penerimaan == "pembelian"){
            $this->column_penerimaan_pembelian();
            $order_by = $this->columns[$order_by]["col_name"];
            $result = $this->content_pembelian($page,$order_by,$order_direction,$search_key,$data_per_page,$tipe_penerimaan);
        }
        else if($tipe_penerimaan == "retur"){
            $this->column_penerimaan_retur();
            $order_by = $this->columns[$order_by]["col_name"];
            $result = $this->content_retur($page,$order_by,$order_direction,$search_key,$data_per_page,$tipe_penerimaan);
        }
        return $result;
    }
    private function content_pembelian($page,$order_by,$order_direction,$search_key,$data_per_page){
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
    private function content_retur($page,$order_by,$order_direction,$search_key,$data_per_page){
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
            select id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_pembelian,penerimaan_tempat,".$this->tbl_name.".id_fk_warehouse,".$this->tbl_name.".id_fk_cabang,penerimaan_last_modified,penj_nomor,retur_no
            from ".$this->tbl_name." 
            inner join mstr_retur on mstr_retur.id_pk_retur = ".$this->tbl_name.".id_fk_retur
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            where penerimaan_status = ? and retur_status = ? and cabang_status = ? and toko_status = ? and ".$this->tbl_name.".id_fk_cabang = ? ".$search_query."  
            order by ".$order_by." ".$order_direction." 
            limit 20 offset ".($page-1)*$data_per_page;
            $args = array(
                "aktif","aktif","aktif","aktif",$this->id_fk_cabang
            );
            $result["data"] = executequery($query,$args);
            $query = "
            select id_pk_penerimaan
            from ".$this->tbl_name." 
            inner join mstr_retur on mstr_retur.id_pk_retur = ".$this->tbl_name.".id_fk_retur
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            where penerimaan_status = ? and retur_status = ? and cabang_status = ? and toko_status = ? and ".$this->tbl_name.".id_fk_cabang = ? ".$search_query."  
            order by ".$order_by." ".$order_direction;
            $result["total_data"] = executequery($query,$args)->num_rows();
        }
        else{
            $query = "
            select id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_retur,penerimaan_tempat,".$this->tbl_name.".id_fk_warehouse,".$this->tbl_name.".id_fk_cabang,penerimaan_last_modified,retur_no
            from ".$this->tbl_name." 
            inner join mstr_retur on mstr_retur.id_pk_retur = ".$this->tbl_name.".id_fk_retur
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
                "penerimaan_tgl" => $this->penerimaan_tgl,
                "penerimaan_status" => $this->penerimaan_status,
                "id_fk_pembelian" => $this->id_fk_pembelian,
                "id_fk_retur" => $this->id_fk_retur,
                "penerimaan_tempat" => $this->penerimaan_tempat,
                "penerimaan_create_date" => $this->penerimaan_create_date,
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            if(strtolower($this->penerimaan_tempat) == "warehouse"){
                $data["id_fk_warehouse"] = $this->id_fk_warehouse;
            }
            else if(strtolower($this->penerimaan_tempat) == "cabang"){
                $data["id_fk_cabang"] = $this->id_fk_cabang;
            }
            return insertrow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_penerimaan" => $this->id_pk_penerimaan
            );
            $data = array(
                "penerimaan_tgl" => $this->penerimaan_tgl,
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
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
                "id_pk_penerimaan" => $this->id_pk_penerimaan
            );
            $data = array(
                "penerimaan_status" => "nonaktif",
                "penerimaan_last_modified" => $this->penerimaan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->penerimaan_tgl == ""){
            return false;
        }
        if($this->penerimaan_status == ""){
            return false;
        }
        if(strtolower($this->penerimaan_tempat) == ""){
            return false;
        }
        
        if(strtolower($this->penerimaan_tempat) == "warehouse"){
            if($this->id_fk_warehouse == ""){
                return false;
            }
        }
        else if(strtolower($this->penerimaan_tempat) == "cabang"){
            if($this->id_fk_cabang == ""){
                return false;
            }
        }
        if($this->penerimaan_create_date == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
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
        if($this->id_pk_penerimaan == ""){
            return false;
        }
        if($this->penerimaan_tgl == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_penerimaan == ""){
            return false;
        }
        if($this->penerimaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($penerimaan_tgl,$penerimaan_status,$id_fk_pembelian = "", $penerimaan_tempat,$id_tempat_penerimaan, $id_fk_retur = ""){
        #id_fk_retur ditaro dibelakang supaya ga ngerusakin yang sudah ada
        if(!$this->set_penerimaan_tgl($penerimaan_tgl)){
            return false;
        }
        if(!$this->set_penerimaan_status($penerimaan_status)){
            return false;
        }
        $this->id_fk_pembelian = $id_fk_pembelian;
        $this->id_fk_retur = $id_fk_retur;
        if(!$this->set_penerimaan_tempat($penerimaan_tempat)){
            return false;
        }
        if(strtolower($penerimaan_tempat) == "warehouse"){
            if(!$this->set_id_fk_warehouse($id_tempat_penerimaan)){
                return false;
            }
        }
        else if(strtolower($penerimaan_tempat) == "cabang"){
            if(!$this->set_id_fk_cabang($id_tempat_penerimaan)){
                return false;
            }
        }
        return true;
    }
    public function set_update($id_pk_penerimaan,$penerimaan_tgl){
        if(!$this->set_id_pk_penerimaan($id_pk_penerimaan)){
            return false;
        }
        if(!$this->set_penerimaan_tgl($penerimaan_tgl)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_penerimaan){
        if(!$this->set_id_pk_penerimaan($id_pk_penerimaan)){
            return false;
        }

        return true;
    }
    public function set_id_pk_penerimaan($id_pk_penerimaan){
        if($id_pk_penerimaan != ""){
            $this->id_pk_penerimaan = $id_pk_penerimaan;
            return true;
        }
        return false;
    }
    public function set_penerimaan_tgl($penerimaan_tgl){
        if($penerimaan_tgl != ""){
            $this->penerimaan_tgl = $penerimaan_tgl;
            return true;
        }
        return false;
    }
    public function set_penerimaan_status($penerimaan_status){
        if($penerimaan_status != ""){
            $this->penerimaan_status = $penerimaan_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_pembelian($id_fk_pembelian){
        if($id_fk_pembelian != ""){
            $this->id_fk_pembelian = $id_fk_pembelian;
            return true;
        }
        return false;
    }
    public function set_penerimaan_tempat($penerimaan_tempat){
        if($penerimaan_tempat != ""){
            $this->penerimaan_tempat = $penerimaan_tempat;
            return true;
        }
        return false;
    }
    public function set_id_fk_warehouse($id_fk_warehouse){
        if($id_fk_warehouse != ""){
            $this->id_fk_warehouse = $id_fk_warehouse;
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
    public function get_id_pk_penerimaan(){
        return $this->id_pk_penerimaan;
    }
    public function get_penerimaan_tgl(){
        return $this->penerimaan_tgl;
    }
    public function get_penerimaan_status(){
        return $this->penerimaan_status;
    }
    public function get_id_fk_pembelian(){
        return $this->id_fk_pembelian;
    }
    public function get_penerimaan_tempat(){
        return $this->penerimaan_tempat;
    }
    public function get_id_fk_warehouse(){
        return $this->id_fk_warehouse;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
}