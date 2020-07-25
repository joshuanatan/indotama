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
                $response["content"][$a]["minimal"] = number_format($result["data"][$a]["brg_minimal"],0,",",".");
                $response["content"][$a]["status"] = $result["data"][$a]["brg_status"];
                $response["content"][$a]["satuan"] = $result["data"][$a]["brg_satuan"];
                $response["content"][$a]["image"] = $result["data"][$a]["brg_image"];
                $response["content"][$a]["last_modified"] = $result["data"][$a]["brg_last_modified"];
                $response["content"][$a]["merk"] = $result["data"][$a]["brg_merk_nama"];
                $response["content"][$a]["jenis"] = $result["data"][$a]["brg_jenis_nama"];
                $response["content"][$a]["harga"] = number_format($result["data"][$a]["brg_harga"],0,",",".");
                $response["content"][$a]["jumlah_barang_kombinasi"] = $result["data"][$a]["jumlah_barang_kombinasi"];
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
            "harga",
            "status",
            "last_modified"
        );
        echo json_encode($response);
    }
    public function list(){
        $response["status"] = "SUCCESS";
        $this->load->model("m_barang");
        $result = $this->m_barang->list();
        if($result->num_rows() > 0){
            $result = $result->result_array();
            for($a = 0; $a<count($result); $a++){
                $response["content"][$a]["id"] = $result[$a]["id_pk_brg"];
                $response["content"][$a]["kode"] = $result[$a]["brg_kode"];
                $response["content"][$a]["nama"] = $result[$a]["brg_nama"];
                $response["content"][$a]["ket"] = $result[$a]["brg_ket"];
                $response["content"][$a]["minimal"] = $result[$a]["brg_minimal"];
                $response["content"][$a]["status"] = $result[$a]["brg_status"];
                $response["content"][$a]["satuan"] = $result[$a]["brg_satuan"];
                $response["content"][$a]["image"] = $result[$a]["brg_image"];
                $response["content"][$a]["last_modified"] = $result[$a]["brg_last_modified"];
                $response["content"][$a]["merk_nama"] = $result[$a]["brg_merk_nama"];
                $response["content"][$a]["jenis_nama"] = $result[$a]["brg_jenis_nama"];
                $response["content"][$a]["harga"] = $result[$a]["brg_harga"];
            }
        }
        else{
            $response["status"] = "ERROR";
            $response["msg"] = "No Barang List";
        }
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
        $this->form_validation->set_rules("harga","harga","required");
        
        if($this->form_validation->run()){
            $this->load->model("m_barang");
            $brg_kode = $this->input->post("kode");
            $brg_nama = $this->input->post("nama");
            $brg_ket = $this->input->post("keterangan");
            $brg_minimal = $this->input->post("minimal");
            $brg_satuan = $this->input->post("satuan");
            $brg_harga = $this->input->post("harga");
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
            $config['overwrite'] = TRUE;
            $config['file_name'] = "barang_" . $brg_kode;

            $this->load->library('upload', $config);
            $brg_image = "noimage.jpg";
            if($this->upload->do_upload('gambar')){
                $p1 = array("upload_data"=> $this->upload->data());
                $brg_image = $p1['upload_data']['file_name'];
            }
            if($this->m_barang->set_insert($brg_kode,$brg_nama,$brg_ket,$brg_minimal,$brg_satuan,$brg_image,$brg_status,$id_fk_brg_jenis,$id_fk_brg_merk,$brg_harga)){
                $id_barang = $this->m_barang->insert();
                if($id_barang){
                    $response["msg"] = "Data is recorded to database";
                    $check = $this->input->post("check");
                    if($check){
                        $counter = 0;
                        foreach($check as $a){
                            $this->load->model("m_barang_kombinasi");
                            $id_barang_utama = $id_barang;

                            $barang_kombinasi = $this->input->post("barang".$a);
                            $this->load->model("m_barang");
                            $this->m_barang->set_brg_nama($barang_kombinasi);
                            $result = $this->m_barang->detail_by_name();
                            if($result->num_rows() > 0){
                                $result = $result->result_array();
                                $id_barang_kombinasi = $result[0]["id_pk_brg"];
                            }
                            else{
                                $this->load->model("m_barang");
                                $this->m_barang->set_brg_nama($barang_kombinasi);
                                $id_barang_kombinasi = $this->m_barang->short_insert();
                            }

                            $barang_kombinasi_qty = $this->input->post("qty".$a);
                            $barang_kombinasi_status = "aktif";
                            if($this->m_barang_kombinasi->set_insert($id_barang_utama,$id_barang_kombinasi,$barang_kombinasi_qty,$barang_kombinasi_status)){
                                if($this->m_barang_kombinasi->insert()){
                                    $response["kombinasimsg"][$counter] = "Data is recorded to database";
                                    $response["kombinasistatus"][$counter] = "SUCCESS";
                                }
                                else{
                                    $response["kombinasimsg"][$counter] = "Insert function error";
                                    $response["kombinasistatus"][$counter] = "ERROR";
                                }
                            }
                            else{
                                $response["kombinasimsg"][$counter] = "Setter function error";
                                $response["kombinasistatus"][$counter] = "ERROR";
                            }
                            $counter++;
                        }
                    }
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
        $this->form_validation->set_rules("harga","harga","required");
        
        if($this->form_validation->run()){
            $this->load->model("m_barang");
            $id_pk_barang = $this->input->post("id");
            $brg_kode = $this->input->post("kode");
            $brg_nama = $this->input->post("nama");
            $brg_ket = $this->input->post("keterangan");
            $brg_minimal = $this->input->post("minimal");
            $brg_satuan = $this->input->post("satuan");
            $brg_harga = $this->input->post("harga");
            
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
            if($this->m_barang->set_update($id_pk_barang,$brg_kode,$brg_nama,$brg_ket,$brg_minimal,$brg_satuan,$brg_image,$id_fk_brg_jenis,$id_fk_brg_merk,$brg_harga)){
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
            $kombinasi_edit = $this->input->post("edit");
            if($kombinasi_edit){
                $counter = 0;
                foreach($kombinasi_edit as $a){
                    $id_pk_barang_kombinasi = $this->input->post("id_barang_kombinasi".$a);

                    $barang_kombinasi = $this->input->post("barang_edit".$a);
                    $this->load->model("m_barang");
                    $this->m_barang->set_brg_nama($barang_kombinasi);
                    $result = $this->m_barang->detail_by_name();
                    if($result->num_rows() > 0){
                        $result = $result->result_array();
                        $id_barang_kombinasi = $result[0]["id_pk_brg"];
                    }
                    else{
                        $this->load->model("m_barang");
                        $this->m_barang->set_brg_nama($barang_kombinasi);
                        $id_barang_kombinasi = $this->m_barang->short_insert();
                    }

                    $barang_kombinasi_qty = $this->input->post("qty_edit".$a);

                    $this->load->model("m_barang_kombinasi");
                    if($this->m_barang_kombinasi->set_update($id_pk_barang_kombinasi,$id_barang_kombinasi,$barang_kombinasi_qty)){
                        if($this->m_barang_kombinasi->update()){
                            $response["kombinasieditmsg"][$counter] = "Data is updated to database";
                            $response["kombinasieditstatus"][$counter] = "SUCCESS";
                        }
                        else{
                            $response["kombinasieditmsg"][$counter] = "update function error";
                            $response["kombinasieditstatus"][$counter] = "ERROR";
                        }
                    }
                    else{
                        $response["kombinasieditmsg"][$counter] = "Setter function error";
                        $response["kombinasieditstatus"][$counter] = "ERROR";
                    }
                    $counter++;
                }
            }

            $check = $this->input->post("check");
            if($check){
                $counter = 0;
                foreach($check as $a){
                    $this->load->model("m_barang_kombinasi");
                    $id_barang_utama = $id_pk_barang;

                    $barang_kombinasi = $this->input->post("barang".$a);
                    $this->load->model("m_barang");
                    $this->m_barang->set_brg_nama($barang_kombinasi);
                    $result = $this->m_barang->detail_by_name();
                    if($result->num_rows() > 0){
                        $result = $result->result_array();
                        $id_barang_kombinasi = $result[0]["id_pk_brg"];
                    }
                    else{
                        $this->load->model("m_barang");
                        $this->m_barang->set_brg_nama($barang_kombinasi);
                        $id_barang_kombinasi = $this->m_barang->short_insert();
                    }

                    $barang_kombinasi_qty = $this->input->post("qty".$a);
                    $barang_kombinasi_status = "aktif";
                    if($this->m_barang_kombinasi->set_insert($id_barang_utama,$id_barang_kombinasi,$barang_kombinasi_qty,$barang_kombinasi_status)){
                        if($this->m_barang_kombinasi->insert()){
                            $response["kombinasimsg"][$counter] = "Data is recorded to database";
                            $response["kombinasistatus"][$counter] = "SUCCESS";
                        }
                        else{
                            $response["kombinasimsg"][$counter] = "Insert function error";
                            $response["kombinasistatus"][$counter] = "ERROR";
                        }
                    }
                    else{
                        $response["kombinasimsg"][$counter] = "Setter function error";
                        $response["kombinasistatus"][$counter] = "ERROR";
                    }
                    $counter++;
                }
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
    public function barang_kombinasi(){
        $id_barang = $this->input->get("id_barang");
        if($id_barang){
            $this->load->model("m_barang_kombinasi");
            $this->m_barang_kombinasi->set_id_barang_utama($id_barang);
            $result = $this->m_barang_kombinasi->list();
            if($result->num_rows() > 0){
                $response["status"] = "success";
                $result = $result->result_array();
                for($a = 0; $a<count($result); $a++){
                    $response["content"][$a]["id"] = $result[$a]["id_pk_barang_kombinasi"];
                    $response["content"][$a]["qty"] = $result[$a]["barang_kombinasi_qty"];
                    $response["content"][$a]["barang"] = $result[$a]["brg_nama"];
                }
            }
            else{
                $response["status"] = "error";
                $response["msg"] = "No Result";
            }
        }
        else{
            $response["status"] = "error";
            $response["msg"] = "Invalid ID";
        }
        echo json_encode($response);
    }
    public function remove_barang_kombinasi(){
        $id_brg_kombinasi = $this->input->get("id_brg_kombinasi");
        if($id_brg_kombinasi){
            $this->load->model("m_barang_kombinasi");
            $this->m_barang_kombinasi->set_id_pk_barang_kombinasi($id_brg_kombinasi);
            if($this->m_barang_kombinasi->delete()){
                $response["status"] = "success";
                $response["msg"] = "data is successfully removed from database";
            }
            else{
                $response["status"] = "error";
                $response["msg"] = "delete function error";
            }
        }
        else{
            $response["status"] = "error";
            $response["msg"] = "invalid id";
        }
        echo json_encode($response);
    }
}
?>