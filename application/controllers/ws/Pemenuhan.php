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
                    $response["content"][$a]["id_fk_brg"] = $result["data"][$a]["id_mstr_barang_cabang_penyedia"];
                    $response["content"][$a]["nama_barang"] = $result["data"][$a]["brg_nama"];
                    $response["content"][$a]["stok_terpenuhi"] = $result["data"][$a]["qty_pemenuhan"];
                    $response["content"][$a]["cabang_peminta"] = $result["data"][$a]["cabang_daerah"];
                    $response["content"][$a]["toko_peminta"] = $result["data"][$a]["toko_nama"];
                    $response["content"][$a]["toko"] = $result["data"][$a]["toko_nama"]." ".$result["data"][$a]["cabang_daerah"];
                    $response["content"][$a]["id_fk_cabang"] = $result["data"][$a]["id_cabang_penyedia"];
                    $response["content"][$a]["tgl_permintaan"] = $result["data"][$a]["brg_permintaan_create_date"];
                    $response["content"][$a]["gambar_barang"] = "<img width='100px' src='" .$result["data"][$a]["brg_image"] . "'>";
                    $response["content"][$a]["jml_brg_cbg"] = $result["data"][$a]["brg_cabang_qty"];
                    
                }
            }
            else{
                $response["status"] = "ERROR";
            }
            $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
            $response["key"] = array(
                "tgl_permintaan",
                "toko",
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
        $this->form_validation->set_rules("id_fk_brg_permintaan","ID Permintaan","required");
        $this->form_validation->set_rules("brg_pemenuhan_tipe","Tipe Permintaan","required");
        $this->form_validation->set_rules("brg_skrg","Stok Sekarang","required");
        $this->form_validation->set_rules("brg_pemenuhan_qty","Jumlah Pemenuhan","required");

        if($this->form_validation->run()){
            $brg_pemenuhan_qty = $this->input->post("brg_pemenuhan_qty");
            $brg_pemenuhan_tipe =$this->input->post("brg_pemenuhan_tipe");
            $brg_pemenuhan_status = "AKTIF";
            $id_fk_brg_permintaan = $this->input->post("id_fk_brg_permintaan");

            if($brg_pemenuhan_tipe=="CABANG"){
                $id_fk_cabang = $this->session->id_cabang;
                $id_fk_warehouse = "0";
            }else{
                $id_fk_warehouse = $this->session->id_warehouse;
                $id_fk_cabang = "0";
            }
            $brg_pemenuhan_create_date =date("y-m-d h:i:s");
            $brg_pemenuhan_last_modified =date("y-m-d h:i:s");
            $id_create_data = $this->session->id_user;
            $id_last_modified =$this->session->id_user;

            //$stok_sisa = $this->input->post("brg_skrg");
            //$stok_minta = get1Value("tbl_brg_permintaan","brg_permintaan_qty",array("id_pk_brg_permintaan"=>$id_fk_brg_permintaan));
            
            $data_permintaan = array(
                "brg_permintaan_status"=>"SEDANG",
                "brg_permintaan_last_modified"=>date("y-m-d h:i:s"),
                "id_last_modified"=>$this->session->id_user,
            );
            $where_permintaan = array(
                "id_pk_brg_permintaan"=>$id_fk_brg_permintaan
            );
            updateRow("tbl_brg_permintaan",$data_permintaan,$where_permintaan);

            $data = array(
                "brg_pemenuhan_qty" => $brg_pemenuhan_qty,
                "brg_pemenuhan_tipe" => $brg_pemenuhan_tipe,
                "brg_pemenuhan_status" => $brg_pemenuhan_status,
                "id_fk_brg_permintaan" => $id_fk_brg_permintaan,
                "id_fk_cabang" => $id_fk_cabang,
                "id_fk_warehouse" => $id_fk_warehouse,
                "brg_pemenuhan_create_date" => $brg_pemenuhan_create_date,
                "brg_pemenuhan_last_modified" => $brg_pemenuhan_last_modified,
                "id_create_data" => $id_create_data,
                "id_last_modified" => $id_last_modified
            );
            $insert = insertRow("tbl_brg_pemenuhan",$data);
            if(!$insert){
                $response["status"] = "ERROR";
                $response["msg"] = validation_errors();
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
    public function list_pemenuhan(){
        $response["status"] = "SUCCESS";
        $id_brg_permintaan = $this->input->get("id_brg_permintaan");
        $this->load->model("m_brg_pemenuhan");
        $this->m_brg_pemenuhan->set_id_fk_brg_permintaan($id_brg_permintaan);
        $this->m_brg_pemenuhan->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_brg_pemenuhan->list_pemenuhan();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg_pemenuhan"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_pemenuhan_last_modified"];
                $response["content"][$a]["qty"] = $result[$a]["brg_pemenuhan_qty"];
                $response["content"][$a]["status"] = $result[$a]["brg_pemenuhan_status"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Data";
        }
        echo json_encode($response);
    }
    public function hapus_pemberian(){
        $response["status"] = "SUCCESS";
        $id_pemenuhan = $this->input->get("id_pemenuhan");
        $this->load->model("m_brg_pemenuhan");
        if($this->m_brg_pemenuhan->set_delete($id_pemenuhan)){
            if($this->m_brg_pemenuhan->delete()){

            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Delete Function Error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Setter Function Error";
        }
        echo json_encode($response);
    }
}