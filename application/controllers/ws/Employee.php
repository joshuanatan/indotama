<?php
defined("BASEPATH") or exit("No direct script");
class Employee extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_employee");
        $columns = $this->m_employee->columns();
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
        
        $this->load->model("m_employee");
        $result = $this->m_employee->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
				$response["content"][$a]["id"] = $result["data"][$a]["id_pk_employee"];
				$response["content"][$a]["nama"] = $result["data"][$a]["emp_nama"];
				$response["content"][$a]["npwp"] = $result["data"][$a]["emp_npwp"];
				$response["content"][$a]["ktp"] = $result["data"][$a]["emp_ktp"];
				$response["content"][$a]["hp"] = $result["data"][$a]["emp_hp"];
				$response["content"][$a]["alamat"] = $result["data"][$a]["emp_alamat"];
				$response["content"][$a]["kode_pos"] = $result["data"][$a]["emp_kode_pos"];
				$response["content"][$a]["foto_npwp"] = $result["data"][$a]["emp_foto_npwp"];
				$response["content"][$a]["foto_ktp"] = $result["data"][$a]["emp_foto_ktp"];
				$response["content"][$a]["foto_lain"] = $result["data"][$a]["emp_foto_lain"];
				$response["content"][$a]["foto"] = "<img src='". base_url() . "asset/uploads/employee/foto/". $result["data"][$a]["emp_foto"]."' width='100px'>";
				$response["content"][$a]["gaji"] = $result["data"][$a]["emp_gaji"];
				$response["content"][$a]["startdate"] = $result["data"][$a]["emp_startdate"];
				$response["content"][$a]["enddate"] = $result["data"][$a]["emp_enddate"];
				$response["content"][$a]["rek"] = $result["data"][$a]["emp_rek"];
				$response["content"][$a]["gender"] = $result["data"][$a]["emp_gender"];
				$response["content"][$a]["suff"] = $result["data"][$a]["emp_suff"];
				$response["content"][$a]["status"] = $result["data"][$a]["emp_status"];
				$response["content"][$a]["toko"] = $result["data"][$a]["toko_nama"];
				$response["content"][$a]["last_modified"] = $result["data"][$a]["emp_last_modified"];
				$response["content"][$a]["id_toko"] = $result["data"][$a]["id_fk_toko"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "foto",
            "nama",
            "toko",
            "hp",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_employee");
        $result = $this->m_employee->list();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg"];
                $response["content"][$a]["kode"] = $result[$a]["brg_kode"];
                $response["content"][$a]["nama"] = $result[$a]["brg_nama"];
                $response["content"][$a]["ket"] = $result[$a]["brg_ket"];
                $response["content"][$a]["minimal"] = $result[$a]["brg_minimal"];
                $response["content"][$a]["status"] = $result[$a]["brg_status"];
                $response["content"][$a]["satuan"] = $result[$a]["brg_satuan"];
                $response["content"][$a]["image"] = $result[$a]["brg_image"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_last_modified"];
                $response["content"][$a]["merk_nama"] = $result[$a]["brg_merk_nama"];
                $response["content"][$a]["jenis_nama"] = $result[$a]["brg_jenis_nama"];
                $response["content"][$a]["ukuran"] = $result[$a]["ukuran"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Barang List";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
		$this->form_validation->set_rules("emp_npwp","Npwp","required");
		$this->form_validation->set_rules("emp_nama","Nama","required");
		$this->form_validation->set_rules("emp_ktp","KTP","required|numeric");
		$this->form_validation->set_rules("emp_hp","No HP","required|numeric");
		$this->form_validation->set_rules("emp_alamat","Alamat","required");
		$this->form_validation->set_rules("emp_kode_pos","Kode Pos","required");
		//$this->form_validation->set_rules("emp_foto_npwp","Foto Npwp","required");
		//$this->form_validation->set_rules("emp_foto_ktp","Foto Ktp","required");
		//$this->form_validation->set_rules("emp_foto_lain","Foto Lain","required");
		//$this->form_validation->set_rules("emp_foto","Foto","required");
		$this->form_validation->set_rules("emp_gaji","Gaji","required|numeric");
		$this->form_validation->set_rules("emp_startdate","Mulai Bekerja","required");
		if($this->input->post("radio_enddate")=="TIDAK"){
			$this->form_validation->set_rules("emp_enddate","Akhir Bekerja","required");
		}
		$this->form_validation->set_rules("emp_rek","No Rekening","required|numeric");
		$this->form_validation->set_rules("emp_gender","Jenis Kelamin","required");
		$this->form_validation->set_rules("emp_suff","suff","required");
			
			//npwp
			$config1['upload_path']          = './asset/uploads/employee/npwp/';
			$config1['allowed_types']        = 'jpg|png|jpeg';

			$this->load->library('upload', $config1);
			
			
			if ( ! $this->upload->do_upload('emp_foto_npwp')){
				$error = array('error' => $this->upload->display_errors());
				//print_r($error);
				$emp_foto_npwp = "-";
			}
			else{
				$emp_foto_npwp = $this->upload->data('file_name');
			}

			//ktp
			$config2['upload_path']          = './asset/uploads/employee/ktp/';
			$config2['allowed_types']        = 'jpg|png|jpeg';

			$this->upload->initialize($config2);
			if ( ! $this->upload->do_upload('emp_foto_ktp')){
				$error = array('error' => $this->upload->display_errors());
				//print_r($error);
				$emp_foto_ktp = "-";
			}
			else{
				$emp_foto_ktp = $this->upload->data('file_name');
			}

			//lain
			$config3['upload_path']          = './asset/uploads/employee/lain/';
			$config3['allowed_types']        = 'jpg|png|jpeg';

			$this->upload->initialize($config3);
			if ( ! $this->upload->do_upload('emp_foto_lain')){
				$error = array('error' => $this->upload->display_errors());
				//print_r($error);
				$emp_foto_lain = "-";
			}
			else{
				$emp_foto_lain = $this->upload->data('file_name');
			}

			//foto
			$config4['upload_path']          = './asset/uploads/employee/foto/';
			$config4['allowed_types']        = 'jpg|png|jpeg';

			$this->upload->initialize($config4);
			if ( ! $this->upload->do_upload('emp_foto')){
				$error = array('error' => $this->upload->display_errors());
				//print_r($error);
				$emp_foto = "-";
			}
			else{
				$emp_foto = $this->upload->data('file_name');
			}

			if($emp_foto_npwp=="-" ||$emp_foto_ktp=="-" ||$emp_foto_lain=="-" ||$emp_foto=="-"){
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
					$emp_startdate = $this->input->post("emp_startdate");
					if($this->input->post("radio_enddate")=="TIDAK"){
						$emp_enddate = $this->input->post("emp_enddate");
					}else{
						$emp_enddate = "0000-00-00";
					}
					
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
    }
    public function update(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("emp_npwp","Npwp","required");
		$this->form_validation->set_rules("emp_nama","Nama","required");
		$this->form_validation->set_rules("emp_ktp","KTP","required|numeric");
		$this->form_validation->set_rules("emp_hp","No HP","required|numeric");
		$this->form_validation->set_rules("emp_alamat","Alamat","required");
		$this->form_validation->set_rules("emp_kode_pos","Kode Pos","required");
		//$this->form_validation->set_rules("emp_foto_npwp","Foto Npwp","required");
		//$this->form_validation->set_rules("emp_foto_ktp","Foto Ktp","required");
		//$this->form_validation->set_rules("emp_foto_lain","Foto Lain","required");
		//$this->form_validation->set_rules("emp_foto","Foto","required");
		$this->form_validation->set_rules("emp_gaji","Gaji","required|numeric");
		$this->form_validation->set_rules("emp_startdate","Mulai Bekerja","required");
		if($this->input->post("radio_enddate")=="TIDAK"){
			$this->form_validation->set_rules("emp_enddate","Akhir Bekerja","required");
		}
		$this->form_validation->set_rules("emp_rek","No Rekening","required|numeric");
		$this->form_validation->set_rules("emp_gender","Jenis Kelamin","required");
		$this->form_validation->set_rules("emp_suff","suff","required");

		//npwp
		$config1['upload_path']          = './asset/uploads/employee/npwp/';
		$config1['allowed_types']        = 'jpg|png|jpeg';

		$this->load->library('upload', $config1);
		
		
		if ( ! $this->upload->do_upload('emp_foto_npwp')){
			$error = array('error' => $this->upload->display_errors());
			//print_r($error);
			$emp_foto_npwp = "-";
		}
		else{
			$emp_foto_npwp = $this->upload->data('file_name');
		}

		//ktp
		$config2['upload_path']          = './asset/uploads/employee/ktp/';
		$config2['allowed_types']        = 'jpg|png|jpeg';

		$this->upload->initialize($config2);
		if ( ! $this->upload->do_upload('emp_foto_ktp')){
			$error = array('error' => $this->upload->display_errors());
			//print_r($error);
			$emp_foto_ktp = "-";
		}
		else{
			$emp_foto_ktp = $this->upload->data('file_name');
		}

		//lain
		$config3['upload_path']          = './asset/uploads/employee/lain/';
		$config3['allowed_types']        = 'jpg|png|jpeg';

		$this->upload->initialize($config3);
		if ( ! $this->upload->do_upload('emp_foto_lain')){
			$error = array('error' => $this->upload->display_errors());
			//print_r($error);
			$emp_foto_lain = "-";
		}
		else{
			$emp_foto_lain = $this->upload->data('file_name');
		}

		//foto
		$config4['upload_path']          = './asset/uploads/employee/foto/';
		$config4['allowed_types']        = 'jpg|png|jpeg';

		$this->upload->initialize($config4);
		if ( ! $this->upload->do_upload('emp_foto')){
			$error = array('error' => $this->upload->display_errors());
			//print_r($error);
			$emp_foto = "-";
		}
		else{
			$emp_foto = $this->upload->data('file_name');
		}

		if($this->form_validation->run()){
            $this->load->model("m_employee");
			
			$emp_npwp = $this->input->post("emp_npwp");
			$emp_nama = $this->input->post("emp_nama");
			$emp_ktp = $this->input->post("emp_ktp");
			$emp_hp = $this->input->post("emp_hp");
			$emp_alamat = $this->input->post("emp_alamat");
			$emp_kode_pos = $this->input->post("emp_kode_pos");
			
			$emp_gaji = $this->input->post("emp_gaji");
			$emp_startdate = $this->input->post("emp_startdate");
			if($this->input->post("radio_enddate")=="TIDAK"){
				$emp_enddate = $this->input->post("emp_enddate");
			}else{
				$emp_enddate = "0000-00-00";
			}
			
			$emp_rek = $this->input->post("emp_rek");
			$emp_gender = $this->input->post("emp_gender");
			$emp_suff = $this->input->post("emp_suff");
			$id_fk_toko = $this->input->post("id_fk_toko");
			//$emp_status = "AKTIF";

			if($emp_foto_npwp!="-"){
				$data['emp_foto_npwp']=$emp_foto_npwp;
			}

			if($emp_foto_ktp!="-"){
				$data['emp_foto_ktp']=$emp_foto_ktp;
			}

			if($emp_foto_lain!="-"){
				$data['emp_foto_lain']=$emp_foto_lain;
			}

			if($emp_foto!="-"){
				$data['emp_foto']=$emp_foto;
			}

			$data=array(
				"emp_nama"=>$emp_nama,
				"emp_npwp"=>$emp_npwp,
				"emp_ktp"=>$emp_ktp,
				"emp_hp"=>$emp_hp,
				"emp_alamat"=>$emp_alamat,
				"emp_kode_pos"=>$emp_kode_pos,
				//"emp_foto_npwp"=>$emp_foto_npwp,
				//"emp_foto_ktp"=>$emp_foto_ktp,
				//"emp_foto_lain"=>$emp_foto_lain,
				//"emp_foto"=>$emp_foto,
				"emp_gaji"=>$emp_gaji,
				"emp_startdate"=>$emp_startdate,
				"emp_enddate"=>$emp_enddate,
				"emp_rek"=>$emp_rek,
				"emp_gender"=>$emp_gender,
				"emp_suff"=>$emp_suff,
				"id_last_modified" => $this->session->id_user,
				"id_fk_toko" =>$id_fk_toko 
			);
			$where = array(
				"id_pk_employee"=>$this->input->post("id_pk_employee")
			);
			updateRow("mstr_employee",$data,$where);
		}else{
			$response["status"] = "ERROR";
			$response["msg"] = validation_errors();
            $this->session->set_flashdata("msg",$response['msg']);
		}
        //redirect(md5('Employee'));
        echo json_encode($response);
    }
    public function delete(){
        $response["status"] = "SUCCESS";
		//$this->form_validation->set_rules("id_pk_employee","ID Employee","required");

		//if($this->form_validation->run()){
            $this->load->model("m_employee");
            $id_pk_employee = $this->input->get("id");

			if($this->m_employee->set_delete($id_pk_employee)){
				if($this->m_employee->delete()){
					$response["msg"] = "Data is deleted";
				}else{
					$response["status"] = "ERROR";
                    $response["msg"] = "Delete function is error";
				}
			}else{
				$response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
			}
		//}else{
			//$response["status"] = "ERROR";
			//$response["msg"] = validation_errors();
//$this->session->set_flashdata("msg",$response['msg']);
		//}
        //redirect(md5('Employee'));
        echo json_encode($response);
    }
}
?>