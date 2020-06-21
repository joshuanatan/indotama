<?php
defined("BASEPATH") or exit("no direct script");
class Penjualan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_penjualan");
        $columns = $this->m_penjualan->columns();
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
        $this->load->model("m_penjualan");
        $this->m_penjualan->set_id_fk_cabang($id_cabang);
        $result = $this->m_penjualan->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_penjualan"];
                $response["content"][$a]["nomor"] = $result["data"][$a]["penj_nomor"];
                $response["content"][$a]["tgl"] = explode(" ",$result["data"][$a]["penj_tgl"])[0];
                $response["content"][$a]["dateline_tgl"] = explode(" ",$result["data"][$a]["penj_dateline_tgl"])[0];
                $response["content"][$a]["status"] = $result["data"][$a]["penj_status"];
                $response["content"][$a]["jenis"] = $result["data"][$a]["penj_jenis"];
                $response["content"][$a]["tipe_pembayaran"] = $result["data"][$a]["penj_tipe_pembayaran"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["penj_last_modified"];
                $response["content"][$a]["name_cust"] = $result["data"][$a]["cust_name"];
                $response["content"][$a]["perusahaan_cust"] = $result["data"][$a]["cust_perusahaan"];
                $response["content"][$a]["cust_display"] = $result["data"][$a]["cust_perusahaan"]." - ".$result["data"][$a]["cust_name"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nomor",
            "tgl",
            "dateline_tgl",
            "jenis",
            "tipe_pembayaran",
            "cust_display",
            "status",
            "last_modified",
        );
        echo json_encode($response);
    }
    public function brg_penjualan(){
        $response["status"] = "SUCCESS";
        $id_penjualan = $this->input->get("id");
        if($id_penjualan != "" && is_numeric($id_penjualan)){
            $this->load->model("m_brg_penjualan");
            $this->m_brg_penjualan->set_id_fk_penjualan($id_penjualan);
            $result = $this->m_brg_penjualan->list();
            if($result->num_rows() > 0){
                $result = $result->result_array();
                for($a = 0; $a<count($result); $a++){
                    $response["content"][$a]["id"] = $result[$a]["id_pk_brg_penjualan"];
                    $response["content"][$a]["qty"] = $result[$a]["brg_penjualan_qty_real"];
                    $response["content"][$a]["satuan"] = $result[$a]["brg_penjualan_satuan_real"];
                    $response["content"][$a]["harga"] = $result[$a]["brg_penjualan_harga"];
                    $response["content"][$a]["note"] = $result[$a]["brg_penjualan_note"];
                    $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                    $response["content"][$a]["last_modified"] = $result[$a]["brg_penjualan_last_modified"];
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "No Data";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid ID";
        }
        echo json_encode($response);
    }
    public function tmbhn_penjualan(){
        $response["status"] = "SUCCESS";
        $id_penjualan = $this->input->get("id");
        if($id_penjualan != "" && is_numeric($id_penjualan)){
            $this->load->model("m_tambahan_penjualan");
            $this->m_tambahan_penjualan->set_id_fk_penjualan($id_penjualan);
            $result = $this->m_tambahan_penjualan->list();
            if($result->num_rows() > 0){
                $result = $result->result_array();
                for($a = 0; $a<count($result); $a++){
                    $response["content"][$a]["id"] = $result[$a]["id_pk_tmbhn"];
                    $response["content"][$a]["tmbhn"] = $result[$a]["tmbhn"];
                    $response["content"][$a]["jumlah"] = $result[$a]["tmbhn_jumlah"];
                    $response["content"][$a]["satuan"] = $result[$a]["tmbhn_satuan"];
                    $response["content"][$a]["harga"] = $result[$a]["tmbhn_harga"];
                    $response["content"][$a]["notes"] = $result[$a]["tmbhn_notes"];
                    $response["content"][$a]["status"] = $result[$a]["tmbhn_status"];
                    $response["content"][$a]["last_modified"] = $result[$a]["tmbhn_last_modified"];
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "No Data";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid ID";
        }
        echo json_encode($response);
    }
    public function remove_brg_penjualan(){
        $response["status"] = "SUCCESS";
        $id_pk_brg_penjualan = $this->input->get("id");
        if($id_pk_brg_penjualan != "" && is_numeric($id_pk_brg_penjualan)){
            $this->load->model("m_brg_penjualan");
            $this->m_brg_penjualan->set_delete($id_pk_brg_penjualan);
            if($this->m_brg_penjualan->delete()){
                $response["msg"] = "Data is deleted from database";
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Delete function error";
            }
        }   
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "ID Invalid";
        }
        echo json_encode($response);
    }
    public function remove_tmbhn_penjualan(){
        $response["status"] = "SUCCESS";
        $id_pk_tmbhn = $this->input->get("id");
        if($id_pk_tmbhn != "" && is_numeric($id_pk_tmbhn)){
            $this->load->model("m_tambahan_penjualan");
            $this->m_tambahan_penjualan->set_delete($id_pk_tmbhn);
            if($this->m_tambahan_penjualan->delete()){
                $response["msg"] = "Data is deleted from database";
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Delete function error";
            }
        }   
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "ID Invalid";
        }
        echo json_encode($response);
    }
    public function remove_pembayaran_penjualan(){
        $response["status"] = "SUCCESS";
        $id_pk_penjualan_pembayaran = $this->input->get("id");
        if($id_pk_penjualan_pembayaran != "" && is_numeric($id_pk_penjualan_pembayaran)){
            $this->load->model("m_penjualan_pembayaran");
            $this->m_penjualan_pembayaran->set_delete($id_pk_penjualan_pembayaran);
            if($this->m_penjualan_pembayaran->delete()){
                $response["msg"] = "Data is deleted from database";
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Delete function error";
            }
        }   
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "ID Invalid";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        
        $this->form_validation->set_rules("nomor","nomor","required");
        $this->form_validation->set_rules("tgl","tgl","required");
        $this->form_validation->set_rules("dateline","dateline","required");
        $this->form_validation->set_rules("customer","customer","required");
        $this->form_validation->set_rules("jenis_penjualan","jenis_penjualan","required");
        $this->form_validation->set_rules("jenis_pembayaran","jenis_pembayaran","required");
        if($this->form_validation->run()){
            $penj_nomor = $this->input->post("nomor");
            $penj_tgl = $this->input->post("tgl");
            $penj_dateline_tgl = $this->input->post("dateline");
            $penj_jenis = $this->input->post("jenis_penjualan");
            $penj_tipe_pembayaran = $this->input->post("jenis_pembayaran");
            $customer = $this->input->post("customer");
            $id_fk_cabang = $this->input->post("id_cabang");
            $penj_status = "AKTIF";

            $this->input->post("customer");
            $this->load->model("m_customer");
            $this->m_customer->set_cust_perusahaan($customer);
            $result = $this->m_customer->detail_by_perusahaan();
            if($result->num_rows() > 0){
                $result = $result->result_array();
                $id_fk_customer = $result[0]["id_pk_cust"];
            }
            else{
                $id_fk_customer = $this->m_customer->short_insert();
            }
            $this->load->model("m_penjualan");
            if($this->m_penjualan->set_insert($penj_nomor,$penj_tgl,$penj_dateline_tgl,$penj_jenis,$penj_tipe_pembayaran,$id_fk_customer,$id_fk_cabang,$penj_status)){
                $id_penjualan = $this->m_penjualan->insert();
                if($id_penjualan){
                    if(strtolower($penj_jenis) == "online"){
                        $penj_on_marketplace = $this->input->post("marketplace");
                        $penj_on_no_resi = $this->input->post("no_resi");
                        $penj_on_kurir = $this->input->post("kurir");
                        $penj_on_status = "AKTIF";
                        $id_fk_penjualan = $id_penjualan;;
                        $this->load->model("m_penjualan_online");
                        if($this->m_penjualan_online->set_insert($penj_on_marketplace,$penj_on_no_resi,$penj_on_kurir,$penj_on_status,$id_fk_penjualan)){
                            if($this->m_penjualan_online->insert()){
                                $response["pnjonlinests"] = "SUCCESS";
                                $response["pnjonlinemsg"] = "Data is recorded to database";
                            }
                            else{
                                $response["pnjonlinests"] = "ERROR";
                                $response["pnjonlinemsg"] = "Insert function error";
                            }
                        }
                        else{
                            $response["pnjonlinests"] = "ERROR";
                            $response["pnjonlinemsg"] = "Setter function error";
                        }
                    }
                    $response["msg"] = "Data is recorded to database";
                    
                    $check = $this->input->post("check");
                    if($check != ""){
                        $counter = 0;
                        foreach($check as $a){
                            $this->form_validation->set_rules("brg".$a,"brg","required");
                            $this->form_validation->set_rules("brg_qty_real".$a,"brg_qty_real","required");
                            $this->form_validation->set_rules("brg_qty".$a,"brg_qty","required");
                            $this->form_validation->set_rules("brg_price".$a,"brg_price","required");
                            $this->form_validation->set_rules("brg_notes".$a,"brg_notes","required");
                            if($this->form_validation->run()){
                                $brg_qty = $this->input->post("brg_qty".$a);
                                $brg_qty = explode(" ",$brg_qty);
                                $brg_penjualan_qty = $brg_qty[0];
                                $brg_penjualan_satuan = $brg_qty[1];
                                
                                $brg_qty = $this->input->post("brg_qty_real".$a);
                                $brg_qty = explode(" ",$brg_qty);
                                $brg_penjualan_qty_real = $brg_qty[0];
                                $brg_penjualan_satuan_real = $brg_qty[1];

                                $brg_penjualan_harga = $this->input->post("brg_price".$a);
                                $brg_penjualan_note = $this->input->post("brg_notes".$a);
                                $brg_penjualan_status = "AKTIF";
                                $id_fk_penjualan = $id_penjualan;
                                $barang = $this->input->post("brg".$a);
                                $this->load->model("m_barang");
                                $this->m_barang->set_brg_nama($barang);
                                $result = $this->m_barang->detail_by_name();
                                if($result->num_rows() > 0){
                                    $result = $result->result_array();
                                    $id_fk_barang = $result[0]["id_pk_brg"];

                                    $this->load->model("m_brg_penjualan");
                                    if($this->m_brg_penjualan->set_insert($brg_penjualan_qty_real,$brg_penjualan_satuan_real,$brg_penjualan_qty,$brg_penjualan_satuan,$brg_penjualan_harga,$brg_penjualan_note,$brg_penjualan_status,$id_fk_penjualan,$id_fk_barang)){
                                        if($this->m_brg_penjualan->insert()){
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
                                else{
                                    $response["itmsts"][$counter] = "ERROR";
                                    $response["itmmsg"][$counter] = "BARANG TIDAK ";
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
                        $response["itmsts"] = "ERROR";
                        $response["itmmsg"] = "No Checks on Item";
                    }

                    $tambahan = $this->input->post("tambahan");
                    if($tambahan != ""){
                        $counter = 0;
                        foreach($tambahan as $a){
                            $this->load->library("form_validation");
                            $this->form_validation->set_rules("tmbhn".$a,"tmbhn","required");
                            $this->form_validation->set_rules("tmbhn_jumlah".$a,"tmbhn_jumlah","required");
                            $this->form_validation->set_rules("tmbhn_harga".$a,"tmbhn_harga","required");
                            $this->form_validation->set_rules("tmbhn_notes".$a,"tmbhn_notes","required");
                            if($this->form_validation->run()){
                                $tmbhn = $this->input->post("tmbhn".$a);
                                $qty = $this->input->post("tmbhn_jumlah".$a);
                                $qty = explode(" ",$qty);
                                $tmbhn_jumlah = $qty[0];
                                $tmbhn_satuan = $qty[1];
                                $tmbhn_harga = $this->input->post("tmbhn_harga".$a);
                                $tmbhn_notes = $this->input->post("tmbhn_notes".$a);
                                $tmbhn_status = "AKTIF";
                                $id_fk_penjualan = $id_penjualan;
                                
                                $this->load->model("m_tambahan_penjualan");
                                if($this->m_tambahan_penjualan->set_insert($tmbhn,$tmbhn_jumlah,$tmbhn_satuan,$tmbhn_harga,$tmbhn_notes,$tmbhn_status,$id_fk_penjualan)){
                                    if($this->m_tambahan_penjualan->insert()){
                                        $response["tmbhnsts"][$counter] = "SUCCESS";
                                        $response["tmbhnmsg"][$counter] = "Data is recorded to database";
                                    }
                                    else{
                                        $response["status"] = "ERROR";
                                        $response["tmbhnsts"][$counter] = "ERROR";
                                        $response["tmbhnmsg"][$counter] = "Insert function error";
                                    }
                                }
                                else{
                                    $response["status"] = "ERROR";
                                    $response["tmbhnsts"][$counter] = "ERROR";
                                    $response["tmbhnmsg"][$counter] = "Setter function error";
                                }
                            }
                            else{
                                $response["status"] = "ERROR";
                                $response["tmbhnsts"][$counter] = "ERROR";
                                $response["tmbhnmsg"][$counter] = validation_errors();
                            }
                            $counter++;
                        }
                    }
                    else{
                        $response["tmbhnsts"] = "ERROR";
                        $response["tmbhnmsg"] = "No Checks on Tambahan";
                    }
                    
                    $pembayaran = $this->input->post("pembayaran");
                    if($pembayaran != ""){
                        $counter = 0;
                        foreach($pembayaran as $a){
                            $this->load->library("form_validation");
                            $this->form_validation->set_rules("pmbyrn_nama".$a,"pmbyrn_nama","required");
                            $this->form_validation->set_rules("pmbyrn_persen".$a,"pmbyrn_persen","required");
                            $this->form_validation->set_rules("pmbyrn_nominal".$a,"pmbyrn_nominal","required");
                            $this->form_validation->set_rules("pmbyrn_notes".$a,"pmbyrn_notes","required");
                            $this->form_validation->set_rules("pmbyrn_dateline".$a,"pmbyrn_dateline","required");
                            
                            if($this->form_validation->run()){
                                $id_fk_penjualan = $id_penjualan;
                                $penjualan_pmbyrn_nama = $this->input->post("pmbyrn_nama".$a); 
                                $penjualan_pmbyrn_persen = $this->input->post("pmbyrn_persen".$a);
                                $penjualan_pmbyrn_nominal = $this->input->post("pmbyrn_nominal".$a);
                                $penjualan_pmbyrn_notes = $this->input->post("pmbyrn_notes".$a);
                                $penjualan_pmbyrn_dateline = $this->input->post("pmbyrn_dateline".$a);
                                $penjualan_pmbyrn_status = "AKTIF";
                                
                                $this->load->model("m_penjualan_pembayaran");
                                if($this->m_penjualan_pembayaran->set_insert($id_fk_penjualan,$penjualan_pmbyrn_nama,$penjualan_pmbyrn_persen,$penjualan_pmbyrn_nominal,$penjualan_pmbyrn_notes,$penjualan_pmbyrn_dateline,$penjualan_pmbyrn_status)){
                                    if($this->m_penjualan_pembayaran->insert()){
                                        $response["pmbyrnsts"][$counter] = "SUCCESS";
                                        $response["pmbyrnmsg"][$counter] = "Data is recorded to database";
                                    }
                                    else{
                                        $response["status"] = "ERROR";
                                        $response["pmbyrnsts"][$counter] = "ERROR";
                                        $response["pmbyrnmsg"][$counter] = "Insert function error";
                                    }
                                }
                                else{
                                    $response["status"] = "ERROR";
                                    $response["pmbyrnsts"][$counter] = "ERROR";
                                    $response["pmbyrnmsg"][$counter] = "Setter function error";
                                }
                            }
                            else{
                                $response["status"] = "ERROR";
                                $response["pmbyrnsts"][$counter] = "ERROR";
                                $response["pmbyrnmsg"][$counter] = validation_errors();
                            }
                            $counter++;
                        }
                    }
                    else{
                        $response["pmbyrnsts"] = "ERROR";
                        $response["pmbyrnmsg"] = "No Checks on Pembayaran";
                    }

                    $brg_custom = $this->input->post("brg_custom");
                    if($brg_custom != ""){
                        $counter = 0;
                        foreach($brg_custom as $a){
                            $id_brg_custom = $this->input->post("id_brg_custom".$a);
                            $this->load->model("m_brg_pindah");
                            $this->m_brg_pindah->set_id_pk_brg_pindah($id_brg_custom);
                            $this->m_brg_pindah->set_id_fk_refrensi_sumber($id_penjualan);
                            $this->m_brg_pindah->update_id_fk_refrensi_sumber();
                            
                            $response["brgcustomsts"][$counter] = "SUCCESS";
                            $response["brgcustommsg"][$counter] = "Data is recorded to database";
                            $counter++;
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
        
        $this->form_validation->set_rules("id_penjualan","id","required");
        $this->form_validation->set_rules("nomor","nomor","required");
        $this->form_validation->set_rules("tgl","tgl","required");
        $this->form_validation->set_rules("dateline","dateline","required");
        $this->form_validation->set_rules("customer","customer","required");
        $this->form_validation->set_rules("jenis_penjualan","jenis_penjualan","required");
        $this->form_validation->set_rules("jenis_pembayaran","jenis_pembayaran","required");
        if($this->form_validation->run()){
            $id_penjualan = $this->input->post("id_penjualan");
            $penj_nomor = $this->input->post("nomor");
            $penj_tgl = $this->input->post("tgl");
            $penj_dateline_tgl = $this->input->post("dateline");
            $penj_jenis = $this->input->post("jenis_penjualan");
            $penj_tipe_pembayaran = $this->input->post("jenis_pembayaran");
            $customer = $this->input->post("customer");

            $this->input->post("customer");
            $this->load->model("m_customer");
            $this->m_customer->set_cust_perusahaan($customer);
            $result = $this->m_customer->detail_by_perusahaan();
            if($result->num_rows() > 0){
                $result = $result->result_array();
                $id_fk_customer = $result[0]["id_pk_cust"];
            }
            else{
                $id_fk_customer = $this->m_customer->short_insert();
            }
            $this->load->model("m_penjualan");
            if($this->m_penjualan->set_update($id_penjualan,$penj_nomor,$penj_dateline_tgl,$penj_jenis,$penj_tipe_pembayaran,$penj_tgl,$id_fk_customer)){
                if($this->m_penjualan->update()){ 
                    $response["msg"] = "Data is update to database";  
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
            
            if(strtolower($penj_jenis) == "online"){
                $penj_on_marketplace = $this->input->post("marketplace");
                $penj_on_no_resi = $this->input->post("no_resi");
                $penj_on_kurir = $this->input->post("kurir");
                $id_fk_penjualan = $id_penjualan;

                $this->load->model("m_penjualan_online");
                if($this->m_penjualan_online->set_update($penj_on_marketplace,$penj_on_no_resi,$penj_on_kurir,$id_fk_penjualan)){
                    if($this->m_penjualan_online->update()){
                        $response["pnjonlinests"] = "SUCCESS";
                        $response["pnjonlinemsg"] = "Data is updated to database";
                    }
                    else{
                        $response["pnjonlinests"] = "ERROR";
                        $response["pnjonlinemsg"] = "Update function error";
                    }
                }
                else{
                    $response["pnjonlinests"] = "ERROR";
                    $response["pnjonlinemsg"] = "Setter function error";
                }
            }
            
            $check = $this->input->post("check");
            if($check != ""){
                $counter = 0;
                foreach($check as $a){
                    $this->form_validation->set_rules("brg".$a,"brg","required");
                    $this->form_validation->set_rules("brg_qty".$a,"brg_qty","required");
                    $this->form_validation->set_rules("brg_qty".$a,"brg_qty","required");
                    $this->form_validation->set_rules("brg_price".$a,"brg_price","required");
                    $this->form_validation->set_rules("brg_notes".$a,"brg_notes","required");
                    if($this->form_validation->run()){
                        $brg_qty = $this->input->post("brg_qty".$a);
                        $brg_qty = explode(" ",$brg_qty);
                        $brg_penjualan_qty = $brg_qty[0];
                        $brg_penjualan_satuan = $brg_qty[1];
                        $brg_penjualan_harga = $this->input->post("brg_price".$a);
                        $brg_penjualan_note = $this->input->post("brg_notes".$a);
                        $brg_penjualan_status = "AKTIF";
                        $id_fk_penjualan = $id_penjualan;
                        $barang = $this->input->post("brg".$a);
                        $this->load->model("m_barang");
                        $this->m_barang->set_brg_nama($barang);
                        $result = $this->m_barang->detail_by_name();
                        if($result->num_rows() > 0){
                            $result = $result->result_array();
                            $id_fk_barang = $result[0]["id_pk_brg"];

                            $this->load->model("m_brg_penjualan");
                            if($this->m_brg_penjualan->set_insert($brg_penjualan_qty,$brg_penjualan_satuan,$brg_penjualan_harga,$brg_penjualan_note,$brg_penjualan_status,$id_fk_penjualan,$id_fk_barang)){
                                if($this->m_brg_penjualan->insert()){
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
                        else{
                            $response["itmsts"][$counter] = "ERROR";
                            $response["itmmsg"][$counter] = "BARANG TIDAK ";
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
                $response["itmsts"] = "ERROR";
                $response["itmmsg"] = "No Checks on Item";
            }
            $check = $this->input->post("check_edit");
            if($check != ""){
                $counter = 0;
                foreach($check as $a){
                    $this->form_validation->set_rules("id_brg_jual_edit".$a,"id","required");
                    $this->form_validation->set_rules("brg_edit".$a,"brg","required");
                    $this->form_validation->set_rules("brg_qty_real_edit".$a,"brg_qty_real","required");
                    $this->form_validation->set_rules("brg_qty_edit".$a,"brg_qty","required");
                    $this->form_validation->set_rules("brg_price_edit".$a,"brg_price","required");
                    $this->form_validation->set_rules("brg_notes_edit".$a,"brg_notes","required");
                    if($this->form_validation->run()){
                        $id_pk_brg_penjualan = $this->input->post("id_brg_jual_edit".$a);
                        $brg_qty = $this->input->post("brg_qty_edit".$a);
                        $brg_qty = explode(" ",$brg_qty);
                        $brg_penjualan_qty = $brg_qty[0];
                        $brg_penjualan_satuan = $brg_qty[1];
                        
                        $brg_qty = $this->input->post("brg_qty_real_edit".$a);
                        $brg_qty = explode(" ",$brg_qty);
                        $brg_penjualan_qty_real = $brg_qty[0];
                        $brg_penjualan_satuan_real = $brg_qty[1];

                        $brg_penjualan_harga = $this->input->post("brg_price_edit".$a);
                        $brg_penjualan_note = $this->input->post("brg_notes_edit".$a);
                        $barang = $this->input->post("brg_edit".$a);

                        $this->load->model("m_barang");
                        $this->m_barang->set_brg_nama($barang);
                        $result = $this->m_barang->detail_by_name();
                        if($result->num_rows() > 0){
                            $result = $result->result_array();
                            $id_fk_barang = $result[0]["id_pk_brg"];

                            $this->load->model("m_brg_penjualan");
                            if($this->m_brg_penjualan->set_update($id_pk_brg_penjualan,$brg_penjualan_qty_real,$brg_penjualan_satuan_real,$brg_penjualan_qty,$brg_penjualan_satuan,$brg_penjualan_harga,$brg_penjualan_note,$id_fk_barang)){
                                if($this->m_brg_penjualan->update()){
                                    $response["itmsts"][$counter] = "SUCCESS";
                                    $response["itmmsg"][$counter] = "Data is updated to database";
                                }
                                else{
                                    $response["itmsts"][$counter] = "ERROR";
                                    $response["itmmsg"][$counter] = "Update function error";
                                }
                            }
                            else{
                                $response["itmsts"][$counter] = "ERROR";
                                $response["itmmsg"][$counter] = "Setter function error";
                            }
                        }
                        else{
                            $response["itmsts"][$counter] = "ERROR";
                            $response["itmmsg"][$counter] = "BARANG TIDAK ";
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
                $response["itmsts"] = "ERROR";
                $response["itmmsg"] = "No Checks on Item";
            }
            
            $tambahan = $this->input->post("tambahan");
            if($tambahan != ""){
                $counter = 0;
                foreach($tambahan as $a){
                    $this->load->library("form_validation");
                    $this->form_validation->set_rules("tmbhn".$a,"tmbhn","required");
                    $this->form_validation->set_rules("tmbhn_jumlah".$a,"tmbhn_jumlah","required");
                    $this->form_validation->set_rules("tmbhn_harga".$a,"tmbhn_harga","required");
                    $this->form_validation->set_rules("tmbhn_notes".$a,"tmbhn_notes","required");
                    if($this->form_validation->run()){
                        $tmbhn = $this->input->post("tmbhn".$a);
                        $qty = $this->input->post("tmbhn_jumlah".$a);
                        $qty = explode(" ",$qty);
                        $tmbhn_jumlah = $qty[0];
                        $tmbhn_satuan = $qty[1];
                        $tmbhn_harga = $this->input->post("tmbhn_harga".$a);
                        $tmbhn_notes = $this->input->post("tmbhn_notes".$a);
                        $tmbhn_status = "AKTIF";
                        $id_fk_penjualan = $id_penjualan;
                        
                        $this->load->model("m_tambahan_penjualan");
                        if($this->m_tambahan_penjualan->set_insert($tmbhn,$tmbhn_jumlah,$tmbhn_satuan,$tmbhn_harga,$tmbhn_notes,$tmbhn_status,$id_fk_penjualan)){
                            if($this->m_tambahan_penjualan->insert()){
                                $response["tmbhnsts"][$counter] = "SUCCESS";
                                $response["tmbhnmsg"][$counter] = "Data is recorded to database";
                            }
                            else{
                                $response["status"] = "ERROR";
                                $response["tmbhnsts"][$counter] = "ERROR";
                                $response["tmbhnmsg"][$counter] = "Insert function error";
                            }
                        }
                        else{
                            $response["status"] = "ERROR";
                            $response["tmbhnsts"][$counter] = "ERROR";
                            $response["tmbhnmsg"][$counter] = "Setter function error";
                        }
                    }
                    else{
                        $response["status"] = "ERROR";
                        $response["tmbhnsts"][$counter] = "ERROR";
                        $response["tmbhnmsg"][$counter] = validation_errors();
                    }
                    $counter++;
                }
            }
            else{
                $response["tmbhnsts"] = "ERROR";
                $response["tmbhnmsg"] = "No Checks on Tambahan";
            }
            $tambahan = $this->input->post("tambahan_edit");
            if($tambahan != ""){
                $counter = 0;
                foreach($tambahan as $a){
                    $this->load->library("form_validation");
                    $this->form_validation->set_rules("id_tmbhn_edit".$a,"id","required");
                    $this->form_validation->set_rules("tmbhn_edit".$a,"tmbhn","required");
                    $this->form_validation->set_rules("tmbhn_jumlah_edit".$a,"tmbhn_jumlah","required");
                    $this->form_validation->set_rules("tmbhn_harga_edit".$a,"tmbhn_harga","required");
                    $this->form_validation->set_rules("tmbhn_notes_edit".$a,"tmbhn_notes","required");
                    if($this->form_validation->run()){
                        $id_pk_tmbhn = $this->input->post("id_tmbhn_edit".$a);
                        $tmbhn = $this->input->post("tmbhn_edit".$a);
                        $qty = $this->input->post("tmbhn_jumlah_edit".$a);
                        $qty = explode(" ",$qty);
                        $tmbhn_jumlah = $qty[0];
                        $tmbhn_satuan = $qty[1];
                        $tmbhn_harga = $this->input->post("tmbhn_harga_edit".$a);
                        $tmbhn_notes = $this->input->post("tmbhn_notes_edit".$a);
                        
                        $this->load->model("m_tambahan_penjualan");
                        if($this->m_tambahan_penjualan->set_update($id_pk_tmbhn,$tmbhn,$tmbhn_jumlah,$tmbhn_satuan,$tmbhn_harga,$tmbhn_notes)){
                            if($this->m_tambahan_penjualan->update()){
                                $response["tmbhnsts"][$counter] = "SUCCESS";
                                $response["tmbhnmsg"][$counter] = "Data is updated to database";
                            }
                            else{
                                $response["status"] = "ERROR";
                                $response["tmbhnsts"][$counter] = "ERROR";
                                $response["tmbhnmsg"][$counter] = "Update function error";
                            }
                        }
                        else{
                            $response["status"] = "ERROR";
                            $response["tmbhnsts"][$counter] = "ERROR";
                            $response["tmbhnmsg"][$counter] = "Setter function error";
                        }
                    }
                    else{
                        $response["status"] = "ERROR";
                        $response["tmbhnsts"][$counter] = "ERROR";
                        $response["tmbhnmsg"][$counter] = validation_errors();
                    }
                    $counter++;
                }
            }
            else{
                $response["tmbhnsts"] = "ERROR";
                $response["tmbhnmsg"] = "No Checks on Tambahan";
            }
            
            $pembayaran = $this->input->post("pembayaran");
            if($pembayaran != ""){
                $counter = 0;
                foreach($pembayaran as $a){
                    $this->load->library("form_validation");
                    $this->form_validation->set_rules("pmbyrn_nama".$a,"pmbyrn_nama","required");
                    $this->form_validation->set_rules("pmbyrn_persen".$a,"pmbyrn_persen","required");
                    $this->form_validation->set_rules("pmbyrn_nominal".$a,"pmbyrn_nominal","required");
                    $this->form_validation->set_rules("pmbyrn_notes".$a,"pmbyrn_notes","required");
                    $this->form_validation->set_rules("pmbyrn_dateline".$a,"pmbyrn_dateline","required");
                    
                    if($this->form_validation->run()){
                        $id_fk_penjualan = $id_penjualan;
                        $penjualan_pmbyrn_nama = $this->input->post("pmbyrn_nama".$a); 
                        $penjualan_pmbyrn_persen = $this->input->post("pmbyrn_persen".$a);
                        $penjualan_pmbyrn_nominal = $this->input->post("pmbyrn_nominal".$a);
                        $penjualan_pmbyrn_notes = $this->input->post("pmbyrn_notes".$a);
                        $penjualan_pmbyrn_dateline = $this->input->post("pmbyrn_dateline".$a);
                        $penjualan_pmbyrn_status = "AKTIF";
                        
                        $this->load->model("m_penjualan_pembayaran");
                        if($this->m_penjualan_pembayaran->set_insert($id_fk_penjualan,$penjualan_pmbyrn_nama,$penjualan_pmbyrn_persen,$penjualan_pmbyrn_nominal,$penjualan_pmbyrn_notes,$penjualan_pmbyrn_dateline,$penjualan_pmbyrn_status)){
                            if($this->m_penjualan_pembayaran->insert()){
                                $response["pmbyrnsts"][$counter] = "SUCCESS";
                                $response["pmbyrnmsg"][$counter] = "Data is recorded to database";
                            }
                            else{
                                $response["status"] = "ERROR";
                                $response["pmbyrnsts"][$counter] = "ERROR";
                                $response["pmbyrnmsg"][$counter] = "Insert function error";
                            }
                        }
                        else{
                            $response["status"] = "ERROR";
                            $response["pmbyrnsts"][$counter] = "ERROR";
                            $response["pmbyrnmsg"][$counter] = "Setter function error";
                        }
                    }
                    else{
                        $response["status"] = "ERROR";
                        $response["pmbyrnsts"][$counter] = "ERROR";
                        $response["pmbyrnmsg"][$counter] = validation_errors();
                    }
                    $counter++;
                }
            }
            else{
                $response["pmbyrnsts"] = "ERROR";
                $response["pmbyrnmsg"] = "No Checks on Pembayaran";
            }
            $pembayaran = $this->input->post("pembayaran_edit");
            if($pembayaran != ""){
                $counter = 0;
                foreach($pembayaran as $a){
                    $this->load->library("form_validation");
                    $this->form_validation->set_rules("id_pembayaran_edit".$a,"id pembayaran","required");
                    $this->form_validation->set_rules("pmbyrn_nama_edit".$a,"pmbyrn_nama","required");
                    $this->form_validation->set_rules("pmbyrn_persen_edit".$a,"pmbyrn_persen","required");
                    $this->form_validation->set_rules("pmbyrn_nominal_edit".$a,"pmbyrn_nominal","required");
                    $this->form_validation->set_rules("pmbyrn_notes_edit".$a,"pmbyrn_notes","required");
                    $this->form_validation->set_rules("pmbyrn_dateline_edit".$a,"pmbyrn_dateline","required");
                    
                    if($this->form_validation->run()){
                        
                        $id_pk_penjualan_pembayaran = $this->input->post("id_pembayaran_edit".$a); 
                        $penjualan_pmbyrn_nama = $this->input->post("pmbyrn_nama_edit".$a); 
                        $penjualan_pmbyrn_persen = $this->input->post("pmbyrn_persen_edit".$a);
                        $penjualan_pmbyrn_nominal = $this->input->post("pmbyrn_nominal_edit".$a);
                        $penjualan_pmbyrn_notes = $this->input->post("pmbyrn_notes_edit".$a);
                        $penjualan_pmbyrn_dateline = $this->input->post("pmbyrn_dateline_edit".$a);
                        
                        $this->load->model("m_penjualan_pembayaran");
                        if($this->m_penjualan_pembayaran->set_update($id_pk_penjualan_pembayaran,$penjualan_pmbyrn_nama,$penjualan_pmbyrn_persen,$penjualan_pmbyrn_nominal,$penjualan_pmbyrn_notes,$penjualan_pmbyrn_dateline)){
                            if($this->m_penjualan_pembayaran->update()){
                                $response["pmbyrnsts"][$counter] = "SUCCESS";
                                $response["pmbyrnmsg"][$counter] = "Data is updated to database";
                            }
                            else{
                                $response["status"] = "ERROR";
                                $response["pmbyrnsts"][$counter] = "ERROR";
                                $response["pmbyrnmsg"][$counter] = "Update function error";
                            }
                        }
                        else{
                            $response["status"] = "ERROR";
                            $response["pmbyrnsts"][$counter] = "ERROR";
                            $response["pmbyrnmsg"][$counter] = "Setter function error";
                        }
                    }
                    else{
                        $response["status"] = "ERROR";
                        $response["pmbyrnsts"][$counter] = "ERROR";
                        $response["pmbyrnmsg"][$counter] = validation_errors();
                    }
                    $counter++;
                }
            }
            else{
                $response["pmbyrnsts"] = "ERROR";
                $response["pmbyrnmsg"] = "No Checks on Pembayaran";
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
        $id_pk_penjualan = $this->input->get("id");
        if($id_pk_penjualan != "" && is_numeric($id_pk_penjualan)){
            $this->load->model("m_penjualan");
            if($this->m_penjualan->set_delete($id_pk_penjualan)){
                if($this->m_penjualan->delete()){
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
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_penjualan");
        $cabang = $this->input->get("id_cabang");
        if($cabang && is_numeric($cabang)){
            $this->m_penjualan->set_id_fk_cabang($cabang);
            $result = $this->m_penjualan->list();
            if($result->num_rows() > 0){ 
                $result = $result->result_array();
                for($a = 0; $a<count($result); $a++){
                    $response["content"][$a]["id"] = $result[$a]["id_pk_penjualan"];
                    $response["content"][$a]["nomor"] = $result[$a]["penj_nomor"];
                    $response["content"][$a]["tgl"] = explode(" ",$result[$a]["penj_tgl"])[0];
                    $response["content"][$a]["dateline_tgl"] = explode(" ",$result[$a]["penj_dateline_tgl"])[0];
                    $response["content"][$a]["status"] = $result[$a]["penj_status"];
                    $response["content"][$a]["jenis"] = $result[$a]["penj_jenis"];
                    $response["content"][$a]["tipe_pembayaran"] = $result[$a]["penj_tipe_pembayaran"];
                    $response["content"][$a]["last_modified"] = $result[$a]["penj_last_modified"];
                    $response["content"][$a]["perusahaan_cust"] = ucwords($result[$a]["cust_perusahaan"]);
                    $response["content"][$a]["name_cust"] = ucwords($result[$a]["cust_name"]);
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "No Data";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid ID";
        }
        echo json_encode($response);
    }
    public function detail($no_penjualan){
        $response["status"] = "SUCCESS";
        $this->load->model("m_penjualan");
        $this->m_penjualan->set_penj_nomor($no_penjualan);
        $result = $this->m_penjualan->detail_by_penj_nomor();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["data"][$a]["id"] = $result[$a]["id_pk_penjualan"];
                $response["data"][$a]["nomor"] = $result[$a]["penj_nomor"];
                $response["data"][$a]["tgl"] = $result[$a]["penj_tgl"];
                $response["data"][$a]["dateline_tgl"] = $result[$a]["penj_dateline_tgl"];
                $response["data"][$a]["status"] = $result[$a]["penj_status"];
                $response["data"][$a]["jenis"] = $result[$a]["penj_jenis"];
                $response["data"][$a]["tipe_pembayaran"] = $result[$a]["penj_tipe_pembayaran"];
                $response["data"][$a]["last_modified"] = $result[$a]["penj_last_modified"];
                $response["data"][$a]["cust_perusahaan"] = strtoupper($result[$a]["cust_perusahaan"]);
                $response["data"][$a]["name_cust"] = strtoupper($result[$a]["cust_name"]);
                $response["data"][$a]["suff_cust"] = strtoupper($result[$a]["cust_suff"]);
                $response["data"][$a]["email_cust"] = $result[$a]["cust_email"];
                $response["data"][$a]["telp_cust"] = $result[$a]["cust_telp"];
                $response["data"][$a]["hp_cust"] = $result[$a]["cust_hp"];
                $response["data"][$a]["alamat_cust"] = $result[$a]["cust_alamat"];
                $response["data"][$a]["keterangan_cust"] = $result[$a]["cust_keterangan"];
            }
        }   
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Detail data untuk nomor terkait tidak ada"; 
        }
        echo json_encode($response);

    }
}