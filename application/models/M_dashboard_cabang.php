<?php
class M_dashboard_cabang extends CI_Model{
    private $id_cabang;
    public function __construct(){
        parent::__construct();
        $this->id_cabang = $this->session->id_cabang;
    }
    public function jumlah_penjualan_bulan_ini(){

    }
    public function jumlah_penjualan_bulan_lalu(){

    }
    public function jumlah_penjualan_tahun_ini(){

    }
    public function jumlah_penjualan_tahun_lalu(){

    }
    public function jumlah_konfirmasi_retur(){

    }
    public function jumlah_item_urgen_restok(){

    }
    public function list_penjualan_belum_selesai(){

    }
    public function list_pembelian_belum_selesai(){

    }
    public function list_barang_custom(){

    }
    public function list_penjualan_3_tahun_terakhir(){

    }
    public function list_penjualan_tahun_ini_perbulan(){

    }
    public function list_penjualan_tahun_lalu_perbulan(){

    }
}