<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_brg_penerimaan extends ci_model
{
  private $tbl_name = "tbl_brg_penerimaan";
  private $columns = array();
  private $id_pk_brg_penerimaan;
  private $brg_penerimaan_qty;
  private $brg_penerimaan_note;
  private $id_fk_penerimaan;
  private $id_fk_brg_pembelian;
  private $id_fk_brg_retur;
  private $id_fk_brg_pengiriman; #untuk yang pengiriman antar cabang
  private $id_fk_satuan;
  private $brg_penerimaan_create_date;
  private $brg_penerimaan_last_modified;
  private $id_create_data;
  private $id_last_modified;

  public function __construct()
  {
    parent::__construct();
    $this->brg_penerimaan_create_date = date("y-m-d h:i:s");
    $this->brg_penerimaan_last_modified = date("y-m-d h:i:s");
    $this->id_create_data = $this->session->id_user;
    $this->id_last_modified = $this->session->id_user;
  }
  public function columns()
  {
    return $this->columns;
  }
  public function install()
  {
    $sql = "
        drop table if exists tbl_brg_penerimaan;
        create table tbl_brg_penerimaan(
            id_pk_brg_penerimaan int primary key auto_increment,
            brg_penerimaan_qty double,
            brg_penerimaan_note varchar(200),
            id_fk_penerimaan int,
            id_fk_brg_pembelian int,
            id_fk_brg_retur int,
            id_fk_brg_pengiriman int,
            id_fk_satuan int,
            brg_penerimaan_create_date datetime,
            brg_penerimaan_last_modified datetime,
            id_create_data int,
            id_last_modified int
        );
        drop table if exists tbl_brg_penerimaan_log;
        create table tbl_brg_penerimaan_log(
            id_pk_brg_penerimaan_log int primary key auto_increment,
            executed_function varchar(30),
            id_pk_brg_penerimaan int,
            brg_penerimaan_qty double,
            brg_penerimaan_note varchar(200),
            id_fk_penerimaan int,
            id_fk_brg_pembelian int,
            id_fk_brg_retur int,
            id_fk_brg_pengiriman int,
            id_fk_satuan int,
            brg_penerimaan_create_date datetime,
            brg_penerimaan_last_modified datetime,
            id_create_data int,
            id_last_modified int,
            id_log_all int
        );
        drop trigger if exists trg_after_insert_brg_penerimaan;
        delimiter $$
        create trigger trg_after_insert_brg_penerimaan
        after insert on tbl_brg_penerimaan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penerimaan_log(executed_function,id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_brg_retur,id_fk_brg_pengiriman,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penerimaan,new.brg_penerimaan_qty,new.brg_penerimaan_note,new.id_fk_penerimaan,new.id_fk_brg_pembelian,new.id_fk_brg_retur,new.id_fk_brg_pengiriman,new.id_fk_satuan,new.brg_penerimaan_create_date,new.brg_penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end$$
        delimiter ;

        drop trigger if exists trg_after_update_brg_penerimaan;
        delimiter $$
        create trigger trg_after_update_brg_penerimaan
        after update on tbl_brg_penerimaan
        for each row
        begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penerimaan_log(executed_function,id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_brg_retur,id_fk_brg_pengiriman,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penerimaan,new.brg_penerimaan_qty,new.brg_penerimaan_note,new.id_fk_penerimaan,new.id_fk_brg_pembelian,new.id_fk_brg_retur,new.id_fk_brg_pengiriman,new.id_fk_satuan,new.brg_penerimaan_create_date,new.brg_penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end$$
        delimiter ;

        delimiter $$
        create trigger trg_update_brg_cabang_after_insert_brg_penerimaan
        after insert on tbl_brg_penerimaan
        for each row
        begin
            set @id_cabang = 0;
            set @id_barang = 0;
            set @id_warehouse = 0;
            set @brg_penerimaan_qty = new.brg_penerimaan_qty;
            set @id_satuan_terima = new.id_fk_satuan;
            set @id_fk_brg_pembelian = new.id_fk_brg_pembelian;
            set @id_fk_brg_retur = new.id_fk_brg_retur;
            set @id_fk_brg_pengiriman = new.id_fk_brg_pengiriman;
            
            if @id_fk_brg_pembelian is not null and @id_fk_brg_pembelian != 0
            then
            select mstr_penerimaan.id_fk_cabang, id_fk_barang, mstr_penerimaan.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse 
            from tbl_brg_penerimaan
            inner join tbl_brg_pembelian on tbl_brg_pembelian.id_pk_brg_pembelian = tbl_brg_penerimaan.id_fk_brg_pembelian
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;

            elseif @id_fk_brg_retur is not null and @id_fk_brg_retur != 0 then
            select mstr_penerimaan.id_fk_cabang, id_fk_brg, mstr_penerimaan.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_penerimaan
            inner join tbl_retur_brg on tbl_retur_brg.id_pk_retur_brg = tbl_brg_penerimaan.id_fk_brg_retur
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;

            elseif @id_fk_brg_pengiriman is not null and @id_fk_brg_pengiriman != 0 then
            select mstr_penerimaan.id_fk_cabang, id_fk_brg, mstr_penerimaan.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_penerimaan
            inner join tbl_brg_pengiriman on tbl_brg_pengiriman.id_pk_brg_pengiriman = tbl_brg_penerimaan.id_fk_brg_pengiriman
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;
            end if;
            
            if @id_warehouse is not null then
            call update_stok_barang_warehouse(@id_barang,@id_warehouse,@brg_penerimaan_qty,@id_satuan_terima,0,0);
            elseif @id_cabang is not null then 
            call update_stok_barang_cabang(@id_barang,@id_cabang,@brg_penerimaan_qty,@id_satuan_terima,0,0);
            end if;
        end $$
        delimiter ;

        delimiter $$
        create trigger trg_update_brg_cabang_after_update_brg_penerimaan
        after update on tbl_brg_penerimaan
        for each row
        begin
            set @id_cabang = 0;
            set @id_barang = 0;
            set @id_warehouse = 0;
            set @brg_penerimaan_qty = new.brg_penerimaan_qty;
            set @id_satuan_terima = new.id_fk_satuan;
            set @brg_keluar_qty = old.brg_penerimaan_qty;
            set @id_satuan_keluar = old.id_fk_satuan;
            set @id_fk_brg_pembelian = new.id_fk_brg_pembelian;
            set @id_fk_brg_retur = new.id_fk_brg_retur;
            set @id_fk_brg_pengiriman = new.id_fk_brg_pengiriman;
            
            if @id_fk_brg_pembelian is not null and @id_fk_brg_pembelian != 0
            then
            select mstr_penerimaan.id_fk_cabang, id_fk_barang, mstr_penerimaan.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse 
            from tbl_brg_penerimaan
            inner join tbl_brg_pembelian on tbl_brg_pembelian.id_pk_brg_pembelian = tbl_brg_penerimaan.id_fk_brg_pembelian
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;

            elseif @id_fk_brg_retur is not null and @id_fk_brg_retur != 0 then
            select mstr_penerimaan.id_fk_cabang, id_fk_brg, mstr_penerimaan.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_penerimaan
            inner join tbl_retur_brg on tbl_retur_brg.id_pk_retur_brg = tbl_brg_penerimaan.id_fk_brg_retur
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;

            elseif @id_fk_brg_pengiriman is not null and @id_fk_brg_pengiriman != 0 then
            select mstr_penerimaan.id_fk_cabang, id_fk_brg, mstr_penerimaan.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_penerimaan
            inner join tbl_brg_pengiriman on tbl_brg_pengiriman.id_pk_brg_pengiriman = tbl_brg_penerimaan.id_fk_brg_pengiriman
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;
            end if;
            
            if @id_warehouse is not null then
            call update_stok_barang_warehouse(@id_barang,@id_warehouse,@brg_penerimaan_qty,@id_satuan_terima,@brg_keluar_qty,@id_satuan_keluar);
            elseif @id_cabang is not null then 
            call update_stok_barang_cabang(@id_barang,@id_cabang,@brg_penerimaan_qty,@id_satuan_terima,@brg_keluar_qty,@id_satuan_keluar);
            end if;

        end $$
        delimiter ;
        ";
  }
  public function list_data()
  {
    $query = "
        select id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_nama,satuan_nama
        from " . $this->tbl_name . "
        inner join tbl_brg_pembelian on tbl_brg_pembelian.id_pk_brg_pembelian = " . $this->tbl_name . ".id_fk_brg_pembelian
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_pembelian.id_fk_barang
        inner join mstr_satuan on mstr_satuan.id_pk_satuan = " . $this->tbl_name . ".id_fk_satuan
        where id_fk_penerimaan = ? and brg_pem_status = ? and brg_status = ?
        ";
    $args = array(
      $this->id_fk_penerimaan, "aktif", "aktif"
    );
    return executequery($query, $args);
  }
  public function list_retur()
  {
    $query = "
        select id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_satuan,brg_nama,satuan_nama,retur_brg_qty,retur_brg_satuan,retur_brg_notes
        from " . $this->tbl_name . "
        inner join tbl_retur_brg on tbl_retur_brg.id_pk_retur_brg = " . $this->tbl_name . ".id_fk_brg_retur
        inner join mstr_barang on mstr_barang.id_pk_brg = tbl_retur_brg.id_fk_brg
        inner join mstr_satuan on mstr_satuan.id_pk_satuan = " . $this->tbl_name . ".id_fk_satuan
        where id_fk_penerimaan = ? and retur_brg_status = ? and brg_status = ?
        ";
    $args = array(
      $this->id_fk_penerimaan, "aktif", "aktif"
    );
    return executequery($query, $args);
  }
  public function insert()
  {
    $data = array(
      "brg_penerimaan_qty" => $this->brg_penerimaan_qty,
      "brg_penerimaan_note" => $this->brg_penerimaan_note,
      "id_fk_penerimaan" => $this->id_fk_penerimaan,
      "id_fk_brg_pembelian" => $this->id_fk_brg_pembelian,
      "id_fk_brg_retur" => $this->id_fk_brg_retur,
      "id_fk_brg_pengiriman" => $this->id_fk_brg_pengiriman,
      "id_fk_satuan" => $this->id_fk_satuan,
      "brg_penerimaan_create_date" => $this->brg_penerimaan_create_date,
      "brg_penerimaan_last_modified" => $this->brg_penerimaan_last_modified,
      "id_create_data" => $this->id_create_data,
      "id_last_modified" => $this->id_last_modified
    );

    $id_hasil_insert = insertrow($this->tbl_name, $data);

    $log_all_msg = "Data Barang Penerimaan baru ditambahkan. Waktu penambahan: $this->brg_penerimaan_create_date";
    $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_create_data));

    $log_all_data_changes = "[ID Barang Penerimaan: $id_hasil_insert][Jumlah: $this->brg_penerimaan_qty][Notes: $this->brg_penerimaan_note][ID Penerimaan: $this->id_fk_penerimaan][ID Pembelian: $this->id_fk_brg_pembelian][ID Retur: $this->id_fk_brg_retur][ID Pengiriman: $this->id_fk_brg_pengiriman][ID Satuan: $this->id_fk_satuan][Waktu Ditambahkan: $this->brg_penerimaan_create_date][Oleh: $nama_user]";
    $log_all_it = "";
    $log_all_user = $this->id_create_data;
    $log_all_tgl = $this->brg_penerimaan_create_date;

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
        "id_pk_brg_penerimaan" => $this->id_pk_brg_penerimaan
      );
      $data = array(
        "brg_penerimaan_qty" => $this->brg_penerimaan_qty,
        "brg_penerimaan_note" => $this->brg_penerimaan_note,
        "id_fk_satuan" => $this->id_fk_satuan,
        "brg_penerimaan_last_modified" => $this->brg_penerimaan_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updaterow($this->tbl_name, $data, $where);
      $id_pk = $this->id_pk_brg_penerimaan;
      $log_all_msg = "Data Barang Penerimaan dengan ID: $id_pk diubah. Waktu diubah: $this->brg_penerimaan_last_modified . Data berubah menjadi: ";
      $nama_user = get1Value("mstr_user", "user_name", array("id_pk_user" => $this->id_last_modified));

      $log_all_data_changes = "[ID Barang Penerimaan: $id_pk][Jumlah: $this->brg_penerimaan_qty][Notes: $this->brg_penerimaan_note][ID Penerimaan: $this->id_fk_penerimaan][ID Pembelian: $this->id_fk_brg_pembelian][ID Retur: $this->id_fk_brg_retur][ID Pengiriman: $this->id_fk_brg_pengiriman][ID Satuan: $this->id_fk_satuan][Waktu Diedit: $this->brg_penerimaan_last_modified][Oleh: $nama_user]";
      $log_all_it = "";
      $log_all_user = $this->id_last_modified;
      $log_all_tgl = $this->brg_penerimaan_last_modified;

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
        "id_pk_brg_penerimaan" => $this->id_pk_brg_penerimaan
      );
      $data = array(
        "brg_penerimaan_last_modified" => $this->brg_penerimaan_last_modified,
        "id_last_modified" => $this->id_last_modified
      );
      updaterow($this->tbl_name, $data, $where);
      return true;
    }
    return false;
  }
  public function delete_brg_penerimaan()
  {
    #method ini dibuat untuk ngosongin brg_penerimaan kalau penerimaannya diapus
    $where = array(
      "id_fk_penerimaan" => $this->id_fk_penerimaan
    );
    $data = array(
      "brg_penerimaan_qty" => 0,
      "brg_penerimaan_last_modified" => $this->brg_penerimaan_last_modified,
      "id_last_modified" => $this->id_last_modified
    );
    updaterow($this->tbl_name, $data, $where);
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
  public function set_insert($brg_penerimaan_qty, $brg_penerimaan_note, $id_fk_penerimaan, $id_fk_brg_pembelian = "", $id_fk_satuan, $id_fk_brg_retur = "", $id_fk_brg_pengiriman = "")
  {
    $this->set_brg_penerimaan_qty($brg_penerimaan_qty);
    $this->set_brg_penerimaan_note($brg_penerimaan_note);
    $this->set_id_fk_penerimaan($id_fk_penerimaan);
    $this->id_fk_brg_pembelian = $id_fk_brg_pembelian;
    $this->id_fk_brg_retur = $id_fk_brg_retur;
    $this->id_fk_brg_pengiriman = $id_fk_brg_pengiriman;
    $this->set_id_fk_satuan($id_fk_satuan);
    return true;
  }
  public function set_update($id_pk_brg_penerimaan, $brg_penerimaan_qty, $brg_penerimaan_note, $id_fk_satuan)
  {
    $this->set_id_pk_brg_penerimaan($id_pk_brg_penerimaan);
    $this->set_brg_penerimaan_qty($brg_penerimaan_qty);
    $this->set_brg_penerimaan_note($brg_penerimaan_note);
    $this->set_id_fk_satuan($id_fk_satuan);
    return true;
  }
  public function set_delete($id_pk_brg_penerimaan)
  {
    $this->set_id_pk_brg_penerimaan($id_pk_brg_penerimaan);
    return true;
  }
  public function set_id_pk_brg_penerimaan($id_pk_brg_penerimaan)
  {
    $this->id_pk_brg_penerimaan = $id_pk_brg_penerimaan;
    return true;
  }
  public function set_brg_penerimaan_qty($brg_penerimaan_qty)
  {
    $this->brg_penerimaan_qty = $brg_penerimaan_qty;
    return true;
  }
  public function set_brg_penerimaan_note($brg_penerimaan_note)
  {
    $this->brg_penerimaan_note = $brg_penerimaan_note;
    return true;
  }
  public function set_id_fk_penerimaan($id_fk_penerimaan)
  {
    $this->id_fk_penerimaan = $id_fk_penerimaan;
    return true;
  }
  public function set_id_fk_brg_pembelian($id_fk_brg_pembelian)
  {
    $this->id_fk_brg_pembelian = $id_fk_brg_pembelian;
    return true;
  }
  public function set_id_fk_satuan($id_fk_satuan)
  {
    $this->id_fk_satuan = $id_fk_satuan;
    return true;
  }
}
