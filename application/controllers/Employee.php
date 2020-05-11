<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends CI_Controller {

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
        $where = array(
            "emp_status"=>"AKTIF"
		);
		$where1= array(
			"toko_status"=>"AKTIF"
		);
		$data['toko'] = selectRow("mstr_toko",$where1)->result_array();
		$data['toko_all'] = selectRow("mstr_toko")->result_array();
        $data['view_employee'] = selectRow("mstr_employee",$where)->result_array();
		$this->load->view('V_employee',$data);
	}

	public function register_employee(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("emp_npwp","Npwp","required");
		$this->form_validation->set_rules("emp_nama","Nama","required");
		$this->form_validation->set_rules("emp_ktp","KTP","required");
		$this->form_validation->set_rules("emp_hp","No HP","required");
		$this->form_validation->set_rules("emp_alamat","Alamat","required");
		$this->form_validation->set_rules("emp_kode_pos","Kode Pos","required");
		//$this->form_validation->set_rules("emp_foto_npwp","Foto Npwp","required");
		//$this->form_validation->set_rules("emp_foto_ktp","Foto Ktp","required");
		//$this->form_validation->set_rules("emp_foto_lain","Foto Lain","required");
		//$this->form_validation->set_rules("emp_foto","Foto","required");
		$this->form_validation->set_rules("emp_gaji","Gaji","required");
		$this->form_validation->set_rules("emp_startdate","Mulai Bekerja","required");
		$this->form_validation->set_rules("emp_enddate","Akhir Bekerja","required");
		$this->form_validation->set_rules("emp_rek","No Rekening","required");
		$this->form_validation->set_rules("emp_gender","Jenis Kelamin","required");
		$this->form_validation->set_rules("emp_suff","suff","required");
			
			//npwp
			$config1['upload_path']          = './asset/images/employee/npwp/';
			$config1['allowed_types']        = 'jpg|png|jpeg';

			$this->load->library('upload', $config1);
			
			
			if ( ! $this->upload->do_upload('emp_foto_npwp')){
				$error = array('error' => $this->upload->display_errors());
				print_r($error);
				$emp_foto_npwp = "-";
			}
			else{
				$emp_foto_npwp = $this->upload->data('file_name');
			}

			//ktp
			$config2['upload_path']          = './asset/images/employee/ktp/';
			$config2['allowed_types']        = 'jpg|png|jpeg';

			$this->upload->initialize($config2);
			if ( ! $this->upload->do_upload('emp_foto_ktp')){
				$error = array('error' => $this->upload->display_errors());
				print_r($error);
				$emp_foto_ktp = "-";
			}
			else{
				$emp_foto_ktp = $this->upload->data('file_name');
			}

			//lain
			$config3['upload_path']          = './asset/images/employee/lain/';
			$config3['allowed_types']        = 'jpg|png|jpeg';

			$this->upload->initialize($config3);
			if ( ! $this->upload->do_upload('emp_foto_lain')){
				$error = array('error' => $this->upload->display_errors());
				print_r($error);
				$emp_foto_lain = "-";
			}
			else{
				$emp_foto_lain = $this->upload->data('file_name');
			}

			//foto
			$config4['upload_path']          = './asset/images/employee/foto/';
			$config4['allowed_types']        = 'jpg|png|jpeg';

			$this->upload->initialize($config4);
			if ( ! $this->upload->do_upload('emp_foto')){
				$error = array('error' => $this->upload->display_errors());
				print_r($error);
				$emp_foto = "-";
			}
			else{
				$emp_foto = $this->upload->data('file_name');
			}

			if($emp_foto_npwp=="-" ||$emp_foto_ktp=="-" ||$emp_foto_lain=="-" ||$emp_foto=="-"){
				echo $emp_foto_npwp;
				echo $emp_foto_ktp;
				echo $emp_foto_lain;
				echo $emp_foto;
				$response["status"] = "ERROR";
                $response["msg"] = "Foto harus berformat .jpg atau .png";
			}else{
				if($this->form_validation->run()){
					$this->load->model("m_employee");
		
					$emp_npwp = $this->input->post("emp_npwp");
					$emp_nama = $this->input->post("emp_nama");
					$emp_ktp = $this->input->post("emp_ktp");
					$emp_hp = $this->input->post("emp_hp");
					$emp_alamat = $this->input->post("emp_alamat");
					$emp_kode_pos = $this->input->post("emp_kode_pos");
					
					$emp_gaji = $this->input->post("emp_gaji");
					
					if($this->input->post("radio_enddate")=="TIDAK"){
						$emp_startdate = $this->input->post("emp_startdate");
					}else{
						$emp_startdate = "0000-00-00";
					}
					$emp_enddate = $this->input->post("emp_enddate");
					$emp_rek = $this->input->post("emp_rek");
					$emp_gender = $this->input->post("emp_gender");
					$emp_suff = $this->input->post("emp_suff");
					$id_fk_toko = $this->input->post("id_fk_toko");
					$emp_status = "AKTIF";
		
					$data=array(
						"emp_nama"=>$emp_nama,
						"emp_npwp"=>$emp_npwp,
						"emp_ktp"=>$emp_ktp,
						"emp_hp"=>$emp_hp,
						"emp_alamat"=>$emp_alamat,
						"emp_kode_pos"=>$emp_kode_pos,
						"emp_foto_npwp"=>$emp_foto_npwp,
						"emp_foto_ktp"=>$emp_foto_ktp,
						"emp_foto_lain"=>$emp_foto_lain,
						"emp_foto"=>$emp_foto,
						"emp_gaji"=>$emp_gaji,
						"emp_startdate"=>$emp_startdate,
						"emp_enddate"=>$emp_enddate,
						"emp_rek"=>$emp_rek,
						"emp_gender"=>$emp_gender,
						"emp_suff"=>$emp_suff,
						"emp_status"=>$emp_status,
						"emp_create_date" => date("Y-m-d H:i:s"),
						"emp_last_modified" => date("Y-m-d H:i:s"),
						"id_create_data" => $this->session->id_user,
						"id_last_modified" => $this->session->id_user,
						"id_fk_toko" =>$id_fk_toko 
					);
					insertRow("mstr_employee",$data);
				}else{
					$response["status"] = "ERROR";
					$response["msg"] = validation_errors();
					$this->session->set_flashdata("msg",$response['msg']);
				}
			}
		echo json_encode($response);
		//redirect(md5('Jabatan'));
    }

    public function edit_jabatan(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("jabatan_nama","Nama Jabatan","required");

		if($this->form_validation->run()){
            $this->load->model("m_jabatan");
            $id_pk_jabatan = $this->input->post("id_pk_jabatan");
            $jabatan_nama = $this->input->post("jabatan_nama");

			if($this->m_jabatan->set_update($id_pk_jabatan,$jabatan_nama)){
				if($this->m_jabatan->update()){
					$response["msg"] = "Data is updated";
				}else{
					$response["status"] = "ERROR";
                    $response["msg"] = "Update function is error";
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
        redirect(md5('Jabatan'));
        //echo json_encode($response);
    }

    public function hapus_jabatan(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("id_pk_jabatan","ID Jabatan","required");

		if($this->form_validation->run()){
            $this->load->model("m_jabatan");
            $id_pk_jabatan = $this->input->post("id_pk_jabatan");

			if($this->m_jabatan->set_delete($id_pk_jabatan)){
				if($this->m_jabatan->delete()){
					$response["msg"] = "Data is deleted";
				}else{
					$response["status"] = "ERROR";
                    $response["msg"] = "Delete function is error";
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
        redirect(md5('Jabatan'));
        //echo json_encode($response);
    }
    
}
