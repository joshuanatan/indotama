<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_penjualan extends ci_model
{
  private $tbl_name = "mstr_penjualan";
  private $columns = array();
  private $id_pk_penjualan;
  private $penj_nomor;
  private $penj_nominal;
  private $penj_nominal_byr;
  private $penj_tgl;
  private $penj_dateline_tgl;/*supaya tau pas pengiriman mana yang urgent*/
  private $penj_status;
  private $penj_jenis; /*online/offline*/
  private $penj_tipe_pembayaran; /*full/dp/trial/dkk*/
  private $id_fk_customer;
  private $id_fk_cabang;
  private $penj_create_date;
  private $penj_last_modified;
  private $id_create_data;
  private $id_last_modified;
  private $no_control;
  private $bln_control;
  private $thn_control;
  /*
    drop view if exists v_penjualan;
    create view v_penjualan as select cust_email,id_pk_penjualan,penj_nomor,penj_nominal,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan,user_name as user_last_modified,if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran
    from mstr_penjualan
    inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
    inner join mstr_user on mstr_user.id_pk_user = mstr_penjualan.id_last_modified
    inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.penjualan_pmbyrn_status != "nonaktif"
    group by id_pk_penjualan
  */
  public function __construct()
  {
    parent::__construct();
    $this->set_column("penj_nomor", "nomor penjualan", true);
    $this->set_column("penj_nominal", "nominal penjualan", false);
    $this->set_column("penj_tgl", "tanggal penjualan", false);
    $this->set_column("cust_perusahaan", "customer", false);
    $this->set_column("penj_jenis", "jenis penjualan", false);
    $this->set_column("penj_status", "status", false);
    $this->set_column("status_pembayaran", "status pembayaran", false);
    $this->set_column("selisih_tanggal", "durasi jatuh tempo", false);
    $this->penj_create_date = date("y-m-d h:i:s");
    $this->penj_last_modified = date("y-m-d h:i:s");
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
  public function list_data()
  {
    $query = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan,user_name as user_last_modified,penj_nominal,penj_nominal_byr
        from mstr_penjualan 
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join mstr_user on mstr_user.id_pk_user = mstr_penjualan.id_last_modified
        where penj_status != 'nonaktif' and id_fk_cabang = ?";
    $args = array(
      $this->id_fk_cabang
    );
    return executeQuery($query, $args);
  }
  public function detail_by_penj_nomor()
  {
    $sql = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_perusahaan,cust_name,cust_suff,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan ,penj_nominal,penj_nominal_byr
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        where penj_nomor = ? and penj_status != ?";
    $args = array(
      $this->penj_nomor, "nonaktif"
    );
    return executeQuery($sql, $args);
  }
  public function detail_by_id()
  {
    $query = "
      select * from (
        select id_fk_cabang,cust_email,id_pk_penjualan,penj_nomor,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan, if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran, DATEDIFF(penj_dateline_tgl,now()) as selisih_tanggal, if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal, penj_on_marketplace, penj_on_no_resi, penj_on_kurir
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.
        inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan
        penjualan_pmbyrn_status != 'nonaktif'
        group by id_pk_penjualan
      ) as a 
      where id_pk_penjualan = ? ";
    $args = array(
      $this->id_pk_penjualan
    );
    return executequery($query, $args);
  }
  public function detail_by_id_pk_penjualan()
  {
    $sql = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_perusahaan,cust_name,cust_suff,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,penj_nominal_byr,id_pk_penjualan_online,penj_on_marketplace,penj_on_no_resi,penj_on_kurir,penj_on_status,if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan 
        where id_pk_penjualan = ?";
    $args = array(
      $this->id_pk_penjualan
    );
    return executeQuery($sql, $args);
  }
  public function install()
  {
    $sql = "
        drop table if exists mstr_penjualan;
        create table mstr_penjualan(
            id_pk_penjualan int primary key auto_increment,
            penj_nomor varchar(100),
            penj_nominal bigint,
            penj_nominal_byr bigint,
            penj_tgl datetime,
            penj_dateline_tgl datetime,
            penj_jenis varchar(50),
            penj_tipe_pembayaran varchar(50),
            penj_status varchar(15),
            id_fk_customer int,
            id_fk_cabang int,
            penj_create_date datetime,
            penj_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            no_control int comment 'untuk tau udah nomor berapa untuk penomoran',
            bln_control int,
            thn_control int
        );
        drop table if exists mstr_penjualan_log;
        create table mstr_penjualan_log(
            id_pk_penjualan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_penjualan int,
            penj_nomor varchar(100),
            penj_nominal bigint,
            penj_nominal_byr bigint,
            penj_tgl datetime,
            penj_dateline_tgl datetime,
            penj_jenis varchar(50),
            penj_tipe_pembayaran varchar(50),
            penj_status varchar(15),
            id_fk_customer int,
            id_fk_cabang int,
            penj_create_date datetime,
            penj_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_penjualan;
        delimiter $$
        create trigger trg_after_insert_penjualan
        after insert on mstr_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_nominal,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan,new.penj_nomor,new.penj_nominal,new.penj_nominal_byr,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_penjualan;
        delimiter $$
        create trigger trg_after_update_penjualan
        after update on mstr_penjualan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_nominal,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan,new.penj_nomor,new.penj_nominal,new.penj_nominal_byr,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
    if ($this->penj_tipe_pembayaran == "" || strtolower($this->penj_tipe_pembayaran) == "all") {
      $query = "
        select * from (
          select id_fk_cabang,cust_email,id_pk_penjualan,penj_nomor,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan, if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran, DATEDIFF(penj_dateline_tgl,now()) as selisih_tanggal, if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal
          from mstr_penjualan
          inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
          inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan 
          inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.penjualan_pmbyrn_status != 'nonaktif'
          group by id_pk_penjualan
        ) as a 
        where id_fk_cabang = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
      $args = array(
        $this->id_fk_cabang
      );
      $result["data"] = executequery($query, $args);
      $query = "
      select * from (
        select id_fk_cabang,cust_email,id_pk_penjualan,penj_nomor,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan, if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran, DATEDIFF(penj_dateline_tgl,now()) as selisih_tanggal, if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan 
        inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.penjualan_pmbyrn_status != 'nonaktif'
        group by id_pk_penjualan
      ) as a 
      where id_fk_cabang = ? " . $search_query;
      $result["total_data"] = executequery($query, $args)->num_rows();
    } else {
      $query = "
      select * from (
        select id_fk_cabang,cust_email,id_pk_penjualan,penj_nomor,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan, if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran, DATEDIFF(penj_dateline_tgl,now()) as selisih_tanggal, if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan 
        inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.penjualan_pmbyrn_status != 'nonaktif'
        group by id_pk_penjualan
      ) as a 
      where id_fk_cabang = ? and list_jenis_pembayaran like '%" . $this->penj_tipe_pembayaran . "%'" . $search_query . "  
      order by " . $order_by . " " . $order_direction . " 
      limit 20 offset " . ($page - 1) * $data_per_page;
      $args = array(
        $this->id_fk_cabang,
      );
      $result["data"] = executequery($query, $args);

      $query = "
      select * from (
        select id_fk_cabang,cust_email,id_pk_penjualan,penj_nomor,penj_nominal_byr,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan, if(penj_tipe_pembayaran = 1, if(cast(penj_nominal*1.1 as unsigned) = penj_nominal_byr, 'Lunas',if(cast(penj_nominal*1.1 as unsigned) > penj_nominal_byr,'Belum Lunas','Lebih Bayar')),if(penj_nominal = penj_nominal_byr,'Lunas',if(penj_nominal > penj_nominal_byr,'Belum Lunas','Lebih Bayar'))) as status_pembayaran, group_concat(penjualan_pmbyrn_nama) as list_jenis_pembayaran, DATEDIFF(penj_dateline_tgl,now()) as selisih_tanggal, if(penj_tipe_pembayaran = 1, cast(penj_nominal*1.1 as unsigned),penj_nominal) as penj_nominal
        from mstr_penjualan
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join tbl_penjualan_online on tbl_penjualan_online.id_fk_penjualan = mstr_penjualan.id_pk_penjualan 
        inner join tbl_penjualan_pembayaran on tbl_penjualan_pembayaran.id_fk_penjualan = mstr_penjualan.id_pk_penjualan where tbl_penjualan_pembayaran.penjualan_pmbyrn_status != 'nonaktif'
        group by id_pk_penjualan
      ) as a 
      where id_fk_cabang = ? and list_jenis_pembayaran like '%" . $this->penj_tipe_pembayaran . "%'" . $search_query;
      $result["total_data"] = executequery($query, $args)->num_rows();
    }
    #echo $this->db->last_query();
    return $result;
  }
  public function insert($penj_nomor, $penj_tgl, $penj_dateline_tgl, $penj_jenis, $penj_tipe_pembayaran, $id_fk_customer, $id_fk_cabang, $penj_status)
  {
    $data = array(
      "penj_nomor" => $penj_nomor,
      "penj_tgl" => $penj_tgl,
      "penj_status" => $penj_status,
      "penj_dateline_tgl" => $penj_dateline_tgl,
      "penj_jenis" => $penj_jenis,
      "penj_tipe_pembayaran" => $penj_tipe_pembayaran,
      "id_fk_customer" => $id_fk_customer,
      "id_fk_cabang" => $id_fk_cabang,
      "penj_create_date" => date("Y-m-d H:i:s"),
      "penj_last_modified" => date("Y-m-d H:i:s"),
      "id_create_data" => $this->session->id_user,
      "id_last_modified" => $this->session->id_user,
      "no_control" => $this->no_control,
      "bln_control" => explode("-", $penj_tgl)[1],
      "thn_control" => explode("-", $penj_tgl)[0]
    );
    $id_hasil_insert = insertrow($this->tbl_name, $data);

    $log_all_msg = "Data Penjualan baru ditambahkan. Waktu penambahan: $this->emp_create_date";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

    $log_all_data_changes = "[ID Penjualan: $id_hasil_insert][Nomor Penjualan: $this->penj_nomor][Tanggal: $this->penj_tgl][Status: $this->penj_status][Dateline: $this->penj_dateline_tgl][Jenis Penjualan: $this->penj_jenis][Tipe Pembayaran: $this->penj_tipe_pembayaran][ID Customer: $this->id_fk_customer][ID Cabang: $this->id_fk_cabang][Waktu Ditambahkan: $this->penj_create_date][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_last_modified;
    $log_all_tgl = $this->emp_create_date;

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
  public function update($id_pk_penjualan, $penj_nomor, $penj_dateline_tgl, $penj_jenis, $penj_tipe_pembayaran, $penj_tgl, $id_fk_customer)
  {
    $where = array(
      "id_pk_penjualan" => $id_pk_penjualan
    );
    $data = array(
      "penj_nomor" => $penj_nomor,
      "penj_jenis" => $penj_jenis,
      "penj_nominal" => 0,
      "penj_nominal_byr" => 0,
      "penj_dateline_tgl" => $penj_dateline_tgl,
      "penj_tgl" => $penj_tgl,
      "penj_tipe_pembayaran" => $penj_tipe_pembayaran,
      "id_fk_customer" => $id_fk_customer,
      "penj_last_modified" => $this->penj_last_modified,
      "id_last_modified" => $this->id_last_modified
    );
    updaterow($this->tbl_name, $data, $where);
    return true;
  }
  public function update_status()
  {
    $where = array(
      "id_pk_penjualan" => $this->id_pk_penjualan
    );
    $data = array(
      "penj_status" => $this->penj_status,
      "penj_last_modified" => $this->penj_last_modified,
      "id_last_modified" => $this->id_last_modified
    );
    updaterow($this->tbl_name, $data, $where);
    return true;
  }
  public function update_nominal($penj_nominal)
  {
    $where = array(
      "id_pk_penjualan" => $this->id_pk_penjualan
    );
    $data = array(
      "penj_nominal" => $penj_nominal,
      "penj_last_modified" => $this->penj_last_modified,
      "id_last_modified" => $this->id_last_modified
    );
    updaterow($this->tbl_name, $data, $where);
    return true;
  }
  public function update_nominal_byr($penj_nominal_byr)
  {
    $where = array(
      "id_pk_penjualan" => $this->id_pk_penjualan
    );
    $data = array(
      "penj_nominal_byr" => $penj_nominal_byr,
      "penj_last_modified" => $this->penj_last_modified,
      "id_last_modified" => $this->id_last_modified
    );
    updaterow($this->tbl_name, $data, $where);
    return true;
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_penjualan" => $this->id_pk_penjualan
      );
      $data = array(
        "penj_status" => "nonaktif",
        "penj_last_modified" => $this->penj_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function check_insert()
  {
    if ($this->penj_nomor == "") {
      return false;
    }
    if ($this->penj_tgl == "") {
      return false;
    }
    if ($this->penj_dateline_tgl == "") {
      return false;
    }
    if ($this->penj_jenis == "") {
      return false;
    }
    if ($this->penj_tipe_pembayaran == "") {
      return false;
    }
    if ($this->penj_status == "") {
      return false;
    }
    if ($this->id_fk_customer == "") {
      return false;
    }
    if ($this->id_fk_cabang == "") {
      return false;
    }
    if ($this->penj_create_date == "") {
      return false;
    }
    if ($this->penj_last_modified == "") {
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
    if ($this->id_pk_penjualan == "") {
      return false;
    }
    if ($this->penj_nomor == "") {
      return false;
    }
    if ($this->penj_nomor == "") {
      return false;
    }
    if ($this->penj_dateline_tgl == "") {
      return false;
    }
    if ($this->penj_jenis == "") {
      return false;
    }
    if ($this->penj_tipe_pembayaran == "") {
      return false;
    }
    if ($this->penj_tgl == "") {
      return false;
    }
    if ($this->id_fk_customer == "") {
      return false;
    }
    if ($this->penj_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function check_delete()
  {
    if ($this->id_pk_penjualan == "") {
      return false;
    }
    if ($this->penj_last_modified == "") {
      return false;
    }
    if ($this->id_last_modified == "") {
      return false;
    }
    return true;
  }
  public function set_insert($penj_nomor, $penj_tgl, $penj_dateline_tgl, $penj_jenis, $penj_tipe_pembayaran, $id_fk_customer, $id_fk_cabang, $penj_status)
  {
    if (!$this->set_penj_nomor($penj_nomor)) {
      return false;
    }
    if (!$this->set_penj_dateline_tgl($penj_dateline_tgl)) {
      return false;
    }
    if (!$this->set_penj_jenis($penj_jenis)) {
      return false;
    }
    if (!$this->set_penj_tipe_pembayaran($penj_tipe_pembayaran)) {
      return false;
    }
    if (!$this->set_penj_tgl($penj_tgl)) {
      return false;
    }
    if (!$this->set_penj_status($penj_status)) {
      return false;
    }
    if (!$this->set_id_fk_customer($id_fk_customer)) {
      return false;
    }
    if (!$this->set_id_fk_cabang($id_fk_cabang)) {
      return false;
    }
    return true;
  }
  public function set_update($id_pk_penjualan, $penj_nomor, $penj_dateline_tgl, $penj_jenis, $penj_tipe_pembayaran, $penj_tgl, $id_fk_customer)
  {
    if (!$this->set_id_pk_penjualan($id_pk_penjualan)) {
      return false;
    }
    if (!$this->set_penj_nomor($penj_nomor)) {
      return false;
    }
    if (!$this->set_penj_dateline_tgl($penj_dateline_tgl)) {
      return false;
    }
    if (!$this->set_penj_jenis($penj_jenis)) {
      return false;
    }
    if (!$this->set_penj_tipe_pembayaran($penj_tipe_pembayaran)) {
      return false;
    }
    if (!$this->set_penj_tgl($penj_tgl)) {
      return false;
    }
    if (!$this->set_id_fk_customer($id_fk_customer)) {
      return false;
    }
    return true;
  }
  public function set_delete($id_pk_penjualan)
  {
    if (!$this->set_id_pk_penjualan($id_pk_penjualan)) {
      return false;
    }
    return true;
  }
  public function set_id_pk_penjualan($id_pk_penjualan)
  {
    if ($id_pk_penjualan != "") {
      $this->id_pk_penjualan = $id_pk_penjualan;
      return true;
    }
    return false;
  }
  public function set_penj_nomor($penj_nomor)
  {
    if ($penj_nomor != "") {
      $this->penj_nomor = $penj_nomor;
      return true;
    }
    return false;
  }
  public function set_penj_nominal($penj_nominal)
  {
    if ($penj_nominal != "") {
      $this->penj_nominal = $penj_nominal;
      return true;
    }
    return false;
  }
  public function set_penj_nominal_byr($penj_nominal_byr)
  {
    if ($penj_nominal_byr != "") {
      $this->penj_nominal_byr = $penj_nominal_byr;
      return true;
    }
    return false;
  }
  public function set_penj_dateline_tgl($penj_dateline_tgl)
  {
    if ($penj_dateline_tgl != "") {
      $this->penj_dateline_tgl = $penj_dateline_tgl;
      return true;
    }
    return false;
  }
  public function set_penj_jenis($penj_jenis)
  {
    if ($penj_jenis != "") {
      $this->penj_jenis = $penj_jenis;
      return true;
    }
    return false;
  }
  public function set_penj_tipe_pembayaran($penj_tipe_pembayaran)
  {
    if ($penj_tipe_pembayaran != "") {
      $this->penj_tipe_pembayaran = $penj_tipe_pembayaran;
      return true;
    }
    return false;
  }
  public function set_penj_tgl($penj_tgl)
  {
    if ($penj_tgl != "") {
      $this->penj_tgl = $penj_tgl;
      return true;
    }
    return false;
  }
  public function set_penj_status($penj_status)
  {
    if ($penj_status != "") {
      $this->penj_status = $penj_status;
      return true;
    }
    return false;
  }
  public function set_id_fk_customer($id_fk_customer)
  {
    if ($id_fk_customer != "") {
      $this->id_fk_customer = $id_fk_customer;
      return true;
    }
    return false;
  }
  public function set_id_fk_cabang($id_fk_cabang)
  {
    if ($id_fk_cabang != "") {
      $this->id_fk_cabang = $id_fk_cabang;
      return true;
    }
    return false;
  }
  public function get_penj_nomor($id_fk_cabang, $jenis_transaksi, $custom_tgl = "-")
  {
    $this->db->trans_start();
    executeQuery("call generate_trans_no(" . $id_fk_cabang . ",'" . $jenis_transaksi . "','" . $custom_tgl . "',@transno,@latest_no);");
    $result = executeQuery("select @transno,@latest_no;");
    $this->db->trans_complete();
    $result = $result->result_array();
    $this->no_control = $result[0]["@latest_no"];
    return $result[0]["@transno"];
  }
  public function data_excel()
  {
    $query = "
        select id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_status,penj_jenis,penj_tipe_pembayaran,penj_last_modified,cust_name,cust_perusahaan,user_name as user_last_modified
        from mstr_penjualan 
        inner join mstr_customer on mstr_customer.id_pk_cust = mstr_penjualan.id_fk_customer
        inner join mstr_user on mstr_user.id_pk_user = mstr_penjualan.id_last_modified
        where penj_status != ? and id_fk_cabang = ?";
    $args = array(
      "nonaktif", $this->session->id_cabang
    );
    return executeQuery($query, $args);
  }
  public function columns_excel()
  {
    $this->columns = array();
    $this->set_column("penj_nomor", "nomor penjualan", true);
    $this->set_column("penj_tgl", "tanggal penjualan", false);
    $this->set_column("cust_name", "customer", false);
    $this->set_column("penj_tipe_pembayaran", "tipe pembayaran", false);
    $this->set_column("penj_jenis", "jenis penjualan", false);
    $this->set_column("penj_status", "status", false);
    $this->set_column("user_last_modified", "User Last Modified", false);
    return $this->columns;
  }
}
