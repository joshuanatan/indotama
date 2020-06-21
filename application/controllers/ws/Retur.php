<?php
defined("BASEPATH") or exit("No Direct Script");
class Retur extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_retur");
        $columns = $this->m_retur->columns();
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
        
        $this->load->model("m_retur");
        $result = $this->m_retur->content($page,$order_by,$order_direction,$search_key,$data_per_page);
        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_retur"];
                $response["content"][$a]["no"] = $result["data"][$a]["retur_no"];
                $response["content"][$a]["tgl"] = $result["data"][$a]["retur_tgl"];
                $response["content"][$a]["status"] = $result["data"][$a]["retur_status"];
                $response["content"][$a]["tipe"] = $result["data"][$a]["retur_tipe"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["retur_create_date"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["retur_last_modified"];
                $response["content"][$a]["nomor_penj"] = $result["data"][$a]["penj_nomor"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "no",
            "tgl",
            "tipe",
            "status",
            "last_modified",
        );
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_retur");
        $result = $this->m_retur->list();
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
        $this->form_validation->set_rules("no_penjualan","no_penjualan","required");
        $this->form_validation->set_rules("tgl_retur","tgl_retur","required");
        $this->form_validation->set_rules("tipe_retur","tipe_retur","required");
        if($this->form_validation->run()){
            $this->load->model("m_retur");
            $id_fk_penjualan = $this->input->post("no_penjualan");
            $this->load->model("m_penjualan");
            $this->m_penjualan->set_penj_nomor($id_fk_penjualan);
            $result = $this->m_penjualan->detail_by_penj_nomor($id_fk_penjualan);
            if($result->num_rows() > 0){
                $result = $result->result_array();
                $id_fk_penjualan = $result[0]["id_pk_penjualan"];

                $retur_no = $this->input->post("no_retur");
                $retur_tgl = $this->input->post("tgl_retur");
                $retur_status = "aktif";
                $retur_tipe = $this->input->post("tipe_retur");
                if($this->m_retur->set_insert($id_fk_penjualan,$retur_no,$retur_tgl,$retur_status,$retur_tipe)){
                    $id_retur = $this->m_retur->insert();
                    if($id_retur){
                        $response["msg"] = "Data is recorded to database";
                        $check = $this->input->post("brg_retur_check");
                        if($check != ""){
                            $counter = 0;
                            foreach($check as $a){
                                $this->load->model("m_retur_brg");
                                $id_fk_retur = $id_retur;
                                $id_fk_brg_cabang = $this->input->post("brg_retur".$a);
                                $this->load->model("m_barang");
                                $this->m_barang->set_brg_nama($id_fk_brg_cabang);
                                $result = $this->m_barang->detail_by_name();
                                if($result->num_rows() > 0){
                                    $result = $result->result_array();
                                    $id_fk_brg_cabang = $result[0]["id_pk_brg"];
                                    
                                    $brg_retur_qty = $this->input->post("brg_retur_jumlah".$a);
                                    $brg_retur_qty = explode(" ",$brg_retur_qty);
                                    $retur_brg_qty = $brg_retur_qty[0];
                                    $retur_brg_satuan = $brg_retur_qty[1];

                                    $retur_brg_status = "aktif";
                                    
                                    if($this->m_retur_brg->set_insert($id_fk_retur,$id_fk_brg_cabang,$retur_brg_qty,$retur_brg_satuan,$retur_brg_status)){
                                        if($this->m_retur_brg->insert()){
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

                        $check = $this->input->post("brg_kembali_check");
                        if($check != ""){
                            foreach($check as $a){
                                $this->load->model("m_retur_kembali");
                                $id_fk_brg_cabang = $this->input->post("brg".$a);
                                $this->load->model("m_barang");
                                $this->m_barang->set_brg_nama($id_fk_brg_cabang);
                                $result = $this->m_barang->detail_by_name();
                                if($result->num_rows() > 0){
                                    $result = $result->result_array();
                                    $id_fk_brg_cabang = $result[0]["id_pk_brg"];

                                    $brg = $this->input->post("brg_qty_real".$a);
                                    $brg = explode(" ",$brg);
                                    $retur_kembali_qty_real = $brg[0];
                                    $retur_kembali_satuan_real = $brg[1];
                                    
                                    $brg = $this->input->post("brg_qty".$a);
                                    $brg = explode(" ",$brg);
                                    $retur_kembali_qty = $brg[0];
                                    $retur_kembali_satuan = $brg[1];

                                    $retur_kembali_harga = $this->input->post("brg_price".$a);
                                    $retur_kembali_note = $this->input->post("brg_notes".$a);
                                    $retur_kembali_status = "aktif";
                                    $id_fk_retur = $id_retur;
                                    if($this->m_retur_kembali->set_insert($retur_kembali_qty_real,$retur_kembali_satuan_real,$retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$retur_kembali_status,$id_fk_retur,$id_fk_brg_cabang)){
                                        if($this->m_retur_kembali->insert()){
    
                                        }
                                    }
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
        $this->form_validation->set_rules("no_retur","no_retur","required");
        $this->form_validation->set_rules("tgl_retur","tgl_retur","required");
        $this->form_validation->set_rules("tipe_retur","tipe_retur","required");
        if($this->form_validation->run()){
            $id_pk_retur = $this->input->post("id");
            $retur_no = $this->input->post("no_retur");
            $retur_tgl = $this->input->post("tgl_retur");
            $retur_tipe = $this->input->post("tipe_retur");
            $this->load->model("m_retur");
            if($this->m_retur->set_update($id_pk_retur,$retur_no,$retur_tgl,$retur_tipe)){
                if($this->m_retur->update()){
                    $response["msg"] = "Data is updated to database";

                    $check = $this->input->post("brg_retur_check_edit");
                    $counter = 0;
                    if($check != ""){
                        foreach($check as $a){
                            $this->load->model("m_retur_brg");
                            $id_pk_retur_brg = $this->input->post("id_brg_retur_edit".$a);

                            $id_fk_brg = $this->input->post("brg_retur_edit".$a);
                            $this->load->model("m_barang");
                            $this->m_barang->set_brg_nama($id_fk_brg);
                            $result = $this->m_barang->detail_by_name();
                            if($result->num_rows() > 0){
                                $result = $result->result_array();
                                $id_fk_brg = $result[0]["id_pk_brg"];
                            }

                            $brg = $this->input->post("brg_retur_jumlah_edit".$a);
                            $brg = explode(" ",$brg);
                            $retur_brg_qty = $brg[0]; 
                            $retur_brg_satuan = $brg[1];
                            
                            if($this->m_retur_brg->set_update($id_pk_retur_brg,$id_fk_brg,$retur_brg_qty,$retur_brg_satuan)){
                                if($this->m_retur_brg->update()){
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
                            $counter++;
                        }
                    }

                    $check = $this->input->post("brg_kembali_check_edit");
                    $counter = 0;
                    if($check != ""){
                        foreach($check as $a){
                            $this->load->model("m_retur_kembali");
                            $id_pk_retur_kembali = $this->input->post("id_brg_kembali_edit".$a);

                            $brg = $this->input->post("brg_qty_real_edit".$a);
                            $brg = explode(" ",$brg);
                            $retur_kembali_qty_real = $brg[0];
                            $retur_kembali_satuan_real = $brg[1];

                            $brg = $this->input->post("brg_qty_edit".$a);
                            $brg = explode(" ",$brg);
                            $retur_kembali_qty = $brg[0];
                            $retur_kembali_satuan = $brg[1];

                            $retur_kembali_harga = $this->input->post("brg_price_edit".$a);
                            $retur_kembali_note = $this->input->post("brg_notes_edit".$a);
                            
                            $id_fk_brg = $this->input->post("brg_edit".$a);
                            $this->m_barang->set_brg_nama($id_fk_brg);
                            $result = $this->m_barang->detail_by_name();
                            if($result->num_rows() > 0){
                                $result = $result->result_array();
                                $id_fk_brg = $result[0]["id_pk_brg"];
                            }
                            
                            if($this->m_retur_kembali->set_update($id_pk_retur_kembali,$retur_kembali_qty_real,$retur_kembali_satuan_real,$retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$id_fk_brg)){
                                if($this->m_retur_kembali->update()){
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
                            $counter++;
                        }
                    }

                    $check = $this->input->post("brg_retur_check");
                    $counter = 0;
                    if($check != ""){
                        $counter = 0;
                        foreach($check as $a){
                            $this->load->model("m_retur_brg");
                            $id_fk_retur = $id_pk_retur;
                            $id_fk_brg_cabang = $this->input->post("brg_retur".$a);
                            $this->load->model("m_barang");
                            $this->m_barang->set_brg_nama($id_fk_brg_cabang);
                            $result = $this->m_barang->detail_by_name();
                            if($result->num_rows() > 0){
                                $result = $result->result_array();
                                $id_fk_brg_cabang = $result[0]["id_pk_brg"];
                                
                                $brg_retur_qty = $this->input->post("brg_retur_jumlah".$a);
                                $brg_retur_qty = explode(" ",$brg_retur_qty);
                                $retur_brg_qty = $brg_retur_qty[0];
                                $retur_brg_satuan = $brg_retur_qty[1];

                                $retur_brg_status = "aktif";
                                
                                if($this->m_retur_brg->set_insert($id_fk_retur,$id_fk_brg_cabang,$retur_brg_qty,$retur_brg_satuan,$retur_brg_status)){
                                    if($this->m_retur_brg->insert()){
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
                            $counter++;
                        }
                    }

                    $check = $this->input->post("brg_kembali_check");
                    $counter = 0;
                    if($check != ""){
                        foreach($check as $a){
                            $this->load->model("m_retur_kembali");
                            $id_fk_brg_cabang = $this->input->post("brg".$a);
                            $this->load->model("m_barang");
                            $this->m_barang->set_brg_nama($id_fk_brg_cabang);
                            $result = $this->m_barang->detail_by_name();
                            if($result->num_rows() > 0){
                                $result = $result->result_array();
                                $id_fk_brg_cabang = $result[0]["id_pk_brg"];

                                $brg = $this->input->post("brg_qty_real".$a);
                                $brg = explode(" ",$brg);
                                $retur_kembali_qty_real = $brg[0];
                                $retur_kembali_satuan_real = $brg[1];
                                
                                $brg = $this->input->post("brg_qty".$a);
                                $brg = explode(" ",$brg);
                                $retur_kembali_qty = $brg[0];
                                $retur_kembali_satuan = $brg[1];

                                $retur_kembali_harga = $this->input->post("brg_price".$a);
                                $retur_kembali_note = $this->input->post("brg_notes".$a);
                                $retur_kembali_status = "aktif";
                                $id_fk_retur = $id_pk_retur;
                                if($this->m_retur_kembali->set_insert($retur_kembali_qty_real,$retur_kembali_satuan_real,$retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$retur_kembali_status,$id_fk_retur,$id_fk_brg_cabang)){
                                    if($this->m_retur_kembali->insert()){

                                    }
                                }
                            }
                            $counter++;
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
            $this->load->model("m_retur");
            if($this->m_retur->set_delete($id)){
                if($this->m_retur->delete()){
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
    public function brg_retur(){
        $response["status"] = "SUCCESS";
        $id_retur = $this->input->get("id_retur");

        $this->load->model("m_retur_brg");
        $this->m_retur_brg->set_id_fk_retur($id_retur);
        $result = $this->m_retur_brg->list();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_retur_brg"];
                $response["content"][$a]["qty"] = $result[$a]["retur_brg_qty"];
                $response["content"][$a]["satuan"] = $result[$a]["retur_brg_satuan"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["status"] = $result[$a]["retur_brg_status"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "TIDAK ADA BARANG RETUR";
        }
        echo json_encode($response);
    }
    public function brg_kembali(){
        $response["status"] = "SUCCESS";
        $id_retur = $this->input->get("id_retur");

        $this->load->model("m_retur_kembali");
        $this->m_retur_kembali->set_id_fk_retur($id_retur);
        $result = $this->m_retur_kembali->list();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_retur_kembali"];
                $response["content"][$a]["qty_real"] = $result[$a]["retur_kembali_qty_real"];
                $response["content"][$a]["satuan_real"] = $result[$a]["retur_kembali_satuan_real"];
                $response["content"][$a]["qty"] = $result[$a]["retur_kembali_qty"];
                $response["content"][$a]["satuan"] = $result[$a]["retur_kembali_satuan"];
                $response["content"][$a]["harga"] = $result[$a]["retur_kembali_harga"];
                $response["content"][$a]["note"] = $result[$a]["retur_kembali_note"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["harga_brg"] = $result[$a]["brg_harga"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "TIDAK ADA BARANG KEMBALI";
        }
        echo json_encode($response);
    }
    public function delete_brg_retur(){
        $response["status"] = "SUCCESS";
        $id = $this->input->get("id");
        $this->load->model("m_retur_brg");
        $this->m_retur_brg->set_id_pk_retur_brg($id);
        if(!$this->m_retur_brg->delete()){
            $response["status"] = "ERROR";
            $response["msg"] = "delete function error";
        }
        echo json_encode($response);
    }
    public function delete_brg_kembali(){
        $response["status"] = "SUCCESS";
        $id = $this->input->get("id");
        $this->load->model("m_retur_kembali");
        $this->m_retur_kembali->set_id_pk_retur_kembali($id);
        if(!$this->m_retur_kembali->delete()){
            $response["status"] = "ERROR";
            $response["msg"] = "delete function error";
        }
        echo json_encode($response);
    }

}