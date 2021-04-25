<?php
defined("BASEPATH") or exit("No direct script");
class Cabang extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function columns(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_cabang");
        $columns = $this->m_cabang->columns();
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
        $id_toko = $this->input->get("id_toko");
        $this->load->model("m_cabang");
        $this->m_cabang->set_id_fk_toko($id_toko);
        $result = $this->m_cabang->content($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_cabang"];
                $response["content"][$a]["nama"] = $result["data"][$a]["cabang_nama"];
                $response["content"][$a]["kode"] = $result["data"][$a]["cabang_kode"];
                $response["content"][$a]["daerah"] = $result["data"][$a]["cabang_daerah"];
                $response["content"][$a]["notelp"] = $result["data"][$a]["cabang_notelp"];
                $response["content"][$a]["alamat"] = $result["data"][$a]["cabang_alamat"];
                $response["content"][$a]["status"] = $result["data"][$a]["cabang_status"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["cabang_create_date"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["cabang_last_modified"];
                $response["content"][$a]["kop_surat"] = $result["data"][$a]["cabang_kop_surat"];
                $response["content"][$a]["nonpkp"] = $result["data"][$a]["cabang_nonpkp"];
                $response["content"][$a]["pernyataan_rek"] = $result["data"][$a]["cabang_pernyataan_rek"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "nama",
            "kode",
            "daerah",
            "notelp",
            "alamat",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("id_toko","id_toko","required");
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("kode","kode","required");
        $this->form_validation->set_rules("daerah","daerah","required");
        $this->form_validation->set_rules("alamat","alamat","required");
        $this->form_validation->set_rules("notelp","notelp","required");
        if($this->form_validation->run()){
            $this->load->model("m_cabang");
            $id_fk_toko = $this->input->post("id_toko");
            $cabang_nama = $this->input->post("nama");
            $cabang_kode = $this->input->post("kode");
            $cabang_daerah = $this->input->post("daerah");
            $cabang_status = "AKTIF";
            $cabang_alamat = $this->input->post("alamat");
            $cabang_notelp = $this->input->post("notelp");
            
            $config['upload_path'] = './asset/uploads/cabang/kop_surat/';
            $config['allowed_types'] = '*';
            $this->load->library("upload",$config);
            $cabang_kop_surat = "noimage.jpg";
            if($this->upload->do_upload('kop_surat')){
                $cabang_kop_surat = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/cabang/nonpkp/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $cabang_nonpkp = "noimage.jpg";
            if($this->upload->do_upload('nonpkp')){
                $cabang_nonpkp = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/cabang/pernyataan_rek/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $cabang_pernyataan_rek = "noimage.jpg";
            if($this->upload->do_upload('pernyataan_rek')){
                $cabang_pernyataan_rek = $this->upload->data("file_name");
            }

            if($this->m_cabang->set_insert($cabang_nama,$cabang_kode,$cabang_daerah,$cabang_notelp,$cabang_status,$cabang_alamat,$id_fk_toko,$cabang_kop_surat,$cabang_nonpkp,$cabang_pernyataan_rek)){
                if($this->m_cabang->insert()){
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
        $this->form_validation->set_rules("nama","nama","required");
        $this->form_validation->set_rules("kode","kode","required");
        $this->form_validation->set_rules("daerah","daerah","required");
        $this->form_validation->set_rules("alamat","alamat","required");
        $this->form_validation->set_rules("notelp","notelp","required");
        if($this->form_validation->run()){
            $this->load->model("m_cabang");
            $id_pk_cabang = $this->input->post("id");
            $cabang_nama = $this->input->post("nama");
            $cabang_kode = $this->input->post("kode");
            $cabang_daerah = $this->input->post("daerah");
            $cabang_alamat = $this->input->post("alamat");
            $cabang_notelp = $this->input->post("notelp");
            
            $config['upload_path'] = './asset/uploads/cabang/kop_surat/';
            $config['allowed_types'] = '*';
            $this->load->library("upload",$config);
            $cabang_kop_surat = $this->input->post("kop_surat_current");
            if($this->upload->do_upload('kop_surat')){
                $cabang_kop_surat = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/cabang/nonpkp/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $cabang_nonpkp = $this->input->post("nonpkp_current");
            if($this->upload->do_upload('nonpkp')){
                $cabang_nonpkp = $this->upload->data("file_name");
            }

            $config['upload_path'] = './asset/uploads/cabang/pernyataan_rek/';
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            $cabang_pernyataan_rek = $this->input->post("pernyataan_rek_current");
            if($this->upload->do_upload('pernyataan_rek')){
                $cabang_pernyataan_rek = $this->upload->data("file_name");
            }

            if($this->m_cabang->set_update($cabang_nama,$cabang_kode,$id_pk_cabang,$cabang_daerah,$cabang_notelp,$cabang_alamat,$cabang_kop_surat,$cabang_nonpkp,$cabang_pernyataan_rek)){
                if($this->m_cabang->update()){
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
            $this->load->model("m_cabang");
            if($this->m_cabang->set_delete($id_pk_toko)){
                if($this->m_cabang->delete()){
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
    public function list_cabang_admin(){
        $response["status"] = "SUCCESS";
        $response["content"] = array();

        $order_by = $this->input->get("orderBy");
        $order_direction = $this->input->get("orderDirection");
        $page = $this->input->get("page");
        $search_key = $this->input->get("searchKey");
        $data_per_page = 20;
        
        $this->load->model("m_cabang_admin");
        $this->m_cabang_admin->set_id_fk_user($this->session->id_user);
        $result = $this->m_cabang_admin->list_cabang_admin($page,$order_by,$order_direction,$search_key,$data_per_page);

        if($result["data"]->num_rows() > 0){
            $result["data"] = $result["data"]->result_array();
            for($a = 0; $a<count($result["data"]); $a++){
                $response["content"][$a]["id"] = $result["data"][$a]["id_pk_cabang"];
                $response["content"][$a]["toko"] = $result["data"][$a]["toko_nama"];
                $response["content"][$a]["daerah"] = $result["data"][$a]["cabang_daerah"];
                $response["content"][$a]["notelp"] = $result["data"][$a]["cabang_notelp"];
                $response["content"][$a]["alamat"] = $result["data"][$a]["cabang_alamat"];
                $response["content"][$a]["status"] = $result["data"][$a]["cabang_status"];
                $response["content"][$a]["create_date"] = $result["data"][$a]["cabang_create_date"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["cabang_last_modified"];
            }
        }
        else{
            $response["status"] = "ERROR";
        }
        $response["page"] = $this->pagination->generate_pagination_rules($page,$result["total_data"],$data_per_page);
        $response["key"] = array(
            "toko",
            "daerah",
            "notelp",
            "alamat",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function columns_cabang_admin(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_cabang_admin");
        $this->m_cabang_admin->set_cabang_admin_columns();
        $columns = $this->m_cabang_admin->columns();
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
        $this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($this->session->id_cabang);
        $result = $this->m_cabang->detail_by_id();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            $response["content"][0]["id"] = $result[0]["id_pk_cabang"];
            $response["content"][0]["nama"] = $result[0]["cabang_nama"];
            $response["content"][0]["kode"] = $result[0]["cabang_kode"];
            $response["content"][0]["daerah"] = $result[0]["cabang_daerah"];
            $response["content"][0]["notelp"] = $result[0]["cabang_notelp"];
            $response["content"][0]["alamat"] = $result[0]["cabang_alamat"];
            $response["content"][0]["status"] = $result[0]["cabang_status"];
            $response["content"][0]["last_modified"] = $result[0]["cabang_last_modified"];

            if(!file_exists(FCPATH."asset/uploads/cabang/kop_surat/".$result[0]["cabang_kop_surat"])){
                $result[0]["cabang_kop_surat"] = "-";
            }
            $response["content"][0]["kop_surat"] = $result[0]["cabang_kop_surat"];
            if(!file_exists(FCPATH."asset/uploads/cabang/nonpkp/".$result[0]["cabang_nonpkp"])){
                $result[0]["cabang_nonpkp"] = "-";
            }
            $response["content"][0]["nonpkp"] = $result[0]["cabang_nonpkp"];
            if(!file_exists(FCPATH."asset/uploads/cabang/pernyataan_rek/".$result[0]["cabang_pernyataan_rek"])){
                $result[0]["cabang_pernyataan_rek"] = "-";
            }
            $response["content"][0]["pernyataan_rek"] = $result[0]["cabang_pernyataan_rek"];
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Data";
        }
        echo json_encode($response);
    }
    public function refresh_id_cabang(){
        #refresh session cabang
        #gabisa di taro di fungsi updatek karena fungsi update dipake di master cabang juga yang ga boleh tiba2 ke assign session cabang
        
        $response["status"] = "SUCCESS";
        $this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($this->session->id_cabang);
        $result = $this->m_cabang->detail_by_id();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            $this->session->id_cabang = $result[0]["id_pk_cabang"];
            $this->session->daerah_cabang = $result[0]["cabang_daerah"];
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "Invalid Active ID";
            $this->session->unset_userdata("id_cabang");
            $this->session->unset_userdata("daerah_cabang");
        }
        echo json_encode($response);
    }
    public function dashboard($id_cabang = 0){
        $this->load->model("m_dashboard_cabang");
        if(!$id_cabang){
            $id_cabang = $this->session->id_cabang;
        }
        $this->m_dashboard_cabang->set_id_cabang($id_cabang);
        $response["status"] = "SUCCESS";
        $response["content"] = array(
            array(
                "type" => "widget",
                "data" => $this->m_dashboard_cabang->jumlah_penjualan_bulan_ini(),
                "title" => "Jumlah Penjualan Bulan Ini"
            ),
            array(
                "type" => "widget",
                "data" => $this->m_dashboard_cabang->jumlah_penjualan_bulan_lalu(),
                "title" => "Jumlah Penjualan Bulan Lalu"
            ),
            array(
                "type" => "widget",
                "data" => $this->m_dashboard_cabang->jumlah_penjualan_tahun_ini(),
                "title" => "Jumlah Penjualan Tahun Ini"
            ),
            array(
                "type" => "widget",
                "data" => $this->m_dashboard_cabang->jumlah_penjualan_tahun_lalu(),
                "title" => "Jumlah Penjualan Tahun Lalu"
            ),
            array(
                "type" => "widget",
                "data" => $this->m_dashboard_cabang->jumlah_konfirmasi_retur(),
                "title" => "Jumlah Retur Dalam Konfirmasi"
            ),
            array(
                "type" => "widget",
                "data" => $this->m_dashboard_cabang->jumlah_item_urgen_restok(),
                "title" => "Jumlah Item Butuh Restok"
            ),
            array(
                "type" => "table",
                "title" => "Daftar Item Butuh Restok",
                "header" => array(
                    "Nama Barang","Stok","Jumlah Minimal"
                ),
                "data" =>$this->m_dashboard_cabang->list_penjualan_belum_selesai(),
            ),
            array(
                "type" => "table",
                "title" => "Daftar Item Butuh Restok",
                "header" => array(
                    "Barang Awal","Barang Rubah","Jumlah Pindah","User","Tanggal Rubah"
                ),
                "data" =>$this->m_dashboard_cabang->list_barang_custom(),
            )
        );
        $result = $this->m_dashboard_cabang->list_penjualan_3_tahun_terakhir();
        $array = array(
            "type" => "pie",
            "title" => "Penjualan 3 Tahun Terakhir",
            "data" => array(
                "label" => $result["label"],
                "data" => $result["data"]
            )
        );
        array_push($response["content"],$array);

        $result = $this->m_dashboard_cabang->list_penjualan_3_tahun_terakhir();
        $array = array(
            "type" => "doughnut",
            "title" => "Penjualan 3 Tahun Terakhir",
            "data" => array(
                "label" => $result["label"],
                "data" => $result["data"]
            )
        );
        array_push($response["content"],$array);
        
        $result = $this->m_dashboard_cabang->list_penjualan_tahun_ini_perbulan();
        $array = array(
            "type" => "chart",
            "title" => "Penjualan Tahun Ini Setiap Bulan",
            "data" => array(
                array(
                    "label" => "Jumlah Penjualan",
                    "data" => $result["data"]
                )
            ),
            "xlabel" => $result["label"]
        );
        array_push($response["content"],$array);
        

        $result = $this->m_dashboard_cabang->list_penjualan_tahun_ini_perbulan();
        $result2 = $this->m_dashboard_cabang->list_penjualan_tahun_lalu_perbulan(1);
        $result3 = $this->m_dashboard_cabang->list_penjualan_tahun_lalu_perbulan(2);
        $array = array(
            "type" => "chart",
            "title" => "Penjualan 3 Tahun Terakhir Setiap Bulan",
            "data" => array(
                array(
                    "label" => "Jumlah Penjualan Tahun ".date("Y"),
                    "data" => $result["data"]
                ),
                array(
                    "label" => "Jumlah Penjualan Tahun ".((int)date("Y")-1),
                    "data" => $result2["data"]
                ),
                array(
                    "label" => "Jumlah Penjualan Tahun ".((int)date("Y")-2),
                    "data" => $result3["data"]
                )
            ),
            "xlabel" => $result["label"]
        );
        array_push($response["content"],$array);
        echo json_encode($response);
    }

    public function list_cabang(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_cabang");
        $result = $this->m_cabang->list_all_cabang();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_cabang"];
                $response["content"][$a]["name"] = $result[$a]["cabang_nama"];
                $response["content"][$a]["kode"] = $result[$a]["cabang_kode"];
                $response["content"][$a]["daerah"] = $result[$a]["cabang_daerah"];
                $response["content"][$a]["kopsurat"] = $result[$a]["cabang_kop_surat"];
                $response["content"][$a]["nonpkp"] = $result[$a]["cabang_nonpkp"];
                $response["content"][$a]["pernyataanrek"] = $result[$a]["cabang_pernyataan_rek"];
                $response["content"][$a]["telp"] = $result[$a]["cabang_notelp"];
                $response["content"][$a]["alamat"] = $result[$a]["cabang_alamat"];
                $response["content"][$a]["status"] = $result[$a]["cabang_status"];
                $response["content"][$a]["createdate"] = $result[$a]["cabang_create_date"];
                $response["content"][$a]["lastmodified"] = $result[$a]["cabang_last_modified"];
                $response["content"][$a]["idcreated"] = $result[$a]["id_create_data"];
                $response["content"][$a]["idlastmodified"] = $result[$a]["id_last_modified"];
                $response["content"][$a]["idtoko"] = $result[$a]["id_fk_toko"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Customer List";
        }
        echo json_encode($response);
    }
}