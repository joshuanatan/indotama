<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Toko extends CI_Controller {
	public function __construct(){
		parent::__construct();
        $this->check_session();
    }
    public function check_session(){
        if($this->session->id_user == ""){
            $this->session->set_flashdata("msg","Session expired, please login");
            redirect("login");
        }
    }
	public function index(){
		$this->load->view('toko/v_master_toko');
	}
	public function cabang($id_toko_page){
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko_page);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		$data["toko"] = $detail_toko;
		$data["id_toko_page"] = $id_toko_page;

		$this->load->view('cabang/v_master_toko_cabang',$data);
	}
	public function cabang_toko(){
		#fungsi ini dibuat serupa dengan fungsi cabang diatas namun aksesnya pake id_session_toko karena ini akses yang dari manajemen toko bukan dari master
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($this->session->id_toko);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		$data["toko"] = $detail_toko;
		$data["id_toko_page"] = $this->session->id_toko;

		$this->load->view('cabang/v_manajemen_toko_cabang',$data);
	}
	public function daftar_akses_toko(){
		$this->load->view('toko/v_list_toko_admin');
	}
	public function daftar_akses_cabang(){
		$this->load->view('cabang/v_list_cabang_admin');
	}
	public function admin($id_toko_page){
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko_page);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();

		$data["toko"] = $detail_toko;
		$data["id_toko_page"] = $id_toko_page;
		
		$this->load->view('toko_admin/v_master_toko_admin',$data);
	}
	public function admin_cabang($id_cabang_page){

		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang_page);
		$result = $this->m_cabang->detail_by_id();
		$detail_cabang = $result->result_array();
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($detail_cabang[0]["id_fk_toko"]);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		
		$data["toko"] = $detail_toko;
		$data["id_cabang"] = $id_cabang_page;
		$data["cabang"] = $detail_cabang;
		
		$this->load->view('cabang_admin/v_master_cabang_admin',$data);
	}
	public function admin_cabang_toko($id_cabang_page){
		#dibuat karena mau ngeload view yang berbeda yang backnya ke halaman lain. dipake di manajemen cabang di manajemen toko
		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang_page);
		$result = $this->m_cabang->detail_by_id();
		$detail_cabang = $result->result_array();
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($detail_cabang[0]["id_fk_toko"]);
		$result = $this->m_toko->detail_by_id();
		$detail_toko = $result->result_array();
		
		$data["toko"] = $detail_toko;
		$data["id_cabang"] = $id_cabang_page;
		$data["cabang"] = $detail_cabang;
		
		$this->load->view('cabang_admin/v_manajemen_cabang_admin',$data);
	}
	public function brg_cabang($id_cabang_page = ""){
		if($id_cabang_page == ""){
			$id_cabang_page = $this->session->id_cabang;
		}
		$data["id_cabang_page"] = $id_cabang_page;

		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang_page);
		$result = $this->m_cabang->detail_by_id();
		$data["cabang"] = $result->result_array();
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($data["cabang"][0]["id_fk_toko"]);
		$result = $this->m_toko->detail_by_id();
		$data["toko"] = $result->result_array();

		
		$this->load->view('brg_cabang/v_brg_cabang',$data);
	}
	public function brg_cabang_toko($id_cabang_page = ""){
		if($id_cabang_page == ""){
			$id_cabang_page = $this->session->id_cabang;
		}
		$data["id_cabang_page"] = $id_cabang_page;

		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang_page);
		$result = $this->m_cabang->detail_by_id();
		$data["cabang"] = $result->result_array();
		
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($data["cabang"][0]["id_fk_toko"]);
		$result = $this->m_toko->detail_by_id();
		$data["toko"] = $result->result_array();

		
		$this->load->view('brg_cabang/v_brg_cabang_toko',$data);
	}
	public function activate_toko_manajemen($id_toko){
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($id_toko);
		$result = $this->m_toko->detail_by_id();
		$result = $result->result_array();
		$this->session->id_toko = $result[0]["id_pk_toko"];
		$this->session->nama_toko = $result[0]["toko_nama"];
		redirect("toko/dashboard_toko");
	}
	public function activate_cabang_manajemen($id_cabang){
		$response["status"] = "SUCCESS";
		$this->load->model("m_cabang");
		$this->m_cabang->set_id_pk_cabang($id_cabang);
		$result = $this->m_cabang->detail_by_id();
		$result = $result->result_array();
		$this->session->id_cabang = $result[0]["id_pk_cabang"];
		$this->session->daerah_cabang = $result[0]["cabang_daerah"];
		$this->load->model("m_toko");
		$this->m_toko->set_id_pk_toko($result[0]["id_fk_toko"]);
		$result = $this->m_toko->detail_by_id();
		$result = $result->result_array();
		$this->session->nama_toko_cabang = $result[0]["toko_nama"];
		$response["content"]["nama_cabang"] = $this->session->daerah_cabang;
		$response["content"]["nama_toko"] = $this->session->nama_toko;
		echo json_encode($response);
	}
	public function pengaturan_cabang(){
		$this->load->view("cabang/v_pengaturan_cabang");
	}
	public function pengaturan_toko(){
		$this->load->view("toko/v_pengaturan_toko");	
	}
	public function dashboard_cabang(){
		$this->load->view("cabang/v_dashboard_cabang");
	}
	public function dashboard_cabang_toko($id_cabang){
		$data["id_cabang"] = $id_cabang;
		$this->load->view("cabang/v_dashboard_cabang_toko",$data);
	}
	public function dashboard_toko(){
		$this->load->view("toko/v_dashboard_toko");
	}
	public function katalog(){
		$this->load->view("brg_cabang/v_master_barang_katalog");
	}
}