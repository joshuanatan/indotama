<?php
defined("BASEPATH") or exit("No Direct Script");
class Penerimaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_penerimaan");
        $tipe = $this->input->get("tipe_penerimaan");
        if($tipe){
            $columns = $this->m_penerimaan->columns($tipe);
        }
        else{
            $columns = $this->m_penerimaan->columns();
        }
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
        $type = $this->input->get("type"); //CABANG / WAREHOUSE
        $tipe_penerimaan = $this->input->get("tipe_penerimaan");
        
        $this->load->model("m_penerimaan");
        $flag = true;
        if($type == "WAREHOUSE" && $this->session->id_warehouse){
            $this->m_penerimaan->set_id_fk_warehouse($this->session->id_warehouse);
        }
        else if($type == "CABANG" && $this->session->id_cabang){
            $this->m_penerimaan->set_id_fk_cabang($this->session->id_cabang);
        }
        else{
            $flag = false;
            $response["status"] = "ERROR";
            $response["msg"] = "Type not registered";
        }

        if($flag){
            if($tipe_penerimaan == ""){
                $tipe_penerimaan = "pembelian";
                $this->m_penerimaan->set_penerimaan_tempat($type);
                $result = $this->m_penerimaan->content($page,$order_by,$order_direction,$search_key,$data_per_page,$tipe_penerimaan);
                if($result["data"]->num_rows() > 0){
                    $result["data"] = $result["data"]->result_array();
                    for($a = 0; $a<count($result["data"]); $a++){
                        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_penerimaan"];
                        $response["content"][$a]["tgl"] = $result["data"][$a]["penerimaan_tgl"];
                        $response["content"][$a]["status"] = $result["data"][$a]["penerimaan_status"];
                        $response["content"][$a]["id_pembelian"] = $result["data"][$a]["id_fk_pembelian"];
                        $response["content"][$a]["tempat"] = $result["data"][$a]["penerimaan_tempat"];
                        if($response["content"][$a]["tempat"] == "WAREHOUSE"){
                            $response["content"][$a]["id_tempat_penerimaan"] = $result["data"][$a]["id_fk_warehouse"];
                        }
                        else if($response["content"][$a]["tempat"] == "CABANG"){
                            $response["content"][$a]["id_tempat_penerimaan"] = $result["data"][$a]["id_fk_cabang"];

                        }
                        $response["content"][$a]["last_modified"] = $result["data"][$a]["penerimaan_last_modified"];
                        $response["content"][$a]["pem_pk_nomor"] = $result["data"][$a]["pem_pk_nomor"];
                    }
                }
                else{
                    $response["status"] = "ERROR";
                }
                $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
                $response["key"] = array(
                    "tgl",
                    "pem_pk_nomor",
                    "status",
                    "last_modified",
                );
            }
            else if($tipe_penerimaan == "retur"){
                $this->m_penerimaan->set_penerimaan_tempat($type);
                $result = $this->m_penerimaan->content($page,$order_by,$order_direction,$search_key,$data_per_page,$tipe_penerimaan);
                if($result["data"]->num_rows() > 0){
                    $result["data"] = $result["data"]->result_array();
                    for($a = 0; $a<count($result["data"]); $a++){
                        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_penerimaan"];
                        $response["content"][$a]["tgl"] = $result["data"][$a]["penerimaan_tgl"];
                        $response["content"][$a]["status"] = $result["data"][$a]["penerimaan_status"];
                        $response["content"][$a]["id_pembelian"] = $result["data"][$a]["id_fk_pembelian"];
                        $response["content"][$a]["tempat"] = $result["data"][$a]["penerimaan_tempat"];
                        if($response["content"][$a]["tempat"] == "WAREHOUSE"){
                            $response["content"][$a]["id_tempat_penerimaan"] = $result["data"][$a]["id_fk_warehouse"];
                        }
                        else if($response["content"][$a]["tempat"] == "CABANG"){
                            $response["content"][$a]["id_tempat_penerimaan"] = $result["data"][$a]["id_fk_cabang"];

                        }
                        $response["content"][$a]["last_modified"] = $result["data"][$a]["penerimaan_last_modified"];
                        $response["content"][$a]["retur_no"] = $result["data"][$a]["retur_no"];
                    }
                }
                else{
                    $response["status"] = "ERROR";
                }
                $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
                $response["key"] = array(
                    "tgl",
                    "retur_no",
                    "status",
                    "last_modified",
                );
            }
        }
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_penerimaan");
        $result = $this->m_penerimaan->list();
        if($result->num_rows()){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_jenis"];
                $response["content"][$a]["nama"] = $result[$a]["brg_jenis_nama"];
                $response["content"][$a]["status"] = $result[$a]["brg_jenis_status"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_jenis_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No data is recorded in database";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id_reff","Nomor","required");
        $this->form_validation->set_rules("tgl_penerimaan","Tanggal Penerimaan","required");
        if($this->form_validation->run()){
            $penerimaan_tgl = $this->input->post("tgl_penerimaan");
            $penerimaan_status = "AKTIF";
            $id_fk_pembelian = "";
            $id_fk_retur = "";
            if($this->input->post("tipe_penerimaan") == "retur"){
                $id_fk_retur = $this->input->post("id_reff");
            }
            else if($this->input->post("tipe_penerimaan") == "pembelian"){
                $id_fk_pembelian = $this->input->post("id_reff");
            }
            $penerimaan_tempat = $this->input->post("tempat");
            $id_tempat_penerimaan = $this->input->post("id_tempat_penerimaan"); //id_warehouse or id_cabang
            $penerimaan_tipe = $this->input->post("tipe_penerimaan");
            $this->load->model("m_penerimaan");
            if($this->m_penerimaan->set_insert($penerimaan_tgl,$penerimaan_status,$penerimaan_tipe,$id_fk_pembelian,$penerimaan_tempat,$id_tempat_penerimaan,$id_fk_retur)){
                $id_penerimaan = $this->m_penerimaan->insert();
                if($id_penerimaan){
                    $response["msg"] = "Data is recorded to database";
                    $check = $this->input->post("check");
                    if($check != ""){
                        $counter = 0;
                        foreach($check as $a){
                            $this->load->model("m_brg_penerimaan");
                            $brg_penerimaan_qty = $this->input->post("qty_terima".$a);
                            $brg_penerimaan_note = $this->input->post("notes".$a);
                            $id_fk_penerimaan = $id_penerimaan;
                            
                            $id_fk_brg_pembelian = "";
                            $id_fk_brg_retur = "";
                            if($this->input->post("tipe_penerimaan") == "retur"){
                                $id_fk_brg_retur = $this->input->post("id_brg".$a);
                            }
                            else if($this->input->post("tipe_penerimaan") == "pembelian"){
                                $id_fk_brg_pembelian = $this->input->post("id_brg".$a);
                            }

                            $id_fk_satuan = $this->input->post("id_satuan".$a);
                            if($this->m_brg_penerimaan->set_insert($brg_penerimaan_qty,$brg_penerimaan_note,$id_fk_penerimaan,$id_fk_brg_pembelian,$id_fk_satuan,$id_fk_brg_retur)){
                                if($this->m_brg_penerimaan->insert()){
                                    $response["statusitm"][$counter] = "SUCCESS";
                                    $response["msgitm"][$counter] = "Item is recorded to database";
                                }
                                else{
                                    
                                    $response["statusitm"][$counter] = "ERROR";
                                    $response["msgitm"][$counter] = "Insert Item function error";
                                }
                            }
                            else{
                                $response["statusitm"][$counter] = "ERROR";
                                $response["msgitm"][$counter] = "Setter Item function error";
                            }
                        }
                    }
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
        $this->form_validation->set_rules("id","id","required");
        $this->form_validation->set_rules("tgl_penerimaan","tgl_penerimaan","required");
        if($this->form_validation->run()){
            $id_pk_penerimaan = $this->input->post("id");
            $penerimaan_tgl = $this->input->post("tgl_penerimaan");
            $this->load->model("m_penerimaan");
            if($this->m_penerimaan->set_update($id_pk_penerimaan,$penerimaan_tgl)){
                if($this->m_penerimaan->update()){
                    $response["msg"] = "Data is updated to database";
                    $check = $this->input->post("check");
                    if($check != ""){
                        $counter = 0;
                        foreach($check as $a){
                            $this->load->model("m_brg_penerimaan");
                            $id_pk_brg_penerimaan = $this->input->post("id_brg_terima".$a);
                            $brg_penerimaan_qty = $this->input->post("qty_terima".$a);
                            $brg_penerimaan_note = $this->input->post("notes".$a);
                            $id_fk_satuan = $this->input->post("id_satuan".$a);
                            
                            if($this->m_brg_penerimaan->set_update($id_pk_brg_penerimaan,$brg_penerimaan_qty,$brg_penerimaan_note,$id_fk_satuan)){
                                if($this->m_brg_penerimaan->update()){
                                    $response["statusitm"][$counter] = "SUCCESS";
                                    $response["msgitm"][$counter] = "Item is updated to database";
                                }
                                else{
                                    
                                    $response["statusitm"][$counter] = "ERROR";
                                    $response["msgitm"][$counter] = "Update Item function error";
                                }
                            }
                            else{
                                $response["statusitm"][$counter] = "ERROR";
                                $response["msgitm"][$counter] = "Setter Item function error";
                            }
                        }
                    }
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
        $id = $this->input->get("id");
        if($id != "" && is_numeric($id)){
            $this->load->model("m_penerimaan");
            if($this->m_penerimaan->set_delete($id)){
                if($this->m_penerimaan->delete()){
                    $this->load->model("m_brg_penerimaan");
                    $this->m_brg_penerimaan->set_id_fk_penerimaan($id);
                    $this->m_brg_penerimaan->delete_brg_penerimaan();
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
    public function brg_penerimaan(){
        $response["status"] = "SUCCESS";
        $id_penerimaan = $this->input->get("id");
        $this->load->model("m_brg_penerimaan");
        $this->m_brg_penerimaan->set_id_fk_penerimaan($id_penerimaan);
        $result = $this->m_brg_penerimaan->list();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_penerimaan"];
                $response["content"][$a]["qty"] = $result[$a]["brg_penerimaan_qty"];
                $response["content"][$a]["note"] = $result[$a]["brg_penerimaan_note"];
                $response["content"][$a]["id_penerimaan"] = $result[$a]["id_fk_penerimaan"];
                $response["content"][$a]["id_brg_pembelian"] = $result[$a]["id_fk_brg_pembelian"];
                $response["content"][$a]["id_satuan"] = $result[$a]["id_fk_satuan"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_penerimaan_last_modified"];
                $response["content"][$a]["pem_qty"] = $result[$a]["brg_pem_qty"];
                $response["content"][$a]["pem_satuan"] = $result[$a]["brg_pem_satuan"];
                $response["content"][$a]["pem_harga"] = $result[$a]["brg_pem_harga"];
                $response["content"][$a]["pem_note"] = $result[$a]["brg_pem_note"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["satuan"] = $result[$a]["satuan_nama"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "TIDAK ADA BARANG PENERIMAAN";
        }
        echo json_encode($response);
    }
    public function brg_penerimaan_retur(){
        $response["status"] = "SUCCESS";
        $id_penerimaan = $this->input->get("id");
        $this->load->model("m_brg_penerimaan");
        $this->m_brg_penerimaan->set_id_fk_penerimaan($id_penerimaan);
        $result = $this->m_brg_penerimaan->list_retur();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_penerimaan"];
                $response["content"][$a]["qty"] = number_format($result[$a]["brg_penerimaan_qty"],2,",",".");
                $response["content"][$a]["note"] = $result[$a]["brg_penerimaan_note"];
                $response["content"][$a]["id_penerimaan"] = $result[$a]["id_fk_penerimaan"];
                $response["content"][$a]["id_satuan"] = $result[$a]["id_fk_satuan"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["satuan"] = $result[$a]["satuan_nama"];
                $response["content"][$a]["brg_qty_retur"] = number_format($result[$a]["retur_brg_qty"],2,",",".");
                $response["content"][$a]["brg_satuan_retur"] = $result[$a]["retur_brg_satuan"];
                $response["content"][$a]["brg_notes_retur"] = $result[$a]["retur_brg_notes"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "TIDAK ADA BARANG PENERIMAAN";
        }
        echo json_encode($response);
    }
}