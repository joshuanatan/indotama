<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_brg_warehouse extends ci_model
{
  private $tbl_name = "tbl_brg_warehouse";
  private $columns = array();
  private $id_pk_brg_warehouse;
  private $brg_warehouse_qty;
  private $brg_warehouse_notes;
  private $brg_warehouse_status;
  private $id_fk_warehouse;
  private $id_fk_brg;
  private $brg_warehouse_create_date;
  private $brg_warehouse_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->set_column("brg_kode", "kode barang", "required");
    $this->set_column("brg_nama", "nama barang", "required");
    $this->set_column("brg_ket", "keterangan", "required");
    $this->set_column("brg_warehouse_qty", "qty", "required");
    $this->set_column("brg_warehouse_notes", "notes", "required");
    $this->set_column("brg_tipe", "Tipe Kombinasi", "required");
    $this->set_column("brg_warehouse_status", "status", "required");
    $this->set_column("brg_warehouse_last_modified", "last modified", "required");
    $this->brg_warehouse_create_date = date("y-m-d h:i:s");
    $this->brg_warehouse_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  private function stock_adjustment()
  {
    #update master kombinasi based on stok
    executeQuery("call update_stok_kombinasi_master_warehouse();");
  }
  public function columns()
  {
    return $this->columns;
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
  public function install()
  {
    $sql = "
        drop table if exists tbl_brg_warehouse;
        create table tbl_brg_warehouse(
            id_pk_brg_warehouse int primary key auto_increment,
            brg_warehouse_qty int,
            brg_warehouse_notes varchar(200),
            brg_warehouse_status varchar(15),
            id_fk_brg int,
            id_fk_warehouse int,
            brg_warehouse_create_date datetime,
            brg_warehouse_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_warehouse_log;
        create table tbl_brg_warehouse_log(
            id_pk_brg_warehouse_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_warehouse int,
            brg_warehouse_qty int,
            brg_warehouse_notes varchar(200),
            brg_warehouse_status varchar(15),
            id_fk_brg int,
            id_fk_warehouse int,
            brg_warehouse_create_date datetime,
            brg_warehouse_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_warehouse;
        delimiter $$
        create trigger trg_after_insert_brg_warehouse
        after insert on tbl_brg_warehouse
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_warehouse_log(executed_function,id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,id_fk_warehouse,brg_warehouse_create_date,brg_warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_warehouse,new.brg_warehouse_qty,new.brg_warehouse_notes,new.brg_warehouse_status,new.id_fk_brg,new.id_fk_warehouse,new.brg_warehouse_create_date,new.brg_warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
        end$$
        delimiter ;

        drop trigger if exists trg_after_update_brg_warehouse;
        delimiter $$
        create trigger trg_after_update_brg_warehouse
        after update on tbl_brg_warehouse
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_warehouse_log(executed_function,id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,id_fk_warehouse,brg_warehouse_create_date,brg_warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_warehouse,new.brg_warehouse_qty,new.brg_warehouse_notes,new.brg_warehouse_status,new.id_fk_brg,new.id_fk_warehouse,new.brg_warehouse_create_date,new.brg_warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop procedure if exists update_stok_barang_warehouse;
        delimiter //
        create procedure update_stok_barang_warehouse(
            in id_barang int,
            in id_warehouse int,
            in barang_masuk double,
            in id_satuan_masuk int,
            in barang_keluar double,
            in id_satuan_keluar int
        )
        begin
            /*
            the logic is
            barang_masuk = n, barang_keluar = 0 [insert new data]
            barang_masuk = n, barang_keluar = m [update data]
            barang_masuk = 0, barang_keluar = m [delete data]
            */
            if barang_masuk != 0 then
            call ubah_satuan_barang(id_satuan_masuk, barang_masuk);
            end if;
            if barang_keluar != 0 then
            call ubah_satuan_barang(id_satuan_keluar, barang_keluar);
            end if;
            update tbl_brg_warehouse 
            set brg_warehouse_qty = brg_warehouse_qty+barang_masuk-barang_keluar
            where id_fk_brg = id_barang and id_fk_warehouse = id_warehouse;
        end //
        delimiter ;";
    executequery($sql);
  }
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "")
  {
    $this->stock_adjustment();
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            (
                id_pk_brg_warehouse like '%" . $search_key . "%' or 
                brg_warehouse_qty like '%" . $search_key . "%' or 
                brg_warehouse_notes like '%" . $search_key . "%' or 
                brg_warehouse_status like '%" . $search_key . "%' or 
                id_fk_brg like '%" . $search_key . "%' or 
                brg_warehouse_last_modified like '%" . $search_key . "%' or 
                brg_nama like '%" . $search_key . "%' or 
                brg_kode like '%" . $search_key . "%' or 
                brg_ket like '%" . $search_key . "%' or 
                brg_minimal like '%" . $search_key . "%' or 
                brg_satuan like '%" . $search_key . "%' or 
                brg_image like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,brg_warehouse_last_modified,brg_nama,brg_kode,brg_ket,brg_minimal,brg_satuan,brg_image,brg_tipe
        from " . $this->tbl_name . " 
        inner join mstr_barang on mstr_barang.id_pk_brg = " . $this->tbl_name . ".id_fk_brg
        where brg_warehouse_status = ? and brg_status = ? and id_fk_warehouse = ? " . $search_query . "  
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;
    $args = array(
      "aktif", "aktif", $this->id_fk_warehouse
    );
    $result["data"] = executequery($query, $args);

    $query = "
        select id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,brg_warehouse_last_modified,brg_nama,brg_kode,brg_ket,brg_minimal,brg_satuan,brg_image
        from " . $this->tbl_name . " 
        inner join mstr_barang on mstr_barang.id_pk_brg = " . $this->tbl_name . ".id_fk_brg
        where brg_warehouse_status = ? and brg_status = ? and id_fk_warehouse = ?" . $search_query . "  
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query, $args)->num_rows();
    return $result;
  }
  public function list_not_exists_brg_kombinasi()
  {
    $sql = "
      select brg_utama_ref.brg_nama as brg_utama, brg_kombinasi_ref.brg_nama as brg_kombinasi_nama, tbl_barang_kombinasi.barang_kombinasi_qty, tbl_barang_kombinasi.id_barang_utama, tbl_barang_kombinasi.id_barang_kombinasi, tbl_brg_warehouse.brg_warehouse_qty*tbl_barang_kombinasi.barang_kombinasi_qty as add_qty 
      from tbl_barang_kombinasi 
      inner join mstr_barang as brg_utama_ref on brg_utama_ref.id_pk_brg = tbl_barang_kombinasi.id_barang_utama and brg_utama_ref.brg_tipe = 'kombinasi' 
      inner join tbl_brg_warehouse on tbl_brg_warehouse.id_fk_brg = tbl_barang_kombinasi.id_barang_utama and id_fk_warehouse = ? 
      inner join mstr_barang as brg_kombinasi_ref on brg_kombinasi_ref.id_pk_brg = tbl_barang_kombinasi.id_barang_kombinasi and brg_kombinasi_ref.brg_tipe = 'nonkombinasi' 
      where tbl_barang_kombinasi.barang_kombinasi_status = 'aktif'
      ";
    $args = array(
      $this->id_fk_warehouse
    );
    return executeQuery($sql, $args);
  }
  public function insert()
  {
    if ($this->check_insert()) {
      $where = array(
        "brg_warehouse_status" => "aktif",
        "id_fk_brg" => $this->id_fk_brg,
        "id_fk_warehouse" => $this->id_fk_warehouse,
      );
      if (!isExistsInTable($this->tbl_name, $where)) {
        $data = array(
          "brg_warehouse_qty" => $this->brg_warehouse_qty,
          "brg_warehouse_notes" => $this->brg_warehouse_notes,
          "brg_warehouse_status" => $this->brg_warehouse_status,
          "id_fk_brg" => $this->id_fk_brg,
          "id_fk_warehouse" => $this->id_fk_warehouse,
          "brg_warehouse_create_date" => $this->brg_warehouse_create_date,
          "brg_warehouse_last_modified" => $this->brg_warehouse_last_modified,
          "id_create_data" => $this->id_create_data,
          "id_last_modified" => $this->id_last_modified
        );

        $id_hasil_insert = insertrow($this->tbl_name, $data);

        $log_all_msg = "Data Barang Warehouse baru ditambahkan. Waktu penambahan: $this->brg_warehouse_create_date";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

        $log_all_data_changes = "[ID Barang Warehouse: $id_hasil_insert][Jumlah: $this->brg_warehouse_qty][Notes: $this->brg_warehouse_notes][Status: $this->brg_warehouse_status][ID Barang: $this->id_fk_brg][ID Warehouse: $this->id_fk_warehouse][Waktu Ditambahkan: $this->brg_warehouse_create_date][Oleh: $nama_user]";
        $log_all_it = "";
        $log_all_user = $this->id_create_data;
        $log_all_tgl = $this->brg_warehouse_create_date;

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
        $query = "update " . $this->tbl_name . " set brg_warehouse_qty = brg_warehouse_qty+" . $this->brg_warehouse_qty . " where id_fk_brg = ? and id_fk_warehouse = ?";
        $args =  array(
          $this->id_fk_brg, $this->id_fk_warehouse
        );
        executeQuery($query, $args);
        return true;
      }
    }
    return false;
  }
  public function insert_adjustment()
  {
    if ($this->check_insert()) {
      $where = array(
        "brg_warehouse_status" => "aktif",
        "id_fk_brg" => $this->id_fk_brg,
        "id_fk_warehouse" => $this->id_fk_warehouse,
      );
      if (!isExistsInTable($this->tbl_name, $where)) {
        $data = array(
          "brg_warehouse_qty" => $this->brg_warehouse_qty,
          "brg_warehouse_notes" => $this->brg_warehouse_notes,
          "brg_warehouse_status" => $this->brg_warehouse_status,
          "id_fk_brg" => $this->id_fk_brg,
          "id_fk_warehouse" => $this->id_fk_warehouse,
          "brg_warehouse_create_date" => $this->brg_warehouse_create_date,
          "brg_warehouse_last_modified" => $this->brg_warehouse_last_modified,
          "id_create_data" => $this->id_create_data,
          "id_last_modified" => $this->id_last_modified
        );

        $id_hasil_insert = insertrow($this->tbl_name, $data);

        $log_all_msg = "Data Barang Warehouse baru ditambahkan. Waktu penambahan: $this->brg_warehouse_create_date";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

        $log_all_data_changes = "[ID Barang Warehouse: $id_hasil_insert][Jumlah: $this->brg_warehouse_qty][Notes: $this->brg_warehouse_notes][Status: $this->brg_warehouse_status][ID Barang: $this->id_fk_brg][ID Warehouse: $this->id_fk_warehouse][Waktu Ditambahkan: $this->brg_warehouse_create_date][Oleh: $nama_user]";
        $log_all_it = "";
        $log_all_user = $this->id_create_data;
        $log_all_tgl = $this->brg_warehouse_create_date;

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
    }
    return false;
  }
  public function update()
  {
    if ($this->check_update()) {
      $where = array(
        "id_pk_brg_warehouse" => $this->id_pk_brg_warehouse
      );
      $data = array(
        "brg_warehouse_qty" => $this->brg_warehouse_qty,
        "brg_warehouse_notes" => $this->brg_warehouse_notes,
        "id_fk_brg" => $this->id_fk_brg,
        "brg_warehouse_last_modified" => $this->brg_warehouse_last_modified,
        "id_last_modified" => $this->id_last_modified
      );

      /* untuk manggil stored procedure aja */
      $query = "select brg_warehouse_qty from tbl_brg_warehouse where id_fk_brg = ? and id_fk_warehouse = ?";
      $args = array(
        $this->id_fk_brg, $this->id_fk_warehouse
      );
      $result = executeQuery($query, $args);
      $result = $result->result_array();
      /*end store procedure*/
      executeQuery("call update_stok_kombinasi_anggota_warehouse(" . $this->id_fk_brg . "," . $this->brg_warehouse_qty . "," . $result[0]["brg_warehouse_qty"] . "," . $this->id_fk_warehouse . ")");

      updateRow($this->tbl_name, $data, $where);
      $id_pk = $this->id_pk_brg_warehouse;
      $log_all_msg = "Data Barang Warehouse dengan ID: $id_pk diubah. Waktu diubah: $this->brg_warehouse_last_modified . Data berubah menjadi: ";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

      $log_all_data_changes = "[ID Barang Warehouse: $id_pk][Jumlah: $this->brg_warehouse_qty][Notes: $this->brg_warehouse_notes][ID Barang: $this->id_fk_brg][ID Warehouse: $this->id_fk_warehouse][Waktu Diedit: $this->brg_warehouse_create_date][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_last_modified;
      $log_all_tgl = $this->brg_warehouse_last_modified;

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
        "id_pk_brg_warehouse" => $this->id_pk_brg_warehouse
      );
      $data = array(
        "brg_warehouse_status" => "nonaktif",
        "brg_warehouse_last_modified" => $this->brg_warehouse_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updaterow($this->tbl_name, $data, $where);
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
  public function set_insert($brg_warehouse_qty, $brg_warehouse_notes, $brg_warehouse_status, $id_fk_brg, $id_fk_warehouse)
  {
    $this->set_brg_warehouse_qty($brg_warehouse_qty);
    $this->set_brg_warehouse_notes($brg_warehouse_notes);
    $this->set_brg_warehouse_status($brg_warehouse_status);
    $this->set_id_fk_brg($id_fk_brg);
    $this->set_id_fk_warehouse($id_fk_warehouse);
    return true;
  }
  public function set_update($id_pk_brg_warehouse, $brg_warehouse_qty, $brg_warehouse_notes, $id_fk_brg)
  {
    $this->set_id_pk_brg_warehouse($id_pk_brg_warehouse);
    $this->set_brg_warehouse_qty($brg_warehouse_qty);
    $this->set_brg_warehouse_notes($brg_warehouse_notes);
    $this->set_id_fk_brg($id_fk_brg);
    return true;
  }
  public function set_delete($id_pk_brg_warehouse)
  {
    $this->set_id_pk_brg_warehouse($id_pk_brg_warehouse);
    return true;
  }
  public function set_id_pk_brg_warehouse($id_pk_brg_warehouse)
  {
    $this->id_pk_brg_warehouse = $id_pk_brg_warehouse;
      return true;
  }
  public function set_brg_warehouse_qty($brg_warehouse_qty)
  {
    $this->brg_warehouse_qty = $brg_warehouse_qty;
      return true;
  }
  public function set_brg_warehouse_notes($brg_warehouse_notes)
  {
    $this->brg_warehouse_notes = $brg_warehouse_notes;
      return true;
  }
  public function set_brg_warehouse_status($brg_warehouse_status)
  {
    $this->brg_warehouse_status = $brg_warehouse_status;
      return true;
  }
  public function set_id_fk_brg($id_fk_brg)
  {
    $this->id_fk_brg = $id_fk_brg;
      return true;
  }
  public function set_id_fk_warehouse($id_fk_warehouse)
  {
    $this->id_fk_warehouse = $id_fk_warehouse;
      return true;
  }
}
