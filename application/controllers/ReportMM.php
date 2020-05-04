<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReportMM extends CI_Controller {

	public function Pembelian()
	{
		$this->load->view('report_pembelian');
	}

	public function Penjualan()
	{
		$this->load->view('report_penjualan');
	}

	public function Stock()
	{
		$this->load->view('report_stock');
	}

	public function Keuangan()
	{
		$this->load->view('report_keuangan');
	}

}
