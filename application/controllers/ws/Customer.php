<?php
defined("BASEPATH") or exit("No direct script");
class Customer extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_customer");
        $columns = $this->m_customer->columns();
        if(count($columns) > 0){
            for($a = 0; $a<count($columns); $a++){
                $response["content"][$a]["col_name"] = $columns[$a]["col_disp"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        echo json_encode($response);
    }
    public function content(){
        $response["status"] = "SUCCESS";
        $response["content"] = array();

        $order_by = $this->input->get("orderBy");
        $order_direction = $this->input->get("orderDirection");
        $page = $this->input->get("page");
        $search_key = $this->input->get("searchKey");
        $data_per_page = 20;
        
        $this->load->model("m_customer");
        $result = $this->m_customer->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){

                if(file_exists(FCPATH."asset/uploads/customer/npwp/".$result["data"][$a]["cust_foto_npwp"])){
                    $response["content"][$a]["foto_npwp"] = $result["data"][$a]["cust_foto_npwp"];
                }
                else{
                    $response["content"][$a]["foto_npwp"] = "noimage.jpg";
                }
                if(file_exists(FCPATH."asset/uploads/customer/krt_nama/".$result["data"][$a]["cust_foto_kartu_nama"])){
                    $response["content"][$a]["foto_kartu_nama"] = $result["data"][$a]["cust_foto_kartu_nama"];
                }
                else{
                    $response["content"][$a]["foto_kartu_nama"] = "noimage.jpg";
                }

                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_cust"];
                $response["content"][$a]["name"] = $result["data"][$a]["cust_name"];
                $response["content"][$a]["suff"] = $result["data"][$a]["cust_suff"];
                $response["content"][$a]["perusahaan"] = $result["data"][$a]["cust_perusahaan"];
                $response["content"][$a]["email"] = $result["data"][$a]["cust_email"];
                $response["content"][$a]["telp"] = $result["data"][$a]["cust_telp"];
                $response["content"][$a]["hp"] = $result["data"][$a]["cust_hp"];
                $response["content"][$a]["alamat"] = $result["data"][$a]["cust_alamat"];
                $response["content"][$a]["keterangan"] = $result["data"][$a]["cust_keterangan"];
                $response["content"][$a]["status"] = $result["data"][$a]["cust_status"];
                $response["content"][$a]["no_npwp"] = $result["data"][$a]["cust_no_npwp"];
                $response["content"][$a]["badan_usaha"] = $result["data"][$a]["cust_badan_usaha"];
                $response["content"][$a]["no_rekening"] = $result["data"][$a]["cust_no_rekening"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["cust_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "name",
            "perusahaan",
            "email",
            "telp",
            "hp",
            "alamat",
            "keterangan",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function list_data(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_customer");
        $result = $this->m_customer->list_data();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_cust"];
                $response["content"][$a]["name"] = $result[$a]["cust_name"];
                $response["content"][$a]["suff"] = $result[$a]["cust_suff"];
                $response["content"][$a]["perusahaan"] = $result[$a]["cust_perusahaan"];
                $response["content"][$a]["email"] = $result[$a]["cust_email"];
                $response["content"][$a]["telp"] = $result[$a]["cust_telp"];
                $response["content"][$a]["hp"] = $result[$a]["cust_hp"];
                $response["content"][$a]["alamat"] = $result[$a]["cust_alamat"];
                $response["content"][$a]["keterangan"] = $result[$a]["cust_keterangan"];
                $response["content"][$a]["status"] = $result[$a]["cust_status"];
                $response["content"][$a]["last_modified"] = $result[$a]["cust_last_modified"];
                $response["content"][$a]["no_npwp"] = $result[$a]["cust_no_npwp"];
                $response["content"][$a]["foto_npwp"] = $result[$a]["cust_foto_npwp"];
                $response["content"][$a]["foto_kartu_nama"] = $result[$a]["cust_foto_kartu_nama"];
                $response["content"][$a]["badan_usaha"] = $result[$a]["cust_badan_usaha"];
                $response["content"][$a]["no_rekening"] = $result[$a]["cust_no_rekening"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Customer List";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("cust_name","Nama","required");
        $this->form_validation->set_rules("cust_suff","Panggilan","required");
        $this->form_validation->set_rules("cust_perusahaan","Perusahaan","required");
        $this->form_validation->set_rules("cust_email","Email","required|valid_email");
        $this->form_validation->set_rules("cust_telp","Telepon","required");
        $this->form_validation->set_rules("cust_hp","No HP","required");
        $this->form_validation->set_rules("cust_alamat","Alamat","required");
        $this->form_validation->set_rules("cust_keterangan","Keterangan","required");
        $this->form_validation->set_rules("cust_badan_usaha","cust_badan_usaha","required");
        $this->form_validation->set_rules("cust_npwp","cust_npwp","required");
        $this->form_validation->set_rules("cust_rek","cust_rek","required");
			
        if($this->form_validation->run()){
            $this->load->model("m_customer");
            

            $config1['upload_path'] = './asset/uploads/customer/npwp/';
			$config1['allowed_types'] = 'jpg|png|jpeg';
			$this->load->library('upload', $config1);
			if ( ! $this->upload->do_upload('cust_foto_npwp')){
				$error = array('error' => $this->upload->display_errors());
				$cust_foto_npwp = "noimage.jpg";
			}
			else{
				$cust_foto_npwp = $this->upload->data('file_name');
            }
            
            $config2['upload_path']          = './asset/uploads/customer/krt_nama/';
			$config2['allowed_types']        = 'jpg|png|jpeg';
			$this->upload->initialize($config2);
			if ( ! $this->upload->do_upload('cust_krt_nama')){
				$error = array('error' => $this->upload->display_errors());
				$cust_foto_kartu_nama = "noimage.jpg";
			}
			else{
				$cust_foto_kartu_nama = $this->upload->data('file_name');
			}


            $cust_name = $this->input->post("cust_name");
            $cust_badan_usaha = $this->input->post("cust_badan_usaha");
            $cust_no_npwp = $this->input->post("cust_npwp");
            $cust_no_rekening = $this->input->post("cust_rek");
            $cust_suff = $this->input->post("cust_suff");
            $cust_perusahaan = $this->input->post("cust_perusahaan");
            $cust_email = $this->input->post("cust_email");
            $cust_telp = $this->input->post("cust_telp");
            $cust_hp = $this->input->post("cust_hp");
            $cust_alamat = $this->input->post("cust_alamat");
            $cust_keterangan = $this->input->post("cust_keterangan");
            $cust_status = "AKTIF";

            if($this->m_customer->set_insert($cust_name,$cust_suff,$cust_perusahaan,$cust_email,$cust_telp,$cust_hp,$cust_alamat,$cust_keterangan,$cust_status,$cust_no_npwp,$cust_foto_npwp,$cust_foto_kartu_nama,$cust_badan_usaha,$cust_no_rekening)){
                if($this->m_customer->insert()){
                    $response["msg"] = "Data is recorded to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Insert function error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = validation_errors();
        }
		echo json_encode($response);
    }
    public function update(){
        $response["status"] = "SUCCESS";
		$this->form_validation->set_rules("cust_name","Nama","required");
		$this->form_validation->set_rules("cust_suff","Panggilan","required");
        $this->form_validation->set_rules("cust_perusahaan","Perusahaan","required");
        $this->form_validation->set_rules("cust_email","Email","required|valid_email");
        $this->form_validation->set_rules("cust_telp","Telepon","required");
        $this->form_validation->set_rules("cust_hp","No HP","required");
        $this->form_validation->set_rules("cust_alamat","Alamat","required");
        $this->form_validation->set_rules("cust_keterangan","Keterangan","required");
        
        if($this->form_validation->run()){

            $config1['upload_path'] = './asset/uploads/customer/npwp/';
			$config1['allowed_types'] = 'jpg|png|jpeg';
			$this->load->library('upload', $config1);
			if ( ! $this->upload->do_upload('cust_foto_npwp')){
				$error = array('error' => $this->upload->display_errors());
				$cust_foto_npwp = $this->input->post("cust_foto_npwp_current");
			}
			else{
				$cust_foto_npwp = $this->upload->data('file_name');
            }
            
            $config2['upload_path']          = './asset/uploads/customer/krt_nama/';
			$config2['allowed_types']        = 'jpg|png|jpeg';
			$this->upload->initialize($config2);
			if ( ! $this->upload->do_upload('cust_krt_nama')){
				$error = array('error' => $this->upload->display_errors());
				$cust_foto_kartu_nama = $this->input->post("cust_krt_nama_current");
			}
			else{
				$cust_foto_kartu_nama = $this->upload->data('file_name');
			}

            $id_pk_cust = $this->input->post("id_pk_cust");
            $cust_name = $this->input->post("cust_name");
            $cust_badan_usaha = $this->input->post("cust_badan_usaha");
            $cust_no_npwp = $this->input->post("cust_npwp");
            $cust_no_rekening = $this->input->post("cust_rek");
            $cust_suff = $this->input->post("cust_suff");
            $cust_perusahaan = $this->input->post("cust_perusahaan");
            $cust_email = $this->input->post("cust_email");
            $cust_telp = $this->input->post("cust_telp");
            $cust_hp = $this->input->post("cust_hp");
            $cust_alamat = $this->input->post("cust_alamat");
            $cust_keterangan = $this->input->post("cust_keterangan");

            $this->load->model("m_customer");
            if($this->m_customer->set_update($id_pk_cust,$cust_name,$cust_suff,$cust_perusahaan,$cust_email,$cust_telp,$cust_hp,$cust_alamat,$cust_keterangan,$cust_no_npwp,$cust_foto_npwp,$cust_foto_kartu_nama,$cust_badan_usaha,$cust_no_rekening)){
                if($this->m_customer->update()){
                    $response["msg"] = "Data is updated to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = validation_errors();
        }
        echo json_encode($response);
    }
    public function delete(){
        $response["status"] = "SUCCESS";
        $id_pk_customer = $this->input->get("id");
        if($id_pk_customer != "" && is_numeric($id_pk_customer)){
            $this->load->model("m_customer");
            if($this->m_customer->set_delete($id_pk_customer)){
                if($this->m_customer->delete()){
                    $response["msg"] = "Data is deleted from database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function is error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "ID is invalid";
        }
        echo json_encode($response);
    }
}
?>