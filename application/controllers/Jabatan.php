<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jabatan extends CI_Controller {

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
            "jabatan_status"=>"AKTIF"
        );
        $data['view_jabatan'] = selectRow("mstr_jabatan",$where)->result_array();

        
        //$data['view_jabatan'] = selectRow("mstr_jabatan")->result_array();
		$this->load->view('V_jabatan',$data);
	}

	public function register_jabatan(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("jabatan_nama","Nama Jabatan","required");

		if($this->form_validation->run()){
            $this->load->model("m_jabatan");
            $jabatan_nama = $this->input->post("jabatan_nama");
            $jabatan_status = "AKTIF";

			if($this->m_jabatan->set_insert($jabatan_nama,$jabatan_status)){
				if($this->m_jabatan->insert()){
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
		redirect(md5('Jabatan'));
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
