<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('V_log_in');
	}

	public function login_method()
	{
		$response["status"] = "SUCCESS";
		$user_name = $this->input->post("user_name");
		$user_pass = $this->input->post("user_pass");

		$this->load->model("m_user");
		if($this->m_user->set_login($user_name,$user_pass)){
			$data = $this->m_user->login();
			if($data){
				$user_data = array(
					"id_user"=>$data['id'],
					"user_name"=>$data['name'],
					"user_status"=>$data['status']
				);
				$this->session->set_userdata($user_data);
				$this->load->view('V_dashboard');
			}else{
				$response["status"] = "ERROR";
				$response["msg"] = "Login function is error!";
				redirect(md5("login"));
			}
		}else{
			$response["status"] = "ERROR";
			$response["msg"] = "Setter function is error";
			redirect("login");
		}
		//echo json_encode($response);
	}


	public function register_user(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("user_name","Username","required|min_length[5]|max_length[15]");
		$this->form_validation->set_rules("user_pass","Password","required|min_length[8]");

		if($this->form_validation->run()){
			$this->load->model("m_user");
			$user_name = "joahuanatan";//$this->input->post("user_name");
			$user_pass = "12345678";//$this->input->post("user_pass");
			$user_status = "AKTIF";

			if($this->m_user->set_insert($user_name,$user_pass,$user_status)){
				if($this->m_user->insert()){
					$response["msg"] = "Data is recorded to database";
				}else{
					$response["status"] = "ERROR";
                    $response["msg"] = "Insert function is error";
				}
			}else{
				$response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
			}
		}else{
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
			$this->session->set_flashdata("msg",$response['msg']);
		}
		echo json_encode($response);
	}

	public function forget_password(){
		$this->load->view('V_forget_password');
	}

	public function forget_password_method(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("user_name","Username","required");

		$user_name = $this->input->post("user_name");

		if($this->form_validation->run()){
			$where = array(
				"user_status" => "AKTIF"
			);
			if(isExistsInTable("user",$where)){
				$where = array(
					"emp_status" => "AKTIF"
				);
				if(isExistsInTable("employee",$where)){
					$data['emp_email'] = get1Value("employee","emp_email",$where);

					//send email
					//buat view cek email
					//buat view new password
					//buat method update password dan ke login lagi
					$this->load->view("V_forget_pw_sendmail");
				}
			}else{

			}
		}else{
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
			$this->session->set_flashdata("msg",$response['msg']);
			redirect("login/forget_password");
		}
	}
}
