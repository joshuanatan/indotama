<?php
defined("BASEPATH") or exit("No direct script");
class Penawaran extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_penawaran");
        $columns = $this->m_penawaran->columns();
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
        $this->load->model("m_penawaran");
        $this->m_penawaran->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_penawaran->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_penawaran"];
                $response["content"][$a]["refrensi"] = $result["data"][$a]["penawaran_refrensi"];
                $response["content"][$a]["tgl"] = explode(" ",$result["data"][$a]["penawaran_tgl"])[0];
                $response["content"][$a]["subject"] = ucwords($result["data"][$a]["penawaran_subject"]);
                $response["content"][$a]["content"] = ucwords($result["data"][$a]["penawaran_content"]);
                $response["content"][$a]["notes"] = $result["data"][$a]["penawaran_notes"];
                $response["content"][$a]["file_html"] = "<a target = '_blank' class = 'btn btn-primary btn-sm col-lg-12' href = '".base_url()."asset/uploads/penawaran/".$result["data"][$a]["penawaran_file"]."'>".$result["data"][$a]["penawaran_file"]."</a>";
                $response["content"][$a]["file"] = $result["data"][$a]["penawaran_file"];
                $response["content"][$a]["status"] = $result["data"][$a]["penawaran_status"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["penawaran_last_modified"];
                
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "refrensi",
            "tgl",
            "subject",
            "content",
            "notes",
            "file_html",
            "status",
            "last_modified",
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("penawar","penawar","required");
        $this->form_validation->set_rules("subjek","subjek","required");
        $this->form_validation->set_rules("content","content","required");
        $this->form_validation->set_rules("notes","notes","required");
        if($this->form_validation->run()){
            $this->load->model("m_penawaran");
            $penawaran_refrensi = $this->input->post("penawar");
            $penawaran_subject = $this->input->post("subjek");
            $penawaran_content = $this->input->post("content");
            $penawaran_notes = $this->input->post("notes");
            $penawaran_tgl = $this->input->post("tgl");
            $penawaran_status = "AKTIF";
            $id_fk_cabang = $this->session->id_cabang;


            $config['upload_path'] = './asset/uploads/penawaran/';
            $config['allowed_types'] = 'gif|jpg|png';

            $this->load->library('upload', $config);
            $penawaran_file = "noimage.jpg";
            if($this->upload->do_upload('file')){
                $p1 = array("upload_data"=> $this->upload->data());
                $penawaran_file = $p1['upload_data']['file_name'];
            }

            if($this->m_penawaran->set_insert($penawaran_subject,$penawaran_content,$penawaran_notes,$penawaran_file,$penawaran_refrensi,$penawaran_tgl,$penawaran_status,$id_fk_cabang)){
                if($this->m_penawaran->insert()){
                    $response["msg"] = "Data is recorded to database";
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
        $this->form_validation->set_rules("penawar","penawar","required");
        $this->form_validation->set_rules("subjek","subjek","required");
        $this->form_validation->set_rules("content","content","required");
        $this->form_validation->set_rules("notes","notes","required");
        if($this->form_validation->run()){
            $this->load->model("m_penawaran");
            $id_pk_penawaran = $this->input->post("id");
            $penawaran_refrensi = $this->input->post("penawar");
            $penawaran_subject = $this->input->post("subjek");
            $penawaran_content = $this->input->post("content");
            $penawaran_notes = $this->input->post("notes");
            $penawaran_tgl = $this->input->post("tgl");
            
            $config['upload_path'] = './asset/uploads/penawaran/';
            $config['allowed_types'] = 'gif|jpg|png';
            $this->load->library('upload', $config);
            if($this->upload->do_upload('file')){
                $p1 = array("upload_data"=> $this->upload->data());
                $penawaran_file = $p1['upload_data']['file_name'];
            }
            else{
                $penawaran_file = $this->input->post("file_current");
            }
            if($this->m_penawaran->set_update($id_pk_penawaran,$penawaran_subject,$penawaran_content,$penawaran_notes,$penawaran_file,$penawaran_refrensi,$penawaran_tgl)){
                if($this->m_penawaran->update()){
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
        $id_pk_penawaran = $this->input->get("id");
        if($id_pk_penawaran != "" && is_numeric($id_pk_penawaran)){
            $this->load->model("m_penawaran");
            if($this->m_penawaran->set_delete($id_pk_penawaran)){
                if($this->m_penawaran->delete()){
                    $response["msg"] = "Data is removed to database";
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
            $response["msg"] = "Invalid ID";
        }
        echo json_encode($response);
    }
}
?>