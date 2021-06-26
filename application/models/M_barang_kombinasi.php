<?php
defined("BASEPATH") or exit("No Direct Script");
date_default_timezone_set("Asia/Jakarta");
class M_barang_kombinasi extends CI_Model
{
  private $tbl_name = "tbl_barang_kombinasi";
  private $columns = array();
  private $id_pk_barang_kombinasi;
  private $id_barang_utama; /*hasil gabungan dari kedua barang itu, contoh wearpack*/
  private $id_barang_kombinasi; /*item yang jadi gabungan untuk membuat wearpack, contoh: celana panjang & baju panjang*/
  private $barang_kombinasi_qty; /*2 celana & 1 baju */
  private $barang_kombinasi_status;
  private $barang_kombinasi_create_date;
  private $barang_kombinasi_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->barang_kombinasi_create_date = date("Y-m-d H:i:s");
    $this->barang_kombinasi_last_modified = date("Y-m-d H:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  public function install()
  {
    $sql = "drop table if exists tbl_barang_kombinasi;
        create table tbl_barang_kombinasi(
            id_pk_barang_kombinasi int primary key auto_increment,
            id_barang_utama int,
            id_barang_kombinasi int,
            barang_kombinasi_qty double,
            barang_kombinasi_status varchar(15),
            barang_kombinasi_create_date datetime,
            barang_kombinasi_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_barang_kombinasi_log;
        create table tbl_barang_kombinasi_log(
            id_pk_barang_kombinasi_log int primary key auto_increment,
            executed_function varchar(20),
            id_pk_barang_kombinasi int,
            id_barang_utama int,
            id_barang_kombinasi int,
            barang_kombinasi_qty double,
            barang_kombinasi_status varchar(15),
            barang_kombinasi_create_date datetime,
            barang_kombinasi_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_barang_kombinasi;
        delimiter $$
        create trigger trg_after_insert_barang_kombinasi
        after insert on tbl_barang_kombinasi
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;
        
        drop trigger if exists trg_after_update_barang_kombinasi;
        delimiter $$
        create trigger trg_after_update_barang_kombinasi
        after update on tbl_barang_kombinasi
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end$$
        delimiter ;

        drop procedure if exists update_stok_kombinasi_anggota_cabang;
        delimiter //
        create procedure update_stok_kombinasi_anggota_cabang(
            in id_barang_utama_in int,
            in qty_brg_masuk_in double,
            in qty_brg_keluar_in double,
            in id_cabang_in int
        )
        begin
            update tbl_barang_kombinasi
            inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
            set brg_cabang_qty = brg_cabang_qty+(barang_kombinasi_qty*qty_brg_masuk_in)-(barang_kombinasi_qty*qty_brg_keluar_in)
            where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in and barang_kombinasi_status = 'aktif';
        end//
        delimiter ;
        
        drop procedure if exists update_stok_kombinasi_anggota_warehouse;
        delimiter //
        create procedure update_stok_kombinasi_anggota_warehouse(
            in id_barang_utama_in int,
            in qty_brg_masuk_in double,
            in qty_brg_keluar_in double,
            in id_warehouse_in int
        )
        begin
            update tbl_barang_kombinasi
            inner join tbl_brg_warehouse on tbl_brg_warehouse.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
            set brg_warehouse_qty = brg_warehouse_qty+(barang_kombinasi_qty*qty_brg_masuk_in)-(barang_kombinasi_qty*qty_brg_keluar_in)
            where id_barang_utama = id_barang_utama_in and id_fk_warehouse = id_warehouse_in and barang_kombinasi_status = 'aktif';
        end//
        delimiter ;";
  }

  public function list_data()
  {
    $sql = "
        select id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty, brg_kombinasi.brg_nama
        from " . $this->tbl_name . "
        inner join mstr_barang as brg_kombinasi on brg_kombinasi.id_pk_brg = tbl_barang_kombinasi.id_barang_kombinasi  
        where barang_kombinasi_status = ? and id_barang_utama = ? and brg_status = ?
        ";
    $args = array(
      "aktif", $this->id_barang_utama, "aktif"
    );
    return executeQuery($sql, $args);
  }
  public function check_barang_in_kombinasi($id_barang_kombinasi)
  {
    $where = array(
      "id_barang_kombinasi" => $id_barang_kombinasi,
      "barang_kombinasi_status" => "aktif"
    );
    return isExistsInTable($this->tbl_name, $where);
  }
  public function check_double_barang($id_pk_barang_kombinasi = 0)
  {
    $where = array(
      "id_pk_barang_kombinasi !=" => $id_pk_barang_kombinasi,
      "id_barang_kombinasi" => $this->id_barang_kombinasi,
      "id_barang_utama" => $this->id_barang_utama,
      "barang_kombinasi_status" => "aktif"
    );
    return isExistsInTable($this->tbl_name, $where);
  }
  public function insert()
  {
    if ($this->check_insert()) {
      if ($this->check_double_barang()) {
        #update jumlahnya pake yang terbaru
        $where = array(
          "id_barang_utama" => $this->id_barang_utama,
          "id_barang_kombinasi" => $this->id_barang_kombinasi,
        );
        $data = array(
          "barang_kombinasi_qty" => $this->barang_kombinasi_qty,
        );
        updateRow($this->tbl_name, $data, $where);
        return true;
      } else {
        $data = array(
          "id_barang_utama" => $this->id_barang_utama,
          "id_barang_kombinasi" => $this->id_barang_kombinasi,
          "barang_kombinasi_qty" => $this->barang_kombinasi_qty,
          "barang_kombinasi_status" => "aktif",
          "barang_kombinasi_create_date" => $this->barang_kombinasi_create_date,
          "barang_kombinasi_last_modified" => $this->barang_kombinasi_last_modified,
          "id_create_data" => $this->id_create_data,
          "id_last_modified" => $this->id_last_modified
        );
        $id_hasil_insert = insertrow($this->tbl_name, $data);

        $log_all_msg = "Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: $this->barang_kombinasi_create_date";
        $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));
        $log_all_data_changes = "[ID Barang Kombinasi: $id_hasil_insert][ID Barang Utama: $this->id_barang_utama][ID Barang Kombinasi: $this->id_barang_kombinasi][Jumlah: $this->barang_kombinasi_qty][Status: $this->barang_kombinasi_status][Waktu Ditambahkan: $this->barang_kombinasi_create_date][Oleh: $this->$id_hasil_insert]";
        $log_all_it = "";
        $log_all_user = $this->id_last_modified;
        $log_all_tgl = $this->barang_kombinasi_create_date;

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

      if ($this->check_double_barang($this->id_pk_barang_kombinasi)) {
        #update jumlahnya pake yang terbaru
        $where = array(
          "id_pk_barang_kombinasi !=" => $this->id_pk_barang_kombinasi,
          "id_barang_utama" => $this->id_barang_utama,
          "id_barang_kombinasi" => $this->id_barang_kombinasi,
        );
        $data = array(
          "barang_kombinasi_qty" => $this->barang_kombinasi_qty,
        );
        updateRow($this->tbl_name, $data, $where);

        #delete yang lagi diupdate (pindah ke barang baru)
        $where = array(
          "id_pk_barang_kombinasi" => $this->id_pk_barang_kombinasi,
        );
        deleteRow($this->tbl_name, $where);
        return true;
      } else {
        $where = array(
          "id_pk_barang_kombinasi" => $this->id_pk_barang_kombinasi,
        );
        $data = array(
          "id_barang_kombinasi" => $this->id_barang_kombinasi,
          "barang_kombinasi_qty" => $this->barang_kombinasi_qty,
          "barang_kombinasi_last_modified" => $this->barang_kombinasi_last_modified,
          "id_last_modified" => $this->id_last_modified
        );
        updateRow($this->tbl_name, $data, $where);
        return true;
      }
    }
    return false;
  }
  public function delete()
  {
    if ($this->check_delete()) {
      $where = array(
        "id_pk_barang_kombinasi" => $this->id_pk_barang_kombinasi,
      );
      $data = array(
        "barang_kombinasi_status" => "nonaktif",
        "barang_kombinasi_last_modified" => $this->barang_kombinasi_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updateRow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function delete_by_barang_utama()
  {
    $where = array(
      "id_barang_utama" => $this->id_barang_utama,
    );
    $data = array(
      "barang_kombinasi_status" => "nonaktif",
      "barang_kombinasi_last_modified" => $this->barang_kombinasi_last_modified,
      "id_last_modified" => $this->id_last_modified
    );
    updateRow($this->tbl_name, $data, $where);
    return true;
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
  public function set_insert($id_barang_utama, $id_barang_kombinasi, $barang_kombinasi_qty, $barang_kombinasi_status)
  {
    $this->set_id_barang_utama($id_barang_utama);
    $this->set_id_barang_kombinasi($id_barang_kombinasi);
    $this->set_barang_kombinasi_qty($barang_kombinasi_qty);
    $this->set_barang_kombinasi_status($barang_kombinasi_status);
    return true;
  }
  public function set_update($id_pk_barang_kombinasi, $id_barang_kombinasi, $barang_kombinasi_qty)
  {
    $this->set_id_pk_barang_kombinasi($id_pk_barang_kombinasi);
    $this->set_id_barang_kombinasi($id_barang_kombinasi);
    $this->set_barang_kombinasi_qty($barang_kombinasi_qty);
    return true;
  }
  public function set_delete($id_pk_barang_kombinasi)
  {
    $this->set_id_pk_barang_kombinasi($id_pk_barang_kombinasi);
    return true;
  }

  public function set_id_pk_barang_kombinasi($id_pk_barang_kombinasi)
  {
    $this->id_pk_barang_kombinasi = $id_pk_barang_kombinasi;
    return true;
  }
  public function set_id_barang_utama($id_barang_utama)
  {
    $this->id_barang_utama = $id_barang_utama;
    return true;
  }
  public function set_id_barang_kombinasi($id_barang_kombinasi)
  {
    $this->id_barang_kombinasi = $id_barang_kombinasi;
    return true;
  }
  public function set_barang_kombinasi_qty($barang_kombinasi_qty)
  {
    $this->barang_kombinasi_qty = $barang_kombinasi_qty;
    return true;
  }
  public function set_barang_kombinasi_status($barang_kombinasi_status)
  {
    $this->barang_kombinasi_status = $barang_kombinasi_status;
    return true;
  }
}
