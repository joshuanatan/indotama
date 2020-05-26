<?php
defined("BASEPATH") or exit("No Direct Script");
class Pemenuhan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_brg_pemenuhan");
        $columns = $this->m_brg_pemenuhan->columns();
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
        
        $this->load->model("m_brg_pemenuhan");
        $flag = true;
        if($type == "WAREHOUSE" && $this->session->id_warehouse){
            $this->m_brg_pemenuhan->set_id_fk_warehouse($this->session->id_warehouse);
        }
        else if($type == "CABANG" && $this->session->id_cabang){
            $this->m_brg_pemenuhan->set_id_fk_cabang($this->session->id_cabang);
        }
        else{
            $flag = false;
            $response["status"] = "ERROR";
            $response["msg"] = "Type not registered";
        }

        if($flag){
            $this->m_brg_pemenuhan->set_brg_pemenuhan_tipe($type);
            $result = $this->m_brg_pemenuhan->content($page,$order_by,$order_direction,$search_key,$data_per_page);
            if($result["data"]->num_rows() > 0){
                $result["data"] = $result["data"]->result_array();
                for($a = 0; $a<count($result["data"]); $a++){
                    $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg_permintaan"];
                    $response["content"][$a]["stok_permintaan"] = $result["data"][$a]["brg_permintaan_qty"];
                    $response["content"][$a]["notes"] = $result["data"][$a]["brg_permintaan_notes"];
                    $response["content"][$a]["deadline"] = $result["data"][$a]["brg_permintaan_deadline"];
                    $response["content"][$a]["status_permintaan"] = $result["data"][$a]["brg_permintaan_status"];
                    $response["content"][$a]["id_fk_brg"] = $result["data"][$a]["id_fk_brg"];
                    $response["content"][$a]["nama_barang"] = $result["data"][$a]["brg_nama"];
                    if($result["data"][$a]["qty_pemenuhan"]==null){
                        $response["content"][$a]["stok_terpenuhi"] = 0;
                    }else{
                        $response["content"][$a]["stok_terpenuhi"] = $result["data"][$a]["qty_pemenuhan"];
                    }
                    $response["content"][$a]["cabang_peminta"] = $result["data"][$a]["cabang_daerah"];
                    $response["content"][$a]["id_fk_cabang"] = $result["data"][$a]["id_fk_cabang"];
                    $response["content"][$a]["tgl_permintaan"] = $result["data"][$a]["brg_permintaan_create_date"];
                    $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_permintaan_last_modified"];
                    $response["content"][$a]["gambar_barang"] = "<img width='100px' src='" .$result["data"][$a]["brg_image"] . "'>";
                }
            }
            else{
                $response["status"] = "ERROR";
            }
            $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
            $response["key"] = array(
                "tgl_permintaan",
                "cabang_peminta",
                "gambar_barang",
                "nama_barang",
                "stok_terpenuhi",
                "stok_permintaan",
                "status_permintaan"
            );
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id_pembelian","Nomor","required");
        $this->form_validation->set_rules("tgl_penerimaan","Tanggal Penerimaan","required");
        if($this->form_validation->run()){
            $penerimaan_tgl = $this->input->post("tgl_penerimaan");
            $penerimaan_status = "AKTIF";
            $id_fk_pembelian = $this->input->post("id_pembelian");
            $penerimaan_tempat = $this->input->post("tempat");
            $id_tempat_penerimaan = $this->input->post("id_tempat_penerimaan"); //id_warehouse or id_cabang
            $this->load->model("m_brg_pemenuhan");
            if($this->m_brg_pemenuhan->set_insert($penerimaan_tgl,$penerimaan_status,$id_fk_pembelian,$penerimaan_tempat,$id_tempat_penerimaan)){
                $id_penerimaan = $this->m_brg_pemenuhan->insert();
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
                            $id_fk_brg_pembelian = $this->input->post("id_brg".$a);
                            $id_fk_satuan = $this->input->post("id_satuan".$a);
                            if($this->m_brg_penerimaan->set_insert($brg_penerimaan_qty,$brg_penerimaan_note,$id_fk_penerimaan,$id_fk_brg_pembelian,$id_fk_satuan)){
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
            $this->load->model("m_brg_pemenuhan");
            if($this->m_brg_pemenuhan->set_update($id_pk_penerimaan,$penerimaan_tgl)){
                if($this->m_brg_pemenuhan->update()){
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
            $this->load->model("m_brg_pemenuhan");
            if($this->m_brg_pemenuhan->set_delete($id)){
                if($this->m_brg_pemenuhan->delete()){
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