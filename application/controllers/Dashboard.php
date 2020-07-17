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


	public function view_profile(){
		$id_user = $this->input->post("id_user");
		$where = array(
			"id_pk_user"=>$id_user
		);
		$user = selectRow("mstr_user",$where)->result_array();
		$id_employee = get1Value("mstr_user","id_fk_employee",$where);


		$employee = selectRow("mstr_employee",array("id_pk_employee"=>$id_employee))->result_array();
		$data['panggilan_profile'] = $employee[0]['emp_suff'];
		$data['nama_profile'] =$employee[0]['emp_nama'];
		$data['foto_profile'] = base_url() . 'asset/uploads/employee/foto/'.$employee[0]['emp_foto'];
		$data['email_profile'] = $user[0]['user_email'];
		
		$data['role_profile'] = get1Value("mstr_jabatan","jabatan_nama",array("id_pk_jabatan"=>$user[0]['id_fk_role']));
		$data['gender_profile'] =$employee[0]['emp_gender'];
		$data['toko_profile'] = get1Value("mstr_toko","toko_nama",array("id_pk_toko"=>$employee[0]['id_fk_toko']));

		echo json_encode($data);
	}

	public function edit_profile_view($id_user){
		if($id_user==$this->session->id_user){
			$data['user'] = selectRow("mstr_user",array("id_pk_user"=>$id_user))->result_array();
			$id_employee = $data['user'][0]['id_fk_employee'];
			$data['employee'] = selectRow("mstr_employee",array("id_pk_employee"=>$id_employee))->result_array();
			$this->load->view("login/V_edit_profile",$data);
		}else{
			redirect("notfound");
		}
		
	}

	public function edit_profile_method(){
		$this->form_validation->set_rules("id_employee","id_employee","required");
		$this->form_validation->set_rules("id_user","id_user","required");
		$this->form_validation->set_rules("name","Username","required");
		$this->form_validation->set_rules("email","Email","required");
		$this->form_validation->set_rules("emp_nama","Nama","required");
		$this->form_validation->set_rules("emp_hp","No HP","required");
		$this->form_validation->set_rules("emp_alamat","Alamat","required");
		$this->form_validation->set_rules("emp_kode_pos","Kode Pos","required");
		$this->form_validation->set_rules("emp_rek","Rekening","required");
		$this->form_validation->set_rules("emp_gender","Jenis Kelamin","required");
		$this->form_validation->set_rules("emp_suff","Panggilan","required");

		
		//foto
		$config4['upload_path']          = './asset/images/employee/foto/';
		$config4['allowed_types']        = 'jpg|png|jpeg';

		$this->load->library('upload', $config4);
		if ( ! $this->upload->do_upload('emp_foto')){
			$error = array('error' => $this->upload->display_errors());
			//print_r($error);
			$emp_foto = get1Value("mstr_employee","emp_foto",array("id_pk_employee"=>$this->input->post("id_employee")));
		}
		else{
			$emp_foto = $this->upload->data('file_name');
		}

		if($this->form_validation->run()){
			$where = array(
				"id_pk_employee"=>$this->input->post("id_employee")
			);
			$data = array(
				"emp_nama" => $this->input->post("emp_nama"),
				"emp_hp" => $this->input->post("emp_hp"),
				"emp_alamat" => $this->input->post("emp_alamat"),
				"emp_kode_pos" => $this->input->post("emp_kode_pos"),
				"emp_rek" => $this->input->post("emp_rek"),
				"emp_gender" => $this->input->post("emp_gender"),
				"emp_suff" => $this->input->post("emp_suff"),
				"emp_foto" => $this->input->post("emp_foto"),
				"emp_last_modified" => date("Y-m-d H:i:s"),
				"id_last_modified" => $this->session->id_user,
			);
			updateRow("mstr_employee",$data,$where);

			$where = array(
				"id_pk_user"=>$this->input->post("id_user")
			);
			$data = array(
				"user_name" => $this->input->post("name"),
				"user_email" => $this->input->post("email"),
				"user_last_modified" => date("Y-m-d H:i:s"),
				"id_last_modified" => $this->session->id_user,
			);
			updateRow("mstr_user",$data,$where);
			$response["status"] = "SUCCESS";
			$response["msg"] = "Edit profile berhasil";
			$this->session->set_flashdata("msg_b",$response['msg']);
		}else{
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
			$this->session->set_flashdata("msg_e",$response['msg']);
		}
		$id_userr = $this->input->post("id_user");
		redirect("dashboard/edit_profile_view/$id_userr");
	}
	
	
}