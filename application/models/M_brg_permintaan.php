<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_brg_permintaan extends ci_model{
    private $tbl_name = "tbl_brg_permintaan";
    private $columns = array();
    private $id_pk_brg_permintaan;
    private $brg_permintaan_qty;
    private $brg_permintaan_notes;
    private $brg_permintaan_deadline;
    private $brg_permintaan_status;
    private $id_fk_brg;
    private $id_fk_cabang;
    private $brg_permintaan_create_date;
    private $brg_permintaan_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->set_column("brg_permintaan_create_date","tanggal permintaan",true);
        $this->set_column("brg_nama","nama barang",false);
        $this->set_column("brg_permintaan_pemenuhan_qty","jumlah terpenuhi",false);
        $this->set_column("brg_permintaan_qty","total permintaan",false);
        $this->set_column("brg_permintaan_status","status permintaan",false);
        $this->brg_permintaan_create_date = date("y-m-d h:i:s");
        $this->brg_permintaan_last_modified = date("y-m-d h:i:s");
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
    public function install(){
        $sql = "
        drop table if exists tbl_brg_permintaan;
        create table tbl_brg_permintaan(
            id_pk_brg_permintaan int primary key auto_increment,
            brg_permintaan_qty int,
            brg_permintaan_notes text,
            brg_permintaan_deadline date,
            brg_permintaan_status varchar(7) comment 'BELUM/SEDANG/SUDAH/BATAL',
            id_fk_brg int,
            id_fk_cabang int,
            brg_permintaan_create_date datetime,
            brg_permintaan_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_permintaan_log;
        create table tbl_brg_permintaan_log(
            id_pk_penerimaan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_permintaan int,
            brg_permintaan_qty int,
            brg_permintaan_notes text,
            brg_permintaan_deadline date,
            brg_permintaan_status varchar(7) comment 'BELUM/SEDANG/SUDAH/BATAL',
            id_fk_brg int,
            id_fk_cabang int,
            brg_permintaan_create_date datetime,
            brg_permintaan_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int 
        );
        drop trigger if exists trg_after_insert_brg_permintaan;
        delimiter $$
        create trigger trg_after_insert_brg_permintaan
        after insert on tbl_brg_permintaan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_permintaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_permintaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_permintaan_log(executed_function,
            id_pk_brg_permintaan,
            brg_permintaan_qty,
            brg_permintaan_notes,
            brg_permintaan_deadline,
            brg_permintaan_status,
            id_fk_brg,
            id_fk_cabang,
            brg_permintaan_create_date,
            brg_permintaan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_permintaan,
            new.brg_permintaan_qty,
            new.brg_permintaan_notes,
            new.brg_permintaan_deadline,
            new.brg_permintaan_status,
            new.id_fk_brg,
            new.id_fk_cabang,
            new.brg_permintaan_create_date,
            new.brg_permintaan_last_modified,
            new.id_create_data,
            new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_brg_permintaan;
        delimiter $$
        create trigger trg_after_update_brg_permintaan
        after update on tbl_brg_permintaan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_permintaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_permintaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_permintaan_log(executed_function,
            id_pk_brg_permintaan,
            brg_permintaan_qty,
            brg_permintaan_notes,
            brg_permintaan_deadline,
            brg_permintaan_status,
            id_fk_brg,
            id_fk_cabang,
            brg_permintaan_create_date,
            brg_permintaan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_permintaan,
            new.brg_permintaan_qty,
            new.brg_permintaan_notes,
            new.brg_permintaan_deadline,
            new.brg_permintaan_status,
            new.id_fk_brg,
            new.id_fk_cabang,
            new.brg_permintaan_create_date,
            new.brg_permintaan_last_modified,
            new.id_create_data,
            new.id_last_modified,@id_log_all);
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
                id_fk_brg like '%".$search_key."%' or
                id_fk_cabang like '%".$search_key."%' or
                brg_permintaan_create_date like '%".$search_key."%' or
                brg_permintaan_last_modified like '%".$search_key."%' or
                brg_nama like '%".$search_key."%'
            )";
        }
        $query = "
        select id_pk_brg_permintaan, brg_permintaan_qty, brg_nama, brg_permintaan_notes, brg_permintaan_deadline, brg_permintaan_status, tbl_brg_permintaan.id_fk_brg, tbl_brg_permintaan.id_fk_cabang, brg_permintaan_create_date, brg_permintaan_last_modified, sum(tbl_brg_pemenuhan.brg_pemenuhan_qty) as qty_pemenuhan, cabang_daerah 
        from tbl_brg_permintaan 
        join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg and brg_status = 'aktif'
        join mstr_cabang on mstr_cabang.id_pk_cabang =tbl_brg_permintaan.id_fk_cabang 
        left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan 
        where tbl_brg_permintaan.id_fk_cabang = ? and tbl_brg_permintaan.brg_permintaan_status!='batal' group by id_pk_brg_permintaan ".$search_query." 
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;
        $args = array(
            $this->session->id_cabang
        );
        $result["data"] = executequery($query,$args);
        
        
        $query = "
        select id_pk_brg_permintaan, brg_permintaan_qty, brg_nama, brg_permintaan_notes, brg_permintaan_deadline, brg_permintaan_status, tbl_brg_permintaan.id_fk_brg, tbl_brg_permintaan.id_fk_cabang, brg_permintaan_create_date, brg_permintaan_last_modified, sum(tbl_brg_pemenuhan.brg_pemenuhan_qty) as qty_pemenuhan, cabang_daerah
        from tbl_brg_permintaan 
        join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg and brg_status = 'aktif'
        join mstr_cabang on mstr_cabang.id_pk_cabang =tbl_brg_permintaan.id_fk_cabang 
        left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan 
        where tbl_brg_permintaan.id_fk_cabang = ? and tbl_brg_permintaan.brg_permintaan_status!='batal' group by id_pk_brg_permintaan  ".$search_query." 
        order by ".$order_by." ".$order_direction;
        $args = array(
            $this->session->id_cabang
        );
        $result["total_data"] = executequery($query,$args)->num_rows();
        
        return $result;
    }
    public function list_permintaan(){
        $query = "
        select id_pk_brg_permintaan, brg_permintaan_qty, brg_nama, brg_permintaan_notes, brg_permintaan_deadline, brg_permintaan_status, tbl_brg_permintaan.id_fk_brg, tbl_brg_permintaan.id_fk_cabang, brg_permintaan_create_date, brg_permintaan_last_modified, sum(tbl_brg_pemenuhan.brg_pemenuhan_qty) as brg_permintaan_pemenuhan_qty, cabang_daerah 
        from tbl_brg_permintaan 
        join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg and brg_status = 'aktif'
        join mstr_cabang on mstr_cabang.id_pk_cabang =tbl_brg_permintaan.id_fk_cabang 
        left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan 
        where tbl_brg_permintaan.id_fk_cabang = ? and tbl_brg_permintaan.brg_permintaan_status!='batal' group by id_pk_brg_permintaan";
        $args = array(
            $this->session->id_cabang
        );
        return executeQuery($query,$args);
    }
    public function list_permintaan_aktif(){
        $query = "
        select id_pk_brg_permintaan, brg_permintaan_qty, brg_nama, brg_permintaan_notes, brg_permintaan_deadline, brg_permintaan_status, tbl_brg_permintaan.id_fk_brg, tbl_brg_permintaan.id_fk_cabang, brg_permintaan_create_date, brg_permintaan_last_modified, sum(tbl_brg_pemenuhan.brg_pemenuhan_qty) as qty_pemenuhan, cabang_daerah 
        from tbl_brg_permintaan 
        join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg and brg_status = 'aktif'
        join mstr_cabang on mstr_cabang.id_pk_cabang =tbl_brg_permintaan.id_fk_cabang 
        left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan 
        where tbl_brg_permintaan.id_fk_cabang = ? and (tbl_brg_permintaan.brg_permintaan_status!='batal' or tbl_brg_permintaan.brg_permintaan_status = 'selesai') group by id_pk_brg_permintaan 
        order by brg_permintaan_deadline ASC";
        $args = array(
            $this->session->id_cabang
        );
        return executeQuery($query,$args);
    }
    public function histori_tgl($tgl){
        $query = "
        select id_pk_brg_permintaan, brg_permintaan_qty, brg_nama, brg_permintaan_notes, brg_permintaan_deadline, brg_permintaan_status, tbl_brg_permintaan.id_fk_brg, tbl_brg_permintaan.id_fk_cabang, brg_permintaan_create_date, brg_permintaan_last_modified, sum(tbl_brg_pemenuhan.brg_pemenuhan_qty) as qty_pemenuhan, cabang_daerah 
        from tbl_brg_permintaan 
        join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_permintaan.id_fk_brg
        join mstr_cabang on mstr_cabang.id_pk_cabang =tbl_brg_permintaan.id_fk_cabang 
        left join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_fk_brg_permintaan = tbl_brg_permintaan.id_pk_brg_permintaan 
        where tbl_brg_permintaan.id_fk_cabang = ? 
        and year(brg_permintaan_create_date) = ? and month(brg_permintaan_create_date) = ? and day(brg_permintaan_create_date) = ?
        group by id_pk_brg_permintaan 
        order by brg_permintaan_deadline ASC";
        $extract_tgl = explode("-",$tgl); 
        $args = array(
            $this->session->id_cabang,$extract_tgl[0],$extract_tgl[1],$extract_tgl[2]
        );
        return executeQuery($query,$args);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "brg_permintaan_qty" => $this->brg_permintaan_qty,
                "brg_permintaan_notes" => $this->brg_permintaan_notes,
                "brg_permintaan_deadline" => $this->brg_permintaan_deadline,
                "brg_permintaan_status" => $this->brg_permintaan_status,
                "id_fk_brg" => $this->id_fk_brg,
                "id_fk_cabang" => $this->id_fk_cabang,
                "brg_permintaan_create_date" => $this->brg_permintaan_create_date,
                "brg_permintaan_last_modified" => $this->brg_permintaan_last_modified,
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
                "id_pk_brg_permintaan" => $this->id_pk_brg_permintaan
            );
            $data = array(
                //ceklagi
                "brg_permintaan_qty" => $this->brg_permintaan_qty,
                "brg_permintaan_notes" => $this->brg_permintaan_notes,
                "brg_permintaan_deadline" => $this->brg_permintaan_deadline,
                "brg_permintaan_last_modified" => $this->brg_permintaan_last_modified,
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
                "id_pk_brg_permintaan" => $this->id_pk_brg_permintaan
            );
            $data = array(
                "brg_permintaan_status" => "batal",
                "brg_permintaan_last_modified" => $this->brg_permintaan_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->brg_permintaan_qty == ""){
            return false;
        }
        if($this->brg_permintaan_notes == ""){
            return false;
        }
        if($this->brg_permintaan_deadline == ""){
            return false;
        }
        if($this->brg_permintaan_status == ""){
            return false;
        }
        if($this->id_fk_brg == ""){
            return false;
        }
        if($this->id_fk_cabang == ""){
            return false;
        }
        if($this->brg_permintaan_create_date == ""){
            return false;
        }
        if($this->brg_permintaan_last_modified == ""){
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
        //ceklagi
        if($this->brg_permintaan_qty == ""){
            return false;
        }
        if($this->brg_permintaan_notes == ""){
            return false;
        }
        if($this->brg_permintaan_deadline == ""){
            return false;
        }
        if($this->brg_permintaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function check_delete(){
        if($this->id_pk_brg_permintaan == ""){
            return false;
        }
        if($this->brg_permintaan_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        else return true;
    }
    public function set_insert($brg_permintaan_qty,$brg_permintaan_notes,$brg_permintaan_deadline,$brg_permintaan_status,$id_fk_brg,$id_fk_cabang){
        if(!$this->set_brg_permintaan_qty($brg_permintaan_qty)){
            return false;
        }
        if(!$this->set_brg_permintaan_notes($brg_permintaan_notes)){
            return false;
        }
        if(!$this->set_brg_permintaan_deadline($brg_permintaan_deadline)){
            return false;
        }
        if(!$this->set_brg_permintaan_status($brg_permintaan_status)){
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
    public function set_update($brg_permintaan_qty,$brg_permintaan_notes,$brg_permintaan_deadline,$id_pk_brg_permintaan){
        //ceklagi
        if(!$this->set_brg_permintaan_qty($brg_permintaan_qty)){
            return false;
        }
        if(!$this->set_brg_permintaan_notes($brg_permintaan_notes)){
            return false;
        }
        if(!$this->set_brg_permintaan_deadline($brg_permintaan_deadline)){
            return false;
        }
        if(!$this->set_id_pk_brg_permintaan($id_pk_brg_permintaan)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_brg_permintaan){
        if(!$this->set_id_pk_brg_permintaan($id_pk_brg_permintaan)){
            return false;
        }

        return true;
    }
    public function set_brg_permintaan_qty($brg_permintaan_qty){
        if($brg_permintaan_qty != ""){
            $this->brg_permintaan_qty = $brg_permintaan_qty;
            return true;
        }
        return false;
    }
    public function set_brg_permintaan_notes($brg_permintaan_notes){
        if($brg_permintaan_notes != ""){
            $this->brg_permintaan_notes = $brg_permintaan_notes;
            return true;
        }
        return false;
    }
    public function set_brg_permintaan_deadline($brg_permintaan_deadline){
        if($brg_permintaan_deadline != ""){
            $this->brg_permintaan_deadline = $brg_permintaan_deadline;
            return true;
        }
        return false;
    }
    public function set_brg_permintaan_status($brg_permintaan_status){
        if($brg_permintaan_status != ""){
            $this->brg_permintaan_status = $brg_permintaan_status;
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
    public function set_id_pk_brg_permintaan($id_pk_brg_permintaan){
        if($id_pk_brg_permintaan != ""){
            $this->id_pk_brg_permintaan = $id_pk_brg_permintaan;
            return true;
        }
        return false;
    }
    public function get_id_pk_brg_permintaan(){
        return $this->id_pk_brg_permintaan;
    }
    public function get_brg_permintaan_qty(){
        return $this->brg_permintaan_qty;
    }
    public function get_brg_permintaan_notes(){
        return $this->brg_permintaan_notes;
    }
    public function get_brg_permintaan_deadline(){
        return $this->brg_permintaan_deadline;
    }
    public function get_brg_permintaan_status(){
        return $this->brg_permintaan_status;
    }
    public function get_id_fk_brg(){
        return $this->id_fk_brg;
    }
    public function get_id_fk_cabang(){
        return $this->id_fk_cabang;
    }
}