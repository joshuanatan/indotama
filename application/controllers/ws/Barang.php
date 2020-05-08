<?php
defined("BASEPATH") or exit("No direct script");
class Barang extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_barang");
        $columns = $this->m_barang->columns();
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
        
        $this->load->model("m_barang");
        $result = $this->m_barang->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_brg"];
                $response["content"][$a]["kode"] = $result["data"][$a]["brg_kode"];
                $response["content"][$a]["nama"] = $result["data"][$a]["brg_nama"];
                $response["content"][$a]["ket"] = $result["data"][$a]["brg_ket"];
                $response["content"][$a]["minimal"] = $result["data"][$a]["brg_minimal"];
                $response["content"][$a]["status"] = $result["data"][$a]["brg_status"];
                $response["content"][$a]["satuan"] = $result["data"][$a]["brg_satuan"];
                $response["content"][$a]["image"] = $result["data"][$a]["brg_image"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_last_modified"];
                $response["content"][$a]["merk"] = $result["data"][$a]["brg_merk_nama"];
                $response["content"][$a]["jenis"] = $result["data"][$a]["brg_jenis_nama"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "kode",
            "jenis",
            "nama",
            "ket",
            "merk",
            "minimal",
            "satuan",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("kode","kode","required");
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("keterangan","ket","required");
        $this->form_validation->set_rules("minimal","minimal","required");
        $this->form_validation->set_rules("satuan","satuan","required");
        $this->form_validation->set_rules("id_brg_jenis","id_brg_jenis","required");
        $this->form_validation->set_rules("id_brg_merk","id_brg_merk","required");
        
        if($this->form_validation->run()){
            $this->load->model("m_barang");
            $brg_kode = $this->input->post("kode");
            $brg_nama = $this->input->post("nama");
            $brg_ket = $this->input->post("keterangan");
            $brg_minimal = $this->input->post("minimal");
            $brg_satuan = $this->input->post("satuan");
            $brg_status = "AKTIF";
            
            $id_fk_brg_jenis = $this->input->post("id_brg_jenis");
            $this->load->model("m_barang_jenis");
            if($this->m_barang_jenis->set_brg_jenis_nama($id_fk_brg_jenis)){
                $result = $this->m_barang_jenis->detail_by_name();
                if($result->num_rows() > 0){
                    $result = $result->result_array();
                    $id_fk_brg_jenis = $result[0]["id_pk_brg_jenis"];
                }
                else{
                    $brg_jenis_nama = $id_fk_brg_jenis;
                    $brg_jenis_status = "AKTIF";
                    if($this->m_barang_jenis->set_insert($brg_jenis_nama,$brg_jenis_status)){
                        $id_insert = $this->m_barang_jenis->insert();
                        if($id_insert){
                            $id_fk_brg_jenis = $id_insert;
                        }
                    }
                }
            }
            $id_fk_brg_merk = $this->input->post("id_brg_merk");
            $this->load->model("m_barang_merk");
            if($this->m_barang_merk->set_brg_merk_nama($id_fk_brg_merk)){
                $result = $this->m_barang_merk->detail_by_name();
                if($result->num_rows() > 0){
                    $result = $result->result_array();
                    $id_fk_brg_merk = $result[0]["id_pk_brg_merk"];
                }
                else{
                    $brg_merk_nama = $id_fk_brg_merk;
                    $brg_merk_status = "AKTIF";
                    if($this->m_barang_merk->set_insert($brg_merk_nama,$brg_merk_status)){
                        $id_insert = $this->m_barang_merk->insert();
                        if($id_insert){
                            $id_fk_brg_merk = $id_insert;
                        }
                    }
                }
            }

            $config['upload_path'] = './asset/uploads/barang/';
            $config['allowed_types'] = 'gif|jpg|png';

            $this->load->library('upload', $config);
            $brg_image = "-";
            if($this->upload->do_upload('gambar')){
                $brg_image = $this->upload->data("file_name");
            }
            if($this->m_barang->set_insert($brg_kode,$brg_nama,$brg_ket,$brg_minimal,$brg_satuan,$brg_image,$brg_status,$id_fk_brg_jenis,$id_fk_brg_merk)){
                if($this->m_barang->insert()){
                    $response["msg"] = "Data is recorded to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Insert function is error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
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
        $this->form_validation->set_rules("kode","kode","required");
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("keterangan","ket","required");
        $this->form_validation->set_rules("minimal","minimal","required");
        $this->form_validation->set_rules("satuan","satuan","required");
        $this->form_validation->set_rules("id_brg_jenis","id_brg_jenis","required");
        $this->form_validation->set_rules("id_brg_merk","id_brg_merk","required");
        
        if($this->form_validation->run()){
            $this->load->model("m_barang");
            $id_pk_barang = $this->input->post("id");
            $brg_kode = $this->input->post("kode");
            $brg_nama = $this->input->post("nama");
            $brg_ket = $this->input->post("keterangan");
            $brg_minimal = $this->input->post("minimal");
            $brg_satuan = $this->input->post("satuan");
            
            $id_fk_brg_jenis = $this->input->post("id_brg_jenis");
            $this->load->model("m_barang_jenis");
            if($this->m_barang_jenis->set_brg_jenis_nama($id_fk_brg_jenis)){
                $result = $this->m_barang_jenis->detail_by_name();
                if($result->num_rows() > 0){
                    $result = $result->result_array();
                    $id_fk_brg_jenis = $result[0]["id_pk_brg_jenis"];
                }
                else{
                    $brg_jenis_nama = $id_fk_brg_jenis;
                    $brg_jenis_status = "AKTIF";
                    if($this->m_barang_jenis->set_insert($brg_jenis_nama,$brg_jenis_status)){
                        $id_insert = $this->m_barang_jenis->insert();
                        if($id_insert){
                            $id_fk_brg_jenis = $id_insert;
                        }
                    }
                }
            }
            $id_fk_brg_merk = $this->input->post("id_brg_merk");
            $this->load->model("m_barang_merk");
            if($this->m_barang_merk->set_brg_merk_nama($id_fk_brg_merk)){
                $result = $this->m_barang_merk->detail_by_name();
                if($result->num_rows() > 0){
                    $result = $result->result_array();
                    $id_fk_brg_merk = $result[0]["id_pk_brg_merk"];
                }
                else{
                    $brg_merk_nama = $id_fk_brg_merk;
                    $brg_merk_status = "AKTIF";
                    if($this->m_barang_merk->set_insert($brg_merk_nama,$brg_merk_status)){
                        $id_insert = $this->m_barang_merk->insert();
                        if($id_insert){
                            $id_fk_brg_merk = $id_insert;
                        }
                    }
                }
            }

            $config['upload_path'] = './asset/uploads/barang/';
            $config['allowed_types'] = 'gif|jpg|png';

            $this->load->library('upload', $config);
            $brg_image = $this->input->post("gambar_current");
            if($this->upload->do_upload('gambar')){
                $brg_image = $this->upload->data("file_name");
            }
            if($this->m_barang->set_update($id_pk_barang,$brg_kode,$brg_nama,$brg_ket,$brg_minimal,$brg_satuan,$brg_image,$id_fk_brg_jenis,$id_fk_brg_merk)){
                if($this->m_barang->update()){
                    $response["msg"] = "Data is updated to database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function is error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
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
        $id_pk_barang = $this->input->get("id");
        if($id_pk_barang != "" && is_numeric($id_pk_barang)){
            $this->load->model("m_barang");
            if($this->m_barang->set_delete($id_pk_barang)){
                if($this->m_barang->delete()){
                    $response["msg"] = "Data is deleted from database";
                }
                else{
                    $response["status"] = "ERROR";
                    $response["msg"] = "Update function is error";
                }
            }
            else{
                $response["status"] = "ERROR";
                $response["msg"] = "Setter function is error";
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "ID is invalid";
        }
        echo json_encode($response);
    }
}
?>