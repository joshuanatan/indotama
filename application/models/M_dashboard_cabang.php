<?php
class M_dashboard_cabang extends CI_Model{
    private $id_cabang;
    public function __construct(){
        parent::__construct();
    }
    public function set_id_cabang($id_cabang){
        $this->id_cabang = $id_cabang;
    }
    public function jumlah_penjualan_bulan_ini(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and id_fk_cabang = ?
        and month(penj_tgl) = month(CURRENT_DATE)
        and year(penj_tgl) = year(CURRENT_DATE)";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];
    }
    public function jumlah_penjualan_bulan_lalu(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and id_fk_cabang = ?
        and month(penj_tgl) = month(CURRENT_DATE - INTERVAL 1 MONTH)
        and year(penj_tgl) = year(CURRENT_DATE - INTERVAL 1 MONTH)";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];

    }
    public function jumlah_penjualan_tahun_ini(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and id_fk_cabang = ?
        and year(penj_tgl) = year(CURRENT_DATE)";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];
    }
    public function jumlah_penjualan_tahun_lalu(){
        $sql = "
        select count(id_pk_penjualan) as jumlah_penjualan from mstr_penjualan 
        where penj_status != 'nonaktif'
        and id_fk_cabang = ?
        and year(penj_tgl) = year(CURRENT_DATE - INTERVAL 1 MONTH)";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        return $result[0]["jumlah_penjualan"];
    }
    public function jumlah_konfirmasi_retur(){
        $sql = "
        select count(id_pk_retur) as jumlah_retur from mstr_retur
        inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
        where retur_status = 'konfirmasi'
        and id_fk_cabang = ?
        and mstr_penjualan.penj_status != 'nonaktif'";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        return $result[0]["jumlah_retur"];
    }
    public function jumlah_item_urgen_restok(){
        $sql = "
        select count(id_pk_brg_cabang) as jumlah_brg_cabang from tbl_brg_cabang
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_cabang.id_fk_brg
        where brg_status = 'aktif'
        and brg_cabang_status = 'aktif'
        and id_fk_cabang = ?
        and brg_cabang_qty <= brg_minimal";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        return $result[0]["jumlah_brg_cabang"];
    }
    public function list_penjualan_belum_selesai(){
        $sql = "
        select brg_nama,brg_cabang_qty, brg_minimal from tbl_brg_cabang
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_cabang.id_fk_brg
        where brg_status = 'aktif'
        and brg_cabang_status = 'aktif'
        and id_fk_cabang = ?
        and brg_cabang_qty <= brg_minimal";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        $respond = array();
        for($a = 0; $a<count($result); $a++){
            $respond[$a] = array(
                $result[$a]["brg_nama"],
                $result[$a]["brg_cabang_qty"],
                $result[$a]["brg_minimal"],
            );
        }
        return $respond;
    }
    public function list_barang_custom(){
        $sql = "
        select brg_awal.brg_nama as brg_awal,brg_tujuan.brg_nama as brg_tujuan,brg_pindah_qty,user_name,brg_pindah_last_modified from tbl_brg_pindah
        inner join mstr_barang as brg_awal on brg_awal.id_pk_brg = tbl_brg_pindah.id_brg_awal
        inner join mstr_barang as brg_tujuan on brg_tujuan.id_pk_brg = tbl_brg_pindah.id_brg_tujuan
        inner join mstr_user on mstr_user.id_pk_user = tbl_brg_pindah.id_last_modified
        where brg_awal.brg_status = 'aktif'
        and brg_tujuan.brg_status = 'aktif'
        and brg_pindah_status = 'aktif'
        and id_fk_cabang = ?
        order by brg_pindah_last_modified DESC
        limit 50
        ";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
        $result = $result->result_array();
        $respond = array();
        for($a = 0; $a<count($result); $a++){
            $respond[$a] = array(
                $result[$a]["brg_awal"],
                $result[$a]["brg_tujuan"],
                $result[$a]["brg_pindah_qty"],
                $result[$a]["user_name"],
                $result[$a]["brg_pindah_last_modified"],
            );
        }
        return $respond;
    }
    public function list_penjualan_3_tahun_terakhir(){
        $sql = "
        select year(penj_tgl) as tahun,count(id_pk_penjualan) as jmlh_penjualan from mstr_penjualan
        where penj_status != 'nonaktif'
        and id_fk_cabang = ?
        group by year(penj_tgl) 
        order by tahun DESC
        limit 3";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
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
        and id_fk_cabang = ?
        and year(penj_tgl) = year(CURRENT_DATE)
        group by month(penj_tgl) 
        order by bulan ASC
        ";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
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
        where penj_status != 'nonaktif'
        and id_fk_cabang = ?
        and year(penj_tgl) = year(CURRENT_DATE - interval ".$tahun." year)
        group by month(penj_tgl) 
        order by bulan ASC
        ";
        $args = array(
            $this->id_cabang
        );
        $result = executeQuery($sql,$args);
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
}