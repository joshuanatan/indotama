-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2020 at 08:42 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `indotama`
--

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `generate_trans_no` (IN `id_cabang_in` INT, IN `jenis_trans` VARCHAR(15), IN `custom_tgl` VARCHAR(20), OUT `trans_no` VARCHAR(100), OUT `latest_no` INT)  begin
	set @nomor = 0;
    if custom_tgl = "-"
    then
		set @bulan = convert(month(current_date),unsigned);
		set @tahun = convert(year(current_date),unsigned);
		set @tgl = convert(day(current_date),unsigned);
	else
		set @tahun = convert(substring_index(custom_tgl,"-",1),unsigned);
        set @bulan = convert(substring_index(substring_index(custom_tgl,"-",2),"-",-1),unsigned);
        set @tgl = convert(substring_index(custom_tgl,"-",-1),unsigned);
    end if;
    
    set @kode_cabang = "";
    if jenis_trans = "pembelian"
    then 
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_pembelian
		where bln_control = @bulan
		and thn_control = @tahun
		and pem_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "penjualan"
    then
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_penjualan
		where bln_control = @bulan
		and thn_control = @tahun
		and penj_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "retur"
	then
		select ifnull(max(mstr_retur.no_control),0)+1 into @nomor
        from mstr_retur
        inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
		where mstr_retur.bln_control = @bulan
		and mstr_retur.thn_control = @tahun
		and retur_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "pengiriman"
    then
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_pengiriman
		where bln_control = @bulan
		and thn_control = @tahun
		and pengiriman_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "penerimaan"
    then
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_penerimaan
		where bln_control = @bulan
		and thn_control = @tahun
		and penerimaan_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	end if;
    
    select cabang_kode into @kode_cabang
    from mstr_cabang
    where id_pk_cabang = id_cabang_in;
    
    /*select id_cabang_in;*/
    
    set latest_no := @nomor;
    set trans_no = concat(upper(@kode_cabang),"-",upper(jenis_trans),"-",@tahun,"-",lpad(@bulan,2,0),"-",lpad(@tgl,2,0),"-",lpad(@nomor,6,0));
end$$

CREATE PROCEDURE `get_minimal_rasio_anggota_kombinasi_cabang` (IN `id_barang_utama_in` INT, IN `id_fk_cabang_in` INT, OUT `new_stok_in` DOUBLE)  begin

	/*ambil rasio [stok cabang / rumusan kombinasi] terkecil dari setiap anggota kombinasi pada master kombinasi tertentu*/
	select floor(min(rasio_stok)) into new_stok_in
	from (
		select id_fk_cabang,id_barang_utama,id_fk_brg,
		brg_cabang_qty,
		barang_kombinasi_qty,
		brg_cabang_qty/barang_kombinasi_qty as rasio_stok 
		from v_brg_kombinasi_final
		left join v_brg_cabang_aktif on v_brg_cabang_aktif.id_fk_brg = v_brg_kombinasi_final.id_barang_kombinasi
		where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_fk_cabang_in
		order by id_fk_cabang,id_barang_utama,id_barang_kombinasi
		) as a
		group by id_barang_utama,id_fk_cabang
		order by id_fk_cabang,id_barang_utama;
end$$

CREATE PROCEDURE `get_minimal_rasio_anggota_kombinasi_warehouse` (IN `id_barang_utama_in` INT, IN `id_fk_warehouse_in` INT, OUT `new_stok_in` DOUBLE)  begin
	select floor(min(rasio_stok)) into new_stok_in
	from (
		select id_fk_warehouse,id_barang_utama,id_fk_brg,
		brg_warehouse_qty,
		barang_kombinasi_qty,
		brg_warehouse_qty/barang_kombinasi_qty as rasio_stok 
		from v_brg_kombinasi_final
		left join v_brg_warehouse_aktif on v_brg_warehouse_aktif.id_fk_brg = v_brg_kombinasi_final.id_barang_kombinasi
		where id_barang_utama = id_barang_utama_in and id_fk_warehouse = id_fk_warehouse_in
		order by id_fk_warehouse,id_barang_utama,id_barang_kombinasi
	) as a
	group by id_barang_utama,id_fk_warehouse
	order by id_fk_warehouse,id_barang_utama;
end$$

CREATE PROCEDURE `get_username` (IN `id_pk_user_in` INT, OUT `username` VARCHAR(100))  begin
	select ifnull(user_name,"-") into username 
    from mstr_user
    where id_pk_user = id_pk_user_in
    and user_status = 'aktif';
end$$

CREATE PROCEDURE `insert_log_all` (IN `id_user` INT, IN `log_date` DATETIME, IN `log_text` VARCHAR(1000), OUT `id_log_all` INT)  begin
	insert into log_all(id_user,log_date,log) values(id_user,log_date,log_text);
    select last_insert_id() into id_log_all ;
end$$

CREATE PROCEDURE `ubah_satuan_barang` (IN `id_satuan_in` INT, INOUT `brg_qty` DOUBLE)  begin
            declare conversion_exp varchar(20);
            select satuan_rumus 
            into conversion_exp
            from mstr_satuan
            where id_pk_satuan = id_satuan_in;
            
            set brg_qty = conversion_exp * brg_qty;
            
        end$$

CREATE PROCEDURE `update_stok_barang_cabang` (IN `id_barang` INT, IN `id_cabang` INT, IN `barang_masuk` DOUBLE, IN `id_satuan_masuk` INT, IN `barang_keluar` DOUBLE, IN `id_satuan_keluar` INT)  begin
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
            update tbl_brg_cabang 
            set brg_cabang_qty = brg_cabang_qty+barang_masuk-barang_keluar
            where id_fk_brg = id_barang and id_fk_cabang = id_cabang;

            call update_stok_kombinasi_anggota_cabang(id_barang,barang_masuk, barang_keluar, id_cabang);

        end$$

CREATE PROCEDURE `update_stok_barang_warehouse` (IN `id_barang` INT, IN `id_warehouse` INT, IN `barang_masuk` DOUBLE, IN `id_satuan_masuk` INT, IN `barang_keluar` DOUBLE, IN `id_satuan_keluar` INT)  begin
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
            call update_stok_kombinasi_anggota_warehouse(id_barang,barang_masuk, barang_keluar, id_warehouse);
        end$$

CREATE PROCEDURE `update_stok_kombinasi_anggota_cabang` (IN `id_barang_utama_in` INT, IN `qty_brg_masuk_in` DOUBLE, IN `qty_brg_keluar_in` DOUBLE, IN `id_cabang_in` INT)  begin
/*
update anggota kombinasi dari master kombinasi
*/
	update tbl_barang_kombinasi
    inner join mstr_barang as brg_utama_ctrl on brg_utama_ctrl.id_pk_brg = tbl_barang_kombinasi.id_barang_utama
	inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
	set brg_cabang_qty = brg_cabang_qty+(barang_kombinasi_qty*qty_brg_masuk_in)-(barang_kombinasi_qty*qty_brg_keluar_in)
	where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in and barang_kombinasi_status = 'aktif' and brg_utama_ctrl.brg_tipe = 'kombinasi';
end$$

CREATE PROCEDURE `update_stok_kombinasi_anggota_warehouse` (IN `id_barang_utama_in` INT, IN `qty_brg_masuk_in` DOUBLE, IN `qty_brg_keluar_in` DOUBLE, IN `id_warehouse_in` INT)  begin
            update tbl_barang_kombinasi
    inner join mstr_barang as brg_utama_ctrl on brg_utama_ctrl.id_pk_brg = tbl_barang_kombinasi.id_barang_utama
            inner join tbl_brg_warehouse on tbl_brg_warehouse.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
            set brg_warehouse_qty = brg_warehouse_qty+(barang_kombinasi_qty*qty_brg_masuk_in)-(barang_kombinasi_qty*qty_brg_keluar_in)
            where id_barang_utama = id_barang_utama_in and id_fk_warehouse = id_warehouse_in and barang_kombinasi_status = 'aktif' and brg_utama_ctrl.brg_tipe = 'kombinasi';
        end$$

CREATE PROCEDURE `update_stok_kombinasi_master_cabang` ()  begin
/*
update stok master kombinasi dengan rasio [jumlah stok / rumusan] anggota kombinasi terkecil
#related function
*get_minimal_rasio_anggota_kombinasi
*/
declare finished int default 0;
declare id_barang_utama_var int default 0;
declare id_cabang_var int default 0;
    
declare brg_kombinasi_cur cursor for 
select id_barang_utama,id_fk_cabang
from tbl_barang_kombinasi
inner join mstr_barang on mstr_barang.id_pk_brg = tbl_barang_kombinasi.id_barang_utama
inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_utama
where mstr_barang.brg_status = 'aktif' 
and tbl_barang_kombinasi.barang_kombinasi_status = 'aktif' 
and tbl_brg_cabang.brg_cabang_status = 'aktif'
and mstr_barang.brg_tipe = 'kombinasi'
group by id_barang_utama,id_fk_cabang
/*supaya urutan dari yang paling awal dibuat, hingga yang akhir dibuat sehingga apabila terdapat kombinasi yang merupakan gabungan dari kombinasi lainnya jadi bisa terupdate dahulu sehingga dapat berjalan 1x. kalau tidak diurutkan berdasarkan id_pk_brg, maka dapat saja kombinasi yang terakhir terupdate terlebih dahulu daripada anggotanya menjadi tidak akurat. prinsipnya, update anggota dahulu sampe beres, baru update kombinasi lain yang menggunakan kombinasi sebelumnya*/
order by id_pk_brg,id_fk_cabang;

declare continue handler 
for not found set finished = 1;

open brg_kombinasi_cur;
mstr_kombinasi_loop:LOOP
	fetch brg_kombinasi_cur into id_barang_utama_var,id_cabang_var;
    
	/*ambil rasio [stok cabang / rumusan kombinasi] terkecil dari setiap anggota kombinasi pada master kombinasi tertentu*/
    call get_minimal_rasio_anggota_kombinasi_cabang(id_barang_utama_var,id_cabang_var,@new_stok);
    if finished = 1 then
		leave mstr_kombinasi_loop;
	end if;
    
    /*update stok master kombinasi*/
    update tbl_brg_cabang set brg_cabang_qty = @new_stok 
    where id_fk_brg = id_barang_utama_var
    and id_fk_cabang = id_cabang_var;
    
END LOOP mstr_kombinasi_loop;
end$$

CREATE PROCEDURE `update_stok_kombinasi_master_warehouse` ()  begin

declare finished int default 0;
declare id_barang_utama_var int default 0;
declare id_warehouse_var int default 0;
    
declare brg_kombinasi_cur cursor for 
select id_barang_utama,id_fk_warehouse
from tbl_barang_kombinasi
inner join mstr_barang on mstr_barang.id_pk_brg = tbl_barang_kombinasi.id_barang_utama
inner join tbl_brg_warehouse on tbl_brg_warehouse.id_fk_brg = mstr_barang.id_pk_brg
where mstr_barang.brg_status = 'aktif' 
and tbl_barang_kombinasi.barang_kombinasi_status = 'aktif' 
and tbl_brg_warehouse.brg_warehouse_status = 'aktif'
and mstr_barang.brg_tipe = 'kombinasi'
group by id_barang_utama,id_fk_warehouse
/*supaya urutan dari yang paling awal dibuat, hingga yang akhir dibuat sehingga apabila terdapat kombinasi yang merupakan gabungan dari kombinasi lainnya jadi bisa terupdate dahulu sehingga dapat berjalan 1x. kalau tidak diurutkan berdasarkan id_pk_brg, maka dapat saja kombinasi yang terakhir terupdate terlebih dahulu daripada anggotanya menjadi tidak akurat. prinsipnya, update anggota dahulu sampe beres, baru update kombinasi lain yang menggunakan kombinasi sebelumnya*/
order by id_pk_brg,id_fk_warehouse;

declare continue handler 
for not found set finished = 1;

open brg_kombinasi_cur;
mstr_kombinasi_loop:LOOP
	fetch brg_kombinasi_cur into id_barang_utama_var,id_warehouse_var;
    
    call get_minimal_rasio_anggota_kombinasi_warehouse(id_barang_utama_var,id_warehouse_var,@new_stok);
    if finished = 1 then
		leave mstr_kombinasi_loop;
	end if;
    
    update tbl_brg_warehouse set brg_warehouse_qty = @new_stok 
    where id_fk_brg = id_barang_utama_var
    and id_fk_warehouse = id_warehouse_var;
    
END LOOP mstr_kombinasi_loop;
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `log_all`
--

CREATE TABLE `log_all` (
  `id_log_all` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `log_date` datetime DEFAULT NULL,
  `log` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang`
--

CREATE TABLE `mstr_barang` (
  `id_pk_brg` int(11) NOT NULL,
  `brg_kode` varchar(50) DEFAULT NULL,
  `brg_nama` varchar(100) DEFAULT NULL,
  `brg_ket` varchar(200) DEFAULT NULL,
  `brg_minimal` double DEFAULT NULL,
  `brg_satuan` varchar(30) DEFAULT NULL,
  `brg_image` varchar(100) DEFAULT NULL,
  `brg_harga` int(11) DEFAULT NULL,
  `brg_tipe` varchar(30) NOT NULL,
  `brg_status` varchar(15) DEFAULT NULL,
  `brg_create_date` datetime DEFAULT NULL,
  `brg_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_brg_jenis` int(11) DEFAULT NULL,
  `id_fk_brg_merk` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_barang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang` AFTER INSERT ON `mstr_barang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;
            
            call get_username(new.id_last_modified,@username);
            set @log_text = concat(@username,' insert data barang at ' , new.brg_last_modified,' nama barang terkait: ',new.brg_nama);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_log(executed_function,
            id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_tipe,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after insert',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_tipe,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang` AFTER UPDATE ON `mstr_barang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;

            call get_username(new.id_last_modified,@username);

            if new.brg_status = 'nonaktif'
            then
                set @log_text = concat(@username,' delete data barang at ',new.brg_last_modified ,' nama barang terkait: ',old.brg_nama);
                call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);

                insert into mstr_barang_log(executed_function,
                id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_tipe,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after delete',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_tipe,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
            else
                set @log_text = concat(@username,' update data barang at ',new.brg_last_modified ,' \nnama barang terkait: ',old.brg_nama,' nama barang baru: ',new.brg_nama);
                call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
                    
                insert into mstr_barang_log(executed_function,
                id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_tipe,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after update',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_tipe,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
            end if;
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang_jenis`
--

CREATE TABLE `mstr_barang_jenis` (
  `id_pk_brg_jenis` int(11) NOT NULL,
  `brg_jenis_nama` varchar(100) DEFAULT NULL,
  `brg_jenis_status` varchar(15) DEFAULT NULL,
  `brg_jenis_create_date` datetime DEFAULT NULL,
  `brg_jenis_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_barang_jenis`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang_jenis` AFTER INSERT ON `mstr_barang_jenis` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_jenis_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_jenis_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_jenis_log(executed_function,id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_create_date,brg_jenis_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_jenis,new.brg_jenis_nama,new.brg_jenis_status,new.brg_jenis_create_date,new.brg_jenis_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang_jenis` AFTER UPDATE ON `mstr_barang_jenis` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_jenis_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_jenis_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_jenis_log(executed_function,id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_create_date,brg_jenis_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_jenis,new.brg_jenis_nama,new.brg_jenis_status,new.brg_jenis_create_date,new.brg_jenis_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang_jenis_log`
--

CREATE TABLE `mstr_barang_jenis_log` (
  `id_pk_brg_jenis_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_brg_jenis` int(11) DEFAULT NULL,
  `brg_jenis_nama` varchar(100) DEFAULT NULL,
  `brg_jenis_status` varchar(15) DEFAULT NULL,
  `brg_jenis_create_date` datetime DEFAULT NULL,
  `brg_jenis_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang_log`
--

CREATE TABLE `mstr_barang_log` (
  `id_pk_brg_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_brg` int(11) DEFAULT NULL,
  `brg_kode` varchar(50) DEFAULT NULL,
  `brg_nama` varchar(100) DEFAULT NULL,
  `brg_ket` varchar(200) DEFAULT NULL,
  `brg_minimal` double DEFAULT NULL,
  `brg_satuan` varchar(30) DEFAULT NULL,
  `brg_image` varchar(100) DEFAULT NULL,
  `brg_harga` int(11) DEFAULT NULL,
  `brg_tipe` varchar(30) NOT NULL,
  `brg_status` varchar(15) DEFAULT NULL,
  `brg_create_date` datetime DEFAULT NULL,
  `brg_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_brg_jenis` int(11) DEFAULT NULL,
  `id_fk_brg_merk` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang_merk`
--

CREATE TABLE `mstr_barang_merk` (
  `id_pk_brg_merk` int(11) NOT NULL,
  `brg_merk_nama` varchar(100) DEFAULT NULL,
  `brg_merk_status` varchar(15) DEFAULT NULL,
  `brg_merk_create_date` datetime DEFAULT NULL,
  `brg_merk_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_barang_merk`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang_merk` AFTER INSERT ON `mstr_barang_merk` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_merk_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_merk_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_merk_log(executed_function,id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_create_date,brg_merk_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_merk,new.brg_merk_nama,new.brg_merk_status,new.brg_merk_create_date,new.brg_merk_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang_merk` AFTER UPDATE ON `mstr_barang_merk` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_merk_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_merk_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_merk_log(executed_function,id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_create_date,brg_merk_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_merk,new.brg_merk_nama,new.brg_merk_status,new.brg_merk_create_date,new.brg_merk_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang_merk_log`
--

CREATE TABLE `mstr_barang_merk_log` (
  `id_pk_brg_merk_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_merk` int(11) DEFAULT NULL,
  `brg_merk_nama` varchar(100) DEFAULT NULL,
  `brg_merk_status` varchar(15) DEFAULT NULL,
  `brg_merk_create_date` datetime DEFAULT NULL,
  `brg_merk_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_cabang`
--

CREATE TABLE `mstr_cabang` (
  `id_pk_cabang` int(11) NOT NULL,
  `cabang_nama` varchar(50) DEFAULT NULL,
  `cabang_kode` varchar(50) DEFAULT NULL,
  `cabang_daerah` varchar(50) DEFAULT NULL,
  `cabang_kop_surat` varchar(100) DEFAULT NULL,
  `cabang_nonpkp` varchar(100) DEFAULT NULL,
  `cabang_pernyataan_rek` varchar(100) DEFAULT NULL,
  `cabang_notelp` varchar(30) DEFAULT NULL,
  `cabang_alamat` varchar(100) DEFAULT NULL,
  `cabang_status` varchar(15) DEFAULT NULL,
  `cabang_create_date` datetime DEFAULT NULL,
  `cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_cabang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_cabang` AFTER INSERT ON `mstr_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_cabang_log(executed_function,id_pk_cabang,cabang_nama,cabang_kode,cabang_daerah,cabang_kop_surat,cabang_nonpkp,cabang_pernyataan_rek,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_create_data,id_last_modified,id_fk_toko,id_log_all) values ('after insert',new.id_pk_cabang,new.cabang_nama,new.cabang_kode,new.cabang_daerah,new.cabang_kop_surat,new.cabang_nonpkp,new.cabang_pernyataan_rek,new.cabang_notelp,new.cabang_alamat,new.cabang_status,new.cabang_create_date,new.cabang_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_toko,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_cabang_log`
--

CREATE TABLE `mstr_cabang_log` (
  `id_pk_cabang_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_cabang` int(11) DEFAULT NULL,
  `cabang_nama` varchar(50) DEFAULT NULL,
  `cabang_kode` varchar(50) DEFAULT NULL,
  `cabang_daerah` varchar(50) DEFAULT NULL,
  `cabang_kop_surat` varchar(100) DEFAULT NULL,
  `cabang_nonpkp` varchar(100) DEFAULT NULL,
  `cabang_pernyataan_rek` varchar(100) DEFAULT NULL,
  `cabang_notelp` varchar(30) DEFAULT NULL,
  `cabang_alamat` varchar(100) DEFAULT NULL,
  `cabang_status` varchar(15) DEFAULT NULL,
  `cabang_create_date` datetime DEFAULT NULL,
  `cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_customer`
--

CREATE TABLE `mstr_customer` (
  `id_pk_cust` int(11) NOT NULL,
  `cust_name` varchar(100) DEFAULT NULL,
  `cust_no_npwp` varchar(100) DEFAULT NULL,
  `cust_foto_npwp` varchar(100) DEFAULT NULL,
  `cust_foto_kartu_nama` varchar(100) DEFAULT NULL,
  `cust_badan_usaha` varchar(100) DEFAULT NULL,
  `cust_no_rekening` varchar(100) DEFAULT NULL,
  `cust_suff` varchar(10) DEFAULT NULL,
  `cust_perusahaan` varchar(100) DEFAULT NULL,
  `cust_email` varchar(100) DEFAULT NULL,
  `cust_telp` varchar(30) DEFAULT NULL,
  `cust_hp` varchar(30) DEFAULT NULL,
  `cust_alamat` varchar(150) DEFAULT NULL,
  `cust_keterangan` varchar(150) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `cust_status` varchar(15) DEFAULT NULL,
  `cust_create_date` datetime DEFAULT NULL,
  `cust_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_customer`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_customer` AFTER INSERT ON `mstr_customer` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_cust,new.cust_name,new.cust_no_npwp,new.cust_foto_npwp,new.cust_foto_kartu_nama,new.cust_badan_usaha,new.cust_no_rekening,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_customer` AFTER UPDATE ON `mstr_customer` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_no_npwp,cust_foto_npwp,cust_foto_kartu_nama,cust_badan_usaha,cust_no_rekening,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_cust,new.cust_name,new.cust_no_npwp,new.cust_foto_npwp,new.cust_foto_kartu_nama,new.cust_badan_usaha,new.cust_no_rekening,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_customer_log`
--

CREATE TABLE `mstr_customer_log` (
  `id_pk_cust_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_cust` int(11) DEFAULT NULL,
  `cust_name` varchar(100) DEFAULT NULL,
  `cust_no_npwp` varchar(100) DEFAULT NULL,
  `cust_foto_npwp` varchar(100) DEFAULT NULL,
  `cust_foto_kartu_nama` varchar(100) DEFAULT NULL,
  `cust_badan_usaha` varchar(100) DEFAULT NULL,
  `cust_no_rekening` varchar(100) DEFAULT NULL,
  `cust_suff` varchar(10) DEFAULT NULL,
  `cust_perusahaan` varchar(100) DEFAULT NULL,
  `cust_email` varchar(100) DEFAULT NULL,
  `cust_telp` varchar(30) DEFAULT NULL,
  `cust_hp` varchar(30) DEFAULT NULL,
  `cust_alamat` varchar(150) DEFAULT NULL,
  `cust_keterangan` varchar(150) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `cust_status` varchar(15) DEFAULT NULL,
  `cust_create_date` datetime DEFAULT NULL,
  `cust_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_employee`
--

CREATE TABLE `mstr_employee` (
  `id_pk_employee` int(11) NOT NULL,
  `emp_nama` varchar(400) DEFAULT NULL,
  `emp_npwp` varchar(25) DEFAULT NULL,
  `emp_ktp` varchar(20) DEFAULT NULL,
  `emp_hp` varchar(15) DEFAULT NULL,
  `emp_alamat` varchar(300) DEFAULT NULL,
  `emp_kode_pos` varchar(10) DEFAULT NULL,
  `emp_foto_npwp` varchar(50) DEFAULT NULL,
  `emp_foto_ktp` varchar(50) DEFAULT NULL,
  `emp_foto_lain` varchar(50) DEFAULT NULL,
  `emp_foto` varchar(50) DEFAULT NULL,
  `emp_gaji` int(11) DEFAULT NULL,
  `emp_startdate` datetime DEFAULT NULL,
  `emp_enddate` datetime DEFAULT NULL,
  `emp_rek` varchar(30) DEFAULT NULL,
  `emp_gender` varchar(6) DEFAULT NULL,
  `emp_suff` varchar(10) DEFAULT NULL,
  `emp_status` varchar(15) DEFAULT NULL,
  `emp_create_date` datetime DEFAULT NULL,
  `emp_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_employee`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_employee` AFTER INSERT ON `mstr_employee` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_employee` AFTER UPDATE ON `mstr_employee` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_employee_log`
--

CREATE TABLE `mstr_employee_log` (
  `id_pk_employee_log` int(11) NOT NULL,
  `executed_function` varchar(40) DEFAULT NULL,
  `id_pk_employee` int(11) DEFAULT NULL,
  `emp_nama` varchar(400) DEFAULT NULL,
  `emp_npwp` varchar(25) DEFAULT NULL,
  `emp_ktp` varchar(20) DEFAULT NULL,
  `emp_hp` varchar(15) DEFAULT NULL,
  `emp_alamat` varchar(300) DEFAULT NULL,
  `emp_kode_pos` varchar(10) DEFAULT NULL,
  `emp_foto_npwp` varchar(50) DEFAULT NULL,
  `emp_foto_ktp` varchar(50) DEFAULT NULL,
  `emp_foto_lain` varchar(50) DEFAULT NULL,
  `emp_foto` varchar(50) DEFAULT NULL,
  `emp_gaji` int(11) DEFAULT NULL,
  `emp_startdate` datetime DEFAULT NULL,
  `emp_enddate` datetime DEFAULT NULL,
  `emp_rek` varchar(30) DEFAULT NULL,
  `emp_gender` varchar(6) DEFAULT NULL,
  `emp_suff` varchar(10) DEFAULT NULL,
  `emp_status` varchar(15) DEFAULT NULL,
  `emp_create_date` datetime DEFAULT NULL,
  `emp_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_jabatan`
--

CREATE TABLE `mstr_jabatan` (
  `id_pk_jabatan` int(11) NOT NULL,
  `jabatan_nama` varchar(100) DEFAULT NULL,
  `jabatan_status` varchar(15) DEFAULT NULL,
  `jabatan_create_date` datetime DEFAULT NULL,
  `jabatan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_jabatan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_jabatan` AFTER INSERT ON `mstr_jabatan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.jabatan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.jabatan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_jabatan_log(executed_function,id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_create_date,jabatan_last_modified,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_jabatan,new.jabatan_nama,new.jabatan_status,new.jabatan_create_date,new.jabatan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

            /* insert new jabatan to all hak akses*/
            set @id_jabatan = new.id_pk_jabatan;
            insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
            select @id_jabatan,id_pk_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_menu;
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_jabatan` AFTER UPDATE ON `mstr_jabatan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.jabatan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.jabatan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_jabatan_log(executed_function,id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_create_date,jabatan_last_modified,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_jabatan,new.jabatan_nama,new.jabatan_status,new.jabatan_create_date,new.jabatan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_jabatan_log`
--

CREATE TABLE `mstr_jabatan_log` (
  `id_pk_jabatan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_jabatan` int(11) DEFAULT NULL,
  `jabatan_nama` varchar(100) DEFAULT NULL,
  `jabatan_status` varchar(15) DEFAULT NULL,
  `jabatan_create_date` datetime DEFAULT NULL,
  `jabatan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_marketplace`
--

CREATE TABLE `mstr_marketplace` (
  `id_pk_marketplace` int(11) NOT NULL,
  `marketplace_nama` varchar(100) DEFAULT NULL,
  `marketplace_ket` varchar(200) DEFAULT NULL,
  `marketplace_biaya` int(11) DEFAULT NULL,
  `marketplace_status` varchar(15) DEFAULT NULL,
  `marketplace_create_date` datetime DEFAULT NULL,
  `marketplace_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_marketplace`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_marketplace` AFTER INSERT ON `mstr_marketplace` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.marketplace_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.marketplace_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_marketplace_log(executed_function,
            id_pk_marketplace,marketplace_nama,marketplace_ket,marketplace_biaya,marketplace_status,marketplace_create_date,marketplace_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_marketplace,new.marketplace_nama,new.marketplace_ket,new.marketplace_biaya,new.marketplace_status,new.marketplace_create_date,new.marketplace_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_marketplace` AFTER UPDATE ON `mstr_marketplace` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.marketplace_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.marketplace_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_marketplace_log(executed_function,
            id_pk_marketplace,marketplace_nama,marketplace_ket,marketplace_biaya,marketplace_status,marketplace_create_date,marketplace_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_marketplace,new.marketplace_nama,new.marketplace_ket,new.marketplace_biaya,new.marketplace_status,new.marketplace_create_date,new.marketplace_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_marketplace_log`
--

CREATE TABLE `mstr_marketplace_log` (
  `id_pk_marketplace_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_marketplace` int(11) DEFAULT NULL,
  `marketplace_nama` varchar(100) DEFAULT NULL,
  `marketplace_ket` varchar(200) DEFAULT NULL,
  `marketplace_biaya` int(11) DEFAULT NULL,
  `marketplace_status` varchar(15) DEFAULT NULL,
  `marketplace_create_date` datetime DEFAULT NULL,
  `marketplace_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_menu`
--

CREATE TABLE `mstr_menu` (
  `id_pk_menu` int(11) NOT NULL,
  `menu_name` varchar(100) DEFAULT NULL,
  `menu_display` varchar(100) DEFAULT NULL,
  `menu_icon` varchar(100) DEFAULT NULL,
  `menu_category` varchar(100) DEFAULT NULL,
  `menu_status` varchar(15) DEFAULT NULL,
  `menu_create_date` datetime DEFAULT NULL,
  `menu_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_menu`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_menu` AFTER INSERT ON `mstr_menu` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.menu_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.menu_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_menu_log(executed_function,id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_create_date,menu_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_menu,new.menu_name,new.menu_display,new.menu_icon,new.menu_category,new.menu_status,new.menu_create_date,new.menu_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            /* insert new menu to all hak akses*/
            set @id_menu = new.id_pk_menu;
            insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
            select id_pk_jabatan,@id_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_jabatan;
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_menu` AFTER UPDATE ON `mstr_menu` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.menu_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.menu_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_menu_log(executed_function,id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_create_date,menu_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_menu,new.menu_name,new.menu_display,new.menu_icon,new.menu_category,new.menu_status,new.menu_create_date,new.menu_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_menu_log`
--

CREATE TABLE `mstr_menu_log` (
  `id_pk_menu_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_menu` int(11) DEFAULT NULL,
  `menu_name` varchar(100) DEFAULT NULL,
  `menu_display` varchar(100) DEFAULT NULL,
  `menu_icon` varchar(100) DEFAULT NULL,
  `menu_category` varchar(100) DEFAULT NULL,
  `menu_status` varchar(15) DEFAULT NULL,
  `menu_create_date` datetime DEFAULT NULL,
  `menu_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_pembelian`
--

CREATE TABLE `mstr_pembelian` (
  `id_pk_pembelian` int(11) NOT NULL,
  `pem_pk_nomor` varchar(100) DEFAULT NULL,
  `pem_tgl` date DEFAULT NULL,
  `pem_status` varchar(15) DEFAULT NULL,
  `id_fk_supp` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pem_create_date` datetime DEFAULT NULL,
  `pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `no_control` int(11) DEFAULT NULL COMMENT 'untuk tau udah nomor berapa untuk penomoran',
  `bln_control` int(11) DEFAULT NULL,
  `thn_control` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_pembelian`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pembelian` AFTER INSERT ON `mstr_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pembelian_log(executed_function,id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,id_fk_supp,id_fk_cabang,pem_create_date,pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_pembelian,new.pem_pk_nomor,new.pem_tgl,new.pem_status,new.id_fk_supp,new.id_fk_cabang,new.pem_create_date,new.pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_pembelian` AFTER UPDATE ON `mstr_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pembelian_log(executed_function,id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,id_fk_supp,id_fk_cabang,pem_create_date,pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_pembelian,new.pem_pk_nomor,new.pem_tgl,new.pem_status,new.id_fk_supp,new.id_fk_cabang,new.pem_create_date,new.pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_pembelian_log`
--

CREATE TABLE `mstr_pembelian_log` (
  `id_pk_pembelian_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_pembelian` int(11) DEFAULT NULL,
  `pem_pk_nomor` varchar(100) DEFAULT NULL,
  `pem_tgl` date DEFAULT NULL,
  `pem_status` varchar(15) DEFAULT NULL,
  `id_fk_supp` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pem_create_date` datetime DEFAULT NULL,
  `pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penawaran`
--

CREATE TABLE `mstr_penawaran` (
  `id_pk_penawaran` int(11) NOT NULL,
  `penawaran_subject` varchar(100) DEFAULT NULL,
  `penawaran_content` varchar(100) DEFAULT NULL,
  `penawaran_notes` varchar(100) DEFAULT NULL,
  `penawaran_file` varchar(100) DEFAULT NULL,
  `penawaran_tgl` datetime DEFAULT NULL,
  `penawaran_refrensi` varchar(100) DEFAULT NULL,
  `penawaran_status` varchar(30) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penawaran_create_date` datetime DEFAULT NULL,
  `penawaran_last_modified` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_penawaran`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penawaran` AFTER INSERT ON `mstr_penawaran` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penawaran_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penawaran_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penawaran_log(executed_function,id_pk_penawaran,penawaran_subject,penawaran_content,penawaran_notes,penawaran_file,penawaran_tgl,penawaran_refrensi,penawaran_status,id_fk_cabang,penawaran_create_date,penawaran_last_modified,id_create_date,id_last_modified,id_log_all) values ('after insert',new.id_pk_penawaran,new.penawaran_subject,new.penawaran_content,new.penawaran_notes,new.penawaran_file,new.penawaran_tgl,new.penawaran_refrensi,new.penawaran_status,new.id_fk_cabang,new.penawaran_create_date,new.penawaran_last_modified,new.id_create_date,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penawaran` AFTER UPDATE ON `mstr_penawaran` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penawaran_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penawaran_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penawaran_log(executed_function,id_pk_penawaran,penawaran_subject,penawaran_content,penawaran_notes,penawaran_file,penawaran_tgl,penawaran_refrensi,penawaran_status,id_fk_cabang,penawaran_create_date,penawaran_last_modified,id_create_date,id_last_modified,id_log_all) values ('after update',new.id_pk_penawaran,new.penawaran_subject,new.penawaran_content,new.penawaran_notes,new.penawaran_file,new.penawaran_tgl,new.penawaran_refrensi,new.penawaran_status,new.id_fk_cabang,new.penawaran_create_date,new.penawaran_last_modified,new.id_create_date,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penawaran_log`
--

CREATE TABLE `mstr_penawaran_log` (
  `id_pk_penawaran_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penawaran` int(11) DEFAULT NULL,
  `penawaran_subject` varchar(100) DEFAULT NULL,
  `penawaran_content` varchar(100) DEFAULT NULL,
  `penawaran_notes` varchar(100) DEFAULT NULL,
  `penawaran_file` varchar(100) DEFAULT NULL,
  `penawaran_tgl` datetime DEFAULT NULL,
  `penawaran_refrensi` varchar(100) DEFAULT NULL,
  `penawaran_status` varchar(30) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penawaran_create_date` datetime DEFAULT NULL,
  `penawaran_last_modified` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penerimaan`
--

CREATE TABLE `mstr_penerimaan` (
  `id_pk_penerimaan` int(11) NOT NULL,
  `penerimaan_tgl` datetime DEFAULT NULL,
  `penerimaan_status` varchar(15) DEFAULT NULL,
  `penerimaan_tipe` varchar(30) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `penerimaan_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penerimaan_create_date` datetime DEFAULT NULL,
  `penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `no_control` int(11) DEFAULT NULL COMMENT 'untuk tau udah nomor berapa untuk penomoran',
  `bln_control` int(11) DEFAULT NULL,
  `thn_control` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_penerimaan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penerimaan` AFTER INSERT ON `mstr_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penerimaan_log(executed_function,id_pk_penerimaan,penerimaan_tgl,penerimaan_status,penerimaan_tipe,id_fk_retur,id_fk_pembelian,penerimaan_tempat,id_fk_warehouse,id_fk_cabang,penerimaan_create_date,penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penerimaan,new.penerimaan_tgl,new.penerimaan_status,new.penerimaan_tipe,new.id_fk_retur,new.id_fk_pembelian,new.penerimaan_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.penerimaan_create_date,new.penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penerimaan` AFTER UPDATE ON `mstr_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penerimaan_log(executed_function,id_pk_penerimaan,penerimaan_tgl,penerimaan_status,penerimaan_tipe,id_fk_retur,id_fk_pembelian,penerimaan_tempat,id_fk_warehouse,id_fk_cabang,penerimaan_create_date,penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penerimaan,new.penerimaan_tgl,new.penerimaan_status,new.penerimaan_tipe,new.id_fk_retur,new.id_fk_pembelian,new.penerimaan_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.penerimaan_create_date,new.penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penerimaan_log`
--

CREATE TABLE `mstr_penerimaan_log` (
  `id_pk_penerimaan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penerimaan` int(11) DEFAULT NULL,
  `penerimaan_tgl` datetime DEFAULT NULL,
  `penerimaan_status` varchar(15) DEFAULT NULL,
  `penerimaan_tipe` varchar(30) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `penerimaan_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penerimaan_create_date` datetime DEFAULT NULL,
  `penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_pengiriman`
--

CREATE TABLE `mstr_pengiriman` (
  `id_pk_pengiriman` int(11) NOT NULL,
  `pengiriman_no` varchar(50) DEFAULT NULL,
  `pengiriman_tgl` datetime DEFAULT NULL,
  `pengiriman_status` varchar(15) DEFAULT NULL,
  `pengiriman_tipe` varchar(30) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `pengiriman_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pengiriman_create_date` datetime DEFAULT NULL,
  `pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `no_control` int(11) DEFAULT NULL,
  `bln_control` int(11) DEFAULT NULL,
  `thn_control` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_pengiriman`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pengiriman` AFTER INSERT ON `mstr_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pengiriman_log(executed_function,id_pk_pengiriman,pengiriman_tgl,pengiriman_status,pengiriman_tipe,id_fk_penjualan,id_fk_retur,pengiriman_tempat,id_fk_warehouse,id_fk_cabang,pengiriman_create_date,pengiriman_last_modified,id_create_data,id_last_modified,pengiriman_no,no_control,bln_control,thn_control,id_log_all) values ('after insert',new.id_pk_pengiriman,new.pengiriman_tgl,new.pengiriman_status,new.pengiriman_tipe,new.id_fk_penjualan,new.id_fk_retur,new.pengiriman_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.pengiriman_create_date,new.pengiriman_last_modified,new.id_create_data,new.id_last_modified,new.pengiriman_no,new.no_control,new.bln_control,new.thn_control,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_pengiriman` AFTER UPDATE ON `mstr_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pengiriman_log(executed_function,id_pk_pengiriman,pengiriman_tgl,pengiriman_status,pengiriman_tipe,id_fk_penjualan,id_fk_retur,pengiriman_tempat,id_fk_warehouse,id_fk_cabang,pengiriman_create_date,pengiriman_last_modified,id_create_data,id_last_modified,pengiriman_no,no_control,bln_control,thn_control,id_log_all) values ('after update',new.id_pk_pengiriman,new.pengiriman_tgl,new.pengiriman_status,new.pengiriman_tipe,new.id_fk_penjualan,new.id_fk_retur,new.pengiriman_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.pengiriman_create_date,new.pengiriman_last_modified,new.id_create_data,new.id_last_modified,new.pengiriman_no,new.no_control,new.bln_control,new.thn_control,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_pengiriman_log`
--

CREATE TABLE `mstr_pengiriman_log` (
  `id_pk_pengiriman_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_pengiriman` int(11) DEFAULT NULL,
  `pengiriman_no` varchar(50) DEFAULT NULL,
  `pengiriman_tgl` datetime DEFAULT NULL,
  `pengiriman_status` varchar(15) DEFAULT NULL,
  `pengiriman_tipe` varchar(30) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `pengiriman_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pengiriman_create_date` datetime DEFAULT NULL,
  `pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `no_control` int(11) DEFAULT NULL,
  `bln_control` int(11) DEFAULT NULL,
  `thn_control` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penjualan`
--

CREATE TABLE `mstr_penjualan` (
  `id_pk_penjualan` int(11) NOT NULL,
  `penj_nomor` varchar(100) DEFAULT NULL,
  `penj_tgl` datetime DEFAULT NULL,
  `penj_dateline_tgl` datetime DEFAULT NULL,
  `penj_jenis` varchar(50) DEFAULT NULL,
  `penj_tipe_pembayaran` varchar(50) DEFAULT NULL,
  `penj_status` varchar(15) DEFAULT NULL,
  `id_fk_customer` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penj_create_date` datetime DEFAULT NULL,
  `penj_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `no_control` int(11) DEFAULT NULL COMMENT 'untuk tau udah nomor berapa untuk penomoran',
  `bln_control` int(11) DEFAULT NULL,
  `thn_control` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penjualan` AFTER INSERT ON `mstr_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan,new.penj_nomor,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penjualan` AFTER UPDATE ON `mstr_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan,new.penj_nomor,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penjualan_log`
--

CREATE TABLE `mstr_penjualan_log` (
  `id_pk_penjualan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penjualan` int(11) DEFAULT NULL,
  `penj_nomor` varchar(30) DEFAULT NULL,
  `penj_tgl` datetime DEFAULT NULL,
  `penj_dateline_tgl` datetime DEFAULT NULL,
  `penj_jenis` varchar(50) DEFAULT NULL,
  `penj_tipe_pembayaran` varchar(50) DEFAULT NULL,
  `penj_status` varchar(15) DEFAULT NULL,
  `id_fk_customer` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penj_create_date` datetime DEFAULT NULL,
  `penj_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_retur`
--

CREATE TABLE `mstr_retur` (
  `id_pk_retur` int(11) NOT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `retur_no` varchar(100) DEFAULT NULL,
  `retur_tgl` datetime DEFAULT NULL,
  `retur_tipe` varchar(15) DEFAULT NULL,
  `retur_status` varchar(100) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `no_control` int(11) DEFAULT NULL COMMENT 'untuk tau udah nomor berapa untuk penomoran',
  `bln_control` int(11) DEFAULT NULL,
  `thn_control` int(11) DEFAULT NULL,
  `retur_confirm_date` datetime DEFAULT NULL,
  `id_retur_confirm` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_retur`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_retur` AFTER INSERT ON `mstr_retur` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_retur_log(executed_function,id_pk_retur,id_fk_penjualan,retur_no,retur_tgl,retur_tipe,retur_status,retur_create_date,retur_last_modified,retur_confirm_date,id_retur_confirm,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_retur,new.id_fk_penjualan,new.retur_no,new.retur_tgl,new.retur_tipe,new.retur_status,new.retur_create_date,new.retur_last_modified,new.retur_confirm_date,new.id_retur_confirm,new.id_create_data,new.id_last_modified,@id_log_all);

        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_retur` AFTER UPDATE ON `mstr_retur` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_retur_log(executed_function,id_pk_retur,id_fk_penjualan,retur_no,retur_tgl,retur_tipe,retur_status,retur_create_date,retur_last_modified,retur_confirm_date,id_retur_confirm,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_retur,new.id_fk_penjualan,new.retur_no,new.retur_tgl,new.retur_tipe,new.retur_status,new.retur_create_date,new.retur_last_modified,new.retur_confirm_date,new.id_retur_confirm,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_retur_log`
--

CREATE TABLE `mstr_retur_log` (
  `id_pk_retur_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_retur` int(11) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `retur_no` varchar(100) DEFAULT NULL,
  `retur_tgl` datetime DEFAULT NULL,
  `retur_tipe` varchar(15) DEFAULT NULL,
  `retur_status` varchar(100) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL,
  `retur_confirm_date` datetime DEFAULT NULL,
  `id_retur_confirm` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_satuan`
--

CREATE TABLE `mstr_satuan` (
  `id_pk_satuan` int(11) NOT NULL,
  `satuan_nama` varchar(100) DEFAULT NULL,
  `satuan_rumus` varchar(100) DEFAULT NULL,
  `satuan_status` varchar(15) DEFAULT NULL,
  `satuan_create_date` datetime DEFAULT NULL,
  `satuan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_satuan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_satuan` AFTER INSERT ON `mstr_satuan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.satuan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.satuan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_satuan_log(executed_function,id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_create_date,satuan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_satuan,new.satuan_nama,new.satuan_status,new.satuan_rumus,new.satuan_create_date,new.satuan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_satuan` AFTER UPDATE ON `mstr_satuan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.satuan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.satuan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_satuan_log(executed_function,id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_create_date,satuan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_satuan,new.satuan_nama,new.satuan_status,new.satuan_rumus,new.satuan_create_date,new.satuan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_satuan_log`
--

CREATE TABLE `mstr_satuan_log` (
  `id_pk_satuan_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_satuan` int(11) DEFAULT NULL,
  `satuan_nama` varchar(100) DEFAULT NULL,
  `satuan_rumus` varchar(100) DEFAULT NULL,
  `satuan_status` varchar(15) DEFAULT NULL,
  `satuan_create_date` datetime DEFAULT NULL,
  `satuan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_stock_opname`
--

CREATE TABLE `mstr_stock_opname` (
  `ID_PK_STOCK_OPNAME` int(11) NOT NULL,
  `SO_TGL` datetime DEFAULT NULL,
  `SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_TOKO` int(11) DEFAULT NULL,
  `ID_EMP_DET` int(11) DEFAULT NULL,
  `SO_CREATE_DATE` datetime DEFAULT NULL,
  `SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_stock_opname`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_STOCK_OPNAME` AFTER INSERT ON `mstr_stock_opname` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_STOCK_OPNAME_LOG(EXECUTED_FUNCTION,ID_PK_STOCK_OPNAME,SO_TGL,SO_NOTES,ID_FK_TOKO,ID_EMP_DET,SO_CREATE_DATE,SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_STOCK_OPNAME,NEW.SO_TGL,NEW.SO_NOTES,NEW.ID_FK_TOKO,NEW.ID_EMP_DET,NEW.SO_CREATE_DATE,NEW.SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_STOCK_OPNAME` AFTER UPDATE ON `mstr_stock_opname` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_STOCK_OPNAME_LOG(EXECUTED_FUNCTION,ID_PK_STOCK_OPNAME,SO_TGL,SO_NOTES,ID_FK_TOKO,ID_EMP_DET,SO_CREATE_DATE,SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_STOCK_OPNAME,NEW.SO_TGL,NEW.SO_NOTES,NEW.ID_FK_TOKO,NEW.ID_EMP_DET,NEW.SO_CREATE_DATE,NEW.SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_stock_opname_log`
--

CREATE TABLE `mstr_stock_opname_log` (
  `ID_PK_STOCK_OPNAME_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `SO_TGL` datetime DEFAULT NULL,
  `SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_TOKO` int(11) DEFAULT NULL,
  `ID_EMP_DET` int(11) DEFAULT NULL,
  `SO_CREATE_DATE` datetime DEFAULT NULL,
  `SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_supplier`
--

CREATE TABLE `mstr_supplier` (
  `id_pk_sup` int(11) NOT NULL,
  `sup_nama` varchar(100) DEFAULT NULL,
  `sup_no_npwp` varchar(100) DEFAULT NULL,
  `sup_foto_npwp` varchar(100) DEFAULT NULL,
  `sup_foto_kartu_nama` varchar(100) DEFAULT NULL,
  `sup_badan_usaha` varchar(100) DEFAULT NULL,
  `sup_no_rekening` varchar(100) DEFAULT NULL,
  `sup_suff` varchar(10) DEFAULT NULL,
  `sup_perusahaan` varchar(100) DEFAULT NULL,
  `sup_email` varchar(100) DEFAULT NULL,
  `sup_telp` varchar(30) DEFAULT NULL,
  `sup_hp` varchar(30) DEFAULT NULL,
  `sup_alamat` varchar(150) DEFAULT NULL,
  `sup_keterangan` varchar(150) DEFAULT NULL,
  `sup_status` varchar(15) DEFAULT NULL,
  `sup_create_date` datetime DEFAULT NULL,
  `sup_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_supplier`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_supplier` AFTER INSERT ON `mstr_supplier` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sup_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.sup_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_supplier_log(executed_function,id_pk_sup,sup_nama,sup_no_npwp,sup_foto_npwp,sup_foto_kartu_nama,sup_badan_usaha,sup_no_rekening,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_create_date,sup_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_sup,new.sup_nama,new.sup_no_npwp,new.sup_foto_npwp,new.sup_foto_kartu_nama,new.sup_badan_usaha,new.sup_no_rekening,new.sup_suff,new.sup_perusahaan,new.sup_email,new.sup_telp,new.sup_hp,new.sup_alamat,new.sup_keterangan,new.sup_status,new.sup_create_date,new.sup_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_supplier` AFTER UPDATE ON `mstr_supplier` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sup_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.sup_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_supplier_log(executed_function,id_pk_sup,sup_nama,sup_no_npwp,sup_foto_npwp,sup_foto_kartu_nama,sup_badan_usaha,sup_no_rekening,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_create_date,sup_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_sup,new.sup_nama,new.sup_no_npwp,new.sup_foto_npwp,new.sup_foto_kartu_nama,new.sup_badan_usaha,new.sup_no_rekening,new.sup_suff,new.sup_perusahaan,new.sup_email,new.sup_telp,new.sup_hp,new.sup_alamat,new.sup_keterangan,new.sup_status,new.sup_create_date,new.sup_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_supplier_log`
--

CREATE TABLE `mstr_supplier_log` (
  `id_pk_sup_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_sup` int(11) DEFAULT NULL,
  `sup_nama` varchar(100) DEFAULT NULL,
  `sup_no_npwp` varchar(100) DEFAULT NULL,
  `sup_foto_npwp` varchar(100) DEFAULT NULL,
  `sup_foto_kartu_nama` varchar(100) DEFAULT NULL,
  `sup_badan_usaha` varchar(100) DEFAULT NULL,
  `sup_no_rekening` varchar(100) DEFAULT NULL,
  `sup_suff` varchar(10) DEFAULT NULL,
  `sup_perusahaan` varchar(100) DEFAULT NULL,
  `sup_email` varchar(100) DEFAULT NULL,
  `sup_telp` varchar(30) DEFAULT NULL,
  `sup_hp` varchar(30) DEFAULT NULL,
  `sup_alamat` varchar(150) DEFAULT NULL,
  `sup_keterangan` varchar(150) DEFAULT NULL,
  `sup_status` varchar(15) DEFAULT NULL,
  `sup_create_date` datetime DEFAULT NULL,
  `sup_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_surat_jalan`
--

CREATE TABLE `mstr_surat_jalan` (
  `ID_PK_SURAT_JALAN` int(11) NOT NULL,
  `SJ_NOMOR` varchar(30) DEFAULT NULL,
  `SJ_TGL` datetime DEFAULT NULL,
  `SJ_PENERIMA` varchar(100) DEFAULT NULL,
  `SJ_PENGIRIM` varchar(100) DEFAULT NULL,
  `SJ_ACC` varchar(50) DEFAULT NULL,
  `SJ_NOTE` varchar(150) DEFAULT NULL,
  `SJ_NO_PENJUALAN` varchar(100) DEFAULT NULL,
  `SJ_JMLH_ITEM` double DEFAULT NULL,
  `SJ_TUJUAN` varchar(150) DEFAULT NULL,
  `SJ_ALAMAT` varchar(150) DEFAULT NULL,
  `SJ_STATUS` varchar(15) DEFAULT NULL,
  `ID_FK_PENJUALAN` int(11) DEFAULT NULL,
  `SJ_CREATE_DATE` datetime DEFAULT NULL,
  `SJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_surat_jalan`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_SURAT_JALAN` AFTER INSERT ON `mstr_surat_jalan` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_SURAT_JALAN_LOG(EXECUTED_FUNCTION,ID_PK_SURAT_JALAN,SJ_NOMOR,SJ_TGL,SJ_PENERIMA,SJ_PENGIRIM,SJ_ACC,SJ_NOTE,SJ_NO_PENJUALAN,SJ_JMLH_ITEM,SJ_TUJUAN,SJ_ALAMAT,SJ_STATUS,ID_FK_PENJUALAN,SJ_CREATE_DATE,SJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SURAT_JALAN,NEW.SJ_NOMOR,NEW.SJ_TGL,NEW.SJ_PENERIMA,NEW.SJ_PENGIRIM,NEW.SJ_ACC,NEW.SJ_NOTE,NEW.SJ_NO_PENJUALAN,NEW.SJ_JMLH_ITEM,NEW.SJ_TUJUAN,NEW.SJ_ALAMAT,NEW.SJ_STATUS,NEW.ID_FK_PENJUALAN,NEW.SJ_CREATE_DATE,NEW.SJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_SURAT_JALAN` AFTER UPDATE ON `mstr_surat_jalan` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_SURAT_JALAN_LOG(EXECUTED_FUNCTION,ID_PK_SURAT_JALAN,SJ_NOMOR,SJ_TGL,SJ_PENERIMA,SJ_PENGIRIM,SJ_ACC,SJ_NOTE,SJ_NO_PENJUALAN,SJ_JMLH_ITEM,SJ_TUJUAN,SJ_ALAMAT,SJ_STATUS,ID_FK_PENJUALAN,SJ_CREATE_DATE,SJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SURAT_JALAN,NEW.SJ_NOMOR,NEW.SJ_TGL,NEW.SJ_PENERIMA,NEW.SJ_PENGIRIM,NEW.SJ_ACC,NEW.SJ_NOTE,NEW.SJ_NO_PENJUALAN,NEW.SJ_JMLH_ITEM,NEW.SJ_TUJUAN,NEW.SJ_ALAMAT,NEW.SJ_STATUS,NEW.ID_FK_PENJUALAN,NEW.SJ_CREATE_DATE,NEW.SJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_surat_jalan_log`
--

CREATE TABLE `mstr_surat_jalan_log` (
  `ID_PK_SURAT_JALAN_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_SURAT_JALAN` int(11) DEFAULT NULL,
  `SJ_NOMOR` varchar(30) DEFAULT NULL,
  `SJ_TGL` datetime DEFAULT NULL,
  `SJ_PENERIMA` varchar(100) DEFAULT NULL,
  `SJ_PENGIRIM` varchar(100) DEFAULT NULL,
  `SJ_ACC` varchar(50) DEFAULT NULL,
  `SJ_NOTE` varchar(150) DEFAULT NULL,
  `SJ_NO_PENJUALAN` varchar(100) DEFAULT NULL,
  `SJ_JMLH_ITEM` double DEFAULT NULL,
  `SJ_TUJUAN` varchar(150) DEFAULT NULL,
  `SJ_ALAMAT` varchar(150) DEFAULT NULL,
  `SJ_STATUS` varchar(15) DEFAULT NULL,
  `ID_FK_PENJUALAN` int(11) DEFAULT NULL,
  `SJ_CREATE_DATE` datetime DEFAULT NULL,
  `SJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_toko`
--

CREATE TABLE `mstr_toko` (
  `id_pk_toko` int(11) NOT NULL,
  `toko_logo` varchar(100) DEFAULT NULL,
  `toko_nama` varchar(100) DEFAULT NULL,
  `toko_kop_surat` varchar(100) DEFAULT NULL,
  `toko_nonpkp` varchar(100) DEFAULT NULL,
  `toko_pernyataan_rek` varchar(100) DEFAULT NULL,
  `toko_ttd` varchar(100) DEFAULT NULL,
  `toko_kode` varchar(20) DEFAULT NULL,
  `toko_status` varchar(15) DEFAULT NULL,
  `toko_create_date` datetime DEFAULT NULL,
  `toko_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_toko`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_toko` AFTER INSERT ON `mstr_toko` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_logo,toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,toko_ttd,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_toko,new.toko_logo,new.toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,new.toko_ttd,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_toko` AFTER UPDATE ON `mstr_toko` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_logo,toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,toko_ttd,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_toko,new.toko_logo,new.toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,new.toko_ttd,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_toko_log`
--

CREATE TABLE `mstr_toko_log` (
  `id_pk_toko_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_toko` int(11) DEFAULT NULL,
  `toko_logo` varchar(100) DEFAULT NULL,
  `toko_nama` varchar(100) DEFAULT NULL,
  `toko_kop_surat` varchar(100) DEFAULT NULL,
  `toko_nonpkp` varchar(100) DEFAULT NULL,
  `toko_pernyataan_rek` varchar(100) DEFAULT NULL,
  `toko_ttd` varchar(100) DEFAULT NULL,
  `toko_kode` varchar(20) DEFAULT NULL,
  `toko_status` varchar(15) DEFAULT NULL,
  `toko_create_date` datetime DEFAULT NULL,
  `toko_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_user`
--

CREATE TABLE `mstr_user` (
  `id_pk_user` int(11) NOT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `user_pass` varchar(200) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_status` varchar(15) DEFAULT NULL,
  `id_fk_role` int(11) DEFAULT NULL,
  `id_fk_employee` int(11) DEFAULT NULL,
  `user_last_modified` datetime DEFAULT NULL,
  `user_create_date` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `mstr_user`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_user` AFTER INSERT ON `mstr_user` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at',' ', new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,id_fk_employee,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after insert',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.id_fk_employee,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_user` AFTER UPDATE ON `mstr_user` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,id_fk_employee,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after update',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.id_fk_employee,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_user_log`
--

CREATE TABLE `mstr_user_log` (
  `id_pk_user_log` int(11) NOT NULL,
  `executed_function` varchar(40) DEFAULT NULL,
  `id_pk_user` int(11) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `user_pass` varchar(200) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_status` varchar(15) DEFAULT NULL,
  `id_fk_role` int(11) DEFAULT NULL,
  `id_fk_employee` int(11) DEFAULT NULL,
  `user_last_modified` datetime DEFAULT NULL,
  `user_create_date` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_warehouse`
--

CREATE TABLE `mstr_warehouse` (
  `id_pk_warehouse` int(11) NOT NULL,
  `warehouse_nama` varchar(100) DEFAULT NULL,
  `warehouse_alamat` varchar(200) DEFAULT NULL,
  `warehouse_notelp` varchar(30) DEFAULT NULL,
  `warehouse_desc` varchar(150) DEFAULT NULL,
  `warehouse_status` varchar(15) DEFAULT NULL,
  `warehouse_create_date` datetime DEFAULT NULL,
  `warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `mstr_warehouse`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_warehouse` AFTER INSERT ON `mstr_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_warehouse_log(executed_function,id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_create_date,warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_warehouse,new.warehouse_nama,new.warehouse_alamat,new.warehouse_notelp,new.warehouse_desc,new.warehouse_status,new.warehouse_create_date,new.warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_warehouse` AFTER UPDATE ON `mstr_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_warehouse_log(executed_function,id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_create_date,warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_warehouse,new.warehouse_nama,new.warehouse_alamat,new.warehouse_notelp,new.warehouse_desc,new.warehouse_status,new.warehouse_create_date,new.warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mstr_warehouse_log`
--

CREATE TABLE `mstr_warehouse_log` (
  `id_pk_warehouse_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_warehouse` int(11) DEFAULT NULL,
  `warehouse_nama` varchar(100) DEFAULT NULL,
  `warehouse_alamat` varchar(200) DEFAULT NULL,
  `warehouse_notelp` varchar(30) DEFAULT NULL,
  `warehouse_desc` varchar(150) DEFAULT NULL,
  `warehouse_status` varchar(15) DEFAULT NULL,
  `warehouse_create_date` datetime DEFAULT NULL,
  `warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_barang_kombinasi`
--

CREATE TABLE `tbl_barang_kombinasi` (
  `id_pk_barang_kombinasi` int(11) NOT NULL,
  `id_barang_utama` int(11) DEFAULT NULL,
  `id_barang_kombinasi` int(11) DEFAULT NULL,
  `barang_kombinasi_qty` double DEFAULT NULL,
  `barang_kombinasi_status` varchar(15) DEFAULT NULL,
  `barang_kombinasi_create_date` datetime DEFAULT NULL,
  `barang_kombinasi_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_barang_kombinasi`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang_kombinasi` AFTER INSERT ON `tbl_barang_kombinasi` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang_kombinasi` AFTER UPDATE ON `tbl_barang_kombinasi` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_barang_kombinasi_log`
--

CREATE TABLE `tbl_barang_kombinasi_log` (
  `id_pk_barang_kombinasi_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_barang_kombinasi` int(11) DEFAULT NULL,
  `id_barang_utama` int(11) DEFAULT NULL,
  `id_barang_kombinasi` int(11) DEFAULT NULL,
  `barang_kombinasi_qty` double DEFAULT NULL,
  `barang_kombinasi_status` varchar(15) DEFAULT NULL,
  `barang_kombinasi_create_date` datetime DEFAULT NULL,
  `barang_kombinasi_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_barang_ukuran`
--

CREATE TABLE `tbl_barang_ukuran` (
  `ID_PK_BARANG_UKURAN` int(11) NOT NULL,
  `ID_FK_BARANG` int(11) DEFAULT NULL,
  `UKURAN` varchar(10) DEFAULT NULL,
  `BRG_UKURAN_STATUS` varchar(15) DEFAULT NULL,
  `BRG_UKURAN_CREATE_DATE` datetime DEFAULT NULL,
  `BRG_UKURAN_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATE` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_cabang`
--

CREATE TABLE `tbl_brg_cabang` (
  `id_pk_brg_cabang` int(11) NOT NULL,
  `brg_cabang_qty` int(11) DEFAULT NULL,
  `brg_cabang_notes` varchar(200) DEFAULT NULL,
  `brg_cabang_status` varchar(15) DEFAULT NULL,
  `brg_cabang_last_price` int(11) DEFAULT 0,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_cabang_create_date` datetime DEFAULT NULL,
  `brg_cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_brg_cabang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_cabang` AFTER INSERT ON `tbl_brg_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_cabang_log(executed_function,id_pk_brg_cabang,brg_cabang_qty,brg_cabang_last_price,brg_cabang_notes,brg_cabang_status,id_fk_brg,id_fk_cabang,brg_cabang_create_date,brg_cabang_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_cabang,new.brg_cabang_last_price,new.brg_cabang_qty,new.brg_cabang_notes,new.brg_cabang_status,new.id_fk_brg,new.id_fk_cabang,new.brg_cabang_create_date,new.brg_cabang_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_cabang` AFTER UPDATE ON `tbl_brg_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_cabang_log(executed_function,id_pk_brg_cabang,brg_cabang_qty,brg_cabang_last_price,brg_cabang_notes,brg_cabang_status,id_fk_brg,id_fk_cabang,brg_cabang_create_date,brg_cabang_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_cabang,new.brg_cabang_last_price,new.brg_cabang_qty,new.brg_cabang_notes,new.brg_cabang_status,new.id_fk_brg,new.id_fk_cabang,new.brg_cabang_create_date,new.brg_cabang_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_cabang_log`
--

CREATE TABLE `tbl_brg_cabang_log` (
  `id_pk_brg_cabang_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_cabang` int(11) DEFAULT NULL,
  `brg_cabang_qty` int(11) DEFAULT NULL,
  `brg_cabang_last_price` int(11) DEFAULT 0,
  `brg_cabang_notes` varchar(200) DEFAULT NULL,
  `brg_cabang_status` varchar(15) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_cabang_create_date` datetime DEFAULT NULL,
  `brg_cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pembelian`
--

CREATE TABLE `tbl_brg_pembelian` (
  `id_pk_brg_pembelian` int(11) NOT NULL,
  `brg_pem_qty` double DEFAULT NULL,
  `brg_pem_satuan` varchar(20) DEFAULT NULL,
  `brg_pem_harga` int(11) DEFAULT NULL,
  `brg_pem_note` varchar(150) DEFAULT NULL,
  `brg_pem_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_pem_create_date` datetime DEFAULT NULL,
  `brg_pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `tbl_brg_pembelian`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pembelian` AFTER INSERT ON `tbl_brg_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pembelian_log(executed_function,id_pk_brg_pembelian,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_pem_status,id_fk_pembelian,id_fk_barang,brg_pem_create_date,brg_pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pembelian,new.brg_pem_qty,new.brg_pem_satuan,new.brg_pem_harga,new.brg_pem_note,new.brg_pem_status,new.id_fk_pembelian,new.id_fk_barang,new.brg_pem_create_date,new.brg_pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pembelian` AFTER UPDATE ON `tbl_brg_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pembelian_log(executed_function,id_pk_brg_pembelian,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_pem_status,id_fk_pembelian,id_fk_barang,brg_pem_create_date,brg_pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pembelian,new.brg_pem_qty,new.brg_pem_satuan,new.brg_pem_harga,new.brg_pem_note,new.brg_pem_status,new.id_fk_pembelian,new.id_fk_barang,new.brg_pem_create_date,new.brg_pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pembelian_log`
--

CREATE TABLE `tbl_brg_pembelian_log` (
  `id_pk_brg_pembelian_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pembelian` int(11) DEFAULT NULL,
  `brg_pem_qty` double DEFAULT NULL,
  `brg_pem_satuan` varchar(20) DEFAULT NULL,
  `brg_pem_harga` int(11) DEFAULT NULL,
  `brg_pem_note` varchar(150) DEFAULT NULL,
  `brg_pem_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_pem_create_date` datetime DEFAULT NULL,
  `brg_pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pemenuhan`
--

CREATE TABLE `tbl_brg_pemenuhan` (
  `id_pk_brg_pemenuhan` int(11) NOT NULL,
  `brg_pemenuhan_qty` int(11) DEFAULT NULL,
  `brg_pemenuhan_tipe` varchar(9) DEFAULT NULL COMMENT 'warehouse/cabang',
  `brg_pemenuhan_status` varchar(30) DEFAULT NULL COMMENT 'aktif/nonaktif',
  `id_fk_brg_permintaan` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_pemenuhan_create_date` datetime DEFAULT NULL,
  `brg_pemenuhan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_brg_pemenuhan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pemenuhan` AFTER INSERT ON `tbl_brg_pemenuhan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pemenuhan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_pemenuhan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pemenuhan_log(executed_function,
            id_pk_brg_pemenuhan,
            brg_pemenuhan_qty,
            brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            id_fk_brg_permintaan,
            id_fk_cabang,
            id_fk_warehouse,
            brg_pemenuhan_create_date,
            brg_pemenuhan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_pemenuhan,
            new.brg_pemenuhan_qty,
            new.brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            new.id_fk_brg_permintaan,
            new.id_fk_cabang,
            new.id_fk_warehouse,
            new.brg_pemenuhan_create_date,
            new.brg_pemenuhan_last_modified,
            new.id_create_data,
            new.id_last_modified
            ,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pemenuhan` AFTER UPDATE ON `tbl_brg_pemenuhan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pemenuhan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_pemenuhan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pemenuhan_log(executed_function,
            id_pk_brg_pemenuhan,
            brg_pemenuhan_qty,
            brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            id_fk_brg_permintaan,
            id_fk_cabang,
            id_fk_warehouse,
            brg_pemenuhan_create_date,
            brg_pemenuhan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_pemenuhan,
            new.brg_pemenuhan_qty,
            new.brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            new.id_fk_brg_permintaan,
            new.id_fk_cabang,
            new.id_fk_warehouse,
            new.brg_pemenuhan_create_date,
            new.brg_pemenuhan_last_modified,
            new.id_create_data,
            new.id_last_modified
            ,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pemenuhan_log`
--

CREATE TABLE `tbl_brg_pemenuhan_log` (
  `id_pk_brg_pemenuhan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pemenuhan` int(11) DEFAULT NULL,
  `brg_pemenuhan_qty` int(11) DEFAULT NULL,
  `brg_pemenuhan_tipe` varchar(9) DEFAULT NULL COMMENT 'warehouse/cabang',
  `brg_pemenuhan_status` varchar(30) DEFAULT NULL COMMENT 'aktif/nonaktif',
  `id_fk_brg_permintaan` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_pemenuhan_create_date` datetime DEFAULT NULL,
  `brg_pemenuhan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_penerimaan`
--

CREATE TABLE `tbl_brg_penerimaan` (
  `id_pk_brg_penerimaan` int(11) NOT NULL,
  `brg_penerimaan_qty` double DEFAULT NULL,
  `brg_penerimaan_note` varchar(200) DEFAULT NULL,
  `id_fk_penerimaan` int(11) DEFAULT NULL,
  `id_fk_brg_pembelian` int(11) DEFAULT NULL,
  `id_fk_brg_retur` int(11) DEFAULT NULL,
  `id_fk_brg_pengiriman` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_penerimaan_create_date` datetime DEFAULT NULL,
  `brg_penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `tbl_brg_penerimaan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_penerimaan` AFTER INSERT ON `tbl_brg_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penerimaan_log(executed_function,id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_brg_retur,id_fk_brg_pengiriman,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penerimaan,new.brg_penerimaan_qty,new.brg_penerimaan_note,new.id_fk_penerimaan,new.id_fk_brg_pembelian,new.id_fk_brg_retur,new.id_fk_brg_pengiriman,new.id_fk_satuan,new.brg_penerimaan_create_date,new.brg_penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

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

        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_penerimaan` AFTER UPDATE ON `tbl_brg_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penerimaan_log(executed_function,id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_brg_retur,id_fk_brg_pengiriman,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penerimaan,new.brg_penerimaan_qty,new.brg_penerimaan_note,new.id_fk_penerimaan,new.id_fk_brg_pembelian,new.id_fk_brg_retur,new.id_fk_brg_pengiriman,new.id_fk_satuan,new.brg_penerimaan_create_date,new.brg_penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

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

        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_penerimaan_log`
--

CREATE TABLE `tbl_brg_penerimaan_log` (
  `id_pk_brg_penerimaan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_penerimaan` int(11) DEFAULT NULL,
  `brg_penerimaan_qty` double DEFAULT NULL,
  `brg_penerimaan_note` varchar(200) DEFAULT NULL,
  `id_fk_penerimaan` int(11) DEFAULT NULL,
  `id_fk_brg_pembelian` int(11) DEFAULT NULL,
  `id_fk_brg_retur` int(11) DEFAULT NULL,
  `id_fk_brg_pengiriman` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_penerimaan_create_date` datetime DEFAULT NULL,
  `brg_penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pengiriman`
--

CREATE TABLE `tbl_brg_pengiriman` (
  `id_pk_brg_pengiriman` int(11) NOT NULL,
  `brg_pengiriman_qty` double DEFAULT NULL,
  `brg_pengiriman_note` varchar(200) DEFAULT NULL,
  `id_fk_pengiriman` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `id_fk_brg_retur_kembali` int(11) DEFAULT NULL,
  `id_fk_brg_pemenuhan` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_pengiriman_create_date` datetime DEFAULT NULL,
  `brg_pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `tbl_brg_pengiriman`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pengiriman` AFTER INSERT ON `tbl_brg_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pengiriman_log(executed_function,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,id_fk_pengiriman,id_fk_brg_penjualan,id_fk_brg_retur_kembali,id_fk_brg_pemenuhan,id_fk_satuan,brg_pengiriman_create_date,brg_pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pengiriman,new.brg_pengiriman_qty,new.brg_pengiriman_note,new.id_fk_pengiriman,new.id_fk_brg_penjualan,new.id_fk_brg_retur_kembali,new.id_fk_brg_pemenuhan,new.id_fk_satuan,new.brg_pengiriman_create_date,new.brg_pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            set @id_cabang = 0;
            set @id_barang = 0;
            set @id_warehouse = 0;
            set @brg_pengiriman_qty = new.brg_pengiriman_qty;
            set @id_satuan_kirim = new.id_fk_satuan;
            set @id_fk_brg_penjualan = new.id_fk_brg_penjualan;
            set @id_fk_brg_retur = new.id_fk_brg_retur_kembali;
            set @id_fk_brg_pemenuhan = new.id_fk_brg_pemenuhan;
            
            if @id_fk_brg_penjualan is not null and @id_fk_brg_penjualan != 0
            then
            select mstr_pengiriman.id_fk_cabang, id_fk_barang, id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse 
            from tbl_brg_pengiriman
            inner join tbl_brg_penjualan on tbl_brg_penjualan.id_pk_brg_penjualan = tbl_brg_pengiriman.id_fk_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            
            elseif @id_fk_brg_retur is not null and @id_fk_brg_retur != 0
            then
            select mstr_pengiriman.id_fk_cabang, id_fk_brg, id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_pengiriman
            inner join tbl_retur_kembali on tbl_retur_kembali.id_pk_retur_kembali = tbl_brg_pengiriman.id_fk_brg_retur_kembali
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;

            elseif @id_fk_brg_pemenuhan is not null and @id_fk_brg_pemenuhan != 0 
            then
            
            select mstr_pengiriman.id_fk_cabang, id_fk_brg, mstr_pengiriman.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_pengiriman
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            end if;
            if @id_warehouse is not null then
            call update_stok_barang_warehouse(@id_barang,@id_warehouse,0,0,@brg_pengiriman_qty,@id_satuan_kirim);
            elseif @id_cabang is not null then 
            call update_stok_barang_cabang(@id_barang,@id_cabang,0,0,@brg_pengiriman_qty,@id_satuan_kirim);
            end if;
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pengiriman` AFTER UPDATE ON `tbl_brg_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pengiriman_log(executed_function,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,id_fk_pengiriman,id_fk_brg_penjualan,id_fk_brg_retur_kembali,id_fk_brg_pemenuhan,id_fk_satuan,brg_pengiriman_create_date,brg_pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pengiriman,new.brg_pengiriman_qty,new.brg_pengiriman_note,new.id_fk_pengiriman,new.id_fk_brg_penjualan,new.id_fk_brg_retur_kembali,new.id_fk_brg_pemenuhan,new.id_fk_satuan,new.brg_pengiriman_create_date,new.brg_pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            set @id_cabang = 0;
            set @id_barang = 0;
            set @id_warehouse = 0;
            set @brg_pengiriman_qty = new.brg_pengiriman_qty;
            set @id_satuan_terima = new.id_fk_satuan;
            set @brg_keluar_qty = old.brg_pengiriman_qty;
            set @id_satuan_keluar = old.id_fk_satuan;
            set @id_fk_brg_penjualan = new.id_fk_brg_penjualan;
            set @id_fk_brg_retur = new.id_fk_brg_retur_kembali;
            set @id_fk_brg_pemenuhan = new.id_fk_brg_pemenuhan;

            if @id_fk_brg_penjualan is not null and @id_fk_brg_penjualan != 0
            then
            select mstr_pengiriman.id_fk_cabang, id_fk_barang, id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse 
            from tbl_brg_pengiriman
            inner join tbl_brg_penjualan on tbl_brg_penjualan.id_pk_brg_penjualan = tbl_brg_pengiriman.id_fk_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            
            elseif @id_fk_brg_retur is not null and @id_fk_brg_retur != 0 then
            select mstr_pengiriman.id_fk_cabang, id_fk_brg, id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_pengiriman
            inner join tbl_retur_kembali on tbl_retur_kembali.id_pk_retur_kembali = tbl_brg_pengiriman.id_fk_brg_retur_kembali
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;

            elseif @id_fk_brg_pemenuhan is not null and @id_fk_brg_pemenuhan != 0
            then
            select mstr_pengiriman.id_fk_cabang, id_fk_brg, mstr_pengiriman.id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse
            from tbl_brg_pengiriman
            inner join tbl_brg_pemenuhan on tbl_brg_pemenuhan.id_pk_brg_pemenuhan = tbl_brg_pengiriman.id_fk_brg_pemenuhan
            inner join tbl_brg_permintaan on tbl_brg_permintaan.id_pk_brg_permintaan = tbl_brg_pemenuhan.id_fk_brg_permintaan
            inner join mstr_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            end if;
            
            if @id_warehouse is not null then
            call update_stok_barang_warehouse(@id_barang,@id_warehouse,@brg_keluar_qty,@id_satuan_keluar,@brg_pengiriman_qty,@id_satuan_terima);
            elseif @id_cabang is not null then 
            call update_stok_barang_cabang(@id_barang,@id_cabang,@brg_keluar_qty,@id_satuan_keluar,@brg_pengiriman_qty,@id_satuan_terima);
            end if;
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pengiriman_log`
--

CREATE TABLE `tbl_brg_pengiriman_log` (
  `id_pk_brg_pengiriman_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pengiriman` int(11) DEFAULT NULL,
  `brg_pengiriman_qty` double DEFAULT NULL,
  `brg_pengiriman_note` varchar(200) DEFAULT NULL,
  `id_fk_pengiriman` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `id_fk_brg_retur_kembali` int(11) DEFAULT NULL,
  `id_fk_brg_pemenuhan` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_pengiriman_create_date` datetime DEFAULT NULL,
  `brg_pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_penjualan`
--

CREATE TABLE `tbl_brg_penjualan` (
  `id_pk_brg_penjualan` int(11) NOT NULL,
  `brg_penjualan_qty_real` double DEFAULT NULL,
  `brg_penjualan_satuan_real` varchar(20) DEFAULT NULL,
  `brg_penjualan_qty` double DEFAULT NULL,
  `brg_penjualan_satuan` varchar(20) DEFAULT NULL,
  `brg_penjualan_harga` int(11) DEFAULT NULL,
  `brg_penjualan_note` varchar(150) DEFAULT NULL,
  `brg_penjualan_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_penjualan_create_date` datetime DEFAULT NULL,
  `brg_penjualan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_brg_penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_penjualan` AFTER INSERT ON `tbl_brg_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_penjualan` AFTER UPDATE ON `tbl_brg_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_penjualan_log`
--

CREATE TABLE `tbl_brg_penjualan_log` (
  `id_pk_brg_penjualan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_penjualan` int(11) DEFAULT NULL,
  `brg_penjualan_qty_real` double DEFAULT NULL,
  `brg_penjualan_satuan_real` varchar(20) DEFAULT NULL,
  `brg_penjualan_qty` double DEFAULT NULL,
  `brg_penjualan_satuan` varchar(20) DEFAULT NULL,
  `brg_penjualan_harga` int(11) DEFAULT NULL,
  `brg_penjualan_note` varchar(150) DEFAULT NULL,
  `brg_penjualan_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_penjualan_create_date` datetime DEFAULT NULL,
  `brg_penjualan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_permintaan`
--

CREATE TABLE `tbl_brg_permintaan` (
  `id_pk_brg_permintaan` int(11) NOT NULL,
  `brg_permintaan_qty` int(11) DEFAULT NULL,
  `brg_permintaan_notes` text DEFAULT NULL,
  `brg_permintaan_deadline` date DEFAULT NULL,
  `brg_permintaan_status` varchar(7) DEFAULT NULL COMMENT 'BELUM/SEDANG/SUDAH/BATAL',
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_permintaan_create_date` datetime DEFAULT NULL,
  `brg_permintaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_brg_permintaan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_permintaan` AFTER INSERT ON `tbl_brg_permintaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_permintaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_permintaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_permintaan_log(executed_function,
            id_pk_brg_permintaan,
            brg_permintaan_qty,
            brg_permintaan_notes,
            brg_permintaan_deadline,
            brg_permintaan_status,
            id_fk_brg,
            id_fk_cabang,
            brg_permintaan_create_date,
            brg_permintaan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_permintaan,
            new.brg_permintaan_qty,
            new.brg_permintaan_notes,
            new.brg_permintaan_deadline,
            new.brg_permintaan_status,
            new.id_fk_brg,
            new.id_fk_cabang,
            new.brg_permintaan_create_date,
            new.brg_permintaan_last_modified,
            new.id_create_data,
            new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_permintaan` AFTER UPDATE ON `tbl_brg_permintaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_permintaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_permintaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_permintaan_log(executed_function,
            id_pk_brg_permintaan,
            brg_permintaan_qty,
            brg_permintaan_notes,
            brg_permintaan_deadline,
            brg_permintaan_status,
            id_fk_brg,
            id_fk_cabang,
            brg_permintaan_create_date,
            brg_permintaan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_permintaan,
            new.brg_permintaan_qty,
            new.brg_permintaan_notes,
            new.brg_permintaan_deadline,
            new.brg_permintaan_status,
            new.id_fk_brg,
            new.id_fk_cabang,
            new.brg_permintaan_create_date,
            new.brg_permintaan_last_modified,
            new.id_create_data,
            new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_permintaan_log`
--

CREATE TABLE `tbl_brg_permintaan_log` (
  `id_pk_penerimaan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_permintaan` int(11) DEFAULT NULL,
  `brg_permintaan_qty` int(11) DEFAULT NULL,
  `brg_permintaan_notes` text DEFAULT NULL,
  `brg_permintaan_deadline` date DEFAULT NULL,
  `brg_permintaan_status` varchar(7) DEFAULT NULL COMMENT 'BELUM/SEDANG/SUDAH/BATAL',
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_permintaan_create_date` datetime DEFAULT NULL,
  `brg_permintaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pindah`
--

CREATE TABLE `tbl_brg_pindah` (
  `id_pk_brg_pindah` int(11) NOT NULL,
  `brg_pindah_sumber` varchar(50) DEFAULT NULL COMMENT 'warehouse/penjualan/...',
  `id_fk_refrensi_sumber` int(11) DEFAULT NULL COMMENT 'id_warehouse/id_penjualan/...',
  `id_brg_awal` int(11) DEFAULT NULL,
  `id_brg_tujuan` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_pindah_qty` double DEFAULT NULL,
  `brg_pindah_status` varchar(15) DEFAULT NULL,
  `brg_pindah_create_date` datetime DEFAULT NULL,
  `brg_pindah_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `tbl_brg_pindah`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pindah` AFTER INSERT ON `tbl_brg_pindah` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pindah_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pindah_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pindah_log(executed_function,id_pk_brg_pindah,brg_pindah_sumber,id_fk_refrensi_sumber,id_brg_awal,id_brg_tujuan,id_fk_cabang,brg_pindah_qty,brg_pindah_status,brg_pindah_create_date,brg_pindah_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pindah,new.brg_pindah_sumber,new.id_fk_refrensi_sumber,new.id_brg_awal,new.id_brg_tujuan,new.id_fk_cabang,new.brg_pindah_qty,new.brg_pindah_status,new.brg_pindah_create_date,new.brg_pindah_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

            /*update barang cabang*/
            select id_pk_satuan into @id_satuan from mstr_satuan where mstr_satuan.satuan_rumus = 1;            
            call update_stok_barang_cabang(new.id_brg_awal,new.id_fk_cabang,0,0,new.brg_pindah_qty,@id_satuan);
            call update_stok_barang_cabang(new.id_brg_tujuan,new.id_fk_cabang,new.brg_pindah_qty,@id_satuan,0,0);
            
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pindah` AFTER UPDATE ON `tbl_brg_pindah` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pindah_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pindah_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pindah_log(executed_function,id_pk_brg_pindah,brg_pindah_sumber,id_fk_refrensi_sumber,id_brg_awal,id_brg_tujuan,id_fk_cabang,brg_pindah_qty,brg_pindah_status,brg_pindah_create_date,brg_pindah_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pindah,new.brg_pindah_sumber,new.id_fk_refrensi_sumber,new.id_brg_awal,new.id_brg_tujuan,new.id_fk_cabang,new.brg_pindah_qty,new.brg_pindah_status,new.brg_pindah_create_date,new.brg_pindah_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pindah_log`
--

CREATE TABLE `tbl_brg_pindah_log` (
  `id_pk_brg_pindah_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pindah` int(11) DEFAULT NULL,
  `brg_pindah_sumber` varchar(50) DEFAULT NULL COMMENT 'warehouse/penjualan/...',
  `id_fk_refrensi_sumber` int(11) DEFAULT NULL COMMENT 'id_warehouse/id_penjualan/...',
  `id_brg_awal` int(11) DEFAULT NULL,
  `id_brg_tujuan` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_pindah_qty` double DEFAULT NULL,
  `brg_pindah_status` varchar(15) DEFAULT NULL,
  `brg_pindah_create_date` datetime DEFAULT NULL,
  `brg_pindah_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_so`
--

CREATE TABLE `tbl_brg_so` (
  `ID_PK_SO_BRG` int(11) NOT NULL,
  `BRG_SO_RESULT` double DEFAULT NULL,
  `BRG_SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_BRG` int(11) DEFAULT NULL,
  `BRG_SO_CREATE_DATE` datetime DEFAULT NULL,
  `BRG_SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `tbl_brg_so`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_BRG_SO` AFTER INSERT ON `tbl_brg_so` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.BRG_SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_BRG_SO_LOG(EXECUTED_FUNCTION,ID_PK_SO_BRG,BRG_SO_RESULT,BRG_SO_NOTES,ID_FK_STOCK_OPNAME,ID_FK_BRG,BRG_SO_CREATE_DATE,BRG_SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SO_BRG,NEW.BRG_SO_RESULT,NEW.BRG_SO_NOTES,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_BRG,NEW.BRG_SO_CREATE_DATE,NEW.BRG_SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_BRG_SO` AFTER UPDATE ON `tbl_brg_so` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.BRG_SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_BRG_SO_LOG(EXECUTED_FUNCTION,ID_PK_SO_BRG,BRG_SO_RESULT,BRG_SO_NOTES,ID_FK_STOCK_OPNAME,ID_FK_BRG,BRG_SO_CREATE_DATE,BRG_SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SO_BRG,NEW.BRG_SO_RESULT,NEW.BRG_SO_NOTES,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_BRG,NEW.BRG_SO_CREATE_DATE,NEW.BRG_SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_so_log`
--

CREATE TABLE `tbl_brg_so_log` (
  `ID_PK_SO_BRG_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_SO_BRG` int(11) DEFAULT NULL,
  `BRG_SO_RESULT` double DEFAULT NULL,
  `BRG_SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_BRG` int(11) DEFAULT NULL,
  `BRG_SO_CREATE_DATE` datetime DEFAULT NULL,
  `BRG_SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_warehouse`
--

CREATE TABLE `tbl_brg_warehouse` (
  `id_pk_brg_warehouse` int(11) NOT NULL,
  `brg_warehouse_qty` int(11) DEFAULT NULL,
  `brg_warehouse_notes` varchar(200) DEFAULT NULL,
  `brg_warehouse_status` varchar(15) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_warehouse_create_date` datetime DEFAULT NULL,
  `brg_warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_brg_warehouse`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_warehouse` AFTER INSERT ON `tbl_brg_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_warehouse_log(executed_function,id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,id_fk_warehouse,brg_warehouse_create_date,brg_warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_warehouse,new.brg_warehouse_qty,new.brg_warehouse_notes,new.brg_warehouse_status,new.id_fk_brg,new.id_fk_warehouse,new.brg_warehouse_create_date,new.brg_warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_warehouse` AFTER UPDATE ON `tbl_brg_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_warehouse_log(executed_function,id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,id_fk_warehouse,brg_warehouse_create_date,brg_warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_warehouse,new.brg_warehouse_qty,new.brg_warehouse_notes,new.brg_warehouse_status,new.id_fk_brg,new.id_fk_warehouse,new.brg_warehouse_create_date,new.brg_warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_warehouse_log`
--

CREATE TABLE `tbl_brg_warehouse_log` (
  `id_pk_brg_warehouse_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_warehouse` int(11) DEFAULT NULL,
  `brg_warehouse_qty` int(11) DEFAULT NULL,
  `brg_warehouse_notes` varchar(200) DEFAULT NULL,
  `brg_warehouse_status` varchar(15) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_warehouse_create_date` datetime DEFAULT NULL,
  `brg_warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cabang_admin`
--

CREATE TABLE `tbl_cabang_admin` (
  `id_pk_cabang_admin` int(11) NOT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `cabang_admin_status` varchar(15) DEFAULT NULL,
  `cabang_admin_create_date` datetime DEFAULT NULL,
  `cabang_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_cabang_admin`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_cabang_admin` AFTER INSERT ON `tbl_cabang_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.cabang_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_cabang_admin_log(executed_function,id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_create_date,cabang_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_cabang_admin,new.id_fk_cabang,new.id_fk_user,new.cabang_admin_status,new.cabang_admin_create_date,new.cabang_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_cabang_admin` AFTER UPDATE ON `tbl_cabang_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.cabang_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_cabang_admin_log(executed_function,id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_create_date,cabang_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_cabang_admin,new.id_fk_cabang,new.id_fk_user,new.cabang_admin_status,new.cabang_admin_create_date,new.cabang_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cabang_admin_log`
--

CREATE TABLE `tbl_cabang_admin_log` (
  `id_pk_cabang_admin_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_cabang_admin` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `cabang_admin_status` varchar(15) DEFAULT NULL,
  `cabang_admin_create_date` datetime DEFAULT NULL,
  `cabang_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hak_akses`
--

CREATE TABLE `tbl_hak_akses` (
  `id_pk_hak_akses` int(11) NOT NULL,
  `id_fk_jabatan` int(11) DEFAULT NULL,
  `id_fk_menu` int(11) DEFAULT NULL,
  `hak_akses_status` varchar(15) DEFAULT NULL,
  `hak_akses_create_date` datetime DEFAULT NULL,
  `hak_akses_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_hak_akses`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_hak_akses` AFTER INSERT ON `tbl_hak_akses` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.hak_akses_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.hak_akses_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_hak_akses_log(executed_function,id_pk_hak_akses,id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_hak_akses,new.id_fk_jabatan,new.id_fk_menu,new.hak_akses_status,new.hak_akses_create_date,new.hak_akses_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_hak_akses` AFTER UPDATE ON `tbl_hak_akses` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.hak_akses_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.hak_akses_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_hak_akses_log(executed_function,id_pk_hak_akses,id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_hak_akses,new.id_fk_jabatan,new.id_fk_menu,new.hak_akses_status,new.hak_akses_create_date,new.hak_akses_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hak_akses_log`
--

CREATE TABLE `tbl_hak_akses_log` (
  `id_pk_hak_akses_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_hak_akses` int(11) DEFAULT NULL,
  `id_fk_jabatan` int(11) DEFAULT NULL,
  `id_fk_menu` int(11) DEFAULT NULL,
  `hak_akses_status` varchar(15) DEFAULT NULL,
  `hak_akses_create_date` datetime DEFAULT NULL,
  `hak_akses_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penjualan_online`
--

CREATE TABLE `tbl_penjualan_online` (
  `id_pk_penjualan_online` int(11) NOT NULL,
  `penj_on_marketplace` varchar(40) DEFAULT NULL,
  `penj_on_no_resi` varchar(40) DEFAULT NULL,
  `penj_on_kurir` varchar(40) DEFAULT NULL,
  `penj_on_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penj_on_create_date` datetime DEFAULT NULL,
  `penj_on_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_penjualan_online`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penjualan_online` AFTER INSERT ON `tbl_penjualan_online` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_on_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penj_on_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_online_log(executed_function,id_pk_penjualan_online,penj_on_marketplace,penj_on_no_resi,penj_on_kurir,penj_on_status,id_fk_penjualan,penj_on_create_date,penj_on_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan_online,new.penj_on_marketplace,new.penj_on_no_resi,new.penj_on_kurir,new.penj_on_status,new.id_fk_penjualan,new.penj_on_create_date,new.penj_on_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penjualan_online` AFTER UPDATE ON `tbl_penjualan_online` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_on_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penj_on_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_online_log(executed_function,id_pk_penjualan_online,penj_on_marketplace,penj_on_no_resi,penj_on_kurir,penj_on_status,id_fk_penjualan,penj_on_create_date,penj_on_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan_online,new.penj_on_marketplace,new.penj_on_no_resi,new.penj_on_kurir,new.penj_on_status,new.id_fk_penjualan,new.penj_on_create_date,new.penj_on_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penjualan_online_log`
--

CREATE TABLE `tbl_penjualan_online_log` (
  `id_pk_penjualan_online_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penjualan_online` int(11) DEFAULT NULL,
  `penj_on_marketplace` varchar(40) DEFAULT NULL,
  `penj_on_no_resi` varchar(40) DEFAULT NULL,
  `penj_on_kurir` varchar(40) DEFAULT NULL,
  `penj_on_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penj_on_create_date` datetime DEFAULT NULL,
  `penj_on_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penjualan_pembayaran`
--

CREATE TABLE `tbl_penjualan_pembayaran` (
  `id_pk_penjualan_pembayaran` int(11) NOT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_nama` varchar(100) DEFAULT NULL,
  `penjualan_pmbyrn_persen` double DEFAULT NULL,
  `penjualan_pmbyrn_nominal` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_notes` varchar(200) DEFAULT NULL,
  `penjualan_pmbyrn_dateline` datetime DEFAULT NULL,
  `penjualan_pmbyrn_status` varchar(15) DEFAULT NULL,
  `penjualan_pmbyrn_create_date` datetime DEFAULT NULL,
  `penjualan_pmbyrn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_penjualan_pembayaran`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penjualan_pembayaran` AFTER INSERT ON `tbl_penjualan_pembayaran` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_pmbyrn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penjualan_pmbyrn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_pembayaran_log(executed_function,id_pk_penjualan_pembayaran,id_fk_penjualan,penjualan_pmbyrn_nama,penjualan_pmbyrn_persen,penjualan_pmbyrn_nominal,penjualan_pmbyrn_notes,penjualan_pmbyrn_dateline,penjualan_pmbyrn_status,penjualan_pmbyrn_create_date,penjualan_pmbyrn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan_pembayaran,new.id_fk_penjualan,new.penjualan_pmbyrn_nama,new.penjualan_pmbyrn_persen,new.penjualan_pmbyrn_nominal,new.penjualan_pmbyrn_notes,new.penjualan_pmbyrn_dateline,new.penjualan_pmbyrn_status,new.penjualan_pmbyrn_create_date,new.penjualan_pmbyrn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penjualan_pembayaran` AFTER UPDATE ON `tbl_penjualan_pembayaran` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_pmbyrn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penjualan_pmbyrn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_pembayaran_log(executed_function,id_pk_penjualan_pembayaran,id_fk_penjualan,penjualan_pmbyrn_nama,penjualan_pmbyrn_persen,penjualan_pmbyrn_nominal,penjualan_pmbyrn_notes,penjualan_pmbyrn_dateline,penjualan_pmbyrn_status,penjualan_pmbyrn_create_date,penjualan_pmbyrn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan_pembayaran,new.id_fk_penjualan,new.penjualan_pmbyrn_nama,new.penjualan_pmbyrn_persen,new.penjualan_pmbyrn_nominal,new.penjualan_pmbyrn_notes,new.penjualan_pmbyrn_dateline,new.penjualan_pmbyrn_status,new.penjualan_pmbyrn_create_date,new.penjualan_pmbyrn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penjualan_pembayaran_log`
--

CREATE TABLE `tbl_penjualan_pembayaran_log` (
  `id_pk_penjualan_pembayaran_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penjualan_pembayaran` int(11) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_nama` varchar(100) DEFAULT NULL,
  `penjualan_pmbyrn_persen` double DEFAULT NULL,
  `penjualan_pmbyrn_nominal` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_notes` varchar(200) DEFAULT NULL,
  `penjualan_pmbyrn_dateline` datetime DEFAULT NULL,
  `penjualan_pmbyrn_status` varchar(15) DEFAULT NULL,
  `penjualan_pmbyrn_create_date` datetime DEFAULT NULL,
  `penjualan_pmbyrn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_retur_brg`
--

CREATE TABLE `tbl_retur_brg` (
  `id_pk_retur_brg` int(11) NOT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_brg_qty` double DEFAULT NULL,
  `retur_brg_satuan` varchar(30) DEFAULT NULL,
  `retur_brg_notes` varchar(100) DEFAULT NULL,
  `retur_brg_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_retur_brg_log`
--

CREATE TABLE `tbl_retur_brg_log` (
  `id_pk_retur_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_retur_brg` int(11) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_brg_qty` double DEFAULT NULL,
  `retur_brg_satuan` varchar(30) DEFAULT NULL,
  `retur_brg_notes` varchar(100) DEFAULT NULL,
  `retur_brg_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_retur_kembali`
--

CREATE TABLE `tbl_retur_kembali` (
  `id_pk_retur_kembali` int(11) NOT NULL,
  `retur_kembali_qty` double DEFAULT NULL,
  `retur_kembali_satuan` varchar(20) DEFAULT NULL,
  `retur_kembali_harga` int(11) DEFAULT NULL,
  `retur_kembali_note` varchar(150) DEFAULT NULL,
  `retur_kembali_status` varchar(15) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_kembali_create_date` datetime DEFAULT NULL,
  `retur_kembali_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_retur_kembali`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_retur_kembali` AFTER INSERT ON `tbl_retur_kembali` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_retur_kembali,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_retur_kembali` AFTER UPDATE ON `tbl_retur_kembali` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_retur_kembali,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_retur_kembali_log`
--

CREATE TABLE `tbl_retur_kembali_log` (
  `id_pk_retur_kembali_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_retur_kembali` int(11) DEFAULT NULL,
  `retur_kembali_qty` double DEFAULT NULL,
  `retur_kembali_satuan` varchar(20) DEFAULT NULL,
  `retur_kembali_harga` int(11) DEFAULT NULL,
  `retur_kembali_note` varchar(150) DEFAULT NULL,
  `retur_kembali_status` varchar(15) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_kembali_create_date` datetime DEFAULT NULL,
  `retur_kembali_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sj_item`
--

CREATE TABLE `tbl_sj_item` (
  `id_pk_sj_item` int(11) NOT NULL,
  `sj_item_qty` double DEFAULT NULL,
  `sj_item_note` varchar(150) DEFAULT NULL,
  `sj_item_status` varchar(15) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `id_fk_surat_jalan` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `sj_item_create_date` datetime DEFAULT NULL,
  `sj_item_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_sj_item`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_sj_item` AFTER INSERT ON `tbl_sj_item` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_item_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.sj_item_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_sj_item_log(executed_function,id_pk_sj_item,sj_item_qty,sj_item_note,sj_item_status,id_fk_satuan,id_fk_surat_jalan,id_fk_brg_penjualan,sj_item_create_date,sj_item_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_sj_item,new.sj_item_qty,new.sj_item_note,new.sj_item_status,new.id_fk_satuan,new.id_fk_surat_jalan,new.id_fk_brg_penjualan,new.sj_item_create_date,new.sj_item_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_sj_item` AFTER UPDATE ON `tbl_sj_item` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_item_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.sj_item_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_sj_item_log(executed_function,id_pk_sj_item,sj_item_qty,sj_item_note,sj_item_status,id_fk_satuan,id_fk_surat_jalan,id_fk_brg_penjualan,sj_item_create_date,sj_item_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_sj_item,new.sj_item_qty,new.sj_item_note,new.sj_item_status,new.id_fk_satuan,new.id_fk_surat_jalan,new.id_fk_brg_penjualan,new.sj_item_create_date,new.sj_item_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sj_item_log`
--

CREATE TABLE `tbl_sj_item_log` (
  `id_pk_sj_item_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_sj_item` int(11) DEFAULT NULL,
  `sj_item_qty` double DEFAULT NULL,
  `sj_item_note` varchar(150) DEFAULT NULL,
  `sj_item_status` varchar(15) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `id_fk_surat_jalan` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `sj_item_create_date` datetime DEFAULT NULL,
  `sj_item_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_so_pj`
--

CREATE TABLE `tbl_so_pj` (
  `ID_PK_SO_PJ` int(11) NOT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_EMP` int(11) DEFAULT NULL,
  `SO_PJ_CREATE_DATE` datetime DEFAULT NULL,
  `SO_PJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `tbl_so_pj`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_SO_PJ` AFTER INSERT ON `tbl_so_pj` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_PJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SO_PJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_SO_PJ_LOG(EXECUTED_FUNCTION,ID_PK_SO_PJ,ID_FK_STOCK_OPNAME,ID_FK_EMP,SO_PJ_CREATE_DATE,SO_PJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SO_PJ,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_EMP,NEW.SO_PJ_CREATE_DATE,NEW.SO_PJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_SO_PJ` AFTER UPDATE ON `tbl_so_pj` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_PJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SO_PJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_SO_PJ_LOG(EXECUTED_FUNCTION,ID_PK_SO_PJ,ID_FK_STOCK_OPNAME,ID_FK_EMP,SO_PJ_CREATE_DATE,SO_PJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SO_PJ,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_EMP,NEW.SO_PJ_CREATE_DATE,NEW.SO_PJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_so_pj_log`
--

CREATE TABLE `tbl_so_pj_log` (
  `ID_PK_SO_PJ_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_SO_PJ` int(11) DEFAULT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_EMP` int(11) DEFAULT NULL,
  `SO_PJ_CREATE_DATE` datetime DEFAULT NULL,
  `SO_PJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tambahan_pembelian`
--

CREATE TABLE `tbl_tambahan_pembelian` (
  `id_pk_tmbhn` int(11) NOT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_tambahan_pembelian`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_tambahan_pembelian` AFTER INSERT ON `tbl_tambahan_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_pembelian_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_pembelian,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_pembelian,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_tambahan_pembelian` AFTER UPDATE ON `tbl_tambahan_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_pembelian_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_pembelian,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_pembelian,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tambahan_pembelian_log`
--

CREATE TABLE `tbl_tambahan_pembelian_log` (
  `id_pk_tmbhn_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_tmbhn` int(11) DEFAULT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tambahan_penjualan`
--

CREATE TABLE `tbl_tambahan_penjualan` (
  `id_pk_tmbhn` int(11) NOT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_tambahan_penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_tambahan_penjualan` AFTER INSERT ON `tbl_tambahan_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_penjualan_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_penjualan,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_penjualan,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_tambahan_penjualan` AFTER UPDATE ON `tbl_tambahan_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_penjualan_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_penjualan,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_penjualan,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tambahan_penjualan_log`
--

CREATE TABLE `tbl_tambahan_penjualan_log` (
  `id_pk_tmbhn_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_tmbhn` int(11) DEFAULT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_toko_admin`
--

CREATE TABLE `tbl_toko_admin` (
  `id_pk_toko_admin` int(11) NOT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `toko_admin_status` varchar(15) DEFAULT NULL,
  `toko_admin_create_date` datetime DEFAULT NULL,
  `toko_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_toko_admin`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_toko_admin` AFTER INSERT ON `tbl_toko_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.toko_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_toko_admin_log(executed_function,id_pk_toko_admin,id_fk_toko,id_fk_user,toko_admin_status,toko_admin_create_date,toko_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_toko_admin,new.id_fk_toko,new.id_fk_user,new.toko_admin_status,new.toko_admin_create_date,new.toko_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_toko_admin` AFTER UPDATE ON `tbl_toko_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.toko_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_toko_admin_log(executed_function,id_pk_toko_admin,id_fk_toko,id_fk_user,toko_admin_status,toko_admin_create_date,toko_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_toko_admin,new.id_fk_toko,new.id_fk_user,new.toko_admin_status,new.toko_admin_create_date,new.toko_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_toko_admin_log`
--

CREATE TABLE `tbl_toko_admin_log` (
  `id_pk_toko_admin_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_toko_admin` int(11) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `toko_admin_status` varchar(15) DEFAULT NULL,
  `toko_admin_create_date` datetime DEFAULT NULL,
  `toko_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_warehouse_admin`
--

CREATE TABLE `tbl_warehouse_admin` (
  `id_pk_warehouse_admin` int(11) NOT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `warehouse_admin_status` varchar(15) DEFAULT NULL,
  `warehouse_admin_create_date` datetime DEFAULT NULL,
  `warehouse_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `tbl_warehouse_admin`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_warehouse_admin` AFTER INSERT ON `tbl_warehouse_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.warehouse_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_warehouse_admin_log(executed_function,id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_create_date,warehouse_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_warehouse_admin,new.id_fk_warehouse,new.id_fk_user,new.warehouse_admin_status,new.warehouse_admin_create_date,new.warehouse_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_warehouse_admin` AFTER UPDATE ON `tbl_warehouse_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.warehouse_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_warehouse_admin_log(executed_function,id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_create_date,warehouse_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_warehouse_admin,new.id_fk_warehouse,new.id_fk_user,new.warehouse_admin_status,new.warehouse_admin_create_date,new.warehouse_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------


--
DROP TABLE IF EXISTS `v_brg_cabang_aktif`;

CREATE VIEW `v_brg_cabang_aktif`  AS  select `tbl_brg_cabang`.`id_pk_brg_cabang` AS `id_pk_brg_cabang`,`tbl_brg_cabang`.`brg_cabang_qty` AS `brg_cabang_qty`,`tbl_brg_cabang`.`brg_cabang_status` AS `brg_cabang_status`,`tbl_brg_cabang`.`brg_cabang_last_price` AS `brg_cabang_last_price`,`tbl_brg_cabang`.`id_fk_brg` AS `id_fk_brg`,`tbl_brg_cabang`.`id_fk_cabang` AS `id_fk_cabang`,`mstr_barang`.`brg_nama` AS `brg_nama` from (`tbl_brg_cabang` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_brg_cabang`.`id_fk_brg`)) where `tbl_brg_cabang`.`brg_cabang_status` = 'aktif' and `mstr_barang`.`brg_status` = 'aktif' order by `tbl_brg_cabang`.`id_fk_brg`,`tbl_brg_cabang`.`id_fk_cabang` ;

-- --------------------------------------------------------

--
-- Structure for view `v_brg_kombinasi_final`
--
DROP TABLE IF EXISTS `v_brg_kombinasi_final`;

CREATE VIEW `v_brg_kombinasi_final`  AS  select `tbl_barang_kombinasi`.`id_pk_barang_kombinasi` AS `id_pk_barang_kombinasi`,`tbl_barang_kombinasi`.`id_barang_utama` AS `id_barang_utama`,`tbl_barang_kombinasi`.`id_barang_kombinasi` AS `id_barang_kombinasi`,sum(`tbl_barang_kombinasi`.`barang_kombinasi_qty`) AS `barang_kombinasi_qty`,`tbl_barang_kombinasi`.`barang_kombinasi_status` AS `barang_kombinasi_status` from (`tbl_barang_kombinasi` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_barang_kombinasi`.`id_barang_kombinasi`)) where `tbl_barang_kombinasi`.`barang_kombinasi_status` = 'aktif' and `mstr_barang`.`brg_status` = 'aktif' group by `tbl_barang_kombinasi`.`id_barang_utama`,`tbl_barang_kombinasi`.`id_barang_kombinasi` ;

-- --------------------------------------------------------

--
-- Structure for view `v_brg_warehouse_aktif`
--
DROP TABLE IF EXISTS `v_brg_warehouse_aktif`;

CREATE VIEW `v_brg_warehouse_aktif`  AS  select `tbl_brg_warehouse`.`id_pk_brg_warehouse` AS `id_pk_brg_warehouse`,`tbl_brg_warehouse`.`brg_warehouse_qty` AS `brg_warehouse_qty`,`tbl_brg_warehouse`.`brg_warehouse_status` AS `brg_warehouse_status`,`tbl_brg_warehouse`.`id_fk_brg` AS `id_fk_brg`,`tbl_brg_warehouse`.`id_fk_warehouse` AS `id_fk_warehouse`,`mstr_barang`.`brg_nama` AS `brg_nama` from (`tbl_brg_warehouse` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_brg_warehouse`.`id_fk_brg`)) where `tbl_brg_warehouse`.`brg_warehouse_status` = 'aktif' and `mstr_barang`.`brg_status` = 'aktif' order by `tbl_brg_warehouse`.`id_fk_brg`,`tbl_brg_warehouse`.`id_fk_warehouse` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log_all`
--
ALTER TABLE `log_all`
  ADD PRIMARY KEY (`id_log_all`);

--
-- Indexes for table `mstr_barang`
--
ALTER TABLE `mstr_barang`
  ADD PRIMARY KEY (`id_pk_brg`);

--
-- Indexes for table `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  ADD PRIMARY KEY (`id_pk_brg_jenis`);

--
-- Indexes for table `mstr_barang_jenis_log`
--
ALTER TABLE `mstr_barang_jenis_log`
  ADD PRIMARY KEY (`id_pk_brg_jenis_log`);

--
-- Indexes for table `mstr_barang_log`
--
ALTER TABLE `mstr_barang_log`
  ADD PRIMARY KEY (`id_pk_brg_log`);

--
-- Indexes for table `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  ADD PRIMARY KEY (`id_pk_brg_merk`);

--
-- Indexes for table `mstr_barang_merk_log`
--
ALTER TABLE `mstr_barang_merk_log`
  ADD PRIMARY KEY (`id_pk_brg_merk_log`);

--
-- Indexes for table `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  ADD PRIMARY KEY (`id_pk_cabang`);

--
-- Indexes for table `mstr_cabang_log`
--
ALTER TABLE `mstr_cabang_log`
  ADD PRIMARY KEY (`id_pk_cabang_log`);

--
-- Indexes for table `mstr_customer`
--
ALTER TABLE `mstr_customer`
  ADD PRIMARY KEY (`id_pk_cust`);

--
-- Indexes for table `mstr_customer_log`
--
ALTER TABLE `mstr_customer_log`
  ADD PRIMARY KEY (`id_pk_cust_log`);

--
-- Indexes for table `mstr_employee`
--
ALTER TABLE `mstr_employee`
  ADD PRIMARY KEY (`id_pk_employee`);

--
-- Indexes for table `mstr_employee_log`
--
ALTER TABLE `mstr_employee_log`
  ADD PRIMARY KEY (`id_pk_employee_log`);

--
-- Indexes for table `mstr_jabatan`
--
ALTER TABLE `mstr_jabatan`
  ADD PRIMARY KEY (`id_pk_jabatan`);

--
-- Indexes for table `mstr_jabatan_log`
--
ALTER TABLE `mstr_jabatan_log`
  ADD PRIMARY KEY (`id_pk_jabatan_log`);

--
-- Indexes for table `mstr_marketplace`
--
ALTER TABLE `mstr_marketplace`
  ADD PRIMARY KEY (`id_pk_marketplace`);

--
-- Indexes for table `mstr_marketplace_log`
--
ALTER TABLE `mstr_marketplace_log`
  ADD PRIMARY KEY (`id_pk_marketplace_log`);

--
-- Indexes for table `mstr_menu`
--
ALTER TABLE `mstr_menu`
  ADD PRIMARY KEY (`id_pk_menu`);

--
-- Indexes for table `mstr_menu_log`
--
ALTER TABLE `mstr_menu_log`
  ADD PRIMARY KEY (`id_pk_menu_log`);

--
-- Indexes for table `mstr_pembelian`
--
ALTER TABLE `mstr_pembelian`
  ADD PRIMARY KEY (`id_pk_pembelian`);

--
-- Indexes for table `mstr_pembelian_log`
--
ALTER TABLE `mstr_pembelian_log`
  ADD PRIMARY KEY (`id_pk_pembelian_log`);

--
-- Indexes for table `mstr_penawaran`
--
ALTER TABLE `mstr_penawaran`
  ADD PRIMARY KEY (`id_pk_penawaran`);

--
-- Indexes for table `mstr_penawaran_log`
--
ALTER TABLE `mstr_penawaran_log`
  ADD PRIMARY KEY (`id_pk_penawaran_log`);

--
-- Indexes for table `mstr_penerimaan`
--
ALTER TABLE `mstr_penerimaan`
  ADD PRIMARY KEY (`id_pk_penerimaan`);

--
-- Indexes for table `mstr_penerimaan_log`
--
ALTER TABLE `mstr_penerimaan_log`
  ADD PRIMARY KEY (`id_pk_penerimaan_log`);

--
-- Indexes for table `mstr_pengiriman`
--
ALTER TABLE `mstr_pengiriman`
  ADD PRIMARY KEY (`id_pk_pengiriman`);

--
-- Indexes for table `mstr_pengiriman_log`
--
ALTER TABLE `mstr_pengiriman_log`
  ADD PRIMARY KEY (`id_pk_pengiriman_log`);

--
-- Indexes for table `mstr_penjualan`
--
ALTER TABLE `mstr_penjualan`
  ADD PRIMARY KEY (`id_pk_penjualan`);

--
-- Indexes for table `mstr_penjualan_log`
--
ALTER TABLE `mstr_penjualan_log`
  ADD PRIMARY KEY (`id_pk_penjualan_log`);

--
-- Indexes for table `mstr_retur`
--
ALTER TABLE `mstr_retur`
  ADD PRIMARY KEY (`id_pk_retur`);

--
-- Indexes for table `mstr_retur_log`
--
ALTER TABLE `mstr_retur_log`
  ADD PRIMARY KEY (`id_pk_retur_log`);

--
-- Indexes for table `mstr_satuan`
--
ALTER TABLE `mstr_satuan`
  ADD PRIMARY KEY (`id_pk_satuan`);

--
-- Indexes for table `mstr_satuan_log`
--
ALTER TABLE `mstr_satuan_log`
  ADD PRIMARY KEY (`id_pk_satuan_log`);

--
-- Indexes for table `mstr_stock_opname`
--
ALTER TABLE `mstr_stock_opname`
  ADD PRIMARY KEY (`ID_PK_STOCK_OPNAME`);

--
-- Indexes for table `mstr_stock_opname_log`
--
ALTER TABLE `mstr_stock_opname_log`
  ADD PRIMARY KEY (`ID_PK_STOCK_OPNAME_LOG`);

--
-- Indexes for table `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  ADD PRIMARY KEY (`id_pk_sup`);

--
-- Indexes for table `mstr_supplier_log`
--
ALTER TABLE `mstr_supplier_log`
  ADD PRIMARY KEY (`id_pk_sup_log`);

--
-- Indexes for table `mstr_surat_jalan`
--
ALTER TABLE `mstr_surat_jalan`
  ADD PRIMARY KEY (`ID_PK_SURAT_JALAN`);

--
-- Indexes for table `mstr_surat_jalan_log`
--
ALTER TABLE `mstr_surat_jalan_log`
  ADD PRIMARY KEY (`ID_PK_SURAT_JALAN_LOG`);

--
-- Indexes for table `mstr_toko`
--
ALTER TABLE `mstr_toko`
  ADD PRIMARY KEY (`id_pk_toko`);

--
-- Indexes for table `mstr_toko_log`
--
ALTER TABLE `mstr_toko_log`
  ADD PRIMARY KEY (`id_pk_toko_log`);

--
-- Indexes for table `mstr_user`
--
ALTER TABLE `mstr_user`
  ADD PRIMARY KEY (`id_pk_user`);

--
-- Indexes for table `mstr_user_log`
--
ALTER TABLE `mstr_user_log`
  ADD PRIMARY KEY (`id_pk_user_log`);

--
-- Indexes for table `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  ADD PRIMARY KEY (`id_pk_warehouse`);

--
-- Indexes for table `mstr_warehouse_log`
--
ALTER TABLE `mstr_warehouse_log`
  ADD PRIMARY KEY (`id_pk_warehouse_log`);

--
-- Indexes for table `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  ADD PRIMARY KEY (`id_pk_barang_kombinasi`);

--
-- Indexes for table `tbl_barang_kombinasi_log`
--
ALTER TABLE `tbl_barang_kombinasi_log`
  ADD PRIMARY KEY (`id_pk_barang_kombinasi_log`);

--
-- Indexes for table `tbl_barang_ukuran`
--
ALTER TABLE `tbl_barang_ukuran`
  ADD PRIMARY KEY (`ID_PK_BARANG_UKURAN`);

--
-- Indexes for table `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  ADD PRIMARY KEY (`id_pk_brg_cabang`);

--
-- Indexes for table `tbl_brg_cabang_log`
--
ALTER TABLE `tbl_brg_cabang_log`
  ADD PRIMARY KEY (`id_pk_brg_cabang_log`);

--
-- Indexes for table `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  ADD PRIMARY KEY (`id_pk_brg_pembelian`);

--
-- Indexes for table `tbl_brg_pembelian_log`
--
ALTER TABLE `tbl_brg_pembelian_log`
  ADD PRIMARY KEY (`id_pk_brg_pembelian_log`);

--
-- Indexes for table `tbl_brg_pemenuhan`
--
ALTER TABLE `tbl_brg_pemenuhan`
  ADD PRIMARY KEY (`id_pk_brg_pemenuhan`);

--
-- Indexes for table `tbl_brg_pemenuhan_log`
--
ALTER TABLE `tbl_brg_pemenuhan_log`
  ADD PRIMARY KEY (`id_pk_brg_pemenuhan_log`);

--
-- Indexes for table `tbl_brg_penerimaan`
--
ALTER TABLE `tbl_brg_penerimaan`
  ADD PRIMARY KEY (`id_pk_brg_penerimaan`);

--
-- Indexes for table `tbl_brg_penerimaan_log`
--
ALTER TABLE `tbl_brg_penerimaan_log`
  ADD PRIMARY KEY (`id_pk_brg_penerimaan_log`);

--
-- Indexes for table `tbl_brg_pengiriman`
--
ALTER TABLE `tbl_brg_pengiriman`
  ADD PRIMARY KEY (`id_pk_brg_pengiriman`);

--
-- Indexes for table `tbl_brg_pengiriman_log`
--
ALTER TABLE `tbl_brg_pengiriman_log`
  ADD PRIMARY KEY (`id_pk_brg_pengiriman_log`);

--
-- Indexes for table `tbl_brg_penjualan`
--
ALTER TABLE `tbl_brg_penjualan`
  ADD PRIMARY KEY (`id_pk_brg_penjualan`);

--
-- Indexes for table `tbl_brg_penjualan_log`
--
ALTER TABLE `tbl_brg_penjualan_log`
  ADD PRIMARY KEY (`id_pk_brg_penjualan_log`);

--
-- Indexes for table `tbl_brg_permintaan`
--
ALTER TABLE `tbl_brg_permintaan`
  ADD PRIMARY KEY (`id_pk_brg_permintaan`);

--
-- Indexes for table `tbl_brg_permintaan_log`
--
ALTER TABLE `tbl_brg_permintaan_log`
  ADD PRIMARY KEY (`id_pk_penerimaan_log`);

--
-- Indexes for table `tbl_brg_pindah`
--
ALTER TABLE `tbl_brg_pindah`
  ADD PRIMARY KEY (`id_pk_brg_pindah`);

--
-- Indexes for table `tbl_brg_pindah_log`
--
ALTER TABLE `tbl_brg_pindah_log`
  ADD PRIMARY KEY (`id_pk_brg_pindah_log`);

--
-- Indexes for table `tbl_brg_so`
--
ALTER TABLE `tbl_brg_so`
  ADD PRIMARY KEY (`ID_PK_SO_BRG`);

--
-- Indexes for table `tbl_brg_so_log`
--
ALTER TABLE `tbl_brg_so_log`
  ADD PRIMARY KEY (`ID_PK_SO_BRG_LOG`);

--
-- Indexes for table `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  ADD PRIMARY KEY (`id_pk_brg_warehouse`);

--
-- Indexes for table `tbl_brg_warehouse_log`
--
ALTER TABLE `tbl_brg_warehouse_log`
  ADD PRIMARY KEY (`id_pk_brg_warehouse_log`);

--
-- Indexes for table `tbl_cabang_admin`
--
ALTER TABLE `tbl_cabang_admin`
  ADD PRIMARY KEY (`id_pk_cabang_admin`);

--
-- Indexes for table `tbl_cabang_admin_log`
--
ALTER TABLE `tbl_cabang_admin_log`
  ADD PRIMARY KEY (`id_pk_cabang_admin_log`);

--
-- Indexes for table `tbl_hak_akses`
--
ALTER TABLE `tbl_hak_akses`
  ADD PRIMARY KEY (`id_pk_hak_akses`);

--
-- Indexes for table `tbl_hak_akses_log`
--
ALTER TABLE `tbl_hak_akses_log`
  ADD PRIMARY KEY (`id_pk_hak_akses_log`);

--
-- Indexes for table `tbl_penjualan_online`
--
ALTER TABLE `tbl_penjualan_online`
  ADD PRIMARY KEY (`id_pk_penjualan_online`);

--
-- Indexes for table `tbl_penjualan_online_log`
--
ALTER TABLE `tbl_penjualan_online_log`
  ADD PRIMARY KEY (`id_pk_penjualan_online_log`);

--
-- Indexes for table `tbl_penjualan_pembayaran`
--
ALTER TABLE `tbl_penjualan_pembayaran`
  ADD PRIMARY KEY (`id_pk_penjualan_pembayaran`);

--
-- Indexes for table `tbl_penjualan_pembayaran_log`
--
ALTER TABLE `tbl_penjualan_pembayaran_log`
  ADD PRIMARY KEY (`id_pk_penjualan_pembayaran_log`);

--
-- Indexes for table `tbl_retur_brg`
--
ALTER TABLE `tbl_retur_brg`
  ADD PRIMARY KEY (`id_pk_retur_brg`);

--
-- Indexes for table `tbl_retur_brg_log`
--
ALTER TABLE `tbl_retur_brg_log`
  ADD PRIMARY KEY (`id_pk_retur_log`);

--
-- Indexes for table `tbl_retur_kembali`
--
ALTER TABLE `tbl_retur_kembali`
  ADD PRIMARY KEY (`id_pk_retur_kembali`);

--
-- Indexes for table `tbl_retur_kembali_log`
--
ALTER TABLE `tbl_retur_kembali_log`
  ADD PRIMARY KEY (`id_pk_retur_kembali_log`);

--
-- Indexes for table `tbl_sj_item`
--
ALTER TABLE `tbl_sj_item`
  ADD PRIMARY KEY (`id_pk_sj_item`);

--
-- Indexes for table `tbl_sj_item_log`
--
ALTER TABLE `tbl_sj_item_log`
  ADD PRIMARY KEY (`id_pk_sj_item_log`);

--
-- Indexes for table `tbl_so_pj`
--
ALTER TABLE `tbl_so_pj`
  ADD PRIMARY KEY (`ID_PK_SO_PJ`);

--
-- Indexes for table `tbl_so_pj_log`
--
ALTER TABLE `tbl_so_pj_log`
  ADD PRIMARY KEY (`ID_PK_SO_PJ_LOG`);

--
-- Indexes for table `tbl_tambahan_pembelian`
--
ALTER TABLE `tbl_tambahan_pembelian`
  ADD PRIMARY KEY (`id_pk_tmbhn`);

--
-- Indexes for table `tbl_tambahan_pembelian_log`
--
ALTER TABLE `tbl_tambahan_pembelian_log`
  ADD PRIMARY KEY (`id_pk_tmbhn_log`);

--
-- Indexes for table `tbl_tambahan_penjualan`
--
ALTER TABLE `tbl_tambahan_penjualan`
  ADD PRIMARY KEY (`id_pk_tmbhn`);

--
-- Indexes for table `tbl_tambahan_penjualan_log`
--
ALTER TABLE `tbl_tambahan_penjualan_log`
  ADD PRIMARY KEY (`id_pk_tmbhn_log`);

--
-- Indexes for table `tbl_toko_admin`
--
ALTER TABLE `tbl_toko_admin`
  ADD PRIMARY KEY (`id_pk_toko_admin`);

--
-- Indexes for table `tbl_toko_admin_log`
--
ALTER TABLE `tbl_toko_admin_log`
  ADD PRIMARY KEY (`id_pk_toko_admin_log`);

--
-- Indexes for table `tbl_warehouse_admin`
--
ALTER TABLE `tbl_warehouse_admin`
  ADD PRIMARY KEY (`id_pk_warehouse_admin`);

--
-- Indexes for table `tbl_warehouse_admin_log`
--
ALTER TABLE `tbl_warehouse_admin_log`
  ADD PRIMARY KEY (`id_pk_warehouse_admin_log`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log_all`
--
ALTER TABLE `log_all`
  MODIFY `id_log_all` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_barang`
--
ALTER TABLE `mstr_barang`
  MODIFY `id_pk_brg` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  MODIFY `id_pk_brg_jenis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_barang_jenis_log`
--
ALTER TABLE `mstr_barang_jenis_log`
  MODIFY `id_pk_brg_jenis_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_barang_log`
--
ALTER TABLE `mstr_barang_log`
  MODIFY `id_pk_brg_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  MODIFY `id_pk_brg_merk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_barang_merk_log`
--
ALTER TABLE `mstr_barang_merk_log`
  MODIFY `id_pk_brg_merk_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  MODIFY `id_pk_cabang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_cabang_log`
--
ALTER TABLE `mstr_cabang_log`
  MODIFY `id_pk_cabang_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_customer`
--
ALTER TABLE `mstr_customer`
  MODIFY `id_pk_cust` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_customer_log`
--
ALTER TABLE `mstr_customer_log`
  MODIFY `id_pk_cust_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_employee`
--
ALTER TABLE `mstr_employee`
  MODIFY `id_pk_employee` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_employee_log`
--
ALTER TABLE `mstr_employee_log`
  MODIFY `id_pk_employee_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_jabatan`
--
ALTER TABLE `mstr_jabatan`
  MODIFY `id_pk_jabatan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_jabatan_log`
--
ALTER TABLE `mstr_jabatan_log`
  MODIFY `id_pk_jabatan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_marketplace`
--
ALTER TABLE `mstr_marketplace`
  MODIFY `id_pk_marketplace` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_marketplace_log`
--
ALTER TABLE `mstr_marketplace_log`
  MODIFY `id_pk_marketplace_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_menu`
--
ALTER TABLE `mstr_menu`
  MODIFY `id_pk_menu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_menu_log`
--
ALTER TABLE `mstr_menu_log`
  MODIFY `id_pk_menu_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_pembelian`
--
ALTER TABLE `mstr_pembelian`
  MODIFY `id_pk_pembelian` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_pembelian_log`
--
ALTER TABLE `mstr_pembelian_log`
  MODIFY `id_pk_pembelian_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_penawaran`
--
ALTER TABLE `mstr_penawaran`
  MODIFY `id_pk_penawaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_penawaran_log`
--
ALTER TABLE `mstr_penawaran_log`
  MODIFY `id_pk_penawaran_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_penerimaan`
--
ALTER TABLE `mstr_penerimaan`
  MODIFY `id_pk_penerimaan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_penerimaan_log`
--
ALTER TABLE `mstr_penerimaan_log`
  MODIFY `id_pk_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_pengiriman`
--
ALTER TABLE `mstr_pengiriman`
  MODIFY `id_pk_pengiriman` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_pengiriman_log`
--
ALTER TABLE `mstr_pengiriman_log`
  MODIFY `id_pk_pengiriman_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_penjualan`
--
ALTER TABLE `mstr_penjualan`
  MODIFY `id_pk_penjualan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_penjualan_log`
--
ALTER TABLE `mstr_penjualan_log`
  MODIFY `id_pk_penjualan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_retur`
--
ALTER TABLE `mstr_retur`
  MODIFY `id_pk_retur` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_retur_log`
--
ALTER TABLE `mstr_retur_log`
  MODIFY `id_pk_retur_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_satuan`
--
ALTER TABLE `mstr_satuan`
  MODIFY `id_pk_satuan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_satuan_log`
--
ALTER TABLE `mstr_satuan_log`
  MODIFY `id_pk_satuan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_stock_opname`
--
ALTER TABLE `mstr_stock_opname`
  MODIFY `ID_PK_STOCK_OPNAME` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_stock_opname_log`
--
ALTER TABLE `mstr_stock_opname_log`
  MODIFY `ID_PK_STOCK_OPNAME_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  MODIFY `id_pk_sup` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_supplier_log`
--
ALTER TABLE `mstr_supplier_log`
  MODIFY `id_pk_sup_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_surat_jalan`
--
ALTER TABLE `mstr_surat_jalan`
  MODIFY `ID_PK_SURAT_JALAN` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_surat_jalan_log`
--
ALTER TABLE `mstr_surat_jalan_log`
  MODIFY `ID_PK_SURAT_JALAN_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_toko`
--
ALTER TABLE `mstr_toko`
  MODIFY `id_pk_toko` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_toko_log`
--
ALTER TABLE `mstr_toko_log`
  MODIFY `id_pk_toko_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_user`
--
ALTER TABLE `mstr_user`
  MODIFY `id_pk_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_user_log`
--
ALTER TABLE `mstr_user_log`
  MODIFY `id_pk_user_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  MODIFY `id_pk_warehouse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_warehouse_log`
--
ALTER TABLE `mstr_warehouse_log`
  MODIFY `id_pk_warehouse_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  MODIFY `id_pk_barang_kombinasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_barang_kombinasi_log`
--
ALTER TABLE `tbl_barang_kombinasi_log`
  MODIFY `id_pk_barang_kombinasi_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_barang_ukuran`
--
ALTER TABLE `tbl_barang_ukuran`
  MODIFY `ID_PK_BARANG_UKURAN` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  MODIFY `id_pk_brg_cabang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_cabang_log`
--
ALTER TABLE `tbl_brg_cabang_log`
  MODIFY `id_pk_brg_cabang_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  MODIFY `id_pk_brg_pembelian` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pembelian_log`
--
ALTER TABLE `tbl_brg_pembelian_log`
  MODIFY `id_pk_brg_pembelian_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pemenuhan`
--
ALTER TABLE `tbl_brg_pemenuhan`
  MODIFY `id_pk_brg_pemenuhan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pemenuhan_log`
--
ALTER TABLE `tbl_brg_pemenuhan_log`
  MODIFY `id_pk_brg_pemenuhan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_penerimaan`
--
ALTER TABLE `tbl_brg_penerimaan`
  MODIFY `id_pk_brg_penerimaan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_penerimaan_log`
--
ALTER TABLE `tbl_brg_penerimaan_log`
  MODIFY `id_pk_brg_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pengiriman`
--
ALTER TABLE `tbl_brg_pengiriman`
  MODIFY `id_pk_brg_pengiriman` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pengiriman_log`
--
ALTER TABLE `tbl_brg_pengiriman_log`
  MODIFY `id_pk_brg_pengiriman_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_penjualan`
--
ALTER TABLE `tbl_brg_penjualan`
  MODIFY `id_pk_brg_penjualan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_penjualan_log`
--
ALTER TABLE `tbl_brg_penjualan_log`
  MODIFY `id_pk_brg_penjualan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_permintaan`
--
ALTER TABLE `tbl_brg_permintaan`
  MODIFY `id_pk_brg_permintaan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_permintaan_log`
--
ALTER TABLE `tbl_brg_permintaan_log`
  MODIFY `id_pk_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pindah`
--
ALTER TABLE `tbl_brg_pindah`
  MODIFY `id_pk_brg_pindah` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_pindah_log`
--
ALTER TABLE `tbl_brg_pindah_log`
  MODIFY `id_pk_brg_pindah_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_so`
--
ALTER TABLE `tbl_brg_so`
  MODIFY `ID_PK_SO_BRG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_so_log`
--
ALTER TABLE `tbl_brg_so_log`
  MODIFY `ID_PK_SO_BRG_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  MODIFY `id_pk_brg_warehouse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_brg_warehouse_log`
--
ALTER TABLE `tbl_brg_warehouse_log`
  MODIFY `id_pk_brg_warehouse_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cabang_admin`
--
ALTER TABLE `tbl_cabang_admin`
  MODIFY `id_pk_cabang_admin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cabang_admin_log`
--
ALTER TABLE `tbl_cabang_admin_log`
  MODIFY `id_pk_cabang_admin_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_hak_akses`
--
ALTER TABLE `tbl_hak_akses`
  MODIFY `id_pk_hak_akses` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_hak_akses_log`
--
ALTER TABLE `tbl_hak_akses_log`
  MODIFY `id_pk_hak_akses_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_penjualan_online`
--
ALTER TABLE `tbl_penjualan_online`
  MODIFY `id_pk_penjualan_online` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_penjualan_online_log`
--
ALTER TABLE `tbl_penjualan_online_log`
  MODIFY `id_pk_penjualan_online_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_penjualan_pembayaran`
--
ALTER TABLE `tbl_penjualan_pembayaran`
  MODIFY `id_pk_penjualan_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_penjualan_pembayaran_log`
--
ALTER TABLE `tbl_penjualan_pembayaran_log`
  MODIFY `id_pk_penjualan_pembayaran_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_retur_brg`
--
ALTER TABLE `tbl_retur_brg`
  MODIFY `id_pk_retur_brg` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_retur_brg_log`
--
ALTER TABLE `tbl_retur_brg_log`
  MODIFY `id_pk_retur_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_retur_kembali`
--
ALTER TABLE `tbl_retur_kembali`
  MODIFY `id_pk_retur_kembali` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_retur_kembali_log`
--
ALTER TABLE `tbl_retur_kembali_log`
  MODIFY `id_pk_retur_kembali_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sj_item`
--
ALTER TABLE `tbl_sj_item`
  MODIFY `id_pk_sj_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sj_item_log`
--
ALTER TABLE `tbl_sj_item_log`
  MODIFY `id_pk_sj_item_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_so_pj`
--
ALTER TABLE `tbl_so_pj`
  MODIFY `ID_PK_SO_PJ` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_so_pj_log`
--
ALTER TABLE `tbl_so_pj_log`
  MODIFY `ID_PK_SO_PJ_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tambahan_pembelian`
--
ALTER TABLE `tbl_tambahan_pembelian`
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tambahan_pembelian_log`
--
ALTER TABLE `tbl_tambahan_pembelian_log`
  MODIFY `id_pk_tmbhn_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tambahan_penjualan`
--
ALTER TABLE `tbl_tambahan_penjualan`
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tambahan_penjualan_log`
--
ALTER TABLE `tbl_tambahan_penjualan_log`
  MODIFY `id_pk_tmbhn_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_toko_admin`
--
ALTER TABLE `tbl_toko_admin`
  MODIFY `id_pk_toko_admin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_toko_admin_log`
--
ALTER TABLE `tbl_toko_admin_log`
  MODIFY `id_pk_toko_admin_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_warehouse_admin`
--
ALTER TABLE `tbl_warehouse_admin`
  MODIFY `id_pk_warehouse_admin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_warehouse_admin_log`
--
ALTER TABLE `tbl_warehouse_admin_log`
  MODIFY `id_pk_warehouse_admin_log` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
