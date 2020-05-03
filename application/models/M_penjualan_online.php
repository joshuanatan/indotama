<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_penjualan_online extends CI_Model{
    private $tbl_name = "TBL_PENJUALAN_ONLINE";
    private $columns = array();
    private $id_pk_penjualan_online;
    private $penj_on_no_resi;
    private $penj_on_kurir;
    private $penj_on_status;
    private $id_fk_toko;
    private $id_fk_penjualan;
    private $penj_on_create_date;
    private $penj_on_last_modified;
    private $id_create_data;
    private $id_last_modified;

    public function __construct(){
        parent::__construct();
        $this->penj_on_create_date = date("Y-m-d H:i:s");
        $this->penj_on_last_modified = date("Y-m-d H:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    public function columns(){
        return $this->columns;
    }
    public function install(){
        $sql = "DROP TABLE IF EXISTS TBL_PENJUALAN_ONLINE;
        CREATE TABLE TBL_PENJUALAN_ONLINE(
            ID_PK_PENJUALAN_ONLINE INT PRIMARY KEY AUTO_INCREMENT,
            PENJ_ON_NO_RESI VARCHAR(40),
            PENJ_ON_KURIR VARCHAR(40),
            PENJ_ON_STATUS VARCHAR(15),
            ID_FK_TOKO INT,
            ID_FK_PENJUALAN INT,
            PENJ_ON_CREATE_DATE DATETIME,
            PENJ_ON_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT
        );
        DROP TABLE IF EXISTS TBL_PENJUALAN_ONLINE_LOG;
        CREATE TABLE TBL_PENJUALAN_ONLINE_LOG(
            ID_PK_PENJUALAN_ONLINE_LOG INT PRIMARY KEY AUTO_INCREMENT,
            EXECUTED_FUNCTION VARCHAR(30),
            ID_PK_PENJUALAN_ONLINE INT,
            PENJ_ON_NO_RESI VARCHAR(40),
            PENJ_ON_KURIR VARCHAR(40),
            PENJ_ON_STATUS VARCHAR(15),
            ID_FK_TOKO INT,
            ID_FK_PENJUALAN INT,
            PENJ_ON_CREATE_DATE DATETIME,
            PENJ_ON_LAST_MODIFIED DATETIME,
            ID_CREATE_DATA INT,
            ID_LAST_MODIFIED INT,
            ID_LOG_ALL INT
        );
        DROP TRIGGER IF EXISTS TRG_AFTER_INSERT_PENJUALAN_ONLINE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_INSERT_PENJUALAN_ONLINE
        AFTER INSERT ON TBL_PENJUALAN_ONLINE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENJ_ON_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.PENJ_ON_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_PENJUALAN_ONLINE_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN_ONLINE,PENJ_ON_NO_RESI,PENJ_ON_KURIR,PENJ_ON_STATUS,ID_FK_TOKO,ID_FK_PENJUALAN,PENJ_ON_CREATE_DATE,PENJ_ON_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_PENJUALAN_ONLINE,NEW.PENJ_ON_NO_RESI,NEW.PENJ_ON_KURIR,NEW.PENJ_ON_STATUS,NEW.ID_FK_TOKO,NEW.ID_FK_PENJUALAN,NEW.PENJ_ON_CREATE_DATE,NEW.PENJ_ON_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;
        
        DROP TRIGGER IF EXISTS TRG_AFTER_UPDATE_PENJUALAN_ONLINE;
        DELIMITER $$
        CREATE TRIGGER TRG_AFTER_UPDATE_PENJUALAN_ONLINE
        AFTER UPDATE ON TBL_PENJUALAN_ONLINE
        FOR EACH ROW
        BEGIN
            SET @ID_USER = NEW.ID_LAST_MODIFIED;
            SET @TGL_ACTION = NEW.PENJ_ON_LAST_MODIFIED;
            SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.PENJ_ON_LAST_MODIFIED);
            CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
            
            INSERT INTO TBL_PENJUALAN_ONLINE_LOG(EXECUTED_FUNCTION,ID_PK_PENJUALAN_ONLINE,PENJ_ON_NO_RESI,PENJ_ON_KURIR,PENJ_ON_STATUS,ID_FK_TOKO,ID_FK_PENJUALAN,PENJ_ON_CREATE_DATE,PENJ_ON_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_PENJUALAN_ONLINE,NEW.PENJ_ON_NO_RESI,NEW.PENJ_ON_KURIR,NEW.PENJ_ON_STATUS,NEW.ID_FK_TOKO,NEW.ID_FK_PENJUALAN,NEW.PENJ_ON_CREATE_DATE,NEW.PENJ_ON_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
        END$$
        DELIMITER ;";
        executeQuery($sql);
    }
    public function insert(){
        if($this->check_insert()){
            $data = array(
                "penj_on_no_resi" => $this->penj_on_no_resi,
                "penj_on_kurir" => $this->penj_on_kurir,
                "penj_on_status" => $this->penj_on_status,
                "id_fk_toko" => $this->id_fk_toko,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "penj_on_create_date" => $this->penj_on_create_date,
                "penj_on_last_modified" => $this->penj_on_last_modified,
                "id_create_data" => $this->id_create_data,
                "id_last_modified" => $this->id_last_modified
            );
            return insertRow($this->tbl_name,$data);
        }
        return false;
    }
    public function update(){
        if($this->check_update()){
            $where = array(
                "id_pk_penjualan_online" => $this->id_pk_penjualan_online
            );
            $data = array(
                "penj_on_no_resi" => $this->penj_on_no_resi,
                "penj_on_kurir" => $this->penj_on_kurir,
                "id_fk_toko" => $this->id_fk_toko,
                "id_fk_penjualan" => $this->id_fk_penjualan,
                "penj_on_last_modified" => $this->penj_on_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true; 
        }
        return false;
    }
    public function delete(){
        if($this->check_delete()){
            $where = array(
                "id_pk_penjualan_online" => $this->id_pk_penjualan_online
            );
            $data = array(
                "penj_on_status" => "NONAKTIF",
                "penj_on_last_modified" => $this->penj_on_last_modified,
                "id_last_modified" => $this->id_last_modified
            );
            updateRow($this->tbl_name,$data,$where);
            return true; 
        }
        return false;
    }
    public function check_insert(){
        if($this->penj_on_no_resi == ""){
            return false;
        }
        if($this->penj_on_kurir == ""){
            return false;
        }
        if($this->penj_on_status == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->penj_on_create_date == ""){
            return false;
        }
        if($this->penj_on_last_modified == ""){
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
        if($this->id_pk_penjualan_online == ""){
            return false;
        }
        if($this->penj_on_no_resi == ""){
            return false;
        }
        if($this->penj_on_kurir == ""){
            return false;
        }
        if($this->id_fk_toko == ""){
            return false;
        }
        if($this->id_fk_penjualan == ""){
            return false;
        }
        if($this->penj_on_last_modified == ""){
            return false;
        }
        if($this->id_last_modified == ""){
            return false;
        }
        return true;
    }
    public function check_delete(){
        if($this->id_pk_penjualan_online == ""){
            return false;
        }
        return true;
    }
    public function set_insert($penj_on_no_resi,$penj_on_kurir,$penj_on_status,$id_fk_toko,$id_fk_penjualan){
        if(!$this->set_penj_on_no_resi($penj_on_no_resi)){
            return false;
        }
        if(!$this->set_penj_on_kurir($penj_on_kurir)){
            return false;
        }
        if(!$this->set_penj_on_status($penj_on_status)){
            return false;
        }
        if(!$this->set_id_fk_toko($id_fk_toko)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_update($set_id_pk_penjualan_online,$set_penj_on_no_resi,$set_penj_on_kurir,$set_id_fk_toko,$set_id_fk_penjualan){
        if(!$this->set_id_pk_penjualan_online($set_id_pk_penjualan_online)){
            return false;
        }
        if(!$this->set_penj_on_no_resi($set_penj_on_no_resi)){
            return false;
        }
        if(!$this->set_penj_on_kurir($set_penj_on_kurir)){
            return false;
        }
        if(!$this->set_id_fk_toko($set_id_fk_toko)){
            return false;
        }
        if(!$this->set_id_fk_penjualan($set_id_fk_penjualan)){
            return false;
        }
        return true;
    }
    public function set_delete($set_id_pk_penjualan_online){
        if(!$this->set_id_pk_penjualan_online($set_id_pk_penjualan_online)){
            return false;
        }
        return true;
    }
    public function get_id_pk_penjualan_online(){
        return $this->id_pk_penjualan_online;
    }
    public function get_penj_on_no_resi(){
        return $this->penj_on_no_resi;
    }
    public function get_penj_on_kurir(){
        return $this->penj_on_kurir;
    }
    public function get_id_fk_toko(){
        return $this->id_fk_toko;
    }
    public function get_id_fk_penjualan(){
        return $this->id_fk_penjualan;
    }
    public function get_penj_on_status(){
        return $this->penj_on_status;
    }
    public function get_penj_on_create_date(){
        return $this->penj_on_create_date;
    }
    public function get_penj_on_last_modified(){
        return $this->penj_on_last_modified;
    }
    public function get_id_create_data(){
        return $this->id_create_data;
    }
    public function get_id_last_modified(){
        return $this->id_last_modified;
    }
    public function set_id_pk_penjualan_online($id_pk_penjualan_online){
        if($id_pk_penjualan_online != ""){
            $this->id_pk_penjualan_online = $id_pk_penjualan_online;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_no_resi($penj_on_no_resi){
        if($penj_on_no_resi != ""){
            $this->penj_on_no_resi = $penj_on_no_resi;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_kurir($penj_on_kurir){
        if($penj_on_kurir != ""){
            $this->penj_on_kurir = $penj_on_kurir;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_status($penj_on_status){
        if($penj_on_status != ""){
            $this->penj_on_status = $penj_on_status;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_fk_toko($id_fk_toko){
        if($id_fk_toko != ""){
            $this->id_fk_toko = $id_fk_toko;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_fk_penjualan($id_fk_penjualan){
        if($id_fk_penjualan != ""){
            $this->id_fk_penjualan = $id_fk_penjualan;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_create_date($penj_on_create_date){
        if($penj_on_create_date != ""){
            $this->penj_on_create_date = $penj_on_create_date;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_penj_on_last_modified($penj_on_last_modified){
        if($penj_on_last_modified != ""){
            $this->penj_on_last_modified = $penj_on_last_modified;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_create_data($id_create_data){
        if($id_create_data != ""){
            $this->id_create_data = $id_create_data;
            return true;
        }
        else{
            return false;
        }
    }
    public function set_id_last_modified($id_last_modified){
        if($id_last_modified != ""){
            $this->id_last_modified = $id_last_modified;
            return true;
        }
        else{
            return false;
        }
    }
}