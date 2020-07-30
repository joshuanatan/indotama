<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
	public function __construct(){
		parent::__construct();
        $this->check_session();
    }
    public function check_session(){
        if($this->session->id_user == ""){
            $this->session->set_flashdata("msg","Session expired, please login");
            redirect("login");
        }
	}
	public function index(){
		$this->load->view("welcome_message");
	}
	public function generate(){	
		$this->load->view('dashboard');
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