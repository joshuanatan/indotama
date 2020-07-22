<?php
defined("BASEPATH") or exit("No direct script");
class Excel extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function barang(){
        $this->load->model("m_barang");
        $result = $this->m_barang->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_brg","brg_kode","brg_nama","brg_ket","brg_minimal","brg_status","brg_satuan","brg_merk_nama","brg_jenis_nama","brg_harga"
        );
        $data["title"] = "Daftar barang";
        $data["header"] = array(
            "ID","Kode Barang","Nama Barang","Keterangan","Jumlah Minimal","Status","Satuan","Nama merk","Nama Tipe","Harga Barang"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=barang.xls");
        echo $response["response"];
    }
    public function customer(){

        
        $this->load->model("m_customer");
        $result = $this->m_customer->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "cust_name",
            "cust_perusahaan",
            "cust_email",
            "cust_telp",
            "cust_hp",
            "cust_alamat",
            "cust_keterangan",
            "cust_status",
            "cust_last_modified",
            "cust_no_npwp",
            "cust_badan_usaha",
            "cust_no_rekening"
        );
        $data["title"] = "Daftar Customer";
        $data["header"] = array(
            "Name","Perusahaan","Email","Telp","HP","Alamat","Keterangan","Status","Last Modified","Nomor NPWP","Badan Usaha","No Rekening"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=customer.xls");
        echo $response["response"];
    }
    public function supplier(){

        $this->load->model("m_supplier");
        $result = $this->m_supplier->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "sup_nama",
            "sup_perusahaan",
            "sup_email",
            "sup_telp",
            "sup_hp",
            "sup_alamat",
            "sup_keterangan",
            "sup_status",
            "sup_last_modified"
        );
        $data["title"] = "Daftar Supplier";
        $data["header"] = array(
            "Nama",
            "Perusahaan",
            "Email",
            "Telp",
            "HP",
            "Alamat",
            "Keterangan",
            "Status",
            "Last Modified"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=supplier.xls");
        echo $response["response"];
    }
    public function employee(){

        $this->load->model("m_employee");
        $result = $this->m_employee->list_employee();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "emp_nama",
            "emp_npwp",
            "emp_ktp",
            "emp_hp",
            "emp_alamat",
            "emp_kode_pos",
            "emp_gaji",
            "emp_startdate",
            "emp_enddate",
            "emp_rek",
            "emp_gender",
            "emp_status"
        );
        $data["title"] = "Daftar Karyawan";
        $data["header"] = array(
            "Nama",
            "NPWP",
            "KTP",
            "HP",
            "Alamat",
            "Kode Pos",
            "Gaji",
            "Start Date",
            "End Date",
            "Rekening",
            "Jenis Kelamin",
            "Status"
        );

        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=employee.xls");
        echo $response["response"];
    }
    public function jenis_barang(){

        $this->load->model("m_barang_jenis");
        $result = $this->m_barang_jenis->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_brg_jenis",
            "brg_jenis_nama",
            "brg_jenis_status",
            "brg_jenis_last_modified"
        );
        $data["title"] = "Daftar Jenis/Tipe Barang";
        $data["header"] = array(
            "ID",
            "Nama Jenis / Nama Tipe",
            "Status",
            "Last Modified"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=jenis_barang.xls");
        echo $response["response"];
    }
    public function marketplace(){
    
        $this->load->model("m_marketplace");
        $result = $this->m_marketplace->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_marketplace",
            "marketplace_nama",
            "marketplace_ket",
            "marketplace_status",
            "marketplace_biaya",
            "marketplace_last_modified",
        );
        $data["title"] = "Daftar Marketplace";
        $data["header"] = array(
            "ID",
            "Marketplace",
            "Keterangan",
            "Status",
            "Biaya",
            "Last Modified",
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=marketplace.xls");
        echo $response["response"];
    }
    public function menu(){

        $this->load->model("m_menu");
        $result = $this->m_menu->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_menu",
            "menu_name",
            "menu_display",
            "menu_icon",
            "menu_category",
            "menu_status",
            "menu_last_modified"
        );
        $data["title"] = "Daftar Menu";
        $data["header"] = array(
            "ID",
            "Controller",
            "Menu",
            "Ikon",
            "Kategori",
            "Status",
            "Last Modified"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=menu.xls");
        echo $response["response"];
    }
    public function merk_barang(){

        $this->load->model("m_barang_merk");
        $result = $this->m_barang_merk->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_brg_merk",
            "brg_merk_nama",
            "brg_merk_status",
            "brg_merk_last_modified"
        );
        $data["title"] = "Daftar Merk";
        $data["header"] = array(
            "ID",
            "Nama Merk",
            "Status",
            "Last Modified"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=merk_barang.xls");
        echo $response["response"];
    }
    public function toko(){
        $this->load->model("m_toko");
        $result = $this->m_toko->list_toko();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_toko",
            "toko_nama",
            "toko_kode",
            "toko_status",
            "toko_last_modified",
        );
        $data["title"] = "Daftar Toko";
        $data["header"] = array(
            "ID",
            "Nama",
            "Kode",
            "Status",
            "Last Modified",
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=toko.xls");
        echo $response["response"];
    }
    public function warehouse(){
        $this->load->model("m_warehouse");
        $result = $this->m_warehouse->list_warehouse();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_warehouse",
            "warehouse_nama",
            "warehouse_alamat",
            "warehouse_notelp",
            "warehouse_desc",
            "warehouse_status",
            "warehouse_last_modified"
        );
        $data["title"] = "Daftar Toko";
        $data["header"] = array(
            "ID",
            "Nama",
            "Alamat",
            "No Telpon",
            "Deskripsi",
            "Status",
            "Last Modified"
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=warehouse.xls");
        echo $response["response"];
    }
    public function user(){
        $this->load->model("m_user");
        $result = $this->m_user->list();
        $data["data"] = $result->result_array();
        $data["access_key"] = array(
            "id_pk_user",
            "user_name",
            "user_email",
            "user_status",
            "user_last_modified",
            "jabatan_nama",
            "emp_nama"
        );
        $data["title"] = "Daftar User";
        $data["header"] = array(
            "ID",
            "Nama User",
            "Email",
            "Status",
            "Nama Role",
            "Nama Karyawan",
            "Last Modified",
        );
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=user.xls");
        echo $response["response"];
    }
    public function penjualan_cabang(){
        $this->load->model("m_penjualan");
        $this->m_penjualan->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_penjualan->list();
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Penjualan";

        $columns = $this->m_penjualan->columns();
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=penjualan_cabang.xls");
        echo $response["response"];
    }
    public function pembelian_cabang(){
        $this->load->model("m_pembelian");
        $this->m_pembelian->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_pembelian->list();
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Pembelian";

        $columns = $this->m_pembelian->columns();
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=pembelian_cabang.xls");
        echo $response["response"];
    }
    public function permintaan_cabang(){
        $this->load->model("m_brg_permintaan");
        $this->m_brg_permintaan->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_brg_permintaan->list_permintaan();
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Permintaan";

        $columns = $this->m_brg_permintaan->columns();
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=permintaan_cabang.xls");
        echo $response["response"];
    }
    public function retur_cabang(){
        $this->load->model("m_retur");
        $result = $this->m_retur->list_excel($this->session->id_cabang);
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Retur";

        $columns = $this->m_retur->columns();
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=retur_cabang.xls");
        echo $response["response"];
    }
    public function pengiriman_penjualan_cabang(){
        $this->load->model("m_pengiriman");
        $this->m_pengiriman->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_pengiriman->list_pengiriman_penjualan();
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Pengiriman Penjualan";

        $columns = $this->m_pengiriman->columns("penjualan");
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=pengiriman_cabang.xls");
        echo $response["response"];
    }

    public function pengiriman_retur_cabang(){
        $this->load->model("m_pengiriman");
        $this->m_pengiriman->set_id_fk_cabang($this->session->id_cabang);
        $result = $this->m_pengiriman->list_pengiriman_retur();
        
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Pengiriman Retur";

        $columns = $this->m_pengiriman->columns("retur");
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=pengiriman_cabang.xls");
        echo $response["response"];
    }
    public function pengiriman_permintaan_cabang(){
        $this->load->model("m_t_pengiriman_permintaan");
        $result = $this->m_t_pengiriman_permintaan->list_pengiriman_permintaan();
        $data["data"] = $result->result_array();
        $data["title"] = "Daftar Pengiriman Permintaan";

        $columns = $this->m_t_pengiriman_permintaan->columns();
        $access_key = array();
        $display = array();
        for($a = 0; $a < count($columns); $a++){
            $access_key[$a] = $columns[$a]["col_name"];
            $display[$a] = ucwords($columns[$a]["col_disp"]);
        }
        $data["access_key"] = $access_key;
        $data["header"] = $display;
        $response = $this->curl->post(base_url()."plugin/excel/generate",array(),$data);
        
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=pengiriman_cabang.xls");
        echo $response["response"];
    }
    public function generate(){
        $title = $this->input->post("title");
        $header = $this->input->post("header");
        $data = $this->input->post("data");
        $filename = $this->input->post("filename");
        $access_key = $this->input->post("access_key");
        $data = array(
            "filename" => $filename,
            "title" => $title,
            "header" => $header,
            "data" => $data,
            "access_key" => $access_key
        );
        $this->load->view("_plugin_template/excel/excel",$data);
    }
}