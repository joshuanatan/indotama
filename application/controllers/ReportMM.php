<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReportMM extends CI_Controller {

	public function Pembelian()
	{
		$this->load->view('V_report_pembelian');
	}

	public function Penjualan()
	{
		$this->load->view('V_report_penjualan');
	}

	public function Stock()
	{
		$this->load->view('V_report_stock');
	}

	public function Keuangan()
	{
		$this->load->view('V_report_keuangan');
	}

}
