<?php
defined("BASEPATH") or exit("no direct script");
class Pembelian extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function insert(){
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
            $pem_pk_nomor = $this->input->post("nomor");
            $pem_tgl = $this->input->post("tgl");
            $pem_tgl_bayar = $this->input->post("tgl_bayar");
            $pem_jenis_bayar = $this->input->post("jenis_bayar");
            $pem_status_bayar = $this->input->post("status_bayar");
            $pem_totalall = $this->input->post("totalall");
            $pem_supp_name = $this->input->post("supp_name");
            $pem_jmlh_item = $this->input->post("jmlh_item");
            $pem_status = "AKTIF";
            $id_fk_supp = $this->input->post("id_supp");
            $id_fk_toko = $this->input->post("id_toko");
            if($this->m_pembelian->set_insert($pem_pk_nomor,$pem_tgl,$pem_tgl_bayar,$pem_jenis_bayar,$pem_status_bayar,$pem_totalall,$pem_supp_name,$pem_jmlh_item,$pem_status,$id_fk_supp,$id_fk_toko)){
                $id_pembelian = $this->m_pembelian->insert();
                if($id_pembelian){
                    $response["msg"] = "Data is recorded to database";
                    $check = $this->input->post("check");
                    if($check != ""){
                        $counter = 0;
                        foreach($check as $a){
                            $this->form_validation->set_rules("brg_qty".$a,"brg_qty","required");
                            $this->form_validation->set_rules("brg_satuan".$a,"brg_satuan","required");
                            $this->form_validation->set_rules("brg_note".$a,"brg_note","required");
                            $this->form_validation->set_rules("id_fk_barang".$a,"id_fk_barang","required");
                            if($this->form_validation->run()){
                                $brg_pem_qty = $this->input->post("brg_qty".$a);
                                $brg_pem_satuan = $this->input->post("brg_satuan".$a);
                                $brg_pem_note = $this->input->post("brg_note".$a);
                                $id_fk_pembelian = $id_pembelian;
                                $id_fk_barang = $this->input->post("id_fk_barang".$a);                            
                                $this->load->model("m_brg_pembelian");
                                if($this->m_brg_pembelian->set_insert($brg_pem_qty,$brg_pem_satuan,$brg_pem_note,$id_fk_pembelian,$id_fk_barang)){
                                    if($this->m_brg_pembelian->insert()){
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
                                $response["itmmsg"][$counter] = validation_errors();
                            }
                            $counter++;
                        }
                    }
                    else{
                        $response["itmstsall"] = "ERROR";
                        $response["itmmsgall"] = "No Checks on Item";
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