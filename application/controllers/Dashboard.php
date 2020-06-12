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

		$this->load->view('welcome_message',$data);
	}
    
}