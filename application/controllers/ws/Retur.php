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
                $response["content"][$a]["confirm_date"] = $result["data"][$a]["retur_confirm_date"];
                $response["content"][$a]["konfirmasi_user"] = $result["data"][$a]["user_konfirmasi"];
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
            "confirm_date",
            "konfirmasi_user",
        );
        echo json_encode($response);
    }
    public function content_konfirmasi(){
        $response["status"] = "SUCCESS";
        $response["content"] = array();

        $order_by = $this->input->get("orderBy");
        $order_direction = $this->input->get("orderDirection");
        $page = $this->input->get("page");
        $search_key = $this->input->get("searchKey");
        $data_per_page = 20;
        $type = $this->input->get("type"); //CABANG / WAREHOUSE
        
        $this->load->model("m_retur");
        $result = $this->m_retur->content_konfirmasi($page,$order_by,$order_direction,$search_key,$data_per_page);
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
                $response["content"][$a]["confirm_date"] = '-';
                $response["content"][$a]["konfirmasi_user"] = '-';
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
            "confirm_date",
            "konfirmasi_user",
        );
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $id_cabang = $this->input->get("id_cabang");
        
        $this->load->model("m_retur");
        $result = $this->m_retur->list($id_cabang);
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_retur"];
                $response["content"][$a]["id_penjualan"] = $result[$a]["id_fk_penjualan"];
                $response["content"][$a]["no"] = $result[$a]["retur_no"];
                $response["content"][$a]["tgl"] = $result[$a]["retur_tgl"];
                $response["content"][$a]["status"] = $result[$a]["retur_status"];
                $response["content"][$a]["tipe"] = $result[$a]["retur_tipe"];
                $response["content"][$a]["last_modified"] = $result[$a]["retur_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No data is recorded in database";
        }
        echo json_encode($response);
    }
    public function list_pengiriman(){
        #untuk ngelist list retur yang dipake buat pengiriman (dengan kata lain, tipe retur = barang)
        $response["status"] = "SUCCESS";
        $id_cabang = $this->input->get("id_cabang");
        
        $this->load->model("m_retur");
        $result = $this->m_retur->list_retur_pengiriman($id_cabang);
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_retur"];
                $response["content"][$a]["id_penjualan"] = $result[$a]["id_fk_penjualan"];
                $response["content"][$a]["no"] = $result[$a]["retur_no"];
                $response["content"][$a]["tgl"] = $result[$a]["retur_tgl"];
                $response["content"][$a]["status"] = $result[$a]["retur_status"];
                $response["content"][$a]["tipe"] = $result[$a]["retur_tipe"];
                $response["content"][$a]["last_modified"] = $result[$a]["retur_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No data is recorded in database";
        }
        echo json_encode($response);
    }
    public function detail($no_retur){
        $response["status"] = "SUCCESS";
        $this->load->model("m_retur");
        $this->m_retur->set_retur_no($no_retur);
        $result = $this->m_retur->detail_by_no();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_retur"];
                $response["content"][$a]["id_penjualan"] = $result[$a]["id_fk_penjualan"];
                $response["content"][$a]["no_retur"] = $result[$a]["retur_no"];
                $response["content"][$a]["tgl_retur"] = $result[$a]["retur_tgl"];
                $response["content"][$a]["status_retur"] = $result[$a]["retur_status"];
                $response["content"][$a]["tipe_retur"] = $result[$a]["retur_tipe"];
                $response["content"][$a]["last_modified_retur"] = $result[$a]["retur_last_modified"];
                $response["content"][$a]["nomor_penj"] = $result[$a]["penj_nomor"];
                $response["content"][$a]["tgl_penj"] = $result[$a]["penj_tgl"];
                $response["content"][$a]["dateline_tgl_penj"] = $result[$a]["penj_dateline_tgl"];
                $response["content"][$a]["name_cust"] = $result[$a]["cust_name"];
                $response["content"][$a]["suff_cust"] = $result[$a]["cust_suff"];
                $response["content"][$a]["perusahaan_cust"] = $result[$a]["cust_perusahaan"];
                $response["content"][$a]["email_cust"] = $result[$a]["cust_email"];
                $response["content"][$a]["telp_cust"] = $result[$a]["cust_telp"];
                $response["content"][$a]["hp_cust"] = $result[$a]["cust_hp"];
                $response["content"][$a]["alamat_cust"] = $result[$a]["cust_alamat"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Data";
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
                $retur_status = "menunggu konfirmasi";
                $retur_tipe = $this->input->post("tipe_retur");
                
                if($this->input->post("generate_pem_no") != ""){
                    $retur_no = $this->m_retur->get_retur_nomor($this->session->id_cabang,"retur",$retur_tgl);
                }
                else{
                    $retur_no = $this->input->post("no_retur");
                }
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
                                    
                                    $retur_brg_notes = $this->input->post("brg_retur_notes".$a);

                                    $brg_retur_qty = $this->input->post("brg_retur_jumlah".$a);
                                    $brg_retur_qty = explode(" ",$brg_retur_qty);
                                    if(count($brg_retur_qty) == 2){
                                        $retur_brg_qty = $brg_retur_qty[0];
                                        $retur_brg_satuan = $brg_retur_qty[1];
                                    }
                                    else{
                                        $retur_brg_qty = $brg_retur_qty[0];
                                        $retur_brg_satuan = "Pcs";
                                    }
                                    $retur_brg_status = "aktif";
                                    
                                    if($this->m_retur_brg->set_insert($id_fk_retur,$id_fk_brg_cabang,$retur_brg_qty,$retur_brg_satuan,$retur_brg_status,$retur_brg_notes)){
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
                                    
                                    $brg = $this->input->post("brg_qty".$a);
                                    $brg = explode(" ",$brg);
                                    if(count($brg) > 1){
                                        $retur_kembali_qty = $brg[0];
                                        $retur_kembali_satuan = $brg[1];
                                    }
                                    else{
                                        $retur_kembali_qty = $brg[0];
                                        $retur_kembali_satuan = "Pcs";
                                    }

                                    $retur_kembali_harga = $this->input->post("brg_price".$a);
                                    $retur_kembali_note = $this->input->post("brg_notes".$a);
                                    $retur_kembali_status = "aktif";
                                    $id_fk_retur = $id_retur;
                                    if($this->m_retur_kembali->set_insert($retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$retur_kembali_status,$id_fk_retur,$id_fk_brg_cabang)){
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

                            $retur_brg_notes = $this->input->post("brg_retur_notes_edit".$a);

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
                            if(count($brg) > 1){
                                $retur_brg_qty = $brg[0]; 
                                $retur_brg_satuan = $brg[1];
                            }
                            else{
                                $retur_brg_qty = $brg[0]; 
                                $retur_brg_satuan = "Pcs";
                            }
                            
                            if($this->m_retur_brg->set_update($id_pk_retur_brg,$id_fk_brg,$retur_brg_qty,$retur_brg_satuan,$retur_brg_notes)){
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

                            $brg = $this->input->post("brg_qty_edit".$a);
                            $brg = explode(" ",$brg);
                            if(count($brg) > 1){
                                $retur_kembali_qty = $brg[0];
                                $retur_kembali_satuan = $brg[1];
                            }
                            else{
                                $retur_kembali_qty = $brg[0];
                                $retur_kembali_satuan = "Pcs";
                            }

                            $retur_kembali_harga = $this->input->post("brg_price_edit".$a);
                            $retur_kembali_note = $this->input->post("brg_notes_edit".$a);
                            
                            $id_fk_brg = $this->input->post("brg_edit".$a);
                            $this->m_barang->set_brg_nama($id_fk_brg);
                            $result = $this->m_barang->detail_by_name();
                            if($result->num_rows() > 0){
                                $result = $result->result_array();
                                $id_fk_brg = $result[0]["id_pk_brg"];
                            }
                            
                            if($this->m_retur_kembali->set_update($id_pk_retur_kembali,$retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$id_fk_brg)){
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
                                
                                $retur_brg_notes = $this->input->post("brg_retur_notes".$a);

                                $brg_retur_qty = $this->input->post("brg_retur_jumlah".$a);
                                $brg_retur_qty = explode(" ",$brg_retur_qty);
                                if(count($brg_retur_qty) > 1){
                                    $retur_brg_qty = $brg_retur_qty[0];
                                    $retur_brg_satuan = $brg_retur_qty[1];
                                }
                                else{
                                    $retur_brg_qty = $brg_retur_qty[0];
                                    $retur_brg_satuan = "Pcs";
                                }

                                $retur_brg_status = "aktif";
                                
                                if($this->m_retur_brg->set_insert($id_fk_retur,$id_fk_brg_cabang,$retur_brg_qty,$retur_brg_satuan,$retur_brg_status,$retur_brg_notes)){
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

                                $brg = $this->input->post("brg_qty".$a);
                                $brg = explode(" ",$brg);
                                if(count($brg) > 1){
                                    $retur_kembali_qty = $brg[0];
                                    $retur_kembali_satuan = $brg[1];
                                }
                                else{
                                    $retur_kembali_qty = $brg[0];
                                    $retur_kembali_satuan = "Pcs";
                                }

                                $retur_kembali_harga = $this->input->post("brg_price".$a);
                                $retur_kembali_note = $this->input->post("brg_notes".$a);
                                $retur_kembali_status = "aktif";
                                $id_fk_retur = $id_pk_retur;
                                if($this->m_retur_kembali->set_insert($retur_kembali_qty,$retur_kembali_satuan,$retur_kembali_harga,$retur_kembali_note,$retur_kembali_status,$id_fk_retur,$id_fk_brg_cabang)){
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
                $response["content"][$a]["qty"] = number_format($result[$a]["retur_brg_qty"],2,",",".");
                $response["content"][$a]["notes"] = $result[$a]["retur_brg_notes"];
                $response["content"][$a]["satuan"] = $result[$a]["retur_brg_satuan"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["status"] = $result[$a]["retur_brg_status"];
                $response["content"][$a]["terkirim"] = $result[$a]["brg_terkirim"];
                $response["content"][$a]["satuan_terkirim"] = $result[$a]["satuan_kirim"];
                $response["content"][$a]["beli"] = $result[$a]["brg_beli"];
                $response["content"][$a]["satuan_beli"] = $result[$a]["satuan_beli"];
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
                $response["content"][$a]["qty"] = number_format($result[$a]["retur_kembali_qty"],2,",",".");
                $response["content"][$a]["satuan"] = $result[$a]["retur_kembali_satuan"];
                $response["content"][$a]["harga"] = number_format($result[$a]["retur_kembali_harga"],0,",",".");
                $response["content"][$a]["note"] = $result[$a]["retur_kembali_note"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["harga_brg"] = number_format($result[$a]["brg_harga"],0,",",".");
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
    public function konfirmasi(){
        $response["status"] = "SUCCESS";
        $id_retur = $this->input->get("id");
        $status = "aktif";
        $this->load->model("m_retur");
        $this->m_retur->set_id_pk_retur($id_retur);
        $this->m_retur->set_retur_status($status);
        if($this->m_retur->update_status()){
            $this->m_retur->konfirmasi();
            $response["msg"] = "Data retur dikonfirmasi";
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Error updating status";

        }            
        echo json_encode($response);
    }
    public function selesai(){
        $response["status"] = "SUCCESS";
        $id = $this->input->get("id");
        if($id != "" && is_numeric($id)){
            $this->load->model("m_retur");
            $this->m_retur->set_id_pk_retur($id);
            $this->m_retur->set_retur_status("selesai");
            $this->m_retur->update_status();
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid ID Supplier";
        }
        echo json_encode($response);
    }
}