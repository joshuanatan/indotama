<?php
defined("BASEPATH") or exit("no direct script");
class Pembelian extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function register(){
        $response["status"] = "SUCCESS";
        $this->form_validation->set_rules("nomor","nomor","required");
        $this->form_validation->set_rules("tgl","tgl","required");
        $this->form_validation->set_rules("supplier","supplier","required");
        if($this->form_validation->run()){
            $this->load->model("m_pembelian");
            $pem_pk_nomor = $this->input->post("nomor");
            $pem_tgl = $this->input->post("tgl");
            $pem_status = "AKTIF";
            $sup_perusahaan = $this->input->post("supplier");
            $this->load->model("m_supplier");
            $this->m_supplier->set_sup_perusahaan($sup_perusahaan);
            $result = $this->m_supplier->detail_by_perusahaan();
            if($result->num_rows() > 0){
                $result = $result->result_array();
                $id_fk_supp = $result[0]["id_pk_sup"];
            }
            else{
                $this->load->model("m_supplier");
                $this->m_supplier->set_sup_perusahaan($sup_perusahaan);
                $id_fk_supp = $this->m_supplier->short_insert();
            }
            if($this->m_pembelian->set_insert($pem_pk_nomor,$pem_tgl,$pem_status,$id_fk_supp)){
                $id_pembelian = $this->m_pembelian->insert();
                
                if($id_pembelian){
                    $response["msg"] = "Data is recorded to database";
                    
                    $check = $this->input->post("check");
                    if($check != ""){
                        $counter = 0;
                        foreach($check as $a){
                            $this->form_validation->set_rules("brg".$a,"brg","required");
                            $this->form_validation->set_rules("brg_qty".$a,"brg_qty","required");
                            $this->form_validation->set_rules("brg_price".$a,"brg_price","required");
                            $this->form_validation->set_rules("brg_notes".$a,"brg_notes","required");
                            if($this->form_validation->run()){
                                $brg_qty = $this->input->post("brg_qty".$a);
                                $brg_qty = explode(" ",$brg_qty);
                                $brg_pem_qty = $brg_qty[0];
                                $brg_pem_satuan = $brg_qty[1];
                                $brg_pem_harga = $this->input->post("brg_price".$a);
                                $brg_pem_note = $this->input->post("brg_notes".$a);
                                $id_fk_pembelian = $id_pembelian;
                                $barang = $this->input->post("brg".$a);
                                $this->load->model("m_barang");
                                $this->m_barang->set_brg_nama($barang);
                                $result = $this->m_barang->detail_by_name();
                                if($result->num_rows() > 0){
                                    $result = $result->result_array();
                                    $id_fk_barang = $result[0]["id_pk_brg"];
                                }
                                else{
                                    $this->load->model("m_barang");
                                    $this->m_barang->set_brg_nama($barang);
                                    $id_fk_barang = $this->m_barang->short_insert();
                                }
                                $this->load->model("m_brg_pembelian");
                                if($this->m_brg_pembelian->set_insert($brg_pem_qty,$brg_pem_satuan,$brg_pem_harga,$brg_pem_note,$id_fk_pembelian,$id_fk_barang)){
                                    if($this->m_brg_pembelian->insert()){
                                        $response["itmsts"][$counter] = "SUCCESS";
                                        $response["itmmsg"][$counter] = "Data is recorded to database";
                                    }
                                    else{
                                        $response["status"] = "ERROR";
                                        $response["itmsts"][$counter] = "ERROR";
                                        $response["itmmsg"][$counter] = "Insert function error";
                                    }
                                }
                                else{
                                    $response["status"] = "ERROR";
                                    $response["itmsts"][$counter] = "ERROR";
                                    $response["itmmsg"][$counter] = "Setter function error";
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
                        $response["itmstsall"] = "ERROR";
                        $response["itmmsgall"] = "No Checks on Item";
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
                                $id_fk_pembelian = $id_pembelian;
                                
                                $this->load->model("m_tambahan_pembelian");
                                if($this->m_tambahan_pembelian->set_insert($tmbhn,$tmbhn_jumlah,$tmbhn_satuan,$tmbhn_harga,$tmbhn_notes,$tmbhn_status,$id_fk_pembelian)){
                                    if($this->m_tambahan_pembelian->insert()){
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
                        }
                    }
                    else{
                        $response["tmbhnsts"] = "ERROR";
                        $response["tmbhnmsg"] = "No Checks on Tambahan";
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
        $this->form_validation->set_rules("id_pembelian","id_pembelian","required");
        $this->form_validation->set_rules("nomor","nomor","required");
        $this->form_validation->set_rules("tgl","tgl","required");
        $this->form_validation->set_rules("tgl_bayar","tgl_bayar","required");
        $this->form_validation->set_rules("jenis_bayar","jenis_bayar","required");
        $this->form_validation->set_rules("status_bayar","status_bayar","required");
        $this->form_validation->set_rules("totalall","totalall","required");
        $this->form_validation->set_rules("supp_name","supp_name","required");
        $this->form_validation->set_rules("jmlh_item","jmlh_item","required");
        $this->form_validation->set_rules("id_supp","id_supp","required");
        $this->form_validation->set_rules("id_toko","id_toko","required");
        if($this->form_validation->run()){
            $this->load->model("m_pembelian");
            
            $id_pk_pembelian = $this->input->post("id_pembelian"); 
            $pem_pk_nomor = $this->input->post("nomor"); 
            $pem_tgl = $this->input->post("tgl"); 
            $pem_tgl_bayar = $this->input->post("tgl_bayar"); 
            $pem_jenis_bayar = $this->input->post("jenis_bayar"); 
            $pem_status_bayar = $this->input->post("status_bayar"); 
            $pem_totalall = $this->input->post("totalall"); 
            $pem_supp_name = $this->input->post("supp_name"); 
            $pem_jmlh_item = $this->input->post("jmlh_item"); 
            $id_fk_supp = $this->input->post("id_supp"); 
            $id_fk_toko = $this->input->post("id_toko"); 

            if($this->m_pembelian->set_update($id_pk_pembelian,$pem_pk_nomor,$pem_tgl,$pem_tgl_bayar,$pem_jenis_bayar,$pem_status_bayar,$pem_totalall,$pem_supp_name,$pem_jmlh_item,$id_fk_supp,$id_fk_toko)){
                if($this->m_pembelian->update()){
                    
                }
            }
        }
        else{

        }
    }
}