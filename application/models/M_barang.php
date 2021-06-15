<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_barang extends ci_model
{
  private $tbl_name = "mstr_barang";
  private $columns = array();
  private $id_pk_brg;
  private $brg_kode;
  private $brg_nama;
  private $brg_ket;
  private $brg_minimal;
  private $brg_status;
  private $brg_satuan;
  private $brg_image;
  private $brg_harga;
  private $brg_harga_toko;
  private $brg_harga_grosir;
  private $brg_tipe; /*kombinasi / nonkombinasi*/
  private $brg_create_date;
  private $brg_last_modified;
  private $id_create_data;
  private $id_last_modified;
  private $id_fk_brg_jenis;
  private $id_fk_brg_merk;

  public function __construct()
  {
    parent::__construct();
    $this->set_column("brg_kode", "kode", true);
    $this->set_column("brg_nama", "nama", false);
    $this->set_column("brg_ket", "keterangan", false);
    $this->set_column("brg_merk_nama", "merk", false);
    $this->set_column("brg_minimal", "minimal", false);
    $this->set_column("brg_satuan", "satuan", false);
    $this->set_column("brg_harga", "harga satuan", false);
    $this->set_column("brg_harga_toko", "harga toko", false);
    $this->set_column("brg_harga_grosir", "harga grosir", false);
    $this->set_column("brg_status", "status", false);
    $this->set_column("brg_last_modified", "last modified", false);

    $this->brg_create_date = date("y-m-d h:i:s");
    $this->brg_last_modified = date("y-m-d h:i:s");
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
        drop table if exists mstr_barang;
        create table mstr_barang(
            id_pk_brg int primary key auto_increment,
            brg_kode varchar(50),
            brg_nama varchar(100),
            brg_ket varchar(200),
            brg_minimal double,
            brg_satuan varchar(30),
            brg_image varchar(100),
            brg_harga int,
            brg_tipe varchar(30),
            brg_status varchar(15),
            brg_create_date datetime,
            brg_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_fk_brg_jenis int,
            id_fk_brg_merk int
        );
        drop table if exists mstr_barang_log;
        create table mstr_barang_log(
            id_pk_brg_log int primary key auto_increment,
            executed_function varchar(20),
            id_pk_brg int,
            brg_kode varchar(50),
            brg_nama varchar(100),
            brg_ket varchar(200),
            brg_minimal double,
            brg_satuan varchar(30),
            brg_image varchar(100),
            brg_harga int,
            brg_tipe varchar(30),
            brg_status varchar(15),
            brg_create_date datetime,
            brg_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_fk_brg_jenis int,
            id_fk_brg_merk int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_barang;
        delimiter $$
        create trigger trg_after_insert_barang
        after insert on mstr_barang
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;
            
            set @log_text = concat(@id_user,' menambah data barang pada pukul ' , new.brg_last_modified,' nama barang terkait: ',new.brg_nama);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_log(executed_function,
            id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_tipe,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after insert',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_tipe,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_barang;
        delimiter $$
        create trigger trg_after_update_barang
        after update on mstr_barang
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;

            if new.brg_status = 'nonaktif'
            then
                set @log_text = concat(@id_user,' delete data barang at ',new.brg_last_modified ,' nama barang terkait: ',old.brg_nama);
                call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);

                insert into mstr_barang_log(executed_function,
                id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_tipe,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after delete',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_tipe,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
            else
                set @log_text = concat(@id_user,' update data barang at ',new.brg_last_modified ,' \nnama barang terkait: ',old.brg_nama,' nama barang baru: ',new.brg_nama);
                call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
                    
                insert into mstr_barang_log(executed_function,
                id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_tipe,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after update',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_tipe,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
            end if;
        end$$
        delimiter ;
        ";
    executeQuery($sql);
  }
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "")
  {
    $search_query = "";
    $order_by = $this->columns[$order_by]["col_name"];
    if ($search_key != "") {
      $search_query .= "and
            ( 
                brg_kode like '%" . $search_key . "%' or
                brg_nama like '%" . $search_key . "%' or
                brg_ket like '%" . $search_key . "%' or
                brg_minimal like '%" . $search_key . "%' or
                brg_status like '%" . $search_key . "%' or
                brg_satuan like '%" . $search_key . "%' or
                brg_image like '%" . $search_key . "%' or
                brg_harga like '%" . $search_key . "%' or
                brg_harga_toko like '%" . $search_key . "%' or
                brg_harga_grosir like '%" . $search_key . "%' or
                brg_merk_nama like '%" . $search_key . "%' or
                brg_jenis_nama like '%" . $search_key . "%' or
                brg_tipe like '%" . $search_key . "%' or
                brg_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_status,brg_satuan,brg_image,brg_last_modified,brg_merk_nama,brg_jenis_nama,brg_harga,brg_harga_toko,brg_harga_grosir,brg_tipe,count(id_pk_barang_kombinasi) as jumlah_barang_kombinasi
        from " . $this->tbl_name . " 
        left join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        left join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        left join tbl_barang_kombinasi as a on a.id_barang_utama = mstr_barang.id_pk_brg and a.barang_kombinasi_status = 'aktif'
        where brg_status = ? and (brg_jenis_status = ? or brg_jenis_status is null) and (brg_merk_status = ? or brg_merk_status is null) " . $search_query . "  
        group by id_pk_brg 
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif", "aktif", "aktif"
    );
    $result["data"] = executeQuery($query, $args);
    //echo $this->db->last_query();
    $query = "
        select id_pk_brg
        from " . $this->tbl_name . " 
        left join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        left join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        left join tbl_barang_kombinasi as a on a.id_barang_utama = mstr_barang.id_pk_brg and a.barang_kombinasi_status = 'aktif'
        where brg_status = ? and (brg_jenis_status = ? or brg_jenis_status is null) and (brg_merk_status = ? or brg_merk_status is null) " . $search_query . "   
        group by id_pk_brg 
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executeQuery($query, $args)->num_rows();
    return $result;
  }
  public function content_tab($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "", $id_jenis = "-")
  {
    $jenis_aktif = selectRow("mstr_barang_jenis", array("brg_jenis_status" => "aktif"))->result_array();
    if ($id_jenis == "-") {
      $id_jenis = $jenis_aktif[0]['id_pk_brg_jenis'];
    }
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            ( 
                brg_kode like '%" . $search_key . "%' or
                brg_nama like '%" . $search_key . "%' or
                brg_ket like '%" . $search_key . "%' or
                brg_minimal like '%" . $search_key . "%' or
                brg_status like '%" . $search_key . "%' or
                brg_satuan like '%" . $search_key . "%' or
                brg_image like '%" . $search_key . "%' or
                brg_harga like '%" . $search_key . "%' or
                brg_harga_toko like '%" . $search_key . "%' or
                brg_harga_grosir like '%" . $search_key . "%' or
                brg_merk_nama like '%" . $search_key . "%' or
                brg_jenis_nama like '%" . $search_key . "%' or
                brg_tipe like '%" . $search_key . "%' or
                brg_last_modified like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_brg,brg_kode,brg_nama,brg_harga,brg_harga_toko,brg_harga_grosir,brg_ket,brg_minimal,brg_status,brg_satuan,brg_image,brg_last_modified,brg_merk_nama,brg_jenis_nama,brg_harga,brg_tipe,count(id_pk_barang_kombinasi) as jumlah_barang_kombinasi
        from " . $this->tbl_name . " 
        left join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        left join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        left join tbl_barang_kombinasi as a on a.id_barang_utama = mstr_barang.id_pk_brg and a.barang_kombinasi_status = 'aktif'
        where id_fk_brg_jenis= " . $id_jenis . " and brg_status = 'aktif' and (brg_jenis_status = 'aktif' or brg_jenis_status is null) and (brg_merk_status = 'aktif' or brg_merk_status is null) " . $search_query . "  
        group by id_pk_brg 
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $result["data"] = executeQuery($query);
    #echo $this->db->last_query();
    $query = "
        select id_pk_brg
        from " . $this->tbl_name . " 
        left join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        left join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        left join tbl_barang_kombinasi as a on a.id_barang_utama = mstr_barang.id_pk_brg and a.barang_kombinasi_status = 'aktif'
        where id_fk_brg_jenis= " . $id_jenis . " and brg_status = 'aktif' and (brg_jenis_status = 'aktif' or brg_jenis_status is null) and (brg_merk_status = 'aktif' or brg_merk_status is null) " . $search_query . "   
        group by id_pk_brg 
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executeQuery($query)->num_rows();
    return $result;
  }
  public function list_data()
  {
    $sql = "select id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_status,brg_satuan,brg_image,brg_harga,brg_harga_toko,brg_harga_grosir,brg_last_modified,brg_merk_nama,brg_jenis_nama,brg_tipe
        from " . $this->tbl_name . " 
        inner join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        inner join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        where brg_status = ? and brg_jenis_status = ? and brg_merk_status = ?  
        group by id_pk_brg 
        order by brg_nama asc";
    $args = array(
      "aktif", "aktif", "aktif"
    );
    return executeQuery($sql, $args);
  }
  public function detail_by_name()
  {

    $sql = "select id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_status,brg_satuan,brg_image,brg_harga,brg_harga_toko,brg_harga_grosir,brg_last_modified,brg_merk_nama,brg_jenis_nama,brg_tipe
        from " . $this->tbl_name . " 
        inner join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        inner join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        where brg_status = ? and brg_jenis_status = ? and brg_merk_status = ? and brg_nama = ?
        group by id_pk_brg 
        order by brg_nama asc";
    $args = array(
      "aktif", "aktif", "aktif", $this->brg_nama
    );
    return executeQuery($sql, $args);
  }
  public function detail_by_id()
  {

    $sql = "select id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_status,brg_satuan,brg_image,brg_harga,brg_harga_toko,brg_harga_grosir,brg_last_modified,brg_merk_nama,brg_jenis_nama,brg_tipe
        from " . $this->tbl_name . " 
        inner join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        inner join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        where brg_status = ? and brg_jenis_status = ? and brg_merk_status = ? and id_pk_brg = ?
        group by id_pk_brg 
        order by brg_nama asc";
    $args = array(
      "aktif", "aktif", "aktif", $this->id_pk_brg
    );
    return executeQuery($sql, $args);
  }
  private function check_double_kode($id_pk_brg = 0)
  {
    $where = array(
      "brg_kode" => $this->brg_kode,
      "id_pk_brg != " => $id_pk_brg,
      "brg_status" => "aktif"
    );
    return isExistsInTable($this->tbl_name, $where);
  }
  private function check_double_nama($id_pk_brg = 0)
  {
    $where = array(
      "brg_nama" => $this->brg_nama,
      "id_pk_brg != " => $id_pk_brg,
      "brg_status" => "aktif"
    );
    return isExistsInTable($this->tbl_name, $where);
  }
  public function short_insert()
  {
    if ($this->check_double_nama()) {
      $data = array(
        "brg_nama" => $this->brg_nama,
        "brg_status" => "aktif",
        "brg_tipe" => "nonkombinasi",
        "brg_create_date" => $this->brg_create_date,
        "brg_last_modified" => $this->brg_last_modified,
        "id_create_data" => $this->id_create_data,
        "id_last_modified" => $this->id_last_modified
      );
      return insertRow($this->tbl_name, $data);
    }
    return false;
  }
  public function insert()
  {
    if ($this->check_insert()) {
      $data = array(
        "brg_kode" => $this->brg_kode,
        "brg_nama" => $this->brg_nama,
        "brg_ket" => $this->brg_ket,
        "brg_minimal" => $this->brg_minimal,
        "brg_status" => $this->brg_status,
        "brg_satuan" => $this->brg_satuan,
        "brg_image" => $this->brg_image,
        "brg_harga" => $this->brg_harga,
        "brg_harga_toko" => $this->brg_harga_toko,
        "brg_harga_grosir" => $this->brg_harga_grosir,
        "brg_tipe" => $this->brg_tipe,
        "id_fk_brg_jenis" => $this->id_fk_brg_jenis,
        "id_fk_brg_merk" => $this->id_fk_brg_merk,
        "brg_create_date" => $this->brg_create_date,
        "brg_last_modified" => $this->brg_last_modified,
        "id_create_data" => $this->id_create_data,
        "id_last_modified" => $this->id_last_modified
      );
      $id_hasil_insert = insertrow($this->tbl_name, $data);

      $log_all_msg = "Data Barang baru ditambahkan. Waktu penambahan: $this->brg_create_date";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

      $log_all_data_changes = "[ID Barang: $id_hasil_insert][Kode: $this->brg_kode][Nama: $this->brg_nama][Keterangan: $this->brg_ket][Minimal: $this->brg_minimal][Status: $this->brg_status][Satuan: $this->brg_satuan][File Gambar: $this->brg_image][Harga Satuan: $this->brg_harga][Harga Toko: $this->brg_harga_toko][Harga Grosir: $this->brg_harga_grosir][Tipe: $this->brg_tipe][ID Jenis Barang: $this->id_fk_brg_jenis][ID Merek Barang: $this->id_fk_brg_merk][Waktu Ditambahkan: $this->brg_create_date][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_create_data;
      $log_all_tgl = $this->brg_create_date;

      $data_log = array(
        "log_all_msg" => $log_all_msg,
        "log_all_data_changes" => $log_all_data_changes,
        "log_all_it" => $log_all_it,
        "log_all_user" => $log_all_user,
        "log_all_tgl" => $log_all_tgl
      );
      insertrow("log_all", $data_log);


      return $id_hasil_insert;
    } else {
      return false;
    }
  }
  public function update()
  {
    if ($this->check_update()) {
      $where = array(
        "id_pk_brg" => $this->id_pk_brg
      );
      $data = array(
        "brg_kode" => $this->brg_kode,
        "brg_nama" => $this->brg_nama,
        "brg_ket" => $this->brg_ket,
        "brg_minimal" => $this->brg_minimal,
        "brg_satuan" => $this->brg_satuan,
        "brg_image" => $this->brg_image,
        "brg_harga" => $this->brg_harga,
        "brg_harga_toko" => $this->brg_harga_toko,
        "brg_harga_grosir" => $this->brg_harga_grosir,
        "brg_tipe" => $this->brg_tipe,
        "id_fk_brg_jenis" => $this->id_fk_brg_jenis,
        "id_fk_brg_merk" => $this->id_fk_brg_merk,
        "brg_last_modified" => $this->brg_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updateRow($this->tbl_name, $data, $where);
        $id_pk = $this->id_pk_brg_merk;
        $log_all_msg = "Data Merk Barang dengan ID: $id_pk diubah. Waktu diubah: $this->brg_merk_last_modified . Data berubah menjadi: ";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

        $log_all_data_changes = "[ID Barang: $id_pk][Kode: $this->brg_kode][Nama: $this->brg_nama][Keterangan: $this->brg_ket][Minimal: $this->brg_minimal][Status: $this->brg_status][Satuan: $this->brg_satuan][File Gambar: $this->brg_image][Harga Satuan: $this->brg_harga][Harga Toko: $this->brg_harga_toko][Harga Grosir: $this->brg_harga_grosir][Tipe: $this->brg_tipe][ID Jenis Barang: $this->id_fk_brg_jenis][ID Merek Barang: $this->id_fk_brg_merk][Waktu Diubah: $this->brg_last_modified][Oleh: $nama_user]";
        $log_all_it = "";
        $log_all_user = $this->id_last_modified;
        $log_all_tgl = $this->brg_merk_last_modified;

        $data_log = array(
          "log_all_msg" => $log_all_msg,
          "log_all_data_changes" => $log_all_data_changes,
          "log_all_it" => $log_all_it,
          "log_all_user" => $log_all_user,
          "log_all_tgl" => $log_all_tgl
        );
        insertrow("log_all", $data_log);
      return true;
    } else {
      return false;
    }
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_brg" => $this->id_pk_brg
      );
      $data = array(
        "brg_status" => "nonaktif",
        "brg_last_modified" => $this->brg_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updateRow($this->tbl_name, $data, $where);
      return true;
    }
  }
  public function check_insert()
  {
    if ($this->check_double_kode()) {
      return false;
    }
    if ($this->check_double_nama()) {
      return false;
    }
    if ($this->brg_kode == "") {
      return false;
    }
    if ($this->brg_nama == "") {
      return false;
    }
    if ($this->brg_ket == "") {
      return false;
    }
    if ($this->brg_minimal == "") {
      return false;
    }
    if ($this->id_fk_brg_jenis == "") {
      return false;
    }
    if ($this->brg_status == "") {
      return false;
    }
    if ($this->brg_satuan == "") {
      return false;
    }
    if ($this->brg_image == "") {
      return false;
    }
    if ($this->brg_harga == "") {
      return false;
    }
    if ($this->brg_harga_toko == "") {
      return false;
    }
    if ($this->brg_harga_grosir == "") {
      return false;
    }
    if ($this->brg_tipe == "") {
      return false;
    }
    if ($this->id_fk_brg_merk == "") {
      return false;
    }
    if ($this->brg_create_date == "") {
      return false;
    }
    if ($this->brg_last_modified == "") {
      return false;
    }
    if ($this->id_create_data == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_update()
  {

    // if($this->check_double_kode($this->id_pk_brg)){
    //     return false;
    // }
    if ($this->check_double_nama($this->id_pk_brg)) {
      return false;
    }
    if ($this->id_pk_brg == "") {
      return false;
    }
    if ($this->brg_kode == "") {
      return false;
    }
    if ($this->brg_nama == "") {
      return false;
    }
    if ($this->brg_minimal == "") {
      return false;
    }
    if ($this->brg_satuan == "") {
      return false;
    }
    if ($this->brg_harga == "") {
      return false;
    }
    if ($this->brg_tipe == "") {
      return false;
    }
    if ($this->id_fk_brg_jenis == "") {
      return false;
    }
    if ($this->id_fk_brg_merk == "") {
      return false;
    }
    if ($this->brg_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {
    if ($this->id_pk_brg == "") {
      return false;
    }
    if ($this->brg_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($brg_kode, $brg_nama, $brg_ket, $brg_minimal, $brg_satuan, $brg_image, $brg_status, $id_fk_brg_jenis, $id_fk_brg_merk, $brg_harga, $brg_harga_toko, $brg_harga_grosir, $brg_tipe)
  {
    if (!$this->set_brg_kode($brg_kode)) {
      return false;
    }
    if (!$this->set_brg_nama($brg_nama)) {
      return false;
    }
    if (!$this->set_brg_ket($brg_ket)) {
      return false;
    }
    if (!$this->set_brg_minimal($brg_minimal)) {
      return false;
    }
    if (!$this->set_brg_satuan($brg_satuan)) {
      return false;
    }
    if (!$this->set_brg_image($brg_image)) {
      return false;
    }
    if (!$this->set_brg_harga($brg_harga)) {
      return false;
    }
    if (!$this->set_brg_harga_toko($brg_harga_toko)) {
      return false;
    }
    if (!$this->set_brg_harga_grosir($brg_harga_grosir)) {
      return false;
    }
    if (!$this->set_brg_tipe($brg_tipe)) {
      return false;
    }
    if (!$this->set_brg_status($brg_status)) {
      return false;
    }
    if (!$this->set_id_fk_brg_jenis($id_fk_brg_jenis)) {
      return false;
    }
    if (!$this->set_id_fk_brg_merk($id_fk_brg_merk)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_brg, $brg_kode, $brg_nama, $brg_ket, $brg_minimal, $brg_satuan, $brg_image, $id_fk_brg_jenis, $id_fk_brg_merk, $brg_harga, $brg_harga_toko, $brg_harga_grosir, $brg_tipe)
  {
    if (!$this->set_id_pk_brg($id_pk_brg)) {
      return false;
    }

    if (!$this->set_brg_kode($brg_kode)) {
      return false;
    }
    if (!$this->set_brg_nama($brg_nama)) {
      return false;
    }
    if (!$this->set_brg_ket($brg_ket)) {
      return false;
    }
    if (!$this->set_brg_minimal($brg_minimal)) {
      return false;
    }
    if (!$this->set_brg_satuan($brg_satuan)) {
      return false;
    }
    if (!$this->set_brg_image($brg_image)) {
      return false;
    }
    if (!$this->set_brg_harga($brg_harga)) {
      return false;
    }
    if (!$this->set_brg_harga_toko($brg_harga_toko)) {
      return false;
    }
    if (!$this->set_brg_harga_grosir($brg_harga_grosir)) {
      return false;
    }
    if (!$this->set_brg_tipe($brg_tipe)) {
      return false;
    }
    if (!$this->set_id_fk_brg_jenis($id_fk_brg_jenis)) {
      return false;
    }
    if (!$this->set_id_fk_brg_merk($id_fk_brg_merk)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_brg)
  {
    if (!$this->set_id_pk_brg($id_pk_brg)) {
      return false;
    }
    return true;
  }
  public function set_id_pk_brg($id_pk_brg)
  {
    if ($id_pk_brg != "") {
      $this->id_pk_brg = $id_pk_brg;
      return true;
    }
    return false;
  }
  public function set_brg_kode($brg_kode)
  {
    if ($brg_kode != "") {
      $this->brg_kode = $brg_kode;
      return true;
    }
    return false;
  }
  public function set_brg_nama($brg_nama)
  {
    if ($brg_nama != "") {
      $this->brg_nama = $brg_nama;
      return true;
    }
    return false;
  }
  public function set_brg_ket($brg_ket)
  {
    if (true) {
      $this->brg_ket = $brg_ket;
      return true;
    }
    return false;
  }
  public function set_brg_minimal($brg_minimal)
  {
    if ($brg_minimal !== "") {
      $this->brg_minimal = $brg_minimal;
      return true;
    }
    return false;
  }
  public function set_brg_satuan($brg_satuan)
  {
    if ($brg_satuan != "") {
      $this->brg_satuan = $brg_satuan;
      return true;
    }
    return false;
  }
  public function set_brg_image($brg_image)
  {
    if (true) {
      $this->brg_image = $brg_image;
      return true;
    }
    return false;
  }
  public function set_brg_harga($brg_harga)
  {
    if ($brg_harga != "") {
      $this->brg_harga = $brg_harga;
      return true;
    }
    return false;
  }
  public function set_brg_harga_toko($brg_harga_toko)
  {
    if ($brg_harga_toko != "") {
      $this->brg_harga_toko = $brg_harga_toko;
      return true;
    }
    return false;
  }
  public function set_brg_harga_grosir($brg_harga_grosir)
  {
    if ($brg_harga_grosir != "") {
      $this->brg_harga_grosir = $brg_harga_grosir;
      return true;
    }
    return false;
  }
  public function set_brg_tipe($brg_tipe)
  {
    if ($brg_tipe != "") {
      $this->brg_tipe = $brg_tipe;
      return true;
    }
    return false;
  }
  public function set_brg_status($brg_status)
  {
    if ($brg_status != "") {
      $this->brg_status = $brg_status;
      return true;
    }
    return false;
  }
  public function set_id_fk_brg_jenis($id_fk_brg_jenis)
  {
    if ($id_fk_brg_jenis != "") {
      $this->id_fk_brg_jenis = $id_fk_brg_jenis;
      return true;
    }
    return false;
  }
  public function set_id_fk_brg_merk($id_fk_brg_merk)
  {
    if ($id_fk_brg_merk != "") {
      $this->id_fk_brg_merk = $id_fk_brg_merk;
      return true;
    }
    return false;
  }
  public function data_excel()
  {
    $sql = "select id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_status,brg_satuan,brg_image,brg_harga,brg_harga_toko,brg_harga_grosir,brg_last_modified,brg_merk_nama,brg_jenis_nama,brg_tipe
        from " . $this->tbl_name . " 
        inner join mstr_barang_jenis on mstr_barang_jenis.id_pk_brg_jenis = " . $this->tbl_name . ".id_fk_brg_jenis
        inner join mstr_barang_merk on mstr_barang_merk.id_pk_brg_merk = " . $this->tbl_name . ".id_fk_brg_merk
        where brg_status = ? and brg_jenis_status = ? and brg_merk_status = ?  
        group by id_pk_brg 
        order by brg_nama asc";
    $args = array(
      "aktif", "aktif", "aktif"
    );
    return executeQuery($sql, $args);
  }
  public function columns_excel()
  {
    $this->columns = array();
    $this->set_column("brg_kode", "kode barang", true);
    $this->set_column("brg_jenis_nama", "tipe barang", false);
    $this->set_column("brg_nama", "nama barang", false);
    $this->set_column("brg_ket", "keterangan", false);
    $this->set_column("brg_merk_nama", "merk barang", false);
    $this->set_column("brg_minimal", "jumlah minimal", false);
    $this->set_column("brg_satuan", "satuan", false);
    $this->set_column("brg_harga", "harga satuan", false);
    $this->set_column("brg_harga_toko", "harga toko", false);
    $this->set_column("brg_harga_grosir", "harga grosir", false);
    $this->set_column("brg_tipe", "Tunggal / Kombinasi", false);
    $this->set_column("brg_status", "status", false);
    $this->set_column("brg_last_modified", "last modified", false);
    return $this->columns;
  }
}
