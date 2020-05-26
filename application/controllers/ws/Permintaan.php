<?php
defined("BASEPATH") or exit("No Direct Script");
class Permintaan extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_brg_permintaan");
        $columns = $this->m_brg_permintaan->columns();
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
        
        $this->load->model("m_brg_permintaan");

        $result = $this->m_brg_permintaan->content($page,$order_by,$order_direction,$search_key,$data_per_page);
        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg_permintaan"];
                $response["content"][$a]["qty"] = $result["data"][$a]["brg_permintaan_qty"];
                $response["content"][$a]["notes"] = $result["data"][$a]["brg_permintaan_notes"];
                $response["content"][$a]["deadline"] = $result["data"][$a]["brg_permintaan_deadline"];
                $response["content"][$a]["status"] = $result["data"][$a]["brg_permintaan_status"];
                $response["content"][$a]["id_fk_brg"] = $result["data"][$a]["id_fk_brg"];
                $response["content"][$a]["barang"] = $result["data"][$a]["brg_nama"];
                if($result["data"][$a]["qty_pemenuhan"]==null){
                    $response["content"][$a]["qty_pemenuhan"] = 0;
                }else{
                    $response["content"][$a]["qty_pemenuhan"] = $result["data"][$a]["qty_pemenuhan"];
                }
                $response["content"][$a]["nama_cabang"] = $result["data"][$a]["cabang_daerah"];
                $response["content"][$a]["id_fk_cabang"] = $result["data"][$a]["id_fk_cabang"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["brg_permintaan_create_date"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_permintaan_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "create_date",
            "barang",
            "qty_pemenuhan",
            "qty",
            "status"
        );
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_brg_permintaan");
        $result = $this->m_brg_permintaan->list();
        if($result->num_rows()){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
              /*  $response["content"][$a]["id"] = $result[$a]["id_pk_brg_jenis"];
                $response["content"][$a]["nama"] = $result[$a]["brg_jenis_nama"];
                $response["content"][$a]["status"] = $result[$a]["brg_jenis_status"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_jenis_last_modified"];*/
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
        
        $this->form_validation->set_rules("brg_nama","Nama Barang","required");
        $this->form_validation->set_rules("brg_permintaan_qty","Jumlah barang","required|numeric");
        $this->form_validation->set_rules("brg_permintaan_notes","Notes","required");
        $this->form_validation->set_rules("brg_permintaan_deadline","Deadline","required");
        if($this->form_validation->run()){
            $id_barang = get1Value("mstr_barang","id_pk_brg",array("brg_nama"=>$this->input->post("brg_nama")));
            if($id_barang){
                $this->load->model("m_brg_permintaan");
                $brg_permintaan_qty = $this->input->post("brg_permintaan_qty");
                $brg_permintaan_notes = $this->input->post("brg_permintaan_notes");
                $brg_permintaan_deadline = $this->input->post("brg_permintaan_deadline");
                $brg_permintaan_status = "BELUM";
                $id_fk_brg = $id_barang;
                $id_fk_cabang = $this->session->id_cabang;
                if($this->m_brg_permintaan->set_insert($brg_permintaan_qty,$brg_permintaan_notes,$brg_permintaan_deadline,$brg_permintaan_status,$id_fk_brg,$id_fk_cabang)){
                    $id_pk_brg_permintaan = $this->m_brg_permintaan->insert();
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Setter function error";
                }
            }else{
                $response["status"] = "ERROR";
                $response["msg"] = "Insert function error";
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
        $this->form_validation->set_rules("id_edit","id","required");
        $this->form_validation->set_rules("brg_permintaan_qty","Qty Permintaan","required");
        $this->form_validation->set_rules("brg_permintaan_notes","Notes","required");
        $this->form_validation->set_rules("brg_permintaan_deadline","Deadline","required");

        if($this->form_validation->run()){
            $id_pk_permintaan = $this->input->post("id_edit");
            $this->load->model("m_brg_permintaan");

            $brg_permintaan_qty = $this->input->post("brg_permintaan_qty");
            $brg_permintaan_notes = $this->input->post("brg_permintaan_notes");
            $brg_permintaan_deadline = $this->input->post("brg_permintaan_deadline");
            if($this->m_brg_permintaan->set_update($brg_permintaan_qty,$brg_permintaan_notes,$brg_permintaan_deadline,$id_pk_permintaan)){
                if($this->m_brg_permintaan->update()){
                    $response["msg"] = "Data is updated to database";
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
            $this->load->model("m_brg_permintaan");
            if($this->m_brg_permintaan->set_delete($id)){
                if($this->m_brg_permintaan->delete()){
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
}