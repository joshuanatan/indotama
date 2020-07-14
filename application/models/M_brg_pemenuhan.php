<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_brg_pemenuhan extends ci_model{
    private $tbl_name = "tbl_brg_pemenuhan";
    private $columns = array();
    private $id_pk_brg_pemenuhan;
    private $brg_pemenuhan_qty;
    private $id_fk_brg_permintaan;
    private $id_fk_cabang;
    private $id_fk_warehouse;
    private $brg_pemenuhan_tipe;
    private $brg_pemenuhan_create_date;
    private $brg_pemenuhan_last_modified;
    private $brg_pemenuhan_status;
    private $id_create_data;
    private $id_last_modified;
    private $today;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_permintaan_create_date","tanggal permintaan",true);
        $this->set_column("cabang_daerah","cabang peminta",false);
        $this->set_column("brg_image","gambar barang",false);
        $this->set_column("brg_nama","nama barang",false);
        $this->set_column("brg_permintaan_qty","jumlah terpenuhi",false);
        $this->set_column("brg_permintaan_qty","jumlah permintaan",false);
        $this->set_column("brg_permintaan_status","status permintaan",false);
        $this->brg_pemenuhan_create_date = date("y-m-d h:i:s");
        $this->brg_pemenuhan_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
        $this->today = date("y-m-d");
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
    public function install(){
        $sql = "
        drop table if exists tbl_brg_pemenuhan;
        create table tbl_brg_pemenuhan(
            id_pk_brg_pemenuhan int primary key auto_increment,
            brg_pemenuhan_qty int,
            brg_pemenuhan_tipe varchar(9) comment 'warehouse/cabang',
            brg_pemenuhan_status varchar(8) comment 'aktif/nonaktif',
            id_fk_brg_permintaan int,
            id_fk_cabang int,
            id_fk_warehouse int,
            brg_pemenuhan_create_date datetime,
            brg_pemenuhan_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_pemenuhan_log;
        create table tbl_brg_pemenuhan_log(
            id_pk_brg_pemenuhan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_pemenuhan int,
            brg_pemenuhan_qty int,
            brg_pemenuhan_tipe varchar(9) comment 'warehouse/cabang',
            brg_pemenuhan_status varchar(8) comment 'aktif/nonaktif',
            id_fk_brg_permintaan int,
            id_fk_cabang int,
            id_fk_warehouse int,
            brg_pemenuhan_create_date datetime,
            brg_pemenuhan_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int 
        );
        drop trigger if exists trg_after_insert_brg_pemenuhan;
        delimiter $$
        create trigger trg_after_insert_brg_pemenuhan
        after insert on tbl_brg_pemenuhan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pemenuhan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_pemenuhan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pemenuhan_log(executed_function,
            id_pk_brg_pemenuhan,
            brg_pemenuhan_qty,
            brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            id_fk_brg_permintaan,
            id_fk_cabang,
            id_fk_warehouse,
            brg_pemenuhan_create_date,
            brg_pemenuhan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_pemenuhan,
            new.brg_pemenuhan_qty,
            new.brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            new.id_fk_brg_permintaan,
            new.id_fk_cabang,
            new.id_fk_warehouse,
            new.brg_pemenuhan_create_date,
            new.brg_pemenuhan_last_modified,
            new.id_create_data,
            new.id_last_modified
            ,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_brg_pemenuhan;
        delimiter $$
        create trigger trg_after_update_brg_pemenuhan
        after update on tbl_brg_pemenuhan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pemenuhan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_pemenuhan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pemenuhan_log(executed_function,
            id_pk_brg_pemenuhan,
            brg_pemenuhan_qty,
            brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            id_fk_brg_permintaan,
            id_fk_cabang,
            id_fk_warehouse,
            brg_pemenuhan_create_date,
            brg_pemenuhan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_pemenuhan,
            new.brg_pemenuhan_qty,
            new.brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            new.id_fk_brg_permintaan,
            new.id_fk_cabang,
            new.id_fk_warehouse,
            new.brg_pemenuhan_create_date,
            new.brg_pemenuhan_last_modified,
            new.id_create_data,
            new.id_last_modified
            ,@id_log_all);
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
                id_pk_brg_permintaan like '%".$search_key."%' or
                brg_permintaan_qty like '%".$search_key."%' or
                brg_permintaan_notes like '%".$search_key."%' or
                brg_permintaan_deadline like '%".$search_key."%' or
                brg_permintaan_status like '%".$search_key."%' or
                brg_permintaan_create_date like '%".$search_key."%' or
                brg_permintaan_last_modified like '%".$search_key."%' or
                brg_nama like '%".$search_key."%' or
                brg_image like '%".$search_key."%'
            )";
        }
        if(strtoupper($this->brg_pemenuhan_tipe) == "CABANG"){
            $query = "
            select id_pk_brg_permintaan, brg_permintaan_deadline, brg_permintaan_qty, brg_permintaan_status,brg_cabang_qty,brg_permintaan_notes,brg_image,tbl_brg_permintaan.id_fk_brg as id_mstr_barang_cabang_peminta, tbl_brg_permintaan.id_fk_cabang as id_cabang_peminta, brg_cabang_status,tbl_brg_cabang.id_fk_brg as id_mstr_barang_cabang_penyedia, brg_permintaan_create_date,tbl_brg_cabang.id_fk_cabang as id_cabang_penyedia,brg_nama, ifnull(sum(tbl_brg_pemenuhan.brg_pemenuhan_qty),0) as qty_pemenuhan,cabang_daerah,mstr_toko.toko_nama, mstr_toko.toko_kode
            from tbl_brg_permintaan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = tbl_brg_permintaan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg
            inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = mstr_barang.id_pk_brg
            left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan and brg_pemenuhan_status != 'nonaktif'
            where tbl_brg_cabang.id_fk_cabang = ?
            and tbl_brg_permintaan.id_fk_cabang != ?
            and tbl_brg_cabang.brg_cabang_status = ?
            and mstr_barang.brg_status = ?
            and tbl_brg_permintaan.brg_permintaan_status != ?
            and tbl_brg_permintaan.brg_permintaan_deadline > current_date() ".$search_query."
            group by id_pk_brg_permintaan
            order by ".$order_by." ".$order_direction." 
            limit 20 offset ".($page-1)*$data_per_page;
            $args = array(
                $this->session->id_cabang,$this->session->id_cabang,'aktif','aktif','batal'
            );
            $result["data"] = executequery($query,$args);
            $query = "
            select id_pk_brg_permintaan
            from tbl_brg_permintaan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = tbl_brg_permintaan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg
            inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = mstr_barang.id_pk_brg
            left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan and brg_pemenuhan_status != 'nonaktif'
            where tbl_brg_cabang.id_fk_cabang = ?
            and tbl_brg_permintaan.id_fk_cabang != ?
            and tbl_brg_cabang.brg_cabang_status = ?
            and mstr_barang.brg_status = ?
            and tbl_brg_permintaan.brg_permintaan_status != ?
            and tbl_brg_permintaan.brg_permintaan_deadline > current_date() ".$search_query."
            group by id_pk_brg_permintaan
            order by ".$order_by." ".$order_direction;
            $result["total_data"] = executequery($query,$args)->num_rows();
        }
        else{
            $query = "
            select id_pk_brg_permintaan, brg_permintaan_deadline, brg_permintaan_qty, brg_permintaan_status,brg_warehouse_qty,brg_permintaan_notes,brg_image,tbl_brg_permintaan.id_fk_brg as id_mstr_barang_cabang_peminta, tbl_brg_permintaan.id_fk_cabang as id_cabang_peminta, brg_warehouse_status,tbl_brg_warehouse.id_fk_brg as id_mstr_barang_cabang_penyedia, brg_permintaan_create_date,tbl_brg_warehouse.id_fk_warehouse as id_warehouse_penyedia,brg_nama, ifnull(sum(tbl_brg_pemenuhan.brg_pemenuhan_qty),0) as qty_pemenuhan,cabang_daerah,mstr_toko.toko_nama, mstr_toko.toko_kode
            from tbl_brg_permintaan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = tbl_brg_permintaan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg
            inner join tbl_brg_warehouse on tbl_brg_warehouse.id_fk_brg = mstr_barang.id_pk_brg
            left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan and brg_pemenuhan_status != 'nonaktif'
            where tbl_brg_warehouse.id_fk_warehouse = ?
            and tbl_brg_warehouse.brg_warehouse_status = ?
            and mstr_barang.brg_status = ?
            and tbl_brg_permintaan.brg_permintaan_status != ?
            and tbl_brg_permintaan.brg_permintaan_deadline > current_date() ".$search_query."
            group by id_pk_brg_permintaan
            order by ".$order_by." ".$order_direction." 
            limit 20 offset ".($page-1)*$data_per_page;
            $args = array(
                $this->session->id_warehouse,'aktif','aktif','batal'
            );
            $result["data"] = executequery($query,$args);
            $query = "
            select id_pk_brg_permintaan
            from tbl_brg_permintaan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = tbl_brg_permintaan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg
            inner join tbl_brg_warehouse on tbl_brg_warehouse.id_fk_brg = mstr_barang.id_pk_brg
            left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan and brg_pemenuhan_status != 'nonaktif'
            where tbl_brg_warehouse.id_fk_warehouse = ?
            and tbl_brg_warehouse.brg_warehouse_status = ?
            and mstr_barang.brg_status = ?
            and tbl_brg_permintaan.brg_permintaan_status != ?
            and tbl_brg_permintaan.brg_permintaan_deadline > current_date() ".$search_query."
            group by id_pk_brg_permintaan
            order by ".$order_by." ".$order_direction;
            $result["total_data"] = executequery($query,$args)->num_rows();
        }
        
        return $result;
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_pemenuhan_qty" => $this->brg_pemenuhan_qty,
                "id_fk_brg_permintaan" => $this->id_fk_brg_permintaan,
                "brg_pemenuhan_tipe" => $this->brg_pemenuhan_tipe,
                "brg_pemenuhan_create_date" => $this->brg_pemenuhan_create_date,
                "brg_pemenuhan_last_modified" => $this->brg_pemenuhan_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            if(strtoupper($this->brg_pemenuhan_tipe) == "warehouse"){
                $data["id_fk_warehouse"] = $this->id_fk_warehouse;
            }
            else if(strtoupper($this->brg_pemenuhan_tipe) == "cabang"){
                $data["id_fk_cabang"] = $this->id_fk_cabang;
            }
            return insertrow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_brg_pemenuhan" => $this->id_pk_brg_pemenuhan
            );
            $data = array(
                "brg_pemenuhan_qty" => $this->brg_pemenuhan_qty,
                "brg_pemenuhan_last_modified" => $this->brg_pemenuhan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function update_status(){
        $where = array(
            "id_pk_brg_pemenuhan" => $this->id_pk_brg_pemenuhan
        );
        $data = array(
            "brg_pemenuhan_status" => $this->brg_pemenuhan_status,
            "brg_pemenuhan_last_modified" => $this->brg_pemenuhan_last_modified,
            "id_last_modified" => $this->id_last_modified
        );
        updateRow($this->tbl_name,$data,$where);
        return true;
    }
    public function list_pemenuhan(){
        $field = array(
            "id_pk_brg_pemenuhan",
            "brg_pemenuhan_last_modified",
            "brg_pemenuhan_qty",
            "brg_pemenuhan_status"
        );
        $where = array(
            "id_fk_brg_permintaan" => $this->id_fk_brg_permintaan,
            "brg_pemenuhan_status !=" => "nonaktif" 

        );
        if($this->id_fk_cabang){
            $where["id_fk_cabang"] = $this->id_fk_cabang;
        }
        else if($this->id_fk_warehouse){
            $where["id_fk_warehouse"] = $this->id_fk_warehouse;
        }
        return selectRow($this->tbl_name,$where,$field);
    }
    public function delete(){
        //belom
        if($this->check_delete()){
            $where = array(
                "id_pk_brg_pemenuhan" => $this->id_pk_brg_pemenuhan
            );
            $data = array(
                "brg_pemenuhan_status" => "nonaktif",
                "brg_pemenuhan_last_modified" => $this->brg_pemenuhan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){

        if($this->brg_pemenuhan_qty == ""){
            return false;
        }
        if($this->id_fk_brg_permintaan == ""){
            return false;
        }
        if($this->brg_pemenuhan_tipe == ""){
            return false;
        }
        if($this->brg_pemenuhan_create_date == ""){
            return false;
        }
        
        if(strtolower($this->brg_pemenuhan_tipe) == "warehouse"){
            if($this->id_fk_warehouse == ""){
                return false;
            }
        }
        else if(strtolower($this->brg_pemenuhan_tipe) == "cabang"){
            if($this->id_fk_cabang == ""){
                return false;
            }
        }
        if($this->brg_pemenuhan_last_modified == ""){
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
        //belom
        if($this->id_pk_brg_pemenuhan == ""){
            return false;
        }
        if($this->brg_pemenuhan_tgl == ""){
            return false;
        }
        if($this->brg_pemenuhan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_pemenuhan == ""){
            return false;
        }
        if($this->brg_pemenuhan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($brg_pemenuhan_qty,$id_fk_brg_permintaan,$brg_pemenuhan_tipe){
        //ceklagi
        if(!$this->set_brg_pemenuhan_qty($brg_pemenuhan_qty)){
            return false;
        }
        if(!$this->set_id_fk_brg_permintaan($id_fk_brg_permintaan)){
            return false;
        }
        if(!$this->set_brg_pemenuhan_tipe($brg_pemenuhan_tipe)){
            return false;
        }
        if(strtoupper($brg_pemenuhan_tipe) == "warehouse"){
            if(!$this->set_id_fk_warehouse($id_fk_warehouse)){
                return false;
            }
        }
        else if(strtoupper($brg_pemenuhan_tipe) == "cabang"){
            if(!$this->set_id_fk_cabang($id_fk_cabang)){
                return false;
            }
        }
        return true;
    }
    public function set_update($id_pk_brg_pemenuhan,$brg_pemenuhan_tgl){
        //belom
        if(!$this->set_id_pk_brg_pemenuhan($id_pk_brg_pemenuhan)){
            return false;
        }
        if(!$this->set_brg_pemenuhan_tgl($brg_pemenuhan_tgl)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_pemenuhan){
        if(!$this->set_id_pk_brg_pemenuhan($id_pk_brg_pemenuhan)){
            return false;
        }

        return true;
    }

    public function set_id_pk_brg_pemenuhan($id_pk_brg_pemenuhan){
        if($id_pk_brg_pemenuhan != ""){
            $this->id_pk_brg_pemenuhan = $id_pk_brg_pemenuhan;
            return true;
        }
        return false;
    }
    public function set_brg_pemenuhan_qty($brg_pemenuhan_qty){
        if($brg_pemenuhan_qty != ""){
            $this->brg_pemenuhan_qty = $brg_pemenuhan_qty;
            return true;
        }
        return false;
    }
    public function set_id_fk_brg_permintaan($id_fk_brg_permintaan){
        if($id_fk_brg_permintaan != ""){
            $this->id_fk_brg_permintaan = $id_fk_brg_permintaan;
            return true;
        }
        return false;
    }
    public function set_brg_pemenuhan_tipe($brg_pemenuhan_tipe){
        if($brg_pemenuhan_tipe != ""){
            $this->brg_pemenuhan_tipe = $brg_pemenuhan_tipe;
            return true;
        }
        return false;
    }

    public function set_brg_pemenuhan_status($brg_pemenuhan_status){
        if($brg_pemenuhan_status != ""){
            $this->brg_pemenuhan_status = $brg_pemenuhan_status;
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

    public function set_id_fk_warehouse($id_fk_warehouse){
        if($id_fk_warehouse != ""){
            $this->id_fk_warehouse = $id_fk_warehouse;
            return true;
        }
        return false;
    }
    
    
    public function get_brg_pemenuhan_qty(){
        return $this->brg_pemenuhan_qty;
    }
    public function get_id_fk_brg_permintaan(){
        return $this->id_fk_brg_permintaan;
    }
    public function get_brg_pemenuhan_tipe(){
        return $this->brg_pemenuhan_tipe;
    }
}