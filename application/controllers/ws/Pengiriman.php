<?php
defined("BASEPATH") or exit("No Direct Script");
class pengiriman extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_pengiriman");
        $tipe = $this->input->get("tipe_pengiriman");
        if($tipe){
            $columns = $this->m_pengiriman->columns($tipe);
        }
        else{
            $columns = $this->m_pengiriman->columns();
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
        $type = strtoupper($this->input->get("type")); //CABANG / WAREHOUSE
        $tipe_pengiriman = $this->input->get("tipe_pengiriman");
        
        $this->load->model("m_pengiriman");
        $flag = true;
        if($type == "WAREHOUSE" && $this->session->id_warehouse){
            $this->m_pengiriman->set_id_fk_warehouse($this->session->id_warehouse);
        }
        else if($type == "CABANG" && $this->session->id_cabang){
            $this->m_pengiriman->set_id_fk_cabang($this->session->id_cabang);
        }
        else{
            $flag = false;
            $response["status"] = "ERROR";
            $response["msg"] = "Type not registered";
        }

        if($flag){
            if($tipe_pengiriman == ""){
                $tipe_pengiriman = "penjualan";
                $this->m_pengiriman->set_pengiriman_tempat($type);
                $result = $this->m_pengiriman->content($page,$order_by,$order_direction,$search_key,$data_per_page);
                if($result["data"]->num_rows() > 0){
                    $result["data"] = $result["data"]->result_array();
                    for($a = 0; $a<count($result["data"]); $a++){
                        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_pengiriman"];
                        $response["content"][$a]["no_pengiriman"] = $result["data"][$a]["pengiriman_no"];
                        $response["content"][$a]["tgl"] = $result["data"][$a]["pengiriman_tgl"];
                        $response["content"][$a]["status"] = $result["data"][$a]["pengiriman_status"];
                        $response["content"][$a]["id_penjualan"] = $result["data"][$a]["id_fk_penjualan"];
                        $response["content"][$a]["tempat"] = $result["data"][$a]["pengiriman_tempat"];
                        $response["content"][$a]["last_modified"] = $result["data"][$a]["pengiriman_last_modified"];
                        $response["content"][$a]["nomor_penj"] = $result["data"][$a]["penj_nomor"];
                        $response["content"][$a]["perusahaan_cust"] = strtoupper($result["data"][$a]["cust_perusahaan"]);
                        $response["content"][$a]["name_cust"] = strtoupper($result["data"][$a]["cust_name"]);
                        $response["content"][$a]["suff_cust"] = strtoupper($result["data"][$a]["cust_suff"]);
                        $response["content"][$a]["hp_cust"] = $result["data"][$a]["cust_hp"];
                        $response["content"][$a]["email_cust"] = $result["data"][$a]["cust_email"];
                        $response["content"][$a]["nomor"] = $result["data"][$a]["penj_nomor"];
                        if(strtoupper($response["content"][$a]["tempat"]) == "WAREHOUSE"){
                            $response["content"][$a]["id_tempat_pengiriman"] = $result["data"][$a]["id_fk_warehouse"];
                        }
                        else if(strtoupper($response["content"][$a]["tempat"]) == "CABANG"){
                            $response["content"][$a]["id_tempat_pengiriman"] = $result["data"][$a]["id_fk_cabang"];

                        }
                    }
                }
                else{
                    $response["status"] = "ERROR";
                }
                $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
                $response["key"] = array(
                    "no_pengiriman",
                    "tgl",
                    "nomor_penj",
                    "status",
                    "last_modified",
                );
            }
            else if($tipe_pengiriman == "retur"){
                $this->m_pengiriman->set_pengiriman_tempat($type);
                $result = $this->m_pengiriman->content($page,$order_by,$order_direction,$search_key,$data_per_page,$tipe_pengiriman);
                if($result["data"]->num_rows() > 0){
                    $result["data"] = $result["data"]->result_array();
                    for($a = 0; $a<count($result["data"]); $a++){
                        $response["content"][$a]["id"] = $result["data"][$a]["id_pk_pengiriman"];
                        $response["content"][$a]["no_pengiriman"] = $result["data"][$a]["pengiriman_no"];
                        $response["content"][$a]["tgl"] = $result["data"][$a]["pengiriman_tgl"];
                        $response["content"][$a]["status"] = $result["data"][$a]["pengiriman_status"];
                        $response["content"][$a]["tempat"] = $result["data"][$a]["pengiriman_tempat"];
                        if($response["content"][$a]["tempat"] == "WAREHOUSE"){
                            $response["content"][$a]["id_tempat_pengiriman"] = $result["data"][$a]["id_fk_warehouse"];
                        }
                        else if($response["content"][$a]["tempat"] == "CABANG"){
                            $response["content"][$a]["id_tempat_pengiriman"] = $result["data"][$a]["id_fk_cabang"];

                        }
                        $response["content"][$a]["last_modified"] = $result["data"][$a]["pengiriman_last_modified"];
                        $response["content"][$a]["retur_no"] = $result["data"][$a]["retur_no"];
                    }
                }
                else{
                    $response["status"] = "ERROR";
                }
                $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
                $response["key"] = array(
                    "no_pengiriman",
                    "tgl",
                    "retur_no",
                    "status",
                    "last_modified",
                );
            }
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id_reff","Nomor","required");
        $this->form_validation->set_rules("tgl_pengiriman","Tanggal Penerimaan","required");
        if($this->form_validation->run()){
            $pengiriman_tgl = $this->input->post("tgl_pengiriman");
            $pengiriman_status = "AKTIF";
            $id_fk_penjualan = "";
            $id_fk_retur = "";
            if($this->input->post("tipe_pengiriman") == "retur"){
                $id_fk_retur = $this->input->post("id_reff");
            }
            else if($this->input->post("tipe_pengiriman") == "penjualan"){
                $id_fk_penjualan = $this->input->post("id_reff");
            }
            $pengiriman_tempat = $this->input->post("type");
            $pengiriman_tipe = $this->input->post("tipe_pengiriman");
            $id_tempat_pengiriman = $this->input->post("id_tempat_pengiriman"); //id_warehouse or id_cabang

            $this->load->model("m_pengiriman");
            $id_fk_cabang = $this->session->id_cabang;
            $pengiriman_no = $this->m_pengiriman->get_pengiriman_nomor($id_fk_cabang,"pengiriman",$pengiriman_tgl);

            if($this->m_pengiriman->set_insert($pengiriman_no,$pengiriman_tgl,$pengiriman_status,$pengiriman_tipe,$id_fk_penjualan,$pengiriman_tempat,$id_tempat_pengiriman,$id_fk_retur)){
                $id_pengiriman = $this->m_pengiriman->insert();
                if($id_pengiriman){
                    $response["msg"] = "Data is recorded to database";

                    $check = $this->input->post("check");
                    if($check != ""){
                        $counter = -1;
                        foreach($check as $a){
                            $counter++;
                            $this->form_validation->reset_validation();
                            $this->form_validation->set_rules("id_brg".$a,"id_brg","required");
                            $this->form_validation->set_rules("qty_kirim".$a,"qty_kirim","required");
                            $this->form_validation->set_rules("id_satuan".$a,"id_satuan","required");
                            if($this->form_validation->run()){
                                $brg_pengiriman_qty = $this->input->post("qty_kirim".$a);
                                $brg_pengiriman_note = $this->input->post("notes".$a);
                                if(!$brg_pengiriman_note){
                                    $brg_pengiriman_note = "-";
                                }
                                $id_fk_pengiriman = $id_pengiriman;
                                
                                $id_fk_brg_penjualan = "";
                                $id_fk_brg_retur = "";
                                if($this->input->post("tipe_pengiriman") == "retur"){
                                    $id_fk_brg_retur = $this->input->post("id_brg".$a);
                                    if(!$this->check_stok("retur",$id_fk_brg_retur,$brg_pengiriman_qty)){
                                        $response["statusitm"][$counter] = "ERROR";
                                        $response["msgitm"][$counter] = "Stok tidak mencukupi";
                                        continue;
                                    }
                                }
                                else if($this->input->post("tipe_pengiriman") == "penjualan"){
                                    $id_fk_brg_penjualan = $this->input->post("id_brg".$a);
                                    if(!$this->check_stok("penjualan",$id_fk_brg_penjualan,$brg_pengiriman_qty)){
                                        $response["statusitm"][$counter] = "ERROR";
                                        $response["msgitm"][$counter] = "Stok tidak mencukupi";
                                        continue;
                                    }
                                }
                                $tipe_pengiriman = $this->input->post("tipe_pengiriman");
                                $id_fk_satuan = $this->input->post("id_satuan".$a);
                                $this->load->model("m_brg_pengiriman");
                                if($this->m_brg_pengiriman->set_insert($brg_pengiriman_qty,$brg_pengiriman_note,$id_fk_pengiriman,$id_fk_brg_penjualan,$id_fk_satuan,$id_fk_brg_retur)){
                                    if($this->m_brg_pengiriman->insert()){
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
        $this->form_validation->set_rules("tgl_pengiriman","tgl_pengiriman","required");
        if($this->form_validation->run()){
            $id_pk_pengiriman = $this->input->post("id");
            $pengiriman_tgl = $this->input->post("tgl_pengiriman");
            $this->load->model("m_pengiriman");
            if($this->m_pengiriman->set_update($id_pk_pengiriman,$pengiriman_tgl)){
                if($this->m_pengiriman->update()){
                    $response["msg"] = "Data is updated to database";
                    $check = $this->input->post("check");
                    if($check != ""){
                        $counter = 0;
                        foreach($check as $a){
                            $this->load->model("m_brg_pengiriman");
                            $id_pk_brg_pengiriman = $this->input->post("id_brg_kirim".$a);
                            $brg_pengiriman_qty = $this->input->post("qty_kirim".$a);
                            $brg_pengiriman_note = $this->input->post("notes".$a);
                            $id_fk_satuan = $this->input->post("id_satuan".$a);
                            

                            if($this->m_brg_pengiriman->set_update($id_pk_brg_pengiriman,$brg_pengiriman_qty,$brg_pengiriman_note,$id_fk_satuan)){
                                if($this->m_brg_pengiriman->update()){
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
            $this->load->model("m_pengiriman");
            if($this->m_pengiriman->set_delete($id)){
                if($this->m_pengiriman->delete()){
                    $this->load->model("m_brg_pengiriman");
                    $this->m_brg_pengiriman->set_id_fk_pengiriman($id);
                    $this->m_brg_pengiriman->delete_brg_pengiriman();
                    $response["msg"] = "Data is deleted from database";

                    $id_fk_brg_pemenuhan = $this->input->post("id_brg_pemenuhan");
                    $this->load->model("m_brg_pemenuhan");
                    $this->m_brg_pemenuhan->set_id_pk_brg_pemenuhan($id_fk_brg_pemenuhan);
                    $this->m_brg_pemenuhan->set_brg_pemenuhan_status("Aktif");
                    $this->m_brg_pemenuhan->update_status();
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
            $response["msg"] = "Invalid ID Pengiriman";
        }
        echo json_encode($response);
    }
    public function brg_pengiriman(){
        $response["status"] = "SUCCESS";
        $id_pengiriman = $this->input->get("id");
        $this->load->model("m_brg_pengiriman");
        $this->m_brg_pengiriman->set_id_fk_pengiriman($id_pengiriman);
        $result = $this->m_brg_pengiriman->list_data();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_pengiriman"];
                $response["content"][$a]["qty"] = number_format($result[$a]["brg_pengiriman_qty"],2,",",".");
                $response["content"][$a]["note"] = $result[$a]["brg_pengiriman_note"];
                $response["content"][$a]["id_pengiriman"] = $result[$a]["id_fk_pengiriman"];
                $response["content"][$a]["id_brg_penjualan"] = $result[$a]["id_fk_brg_penjualan"];
                $response["content"][$a]["id_satuan"] = $result[$a]["id_fk_satuan"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_pengiriman_last_modified"];
                $response["content"][$a]["qty_brg_penjualan"] = number_format($result[$a]["brg_penjualan_qty"],2,",",".");
                $response["content"][$a]["satuan_brg_penjualan"] = $result[$a]["brg_penjualan_satuan"];
                $response["content"][$a]["harga_brg_penjualan"] = number_format($result[$a]["brg_penjualan_harga"],0,",",".");
                $response["content"][$a]["note_brg_penjualan"] = $result[$a]["brg_penjualan_note"];
                $response["content"][$a]["status_brg_penjualan"] = $result[$a]["brg_penjualan_status"];
                $response["content"][$a]["satuan"] = $result[$a]["satuan_nama"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "TIDAK ADA BARANG PENGIRIMAN";
        }
        echo json_encode($response);
    }
    public function brg_pengiriman_retur(){
        $response["status"] = "SUCCESS";
        $id_pengiriman = $this->input->get("id");
        $this->load->model("m_brg_pengiriman");
        $this->m_brg_pengiriman->set_id_fk_pengiriman($id_pengiriman);
        $result = $this->m_brg_pengiriman->list_retur();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_pengiriman"];
                $response["content"][$a]["qty"] = number_format($result[$a]["brg_pengiriman_qty"],"2",",",".");
                $response["content"][$a]["note"] = $result[$a]["brg_pengiriman_note"];
                $response["content"][$a]["id_pengiriman"] = $result[$a]["id_fk_pengiriman"];
                $response["content"][$a]["id_satuan"] = $result[$a]["id_fk_satuan"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["satuan"] = $result[$a]["satuan_nama"];
                $response["content"][$a]["brg_qty_retur"] = number_format($result[$a]["retur_kembali_qty"],"2",",",".");
                $response["content"][$a]["brg_satuan_retur"] = $result[$a]["retur_kembali_satuan"];
                $response["content"][$a]["brg_notes_retur"] = $result[$a]["retur_kembali_note"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "TIDAK ADA BARANG PENERIMAAN";
        }
        echo json_encode($response);
    }
    private function check_stok($tipe,$id_brg,$request_qty){
        if(strtolower($tipe) == "penjualan"){
            $sql = "select id_fk_barang,brg_cabang_qty from tbl_brg_penjualan
            inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_brg_penjualan.id_fk_barang
            where id_pk_brg_penjualan = ?
            and tbl_brg_cabang.id_fk_cabang = ?
            and brg_cabang_qty >= ".$request_qty;
        }
        else if(strtolower($tipe) == "retur"){
            $sql = "select tbl_brg_cabang.id_fk_brg,brg_cabang_qty from tbl_retur_kembali
            inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_retur_kembali.id_fk_brg
            where id_pk_retur_kembali = ?
            and tbl_brg_cabang.id_fk_cabang = ?
            and brg_cabang_qty >= ".$request_qty;
        }
        $args = array(
            $id_brg,$this->session->id_cabang
        );
        $result = executeQuery($sql,$args);
        if($result->num_rows() > 0){
            return true;
        }
        else{
            return false;
        }
    }
}