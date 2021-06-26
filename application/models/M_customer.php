<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_customer extends ci_model
{
  private $tbl_name = "mstr_customer";
  private $columns = array();
  private $id_pk_cust;
  private $cust_name;

  private $cust_no_npwp;
  private $cust_foto_npwp;
  private $cust_foto_kartu_nama;
  private $cust_badan_usaha;
  private $cust_no_rekening;

  private $cust_suff;
  private $cust_sapaan;
  private $cust_perusahaan;
  private $cust_email;
  private $cust_telp;
  private $cust_hp;
  private $cust_alamat;
  private $id_fk_toko;
  private $cust_keterangan;
  private $cust_status;
  private $cust_create_date;
  private $cust_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->set_column("cust_name", "name", true);
    $this->set_column("cust_perusahaan", "perusahaan", false);
    $this->set_column("cust_email", "email", false);
    $this->set_column("cust_telp", "telp", false);
    $this->set_column("cust_hp", "hp", false);
    $this->set_column("cust_alamat", "alamat", false);
    $this->set_column("cust_keterangan", "keterangan", false);
    $this->set_column("cust_status", "status", false);
    $this->set_column("cust_last_modified", "last modified", false);
    $this->cust_create_date = date("y-m-d h:i:s");
    $this->cust_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  private function set_column($col_name, $col_disp, $order_by)
  {
    $array = array(
      "col_name" => $col_name,
      "col_disp" => $col_disp,
      "order_by" => $order_by
    );
    $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
  }
  public function columns()
  {
    return $this->columns;
  }
  public function install()
  {
    $sql = "
        drop table if exists mstr_customer;
        create table mstr_customer(
            id_pk_cust int primary key auto_increment,
            cust_name varchar(100),
            cust_no_npwp varchar(100),
            cust_foto_npwp varchar(100),
            cust_foto_kartu_nama varchar(100),
            cust_badan_usaha varchar(100),
            cust_no_rekening varchar(100),
            cust_suff varchar(10),
            cust_perusahaan varchar(100),
            cust_email varchar(100),
            cust_telp varchar(30),
            cust_hp varchar(30),
            cust_alamat varchar(150),
            cust_keterangan varchar(150),
            id_fk_toko int,
            cust_status varchar(15),
            cust_create_date datetime,
            cust_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists mstr_customer_log;
        create table mstr_customer_log(
            id_pk_cust_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_cust int,
            cust_name varchar(100),
            cust_no_npwp varchar(100),
            cust_foto_npwp varchar(100),
            cust_foto_kartu_nama varchar(100),
            cust_badan_usaha varchar(100),
            cust_no_rekening varchar(100),
            cust_suff varchar(10),
            cust_perusahaan varchar(100),
            cust_email varchar(100),
            cust_telp varchar(30),
            cust_hp varchar(30),
            cust_alamat varchar(150),
            cust_keterangan varchar(150),
            id_fk_toko int,
            cust_status varchar(15),
            cust_create_date datetime,
            cust_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_customer;
        delimiter $$
        create trigger trg_after_insert_customer
        after insert on mstr_customer
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_cust,new.cust_name,new.cust_no_npwp,new.cust_foto_npwp,new.cust_foto_kartu_nama,new.cust_badan_usaha,new.cust_no_rekening,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_customer;
        delimiter $$
        create trigger trg_after_update_customer
        after update on mstr_customer
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_cust,new.cust_name,new.cust_no_npwp,new.cust_foto_npwp,new.cust_foto_kartu_nama,new.cust_badan_usaha,new.cust_no_rekening,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;";
    executequery($sql);
  }
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "")
  {
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            ( 
                id_pk_cust like '%" . $search_key . "%' or
                cust_name like '%" . $search_key . "%' or
                cust_perusahaan like '%" . $search_key . "%' or
                cust_email like '%" . $search_key . "%' or
                cust_telp like '%" . $search_key . "%' or
                cust_hp like '%" . $search_key . "%' or
                cust_alamat like '%" . $search_key . "%' or
                cust_keterangan like '%" . $search_key . "%' or
                cust_status like '%" . $search_key . "%' or
                cust_no_npwp like '%" . $search_key . "%' or
                cust_foto_npwp like '%" . $search_key . "%' or
                cust_foto_kartu_nama like '%" . $search_key . "%' or
                cust_badan_usaha like '%" . $search_key . "%' or
                cust_no_rekening like '%" . $search_key . "%' or
                cust_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_cust,cust_name,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,id_fk_toko,cust_no_rekening,cust_last_modified,cust_status,toko_nama
        from " . $this->tbl_name . " join mstr_toko on mstr_toko.id_pk_toko = mstr_customer.id_fk_toko
        where cust_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif"
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_cust
        from " . $this->tbl_name . " join mstr_toko on mstr_toko.id_pk_toko = mstr_customer.id_fk_toko
        where cust_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function content_cust_toko($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "", $id_toko)
  {
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            ( 
                id_pk_cust like '%" . $search_key . "%' or
                cust_name like '%" . $search_key . "%' or
                cust_perusahaan like '%" . $search_key . "%' or
                cust_email like '%" . $search_key . "%' or
                cust_telp like '%" . $search_key . "%' or
                cust_hp like '%" . $search_key . "%' or
                cust_alamat like '%" . $search_key . "%' or
                cust_keterangan like '%" . $search_key . "%' or
                cust_status like '%" . $search_key . "%' or
                cust_no_npwp like '%" . $search_key . "%' or
                cust_foto_npwp like '%" . $search_key . "%' or
                cust_foto_kartu_nama like '%" . $search_key . "%' or
                cust_badan_usaha like '%" . $search_key . "%' or
                cust_no_rekening like '%" . $search_key . "%' or
                id_fk_toko like '%" . $search_key . "%' or
                cust_last_modified like '%" . $search_key . "%'
                
            )";
    }
    $query = "
        select id_pk_cust,cust_name,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_last_modified,cust_status,id_fk_toko,toko_nama,toko_kode
        from " . $this->tbl_name . " join mstr_toko on mstr_toko.id_pk_toko = mstr_customer.id_fk_toko
        where id_fk_toko = ? and cust_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      $id_toko, "aktif"
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_cust
        from " . $this->tbl_name . " join mstr_toko on mstr_toko.id_pk_toko = mstr_customer.id_fk_toko
        where id_fk_toko = ? and cust_status = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function list_data()
  {
    $where = array(
      "cust_status" => "aktif"
    );
    $field = array(
      "id_pk_cust",
      "cust_name",
      "cust_suff",
      "cust_perusahaan",
      "cust_email",
      "cust_telp",
      "cust_hp",
      "cust_alamat",
      "cust_keterangan",
      "cust_no_npwp",
      "cust_foto_npwp",
      "cust_foto_kartu_nama",
      "cust_badan_usaha",
      "cust_no_rekening",
      "cust_last_modified",
      "cust_status"
    );
    return selectRow($this->tbl_name, $where, $field);
  }
  public function detail_by_perusahaan()
  {
    $where = array(
      "cust_perusahaan" => $this->cust_perusahaan,
      "cust_status" => "aktif"
    );
    $field = array(
      "id_pk_cust",
      "cust_name",
      "cust_no_npwp",
      "cust_foto_npwp",
      "cust_foto_kartu_nama",
      "cust_badan_usaha",
      "cust_no_rekening",
      "cust_suff",
      "cust_perusahaan",
      "cust_email",
      "cust_telp",
      "cust_hp",
      "cust_alamat",
      "cust_keterangan",
      "cust_status",
      "cust_create_date",
      "cust_last_modified",
    );
    return selectRow($this->tbl_name, $where, $field);
  }
  public function short_insert()
  {
    $data = array(
      "cust_perusahaan" => $this->cust_perusahaan,
      "cust_status" => "aktif",
      "cust_create_date" => $this->cust_create_date,
      "cust_last_modified" => $this->cust_last_modified,
      "id_create_data" => $this->id_create_data,
      "id_last_modified" => $this->id_last_modified
    );
    return insertRow($this->tbl_name, $data);
  }
  public function insert($cust_name, $cust_suff, $cust_perusahaan, $cust_email, $cust_telp, $cust_hp, $cust_alamat, $cust_keterangan, $cust_status, $cust_no_npwp, $cust_foto_npwp, $cust_foto_kartu_nama, $cust_badan_usaha, $cust_no_rekening, $id_fk_toko)
  {
    $data = array(
      "cust_name" => $cust_name,
      "cust_no_npwp" => $cust_no_npwp,
      "cust_foto_npwp" => $cust_foto_npwp,
      "cust_foto_kartu_nama" => $cust_foto_kartu_nama,
      "cust_badan_usaha" => $cust_badan_usaha,
      "cust_no_rekening" => $cust_no_rekening,
      "cust_suff" => $cust_suff,
      "cust_perusahaan" => $cust_perusahaan,
      "cust_email" => $cust_email,
      "cust_telp" => $cust_telp,
      "cust_hp" => $cust_hp,
      "cust_alamat" => $cust_alamat,
      "id_fk_toko" => $id_fk_toko,
      "cust_keterangan" => $cust_keterangan,
      "cust_status" => $cust_status,
      "cust_create_date" => $this->cust_create_date,
      "cust_last_modified" => $this->cust_last_modified,
      "id_create_data" => $this->id_create_data,
      "id_last_modified" => $this->id_last_modified
    );

    $id_hasil_insert = insertrow($this->tbl_name, $data);

    $log_all_msg = "Data Customer baru ditambahkan. Waktu penambahan: $this->cust_create_date";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

    $log_all_data_changes = "[ID Customer: $id_hasil_insert][Nama: $this->cust_name][NPWP: $this->cust_no_npwp][Foto NPWP: $this->cust_foto_npwp][Kartu Nama: $this->cust_foto_kartu_nama][Badan Usaha: $this->cust_badan_usaha][No Rek: $this->cust_no_rekening][Panggilan: $this->cust_suff][Perusahaan: $this->cust_perusahaan][Email: $this->cust_email][Telepon: $this->cust_telp][No HP: $this->cust_hp][Alamat: $this->cust_alamat][ID Toko: $this->id_fk_toko][Keterangan: $this->cust_keterangan][Status: $this->cust_status][Waktu Ditambahkan: $this->cust_create_date][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_create_data;
    $log_all_tgl = $this->cust_create_date;

    $data_log = array(
      "log_all_msg" => $log_all_msg,
      "log_all_data_changes" => $log_all_data_changes,
      "log_all_it" => $log_all_it,
      "log_all_user" => $log_all_user,
      "log_all_tgl" => $log_all_tgl
    );
    insertrow("log_all", $data_log);

    return $id_hasil_insert;
  }
  public function update()
  {
    if ($this->check_update()) {
      $where = array(
        "id_pk_cust" => $this->id_pk_cust
      );
      $data = array(
        "cust_name" => $this->cust_name,
        "cust_no_npwp" => $this->cust_no_npwp,
        "cust_foto_npwp" => $this->cust_foto_npwp,
        "cust_foto_kartu_nama" => $this->cust_foto_kartu_nama,
        "cust_badan_usaha" => $this->cust_badan_usaha,
        "cust_no_rekening" => $this->cust_no_rekening,
        "cust_suff" => $this->cust_suff,
        "cust_perusahaan" => $this->cust_perusahaan,
        "cust_email" => $this->cust_email,
        "cust_telp" => $this->cust_telp,
        "cust_hp" => $this->cust_hp,
        "cust_alamat" => $this->cust_alamat,
        "id_fk_toko" => $this->id_fk_toko,
        "cust_keterangan" => $this->cust_keterangan,
        "cust_last_modified" => $this->cust_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updateRow($this->tbl_name, $data, $where);
      $id_pk = $this->id_pk_cust;
      $log_all_msg = "Data Customer dengan ID: $id_pk diubah. Waktu diubah: $this->cust_last_modified . Data berubah menjadi: ";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

      $log_all_data_changes = "[ID Customer: $id_pk][Nama: $this->cust_name][NPWP: $this->cust_no_npwp][Foto NPWP: $this->cust_foto_npwp][Kartu Nama: $this->cust_foto_kartu_nama][Badan Usaha: $this->cust_badan_usaha][No Rek: $this->cust_no_rekening][Panggilan: $this->cust_suff][Perusahaan: $this->cust_perusahaan][Email: $this->cust_email][Telepon: $this->cust_telp][No HP: $this->cust_hp][Alamat: $this->cust_alamat][ID Toko: $this->id_fk_toko][Keterangan: $this->cust_keterangan][Waktu Diedit: $this->cust_last_modified][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_last_modified;
      $log_all_tgl = $this->cust_last_modified;

      $data_log = array(
        "log_all_msg" => $log_all_msg,
        "log_all_data_changes" => $log_all_data_changes,
        "log_all_it" => $log_all_it,
        "log_all_user" => $log_all_user,
        "log_all_tgl" => $log_all_tgl
      );
      insertrow("log_all", $data_log);
      return true;
    }
    return false;
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_cust" => $this->id_pk_cust
      );
      $data = array(
        "cust_status" => "nonaktif",
        "cust_last_modified" => $this->cust_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updateRow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    return true;
  }
  public function check_update()
  {
    return true;
  }
  public function check_delete()
  {
    return true;
  }
  public function set_insert($cust_name, $cust_suff, $cust_perusahaan, $cust_email, $cust_telp, $cust_hp, $cust_alamat, $cust_keterangan, $cust_status, $cust_no_npwp, $cust_foto_npwp, $cust_foto_kartu_nama, $cust_badan_usaha, $cust_no_rekening, $id_fk_toko)
  {
    $this->set_cust_name($cust_name);
    $this->set_cust_no_npwp($cust_no_npwp);
    $this->set_cust_foto_npwp($cust_foto_npwp);
    $this->set_cust_foto_kartu_nama($cust_foto_kartu_nama);
    $this->set_cust_badan_usaha($cust_badan_usaha);
    $this->set_cust_no_rekening($cust_no_rekening);
    $this->set_cust_suff($cust_suff);
    $this->set_cust_perusahaan($cust_perusahaan);
    $this->set_cust_email($cust_email);
    $this->set_cust_telp($cust_telp);
    $this->set_cust_hp($cust_hp);
    $this->set_cust_alamat($cust_alamat);
    $this->set_id_fk_toko($id_fk_toko);
    $this->set_cust_keterangan($cust_keterangan);
    $this->set_cust_status($cust_status);
    return true;
  }
  public function set_update($id_pk_cust, $cust_name, $cust_suff, $cust_perusahaan, $cust_email, $cust_telp, $cust_hp, $cust_alamat, $cust_keterangan, $cust_no_npwp, $cust_foto_npwp, $cust_foto_kartu_nama, $cust_badan_usaha, $cust_no_rekening, $id_fk_toko)
  {
    $this->set_id_pk_cust($id_pk_cust);
    $this->set_cust_name($cust_name);
    $this->set_cust_no_npwp($cust_no_npwp);
    $this->set_cust_foto_npwp($cust_foto_npwp);
    $this->set_cust_foto_kartu_nama($cust_foto_kartu_nama);
    $this->set_cust_badan_usaha($cust_badan_usaha);
    $this->set_cust_no_rekening($cust_no_rekening);
    $this->set_cust_suff($cust_suff);
    $this->set_cust_perusahaan($cust_perusahaan);
    $this->set_cust_email($cust_email);
    $this->set_cust_telp($cust_telp);
    $this->set_cust_hp($cust_hp);
    $this->set_cust_alamat($cust_alamat);
    $this->set_id_fk_toko($id_fk_toko);
    $this->set_cust_keterangan($cust_keterangan);
    return true;
  }
  public function set_delete($id_pk_cust)
  {
    $this->set_id_pk_cust($id_pk_cust);
    return true;
  }
  public function set_id_pk_cust($id_pk_cust)
  {
    $this->id_pk_cust = $id_pk_cust;
    return true;
  }
  public function set_cust_name($cust_name)
  {
    $this->cust_name = $cust_name;
    return true;
  }
  public function set_cust_no_npwp($cust_no_npwp)
  {
    $this->cust_no_npwp = $cust_no_npwp;
    return true;
  }
  public function set_cust_foto_npwp($cust_foto_npwp)
  {
    $this->cust_foto_npwp = $cust_foto_npwp;
    return true;
  }
  public function set_cust_foto_kartu_nama($cust_foto_kartu_nama)
  {
    $this->cust_foto_kartu_nama = $cust_foto_kartu_nama;
    return true;
  }
  public function set_cust_badan_usaha($cust_badan_usaha)
  {
    $this->cust_badan_usaha = $cust_badan_usaha;
    return true;
  }
  public function set_cust_no_rekening($cust_no_rekening)
  {
    $this->cust_no_rekening = $cust_no_rekening;
    return true;
  }
  public function set_cust_suff($cust_suff)
  {
    $this->cust_suff = $cust_suff;
    return true;
  }
  public function set_cust_perusahaan($cust_perusahaan)
  {
    $this->cust_perusahaan = $cust_perusahaan;
    return true;
  }
  public function set_cust_email($cust_email)
  {
    $this->cust_email = $cust_email;
    return true;
  }
  public function set_cust_telp($cust_telp)
  {
    $this->cust_telp = $cust_telp;
    return true;
  }
  public function set_cust_hp($cust_hp)
  {
    $this->cust_hp = $cust_hp;
    return true;
  }
  public function set_cust_alamat($cust_alamat)
  {
    $this->cust_alamat = $cust_alamat;
    return true;
  }
  public function set_id_fk_toko($id_fk_toko)
  {
    $this->id_fk_toko = $id_fk_toko;
    return true;
  }
  public function set_cust_keterangan($cust_keterangan)
  {
    $this->cust_keterangan = $cust_keterangan;
    return true;
  }
  public function set_cust_status($cust_status)
  {
    $this->cust_status = $cust_status;
    return true;
  }
  public function data_excel()
  {
    $where = array(
      "cust_status" => "aktif"
    );
    $field = array(
      "id_pk_cust",
      "cust_name",
      "cust_suff",
      "cust_perusahaan",
      "cust_email",
      "cust_telp",
      "cust_hp",
      "cust_alamat",
      "cust_keterangan",
      "cust_no_npwp",
      "cust_foto_npwp",
      "cust_foto_kartu_nama",
      "cust_badan_usaha",
      "cust_no_rekening",
      "cust_last_modified",
      "cust_status"
    );

    return selectRow($this->tbl_name, $where, $field);
  }
  public function columns_excel()
  {
    $this->columns = array();
    $this->set_column("cust_name", "name", true);
    $this->set_column("cust_perusahaan", "perusahaan", false);
    $this->set_column("cust_email", "email", false);
    $this->set_column("cust_telp", "telp", false);
    $this->set_column("cust_hp", "hp", false);
    $this->set_column("cust_alamat", "alamat", false);
    $this->set_column("cust_keterangan", "keterangan", false);
    $this->set_column("cust_status", "status", false);
    $this->set_column("cust_last_modified", "last modified", false);
    return $this->columns;
  }
  public function list_data_cust_toko($id_toko)
  {
    $sql = "select id_pk_cust, cust_name, cust_suff, cust_perusahaan, cust_email, cust_telp, cust_hp, cust_alamat, cust_keterangan, cust_no_npwp, cust_foto_npwp, cust_foto_kartu_nama, cust_badan_usaha, cust_no_rekening, cust_last_modified, cust_status from mstr_customer where cust_status = 'aktif' and id_fk_toko = ?";
    $args = array(
      $id_toko
    );
    return executeQuery($sql, $args);
  }
  public function columns_detail_penjualan()
  {
    $columns[0] = $this->local_set_column("penj_nomor", "nomor penjualan", true);
    $columns[1] = $this->local_set_column("penj_nominal", "nominal penjualan", false);
    $columns[2] = $this->local_set_column("penj_tgl", "tanggal penjualan", false);
    $columns[3] = $this->local_set_column("cust_perusahaan", "customer", false);
    $columns[4] = $this->local_set_column("penj_jenis", "jenis penjualan", false);
    $columns[5] = $this->local_set_column("penj_status", "status", false);
    $columns[6] = $this->local_set_column("status_pembayaran", "status pembayaran", false);
    $columns[7] = $this->local_set_column("selisih_tanggal", "durasi jatuh tempo", false);
    return $columns;
  }
  public function detail_penjualan_table($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "", $id_fk_customer)
  {
    $columns = array(
      "penj_nomor",
      "penj_nominal",
      "penj_tgl",
      "cust_perusahaan",
      "penj_jenis",
      "penj_status",
      "status_pembayaran",
      "selisih_tanggal"
    );
    $order_by = $columns[$order_by];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
      ( 
          id_pk_penjualan like '%" . $search_key . "%' or
          penj_nomor like '%" . $search_key . "%' or
          penj_tgl like '%" . $search_key . "%' or
          penj_status like '%" . $search_key . "%' or
          penj_jenis like '%" . $search_key . "%' or
          penj_tipe_pembayaran like '%" . $search_key . "%' or
          selisih_tanggal like '%" . $search_key . "%' or
          list_jenis_pembayaran like '%" . $search_key . "%' or
          status_pembayaran like '%" . $search_key . "%' or
          penj_nominal like '%" . $search_key . "%'
      )";
    }
    $query = "
      select * from (
        select id_fk_cabang,cust_email,id_pk_penjualan,penj_nomor,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan, if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran, DATEDIFF(penj_dateline_tgl,now()) as selisih_tanggal, if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal,id_fk_customer
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan 
        inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.penjualan_pmbyrn_status != 'nonaktif'
        group by id_pk_penjualan
      ) as a 
      where id_fk_customer = ? " . $search_query . "  
      order by " . $order_by . " " . $order_direction . " 
      limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      $id_fk_customer
    );
    $result["data"] = executequery($query, $args);
    $query = "
    select * from (
      select id_fk_cabang,cust_email,id_pk_penjualan,penj_nomor,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan, if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran, DATEDIFF(penj_dateline_tgl,now()) as selisih_tanggal, if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal,id_fk_customer
      from mstr_penjualan
      inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
      inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan 
      inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.penjualan_pmbyrn_status != 'nonaktif'
      group by id_pk_penjualan
    ) as a 
    where id_fk_customer = ? " . $search_query;
    $result["total_data"] = executequery($query, $args)->num_rows();
    #echo $this->db->last_query();
    return $result;
  }
  public function columns_detail_brg_penjualan(){
    $columns[0] = $this->local_set_column("brg_nama", "Nama Barang", true);
    $columns[1] = $this->local_set_column("brg_penjualan_qty", "Jumlah Barang", false);
    $columns[2] = $this->local_set_column("brg_penjualan_harga", "Harga Jual", false);
    $columns[3] = $this->local_set_column("penj_nomor", "Nomor Penjualan", false);
    $columns[4] = $this->local_set_column("penj_tgl", "Tanggal Penjualan", false);
    $columns[5] = $this->local_set_column("penj_jenis", "Jenis Penjualan", false);
    return $columns;
  }
  public function detail_brg_penjualan_table($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "", $id_fk_customer)
  {
    $columns = array(
      "brg_nama",
      "brg_penjualan_qty",
      "brg_penjualan_harga",
      "penj_nomor",
      "penj_tgl",
      "penj_jenis"
    );
    $order_by = $columns[$order_by];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
      ( 
          brg_nama like '%" . $search_key . "%' or
          brg_penjualan_qty like '%" . $search_key . "%' or
          brg_penjualan_harga like '%" . $search_key . "%' or
          penj_nomor like '%" . $search_key . "%' or
          penj_tgl like '%" . $search_key . "%' or
          penj_jenis like '%" . $search_key . "%'
      )";
    }
    $query = "
      select brg_nama,brg_penjualan_qty,brg_penjualan_satuan, brg_penjualan_harga, penj_nomor, penj_tgl, penj_jenis, penj_status, brg_penjualan_status from tbl_brg_penjualan
      inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan
      inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang
      where penj_status = 'aktif' and brg_penjualan_status = 'aktif' and brg_penjualan_qty > 0 and id_fk_customer = ?" . $search_query . "  
      order by " . $order_by . " " . $order_direction . " 
      limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      $id_fk_customer
    );
    $result["data"] = executequery($query, $args);
    $query = "
      select brg_nama,brg_penjualan_qty,brg_penjualan_satuan, brg_penjualan_harga, penj_nomor, penj_tgl, penj_jenis, penj_status, brg_penjualan_status from tbl_brg_penjualan
      inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan
      inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_penjualan.id_fk_barang
      where penj_status = 'aktif' and brg_penjualan_status = 'aktif' and brg_penjualan_qty > 0 and id_fk_customer = ?" . $search_query;
    $result["total_data"] = executequery($query, $args)->num_rows();
    #echo $this->db->last_query();
    return $result;
  }
  private function local_set_column($col_name, $col_disp, $order_by)
  {
    $array = array(
      "col_name" => $col_name,
      "col_disp" => $col_disp,
      "order_by" => $order_by
    );
    return $array;
    $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
  }
}
