<?php
defined("BASEPATH") or exit("no direct script");
class Barang_cabang extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->update_list_barang();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_brg_cabang");
        $columns = $this->m_brg_cabang->columns();
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
        $id_cabang = $this->input->get("id_cabang");
        $this->load->model("m_brg_cabang");
        $this->m_brg_cabang->set_id_fk_cabang($id_cabang);
        $result = $this->m_brg_cabang->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg_cabang"];
                $response["content"][$a]["qty"] = $result["data"][$a]["brg_cabang_qty"];
                $response["content"][$a]["notes"] = $result["data"][$a]["brg_cabang_notes"];
                $response["content"][$a]["last_price"] = $result["data"][$a]["brg_cabang_last_price"];
                $response["content"][$a]["harga"] = $result["data"][$a]["brg_harga"];
                $response["content"][$a]["status"] = $result["data"][$a]["brg_cabang_status"];
                $response["content"][$a]["id_brg"] = $result["data"][$a]["id_fk_brg"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_cabang_last_modified"];
                $response["content"][$a]["nama_brg"] = $result["data"][$a]["brg_nama"];
                $response["content"][$a]["kode_brg"] = $result["data"][$a]["brg_kode"];
                $response["content"][$a]["ket_brg"] = $result["data"][$a]["brg_ket"];
                $response["content"][$a]["minimal_brg"] = $result["data"][$a]["brg_minimal"];
                $response["content"][$a]["satuan_brg"] = $result["data"][$a]["brg_satuan"];
                $response["content"][$a]["image_brg"] = $result["data"][$a]["brg_image"];
                $response["content"][$a]["jenis"] = $result["data"][$a]["brg_jenis_nama"];
                $response["content"][$a]["merk"] = $result["data"][$a]["brg_merk_nama"];
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
            "last_price",
            "notes",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $id_cabang = $this->input->get("id_cabang");
        $this->load->model("m_brg_cabang");
        $this->m_brg_cabang->set_id_fk_cabang($id_cabang);
        $result = $this->m_brg_cabang->list();
        if($result->num_rows() > 0 ){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_cabang"]."";
                $response["content"][$a]["qty"] = $result[$a]["brg_cabang_qty"]."";
                $response["content"][$a]["notes"] = $result[$a]["brg_cabang_notes"]."";
                $response["content"][$a]["last_price"] = $result[$a]["brg_cabang_last_price"]."";
                $response["content"][$a]["status"] = $result[$a]["brg_cabang_status"]."";
                $response["content"][$a]["id_brg"] = $result[$a]["id_fk_brg"]."";
                $response["content"][$a]["last_modified"] = $result[$a]["brg_cabang_last_modified"]."";
                $response["content"][$a]["nama"] = $result[$a]["brg_nama"]."";
                $response["content"][$a]["kode"] = $result[$a]["brg_kode"]."";
                $response["content"][$a]["ket"] = $result[$a]["brg_ket"]."";
                $response["content"][$a]["minimal"] = $result[$a]["brg_minimal"]."";
                $response["content"][$a]["satuan"] = $result[$a]["brg_satuan"]."";
                $response["content"][$a]["image"] = $result[$a]["brg_image"]."";
                $response["content"][$a]["harga"] = $result[$a]["brg_harga"]."";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Tidak ada barang cabang";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $check = $this->input->post("check");
        if($check != ""){
            $id_fk_cabang = $this->input->post("id_cabang");
            $counter = 0;
            foreach($check as $a){
                $this->form_validation->set_rules("brg".$a,"brg","required");
                $this->form_validation->set_rules("brg_qty".$a,"brg_qty","required");
                $this->form_validation->set_rules("brg_notes".$a,"brg_notes","required");
                if($this->form_validation->run()){
                    $brg_cabang_qty = $this->input->post("brg_qty".$a);
                    $brg_cabang_notes = $this->input->post("brg_notes".$a);
                    $brg_cabang_status = "AKTIF";

                    $barang = $this->input->post("brg".$a);
                    $this->load->model("m_barang");
                    $this->m_barang->set_brg_nama($barang);
                    $result = $this->m_barang->detail_by_name();

                    if($result->num_rows() > 0){
                        $result = $result->result_array();
                        $id_fk_brg = $result[0]["id_pk_brg"];

                        $this->load->model("m_brg_cabang");
                        $this->m_brg_cabang->set_id_fk_brg($id_fk_brg);
                        $this->m_brg_cabang->set_id_fk_cabang($id_fk_cabang);
                        if(!$this->m_brg_cabang->is_item_exists()){
                            $this->m_brg_cabang->set_insert(0,"-","aktif",$id_fk_brg,$id_fk_cabang);
                            $this->m_brg_cabang->insert();
                        }

                        $this->load->model("m_brg_cabang");
                        if($this->m_brg_cabang->set_insert($brg_cabang_qty,$brg_cabang_notes,$brg_cabang_status,$id_fk_brg,$id_fk_cabang)){
                            if($this->m_brg_cabang->insert()){
                                $response["itmsts"][$counter] = "SUCCESS";
                                $response["itmmsg"][$counter] = "Data is recorded to database";
                            }
                            else{
                                $response["itmsts"][$counter] = "ERROR";
                                $response["itmmsg"][$counter] = "Insert function error";
                            }
                        }
                        else{
                            $response["itmsts"][$counter] = "ERROR";
                            $response["itmmsg"][$counter] = "Setter function error";
                        }
                    }
                }
                else{
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
            $this->load->model("m_brg_cabang");
            $id_pk_brg_cabang = $this->input->post("id");
            $brg_cabang_qty = $this->input->post("stok");
            $brg_cabang_notes = $this->input->post("notes");

            $barang = $this->input->post("brg");
            $this->load->model("m_barang");
            $this->m_barang->set_brg_nama($barang);
            $result = $this->m_barang->detail_by_name();

            if($result->num_rows() > 0){
                $result = $result->result_array();
                $id_fk_brg = $result[0]["id_pk_brg"];
                $this->load->model("m_brg_cabang");
                if($this->m_brg_cabang->set_update($id_pk_brg_cabang,$brg_cabang_qty,$brg_cabang_notes,$id_fk_brg)){
                    if($this->m_brg_cabang->update()){
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
        $id_brg_cabang = $this->input->get("id");
        if($id_brg_cabang != "" && is_numeric($id_brg_cabang)){
            $this->load->model("m_brg_cabang");
            if($this->m_brg_cabang->set_delete($id_brg_cabang)){
                if($this->m_brg_cabang->delete()){
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
    private function update_list_barang(){
        //select semua yang belom ada   
        $this->load->model("m_brg_cabang");
        $this->m_brg_cabang->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_brg_cabang->list_not_exists_brg_kombinasi();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                /*harusnya bukan 0 tapi sejumlah kombinasi qty * mstrkombinasi qty*/
                /*kalau misalnya ada, itu harusnya ditambahin bukan di abaikan*/
                if($this->m_brg_cabang->set_insert($result[$a]["add_qty"],"Auto insert from item existance check","aktif",$result[$a]["id_barang_kombinasi"],$this->session->id_cabang)){
                    if($this->m_brg_cabang->insert()){
                    }
                    else{
                    }
                }
                else{
                }
            }   
        }
        //loop masuk
    }
}