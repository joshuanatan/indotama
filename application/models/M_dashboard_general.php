<?php
class M_dashboard_general extends CI_Model{
    public function __construct(){
        parent::__construct();
    }
    public function jumlah_penjualan_bulan_ini(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and month(penj_tgl) = month(CURRENT_DATE)
        and year(penj_tgl) = year(CURRENT_DATE)";
        $result = executeQuery($sql);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];
    }
    public function jumlah_penjualan_bulan_lalu(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and month(penj_tgl) = month(CURRENT_DATE - INTERVAL 1 MONTH)
        and year(penj_tgl) = year(CURRENT_DATE - INTERVAL 1 MONTH)";
        $result = executeQuery($sql);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];

    }
    public function jumlah_penjualan_tahun_ini(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and year(penj_tgl) = year(CURRENT_DATE)";
        $result = executeQuery($sql);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];
    }
    public function jumlah_penjualan_tahun_lalu(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and year(penj_tgl) = year(CURRENT_DATE - INTERVAL 1 MONTH)";
        $result = executeQuery($sql);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];
    }
    public function list_penjualan_3_tahun_terakhir(){
        $sql = "
        select year(penj_tgl) as tahun,count(id_pk_penjualan) as jmlh_penjualan from mstr_penjualan
        where penj_status != 'nonaktif'
        group by year(penj_tgl) 
        order by tahun DESC
        limit 3";
        $result = executeQuery($sql);
        $result = $result->result_array();
        $respond["data"] = array();
        $respond["label"] = array();
        for($a = 0; $a<count($result); $a++){
            $respond["data"][$a] = $result[$a]["jmlh_penjualan"];
            $respond["label"][$a] = $result[$a]["tahun"];
        }
        return $respond;
        
    }
    public function list_penjualan_tahun_ini_perbulan(){
        $sql = "
        select month(penj_tgl) as bulan,count(id_pk_penjualan) as jmlh_penjualan from mstr_penjualan
        where penj_status != 'nonaktif'
        and year(penj_tgl) = year(CURRENT_DATE)
        group by month(penj_tgl) 
        order by bulan ASC
        ";
        $result = executeQuery($sql);
        $result = $result->result_array();
        $respond["data"] = array();
        $respond["label"] = array();
        $respond["label"] = array(
            "Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"
        );
        for($a = 0; $a<12; $a++){
            $respond["data"][$a] = 0;
        }
        for($a = 0; $a<count($result); $a++){
            $respond["data"][$result[$a]["bulan"]-1] = $result[$a]["jmlh_penjualan"];
        }
        return $respond;

    }
    public function list_penjualan_tahun_lalu_perbulan($tahun){
        $sql = "
        select month(penj_tgl) as bulan,count(id_pk_penjualan) as jmlh_penjualan from mstr_penjualan
        where penj_status = 'aktif'
        and year(penj_tgl) = year(CURRENT_DATE - interval ".$tahun." year)
        group by month(penj_tgl) 
        order by bulan ASC
        ";
        $result = executeQuery($sql);
        $result = $result->result_array();
        $respond["data"] = array();
        $respond["label"] = array();
        $respond["label"] = array(
            "Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"
        );
        for($a = 0; $a<12; $a++){
            $respond["data"][$a] = 0;
        }
        for($a = 0; $a<count($result); $a++){
            $respond["data"][$result[$a]["bulan"]-1] = $result[$a]["jmlh_penjualan"];
        }
        return $respond;
    }
    public function jumlah_barang(){
        $sql = "select count(distinct brg_nama) as jmlh_barang from mstr_barang
        where brg_status != 'nonaktif'
        ";
        $result = executeQuery($sql);
        $result = $result->result_array();
        return $result[0]["jmlh_barang"];
    }
    public function jumlah_jenis_barang(){
        $sql = "select count(distinct brg_jenis_nama) as jmlh_brg_jenis from mstr_barang_jenis
        where brg_jenis_status != 'nonaktif'
        ";
        $result = executeQuery($sql);
        $result = $result->result_array();
        return $result[0]["jmlh_brg_jenis"];
    }
    public function jumlah_nominal_penjualan($tahun = "", $bulan = ""){
        if($tahun && $bulan){
            $sql = "
            select ifnull(sum(penj_nominal),0) as penj_nominal from mstr_penjualan
            where penj_status != 'nonaktif'
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?";
            $args = array(
                $tahun,$bulan
            );
            $result = executeQuery($sql, $args);
        }
        else if($tahun && !$bulan){
            $sql = "
            select ifnull(sum(penj_nominal),0) as penj_nominal from mstr_penjualan
            where penj_status != 'nonaktif'
            and year(penj_tgl) = ?";
            $args = array(
                $tahun
            );
            $result = executeQuery($sql, $args);
        }
        else if(!$tahun && $bulan){
            $sql = "
            select ifnull(sum(penj_nominal),0) as penj_nominal from mstr_penjualan
            where penj_status != 'nonaktif'
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?";
            $args = array(
                date("Y"),$bulan
            );
            $result = executeQuery($sql, $args);
        }
        $result = $result->result_array();
        return $result[0]["penj_nominal"];
    }
    public function jumlah_nominal_pembayaran($tahun = "", $bulan = ""){
        if($tahun && $bulan){
            $sql = "
            select ifnull(sum(penj_nominal_byr),0) as penj_nominal_byr from mstr_penjualan
            where penj_status != 'nonaktif'
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?";
            $args = array(
                $tahun,$bulan
            );
            $result = executeQuery($sql, $args);
        }
        else if($tahun && !$bulan){
            $sql = "
            select ifnull(sum(penj_nominal_byr),0) as penj_nominal_byr from mstr_penjualan
            where penj_status != 'nonaktif'
            and year(penj_tgl) = ?";
            $args = array(
                $tahun
            );
            $result = executeQuery($sql, $args);
        }
        else if(!$tahun && $bulan){
            $sql = "
            select ifnull(sum(penj_nominal_byr),0) as penj_nominal_byr from mstr_penjualan
            where penj_status != 'nonaktif'
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?";
            $args = array(
                date("Y"),$bulan
            );
            $result = executeQuery($sql, $args);
        }
        $result = $result->result_array();
        return $result[0]["penj_nominal_byr"];
    }
    public function urutan_barang($tahun = "",$bulan = "",$jumlah = 10,$urutan = "desc"){
        
        if($tahun && $bulan){
            $sql = "select sum(brg_penjualan_qty) as jmlh_brg,brg_nama 
            from tbl_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan 
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang
            where 
            brg_penjualan_status != 'nonaktif'
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?
            group by id_fk_barang
            order by jmlh_brg ".$urutan."
            limit ".$jumlah.";
            ";
            $args = array(
                $tahun,$bulan
            );
            $result = executeQuery($sql, $args);
        }
        else if($tahun && !$bulan){
            $sql = "select sum(brg_penjualan_qty) as jmlh_brg,brg_nama 
            from tbl_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan 
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang
            where 
            brg_penjualan_status != 'nonaktif'
            and year(penj_tgl) = ?
            group by id_fk_barang
            order by jmlh_brg ".$urutan."
            limit ".$jumlah.";
            ";
            $args = array(
                $tahun
            );
            $result = executeQuery($sql, $args);
        }
        else if(!$tahun && $bulan){
            
            $sql = "select sum(brg_penjualan_qty) as jmlh_brg,brg_nama 
            from tbl_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan 
            inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang
            where 
            brg_penjualan_status != 'nonaktif'
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?
            group by id_fk_barang
            order by jmlh_brg ".$urutan."
            limit ".$jumlah.";
            ";
            $args = array(
                date("Y"),$bulan
            );
            $result = executeQuery($sql, $args);
        }
        $result = $result->result_array();
        
        $respond["data"] = array();
        $respond["label"] = array();
        for($a = 0; $a<count($result); $a++){
            $respond["data"][$a] = $result[$a]["jmlh_brg"];
            $respond["label"][$a] = $result[$a]["brg_nama"];
        }
        return $respond;
    }
    public function jumlah_penjualan_cabang($tahun = "", $bulan = ""){
        
        if($tahun && $bulan){
            $sql = "
            select count(id_pk_penjualan) as jmlh_penjualan,cabang_nama,toko_nama,cabang_daerah
            from mstr_penjualan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            where penj_status != 'nonaktif'
            group by id_fk_cabang
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?";
            $args = array(
                $tahun,$bulan
            );
            $result = executeQuery($sql,$args);
        }
        else if($tahun && !$bulan){
            $sql = "
            select count(id_pk_penjualan) as jmlh_penjualan,cabang_nama,toko_nama,cabang_daerah
            from mstr_penjualan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            where penj_status != 'nonaktif'
            group by id_fk_cabang
            and year(penj_tgl) = ?";
            $args = array(
                $tahun
            );
            $result = executeQuery($sql,$args);

        }
        else if(!$tahun && $bulan){
            $sql = "
            select count(id_pk_penjualan) as jmlh_penjualan,cabang_nama,toko_nama,cabang_daerah
            from mstr_penjualan
            inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang
            inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
            where penj_status != 'nonaktif'
            group by id_fk_cabang
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?";
            $args = array(
                date("Y"),$bulan
            );
            $result = executeQuery($sql,$args);
        }
        $result = $result->result_array();
        $respond["data"] = array();
        $respond["label"] = array();
        for($a = 0; $a<count($result); $a++){
            $respond["data"][$a] = $result[$a]["jmlh_penjualan"];
            $respond["label"][$a] = $result[$a]["toko_nama"]." - ".$result[$a]["cabang_nama"]."/".$result[$a]["cabang_daerah"];
        }
        return $respond;
    }
    public function penjualan_dekat_dateline(){
        $sql = "select penj_nomor, penj_nominal, penj_nominal_byr,penj_dateline_tgl,penj_jenis,cabang_daerah, toko_nama,cabang_nama 
        from mstr_penjualan
        inner join mstr_cabang on mstr_cabang.id_pk_cabang = mstr_penjualan.id_fk_cabang
        inner join mstr_toko on mstr_toko.id_pk_toko = mstr_cabang.id_fk_toko
        where penj_status != 'selesai' and penj_status != 'nonaktif'
        and penj_dateline_tgl < CURRENT_DATE + interval 7 day";
        $result = executeQuery($sql);
        $result = $result->result_array();
        $respond = array();
        for($a = 0; $a<count($result); $a++){
            $respond[$a] = array(
                $result[$a]["penj_nomor"],
                number_format($result[$a]["penj_nominal"]),
                $result[$a]["penj_dateline_tgl"],
                $result[$a]["penj_jenis"],
                $result[$a]["toko_nama"]." ".$result[$a]["cabang_nama"]."/".$result[$a]["cabang_daerah"]
            );
        }
        return $respond;
    } 
    public function jumlah_penjualan_tipe($tahun = "", $bulan = "", $tipe){

        if($tahun && $bulan){
            $sql = "select count(id_pk_penjualan) as jmlh_penjualan
            from mstr_penjualan
            where penj_status != 'nonaktif' 
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?
            and penj_jenis = ?
            ";
            $args = array(
                $tahun,$bulan,$tipe
            );
            $result = executeQuery($sql,$args);
        }

        else if($tahun && !$bulan){
            $sql = "select count(id_pk_penjualan) as jmlh_penjualan
            from mstr_penjualan
            where penj_status != 'nonaktif' 
            and year(penj_tgl) = ?
            and penj_jenis = ?
            ";
            $args = array(
                $tahun,$tipe
            );
            $result = executeQuery($sql,$args);
        }

        else if(!$tahun && $bulan){
            $sql = "select count(id_pk_penjualan) as jmlh_penjualan
            from mstr_penjualan
            where penj_status != 'nonaktif' 
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?
            and penj_jenis = ?
            ";
            $args = array(
                date("Y"),$bulan,$tipe
            );
            $result = executeQuery($sql,$args);
        }
        $result = $result->result_array();
        return $result[0]["jmlh_penjualan"];
    }
    public function nominal_penjualan_tipe($tahun = "", $bulan = "", $tipe){

        if($tahun && $bulan){
            $sql = "select sum(penj_nominal) as nominal
            from mstr_penjualan
            where penj_status != 'nonaktif' 
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?
            and penj_jenis = ?
            ";
            $args = array(
                $tahun,$bulan,$tipe
            );
            $result = executeQuery($sql,$args);
        }

        else if($tahun && !$bulan){
            $sql = "select sum(penj_nominal) as nominal
            from mstr_penjualan
            where penj_status != 'nonaktif' 
            and year(penj_tgl) = ?
            and penj_jenis = ?
            ";
            $args = array(
                $tahun,$tipe
            );
            $result = executeQuery($sql,$args);
        }

        else if(!$tahun && $bulan){
            $sql = "select sum(penj_nominal) as nominal
            from mstr_penjualan
            where penj_status != 'nonaktif' 
            and year(penj_tgl) = ?
            and month(penj_tgl) = ?
            and penj_jenis = ?
            ";
            $args = array(
                date("Y"),$bulan,$tipe
            );
            $result = executeQuery($sql,$args);
        }
        $result = $result->result_array();
        return $result[0]["nominal"];
    }
}