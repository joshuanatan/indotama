<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");
class Login extends CI_Controller
{

	public function index()
	{
		$this->session->sess_destroy();
		$this->register_user();
		$this->load->view('login/V_log_in');
	}
	public function set_session_dummy()
	{
		$this->session->id_user = 1;
		echo "session is set. id_user session: " . $this->session->id_user;
	}
	public function login_method()
	{
		$response["status"] = "SUCCESS";
		$user_name = $this->input->post("user_name");
		$user_pass = md5($this->input->post("user_pass"));

		$this->load->model("m_user");
		if ($this->m_user->set_login($user_name, $user_pass)) {
			$data = $this->m_user->login();
			if ($data) {

				$user_data = array(
					"id_user" => $data['id'],
					"user_name" => $data['name'],
					"user_email" => $data['email'],
					"role" => $data['role'],
					"user_status" => $data['status'],
					"foto" => $data['foto']
				);
				$this->session->set_userdata($user_data);
				redirect(md5("Dashboard"));
			} else {
				$response["status"] = "ERROR";
				$response["msg"] = "Login function is error!";
				$response["msg"] = "Login gagal! Cek kembali username dan password Anda";
				$this->session->set_flashdata("gagals_login", $response['msg']);
			}
		} else {
			$response["status"] = "ERROR";
			$response["msg"] = "Login gagal! Cek kembali username dan password Anda";
			$this->session->set_flashdata("gagals_login", $response['msg']);
		}
		redirect(md5("Login"));
	}
	public function forget_password()
	{
		$this->load->view('login/V_forget_password');
	}

	public function forget_password_method()
	{
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("user_email", "Email", "required|valid_email");
		$this->form_validation->set_rules("user_name", "User Name", "required");

		$user_email = $this->input->post("user_email");
		$user_name = $this->input->post("user_name");
		if ($this->form_validation->run()) {
			$where = array(
				"user_status" => "AKTIF",
				"user_email" => $user_email,
				"user_name" => $user_name
			);
			$result = selectRow("mstr_user",$where);
			if ($result->num_rows() > 0) {
				$result = $result->result_array();
				$data['id_pk_user'] = $result[0]['id_pk_user'];
				$this->load->view('login/V_reset_password', $data);
			} else {
				$response["status"] = "ERROR";
				$response["msg"] = "Email tidak terdaftar dalam sistem!";
				$this->session->set_flashdata("eror_send", $response['msg']);
			}
		} else {
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
			$this->session->set_flashdata("msg", $response['msg']);
			redirect("99dea78007133396a7b8ed70578ac6ae");
		}
	}

	public function change_password_method()
	{
		$where = array(
			"id_pk_user" => $this->input->post("id_pk_user")
		);
		$passlama = get1Value("mstr_user", "user_pass", $where);
		if ($passlama == md5($this->input->post("pass_lama"))) {
			$data = array(
				"user_pass" => md5($this->input->post("pass_baru")),
				"user_last_modified" => date("Y-m-d H:i:s"),
				"id_last_modified" => $_SESSION['id_user']
			);
			updateRow("mstr_user", $data, $where);
			redirect("login/logout");
		} else {
			$this->session->set_flashdata("gagal_pass", "Password sekarang tidak sesuai!");
			redirect("login/change_password");
		}
	}

	function cek_password()
	{
		$passbaru = $this->input->post('passb');
		$passkonfir = $this->input->post('passk');

		if ($passbaru != $passkonfir || $passbaru == "" || $passbaru == "" || strlen($passbaru) < 8) {
			echo "1";
		} else {
			echo "0";
		}
	}

	function pass_reset()
	{
		$config = array(
			array(
				"field" => "user_pass",
				"label" => "Password",
				"rules" => "required|min_length[8]"
			),
		);
		$this->form_validation->set_rules($config);
		if ($this->form_validation->run()) {

			$data = array(
				"user_pass" => md5($this->input->post('user_pass')),
				"id_last_modified" => $this->input->post('id_pk_user'),
				"user_last_modified" => date("Y-m-d H:i:s")
			);
			$where = array(
				"id_pk_user" => $this->input->post('id_pk_user')
			);
			updateRow("mstr_user", $data, $where);
			$this->session->set_flashdata("status", "success");
			redirect("99dea78007133396a7b8ed70578ac6ae");
		} else {
			$this->session->set_flashdata("status", "danger");
			$this->session->set_flashdata("msg", validation_errors());
			redirect("login/forget_get_new_pass/" . $this->input->post('id_pk_user'));
		}
	}

	public function logout()
	{
		redirect(md5("Login"));
	}
	private function register_user()
	{
		$this->load->model("m_user");
		$result = $this->m_user->list_data();
		if ($result->num_rows() == 0) {
			$data = array(
				"user_name" => "admin",
				"user_pass" => md5("admin"),
				"user_email" => "admin@example.com",
				"user_status" => "AKTIF",
				"id_fk_role" => 1,
				"user_create_date" => date("Y-m-d H:i:s"),
				"user_last_modified" => date("Y-m-d H:i:s"),
				"id_create_date" => 0,
				"id_last_modified" => 0
			);
			insertRow("mstr_user", $data);
		}
	}
}
