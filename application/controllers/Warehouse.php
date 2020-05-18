<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse extends CI_Controller {

	public function index()
	{
        $where = array(
            "warehouse_status"=>"AKTIF"
        );
        $data['view_warehouse'] = selectRow("mstr_warehouse",$where)->result_array();
		$this->load->view('warehouse/V_warehouse',$data);
	}
    public function warehouse_barang($id_pk_warehouse){
        $where= array(
            "brg_warehouse_status"=>"AKTIF",
            "id_fk_warehouse"=>$id_pk_warehouse
        );
        $where1 = array(
            "id_pk_warehouse"=>$id_pk_warehouse
        );
        $data['warehouse'] = selectRow("mstr_warehouse",$where1)->result_array();
        $data['view_barang_wh'] = selectRow("tbl_brg_warehouse",$where)->result_array();
        $this->load->view("brg_warehouse/V_brg_warehouse",$data);
    }
    
    public function register_brg_warehouse(){
		$response["status"] = "SUCCESS";
         $this->form_validation->set_rules("warehouse_nama","Nama Warehouse","required");
         $this->form_validation->set_rules("warehouse_alamat","Alamat","required");
         $this->form_validation->set_rules("warehouse_notelp","No Telepon","required");
         $this->form_validation->set_rules("warehouse_desc","Deskripsi","required");
			
        if($this->form_validation->run()){
            $this->load->model("m_warehouse");

            $warehouse_nama = $this->input->post("warehouse_nama");
            $warehouse_alamat = $this->input->post("warehouse_alamat");
            $warehouse_notelp = $this->input->post("warehouse_notelp");
            $warehouse_desc = $this->input->post("warehouse_desc");
            $warehouse_status = "AKTIF";
            $warehouse_create_date = date("Y-m-d H:i:s");
            $warehouse_last_modified = date("Y-m-d H:i:s");
            $id_create_data = $this->session->id_user;
            $id_last_modified = $this->session->id_user;

            $data=array(
                "warehouse_nama" => $warehouse_nama,
                "warehouse_alamat" => $warehouse_alamat,
                "warehouse_notelp" => $warehouse_notelp,
                "warehouse_desc" => $warehouse_desc,
                "warehouse_status" => $warehouse_status,
                "warehouse_create_date" => $warehouse_create_date,
                "warehouse_last_modified" => $warehouse_last_modified,
                "id_create_data" => $id_create_data,
                "id_last_modified" => $id_last_modified
            );
            insertRow("mstr_warehouse",$data);
        }else{
            $response["status"] = "ERROR";
            $response["msg"] = validation_errors();
            $this->session->set_flashdata("msg",$response['msg']);
        }
		//echo json_encode($response);
		redirect(md5('Warehouse'));
    }
}
