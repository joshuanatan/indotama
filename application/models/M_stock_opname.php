<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class m_stock_opname extends ci_model{
    private $tbl_name = "mstr_stock_opname";
    private $columns = array();
    private $id_pk_sup;
    private $sup_nama;
    private $sup_perusahaan;
    private $sup_email;
    private $sup_telp;
    private $sup_hp;
    private $sup_alamat;
    private $sup_keterangan;
    private $sup_status;
    private $id_fk_toko;
    private $sup_create_date;
    private $sup_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->sup_create_date = date("y-m-d h:i:s");
        $this->sup_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "
        drop table if exists mstr_stock_opname;
        create table mstr_stock_opname(
            id_pk_stock_opname int primary key auto_increment,
            so_tgl datetime,
            so_notes varchar(200),
            id_fk_toko int,
            id_emp_det int,
            so_create_date datetime,
            so_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_stock_opname_log;
        create table mstr_stock_opname_log(
            id_pk_stock_opname_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_stock_opname int,
            so_tgl datetime,
            so_notes varchar(200),
            id_fk_toko int,
            id_emp_det int,
            so_create_date datetime,
            so_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_stock_opname;
        delimiter $$
        create trigger trg_after_insert_stock_opname
        after insert on mstr_stock_opname
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.so_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.so_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_stock_opname_log(executed_function,id_pk_stock_opname,so_tgl,so_notes,id_fk_toko,id_emp_det,so_create_date,so_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_stock_opname,new.so_tgl,new.so_notes,new.id_fk_toko,new.id_emp_det,new.so_create_date,new.so_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_stock_opname;
        delimiter $$
        create trigger trg_after_update_stock_opname
        after update on mstr_stock_opname
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.so_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.so_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_stock_opname_log(executed_function,id_pk_stock_opname,so_tgl,so_notes,id_fk_toko,id_emp_det,so_create_date,so_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_stock_opname,new.so_tgl,new.so_notes,new.id_fk_toko,new.id_emp_det,new.so_create_date,new.so_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        ";
        executequery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "sup_nama" => $this->sup_nama,
                "sup_perusahaan" => $this->sup_perusahaan,
                "sup_email" => $this->sup_email,
                "sup_telp" => $this->sup_telp,
                "sup_hp" => $this->sup_hp,
                "sup_alamat" => $this->sup_alamat,
                "sup_keterangan" => $this->sup_keterangan,
                "sup_status" => $this->sup_status,
                "id_fk_toko" => $this->id_fk_toko,
                "sup_create_date" => $this->sup_create_date,
                "sup_last_modified" => $this->sup_last_modified,
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
                "id_pk_sup" => $this->id_pk_sup
            );
            $data = array(
                "sup_nama" => $this->sup_nama,
                "sup_perusahaan" => $this->sup_perusahaan,
                "sup_email" => $this->sup_email,
                "sup_telp" => $this->sup_telp,
                "sup_hp" => $this->sup_hp,
                "sup_alamat" => $this->sup_alamat,
                "sup_keterangan" => $this->sup_keterangan,
                "id_fk_toko" => $this->id_fk_toko,
                "sup_last_modified" => $this->sup_last_modified,
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
                "id_pk_sup" => $this->id_pk_sup
            );
            $data = array(
                "sup_status" => "nonaktif",
                "sup_last_modified" => $this->sup_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updaterow($this->tbl_name,$data,$where);
            return true;
        }
        return false;
    }
    public function check_insert(){
        if($this->sup_nama == ""){
            return false;
        }
        if($this->sup_perusahaan == ""){
            return false;
        }
        if($this->sup_email == ""){
            return false;
        }
        if($this->sup_telp == ""){
            return false;
        }
        if($this->sup_hp == ""){
            return false;
        }
        if($this->sup_alamat == ""){
            return false;
        }
        if($this->sup_keterangan == ""){
            return false;
        }
        if($this->sup_status == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->sup_create_date == ""){
            return false;
        }
        if($this->sup_last_modified == ""){
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
        if($this->id_pk_sup == ""){
            return false;
        }
        if($this->sup_nama == ""){
            return false;
        }
        if($this->sup_perusahaan == ""){
            return false;
        }
        if($this->sup_email == ""){
            return false;
        }
        if($this->sup_telp == ""){
            return false;
        }
        if($this->sup_hp == ""){
            return false;
        }
        if($this->sup_alamat == ""){
            return false;
        }
        if($this->sup_keterangan == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->sup_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_sup == ""){
            return false;
        }
        if($this->sup_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function set_insert($sup_nama,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan,$sup_status,$id_fk_toko){
        if(!$this->set_sup_nama($sup_nama)){
            return false;
        }
        if(!$this->set_sup_perusahaan($sup_perusahaan)){
            return false;
        }
        if(!$this->set_sup_email($sup_email)){
            return false;
        }
        if(!$this->set_sup_telp($sup_telp)){
            return false;
        }
        if(!$this->set_sup_hp($sup_hp)){
            return false;
        }
        if(!$this->set_sup_alamat($sup_alamat)){
            return false;
        }
        if(!$this->set_sup_keterangan($sup_keterangan)){
            return false;
        }
        if(!$this->set_sup_status($sup_status)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        return true;
    }
    public function set_update($id_pk_sup,$sup_nama,$sup_perusahaan,$sup_email,$sup_telp,$sup_hp,$sup_alamat,$sup_keterangan,$id_fk_toko){
        if(!$this->set_id_pk_sup($id_pk_sup)){
            return false;
        }
        if(!$this->set_sup_nama($sup_nama)){
            return false;
        }
        if(!$this->set_sup_perusahaan($sup_perusahaan)){
            return false;
        }
        if(!$this->set_sup_email($sup_email)){
            return false;
        }
        if(!$this->set_sup_telp($sup_telp)){
            return false;
        }
        if(!$this->set_sup_hp($sup_hp)){
            return false;
        }
        if(!$this->set_sup_alamat($sup_alamat)){
            return false;
        }
        if(!$this->set_sup_keterangan($sup_keterangan)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        return true;
    }
    public function set_delete($id_pk_sup){
        if(!$this->set_id_pk_sup($id_pk_sup)){
            return false;
        }
        return true;
    }
    public function set_id_pk_sup($id_pk_sup){
        if($id_pk_sup != ""){
            $this->id_pk_sup = $id_pk_sup;
            return true;
        }
        return false;
    }
    public function set_sup_nama($sup_nama){
        if($sup_nama != ""){
            $this->sup_nama = $sup_nama;
            return true;
        }
        return false;
    }
    public function set_sup_perusahaan($sup_perusahaan){
        if($sup_perusahaan != ""){
            $this->sup_perusahaan = $sup_perusahaan;
            return true;
        }
        return false;
    }
    public function set_sup_email($sup_email){
        if($sup_email != ""){
            $this->sup_email = $sup_email;
            return true;
        }
        return false;
    }
    public function set_sup_telp($sup_telp){
        if($sup_telp != ""){
            $this->sup_telp = $sup_telp;
            return true;
        }
        return false;
    }
    public function set_sup_hp($sup_hp){
        if($sup_hp != ""){
            $this->sup_hp = $sup_hp;
            return true;
        }
        return false;
    }
    public function set_sup_alamat($sup_alamat){
        if($sup_alamat != ""){
            $this->sup_alamat = $sup_alamat;
            return true;
        }
        return false;
    }
    public function set_sup_keterangan($sup_keterangan){
        if($sup_keterangan != ""){
            $this->sup_keterangan = $sup_keterangan;
            return true;
        }
        return false;
    }
    public function set_sup_status($sup_status){
        if($sup_status != ""){
            $this->sup_status = $sup_status;
            return true;
        }
        return false;
    }
    public function set_id_fk_toko($id_fk_toko){
        if($id_fk_toko != ""){
            $this->id_fk_toko = $id_fk_toko;
            return true;
        }
        return false;
    }
    public function set_sup_create_date($sup_create_date){
        if($sup_create_date != ""){
            $this->sup_create_date = $sup_create_date;
            return true;
        }
        return false;
    }
    public function set_sup_last_modified($sup_last_modified){
        if($sup_last_modified != ""){
            $this->sup_last_modified = $sup_last_modified;
            return true;
        }
        return false;
    }
    public function set_id_create_data($id_create_data){
        if($id_create_data != ""){
            $this->id_create_data = $id_create_data;
            return true;
        }
        return false;
    }
    public function set_id_last_modified($id_last_modified){
        if($id_last_modified != ""){
            $this->id_last_modified = $id_last_modified;
            return true;
        }
        return false;
    }
    public function get_id_pk_sup(){
        return $this->id_pk_sup;
    }
    public function get_sup_nama(){
        return $this->sup_nama;
    }
    public function get_sup_perusahaan(){
        return $this->sup_perusahaan;
    }
    public function get_sup_email(){
        return $this->sup_email;
    }
    public function get_sup_telp(){
        return $this->sup_telp;
    }
    public function get_sup_hp(){
        return $this->sup_hp;
    }
    public function get_sup_alamat(){
        return $this->sup_alamat;
    }
    public function get_sup_keterangan(){
        return $this->sup_keterangan;
    }
    public function get_sup_status(){
        return $this->sup_status;
    }
    public function get_id_fk_toko(){
        return $this->id_fk_toko;
    }
    public function get_sup_create_date(){
        return $this->sup_create_date;
    }
    public function get_sup_last_modified(){
        return $this->sup_last_modified;
    }
    public function get_id_create_data(){
        return $this->id_create_data;
    }
    public function get_id_last_modified(){
        return $this->id_last_modified;
    }
}