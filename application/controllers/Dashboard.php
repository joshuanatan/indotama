<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function index()
	{	
		//total penjualan
		$tbl_brg_penjualan_harga = get1Value("tbl_brg_penjualan","brg_penjualan_harga",array("brg_penjualan_status"=>"AKTIF"));
		$tbl_tambahan_penjualan_harga = get1Value("tbl_tambahan_penjualan","tmbhn_harga",array("tmbhn_status"=>"AKTIF"));
		$data['total_penjualan'] = $tbl_brg_penjualan_harga + $tbl_tambahan_penjualan_harga;

		//total pembelian
		$tbl_brg_pembelian_harga = get1Value("tbl_brg_pembelian","brg_pem_harga",array("brg_pem_status"=>"AKTIF"));
		$tbl_tambahan_pembelian_harga = get1Value("tbl_tambahan_pembelian","tmbhn_harga",array("tmbhn_status"=>"AKTIF"));
		$data['total_pembelian'] = $tbl_brg_pembelian_harga + $tbl_tambahan_pembelian_harga;

		//total customer
		$data['total_customer'] = selectRow("mstr_customer",array("cust_status"=>"AKTIF"))->num_rows();

		//total produk
		$data['total_produk'] = selectRow("mstr_barang",array("brg_status"=>"AKTIF"))->num_rows();

		//laba bulan ini
		$pembelian = executeQuery("SELECT sum(brg_pem_harga) as harga_pem, brg_pem_create_date FROM tbl_brg_pembelian WHERE brg_pem_status='AKTIF' AND MONTH(brg_pem_create_date) = MONTH(CURRENT_DATE()) AND YEAR(brg_pem_create_date) = YEAR(CURRENT_DATE())")->result_array();
		$penjualan = executeQuery("SELECT sum(brg_penjualan_harga) as harga_pen, brg_penjualan_create_date FROM tbl_brg_penjualan WHERE brg_penjualan_status='AKTIF' AND MONTH(brg_penjualan_create_date) = MONTH(CURRENT_DATE()) AND YEAR(brg_penjualan_create_date) = YEAR(CURRENT_DATE())")->result_array();$data['laba_bulan_ini'] = $penjualan[0]['harga_pen'] - $pembelian[0]['harga_pem'];

		//laba tahun ini
		$pembelian2 = executeQuery("SELECT sum(brg_pem_harga) as harga_pem, brg_pem_create_date FROM tbl_brg_pembelian WHERE brg_pem_status='AKTIF' AND YEAR(brg_pem_create_date) = YEAR(CURRENT_DATE())")->result_array();
		$penjualan2 = executeQuery("SELECT sum(brg_penjualan_harga) as harga_pen, brg_penjualan_create_date FROM tbl_brg_penjualan WHERE brg_penjualan_status='AKTIF' AND YEAR(brg_penjualan_create_date) = YEAR(CURRENT_DATE())")->result_array();$data['laba_bulan_ini'] = $penjualan[0]['harga_pen'] - $pembelian[0]['harga_pem'];
		$data['laba_tahun_ini'] = $penjualan2[0]['harga_pen'] - $pembelian2[0]['harga_pem'];

		//top produk terjual

		$this->load->view('welcome_message',$data);
	}
    
}