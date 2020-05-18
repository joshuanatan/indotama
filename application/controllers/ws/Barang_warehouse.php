<?php
defined("BASEPATH") or exit("No Direct Script");
class Barang_warehouse extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }

    public function register(){
        $response["status"] = "SUCCESS";
        $checks = $this->input->post("check");
        if($checks != ""){
            foreach($checks as $a){
                $this->form_validation->set_rules("brg".$a,"Barang","required");
                $this->form_validation->set_rules("brg_warehouse_notes".$a,"Notes","required");
                $this->form_validation->set_rules("id_fk_Warehouse","Warehouse","required");
                  
                if($this->form_validation->run()){

                    $brg = $this->input->post("brg".$a);
                    $id_fk_brg = get1Value("mstr_barang","id_pk_brg",array("brg_nama"=>$brg));
                    $brg_warehouse_notes=$this->input->post("brg_warehouse_notes".$a);
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
                    insertRow("tbl_brg_warehouse",$data);
                    echo "insert $a";
                }else{
                    $response["status"] = "ERROR";
                    $response["msg"] = validation_errors();
                    $this->session->set_flashdata("msg",$response['msg']);
                }
             }
             //redirect("warehouse/warehouse_barang/".$id_fk_warehouse);
             echo json_encode($response);   
       }else{
           
       }
    }
}