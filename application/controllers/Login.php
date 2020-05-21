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
		$user_pass = $this->input->post("user_pass");

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
		$this->form_validation->set_rules("user_name","Username","required");

		$user_name = $this->input->post("user_name");
		echo $user_name;
		if($this->form_validation->run()){
			$where = array(
				"user_status" => "AKTIF"
			);
			if(isExistsInTable("mstr_user",$where)){
				$where = array(
					"user_status" => "AKTIF",
					"user_name" =>$user_name
				);
				if(isExistsInTable("mstr_user",$where)){
					$data['user_email'] = get1Value("mstr_user","user_email",$where);
					$data['id_pk_user'] = get1Value("mstr_user","id_pk_user",$where);
					echo $data['id_pk_user'];
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
					$mail->AddAddress($data['user_email']);
					$mail->Subject    = "Reset Your Password";
					$mail->IsHTML(true);

					$isiEmail = "<center style='color:black'>LOGO<br>
					<h2>Reset Password</h2>
					<p>Hi<br>Reset your password by clicking <a href='".base_url()."Login/forget_get_new_pass/".$data['id_pk_user']."'>HERE</a></center>
					";
					$mail->Body       = $isiEmail;
		
					//$mail->SMTPDebug = 2;

					if(!$mail->Send()) {
						//echo "Mailer Error: " . $mail->ErrorInfo;
						echo "<script>alert('Gagal reset password! Silahkan hubungi admin sistem!'); window.location.href='../99dea78007133396a7b8ed70578ac6ae';</script>";
					} 
					else {
						echo "<script>alert('Cek email Anda dan ikuti instruksi selanjutnya untuk melakukan reset password!'); window.location.href='../99dea78007133396a7b8ed70578ac6ae';</script>";
					}
				}
				else{
					$response["status"] = "ERROR";
					$response["msg"] = "Employee tidak ditemukan!";
				}
			}
			else{
				$response["status"] = "ERROR";
				$response["msg"] = "Employee tidak ditemukan!";
			}
		}
		else{
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
			$this->session->set_flashdata("msg",$response['msg']);
			redirect("login/forget_password");
		}
	}

	public function forget_get_new_pass($id_pk_user){
		$data['id_pk_user'] = $id_pk_user;
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
                "user_pass"=>password_hash($this->input->post('user_pass'),PASSWORD_DEFAULT),
				"id_last_modified"=>$this->input->post('id_pk_user'),
				"user_last_modified"=>date("Y-m-d H:i:s")
			);
            $where = array(
                "id_pk_user"=>$this->input->post('id_pk_user')
            );
            updateRow("mstr_user",$data,$where);

			echo "<script>alert('Password telah diperbarui! Silahkan login'); window.location.href='../99dea78007133396a7b8ed70578ac6ae';</script>";
            
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
                "user_pass" => password_hash("admin",PASSWORD_DEFAULT),
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
