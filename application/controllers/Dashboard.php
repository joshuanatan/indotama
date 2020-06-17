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
		$pembelian = executeQuery("SELECT sum(brg_pem_harga*brg_pem_qty) as harga_pem, brg_pem_create_date FROM tbl_brg_pembelian WHERE brg_pem_status='AKTIF' AND MONTH(brg_pem_create_date) = MONTH(CURRENT_DATE()) AND YEAR(brg_pem_create_date) = YEAR(CURRENT_DATE())")->result_array();
		$penjualan = executeQuery("SELECT sum(brg_penjualan_harga*brg_penjualan_qty) as harga_pen, brg_penjualan_create_date FROM tbl_brg_penjualan WHERE brg_penjualan_status='AKTIF' AND MONTH(brg_penjualan_create_date) = MONTH(CURRENT_DATE()) AND YEAR(brg_penjualan_create_date) = YEAR(CURRENT_DATE())")->result_array();
		$data['laba_bulan_ini'] = $penjualan[0]['harga_pen'] - $pembelian[0]['harga_pem'];

		//laba tahun ini
		$pembelian2 = executeQuery("SELECT sum(brg_pem_harga*brg_pem_qty) as harga_pem, brg_pem_create_date FROM tbl_brg_pembelian WHERE brg_pem_status='AKTIF' AND YEAR(brg_pem_create_date) = YEAR(CURRENT_DATE())")->result_array();
		$penjualan2 = executeQuery("SELECT sum(brg_penjualan_harga*brg_penjualan_qty) as harga_pen, brg_penjualan_create_date FROM tbl_brg_penjualan WHERE brg_penjualan_status='AKTIF' AND YEAR(brg_penjualan_create_date) = YEAR(CURRENT_DATE())")->result_array();
		$data['laba_tahun_ini'] = $penjualan2[0]['harga_pen'] - $pembelian2[0]['harga_pem'];

		//top produk terjual
		$data['top_produk_terjual'] = executeQuery("SELECT mstr_barang.brg_nama, sum(tbl_brg_penjualan.BRG_PENJUALAN_QTY) as brg_top FROM `tbl_brg_penjualan` join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.ID_FK_BARANG WHERE BRG_PENJUALAN_STATUS='AKTIF' GROUP BY tbl_brg_penjualan.ID_FK_BARANG order by brg_top DESC LIMIT 4")->result_array();

		//penjualan kemarin
		$data['tanggal_kemarin'] = date('j F Y',strtotime("-1 days"));

		$jml_pen = executeQuery("SELECT count(id_pk_brg_penjualan) as jml_pen FROM tbl_brg_penjualan WHERE brg_penjualan_status='AKTIF' AND brg_penjualan_create_date=DATE(NOW() - INTERVAL 1 DAY)")->result_array();
		if($jml_pen[0]['jml_pen']==""){
			$jml_pen[0]['jml_pen'] = 0;
		}
		$data['jumlah_transaksi'] = $jml_pen[0]['jml_pen'];
		
		$penjualan3 = executeQuery("SELECT sum(brg_penjualan_harga*brg_penjualan_qty) as harga_pen, brg_penjualan_create_date FROM tbl_brg_penjualan WHERE brg_penjualan_status='AKTIF' AND brg_penjualan_create_date=DATE(NOW() - INTERVAL 1 DAY)")->result_array();
		if($penjualan3[0]['harga_pen']==""){
			$penjualan3[0]['harga_pen'] = 0;
		}
		$data['nilai_omset']=$penjualan3[0]['harga_pen'];
		
		$jml_brg = executeQuery("SELECT sum(brg_penjualan_qty) as brg, brg_penjualan_create_date FROM tbl_brg_penjualan WHERE brg_penjualan_status='AKTIF' AND brg_penjualan_create_date=DATE(NOW() - INTERVAL 1 DAY)")->result_array();

		$data['jumlah_barang']=$jml_brg[0]['brg'];

		//top 5 pelanggan
		 $top = executeQuery("select sum(brg_penjualan_harga*brg_penjualan_qty) as top, cust_name FROM tbl_brg_penjualan join mstr_penjualan on mstr_penjualan.ID_PK_PENJUALAN = tbl_brg_penjualan.ID_FK_PENJUALAN join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.ID_FK_CUSTOMER WHERE MONTH(mstr_penjualan.penj_tgl) = MONTH(CURRENT_DATE()) AND YEAR(mstr_penjualan.penj_tgl) = YEAR(CURRENT_DATE()) order by top desc limit 5")->result_array();

		if(count($top)==1){
			$top[0]['top'] = 0;
			$top[0]['cust_name'] = 0;
		}

		$data['top_5_pelanggan'] = $top;
		$this->load->view('welcome_message',$data);
	}
    
}