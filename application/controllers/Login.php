<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");
class Login extends CI_Controller {

	public function index()
	{
		$this->session->sess_destroy();
		$this->register_user();
		$this->load->view('login/V_log_in');
	}
	public function set_session_dummy(){
		$this->session->id_user = 1;
		echo "session is set. id_user session: ".$this->session->id_user;
	}
	public function login_method()
	{
        
		$response["status"] = "SUCCESS";
		$user_name = $this->input->post("user_name");
		$user_pass = md5($this->input->post("user_pass"));

		$this->load->model("m_user");
		if($this->m_user->set_login($user_name,$user_pass)){
			$data = $this->m_user->login();
			if($data){
				
				$user_data = array(
					"id_user"=>$data['id'],
					"user_name"=>$data['name'],
					"user_email"=>$data['email'],
					"role"=>$data['role'],
					"user_status"=>$data['status']
				);
				$this->session->set_userdata($user_data);
				redirect(md5("Dashboard"));
			}
			else{
				$response["status"] = "ERROR";
				$response["msg"] = "Login function is error!";
			}
		}
		else{
			$response["status"] = "ERROR";
			$response["msg"] = "Setter function is error";
			
		}
		redirect(md5("Login"));
	}
	public function forget_password(){
		$this->load->view('login/V_forget_password');
	}

	public function forget_password_method(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("user_email","Email","required|valid_email");

		$user_email = $this->input->post("user_email");
		if($this->form_validation->run()){
			$where = array(
				"user_status" => "AKTIF",
				"user_email"=>$user_email
			);
			if(isExistsInTable("mstr_user",$where)){
				$data['id_pk_user'] = get1Value("mstr_user","id_pk_user",$where);
				$data['user_name'] = get1Value("mstr_user","user_name",$where);
				$data['user_pass'] = get1Value("mstr_user","user_pass",$where);
				
				//send email
				$this->load->library('phpmailer_lib');
				$mail = $this->phpmailer_lib->load();
				$mail->IsSMTP();

				$mail->Host       = "smtp.gmail.com";
				$mail->SMTPSecure = "ssl";
				$mail->Port       = 465;
				$mail->Username   = "clickrentsistech@gmail.com";
				$mail->Password   = "sistech123";

				$mail->SMTPAuth   = true;
				$mail->SetFrom('clickrentsistech@gmail.com', 'INDOTAMA');
				$mail->AddAddress($user_email);
				$mail->Subject    = "Reset Your Password";
				$mail->IsHTML(true);

				$link_reset = base_url() . 'Login/forget_get_new_pass/' . md5($data['id_pk_user']) . '/' . $data['user_pass'];

				$isiEmail = "<center style='color:black'>LOGO<br>
				<h2>Reset Password</h2>
				<p>Hi<br>Reset your password by clicking <a href='".$link_reset."'>HERE</a></center>
				";
				$mail->Body       = $isiEmail;
	
				//$mail->SMTPDebug = 2;

				if(!$mail->Send()) {
					//echo "Mailer Error: " . $mail->ErrorInfo;
					$response["status"] = "ERROR";
					$response["msg"] = "Gagal reset password! Silahkan hubungi admin sistem!";
					$this->session->set_flashdata("msg",$response['msg']);
				} 
				else {
					$response["msg"] = "Cek email Anda dan ikuti instruksi selanjutnya untuk melakukan reset password!";
					$this->session->set_flashdata("success_send",$response['msg']);
				}
			}
			else{
				$response["status"] = "ERROR";
				$response["msg"] = "User tidak ditemukan!";
			}
		}
		else{
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
			$this->session->set_flashdata("msg",$response['msg']);
		}
		redirect("99dea78007133396a7b8ed70578ac6ae");
	}

	public function change_password(){
		$this->load->view("login/V_change_password");
	}

	public function change_password_method(){
		$where =array(
			"id_pk_user"=>$this->input->post("id_pk_user")
		);
		$passlama = get1Value("mstr_user","user_pass",$where);
		if($passlama==md5($this->input->post("pass_lama"))){
			$data = array(
				"user_pass"=>md5($this->input->post("pass_baru")),
				"user_last_modified"=>date("Y-m-d H:i:s"),
				"id_last_modified"=>$_SESSION['id_user']
			);
			updateRow("mstr_user",$data,$where);
			redirect("login/logout");
		}else{
			$this->session->set_flashdata("gagal_pass","Password sekarang tidak sesuai!");
			redirect("login/change_password");
		}
	}

	public function forget_get_new_pass($md5_id,$md5){
		$where = array(
			"user_pass"=>$md5
		);
		$admin = selectRow("mstr_user",$where)->result_array();

		if(count($admin)==0){
			$this->session->set_flashdata("msg","Your link has been expired!");
			redirect("99dea78007133396a7b8ed70578ac6ae");
		}else if(count($admin)==1){
			$data['id_pk_user'] = $admin[0]['id_pk_user'];
		}else{
			for($x=0; $x<count($admin); $x++){
				if(md5($admin[$x]['id_pk_user'])==$md5_id){
					$data['id_pk_user'] = $admin[$x]['id_pk_user'];
				}
			}
		}
		$this->load->view('login/V_reset_password',$data);
	}

	function cek_password(){
        $passbaru = $this->input->post('passb');
        $passkonfir = $this->input->post('passk');

        if($passbaru != $passkonfir || $passbaru=="" || $passbaru=="" || strlen($passbaru)<8){
            echo "1";
		}
		else{
            echo "0";
        }
    }

	function pass_reset(){
        $config = array(
            array(
                "field" => "user_pass",
                "label" => "Password",
                "rules" => "required|min_length[8]"
            ),
        );
        $this->form_validation->set_rules($config);
        if($this->form_validation->run()){
			
            $data = array(
                "user_pass"=>md5($this->input->post('user_pass')),
				"id_last_modified"=>$this->input->post('id_pk_user'),
				"user_last_modified"=>date("Y-m-d H:i:s")
			);
            $where = array(
                "id_pk_user"=>$this->input->post('id_pk_user')
            );
            updateRow("mstr_user",$data,$where);
			$this->session->set_flashdata("status","success");
			redirect("99dea78007133396a7b8ed70578ac6ae");
            
        }
        else{
            $this->session->set_flashdata("status","danger");
            $this->session->set_flashdata("msg",validation_errors());
            redirect("login/forget_get_new_pass/" . $this->input->post('id_pk_user'));
        }
	}
	
	public function logout(){
		redirect(md5("Login"));
	}
	private function register_user(){
		$this->load->model("m_user");
		$result = $this->m_user->list();
		if($result->num_rows() == 0){
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
			insertRow("mstr_user",$data);
		}
	}
}
