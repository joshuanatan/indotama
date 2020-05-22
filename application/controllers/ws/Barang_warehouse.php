<?php
defined("BASEPATH") or exit("no direct script");
class Barang_warehouse extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_brg_warehouse");
        $columns = $this->m_brg_warehouse->columns();
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
        $id_warehouse = $this->input->get("id_warehouse");
        $this->load->model("m_brg_warehouse");
        $this->m_brg_warehouse->set_id_fk_warehouse($id_warehouse);
        $result = $this->m_brg_warehouse->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg_warehouse"];
                $response["content"][$a]["qty"] = $result["data"][$a]["brg_warehouse_qty"];
                $response["content"][$a]["notes"] = $result["data"][$a]["brg_warehouse_notes"];
                $response["content"][$a]["status"] = $result["data"][$a]["brg_warehouse_status"];
                $response["content"][$a]["id_brg"] = $result["data"][$a]["id_fk_brg"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_warehouse_last_modified"];
                $response["content"][$a]["nama_brg"] = $result["data"][$a]["brg_nama"];
                $response["content"][$a]["kode_brg"] = $result["data"][$a]["brg_kode"];
                $response["content"][$a]["ket_brg"] = $result["data"][$a]["brg_ket"];
                $response["content"][$a]["minimal_brg"] = $result["data"][$a]["brg_minimal"];
                $response["content"][$a]["satuan_brg"] = $result["data"][$a]["brg_satuan"];
                $response["content"][$a]["image_brg"] = $result["data"][$a]["brg_image"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "kode_brg",
            "nama_brg",
            "ket_brg",
            "qty",
            "notes",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $check = $this->input->post("check");
        if($check != ""){
            $id_fk_warehouse = $this->input->post("id_warehouse");
            $counter = 0;
            foreach($check as $a){
                $this->form_validation->set_rules("brg".$a,"brg","required");
                $this->form_validation->set_rules("brg_qty".$a,"brg_qty","required");
                $this->form_validation->set_rules("brg_notes".$a,"brg_notes","required");
                if($this->form_validation->run()){
                    $brg_warehouse_qty = $this->input->post("brg_qty".$a);
                    $brg_warehouse_notes = $this->input->post("brg_notes".$a);
                    $brg_warehouse_status = "AKTIF";

                    $barang = $this->input->post("brg".$a);
                    $this->load->model("m_barang");
                    $this->m_barang->set_brg_nama($barang);
                    $result = $this->m_barang->detail_by_name();

                    if($result->num_rows() > 0){
                        $result = $result->result_array();
                        $id_fk_brg = $result[0]["id_pk_brg"];
                        $this->load->model("m_brg_warehouse");
                        if($this->m_brg_warehouse->set_insert($brg_warehouse_qty,$brg_warehouse_notes,$brg_warehouse_status,$id_fk_brg,$id_fk_warehouse)){
                            if($this->m_brg_warehouse->insert()){
                                $response["itmsts"][$counter] = "SUCCESS";
                                $response["itmmsg"][$counter] = "Data is recorded to database";
                            }
                            else{
                                $response["status"] = "ERROR";
                                $response["itmsts"][$counter] = "ERROR";
                                $response["itmmsg"][$counter] = "Insert function error";
                            }
                        }
                        else{
                            $response["status"] = "ERROR";
                            $response["itmsts"][$counter] = "ERROR";
                            $response["itmmsg"][$counter] = "Setter function error";
                        }
                    }
                }
                else{
                    $response["status"] = "ERROR";
                    $response["itmsts"][$counter] = "ERROR";
                    $response["itmmsg"][$counter] = validation_errors();
                }
                $counter++;
            }
        }
        else{
            $response["itmstsall"] = "ERROR";
            $response["itmmsgall"] = "No Checks on Item";
        }
        echo json_encode($response);
    }
    public function update(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id","id","required");
        $this->form_validation->set_rules("brg","brg","required");
        $this->form_validation->set_rules("stok","stok","required");
        $this->form_validation->set_rules("notes","notes","required");
        if($this->form_validation->run()){
            $this->load->model("m_brg_warehouse");
            $id_pk_brg_warehouse = $this->input->post("id");
            $brg_warehouse_qty = $this->input->post("stok");
            $brg_warehouse_notes = $this->input->post("notes");

            $barang = $this->input->post("brg");
            $this->load->model("m_barang");
            $this->m_barang->set_brg_nama($barang);
            $result = $this->m_barang->detail_by_name();

            if($result->num_rows() > 0){
                $result = $result->result_array();
                $id_fk_brg = $result[0]["id_pk_brg"];
                $this->load->model("m_brg_warehouse");
                if($this->m_brg_warehouse->set_update($id_pk_brg_warehouse,$brg_warehouse_qty,$brg_warehouse_notes,$id_fk_brg)){
                    if($this->m_brg_warehouse->update()){
                        $data["msg"] = "Data is updated to database";
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
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = validation_errors();
        }
        echo json_encode($response);
    }
    public function delete(){
        $response["status"] = "SUCCESS";
        $id_brg_warehouse = $this->input->get("id");
        if($id_brg_warehouse != "" && is_numeric($id_brg_warehouse)){
            $this->load->model("m_brg_warehouse");
            if($this->m_brg_warehouse->set_delete($id_brg_warehouse)){
                if($this->m_brg_warehouse->delete()){
                    $response["msg"] = "Data is deleted from database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Delete function error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid ID Supplier";
        }
        echo json_encode($response);
    }
}