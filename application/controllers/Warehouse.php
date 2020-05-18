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
        $data['view_barang_wh'] = executeQuery("SELECT * FROM tbl_brg_warehouse join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_warehouse.id_fk_brg join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = mstr_barang.id_fk_brg_jenis join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = mstr_barang.id_fk_brg_merk WHERE tbl_brg_warehouse.id_fk_warehouse='$id_pk_warehouse' AND brg_warehouse_status = 'AKTIF'")->result_array();
        $this->load->view("brg_warehouse/V_brg_warehouse",$data);
    }

    public function hapus_brg_warehouse(){
		$response["status"] = "SUCCESS";
		$this->form_validation->set_rules("id_pk_brg_warehouse","ID Barang Warehouse","required");

		if($this->form_validation->run()){
            $this->load->model("m_brg_warehouse");
            $id_pk_brg_warehouse = $this->input->post("id_pk_brg_warehouse");
            $id_fk_warehouse = $this->input->post("id_fk_warehouse");

			if($this->m_brg_warehouse->set_delete($id_pk_brg_warehouse)){
				if($this->m_brg_warehouse->delete()){
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
        redirect("warehouse/warehouse_barang/".$id_fk_warehouse);
        //echo json_encode($response);
    }

    public function edit_brg_warehouse(){
        $this->form_validation->set_rules("id_fk_brg","Barang","required");
        $this->form_validation->set_rules("brg_warehouse_notes","Notes","required");
        $this->form_validation->set_rules("id_fk_Warehouse","Warehouse","required");
                  
                if($this->form_validation->run()){

                    $id_fk_brg = $this->input->post("id_fk_brg");
                    $brg_warehouse_notes=$this->input->post("brg_warehouse_notes");
                    $id_fk_warehouse = $this->input->post("id_fk_Warehouse");
                    $brg_warehouse_status = "AKTIF";
                    $brg_warehouse_qty=0;
                    $brg_warehouse_create_date = date("Y-m-d H:i:s");
                    $brg_warehouse_last_modified = date("Y-m-d H:i:s");
                    $id_create_data = $this->session->id_user;
                    $id_last_modified = $this->session->id_user;

                    $data = array(
                        "id_fk_brg" => $id_fk_brg,
                        "brg_warehouse_notes" => $brg_warehouse_notes,
                        "id_fk_warehouse" => $id_fk_warehouse,
                        "brg_warehouse_status" => $brg_warehouse_status,
                        "brg_warehouse_qty" => $brg_warehouse_qty,
                        "brg_warehouse_create_date" => $brg_warehouse_create_date,
                        "brg_warehouse_last_modified" => $brg_warehouse_last_modified,
                        "id_create_data" => $id_create_data,
                        "id_last_modified" => $id_last_modified
                    );
                    $where = array(
                        "id_pk_brg_warehouse"=>$this->input->post("id_pk_brg_warehouse")
                    );
                    updateRow("tbl_brg_warehouse",$data);
                }else{
                    $response["status"] = "ERROR";
                    $response["msg"] = validation_errors();
                    $this->session->set_flashdata("msg",$response['msg']);
                }
                redirect("warehouse/warehouse_barang/".$id_fk_warehouse);
             }
             
             //echo json_encode($response);   
       
    
}
