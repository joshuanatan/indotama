<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");

class M_retur_brg extends CI_Model{
    private $tbl_name = "tbl_retur_brg";
    private $columns = array();
    private $id_pk_retur_brg;
    private $id_fk_retur;
    private $id_fk_brg;
    private $retur_brg_qty;
    private $retur_brg_satuan;
    private $retur_brg_notes;
    private $retur_brg_status;
    private $retur_create_date;
    private $retur_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->retur_create_date = date("y-m-d h:i:s");
        $this->retur_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function install(){
        $sql = "
        drop table if exists tbl_retur_brg;
        create table tbl_retur_brg(
            id_pk_retur_brg int primary key auto_increment,
            id_fk_retur int,
            id_fk_brg int,
            retur_brg_qty double,
            retur_brg_satuan varchar(30),
            retur_brg_notes varchar(100),
            retur_brg_status varchar(15),
            retur_create_date datetime,
            retur_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_retur_brg_log;
        create table tbl_retur_brg_log(
            id_pk_retur_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_retur_brg int,
            id_fk_retur int,
            id_fk_brg int,
            retur_brg_qty double,
            retur_brg_satuan varchar(30),
            retur_brg_notes varchar(100),
            retur_brg_status varchar(15),
            retur_create_date datetime,
            retur_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_retur;
        delimiter $$
        create trigger trg_after_insert_retur
        after insert on tbl_retur_brg
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_brg_log(executed_function,id_pk_retur_brg,id_fk_retur,id_fk_brg,retur_brg_qty,retur_brg_satuan,retur_brg_notes,retur_brg_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_retur_brg,new.id_fk_retur,new.id_fk_brg,new.retur_brg_qty,new.retur_brg_satuan,new.retur_brg_notes,new.retur_brg_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_retur;
        delimiter $$
        create trigger trg_after_update_retur
        after update on tbl_retur_brg
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_brg_log(executed_function,id_pk_retur_brg,id_fk_retur,id_fk_brg,retur_brg_qty,retur_brg_satuan,retur_brg_notes,retur_brg_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_retur_brg,new.id_fk_retur,new.id_fk_brg,new.retur_brg_qty,new.retur_brg_satuan,new.retur_brg_notes,new.retur_brg_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function list_data(){
        $sql = "
        select 
            id_pk_retur_brg,
            retur_brg_qty,
            retur_brg_notes,
            retur_brg_satuan,
            retur_brg_status,
            brg_nama,
            jumlah_terkirim as brg_terkirim,
            satuan_nama as satuan_kirim,
            brg_penjualan_qty as brg_beli,
            brg_penjualan_satuan as satuan_beli
        from tbl_retur_brg
        inner join mstr_retur on mstr_retur.id_pk_retur= tbl_retur_brg.id_fk_retur 
        inner join (
            select 
            id_pk_brg_penjualan,
            brg_penjualan_qty_real,
            brg_penjualan_satuan_real,
            brg_penjualan_qty,
            brg_penjualan_satuan,
            brg_penjualan_harga,
            brg_penjualan_note,
            id_fk_penjualan,
            id_fk_barang,
            brg_nama,
            brg_harga,
            brg_penjualan_create_date,
            brg_penjualan_last_modified, 
            satuan_nama,
            ifnull(sum(brg_pengiriman_qty),0) as jumlah_terkirim
            from tbl_brg_penjualan
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang
            left join tbl_brg_pengiriman on tbl_brg_pengiriman.id_fk_brg_penjualan = tbl_brg_penjualan.id_pk_brg_penjualan and brg_pengiriman_qty > 0
            left join mstr_satuan on mstr_satuan.id_pk_satuan = tbl_brg_pengiriman.id_fk_satuan
            where brg_penjualan_status = 'aktif' and brg_status = 'aktif'
            group by id_pk_brg_penjualan
        ) as a on a.id_fk_barang = tbl_retur_brg.id_fk_brg and a.id_fk_penjualan = mstr_retur.id_fk_penjualan
        where id_fk_retur = ?   
        ";
        $args = array(
            $this->id_fk_retur
        );
        return executeQuery($sql,$args);
    }
    public function columns(){
        return $this->columns;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "id_fk_retur" => $this->id_fk_retur,
                "id_fk_brg" => $this->id_fk_brg,
                "retur_brg_qty" => $this->retur_brg_qty,
                "retur_brg_satuan" => $this->retur_brg_satuan,
                "retur_brg_notes" => $this->retur_brg_notes,
                "retur_brg_status" => $this->retur_brg_status,
                "retur_create_date" => $this->retur_create_date,
                "retur_last_modified" => $this->retur_last_modified,
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
                "id_pk_retur_brg" => $this->id_pk_retur_brg,
            );
            $data = array(
                "id_fk_brg" => $this->id_fk_brg,
                "retur_brg_qty" => $this->retur_brg_qty,
                "retur_brg_satuan" => $this->retur_brg_satuan,
                "retur_brg_notes" => $this->retur_brg_notes,
                "retur_last_modified" => $this->retur_last_modified,
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
                "id_pk_retur_brg" => $this->id_pk_retur_brg,
            );
            $data = array(
                "retur_brg_status" => "nonaktif",
                "retur_last_modified" => $this->retur_last_modified,
                "id_last_modified" => $this->id_last_modified,
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->id_fk_retur == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->retur_brg_qty == ""){
            return false;
        }
        if($this->retur_brg_satuan == ""){
            return false;
        }
        if($this->retur_brg_status == ""){
            return false;
        }
        if($this->retur_create_date == ""){
            return false;
        }
        if($this->retur_last_modified == ""){
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
        if($this->id_pk_retur_brg == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->retur_brg_qty == ""){
            return false;
        }
        if($this->retur_brg_satuan == ""){
            return false;
        }
        if($this->retur_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_retur_brg == ""){
            return false;
        }
        if($this->retur_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($id_fk_retur,$id_fk_brg,$retur_brg_qty,$retur_brg_satuan,$retur_brg_status,$retur_brg_notes){
        if(!$this->set_id_fk_retur($id_fk_retur)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        if(!$this->set_retur_brg_qty($retur_brg_qty)){
            return false;
        }
        if(!$this->set_retur_brg_satuan($retur_brg_satuan)){
            return false;
        }
        if(!$this->set_retur_brg_notes($retur_brg_notes)){
            return false;
        }
        if(!$this->set_retur_brg_status($retur_brg_status)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_retur_brg,$id_fk_brg,$retur_brg_qty,$retur_brg_satuan,$retur_brg_notes){
        if(!$this->set_id_pk_retur_brg($id_pk_retur_brg)){
            return false;
        }
        if(!$this->set_id_fk_brg($id_fk_brg)){
            return false;
        }
        if(!$this->set_retur_brg_qty($retur_brg_qty)){
            return false;
        }
        if(!$this->set_retur_brg_satuan($retur_brg_satuan)){
            return false;
        }
        if(!$this->set_retur_brg_notes($retur_brg_notes)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_retur_brg){
        if(!$this->set_id_pk_retur_brg($id_pk_retur_brg)){
            return false;
        }
        return true;
    }
    public function get_id_pk_retur_brg(){
        return $this->id_pk_retur_brg;
    }
    public function get_id_fk_retur(){
        return $this->id_fk_retur;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
    public function get_retur_brg_qty(){
        return $this->retur_brg_qty;
    }
    public function get_retur_brg_satuan(){
        return $this->retur_brg_satuan;
    }
    public function get_retur_brg_notes(){
        return $this->retur_brg_notes;
    }
    public function get_retur_brg_status(){
        return $this->retur_brg_status;
    }
    public function set_id_pk_retur_brg($id_pk_retur_brg){
        if($id_pk_retur_brg != ""){
            $this->id_pk_retur_brg = $id_pk_retur_brg;
            return true;
        }
        return false;
    }
    public function set_id_fk_retur($id_fk_retur){
        if($id_fk_retur != ""){
            $this->id_fk_retur = $id_fk_retur;
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
    public function set_retur_brg_qty($retur_brg_qty){
        if($retur_brg_qty != ""){
            $this->retur_brg_qty = $retur_brg_qty;
            return true;
        }
        return false;
    }
    public function set_retur_brg_satuan($retur_brg_satuan){
        if($retur_brg_satuan != ""){
            $this->retur_brg_satuan = $retur_brg_satuan;
            return true;
        }
        return false;
    }
    public function set_retur_brg_notes($retur_brg_notes){
        if($retur_brg_notes != ""){
            $this->retur_brg_notes = $retur_brg_notes;
            return true;
        }
        return false;
    }
    public function set_retur_brg_status($retur_brg_status){
        if($retur_brg_status != ""){
            $this->retur_brg_status = $retur_brg_status;
            return true;
        }
        return false;
    }
}