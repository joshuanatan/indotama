<?php
class M_e_pengiriman_retur extends CI_Model{
    private $tbl_name = "mstr_pengiriman";
    private $columns = array();
    public function __construct(){
        parent::__construct();
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function columns_excel(){
        $this->columns = array();
        $this->set_column("pengiriman_no","nomor pengiriman",true);
        $this->set_column("pengiriman_tgl","tanggal pengiriman",false);
        $this->set_column("retur_no","nomor retur",false);
        $this->set_column("pengiriman_status","status",false);
        $this->set_column("pengiriman_last_modified","last modified",false);
        return $this->columns;
    }
    public function data_excel(){
        $query = "select id_pk_pengiriman,pengiriman_no,pengiriman_tgl,pengiriman_status,pengiriman_tempat,".$this->tbl_name.".id_fk_warehouse,".$this->tbl_name.".id_fk_cabang,pengiriman_last_modified,penj_nomor,cust_perusahaan, cust_name, cust_suff, cust_hp, cust_email,penj_nomor,retur_no
        from ".$this->tbl_name."
        inner join mstr_retur on mstr_retur.id_pk_retur = ".$this->tbl_name.".id_fk_retur 
        inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = ".$this->tbl_name.".id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where pengiriman_status = ? and cust_status = ? and cabang_status = ? and toko_status = ? and ".$this->tbl_name.".id_fk_cabang = ?";
        $args = array(
            "aktif","aktif","aktif","aktif",$this->session->id_cabang
        );
        $result = executeQuery($query,$args);
        return $result;
    }
}