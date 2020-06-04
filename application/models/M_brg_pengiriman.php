<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_brg_pengiriman extends ci_model{
    private $tbl_name = "tbl_brg_pengiriman";
    private $columns = array();
    private $id_pk_brg_pengiriman;
    private $brg_pengiriman_qty;
    private $brg_pengiriman_note;
    private $id_fk_pengiriman;
    private $id_fk_brg_penjualan;
    private $id_fk_satuan;
    private $brg_pengiriman_create_date;
    private $brg_pengiriman_last_modified;
    private $id_create_data;
    private $id_last_modified;
    
    public function __construct(){
        parent::__construct();
        $this->brg_pengiriman_create_date = date("y-m-d h:i:s");
        $this->brg_pengiriman_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "
        drop table if exists tbl_brg_pengiriman;
        create table tbl_brg_pengiriman(
            id_pk_brg_pengiriman int primary key auto_increment,
            brg_pengiriman_qty double,
            brg_pengiriman_note varchar(200),
            id_fk_pengiriman int,
            id_fk_brg_penjualan int,
            id_fk_satuan int,
            brg_pengiriman_create_date datetime,
            brg_pengiriman_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_pengiriman_log;
        create table tbl_brg_pengiriman_log(
            id_pk_brg_pengiriman_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_pengiriman int,
            brg_pengiriman_qty double,
            brg_pengiriman_note varchar(200),
            id_fk_pengiriman int,
            id_fk_brg_penjualan int,
            id_fk_satuan int,
            brg_pengiriman_create_date datetime,
            brg_pengiriman_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_pengiriman;
        delimiter $$
        create trigger trg_after_insert_brg_pengiriman
        after insert on tbl_brg_pengiriman
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pengiriman_log(executed_function,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,id_fk_pengiriman,id_fk_brg_penjualan,id_fk_satuan,brg_pengiriman_create_date,brg_pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pengiriman,new.brg_pengiriman_qty,new.brg_pengiriman_note,new.id_fk_pengiriman,new.id_fk_brg_penjualan,new.id_fk_satuan,new.brg_pengiriman_create_date,new.brg_pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            set @id_cabang = 0;
            set @id_barang = 0;
            set @brg_pengiriman_qty = new.brg_pengiriman_qty;
            set @id_satuan_kirim = new.id_fk_satuan;
            
            select id_fk_cabang, id_fk_barang into @id_cabang, @id_barang 
            from tbl_brg_pengiriman
            inner join tbl_brg_penjualan on tbl_brg_penjualan.id_pk_brg_penjualan = tbl_brg_pengiriman.id_fk_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            call update_stok_barang_cabang(@id_barang,@id_cabang,0,0,@brg_pengiriman_qty,@id_satuan_kirim);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_brg_pengiriman;
        delimiter $$
        create trigger trg_after_update_brg_pengiriman
        after update on tbl_brg_pengiriman
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pengiriman_log(executed_function,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,id_fk_pengiriman,id_fk_brg_penjualan,id_fk_satuan,brg_pengiriman_create_date,brg_pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pengiriman,new.brg_pengiriman_qty,new.brg_pengiriman_note,new.id_fk_pengiriman,new.id_fk_brg_penjualan,new.id_fk_satuan,new.brg_pengiriman_create_date,new.brg_pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            set @id_cabang = 0;
            set @id_barang = 0;
            set @brg_keluar_qty = new.brg_pengiriman_qty;
            set @id_satuan_keluar = new.id_fk_satuan;
            set @brg_penerimaan_qty = old.brg_pengiriman_qty;
            set @id_satuan_terima = old.id_fk_satuan;

            select id_fk_cabang, id_fk_barang into @id_cabang, @id_barang 
            from tbl_brg_pengiriman
            inner join tbl_brg_penjualan on tbl_brg_penjualan.id_pk_brg_penjualan = tbl_brg_pengiriman.id_fk_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            call update_stok_barang_cabang(@id_barang,@id_cabang,@brg_penerimaan_qty,@id_satuan_terima,@brg_keluar_qty,@id_satuan_keluar);
        end$$
        delimiter ;
        ";
    }
    public function list(){
        $query = "
        select id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,id_fk_pengiriman,id_fk_brg_penjualan,id_fk_satuan,brg_pengiriman_create_date,brg_pengiriman_last_modified,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,satuan_nama,brg_nama,brg_kode,brg_minimal,brg_satuan
        from ".$this->tbl_name."
        inner join tbl_brg_penjualan on tbl_brg_penjualan.id_pk_brg_penjualan = ".$this->tbl_name.".id_fk_brg_penjualan
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang
        inner join mstr_satuan on mstr_satuan.id_pk_satuan = ".$this->tbl_name.".id_fk_satuan
        where id_fk_pengiriman = ? and brg_penjualan_status = ? and brg_status = ?
        ";
        $args = array(
            $this->id_fk_pengiriman,"aktif","aktif"
        );
        return executequery($query,$args);
    }
    public function insert(){
        $data = array(
            "brg_pengiriman_qty" => $this->brg_pengiriman_qty,
            "brg_pengiriman_note" => $this->brg_pengiriman_note,
            "id_fk_pengiriman" => $this->id_fk_pengiriman,
            "id_fk_brg_penjualan" => $this->id_fk_brg_penjualan,
            "id_fk_satuan" => $this->id_fk_satuan,
            "brg_pengiriman_create_date" => $this->brg_pengiriman_create_date,
            "brg_pengiriman_last_modified" => $this->brg_pengiriman_last_modified,
            "id_create_data" => $this->id_create_data,
            "id_last_modified" => $this->id_last_modified
        );
        return insertrow($this->tbl_name,$data);
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_brg_pengiriman" => $this->id_pk_brg_pengiriman
            );
            $data = array(
                "brg_pengiriman_qty" => $this->brg_pengiriman_qty,
                "brg_pengiriman_note" => $this->brg_pengiriman_note,
                "id_fk_satuan" => $this->id_fk_satuan,
                "brg_pengiriman_last_modified" => $this->brg_pengiriman_last_modified,
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
                "id_pk_brg_pengiriman" => $this->id_pk_brg_pengiriman
            );
            $data = array(
                "brg_pengiriman_last_modified" => $this->brg_pengiriman_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_pengiriman_qty == ""){
            return false;
        }
        if($this->brg_pengiriman_note == ""){
            return false;
        }
        if($this->id_fk_pengiriman == ""){
            return false;
        }
        if($this->id_fk_brg_penjualan == ""){
            return false;
        }
        if($this->id_fk_satuan == ""){
            return false;
        }
        if($this->brg_pengiriman_create_date == ""){
            return false;
        }
        if($this->brg_pengiriman_last_modified == ""){
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
        if($this->id_pk_brg_pengiriman == ""){
            return false;
        }
        if($this->brg_pengiriman_qty == ""){
            return false;
        }
        if($this->brg_pengiriman_note == ""){
            return false;
        }
        if($this->id_fk_satuan == ""){
            return false;
        }
        if($this->brg_pengiriman_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_pengiriman == ""){
            return false;
        }
        if($this->brg_pengiriman_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_pengiriman_qty,$brg_pengiriman_note,$id_fk_pengiriman,$id_fk_brg_penjualan,$id_fk_satuan){
        if(!$this->set_brg_pengiriman_qty($brg_pengiriman_qty)){
            return false;
        }
        if(!$this->set_brg_pengiriman_note($brg_pengiriman_note)){
            return false;
        }
        if(!$this->set_id_fk_pengiriman($id_fk_pengiriman)){
            return false;
        }
        if(!$this->set_id_fk_brg_penjualan($id_fk_brg_penjualan)){
            return false;
        }
        if(!$this->set_id_fk_satuan($id_fk_satuan)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_brg_pengiriman,$brg_pengiriman_qty,$brg_pengiriman_note,$id_fk_satuan){
        if(!$this->set_id_pk_brg_pengiriman($id_pk_brg_pengiriman)){
            return false;
        }
        if(!$this->set_brg_pengiriman_qty($brg_pengiriman_qty)){
            return false;
        }
        if(!$this->set_brg_pengiriman_note($brg_pengiriman_note)){
            return false;
        }
        if(!$this->set_id_fk_satuan($id_fk_satuan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_pengiriman){
        if(!$this->set_id_pk_brg_pengiriman($id_pk_brg_pengiriman)){
            return false;
        }
        return true;
    }
    public function set_id_pk_brg_pengiriman($id_pk_brg_pengiriman){
        $this->id_pk_brg_pengiriman = $id_pk_brg_pengiriman;
        return true;
    }
    public function set_brg_pengiriman_qty($brg_pengiriman_qty){
        $this->brg_pengiriman_qty = $brg_pengiriman_qty;
        return true;
    }
    public function set_brg_pengiriman_note($brg_pengiriman_note){
        $this->brg_pengiriman_note = $brg_pengiriman_note;
        return true;
    }
    public function set_id_fk_pengiriman($id_fk_pengiriman){
        $this->id_fk_pengiriman = $id_fk_pengiriman;
        return true;
    }
    public function set_id_fk_brg_penjualan($id_fk_brg_penjualan){
        $this->id_fk_brg_penjualan = $id_fk_brg_penjualan;
        return true;
    }
    public function set_id_fk_satuan($id_fk_satuan){
        $this->id_fk_satuan = $id_fk_satuan;
        return true;
    }
    public function get_id_pk_brg_pengiriman(){
        return $this->id_pk_brg_pengiriman;
    }
    public function get_brg_pengiriman_qty(){
        return $this->brg_pengiriman_qty;
    }
    public function get_brg_pengiriman_note(){
        return $this->brg_pengiriman_note;
    }
    public function get_id_fk_pengiriman(){
        return $this->id_fk_pengiriman;
    }
    public function get_id_fk_brg_penjualan(){
        return $this->id_fk_brg_penjualan;
    }
    public function get_id_fk_satuan(){
        return $this->id_fk_satuan;
    }
}