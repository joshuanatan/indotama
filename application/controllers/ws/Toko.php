<?php
defined("BASEPATH") or exit("No direct script");
class Toko extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_toko");
        $columns = $this->m_toko->columns();
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
        
        $this->load->model("m_toko");
        $result = $this->m_toko->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_toko"];
                if(file_exists(FCPATH."asset/uploads/toko/logo/".$result["data"][$a]["toko_logo"])){
                    $response["content"][$a]["logo_file"] = $result["data"][$a]["toko_logo"];
                }
                else{
                    $response["content"][$a]["logo_file"] = "noimage.jpg";
                }
                $response["content"][$a]["logo"] = "<img src = '".base_url()."asset/uploads/toko/logo/".$response["content"][$a]["logo_file"]."' width = '80px'>";
                $response["content"][$a]["nama"] = $result["data"][$a]["toko_nama"];
                $response["content"][$a]["kode"] = $result["data"][$a]["toko_kode"];
                $response["content"][$a]["status"] = $result["data"][$a]["toko_status"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["toko_create_date"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["toko_last_modified"];
                $response["content"][$a]["kop_surat"] = $result["data"][$a]["toko_kop_surat"];
                $response["content"][$a]["nonpkp"] = $result["data"][$a]["toko_nonpkp"];
                $response["content"][$a]["pernyataan_rek"] = $result["data"][$a]["toko_pernyataan_rek"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "logo",
            "nama",
            "kode",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("nama","toko_nama","required");
        $this->form_validation->set_rules("kode","toko_kode","required");
        if($this->form_validation->run()){
            $this->load->model("m_toko");
            $toko_nama = $this->input->post("nama");
            $toko_kode = $this->input->post("kode");
            $toko_status = "AKTIF";
            
            $config['upload_path'] = './asset/uploads/toko/logo/';
            $config['allowed_types'] = 'gif|jpg|png';
            $this->load->library('upload', $config);
            $toko_logo = "noimage.jpg";
            if($this->upload->do_upload('logo')){
                $toko_logo = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/toko/kop_surat/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $toko_kop_surat = "noimage.jpg";
            if($this->upload->do_upload('kop_surat')){
                $toko_kop_surat = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/toko/nonpkp/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $toko_nonpkp = "noimage.jpg";
            if($this->upload->do_upload('nonpkp')){
                $toko_nonpkp = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/toko/pernyataan_rek/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $toko_pernyataan_rek = "noimage.jpg";
            if($this->upload->do_upload('pernyataan_rek')){
                $toko_pernyataan_rek = $this->upload->data("file_name");
            }

            if($this->m_toko->set_insert($toko_logo,$toko_nama,$toko_kode,$toko_status,$toko_kop_surat,$toko_nonpkp,$toko_pernyataan_rek)){
                if($this->m_toko->insert()){
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
    public function list_cabang(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_cabang");
        $this->m_cabang->set_id_fk_toko($this->session->id_toko);
        $result = $this->m_cabang->list_cabang();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_cabang"];
                $response["content"][$a]["daerah"] = $result[$a]["cabang_daerah"];
                $response["content"][$a]["notelp"] = $result[$a]["cabang_notelp"];
                $response["content"][$a]["alamat"] = $result[$a]["cabang_alamat"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Data";
        }
        echo json_encode($response);
    }
    public function update(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id","id","required");
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("kode","kode","required");
        if($this->form_validation->run()){
            $this->load->model("m_toko");
            $id_pk_toko = $this->input->post("id");
            $toko_nama = $this->input->post("nama");
            $toko_kode = $this->input->post("kode");

            $config['upload_path'] = './asset/uploads/toko/logo/';
            $config['allowed_types'] = 'gif|jpg|png';
            $this->load->library('upload', $config);
            $toko_logo = $this->input->post("logo_current");
            if($this->upload->do_upload('logo')){
                $toko_logo = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/toko/kop_surat/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $toko_kop_surat = $this->input->post("kop_surat_current");
            if($this->upload->do_upload('kop_surat')){
                $toko_kop_surat = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/toko/nonpkp/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $toko_nonpkp = $this->input->post("nonpkp_current");
            if($this->upload->do_upload('nonpkp')){
                $toko_nonpkp = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/toko/pernyataan_rek/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $toko_pernyataan_rek = $this->input->post("pernyataan_rek_current");
            if($this->upload->do_upload('pernyataan_rek')){
                $toko_pernyataan_rek = $this->upload->data("file_name");
            }

            if($this->m_toko->set_update($id_pk_toko,$toko_logo,$toko_nama,$toko_kode,$toko_kop_surat,$toko_nonpkp,$toko_pernyataan_rek)){
                if($this->m_toko->update()){
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
        $id_toko = $this->input->get("id");
        if($id_toko != "" && is_numeric($id_toko)){
            $id_pk_toko = $id_toko;
            $this->load->model("m_toko");
            if($this->m_toko->set_delete($id_pk_toko)){
                if($this->m_toko->delete()){
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
    public function list_toko_admin(){
        $response["status"] = "SUCCESS";
        $response["content"] = array();

        $order_by = $this->input->get("orderBy");
        $order_direction = $this->input->get("orderDirection");
        $page = $this->input->get("page");
        $search_key = $this->input->get("searchKey");
        $data_per_page = 20;
        
        $this->load->model("m_toko_admin");
        $this->m_toko_admin->set_id_fk_user($this->session->id_user);
        $result = $this->m_toko_admin->list_toko_admin($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_toko"];
                $response["content"][$a]["nama"] = $result["data"][$a]["toko_nama"];
                $response["content"][$a]["kode"] = $result["data"][$a]["toko_kode"];
                $response["content"][$a]["status"] = $result["data"][$a]["toko_status"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["toko_create_date"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["toko_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nama",
            "kode",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function columns_toko_admin(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_toko_admin");
        $this->m_toko_admin->set_toko_admin_columns();
        $columns = $this->m_toko_admin->columns();
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
    public function pengaturan(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($this->session->id_toko);
        $result = $this->m_toko->detail_by_id();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            $response["content"][0]["id"] = $result[0]["id_pk_toko"];
            $response["content"][0]["nama"] = $result[0]["toko_nama"];
            $response["content"][0]["kode"] = $result[0]["toko_kode"];
            
            if(!file_exists(FCPATH."asset/uploads/toko/logo/".$result[0]["toko_logo"])){
                $result[0]["toko_logo"] = "-";
            }
            $response["content"][0]["logo"] = $result[0]["toko_logo"];
            if(!file_exists(FCPATH."asset/uploads/toko/kop_surat/".$result[0]["toko_kop_surat"])){
                $result[0]["toko_kop_surat"] = "-";
            }
            $response["content"][0]["kop_surat"] = $result[0]["toko_kop_surat"];
            if(!file_exists(FCPATH."asset/uploads/toko/nonpkp/".$result[0]["toko_nonpkp"])){
                $result[0]["toko_nonpkp"] = "-";
            }
            $response["content"][0]["nonpkp"] = $result[0]["toko_nonpkp"];
            if(!file_exists(FCPATH."asset/uploads/toko/pernyataan_rek/".$result[0]["toko_pernyataan_rek"])){
                $result[0]["toko_pernyataan_rek"] = "-";
            }
            $response["content"][0]["pernyataan_rek"] = $result[0]["toko_pernyataan_rek"];
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Data";
        }
        echo json_encode($response);
    }
    public function refresh_id_toko(){
        #refresh session toko
        #gabisa di taro di fungsi updatek karena fungsi update dipake di master toko juga yang ga boleh tiba2 ke assign session toko
        
        $response["status"] = "SUCCESS";
        $this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($this->session->id_toko);
        $result = $this->m_toko->detail_by_id();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            $this->session->id_toko = $result[0]["id_pk_toko"];
            $this->session->nama_toko = $result[0]["toko_nama"];
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid Active ID";
            $this->session->unset_userdata("id_toko");
            $this->session->unset_userdata("nama_toko");
        }
        echo json_encode($response);
    }
    public function dashboard(){
        $this->load->model("m_dashboard_toko");
        $this->m_dashboard_toko->set_id_toko($this->session->id_toko);
        $response["status"] = "SUCCESS";
        $response["content"] = array();

        $this->load->model("m_cabang");
        $this->m_cabang->set_id_fk_toko($this->session->id_toko);
        $result = $this->m_cabang->list_cabang();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            $array = array();
            $xlabel = array();

            $array2 = array();
            $xlabel2 = array();
            for($a = 0; $a<count($result); $a++){
                $this->load->model("m_dashboard_cabang");
                $this->m_dashboard_cabang->set_id_cabang($result[$a]["id_pk_cabang"]);
                $daerah_cabang = $result[$a]["cabang_daerah"];

                $data = $this->m_dashboard_cabang->list_penjualan_3_tahun_terakhir();
                $data2 = $this->m_dashboard_cabang->list_penjualan_tahun_ini_perbulan();
                
                $xlabel = $data["label"];
                $array[$a]["data"] = $data["data"];
                $array[$a]["label"] = $daerah_cabang;

                $xlabel2 = $data2["label"];
                $array2[$a]["data"] = $data2["data"];
                $array2[$a]["label"] = $daerah_cabang;
            }
            $array = array(
                "type" => "chart",
                "title" => "Perbandingan Penjualan 3 Tahun Terakhir Antar Cabang",
                "data" => $array,
                "xlabel" => $xlabel
            );
            array_push($response["content"],$array);

            $array = array(
                "type" => "chart",
                "title" => "Perbandingan Penjualan Tahun Ini Setiap Bulan Antar Cabang",
                "data" => $array2,
                "xlabel" => $xlabel2
            );
            array_push($response["content"],$array);
        }
        
        echo json_encode($response);
    }
}