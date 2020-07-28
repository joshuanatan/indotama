<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class Penerimaan_permintaan extends CI_Controller{
    #class ini dibuat untuk tidak memusingkan proses insert yang berbeda dengan pengiriman & penerimaan yang lain.
    #ada potensi memusingkan karena proses pemberian itu langsung brg_pemberian ga ada master pemberian. oleh karena itu waktu pengiriman dan penerimaan ga punya id_pemberian yang bisa jadi acuan
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_t_penerimaan_permintaan");
        $columns = $this->m_t_penerimaan_permintaan->columns();
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
        $penerimaan_tempat = $this->input->get("type");
        $id_tempat_penerimaan = $this->session->id_cabang;
        $this->load->model("m_t_penerimaan_permintaan");
        
        $result = $this->m_t_penerimaan_permintaan->content($page,$order_by,$order_direction,$search_key,$data_per_page,$penerimaan_tempat,$id_tempat_penerimaan);
        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_penerimaan"];
                $response["content"][$a]["id_brg_pemenuhan"] = $result["data"][$a]["id_pk_brg_pemenuhan"];
                $response["content"][$a]["id_brg_pengiriman"] = $result["data"][$a]["id_pk_brg_pengiriman"];
                $response["content"][$a]["qty_brg_pengiriman"] = number_format($result["data"][$a]["brg_pengiriman_qty"],2,",",".");
                $response["content"][$a]["qty_brg_pengiriman_display"] = number_format($result["data"][$a]["brg_pengiriman_qty"],2,",",".")." Pcs";
                $response["content"][$a]["note_brg_pengiriman"] = $result["data"][$a]["brg_pengiriman_note"];
                $response["content"][$a]["status"] = $result["data"][$a]["brg_pemenuhan_status"];
                $response["content"][$a]["tgl_pengiriman"] = $result["data"][$a]["pengiriman_tgl"];
                $response["content"][$a]["daerah_cabang"] = $result["data"][$a]["cabang_daerah"];
                $response["content"][$a]["nama_toko"] = $result["data"][$a]["toko_nama"];
                $response["content"][$a]["kode_toko"] = $result["data"][$a]["toko_kode"];
                $response["content"][$a]["nama_brg"] = $result["data"][$a]["brg_nama"];
                $response["content"][$a]["kode_brg"] = $result["data"][$a]["brg_kode"];
                $response["content"][$a]["tgl_penerimaan"] = $result["data"][$a]["penerimaan_tgl"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nama_brg",
            "qty_brg_pengiriman_display",
            "nama_toko",
            "daerah_cabang",
            "status",
            "tgl_pengiriman",
            "tgl_penerimaan"
        );
        echo json_encode($response);
    }
    public function list_pengiriman_otw(){
        #dipake di graphic perjalanan pengiriman permintaan
        $response["status"] = "SUCCESS";
        $response["content"] = array();

        $id_tempat_pengiriman = $this->session->id_cabang;
        $this->load->model("m_t_penerimaan_permintaan");

        $result = $this->m_t_penerimaan_permintaan->content_pengiriman_otw($id_tempat_pengiriman);
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_penerimaan"];
                $response["content"][$a]["id_brg_pemenuhan"] = $result[$a]["id_pk_brg_pemenuhan"];
                $response["content"][$a]["id_brg_pengiriman"] = $result[$a]["id_pk_brg_pengiriman"];
                $response["content"][$a]["qty_brg_pengiriman"] = number_format($result[$a]["brg_pengiriman_qty"],2,",",".");
                $response["content"][$a]["note_brg_pengiriman"] = $result[$a]["brg_pengiriman_note"];
                $response["content"][$a]["status"] = $result[$a]["brg_pemenuhan_status"];
                $response["content"][$a]["tgl_pengiriman"] = $result[$a]["pengiriman_tgl"];
                $response["content"][$a]["daerah_cabang"] = $result[$a]["cabang_daerah"];
                $response["content"][$a]["nama_toko"] = $result[$a]["toko_nama"];
                $response["content"][$a]["kode_toko"] = $result[$a]["toko_kode"];
                $response["content"][$a]["nama_brg"] = $result[$a]["brg_nama"];
                $response["content"][$a]["kode_brg"] = $result[$a]["brg_kode"];
                $response["content"][$a]["tgl_penerimaan"] = $result[$a]["penerimaan_tgl"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $penerimaan_tgl = date("Y-m-d H:i:s");
        $penerimaan_status = "aktif";
        $penerimaan_tempat = $this->input->post("type");
        $penerimaan_tipe = $this->input->post("tipe_penerimaan");
        $id_tempat_penerimaan = $this->input->post("id_tempat_penerimaan");
        
        $this->load->model("m_penerimaan");
        if($this->m_penerimaan->set_insert($penerimaan_tgl,$penerimaan_status,$penerimaan_tipe,"",$penerimaan_tempat,$id_tempat_penerimaan,"")){
            $id_penerimaan = $this->m_penerimaan->insert();
            if($id_penerimaan){
                $this->load->model("m_brg_penerimaan");
                $brg_penerimaan_qty = $this->input->post("brg_penerimaan_qty");
                $brg_penerimaan_note = "-"; 
                $id_fk_penerimaan = $id_penerimaan;
                $id_fk_brg_pengiriman = $this->input->post("id");
                $id_fk_brg_pemenuhan = $this->input->post("id_brg_pemenuhan");
                
                $this->load->model("m_satuan");
                $result = $this->m_satuan->list();
                if($result->num_rows() > 0){
                    $result = $result->result_array();
                    $where = array(
                        "satuan_rumus" => "1"
                    );
                    $field = array(
                        "id_pk_satuan"
                    );
                    $result = selectRow("mstr_satuan",$where,$field);
                    $result = $result->result_array();
                    $id_fk_satuan = $result[0]["id_pk_satuan"];
                }
                else{
                    $satuan_nama = "Pcs";
                    $satuan_status = "aktif";
                    $satuan_rumus = "1";
                    $this->m_satuan->set_insert($satuan_nama,$satuan_status,$satuan_rumus);
                    $this->m_satuan->insert();
                }

                if($this->m_brg_penerimaan->set_insert($brg_penerimaan_qty,$brg_penerimaan_note,$id_fk_penerimaan,"",$id_fk_satuan,"",$id_fk_brg_pengiriman)){
                    if($this->m_brg_penerimaan->insert()){
                        $this->load->model("m_brg_pemenuhan");
                        $this->m_brg_pemenuhan->set_id_pk_brg_pemenuhan($id_fk_brg_pemenuhan);
                        $this->m_brg_pemenuhan->set_brg_pemenuhan_status("Diterima");
                        $this->m_brg_pemenuhan->update_status();
                    }
                    else{
                        $response["status"] = "ERROR";
                        $response["msg"] = "Insert item function error";
                    }
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Setter item error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Insert function error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Setter error";
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

                    $id_fk_brg_pemenuhan = $this->input->get("id_brg");
                    $this->load->model("m_brg_pemenuhan");
                    $this->m_brg_pemenuhan->set_id_pk_brg_pemenuhan($id_fk_brg_pemenuhan);
                    $this->m_brg_pemenuhan->set_brg_pemenuhan_status("Perjalanan");
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
        echo json_encode($response);
    }
}