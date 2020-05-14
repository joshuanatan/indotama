<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends CI_Controller {

	public function index()
	{
        $where = array(
            "cust_status"=>"AKTIF"
        );
        $where1= array(
			"toko_status"=>"AKTIF"
		);
		$data['toko'] = selectRow("mstr_toko",$where1)->result_array();
        $data['view_customer'] = selectRow("mstr_customer",$where)->result_array();
		$this->load->view('customer/V_customer',$data);
	}

	public function register_customer(){
		$response["status"] = "SUCCESS";
         $this->form_validation->set_rules("cust_name","Nama","required");
         $this->form_validation->set_rules("cust_perusahaan","Perusahaan","required");
         $this->form_validation->set_rules("cust_email","Email","required|valid_email");
         $this->form_validation->set_rules("cust_telp","Telepon","required");
         $this->form_validation->set_rules("cust_hp","No HP","required");
         $this->form_validation->set_rules("cust_alamat","Alamat","required");
         $this->form_validation->set_rules("cust_keterangan","Keterangan","required");
         $this->form_validation->set_rules("id_fk_toko","Toko","required");
			
        if($this->form_validation->run()){
            $this->load->model("m_customer");

            $cust_name = $this->input->post("cust_name");
            $cust_perusahaan = $this->input->post("cust_perusahaan");
            $cust_email = $this->input->post("cust_email");
            $cust_telp = $this->input->post("cust_telp");
            $cust_hp = $this->input->post("cust_hp");
            $cust_alamat = $this->input->post("cust_alamat");
            $cust_keterangan = $this->input->post("cust_keterangan");
            $id_fk_toko = $this->input->post("id_fk_toko");
            $cust_status="AKTIF";
            $cust_create_date = date("Y-m-d H:i:s");
            $cust_last_modified = date("Y-m-d H:i:s");
            $id_create_data = $this->session->id_user;
            $id_last_modified = $this->session->id_user;

            $data=array(
                "cust_name" => $cust_name,
                "cust_perusahaan" => $cust_perusahaan,
                "cust_email" => $cust_email,
                "cust_telp" => $cust_telp,
                "cust_hp" => $cust_hp,
                "cust_alamat" => $cust_alamat,
                "cust_keterangan" => $cust_keterangan,
                "id_fk_toko" => $id_fk_toko,
                "cust_status" => $cust_status,
                "cust_create_date" => $cust_create_date,
                "cust_last_modified" => $cust_last_modified,
                "id_create_data" => $id_create_data,
                "id_last_modified" => $id_last_modified
            );
            insertRow("mstr_customer",$data);
        }else{
            $response["status"] = "ERROR";
            $response["msg"] = validation_errors();
            $this->session->set_flashdata("msg",$response['msg']);
        }
		//echo json_encode($response);
		redirect(md5('Customer'));
    }

    public function edit_customer(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("cust_name","Nama","required");
         $this->form_validation->set_rules("cust_perusahaan","Perusahaan","required");
         $this->form_validation->set_rules("cust_email","Email","required|valid_email");
         $this->form_validation->set_rules("cust_telp","Telepon","required");
         $this->form_validation->set_rules("cust_hp","No HP","required");
         $this->form_validation->set_rules("cust_alamat","Alamat","required");
         $this->form_validation->set_rules("cust_keterangan","Keterangan","required");
         $this->form_validation->set_rules("id_fk_toko","Toko","required");

		if($this->form_validation->run()){
            $this->load->model("m_employee");
			
			$cust_name = $this->input->post("cust_name");
            $cust_perusahaan = $this->input->post("cust_perusahaan");
            $cust_email = $this->input->post("cust_email");
            $cust_telp = $this->input->post("cust_telp");
            $cust_hp = $this->input->post("cust_hp");
            $cust_alamat = $this->input->post("cust_alamat");
            $cust_keterangan = $this->input->post("cust_keterangan");
            $id_fk_toko = $this->input->post("id_fk_toko");
            $cust_status="AKTIF";
            $cust_last_modified = date("Y-m-d H:i:s");
            $id_last_modified = $this->session->id_user;

			$data=array(
                "cust_name" => $cust_name,
                "cust_perusahaan" => $cust_perusahaan,
                "cust_email" => $cust_email,
                "cust_telp" => $cust_telp,
                "cust_hp" => $cust_hp,
                "cust_alamat" => $cust_alamat,
                "cust_keterangan" => $cust_keterangan,
                "id_fk_toko" => $id_fk_toko,
                "cust_last_modified" => $cust_last_modified,
                "id_last_modified" => $id_last_modified
			);
			$where = array(
				"id_pk_cust"=>$this->input->post("id_pk_cust")
			);
			updateRow("mstr_customer",$data,$where);
		}else{
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
            $this->session->set_flashdata("msg",$response['msg']);
		}
        //redirect(md5('Customer'));
        echo json_encode($response);
    }

    public function hapus_customer(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("id_pk_cust","ID Customer","required");

		if($this->form_validation->run()){
            $this->load->model("m_customer");
            $id_pk_cust = $this->input->post("id_pk_cust");

			if($this->m_customer->set_delete($id_pk_cust)){
				if($this->m_customer->delete()){
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
        //redirect(md5('Customer'));
        echo json_encode($response);
    }
    
}
