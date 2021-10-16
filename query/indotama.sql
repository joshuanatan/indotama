-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2021 at 03:27 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
    
    select ifnull(cabang_kode,"-") into @kode_cabang
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

CREATE PROCEDURE `insert_log_all` (IN `id_user` INT, IN `log_msg` VARCHAR(1000), IN `log_data_changes` VARCHAR(1000), IN `log_data_it` VARCHAR(1000))  begin
	insert into log_all(
		log_all_msg,
        log_all_data_changes,
        log_all_it,
        log_all_user,
        log_all_tgl
	) 
    values(
		log_msg,
        log_data_changes,
        log_data_it,
        id_user,
        now()
	);
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
            call update_stok_kombinasi_master_cabang();

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
  `id_pk_log_all` int(11) NOT NULL,
  `log_all_msg` varchar(300) DEFAULT NULL,
  `log_all_data_changes` varchar(1000) DEFAULT NULL,
  `log_all_it` varchar(300) DEFAULT NULL COMMENT 'IT refrence',
  `log_all_user` int(11) DEFAULT NULL,
  `log_all_tgl` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `log_all`
--

INSERT INTO `log_all` (`id_pk_log_all`, `log_all_msg`, `log_all_data_changes`, `log_all_it`, `log_all_user`, `log_all_tgl`) VALUES
(1, 'Data baru ditambahkan pada tabel mstr_barang_jenis. Waktu penambahan: 2020-07-31 16:10:31', '[id_pk_brg_jenis: 18][brg_jenis_nama: TEST 2][brg_jenis_status: AKTIF][brg_jenis_create_date: 2020-07-31 04:10:31][brg_jenis_last_modified: 2020-07-31 04:10:31][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_barang_jenis_log dengan id_pk_brg_jenis 21', 2, '2020-07-31 16:10:31'),
(2, 'Data baru ditambahkan pada tabel mstr_barang_jenis. Waktu penambahan: 2020-07-31 16:15:34', '[id_pk_brg_jenis: 18 => 18][brg_jenis_nama: TEST 2 => TEST 3][brg_jenis_status: AKTIF => AKTIF][brg_jenis_create_date: 2020-07-31 04:10:31 => 2020-07-31 04:10:31][brg_jenis_last_modified: 2020-07-31 04:10:31 => 2020-07-31 04:15:34][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_barang_jenis_log dengan id_pk_brg_jenis 22', 2, '2020-07-31 16:15:34'),
(3, 'Data diubah pada tabel mstr_barang_jenis. Waktu perubahan: 2020-07-31 16:16:44', '[id_pk_brg_jenis: 18 => 18][brg_jenis_nama: TEST 3 => TEST 4][brg_jenis_status: AKTIF => AKTIF][brg_jenis_create_date: 2020-07-31 04:10:31 => 2020-07-31 04:10:31][brg_jenis_last_modified: 2020-07-31 04:15:34 => 2020-07-31 04:16:44][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_barang_jenis_log dengan id_pk_brg_jenis 23', 2, '2020-07-31 16:16:44'),
(4, 'Data baru ditambahkan pada tabel mstr_barang. Waktu penambahan: 2020-07-31 16:43:16', '[id_pk_brg: 176][brg_kode: Kode 2][brg_nama: Nama 2][brg_ket: -][brg_minimal: 120][brg_satuan: PCS][brg_image: noimage.jpg][brg_harga: 123][brg_tipe: kombinasi][brg_status: AKTIF][brg_create_date: 2020-07-31 04:43:16][brg_last_modified: 2020-07-31 04:43:16][id_create_data: 2][id_last_modified: 2][id_fk_brg_jenis: 1][id_fk_brg_merk: 2]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 219', 2, '2020-07-31 16:43:16'),
(5, 'Data baru ditambahkan pada tabel tbl_brg_pindah. Waktu penambahan: 2020-07-31 16:44:39', '[id_pk_brg_pindah: 3][brg_pindah_sumber: penjualan][id_fk_refrensi_sumber: 0][id_brg_awal: 23][id_brg_tujuan: 24][id_fk_cabang: 2][brg_pindah_qty: 100][brg_pindah_status: AKTIF][brg_pindah_create_date: 2020-07-31 16:44:39][brg_pindah_last_modified: 2020-07-31 16:44:39][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 5', 2, '2020-07-31 16:44:39'),
(6, 'Data baru ditambahkan pada tabel mstr_barang. Waktu penambahan: 2020-07-31 17:12:23', '[id_pk_brg: 177][brg_kode: Kode 3][brg_nama: Nama 3][brg_ket: -][brg_minimal: 1000][brg_satuan: PCS][brg_image: noimage.jpg][brg_harga: 1000][brg_tipe: nonkombinasi][brg_status: AKTIF][brg_create_date: 2020-07-31 05:12:23][brg_last_modified: 2020-07-31 05:12:23][id_create_data: 2][id_last_modified: 2][id_fk_brg_jenis: 1][id_fk_brg_merk: 1]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 1', 2, '2020-07-31 17:12:23'),
(7, 'Data baru ditambahkan pada tabel tbl_brg_pindah. Waktu penambahan: 2020-07-31 17:21:36', '[id_pk_brg_pindah: 4][brg_pindah_sumber: penjualan][id_fk_refrensi_sumber: 0][id_brg_awal: 23][id_brg_tujuan: 24][id_fk_cabang: 2][brg_pindah_qty: 100][brg_pindah_status: AKTIF][brg_pindah_create_date: 2020-07-31 17:21:36][brg_pindah_last_modified: 2020-07-31 17:21:36][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 1', 2, '2020-07-31 17:21:36'),
(8, 'Data baru ditambahkan pada tabel mstr_penjualan. Waktu penambahan: 2020-07-31 17:22:46', '[id_pk_penjualan: 8][penj_nomor: MSTRCABANG3-PENJUALAN-2020-08-08-000001][penj_nominal: 0][penj_nominal_byr: 0][penj_tgl: 2020-08-08 00:00:00][penj_dateline_tgl: 2020-08-08 00:00:00][penj_jenis: ONLINE][penj_tipe_pembayaran: FULL PAYMENT][penj_status: AKTIF][id_fk_customer: 2][id_fk_cabang: 2][penj_create_date: 2020-07-31 05:22:46][penj_last_modified: 2020-07-31 05:22:46][id_create_data: 2][id_last_modified: 2][no_control: 1][bln_control: 7][thn_control: 2020]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 1', 2, '2020-07-31 17:22:46'),
(9, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2020-07-31 17:22:46', '[id_pk_brg_pindah: 4 => 4][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 0 => 8][id_brg_awal: 23 => 23][id_brg_tujuan: 24 => 24][id_fk_cabang: 2 => 2][brg_pindah_qty: 100 => 100][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2020-07-31 17:21:36 => 2020-07-31 17:21:36][brg_pindah_last_modified: 2020-07-31 17:21:36 => 2020-07-31 17:21:36][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 2', 2, '2020-07-31 17:22:46'),
(10, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2020-07-31 17:22:47', '[id_pk_penjualan: 8 => 8][penj_nomor: MSTRCABANG3-PENJUALAN-2020-08-08-000001 => MSTRCABANG3-PENJUALAN-2020-08-08-000001][penj_nominal: 0 => 20100000][penj_nominal_byr: 0 => 0][penj_tgl: 2020-08-08 00:00:00 => 2020-08-08 00:00:00][penj_dateline_tgl: 2020-08-08 00:00:00 => 2020-08-08 00:00:00][penj_jenis: ONLINE => ONLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 2 => 2][id_fk_cabang: 2 => 2][penj_create_date: 2020-07-31 05:22:46 => 2020-07-31 05:22:46][penj_last_modified: 2020-07-31 05:22:46 => 2020-07-31 05:22:46][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 1 => 1][bln_control: 7 => 7][thn_control: 2020 => 2020]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 2', 2, '2020-07-31 17:22:47'),
(11, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2020-07-31 17:22:47', '[id_pk_penjualan: 8 => 8][penj_nomor: MSTRCABANG3-PENJUALAN-2020-08-08-000001 => MSTRCABANG3-PENJUALAN-2020-08-08-000001][penj_nominal: 20100000 => 20100000][penj_nominal_byr: 0 => 10050000][penj_tgl: 2020-08-08 00:00:00 => 2020-08-08 00:00:00][penj_dateline_tgl: 2020-08-08 00:00:00 => 2020-08-08 00:00:00][penj_jenis: ONLINE => ONLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 2 => 2][id_fk_cabang: 2 => 2][penj_create_date: 2020-07-31 05:22:46 => 2020-07-31 05:22:46][penj_last_modified: 2020-07-31 05:22:46 => 2020-07-31 05:22:46][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 1 => 1][bln_control: 7 => 7][thn_control: 2020 => 2020]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 3', 2, '2020-07-31 17:22:47'),
(12, 'Data baru ditambahkan pada tabel tbl_brg_pindah. Waktu penambahan: 2020-07-31 18:10:10', '[id_pk_brg_pindah: 5][brg_pindah_sumber: penjualan][id_fk_refrensi_sumber: 0][id_brg_awal: 23][id_brg_tujuan: 24][id_fk_cabang: 2][brg_pindah_qty: 100][brg_pindah_status: AKTIF][brg_pindah_create_date: 2020-07-31 18:10:10][brg_pindah_last_modified: 2020-07-31 18:10:10][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 3', 2, '2020-07-31 18:10:10'),
(13, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2020-07-31 18:10:10', '[id_pk_brg_cabang: 1 => 1][brg_cabang_qty: 430 => 330][brg_cabang_notes: - => -][brg_cabang_status: AKTIF => AKTIF][brg_cabang_last_price: 5000 => 5000][id_fk_brg: 23 => 23][id_fk_cabang: 2 => 2][brg_cabang_create_date: 2020-07-26 09:43:43 => 2020-07-26 09:43:43][brg_cabang_last_modified: 2020-07-27 08:58:14 => 2020-07-27 08:58:14][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 1', 2, '2020-07-31 18:10:10'),
(14, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2020-07-31 18:10:10', '[id_pk_brg_cabang: 2 => 2][brg_cabang_qty: 44560 => 44660][brg_cabang_notes: Auto insert from checking construct => Auto insert from checking construct][brg_cabang_status: aktif => aktif][brg_cabang_last_price: 1300 => 1300][id_fk_brg: 24 => 24][id_fk_cabang: 2 => 2][brg_cabang_create_date: 2020-07-26 09:43:43 => 2020-07-26 09:43:43][brg_cabang_last_modified: 2020-07-27 08:58:14 => 2020-07-27 08:58:14][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 2', 2, '2020-07-31 18:10:10'),
(15, 'Data diubah pada tabel mstr_user. Waktu perubahan: 2021-04-12 20:03:23', '[id_pk_user: 2 => 2][user_name: admin => admin][user_pass: 21232f297a57a5a743894a0e4a801fc3 => 25d55ad283aa400af464c76d713c07ad][user_email: admin@email.com2 => admin@email.com2][user_status: AKTIF => AKTIF][id_fk_role: 1 => 1][id_fk_employee: 1 => 1][user_last_modified: 2020-07-14 09:59:42 => 2020-07-14 09:59:42][user_create_date: 2020-07-14 09:53:58 => 2020-07-14 09:53:58][id_create_date: 1 => 1][id_last_modified: 1 => 1]', 'Refrensi log table mstr_user_log dengan id_pk_user_log 0', 1, '2021-04-12 20:03:23'),
(16, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-12 23:27:11', '[id_pk_warehouse: 1 => 1][warehouse_nama: GUDANG => GUDANG][warehouse_alamat: Puri Indah => Puri Indah][warehouse_notelp: 12345 => 12345][warehouse_desc: - => -][id_fk_cabang: 0 => 0][warehouse_status: AKTIF => nonaktif][warehouse_create_date: 2020-06-21 11:45:42 => 2020-06-21 11:45:42][warehouse_last_modified: 2020-07-24 11:10:33 => 2021-04-12 11:27:11][id_create_data: 1 => 1][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:27:11'),
(17, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-12 23:34:17', '[id_pk_warehouse: 0][warehouse_nama: aa][warehouse_alamat: aa][warehouse_notelp: aa][warehouse_desc: aa][id_fk_cabang: -1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-12 11:34:17][warehouse_last_modified: 2021-04-12 11:34:17][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:34:17'),
(18, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-12 23:34:20', '[id_pk_warehouse: 0][warehouse_nama: aa][warehouse_alamat: aa][warehouse_notelp: aa][warehouse_desc: aa][id_fk_cabang: -1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-12 11:34:20][warehouse_last_modified: 2021-04-12 11:34:20][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:34:20'),
(19, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-12 23:39:35', '[id_pk_warehouse: 0][warehouse_nama: erwer][warehouse_alamat: 34234][warehouse_notelp: 234234][warehouse_desc: 24342][id_fk_cabang: 0][warehouse_status: AKTIF][warehouse_create_date: 2021-04-12 11:39:35][warehouse_last_modified: 2021-04-12 11:39:35][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:39:35'),
(20, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-12 23:41:26', '[id_pk_warehouse: 0][warehouse_nama: aaaa][warehouse_alamat: 234][warehouse_notelp: 3][warehouse_desc: 234][id_fk_cabang: -1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-12 11:41:26][warehouse_last_modified: 2021-04-12 11:41:26][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:41:26'),
(21, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-12 23:42:30', '[id_pk_warehouse: 0][warehouse_nama: aa][warehouse_alamat: aa][warehouse_notelp: aa][warehouse_desc: aa][id_fk_cabang: -1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-12 11:42:30][warehouse_last_modified: 2021-04-12 11:42:30][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:42:30'),
(22, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-12 23:42:42', '[id_pk_warehouse: 0][warehouse_nama: aawewerwerwer][warehouse_alamat: aawerwerwer][warehouse_notelp: aawerwerwerwerwer][warehouse_desc: aawerwerw][id_fk_cabang: -1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-12 11:42:42][warehouse_last_modified: 2021-04-12 11:42:42][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:42:42'),
(23, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-12 23:43:31', '[id_pk_warehouse: 9][warehouse_nama: sadsd][warehouse_alamat: asdasd][warehouse_notelp: asdasd][warehouse_desc: qewqwe][id_fk_cabang: -1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-12 11:43:31][warehouse_last_modified: 2021-04-12 11:43:31][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:43:31'),
(24, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-12 23:47:31', '[id_pk_warehouse: 3 => 3][warehouse_nama: aa => aabbbb][warehouse_alamat: aa => aabbbbbbb][warehouse_notelp: aa => aabbbbbbb][warehouse_desc: aa => aabbbbbbb][id_fk_cabang: -1 => -1][warehouse_status: AKTIF => AKTIF][warehouse_create_date: 2021-04-12 11:34:17 => 2021-04-12 11:34:17][warehouse_last_modified: 2021-04-12 11:34:17 => 2021-04-12 11:47:31][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-12 23:47:31'),
(25, 'Data baru ditambahkan pada tabel tbl_warehouse_admin. Waktu penambahan: 2021-04-17 21:59:38', '[id_pk_warehouse_admin: 0][id_fk_warehouse: 4][id_fk_user: 2][warehouse_admin_status: AKTIF][warehouse_admin_create_date: 2021-04-17 09:59:38][warehouse_admin_last_modified: 2021-04-17 09:59:38][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_warehouse_admin_log dengan id_pk_warehouse_admin_log 0', 2, '2021-04-17 21:59:38'),
(26, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-21 19:42:32', '[id_pk_warehouse: 1 => 1][warehouse_nama: GUDANG => GUDANG][warehouse_alamat: Puri Indah => Puri Indah][warehouse_notelp: 12345 => 12345][warehouse_desc: - => -][id_fk_cabang: 0 => 1][warehouse_status: nonaktif => nonaktif][warehouse_create_date: 2020-06-21 11:45:42 => 2020-06-21 11:45:42][warehouse_last_modified: 2021-04-12 11:27:11 => 2021-04-12 11:27:11][id_create_data: 1 => 1][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-21 19:42:32'),
(27, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-21 19:48:40', '[id_pk_warehouse: 3 => 3][warehouse_nama: aabbbb => aabbbb][warehouse_alamat: aabbbbbbb => aabbbbbbb][warehouse_notelp: aabbbbbbb => aabbbbbbb][warehouse_desc: aabbbbbbb => aabbbbbbb][id_fk_cabang: -1 => 1][warehouse_status: AKTIF => AKTIF][warehouse_create_date: 2021-04-12 11:34:17 => 2021-04-12 11:34:17][warehouse_last_modified: 2021-04-12 11:47:31 => 2021-04-12 11:47:31][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 0', 2, '2021-04-21 19:48:40'),
(28, 'Data baru ditambahkan pada tabel tbl_warehouse_admin. Waktu penambahan: 2021-04-24 14:53:54', '[id_pk_warehouse_admin: 0][id_fk_warehouse: 7][id_fk_user: 2][warehouse_admin_status: AKTIF][warehouse_admin_create_date: 2021-04-24 02:53:54][warehouse_admin_last_modified: 2021-04-24 02:53:54][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_warehouse_admin_log dengan id_pk_warehouse_admin_log 0', 2, '2021-04-24 14:53:54'),
(29, 'Data baru ditambahkan pada tabel tbl_warehouse_admin. Waktu penambahan: 2021-04-24 14:54:27', '[id_pk_warehouse_admin: 0][id_fk_warehouse: 7][id_fk_user: 2][warehouse_admin_status: AKTIF][warehouse_admin_create_date: 2021-04-24 02:54:27][warehouse_admin_last_modified: 2021-04-24 02:54:27][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_warehouse_admin_log dengan id_pk_warehouse_admin_log 0', 2, '2021-04-24 14:54:27'),
(30, 'Data baru ditambahkan pada tabel tbl_warehouse_admin. Waktu penambahan: 2021-04-24 14:57:05', '[id_pk_warehouse_admin: 8][id_fk_warehouse: 7][id_fk_user: 2][warehouse_admin_status: AKTIF][warehouse_admin_create_date: 2021-04-24 02:57:05][warehouse_admin_last_modified: 2021-04-24 02:57:05][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_warehouse_admin_log dengan id_pk_warehouse_admin_log 0', 2, '2021-04-24 14:57:05'),
(31, 'Data baru ditambahkan pada tabel mstr_employee. Waktu penambahan: 2021-04-24 15:03:07', NULL, 'Refrensi log table mstr_employee_log dengan id_pk_employee_log 0', 2, '2021-04-24 15:03:07'),
(32, 'Data baru ditambahkan pada tabel mstr_user. Waktu penambahan: 2021-04-24 15:26:19', '[id_pk_user: 3][user_name: wivina][user_pass: 25d55ad283aa400af464c76d713c07ad][user_email: daicy.choice@gmail.com][user_status: AKTIF][id_fk_role: 1][id_fk_employee: 2][user_last_modified: 2021-04-24 03:26:19][user_create_date: 2021-04-24 03:26:19][id_create_date: 2][id_last_modified: 2]', 'Refrensi log table mstr_user_log dengan id_pk_user_log 2', 2, '2021-04-24 15:26:19'),
(33, 'Data baru ditambahkan pada tabel tbl_warehouse_admin. Waktu penambahan: 2021-04-24 15:27:35', '[id_pk_warehouse_admin: 9][id_fk_warehouse: 4][id_fk_user: 3][warehouse_admin_status: AKTIF][warehouse_admin_create_date: 2021-04-24 03:27:35][warehouse_admin_last_modified: 2021-04-24 03:27:35][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_warehouse_admin_log dengan id_pk_warehouse_admin_log 5', 2, '2021-04-24 15:27:35'),
(34, 'Data baru ditambahkan pada tabel tbl_brg_warehouse. Waktu penambahan: 2021-04-24 15:28:49', '[id_pk_brg_warehouse: 9][brg_warehouse_qty: 10][brg_warehouse_notes: -][brg_warehouse_status: AKTIF][id_fk_brg: 9][id_fk_warehouse: 4][brg_warehouse_create_date: 2021-04-24 03:28:49][brg_warehouse_last_modified: 2021-04-24 03:28:49][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_brg_warehouse_log dengan id_pk_brg_warehouse_log 1', 2, '2021-04-24 15:28:49'),
(35, 'Data diubah pada tabel tbl_toko_admin. Waktu perubahan: 2021-04-24 15:57:36', '[id_pk_toko_admin: 4 => 4][id_fk_toko: 1 => 1][id_fk_user: 3 => 3][toko_admin_status: AKTIF => nonaktif][toko_admin_create_date: 2020-06-22 05:20:09 => 2020-06-22 05:20:09][toko_admin_last_modified: 2020-06-22 05:20:09 => 2021-04-24 03:57:36][id_create_data: 1 => 1][id_last_modified: 1 => 2]', 'Refrensi log table tbl_toko_admin_log dengan id_pk_toko_admin_log 1', 2, '2021-04-24 15:57:36'),
(36, 'Data baru ditambahkan pada tabel mstr_retur. Waktu penambahan: 2021-04-25 01:11:32', NULL, 'Refrensi log table mstr_retur_log dengan id_pk_retur_log 1', 2, '2021-04-25 01:11:32'),
(37, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-25 15:49:43', '[id_pk_warehouse: 10][warehouse_nama: qwerty][warehouse_alamat: qwerty][warehouse_notelp: 09822][warehouse_desc: qwedwed][id_fk_cabang: -1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-25 03:49:43][warehouse_last_modified: 2021-04-25 03:49:43][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 12', 2, '2021-04-25 15:49:43'),
(38, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-25 16:13:17', '[id_pk_warehouse: 11][warehouse_nama: asdzxc][warehouse_alamat: qweasd][warehouse_notelp: 123450987654][warehouse_desc: asdasdasdas][id_fk_cabang: 1][warehouse_status: AKTIF][warehouse_create_date: 2021-04-25 04:13:17][warehouse_last_modified: 2021-04-25 04:13:17][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 13', 2, '2021-04-25 16:13:17'),
(39, 'Data baru ditambahkan pada tabel mstr_warehouse. Waktu penambahan: 2021-04-25 16:22:09', '[id_pk_warehouse: 12][warehouse_nama: asdadsc][warehouse_alamat: sadvsrf][warehouse_notelp: 56756][warehouse_desc: jhkjk][id_fk_cabang: 2][warehouse_status: AKTIF][warehouse_create_date: 2021-04-25 04:22:09][warehouse_last_modified: 2021-04-25 04:22:09][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 14', 2, '2021-04-25 16:22:09'),
(40, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-25 16:36:25', '[id_pk_warehouse: 4 => 4][warehouse_nama: aa => aabb][warehouse_alamat: aa => aabb][warehouse_notelp: aa => aabb][warehouse_desc: aa => aabb][id_fk_cabang: -1 => -1][warehouse_status: AKTIF => AKTIF][warehouse_create_date: 2021-04-12 11:34:20 => 2021-04-12 11:34:20][warehouse_last_modified: 2021-04-12 11:34:20 => 2021-04-25 04:36:25][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 15', 2, '2021-04-25 16:36:25'),
(41, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-25 16:36:41', '[id_pk_warehouse: 4 => 4][warehouse_nama: aabb => aabb][warehouse_alamat: aabb => aabb][warehouse_notelp: aabb => aabb][warehouse_desc: aabb => aabb][id_fk_cabang: -1 => 2][warehouse_status: AKTIF => AKTIF][warehouse_create_date: 2021-04-12 11:34:20 => 2021-04-12 11:34:20][warehouse_last_modified: 2021-04-25 04:36:25 => 2021-04-25 04:36:41][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 16', 2, '2021-04-25 16:36:41'),
(42, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-25 16:39:21', '[id_pk_warehouse: 4 => 4][warehouse_nama: aabb => aabb][warehouse_alamat: aabb => aabb][warehouse_notelp: aabb => aabb][warehouse_desc: aabb => aabb][id_fk_cabang: 2 => 2][warehouse_status: AKTIF => nonaktif][warehouse_create_date: 2021-04-12 11:34:20 => 2021-04-12 11:34:20][warehouse_last_modified: 2021-04-25 04:36:41 => 2021-04-25 04:39:21][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 17', 2, '2021-04-25 16:39:21'),
(43, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-25 20:52:46', '[id_pk_warehouse: 6 => 6][warehouse_nama: aaaa => aaaa][warehouse_alamat: 234 => 234][warehouse_notelp: 3 => 3][warehouse_desc: 234 => 234][id_fk_cabang: -1 => 1][warehouse_status: AKTIF => AKTIF][warehouse_create_date: 2021-04-12 11:41:26 => 2021-04-12 11:41:26][warehouse_last_modified: 2021-04-12 11:41:26 => 2021-04-12 11:41:26][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 18', 2, '2021-04-25 20:52:46'),
(44, 'Data diubah pada tabel mstr_warehouse. Waktu perubahan: 2021-04-25 22:05:25', '[id_pk_warehouse: 6 => 6][warehouse_nama: aaaa => aaaa][warehouse_alamat: 234 => 234][warehouse_notelp: 3 => 3][warehouse_desc: 234 => 234][id_fk_cabang: 1 => 1][warehouse_status: AKTIF => nonaktif][warehouse_create_date: 2021-04-12 11:41:26 => 2021-04-12 11:41:26][warehouse_last_modified: 2021-04-12 11:41:26 => 2021-04-25 10:05:25][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table mstr_warehouse_log dengan id_pk_warehouse_log 19', 2, '2021-04-25 22:05:25'),
(45, 'Data baru ditambahkan pada tabel mstr_barang_jenis. Waktu penambahan: 2021-04-25 22:51:58', '[id_pk_brg_jenis: 19][brg_jenis_nama: Kantor][brg_jenis_status: aktif][brg_jenis_create_date: 2021-04-25 22:51:31][brg_jenis_last_modified: 2021-04-25 22:51:31][id_create_data: 1][id_last_modified: 1]', 'Refrensi log table mstr_barang_jenis_log dengan id_pk_brg_jenis_log 1', 1, '2021-04-25 22:51:58'),
(46, 'Data diubah pada tabel mstr_barang_jenis. Waktu perubahan: 2021-04-25 22:52:06', '[id_pk_brg_jenis: 19 => 0][brg_jenis_nama: Kantor => Kantor][brg_jenis_status: aktif => aktif][brg_jenis_create_date: 2021-04-25 22:51:31 => 2021-04-25 22:51:31][brg_jenis_last_modified: 2021-04-25 22:51:31 => 2021-04-25 22:51:31][id_create_data: 1 => 1][id_last_modified: 1 => 1]', 'Refrensi log table mstr_barang_jenis_log dengan id_pk_brg_jenis_log 2', 1, '2021-04-25 22:52:06'),
(47, 'Data diubah pada tabel mstr_barang_jenis. Waktu perubahan: 2021-04-25 22:57:09', '[id_pk_brg_jenis: 0 => 0][brg_jenis_nama: Kantor => BARANG KANTOR][brg_jenis_status: aktif => aktif][brg_jenis_create_date: 2021-04-25 22:51:31 => 2021-04-25 22:51:31][brg_jenis_last_modified: 2021-04-25 22:51:31 => 2021-04-25 22:51:31][id_create_data: 1 => 1][id_last_modified: 1 => 1]', 'Refrensi log table mstr_barang_jenis_log dengan id_pk_brg_jenis_log 3', 1, '2021-04-25 22:57:09'),
(48, 'Data diubah pada tabel mstr_barang. Waktu perubahan: 2021-04-25 23:12:44', '[id_pk_brg: 1 => 1][brg_kode: - => -][brg_nama: Sepatu => Sepatu][brg_ket: -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44 => -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44][brg_minimal: 0 => 0][brg_satuan: psg => psg][brg_image:  => ][brg_harga: 165000 => 165000][brg_tipe: nonkombinasi => nonkombinasi][brg_status: aktif => aktif][brg_create_date: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][brg_last_modified: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][id_create_data: 1 => 1][id_last_modified: 1 => 1][id_fk_brg_jenis: 14 => 0][id_fk_brg_merk: 25 => 25]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 2', 1, '2021-04-25 23:12:44'),
(49, 'Data diubah pada tabel mstr_barang. Waktu perubahan: 2021-04-25 23:12:48', '[id_pk_brg: 3 => 3][brg_kode: - => -][brg_nama: HELM CLIMB => HELM CLIMB][brg_ket:  => ][brg_minimal: 0 => 0][brg_satuan:  => ][brg_image:  => ][brg_harga: 550000 => 550000][brg_tipe: nonkombinasi => nonkombinasi][brg_status: aktif => aktif][brg_create_date: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][brg_last_modified: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][id_create_data: 1 => 1][id_last_modified: 1 => 1][id_fk_brg_jenis: 6 => 0][id_fk_brg_merk: 28 => 28]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 3', 1, '2021-04-25 23:12:48'),
(50, 'Data diubah pada tabel mstr_barang. Waktu perubahan: 2021-04-25 23:12:58', '[id_pk_brg: 1 => 1][brg_kode: - => -][brg_nama: Sepatu => Sepatu xxx][brg_ket: -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44 => -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44][brg_minimal: 0 => 0][brg_satuan: psg => psg][brg_image:  => ][brg_harga: 165000 => 165000][brg_tipe: nonkombinasi => nonkombinasi][brg_status: aktif => aktif][brg_create_date: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][brg_last_modified: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][id_create_data: 1 => 1][id_last_modified: 1 => 1][id_fk_brg_jenis: 0 => 0][id_fk_brg_merk: 25 => 25]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 4', 1, '2021-04-25 23:12:58'),
(51, 'Data diubah pada tabel mstr_barang. Waktu perubahan: 2021-04-25 23:13:26', '[id_pk_brg: 1 => 1][brg_kode: - => -][brg_nama: Sepatu xxx => Sepatu xxx][brg_ket: -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44 => -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44][brg_minimal: 0 => 0][brg_satuan: psg => psg][brg_image:  => ][brg_harga: 165000 => 165000][brg_tipe: nonkombinasi => nonkombinasi][brg_status: aktif => aktif][brg_create_date: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][brg_last_modified: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][id_create_data: 1 => 1][id_last_modified: 1 => 1][id_fk_brg_jenis: 0 => 1][id_fk_brg_merk: 25 => 25]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 5', 1, '2021-04-25 23:13:26'),
(52, 'Data diubah pada tabel mstr_barang. Waktu perubahan: 2021-04-25 23:14:30', '[id_pk_brg: 1 => 1][brg_kode: - => -][brg_nama: Sepatu xxx => Sepatu xxx][brg_ket: -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44 => -Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44][brg_minimal: 0 => 0][brg_satuan: psg => psg][brg_image:  => ][brg_harga: 165000 => 165000][brg_tipe: nonkombinasi => nonkombinasi][brg_status: aktif => aktif][brg_create_date: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][brg_last_modified: 2020-07-29 12:15:30 => 2020-07-29 12:15:30][id_create_data: 1 => 1][id_last_modified: 1 => 1][id_fk_brg_jenis: 1 => 0][id_fk_brg_merk: 25 => 25]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 6', 1, '2021-04-25 23:14:30'),
(53, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-25 23:14:59', '[id_pk_brg_cabang: 1 => 1][brg_cabang_qty: 330 => 330][brg_cabang_notes: - => -][brg_cabang_status: AKTIF => AKTIF][brg_cabang_last_price: 5000 => 5000][id_fk_brg: 23 => 1][id_fk_cabang: 2 => 2][brg_cabang_create_date: 2020-07-26 09:43:43 => 2020-07-26 09:43:43][brg_cabang_last_modified: 2020-07-27 08:58:14 => 2020-07-27 08:58:14][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 3', 2, '2021-04-25 23:14:59'),
(54, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-25 23:15:05', '[id_pk_brg_cabang: 2 => 2][brg_cabang_qty: 44660 => 44660][brg_cabang_notes: Auto insert from checking construct => Auto insert from checking construct][brg_cabang_status: aktif => aktif][brg_cabang_last_price: 1300 => 1300][id_fk_brg: 24 => 3][id_fk_cabang: 2 => 2][brg_cabang_create_date: 2020-07-26 09:43:43 => 2020-07-26 09:43:43][brg_cabang_last_modified: 2020-07-27 08:58:14 => 2020-07-27 08:58:14][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 4', 2, '2021-04-25 23:15:05'),
(55, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-25 23:15:29', '[id_pk_brg_cabang: 1 => 1][brg_cabang_qty: 330 => 330][brg_cabang_notes: - => -][brg_cabang_status: AKTIF => AKTIF][brg_cabang_last_price: 5000 => 5000][id_fk_brg: 1 => 1][id_fk_cabang: 2 => 1][brg_cabang_create_date: 2020-07-26 09:43:43 => 2020-07-26 09:43:43][brg_cabang_last_modified: 2020-07-27 08:58:14 => 2020-07-27 08:58:14][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 5', 2, '2021-04-25 23:15:29'),
(56, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-25 23:15:32', '[id_pk_brg_cabang: 2 => 2][brg_cabang_qty: 44660 => 44660][brg_cabang_notes: Auto insert from checking construct => Auto insert from checking construct][brg_cabang_status: aktif => aktif][brg_cabang_last_price: 1300 => 1300][id_fk_brg: 3 => 3][id_fk_cabang: 2 => 1][brg_cabang_create_date: 2020-07-26 09:43:43 => 2020-07-26 09:43:43][brg_cabang_last_modified: 2020-07-27 08:58:14 => 2020-07-27 08:58:14][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 6', 2, '2021-04-25 23:15:32'),
(57, 'Data baru ditambahkan pada tabel tbl_brg_pindah. Waktu penambahan: 2021-04-25 23:51:22', '[id_pk_brg_pindah: 6][brg_pindah_sumber: penjualan][id_fk_refrensi_sumber: 0][id_brg_awal: 23][id_brg_tujuan: 24][id_fk_cabang: 1][brg_pindah_qty: 10][brg_pindah_status: AKTIF][brg_pindah_create_date: 2021-04-25 23:51:22][brg_pindah_last_modified: 2021-04-25 23:51:22][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 4', 2, '2021-04-25 23:51:22'),
(58, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-25 23:51:22', '[id_pk_brg_cabang: 12 => 12][brg_cabang_qty: 40 => 30][brg_cabang_notes: - => -][brg_cabang_status: AKTIF => AKTIF][brg_cabang_last_price: 0 => 0][id_fk_brg: 23 => 23][id_fk_cabang: 1 => 1][brg_cabang_create_date: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][brg_cabang_last_modified: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 7', 2, '2021-04-25 23:51:22'),
(59, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-25 23:51:22', '[id_pk_brg_cabang: 13 => 13][brg_cabang_qty: 2000 => 2010][brg_cabang_notes: Auto insert from checking construct => Auto insert from checking construct][brg_cabang_status: aktif => aktif][brg_cabang_last_price: 0 => 0][id_fk_brg: 24 => 24][id_fk_cabang: 1 => 1][brg_cabang_create_date: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][brg_cabang_last_modified: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 8', 2, '2021-04-25 23:51:22'),
(60, 'Data baru ditambahkan pada tabel mstr_penjualan. Waktu penambahan: 2021-04-25 23:52:04', '[id_pk_penjualan: 9][penj_nomor: CBGKRC1-PENJUALAN-2021-04-25-000001][penj_nominal: 0][penj_nominal_byr: 0][penj_tgl: 2021-04-25 00:00:00][penj_dateline_tgl: 2021-04-27 00:00:00][penj_jenis: OFFLINE][penj_tipe_pembayaran: FULL PAYMENT][penj_status: AKTIF][id_fk_customer: 2][id_fk_cabang: 1][penj_create_date: 2021-04-25 11:52:04][penj_last_modified: 2021-04-25 11:52:04][id_create_data: 2][id_last_modified: 2][no_control: 1][bln_control: 4][thn_control: 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 4', 2, '2021-04-25 23:52:04'),
(61, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-25 23:52:04', '[id_pk_brg_pindah: 6 => 6][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 0 => 9][id_brg_awal: 23 => 23][id_brg_tujuan: 24 => 24][id_fk_cabang: 1 => 1][brg_pindah_qty: 10 => 10][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-25 23:51:22 => 2021-04-25 23:51:22][brg_pindah_last_modified: 2021-04-25 23:51:22 => 2021-04-25 23:51:22][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 5', 2, '2021-04-25 23:52:04'),
(62, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-25 23:52:04', '[id_pk_penjualan: 9 => 9][penj_nomor: CBGKRC1-PENJUALAN-2021-04-25-000001 => CBGKRC1-PENJUALAN-2021-04-25-000001][penj_nominal: 0 => 101000][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-25 00:00:00 => 2021-04-25 00:00:00][penj_dateline_tgl: 2021-04-27 00:00:00 => 2021-04-27 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 2 => 2][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-25 11:52:04 => 2021-04-25 11:52:04][penj_last_modified: 2021-04-25 11:52:04 => 2021-04-25 11:52:04][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 1 => 1][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 5', 2, '2021-04-25 23:52:04'),
(63, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-25 23:52:04', '[id_pk_penjualan: 9 => 9][penj_nomor: CBGKRC1-PENJUALAN-2021-04-25-000001 => CBGKRC1-PENJUALAN-2021-04-25-000001][penj_nominal: 101000 => 101000][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-25 00:00:00 => 2021-04-25 00:00:00][penj_dateline_tgl: 2021-04-27 00:00:00 => 2021-04-27 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 2 => 2][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-25 11:52:04 => 2021-04-25 11:52:04][penj_last_modified: 2021-04-25 11:52:04 => 2021-04-25 11:52:04][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 1 => 1][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 6', 2, '2021-04-25 23:52:04'),
(64, 'Data baru ditambahkan pada tabel mstr_retur. Waktu penambahan: 2021-04-25 23:53:20', NULL, 'Refrensi log table mstr_retur_log dengan id_pk_retur_log 2', 2, '2021-04-25 23:53:20'),
(65, 'Data baru ditambahkan pada tabel mstr_retur. Waktu penambahan: 2021-04-25 23:56:10', NULL, 'Refrensi log table mstr_retur_log dengan id_pk_retur_log 3', 2, '2021-04-25 23:56:10'),
(66, 'Data diubah pada tabel mstr_retur. Waktu perubahan: 2021-04-29 21:01:53', NULL, 'Refrensi log table mstr_retur_log dengan id_pk_retur_log 4', 2, '2021-04-29 21:01:53'),
(67, 'Data diubah pada tabel mstr_retur. Waktu perubahan: 2021-04-29 21:02:01', NULL, 'Refrensi log table mstr_retur_log dengan id_pk_retur_log 5', 2, '2021-04-29 21:02:01'),
(68, 'Data baru ditambahkan pada tabel tbl_brg_pindah. Waktu penambahan: 2021-04-29 21:05:17', '[id_pk_brg_pindah: 7][brg_pindah_sumber: penjualan][id_fk_refrensi_sumber: 0][id_brg_awal: 13][id_brg_tujuan: 20][id_fk_cabang: 1][brg_pindah_qty: 10][brg_pindah_status: AKTIF][brg_pindah_create_date: 2021-04-29 21:05:17][brg_pindah_last_modified: 2021-04-29 21:05:17][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 6', 2, '2021-04-29 21:05:17'),
(69, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-29 21:05:17', '[id_pk_brg_cabang: 14 => 14][brg_cabang_qty: 1000 => 990][brg_cabang_notes: Auto insert from checking construct => Auto insert from checking construct][brg_cabang_status: aktif => aktif][brg_cabang_last_price: 0 => 0][id_fk_brg: 13 => 13][id_fk_cabang: 1 => 1][brg_cabang_create_date: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][brg_cabang_last_modified: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 9', 2, '2021-04-29 21:05:17'),
(70, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-29 21:05:17', '[id_pk_brg_cabang: 16 => 16][brg_cabang_qty: 500 => 510][brg_cabang_notes: - => -][brg_cabang_status: AKTIF => AKTIF][brg_cabang_last_price: 15000 => 15000][id_fk_brg: 20 => 20][id_fk_cabang: 1 => 1][brg_cabang_create_date: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][brg_cabang_last_modified: 2020-07-29 08:47:06 => 2020-07-29 08:47:06][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 10', 2, '2021-04-29 21:05:17'),
(71, 'Data baru ditambahkan pada tabel tbl_brg_pindah. Waktu penambahan: 2021-04-29 21:05:38', '[id_pk_brg_pindah: 8][brg_pindah_sumber: penjualan][id_fk_refrensi_sumber: 0][id_brg_awal: 24][id_brg_tujuan: 2][id_fk_cabang: 1][brg_pindah_qty: 20][brg_pindah_status: AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38][id_create_data: 2][id_last_modified: 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 7', 2, '2021-04-29 21:05:38'),
(72, 'Data diubah pada tabel tbl_brg_cabang. Waktu perubahan: 2021-04-29 21:05:38', '[id_pk_brg_cabang: 13 => 13][brg_cabang_qty: 2010 => 1990][brg_cabang_notes: Auto insert from checking construct => Auto insert from checking construct][brg_cabang_status: aktif => aktif][brg_cabang_last_price: 0 => 0][id_fk_brg: 24 => 24][id_fk_cabang: 1 => 1][brg_cabang_create_date: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][brg_cabang_last_modified: 2020-07-28 12:33:53 => 2020-07-28 12:33:53][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_cabang_log dengan id_pk_brg_cabang_log 11', 2, '2021-04-29 21:05:38'),
(73, 'Data baru ditambahkan pada tabel mstr_penjualan. Waktu penambahan: 2021-04-29 21:06:59', '[id_pk_penjualan: 10][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000002][penj_nominal: 0][penj_nominal_byr: 0][penj_tgl: 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00][penj_jenis: OFFLINE][penj_tipe_pembayaran: FULL PAYMENT][penj_status: AKTIF][id_fk_customer: 4][id_fk_cabang: 1][penj_create_date: 2021-04-29 09:06:59][penj_last_modified: 2021-04-29 09:06:59][id_create_data: 2][id_last_modified: 2][no_control: 2][bln_control: 4][thn_control: 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 7', 2, '2021-04-29 21:06:59'),
(74, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:06:59', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 0 => 10][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 8', 2, '2021-04-29 21:06:59'),
(75, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:06:59', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 10 => 10][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 9', 2, '2021-04-29 21:06:59'),
(76, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:06:59', '[id_pk_penjualan: 10 => 10][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000002 => CBGKRC1-PENJUALAN-2021-04-29-000002][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:06:59 => 2021-04-29 09:06:59][penj_last_modified: 2021-04-29 09:06:59 => 2021-04-29 09:06:59][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 2 => 2][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 8', 2, '2021-04-29 21:06:59'),
(77, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:06:59', '[id_pk_penjualan: 10 => 10][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000002 => CBGKRC1-PENJUALAN-2021-04-29-000002][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:06:59 => 2021-04-29 09:06:59][penj_last_modified: 2021-04-29 09:06:59 => 2021-04-29 09:06:59][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 2 => 2][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 9', 2, '2021-04-29 21:06:59'),
(78, 'Data baru ditambahkan pada tabel mstr_penjualan. Waktu penambahan: 2021-04-29 21:07:07', '[id_pk_penjualan: 11][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000003][penj_nominal: 0][penj_nominal_byr: 0][penj_tgl: 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00][penj_jenis: OFFLINE][penj_tipe_pembayaran: FULL PAYMENT][penj_status: AKTIF][id_fk_customer: 4][id_fk_cabang: 1][penj_create_date: 2021-04-29 09:07:07][penj_last_modified: 2021-04-29 09:07:07][id_create_data: 2][id_last_modified: 2][no_control: 3][bln_control: 4][thn_control: 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 10', 2, '2021-04-29 21:07:07'),
(79, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:07:07', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 10 => 11][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 10', 2, '2021-04-29 21:07:07'),
(80, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:07:07', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 11 => 11][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 11', 2, '2021-04-29 21:07:07'),
(81, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:07:07', '[id_pk_penjualan: 11 => 11][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000003 => CBGKRC1-PENJUALAN-2021-04-29-000003][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:07:07 => 2021-04-29 09:07:07][penj_last_modified: 2021-04-29 09:07:07 => 2021-04-29 09:07:07][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 3 => 3][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 11', 2, '2021-04-29 21:07:07'),
(82, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:07:07', '[id_pk_penjualan: 11 => 11][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000003 => CBGKRC1-PENJUALAN-2021-04-29-000003][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:07:07 => 2021-04-29 09:07:07][penj_last_modified: 2021-04-29 09:07:07 => 2021-04-29 09:07:07][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 3 => 3][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 12', 2, '2021-04-29 21:07:07'),
(83, 'Data baru ditambahkan pada tabel mstr_penjualan. Waktu penambahan: 2021-04-29 21:09:59', '[id_pk_penjualan: 12][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000004][penj_nominal: 0][penj_nominal_byr: 0][penj_tgl: 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00][penj_jenis: OFFLINE][penj_tipe_pembayaran: FULL PAYMENT][penj_status: AKTIF][id_fk_customer: 4][id_fk_cabang: 1][penj_create_date: 2021-04-29 09:09:59][penj_last_modified: 2021-04-29 09:09:59][id_create_data: 2][id_last_modified: 2][no_control: 4][bln_control: 4][thn_control: 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 13', 2, '2021-04-29 21:09:59'),
(84, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:09:59', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 11 => 12][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 12', 2, '2021-04-29 21:09:59'),
(85, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:09:59', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 12 => 12][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 13', 2, '2021-04-29 21:09:59'),
(86, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:09:59', '[id_pk_penjualan: 12 => 12][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000004 => CBGKRC1-PENJUALAN-2021-04-29-000004][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:09:59 => 2021-04-29 09:09:59][penj_last_modified: 2021-04-29 09:09:59 => 2021-04-29 09:09:59][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 4 => 4][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 14', 2, '2021-04-29 21:09:59'),
(87, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:09:59', '[id_pk_penjualan: 12 => 12][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000004 => CBGKRC1-PENJUALAN-2021-04-29-000004][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:09:59 => 2021-04-29 09:09:59][penj_last_modified: 2021-04-29 09:09:59 => 2021-04-29 09:09:59][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 4 => 4][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 15', 2, '2021-04-29 21:09:59');
INSERT INTO `log_all` (`id_pk_log_all`, `log_all_msg`, `log_all_data_changes`, `log_all_it`, `log_all_user`, `log_all_tgl`) VALUES
(88, 'Data baru ditambahkan pada tabel mstr_penjualan. Waktu penambahan: 2021-04-29 21:10:21', '[id_pk_penjualan: 13][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000005][penj_nominal: 0][penj_nominal_byr: 0][penj_tgl: 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00][penj_jenis: OFFLINE][penj_tipe_pembayaran: FULL PAYMENT][penj_status: AKTIF][id_fk_customer: 4][id_fk_cabang: 1][penj_create_date: 2021-04-29 09:10:21][penj_last_modified: 2021-04-29 09:10:21][id_create_data: 2][id_last_modified: 2][no_control: 5][bln_control: 4][thn_control: 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 16', 2, '2021-04-29 21:10:21'),
(89, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:10:21', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 12 => 13][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 14', 2, '2021-04-29 21:10:21'),
(90, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:10:21', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 13 => 13][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 15', 2, '2021-04-29 21:10:21'),
(91, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:10:21', '[id_pk_penjualan: 13 => 13][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000005 => CBGKRC1-PENJUALAN-2021-04-29-000005][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:10:21 => 2021-04-29 09:10:21][penj_last_modified: 2021-04-29 09:10:21 => 2021-04-29 09:10:21][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 5 => 5][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 17', 2, '2021-04-29 21:10:21'),
(92, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:10:21', '[id_pk_penjualan: 13 => 13][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000005 => CBGKRC1-PENJUALAN-2021-04-29-000005][penj_nominal: 0 => 0][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:10:21 => 2021-04-29 09:10:21][penj_last_modified: 2021-04-29 09:10:21 => 2021-04-29 09:10:21][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 5 => 5][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 18', 2, '2021-04-29 21:10:21'),
(93, 'Data baru ditambahkan pada tabel mstr_penjualan. Waktu penambahan: 2021-04-29 21:11:15', '[id_pk_penjualan: 14][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000006][penj_nominal: 0][penj_nominal_byr: 0][penj_tgl: 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00][penj_jenis: OFFLINE][penj_tipe_pembayaran: FULL PAYMENT][penj_status: AKTIF][id_fk_customer: 4][id_fk_cabang: 1][penj_create_date: 2021-04-29 09:11:15][penj_last_modified: 2021-04-29 09:11:15][id_create_data: 2][id_last_modified: 2][no_control: 6][bln_control: 4][thn_control: 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 19', 2, '2021-04-29 21:11:15'),
(94, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:11:15', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 13 => 14][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 16', 2, '2021-04-29 21:11:15'),
(95, 'Data diubah pada tabel tbl_brg_pindah. Waktu perubahan: 2021-04-29 21:11:15', '[id_pk_brg_pindah: 8 => 8][brg_pindah_sumber: penjualan => penjualan][id_fk_refrensi_sumber: 14 => 14][id_brg_awal: 24 => 24][id_brg_tujuan: 2 => 2][id_fk_cabang: 1 => 1][brg_pindah_qty: 20 => 20][brg_pindah_status: AKTIF => AKTIF][brg_pindah_create_date: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][brg_pindah_last_modified: 2021-04-29 21:05:38 => 2021-04-29 21:05:38][id_create_data: 2 => 2][id_last_modified: 2 => 2]', 'Refrensi log table tbl_brg_pindah_log dengan id_pk_brg_pindah_log 17', 2, '2021-04-29 21:11:15'),
(96, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:11:15', '[id_pk_penjualan: 14 => 14][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000006 => CBGKRC1-PENJUALAN-2021-04-29-000006][penj_nominal: 0 => 2000000][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:11:15 => 2021-04-29 09:11:15][penj_last_modified: 2021-04-29 09:11:15 => 2021-04-29 09:11:15][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 6 => 6][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 20', 2, '2021-04-29 21:11:15'),
(97, 'Data diubah pada tabel mstr_penjualan. Waktu perubahan: 2021-04-29 21:11:15', '[id_pk_penjualan: 14 => 14][penj_nomor: CBGKRC1-PENJUALAN-2021-04-29-000006 => CBGKRC1-PENJUALAN-2021-04-29-000006][penj_nominal: 2000000 => 2000000][penj_nominal_byr: 0 => 0][penj_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_dateline_tgl: 2021-04-29 00:00:00 => 2021-04-29 00:00:00][penj_jenis: OFFLINE => OFFLINE][penj_tipe_pembayaran: FULL PAYMENT => FULL PAYMENT][penj_status: AKTIF => AKTIF][id_fk_customer: 4 => 4][id_fk_cabang: 1 => 1][penj_create_date: 2021-04-29 09:11:15 => 2021-04-29 09:11:15][penj_last_modified: 2021-04-29 09:11:15 => 2021-04-29 09:11:15][id_create_data: 2 => 2][id_last_modified: 2 => 2][no_control: 6 => 6][bln_control: 4 => 4][thn_control: 2021 => 2021]', 'Refrensi log table mstr_penjualan_log dengan id_pk_penjualan_log 21', 2, '2021-04-29 21:11:15'),
(98, 'Data baru ditambahkan pada tabel mstr_retur. Waktu penambahan: 2021-04-29 21:16:25', NULL, 'Refrensi log table mstr_retur_log dengan id_pk_retur_log 6', 2, '2021-04-29 21:16:25'),
(99, 'Data baru ditambahkan pada tabel mstr_barang. Waktu penambahan: 2021-05-07 21:32:57', '[id_pk_brg: 178][brg_kode: wewe][brg_nama: qwe][brg_ket: eeee][brg_minimal: 0][brg_satuan: PCS][brg_image: noimage.jpg][brg_harga: 45343554][brg_tipe: nonkombinasi][brg_status: AKTIF][brg_create_date: 2021-05-07 09:32:57][brg_last_modified: 2021-05-07 09:32:57][id_create_data: 2][id_last_modified: 2][id_fk_brg_jenis: 1][id_fk_brg_merk: 2]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 7', 2, '2021-05-07 21:32:57'),
(100, 'Data baru ditambahkan pada tabel mstr_barang. Waktu penambahan: 2021-05-09 15:25:20', '[id_pk_brg: 179][brg_kode: qwe][brg_nama: qew][brg_ket: qwe][brg_minimal: 3][brg_satuan: PCS][brg_image: noimage.jpg][brg_harga: 123456][brg_harga_toko: 12345][brg_harga_grosir: 12345][brg_tipe: nonkombinasi][brg_status: AKTIF][brg_create_date: 2021-05-09 03:25:20][brg_last_modified: 2021-05-09 03:25:20][id_create_data: 2][id_last_modified: 2][id_fk_brg_jenis: 3][id_fk_brg_merk: 3]', 'Refrensi log table mstr_barang_log dengan id_pk_brg_log 8', 2, '2021-05-09 15:25:20'),
(101, 'Penambahan data jenis barang', 'Jenis barang: asdfasdfsadfasdfasdf', '-', 2, '2021-05-24 21:20:41'),
(102, 'Penambahan data jenis barang', 'Jenis barang: aaaaaaaaaaaaaaaaaaaaaaaaaa', '-', 2, '2021-05-24 21:24:37'),
(103, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:07:00', '[ID Barang Pengiriman: 10][Jumlah: 0][Notes: -][ID Pengiriman: 7][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:07:00][Oleh: admin]', '', 2, '2021-06-07 10:07:00'),
(104, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:07:00', '[ID Barang Pengiriman: 11][Jumlah: 0][Notes: -][ID Pengiriman: 7][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:07:00][Oleh: admin]', '', 2, '2021-06-07 10:07:00'),
(105, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:07:00', '[ID Barang Pengiriman: 12][Jumlah: 0][Notes: -][ID Pengiriman: 7][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:07:00][Oleh: admin]', '', 2, '2021-06-07 10:07:00'),
(106, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:07:00', '[ID Barang Pengiriman: 13][Jumlah: 0][Notes: -][ID Pengiriman: 7][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:07:00][Oleh: admin]', '', 2, '2021-06-07 10:07:00'),
(107, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:24:15', '[ID Barang Pengiriman: 14][Jumlah: 0][Notes: -][ID Pengiriman: 8][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:24:15][Oleh: admin]', '', 2, '2021-06-07 10:24:15'),
(108, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:24:15', '[ID Barang Pengiriman: 15][Jumlah: 0][Notes: -][ID Pengiriman: 8][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 3][Waktu Ditambahkan: 21-06-07 10:24:15][Oleh: admin]', '', 2, '2021-06-07 10:24:15'),
(109, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:24:15', '[ID Barang Pengiriman: 16][Jumlah: 0][Notes: -][ID Pengiriman: 8][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Ditambahkan: 21-06-07 10:24:15][Oleh: admin]', '', 2, '2021-06-07 10:24:15'),
(110, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:24:15', '[ID Barang Pengiriman: 17][Jumlah: 0][Notes: -][ID Pengiriman: 8][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 4][Waktu Ditambahkan: 21-06-07 10:24:15][Oleh: admin]', '', 2, '2021-06-07 10:24:15'),
(111, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:49:37', '[ID Barang Pengiriman: 18][Jumlah: 0][Notes: -][ID Pengiriman: 9][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:49:37][Oleh: admin]', '', 2, '2021-06-07 10:49:37'),
(112, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:49:37', '[ID Barang Pengiriman: 19][Jumlah: 0][Notes: -][ID Pengiriman: 9][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:49:37][Oleh: admin]', '', 2, '2021-06-07 10:49:37'),
(113, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:49:37', '[ID Barang Pengiriman: 20][Jumlah: 0][Notes: -][ID Pengiriman: 9][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:49:37][Oleh: admin]', '', 2, '2021-06-07 10:49:37'),
(114, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:49:37', '[ID Barang Pengiriman: 21][Jumlah: 0][Notes: -][ID Pengiriman: 9][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:49:37][Oleh: admin]', '', 2, '2021-06-07 10:49:37'),
(115, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:16', '[ID Barang Pengiriman: 22][Jumlah: 0][Notes: -][ID Pengiriman: 10][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:16][Oleh: admin]', '', 2, '2021-06-07 10:51:16'),
(116, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:16', '[ID Barang Pengiriman: 23][Jumlah: 0][Notes: -][ID Pengiriman: 10][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:16][Oleh: admin]', '', 2, '2021-06-07 10:51:16'),
(117, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:16', '[ID Barang Pengiriman: 24][Jumlah: 0][Notes: -][ID Pengiriman: 10][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:16][Oleh: admin]', '', 2, '2021-06-07 10:51:16'),
(118, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:16', '[ID Barang Pengiriman: 25][Jumlah: 0][Notes: -][ID Pengiriman: 10][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:16][Oleh: admin]', '', 2, '2021-06-07 10:51:16'),
(119, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:59', '[ID Barang Pengiriman: 26][Jumlah: 0][Notes: -][ID Pengiriman: 11][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:59][Oleh: admin]', '', 2, '2021-06-07 10:51:59'),
(120, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:59', '[ID Barang Pengiriman: 27][Jumlah: 0][Notes: -][ID Pengiriman: 11][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:59][Oleh: admin]', '', 2, '2021-06-07 10:51:59'),
(121, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:59', '[ID Barang Pengiriman: 28][Jumlah: 0][Notes: -][ID Pengiriman: 11][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:59][Oleh: admin]', '', 2, '2021-06-07 10:51:59'),
(122, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:51:59', '[ID Barang Pengiriman: 29][Jumlah: 0][Notes: -][ID Pengiriman: 11][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:51:59][Oleh: admin]', '', 2, '2021-06-07 10:51:59'),
(123, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:53:05', '[ID Barang Pengiriman: 30][Jumlah: 0][Notes: -][ID Pengiriman: 12][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:53:05][Oleh: admin]', '', 2, '2021-06-07 10:53:05'),
(124, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:53:05', '[ID Barang Pengiriman: 31][Jumlah: 0][Notes: -][ID Pengiriman: 12][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:53:05][Oleh: admin]', '', 2, '2021-06-07 10:53:05'),
(125, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:53:05', '[ID Barang Pengiriman: 32][Jumlah: 0][Notes: -][ID Pengiriman: 12][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:53:05][Oleh: admin]', '', 2, '2021-06-07 10:53:05'),
(126, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:53:05', '[ID Barang Pengiriman: 33][Jumlah: 0][Notes: -][ID Pengiriman: 12][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:53:05][Oleh: admin]', '', 2, '2021-06-07 10:53:05'),
(127, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:55:22', '[ID Barang Pengiriman: 34][Jumlah: 0][Notes: -][ID Pengiriman: 13][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:55:22][Oleh: admin]', '', 2, '2021-06-07 10:55:22'),
(128, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:55:22', '[ID Barang Pengiriman: 35][Jumlah: 0][Notes: -][ID Pengiriman: 13][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:55:22][Oleh: admin]', '', 2, '2021-06-07 10:55:22'),
(129, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:55:22', '[ID Barang Pengiriman: 36][Jumlah: 0][Notes: -][ID Pengiriman: 13][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:55:22][Oleh: admin]', '', 2, '2021-06-07 10:55:22'),
(130, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:55:22', '[ID Barang Pengiriman: 37][Jumlah: 0][Notes: -][ID Pengiriman: 13][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:55:22][Oleh: admin]', '', 2, '2021-06-07 10:55:22'),
(131, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:27', '[ID Barang Pengiriman: 38][Jumlah: 0][Notes: -][ID Pengiriman: 14][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:27][Oleh: admin]', '', 2, '2021-06-07 10:57:27'),
(132, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:27', '[ID Barang Pengiriman: 39][Jumlah: 0][Notes: -][ID Pengiriman: 14][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:27][Oleh: admin]', '', 2, '2021-06-07 10:57:27'),
(133, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:27', '[ID Barang Pengiriman: 40][Jumlah: 0][Notes: -][ID Pengiriman: 14][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:27][Oleh: admin]', '', 2, '2021-06-07 10:57:27'),
(134, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:27', '[ID Barang Pengiriman: 41][Jumlah: 0][Notes: -][ID Pengiriman: 14][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:27][Oleh: admin]', '', 2, '2021-06-07 10:57:27'),
(135, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:55', '[ID Barang Pengiriman: 42][Jumlah: 0][Notes: -][ID Pengiriman: 15][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:55][Oleh: admin]', '', 2, '2021-06-07 10:57:55'),
(136, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:55', '[ID Barang Pengiriman: 43][Jumlah: 0][Notes: -][ID Pengiriman: 15][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:55][Oleh: admin]', '', 2, '2021-06-07 10:57:55'),
(137, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:55', '[ID Barang Pengiriman: 44][Jumlah: 0][Notes: -][ID Pengiriman: 15][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:55][Oleh: admin]', '', 2, '2021-06-07 10:57:55'),
(138, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:57:55', '[ID Barang Pengiriman: 45][Jumlah: 0][Notes: -][ID Pengiriman: 15][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:57:55][Oleh: admin]', '', 2, '2021-06-07 10:57:55'),
(139, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:16', '[ID Barang Pengiriman: 46][Jumlah: 0][Notes: -][ID Pengiriman: 16][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:16][Oleh: admin]', '', 2, '2021-06-07 10:58:16'),
(140, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:16', '[ID Barang Pengiriman: 47][Jumlah: 0][Notes: -][ID Pengiriman: 16][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:16][Oleh: admin]', '', 2, '2021-06-07 10:58:16'),
(141, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:16', '[ID Barang Pengiriman: 48][Jumlah: 0][Notes: -][ID Pengiriman: 16][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:16][Oleh: admin]', '', 2, '2021-06-07 10:58:16'),
(142, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:16', '[ID Barang Pengiriman: 49][Jumlah: 0][Notes: -][ID Pengiriman: 16][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:16][Oleh: admin]', '', 2, '2021-06-07 10:58:16'),
(143, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:37', '[ID Barang Pengiriman: 50][Jumlah: 0][Notes: -][ID Pengiriman: 17][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:37][Oleh: admin]', '', 2, '2021-06-07 10:58:37'),
(144, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:37', '[ID Barang Pengiriman: 51][Jumlah: 0][Notes: -][ID Pengiriman: 17][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:37][Oleh: admin]', '', 2, '2021-06-07 10:58:37'),
(145, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:37', '[ID Barang Pengiriman: 52][Jumlah: 0][Notes: -][ID Pengiriman: 17][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:37][Oleh: admin]', '', 2, '2021-06-07 10:58:37'),
(146, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:58:37', '[ID Barang Pengiriman: 53][Jumlah: 0][Notes: -][ID Pengiriman: 17][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:58:37][Oleh: admin]', '', 2, '2021-06-07 10:58:37'),
(147, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:05', '[ID Barang Pengiriman: 54][Jumlah: 0][Notes: -][ID Pengiriman: 18][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:05][Oleh: admin]', '', 2, '2021-06-07 10:59:05'),
(148, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:05', '[ID Barang Pengiriman: 55][Jumlah: 0][Notes: -][ID Pengiriman: 18][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:05][Oleh: admin]', '', 2, '2021-06-07 10:59:05'),
(149, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:05', '[ID Barang Pengiriman: 56][Jumlah: 0][Notes: -][ID Pengiriman: 18][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:05][Oleh: admin]', '', 2, '2021-06-07 10:59:05'),
(150, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:05', '[ID Barang Pengiriman: 57][Jumlah: 0][Notes: -][ID Pengiriman: 18][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:05][Oleh: admin]', '', 2, '2021-06-07 10:59:05'),
(151, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:21', '[ID Barang Pengiriman: 58][Jumlah: 0][Notes: -][ID Pengiriman: 19][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:21][Oleh: admin]', '', 2, '2021-06-07 10:59:21'),
(152, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:21', '[ID Barang Pengiriman: 59][Jumlah: 0][Notes: -][ID Pengiriman: 19][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:21][Oleh: admin]', '', 2, '2021-06-07 10:59:21'),
(153, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:21', '[ID Barang Pengiriman: 60][Jumlah: 0][Notes: -][ID Pengiriman: 19][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:21][Oleh: admin]', '', 2, '2021-06-07 10:59:21'),
(154, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:21', '[ID Barang Pengiriman: 61][Jumlah: 0][Notes: -][ID Pengiriman: 19][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:21][Oleh: admin]', '', 2, '2021-06-07 10:59:21'),
(155, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:34', '[ID Barang Pengiriman: 62][Jumlah: 0][Notes: -][ID Pengiriman: 20][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:34][Oleh: admin]', '', 2, '2021-06-07 10:59:34'),
(156, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:34', '[ID Barang Pengiriman: 63][Jumlah: 0][Notes: -][ID Pengiriman: 20][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:34][Oleh: admin]', '', 2, '2021-06-07 10:59:34'),
(157, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:34', '[ID Barang Pengiriman: 64][Jumlah: 0][Notes: -][ID Pengiriman: 20][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:34][Oleh: admin]', '', 2, '2021-06-07 10:59:34'),
(158, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:34', '[ID Barang Pengiriman: 65][Jumlah: 0][Notes: -][ID Pengiriman: 20][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:34][Oleh: admin]', '', 2, '2021-06-07 10:59:34'),
(159, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:46', '[ID Barang Pengiriman: 66][Jumlah: 0][Notes: -][ID Pengiriman: 21][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:46][Oleh: admin]', '', 2, '2021-06-07 10:59:46'),
(160, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:46', '[ID Barang Pengiriman: 67][Jumlah: 0][Notes: -][ID Pengiriman: 21][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:46][Oleh: admin]', '', 2, '2021-06-07 10:59:46'),
(161, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:46', '[ID Barang Pengiriman: 68][Jumlah: 0][Notes: -][ID Pengiriman: 21][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:46][Oleh: admin]', '', 2, '2021-06-07 10:59:46'),
(162, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 10:59:46', '[ID Barang Pengiriman: 69][Jumlah: 0][Notes: -][ID Pengiriman: 21][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 10:59:46][Oleh: admin]', '', 2, '2021-06-07 10:59:46'),
(163, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:00:20', '[ID Barang Pengiriman: 70][Jumlah: 0][Notes: -][ID Pengiriman: 22][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:00:20][Oleh: admin]', '', 2, '2021-06-07 11:00:20'),
(164, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:00:20', '[ID Barang Pengiriman: 71][Jumlah: 0][Notes: -][ID Pengiriman: 22][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:00:20][Oleh: admin]', '', 2, '2021-06-07 11:00:20'),
(165, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:00:20', '[ID Barang Pengiriman: 72][Jumlah: 0][Notes: -][ID Pengiriman: 22][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:00:20][Oleh: admin]', '', 2, '2021-06-07 11:00:20'),
(166, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:00:20', '[ID Barang Pengiriman: 73][Jumlah: 0][Notes: -][ID Pengiriman: 22][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:00:20][Oleh: admin]', '', 2, '2021-06-07 11:00:20'),
(167, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:01:22', '[ID Barang Pengiriman: 74][Jumlah: 0][Notes: -][ID Pengiriman: 23][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:01:22][Oleh: admin]', '', 2, '2021-06-07 11:01:22'),
(168, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:01:22', '[ID Barang Pengiriman: 75][Jumlah: 0][Notes: -][ID Pengiriman: 23][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:01:22][Oleh: admin]', '', 2, '2021-06-07 11:01:22'),
(169, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:01:22', '[ID Barang Pengiriman: 76][Jumlah: 0][Notes: -][ID Pengiriman: 23][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:01:22][Oleh: admin]', '', 2, '2021-06-07 11:01:22'),
(170, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:01:22', '[ID Barang Pengiriman: 77][Jumlah: 0][Notes: -][ID Pengiriman: 23][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:01:22][Oleh: admin]', '', 2, '2021-06-07 11:01:22'),
(171, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:08:48', '[ID Barang Pengiriman: 78][Jumlah: 1][Notes: -][ID Pengiriman: 24][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:08:48][Oleh: admin]', '', 2, '2021-06-07 11:08:48'),
(172, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:08:48', '[ID Barang Pengiriman: 79][Jumlah: 2][Notes: -][ID Pengiriman: 24][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:08:48][Oleh: admin]', '', 2, '2021-06-07 11:08:48'),
(173, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:08:48', '[ID Barang Pengiriman: 80][Jumlah: 2][Notes: -][ID Pengiriman: 24][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:08:48][Oleh: admin]', '', 2, '2021-06-07 11:08:48'),
(174, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:08:48', '[ID Barang Pengiriman: 81][Jumlah: 1][Notes: -][ID Pengiriman: 24][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:08:48][Oleh: admin]', '', 2, '2021-06-07 11:08:48'),
(175, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:18', '[ID Barang Pengiriman: 82][Jumlah: 1][Notes: -][ID Pengiriman: 25][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:18][Oleh: admin]', '', 2, '2021-06-07 11:09:18'),
(176, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:18', '[ID Barang Pengiriman: 83][Jumlah: 2][Notes: -][ID Pengiriman: 25][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:18][Oleh: admin]', '', 2, '2021-06-07 11:09:18'),
(177, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:18', '[ID Barang Pengiriman: 84][Jumlah: 2][Notes: -][ID Pengiriman: 25][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:18][Oleh: admin]', '', 2, '2021-06-07 11:09:18'),
(178, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:18', '[ID Barang Pengiriman: 85][Jumlah: 1][Notes: -][ID Pengiriman: 25][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:18][Oleh: admin]', '', 2, '2021-06-07 11:09:18'),
(179, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:45', '[ID Barang Pengiriman: 86][Jumlah: 123][Notes: -][ID Pengiriman: 26][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 1][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:45][Oleh: admin]', '', 2, '2021-06-07 11:09:45'),
(180, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:45', '[ID Barang Pengiriman: 87][Jumlah: 12][Notes: -][ID Pengiriman: 26][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 2][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:45][Oleh: admin]', '', 2, '2021-06-07 11:09:45'),
(181, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:45', '[ID Barang Pengiriman: 88][Jumlah: 1][Notes: -][ID Pengiriman: 26][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 3][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:45][Oleh: admin]', '', 2, '2021-06-07 11:09:45'),
(182, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:09:45', '[ID Barang Pengiriman: 89][Jumlah: 2][Notes: -][ID Pengiriman: 26][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 4][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:09:45][Oleh: admin]', '', 2, '2021-06-07 11:09:45'),
(183, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:10:17', '[ID Barang Pengiriman: 90][Jumlah: 0][Notes: -][ID Pengiriman: 27][ID Barang Penjualan: 3][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:10:17][Oleh: admin]', '', 2, '2021-06-07 11:10:17'),
(184, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:10:17', '[ID Barang Pengiriman: 91][Jumlah: 0][Notes: -][ID Pengiriman: 27][ID Barang Penjualan: 4][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:10:17][Oleh: admin]', '', 2, '2021-06-07 11:10:17'),
(185, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:12:59', '[ID Barang Pengiriman: 92][Jumlah: 1][Notes: -][ID Pengiriman: 28][ID Barang Penjualan: 3][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:12:59][Oleh: admin]', '', 2, '2021-06-07 11:12:59'),
(186, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:12:59', '[ID Barang Pengiriman: 93][Jumlah: 3][Notes: -][ID Pengiriman: 28][ID Barang Penjualan: 4][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:12:59][Oleh: admin]', '', 2, '2021-06-07 11:12:59'),
(187, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:13:25', '[ID Barang Pengiriman: 94][Jumlah: 5][Notes: -][ID Pengiriman: 29][ID Barang Penjualan: 3][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:13:25][Oleh: admin]', '', 2, '2021-06-07 11:13:25'),
(188, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:13:25', '[ID Barang Pengiriman: 95][Jumlah: 5][Notes: -][ID Pengiriman: 29][ID Barang Penjualan: 4][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:13:25][Oleh: admin]', '', 2, '2021-06-07 11:13:25'),
(189, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:13:42', '[ID Barang Pengiriman: 96][Jumlah: 0][Notes: 123][ID Pengiriman: 30][ID Barang Penjualan: 1][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:13:42][Oleh: admin]', '', 2, '2021-06-07 11:13:42'),
(190, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:13:42', '[ID Barang Pengiriman: 97][Jumlah: 123][Notes: 123][ID Pengiriman: 30][ID Barang Penjualan: 2][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:13:42][Oleh: admin]', '', 2, '2021-06-07 11:13:42'),
(191, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:13:52', '[ID Barang Pengiriman: 98][Jumlah: 0][Notes: 123][ID Pengiriman: 31][ID Barang Penjualan: 1][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:13:52][Oleh: admin]', '', 2, '2021-06-07 11:13:52'),
(192, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-07 11:13:52', '[ID Barang Pengiriman: 99][Jumlah: 123][Notes: 123][ID Pengiriman: 31][ID Barang Penjualan: 2][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-07 11:13:52][Oleh: admin]', '', 2, '2021-06-07 11:13:52'),
(193, 'Data Customer baru ditambahkan. Waktu penambahan: 21-06-09 01:15:28', '[ID Customer: 3738][Nama: ][NPWP: ][Foto NPWP: ][Kartu Nama: ][Badan Usaha: ][No Rek: ][Panggilan: ][Perusahaan: ][Email: ][Telepon: ][No HP: ][Alamat: ][ID Toko: ][Keterangan: ][Status: ][Waktu Ditambahkan: 21-06-09 01:15:28][Oleh: 2]', '', 2, '2021-06-09 01:15:28'),
(194, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-06-09 01:24:03', '[ID Barang Jenis: 22][Nama: CUSTOM][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:24:03][Oleh: puteri]', '', 2, '2021-06-09 01:24:03'),
(195, 'Penambahan data jenis barang', 'Jenis barang: CUSTOM', '-', 2, '2021-06-09 01:24:03'),
(196, 'Data Marketplace baru ditambahkan. Waktu penambahan: 21-06-09 01:25:53', '[ID Marketplace: 5][Nama: Lazada][Keterangan: Lazada][Status: AKTIF][Biaya: 20][Waktu Ditambahkan: 21-06-09 01:25:53][Foto NPWP: puteri]', '', 2, '2021-06-09 01:25:53'),
(197, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 36][Nama: 1ACAII TEA CO.][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:26:56][Oleh: puteri]', '', 2, '2021-06-09 01:26:56'),
(198, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 37][Nama: 1ACAII TEA CO.][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:26:58][Oleh: puteri]', '', 2, '2021-06-09 01:26:58'),
(199, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 38][Nama: 1ACAII TEA CO.][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:02][Oleh: puteri]', '', 2, '2021-06-09 01:27:02'),
(200, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 39][Nama: 1ACAII TEA CO.][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:05][Oleh: puteri]', '', 2, '2021-06-09 01:27:05'),
(201, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 40][Nama: 1ACAII TEA CO.][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:05][Oleh: puteri]', '', 2, '2021-06-09 01:27:05'),
(202, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 41][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:07][Oleh: puteri]', '', 2, '2021-06-09 01:27:07'),
(203, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 42][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:07][Oleh: puteri]', '', 2, '2021-06-09 01:27:07'),
(204, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 43][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:07][Oleh: puteri]', '', 2, '2021-06-09 01:27:07'),
(205, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 44][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:07][Oleh: puteri]', '', 2, '2021-06-09 01:27:07'),
(206, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 45][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:07][Oleh: puteri]', '', 2, '2021-06-09 01:27:07'),
(207, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 46][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:08][Oleh: puteri]', '', 2, '2021-06-09 01:27:08'),
(208, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 47][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:08][Oleh: puteri]', '', 2, '2021-06-09 01:27:08'),
(209, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 48][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:08][Oleh: puteri]', '', 2, '2021-06-09 01:27:08'),
(210, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 49][Nama: 1ACAII TEA CO.sdas][Status: AKTIF][Waktu Ditambahkan: 21-06-09 01:27:08][Oleh: puteri]', '', 2, '2021-06-09 01:27:08'),
(211, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-06-09 01:46:58', '[ID Barang Jenis: 5][Jumlah (real): ][Satuan (real): ][Jumlah: ][Satuan: ][Harga: ][Notes: ][Status: ][ID Penjualan: ][ID Barang: ][Waktu Ditambahkan: 21-06-09 01:46:58][Oleh: puteri]', '', 2, '2021-06-09 01:46:58'),
(212, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-06-09 01:47:03', '[ID Barang Jenis: 6][Jumlah (real): ][Satuan (real): ][Jumlah: ][Satuan: ][Harga: ][Notes: ][Status: ][ID Penjualan: ][ID Barang: ][Waktu Ditambahkan: 21-06-09 01:47:03][Oleh: puteri]', '', 2, '2021-06-09 01:47:03'),
(213, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-09 01:55:04', '[ID Barang Pengiriman: 100][Jumlah: 0][Notes: -][ID Pengiriman: 32][ID Barang Penjualan: 5][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-09 01:55:04][Oleh: puteri]', '', 2, '2021-06-09 01:55:04'),
(214, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-09 01:57:26', '[ID Barang Permintaan: 8][Jumlah: 15][Notes: sd][Deadline: 2021-06-09][Status: aktif][ID Barang: 1][ID Cabang: 1][Waktu Ditambahkan: 21-06-09 01:57:26][Oleh: puteri]', '', 2, '2021-06-09 01:57:26'),
(215, 'Data Admin Cabang baru ditambahkan. Waktu penambahan: 21-06-09 10:01:57', '[ID Admin Cabang: 9][ID Cabang: 1][ID User: 5][Status: AKTIF][Waktu Ditambahkan: 21-06-09 10:01:57][Oleh: admin]', '', 5, '2021-06-09 10:01:57'),
(216, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 50][Nama: asdf][Status: AKTIF][Waktu Ditambahkan: 21-06-09 10:04:13][Oleh: admin]', '', 5, '2021-06-09 10:04:13'),
(217, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 51][Nama: asdf][Status: AKTIF][Waktu Ditambahkan: 21-06-09 10:04:19][Oleh: admin]', '', 5, '2021-06-09 10:04:19'),
(218, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: ', '[ID Merek Barang: 52][Nama: test][Status: AKTIF][Waktu Ditambahkan: 21-06-09 09:06:12][Oleh: admin]', '', 5, '2021-06-09 09:06:12'),
(219, 'Data Barang Warehouse baru ditambahkan. Waktu penambahan: 21-06-09 09:18:42', '[ID Barang Warehouse: 10][Jumlah: 1000][Notes: -][Status: AKTIF][ID Barang: 8][ID Warehouse: 5][Waktu Ditambahkan: 21-06-09 09:18:42][Oleh: admin]', '', 5, '2021-06-09 09:18:42'),
(220, 'Data Barang Warehouse baru ditambahkan. Waktu penambahan: 21-06-09 09:33:18', '[ID Barang Warehouse: 11][Jumlah: 2000][Notes: -][Status: AKTIF][ID Barang: 9][ID Warehouse: 5][Waktu Ditambahkan: 21-06-09 09:33:18][Oleh: admin]', '', 5, '2021-06-09 09:33:18'),
(221, 'Data Admin Cabang baru ditambahkan. Waktu penambahan: 21-06-09 09:50:52', '[ID Admin Cabang: 10][ID Cabang: 3][ID User: 5][Status: AKTIF][Waktu Ditambahkan: 21-06-09 09:50:52][Oleh: admin]', '', 5, '2021-06-09 09:50:52'),
(222, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-09 10:12:00', '[ID Barang Permintaan: 9][Jumlah: 1000][Notes: -][Deadline: 2021-06-09][Status: aktif][ID Barang: 1][ID Cabang: 1][Waktu Ditambahkan: 21-06-09 10:12:00][Oleh: admin]', '', 5, '2021-06-09 10:12:00'),
(223, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-09 10:20:05', '[ID Barang Pembelian: 16][Jumlah: 1000][Satuan: Pcs][Harga: 5000][Notes: -][Status: AKTIF][ID Pembelian: 29][ID Barang: 1][Waktu Ditambahkan: 21-06-09 10:20:05][Oleh: admin]', '', 5, NULL),
(224, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-09 10:20:05', '[ID Barang Pembelian: 17][Jumlah: 2000][Satuan: Pcs][Harga: 15000][Notes: -][Status: AKTIF][ID Pembelian: 29][ID Barang: 20][Waktu Ditambahkan: 21-06-09 10:20:05][Oleh: admin]', '', 5, NULL),
(225, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-09 10:20:13', '[ID Barang Pembelian: 18][Jumlah: 1000][Satuan: Pcs][Harga: 5000][Notes: -][Status: AKTIF][ID Pembelian: 30][ID Barang: 1][Waktu Ditambahkan: 21-06-09 10:20:13][Oleh: admin]', '', 5, NULL),
(226, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-09 10:20:13', '[ID Barang Pembelian: 19][Jumlah: 2000][Satuan: Pcs][Harga: 15000][Notes: -][Status: AKTIF][ID Pembelian: 30][ID Barang: 20][Waktu Ditambahkan: 21-06-09 10:20:13][Oleh: admin]', '', 5, NULL),
(227, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-09 10:22:59', '[ID Barang Penerimaan: 55][Jumlah: 100][Notes: -][ID Penerimaan: 44][ID Pembelian: 16][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-09 10:22:59][Oleh: admin]', '', 5, '2021-06-09 10:22:59'),
(228, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-09 10:22:59', '[ID Barang Penerimaan: 56][Jumlah: 100][Notes: -][ID Penerimaan: 44][ID Pembelian: 17][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-09 10:22:59][Oleh: admin]', '', 5, '2021-06-09 10:22:59'),
(229, 'Data Barang Warehouse baru ditambahkan. Waktu penambahan: 21-06-09 10:32:43', '[ID Barang Warehouse: 12][Jumlah: 1000][Notes: -=][Status: AKTIF][ID Barang: 8][ID Warehouse: 11][Waktu Ditambahkan: 21-06-09 10:32:43][Oleh: admin]', '', 5, '2021-06-09 10:32:43'),
(230, 'Data Barang Warehouse baru ditambahkan. Waktu penambahan: 21-06-09 10:33:05', '[ID Barang Warehouse: 13][Jumlah: 2000][Notes: -][Status: AKTIF][ID Barang: 10][ID Warehouse: 11][Waktu Ditambahkan: 21-06-09 10:33:05][Oleh: admin]', '', 5, '2021-06-09 10:33:05'),
(231, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-10 01:54:25', '[ID Barang Penerimaan: 57][Jumlah: 1000][Notes: ][ID Penerimaan: 45][ID Pembelian: 16][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-10 01:54:25][Oleh: admin]', '', 5, '2021-06-10 01:54:25'),
(232, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-10 01:54:25', '[ID Barang Penerimaan: 58][Jumlah: 2000][Notes: ][ID Penerimaan: 45][ID Pembelian: 17][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-10 01:54:25][Oleh: admin]', '', 5, '2021-06-10 01:54:25'),
(233, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-10 08:22:14', '[ID Barang Pembelian: 20][Jumlah: 100][Satuan: wkwk][Harga: 2000][Notes: -][Status: AKTIF][ID Pembelian: 29][ID Barang: 22][Waktu Ditambahkan: 21-06-10 08:22:14][Oleh: admin]', '', 5, NULL),
(234, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-10 08:22:19', '[ID Barang Pembelian: 21][Jumlah: 100][Satuan: wkwk][Harga: 2000][Notes: -][Status: AKTIF][ID Pembelian: 29][ID Barang: 22][Waktu Ditambahkan: 21-06-10 08:22:19][Oleh: admin]', '', 5, NULL),
(235, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-10 08:24:20', '[ID Barang Pembelian: 22][Jumlah: 1][Satuan: jeen][Harga: 1000][Notes: -][Status: AKTIF][ID Pembelian: 29][ID Barang: 2][Waktu Ditambahkan: 21-06-10 08:24:20][Oleh: admin]', '', 5, NULL);
INSERT INTO `log_all` (`id_pk_log_all`, `log_all_msg`, `log_all_data_changes`, `log_all_it`, `log_all_user`, `log_all_tgl`) VALUES
(236, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-15 07:53:06', '[ID Penerimaan: 46][Tanggal Penerimaan: 2021-06-15][Penerimaan Status: AKTIF][Tipe Penerimaan: retur][ID Pembelian: ][ID Retur: 5][Tempat: CABANG][Waktu Ditambahkan: 21-06-15 07:53:06][Oleh: admin]', '', 5, '2021-06-15 07:53:06'),
(237, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-15 07:53:06', '[ID Barang Penerimaan: 59][Jumlah: 1][Notes: -][ID Penerimaan: 46][ID Pembelian: ][ID Retur: 9][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 07:53:06][Oleh: admin]', '', 5, '2021-06-15 07:53:06'),
(238, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-15 07:53:43', '[ID Penerimaan: 47][Tanggal Penerimaan: 2021-07-02][Penerimaan Status: AKTIF][Tipe Penerimaan: retur][ID Pembelian: ][ID Retur: 5][Tempat: CABANG][Waktu Ditambahkan: 21-06-15 07:53:43][Oleh: admin]', '', 5, '2021-06-15 07:53:43'),
(239, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-15 07:53:43', '[ID Barang Penerimaan: 60][Jumlah: 2][Notes: -][ID Penerimaan: 47][ID Pembelian: ][ID Retur: 9][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 07:53:43][Oleh: admin]', '', 5, '2021-06-15 07:53:43'),
(240, 'Data Retur baru ditambahkan. Waktu penambahan: 21-06-15 08:01:33', '[ID Retur: 6][ID Penjualan: 3][No Retur: PS-RETUR-2021-06-17-000002][Tanggal: 2021-06-17][Tipe Retur: BARANG][Status: menunggu konfirmasi][Oleh: admin][Waktu Ditambahkan: 21-06-15 08:01:33][Nomor Control: 2][Bulan Control: ][Tahun Control: ]', '', 5, '2021-06-15 08:01:33'),
(241, 'Data Retur Barang baru ditambahkan. Waktu penambahan: ', '[ID Retur Barang: 10][ID Retur: 6][ID Barang: 2][Jumalh Barang: 1][Satuan Barang: Pcs][Notes: -][Status: aktif][Waktu Ditambahkan: 21-06-15 08:01:33][Oleh: admin]', '', 5, NULL),
(242, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:01:33', '[ID Retur Kembali: 5][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 6][ID Barang: 22][Waktu Ditambahkan: 21-06-15 08:01:33][Oleh: admin]', '', 5, '2021-06-15 08:01:33'),
(243, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:01:33', '[ID Retur Kembali: 6][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 6][ID Barang: 2][Waktu Ditambahkan: 21-06-15 08:01:33][Oleh: admin]', '', 5, '2021-06-15 08:01:33'),
(244, 'Data Retur baru ditambahkan. Waktu penambahan: 21-06-15 08:01:40', '[ID Retur: 7][ID Penjualan: 3][No Retur: PS-RETUR-2021-06-17-000003][Tanggal: 2021-06-17][Tipe Retur: BARANG][Status: menunggu konfirmasi][Oleh: admin][Waktu Ditambahkan: 21-06-15 08:01:40][Nomor Control: 3][Bulan Control: ][Tahun Control: ]', '', 5, '2021-06-15 08:01:40'),
(245, 'Data Retur Barang baru ditambahkan. Waktu penambahan: ', '[ID Retur Barang: 11][ID Retur: 7][ID Barang: 2][Jumalh Barang: 1][Satuan Barang: Pcs][Notes: -][Status: aktif][Waktu Ditambahkan: 21-06-15 08:01:40][Oleh: admin]', '', 5, NULL),
(246, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:01:40', '[ID Retur Kembali: 7][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 7][ID Barang: 22][Waktu Ditambahkan: 21-06-15 08:01:40][Oleh: admin]', '', 5, '2021-06-15 08:01:40'),
(247, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:01:40', '[ID Retur Kembali: 8][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 7][ID Barang: 2][Waktu Ditambahkan: 21-06-15 08:01:40][Oleh: admin]', '', 5, '2021-06-15 08:01:40'),
(248, 'Data Retur baru ditambahkan. Waktu penambahan: 21-06-15 08:06:13', '[ID Retur: 8][ID Penjualan: 3][No Retur: PS-RETUR-2021-06-17-000004][Tanggal: 2021-06-17][Tipe Retur: BARANG][Status: menunggu konfirmasi][Oleh: admin][Waktu Ditambahkan: 21-06-15 08:06:13][Nomor Control: 4][Bulan Control: ][Tahun Control: ]', '', 5, '2021-06-15 08:06:13'),
(249, 'Data Retur Barang baru ditambahkan. Waktu penambahan: 21-06-15 08:06:13', '[ID Retur Barang: 12][ID Retur: 8][ID Barang: 2][Jumalh Barang: 1][Satuan Barang: Pcs][Notes: -][Status: aktif][Waktu Ditambahkan: 21-06-15 08:06:13][Oleh: admin]', '', 5, NULL),
(250, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:06:14', '[ID Retur Kembali: 9][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 8][ID Barang: 22][Waktu Ditambahkan: 21-06-15 08:06:14][Oleh: admin]', '', 5, '2021-06-15 08:06:14'),
(251, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:06:14', '[ID Retur Kembali: 10][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 8][ID Barang: 2][Waktu Ditambahkan: 21-06-15 08:06:14][Oleh: admin]', '', 5, '2021-06-15 08:06:14'),
(252, 'Data Retur baru ditambahkan. Waktu penambahan: 21-06-15 08:06:22', '[ID Retur: 9][ID Penjualan: 3][No Retur: PS-RETUR-2021-06-17-000005][Tanggal: 2021-06-17][Tipe Retur: BARANG][Status: menunggu konfirmasi][Oleh: admin][Waktu Ditambahkan: 21-06-15 08:06:22][Nomor Control: 5][Bulan Control: ][Tahun Control: ]', '', 5, '2021-06-15 08:06:22'),
(253, 'Data Retur Barang baru ditambahkan. Waktu penambahan: 21-06-15 08:06:22', '[ID Retur Barang: 13][ID Retur: 9][ID Barang: 2][Jumalh Barang: 1][Satuan Barang: Pcs][Notes: -][Status: aktif][Waktu Ditambahkan: 21-06-15 08:06:22][Oleh: admin]', '', 5, '2021-06-15 08:06:22'),
(254, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:06:22', '[ID Retur Kembali: 11][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 9][ID Barang: 22][Waktu Ditambahkan: 21-06-15 08:06:22][Oleh: admin]', '', 5, '2021-06-15 08:06:22'),
(255, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-15 08:06:22', '[ID Retur Kembali: 12][Jumlah: 10][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Retur: 9][ID Barang: 2][Waktu Ditambahkan: 21-06-15 08:06:22][Oleh: admin]', '', 5, '2021-06-15 08:06:22'),
(256, 'Data Pengiriman baru ditambahkan. Waktu penambahan: ', '[ID Pengiriman: 33][No: PS-PENGIRIMAN-2021-06-18-000002][Tanggal: 2021-06-18][Status: AKTIF][Tipe: retur][ID Penjualan: ][ID Retur: 6][Tempat: cabang][Waktu Ditambahkan: 21-06-15 08:07:45][Oleh: admin][Nomor Control: 2][Bulan Control: ]', '', 5, NULL),
(257, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:07:45', '[ID Barang Pengiriman: 101][Jumlah: 0][Notes: -][ID Pengiriman: 33][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 5][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:07:45][Oleh: admin]', '', 5, '2021-06-15 08:07:45'),
(258, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:07:45', '[ID Barang Pengiriman: 102][Jumlah: 0][Notes: -][ID Pengiriman: 33][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 6][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:07:45][Oleh: admin]', '', 5, '2021-06-15 08:07:45'),
(259, 'Data Pengiriman baru ditambahkan. Waktu penambahan: ', '[ID Pengiriman: 34][No: PS-PENGIRIMAN-2021-06-18-000003][Tanggal: 2021-06-18][Status: AKTIF][Tipe: retur][ID Penjualan: ][ID Retur: 6][Tempat: cabang][Waktu Ditambahkan: 21-06-15 08:07:48][Oleh: admin][Nomor Control: 3][Bulan Control: ]', '', 5, NULL),
(260, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:07:48', '[ID Barang Pengiriman: 103][Jumlah: 10][Notes: -][ID Pengiriman: 34][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 5][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:07:48][Oleh: admin]', '', 5, '2021-06-15 08:07:48'),
(261, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:07:48', '[ID Barang Pengiriman: 104][Jumlah: 0][Notes: -][ID Pengiriman: 34][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 6][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:07:48][Oleh: admin]', '', 5, '2021-06-15 08:07:48'),
(262, 'Data Pengiriman baru ditambahkan. Waktu penambahan: ', '[ID Pengiriman: 35][No: PS-PENGIRIMAN-2021-06-18-000004][Tanggal: 2021-06-18][Status: AKTIF][Tipe: retur][ID Penjualan: ][ID Retur: 6][Tempat: cabang][Waktu Ditambahkan: 21-06-15 08:07:53][Oleh: admin][Nomor Control: 4][Bulan Control: ]', '', 5, NULL),
(263, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:07:53', '[ID Barang Pengiriman: 105][Jumlah: 10][Notes: -][ID Pengiriman: 35][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 5][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:07:53][Oleh: admin]', '', 5, '2021-06-15 08:07:53'),
(264, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:07:53', '[ID Barang Pengiriman: 106][Jumlah: 0][Notes: -][ID Pengiriman: 35][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 6][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:07:53][Oleh: admin]', '', 5, '2021-06-15 08:07:53'),
(265, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:08:33', '[ID Pengiriman: 36][No: PS-PENGIRIMAN-2021-06-18-000005][Tanggal: 2021-06-18][Status: AKTIF][Tipe: retur][ID Penjualan: ][ID Retur: 6][Tempat: cabang][Waktu Ditambahkan: 21-06-15 08:08:33][Oleh: admin][Nomor Control: 5][Bulan Control: ]', '', 5, '2021-06-15 08:08:33'),
(266, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:08:33', '[ID Barang Pengiriman: 107][Jumlah: 10][Notes: -][ID Pengiriman: 36][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 5][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:08:33][Oleh: admin]', '', 5, '2021-06-15 08:08:33'),
(267, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-15 08:08:33', '[ID Barang Pengiriman: 108][Jumlah: 0][Notes: -][ID Pengiriman: 36][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 6][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-15 08:08:33][Oleh: admin]', '', 5, '2021-06-15 08:08:33'),
(268, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-15 08:12:27', '[ID Barang Permintaan: 10][Jumlah: 20000][Notes: -][Deadline: 2021-06-24][Status: aktif][ID Barang: 24][ID Cabang: 1][Waktu Ditambahkan: 21-06-15 08:12:27][Oleh: admin]', '', 5, '2021-06-15 08:12:27'),
(269, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:05:25', '[ID Barang Permintaan: 11][Jumlah: 1000][Notes: -][Deadline: 2021-06-25][Status: aktif][ID Barang: 23][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:05:25][Oleh: admin]', '', 5, '2021-06-16 09:05:25'),
(270, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:09:15', '[ID Barang Permintaan: 12][Jumlah: 1000][Notes: -][Deadline: 2021-06-24][Status: aktif][ID Barang: 24][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:09:15][Oleh: admin]', '', 5, '2021-06-16 09:09:15'),
(271, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:10:49', '[ID Barang Permintaan: 13][Jumlah: 1000][Notes: -][Deadline: 2021-06-25][Status: aktif][ID Barang: 13][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:10:49][Oleh: admin]', '', 5, '2021-06-16 09:10:49'),
(272, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:32:18', '[ID Barang Permintaan: 14][Jumlah: 1000][Notes: -][Deadline: 2021-06-25][Status: aktif][ID Barang: 23][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:32:18][Oleh: admin]', '', 5, '2021-06-16 09:32:18'),
(273, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:34:08', '[ID Barang Permintaan: 15][Jumlah: 1000][Notes: -][Deadline: 2021-06-17][Status: aktif][ID Barang: 23][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:34:08][Oleh: admin]', '', 5, '2021-06-16 09:34:08'),
(274, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:34:32', '[ID Barang Permintaan: 16][Jumlah: 1000][Notes: -][Deadline: 2021-06-25][Status: aktif][ID Barang: 23][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:34:32][Oleh: admin]', '', 5, '2021-06-16 09:34:32'),
(275, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:35:56', '[ID Barang Permintaan: 17][Jumlah: 1000][Notes: -][Deadline: 2021-06-24][Status: aktif][ID Barang: 23][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:35:56][Oleh: admin]', '', 5, '2021-06-16 09:35:56'),
(276, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:36:06', '[ID Barang Permintaan: 18][Jumlah: 1000][Notes: -][Deadline: 2021-06-24][Status: aktif][ID Barang: 23][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:36:06][Oleh: admin]', '', 5, '2021-06-16 09:36:06'),
(277, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:36:16', '[ID Barang Permintaan: 19][Jumlah: 2000][Notes: -][Deadline: 2021-06-24][Status: aktif][ID Barang: 13][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:36:16][Oleh: admin]', '', 5, '2021-06-16 09:36:16'),
(278, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-16 09:36:25', '[ID Barang Permintaan: 20][Jumlah: 3000][Notes: -=][Deadline: 2021-06-25][Status: aktif][ID Barang: 24][ID Cabang: 1][Waktu Ditambahkan: 21-06-16 09:36:25][Oleh: admin]', '', 5, '2021-06-16 09:36:25'),
(279, 'Data Barang Cabang baru ditambahkan. Waktu penambahan: 21-06-16 09:41:00', '[ID Barang Cabang: 26][Jumlah: 3000][Notes: -][Status: AKTIF][ID Barang: 23][ID Cabang: 3][Waktu Ditambahkan: 21-06-16 09:41:00][Oleh: admin]', '', 5, NULL),
(280, 'Penambahan data barang MM Safety cabang Cabang 1', 'Nama barang: 4007 H Jumlah barang: 3000, Catatan :-', '-', 5, '2021-06-16 21:41:00'),
(281, 'Penambahan data barang MM Safety cabang Cabang 1', 'Nama barang: 4007 H Jumlah barang: 3000, Catatan :-', '-', 5, '2021-06-16 21:41:19'),
(282, 'Data Barang Cabang baru ditambahkan. Waktu penambahan: 21-06-16 09:41:19', '[ID Barang Cabang: 27][Jumlah: 3000][Notes: -][Status: AKTIF][ID Barang: 24][ID Cabang: 3][Waktu Ditambahkan: 21-06-16 09:41:19][Oleh: admin]', '', 5, NULL),
(283, 'Penambahan data barang MM Safety cabang Cabang 1', 'Nama barang: 4008 H Jumlah barang: 3000, Catatan :-', '-', 5, '2021-06-16 21:41:20'),
(284, 'Data Cabang baru ditambahkan. Waktu penambahan: 21-06-16 09:48:34', '[ID Cabang: 5][Nama: Puri Indah][Kode: PRINDAH][Daerah: Jakarta Barat][Kop Surat: noimage.jpg][Nonpkp: noimage.jpg][Pernyataan Rek.: noimage.jpg][No Telp: -][Alamat: -][Status: AKTIF][Waktu Ditambahkan: 21-06-16 09:48:34][Oleh: admin]', '', 5, '2021-06-16 09:48:34'),
(285, 'Data Admin Cabang baru ditambahkan. Waktu penambahan: 21-06-16 09:48:41', '[ID Admin Cabang: 11][ID Cabang: 5][ID User: 5][Status: AKTIF][Waktu Ditambahkan: 21-06-16 09:48:41][Oleh: admin]', '', 5, '2021-06-16 09:48:41'),
(286, 'Data Barang Cabang baru ditambahkan. Waktu penambahan: 21-06-16 09:49:17', '[ID Barang Cabang: 28][Jumlah: 2000][Notes: -][Status: AKTIF][ID Barang: 23][ID Cabang: 5][Waktu Ditambahkan: 21-06-16 09:49:17][Oleh: admin]', '', 5, NULL),
(287, 'Penambahan data barang Indotama Maju Mandiri cabang Jakarta Barat', 'Nama barang: 4007 H Jumlah barang: 2000, Catatan :-', '-', 5, '2021-06-16 21:49:17'),
(288, 'Data Barang Cabang baru ditambahkan. Waktu penambahan: 21-06-16 09:49:17', '[ID Barang Cabang: 29][Jumlah: 3000][Notes: -][Status: AKTIF][ID Barang: 24][ID Cabang: 5][Waktu Ditambahkan: 21-06-16 09:49:17][Oleh: admin]', '', 5, NULL),
(289, 'Penambahan data barang Indotama Maju Mandiri cabang Jakarta Barat', 'Nama barang: 4008 H Jumlah barang: 3000, Catatan :-', '-', 5, '2021-06-16 21:49:17'),
(290, 'Data Cabang dengan ID: 3 diubah. Waktu diubah: 21-06-16 10:29:38 . Data berubah menjadi: ', '[ID Cabang: 3][Nama: CBG1][Kode: CBG1][Daerah: Cabang 1][Kop Surat: noimage.jpg][Nonpkp: noimage.jpg][Pernyataan Rek.: noimage.jpg][No Telp: 123456][Alamat: Alamat ][Waktu Diedit: 21-06-16 10:29:38][Oleh: admin]', '', 5, '2021-06-16 10:29:38'),
(291, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:30:11', '[ID Pengiriman: 37][No: CBG1-PENGIRIMAN-2021-06-16-000001][Tanggal: 2021-06-16 22:30:11][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-16 10:30:11][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-16 10:30:11'),
(292, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:30:12', '[ID Barang Pengiriman: 109][Jumlah: 60000][Notes: -][ID Pengiriman: 37][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 22][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:30:12][Oleh: admin]', '', 5, '2021-06-16 10:30:12'),
(293, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:31:55', '[ID Pengiriman: 38][No: CBG1-PENGIRIMAN-2021-06-16-000002][Tanggal: 2021-06-16 22:31:55][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-16 10:31:55][Oleh: admin][Nomor Control: 2][Bulan Control: ]', '', 5, '2021-06-16 10:31:55'),
(294, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:31:55', '[ID Barang Pengiriman: 110][Jumlah: 500.00][Notes: -][ID Pengiriman: 38][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 21][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:31:55][Oleh: admin]', '', 5, '2021-06-16 10:31:55'),
(295, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-16 10:32:41', '[ID Penerimaan: 48][Tanggal Penerimaan: 2021-06-16 22:32:41][Penerimaan Status: aktif][Tipe Penerimaan: permintaan][ID Pembelian: ][ID Retur: ][Tempat: CABANG][Waktu Ditambahkan: 21-06-16 10:32:41][Oleh: admin]', '', 5, '2021-06-16 10:32:41'),
(296, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-16 10:32:42', '[ID Barang Penerimaan: 61][Jumlah: 500.00][Notes: -][ID Penerimaan: 48][ID Pembelian: ][ID Retur: ][ID Pengiriman: 110][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:32:42][Oleh: admin]', '', 5, '2021-06-16 10:32:42'),
(297, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:43:20', '[ID Pengiriman: 39][No: PRINDAH-PENGIRIMAN-2021-06-16-000001][Tanggal: 2021-06-16 22:43:20][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-16 10:43:20][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-16 10:43:20'),
(298, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:43:20', '[ID Barang Pengiriman: 111][Jumlah: 600.00][Notes: -][ID Pengiriman: 39][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 23][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:43:20][Oleh: admin]', '', 5, '2021-06-16 10:43:20'),
(299, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:43:44', '[ID Pengiriman: 40][No: PRINDAH-PENGIRIMAN-2021-06-16-000002][Tanggal: 2021-06-16 22:43:44][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-16 10:43:44][Oleh: admin][Nomor Control: 2][Bulan Control: ]', '', 5, '2021-06-16 10:43:44'),
(300, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:43:44', '[ID Barang Pengiriman: 112][Jumlah: 700.00][Notes: -][ID Pengiriman: 40][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 24][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:43:44][Oleh: admin]', '', 5, '2021-06-16 10:43:44'),
(301, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:46:02', '[ID Pengiriman: 41][No: CBG1-PENGIRIMAN-2021-06-16-000003][Tanggal: 2021-06-16 22:46:02][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-16 10:46:02][Oleh: admin][Nomor Control: 3][Bulan Control: ]', '', 5, '2021-06-16 10:46:02'),
(302, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-16 10:46:02', '[ID Barang Pengiriman: 113][Jumlah: 600.00][Notes: -][ID Pengiriman: 41][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 22][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:46:02][Oleh: admin]', '', 5, '2021-06-16 10:46:02'),
(303, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-16 10:49:59', '[ID Penerimaan: 49][Tanggal Penerimaan: 2021-06-16 22:49:59][Penerimaan Status: aktif][Tipe Penerimaan: permintaan][ID Pembelian: ][ID Retur: ][Tempat: Cabang][Waktu Ditambahkan: 21-06-16 10:49:59][Oleh: admin]', '', 5, '2021-06-16 10:49:59'),
(304, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-16 10:49:59', '[ID Barang Penerimaan: 62][Jumlah: 600][Notes: -][ID Penerimaan: 49][ID Pembelian: ][ID Retur: ][ID Pengiriman: 113][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:49:59][Oleh: admin]', '', 5, '2021-06-16 10:49:59'),
(305, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-16 10:50:02', '[ID Penerimaan: 50][Tanggal Penerimaan: 2021-06-16 22:50:02][Penerimaan Status: aktif][Tipe Penerimaan: permintaan][ID Pembelian: ][ID Retur: ][Tempat: Cabang][Waktu Ditambahkan: 21-06-16 10:50:02][Oleh: admin]', '', 5, '2021-06-16 10:50:02'),
(306, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-16 10:50:02', '[ID Barang Penerimaan: 63][Jumlah: 700][Notes: -][ID Penerimaan: 50][ID Pembelian: ][ID Retur: ][ID Pengiriman: 112][ID Satuan: 1][Waktu Ditambahkan: 21-06-16 10:50:02][Oleh: admin]', '', 5, '2021-06-16 10:50:02'),
(307, 'Data Warehouse dengan ID: 14 diubah. Waktu diubah: 21-06-16 11:58:58 . Data berubah menjadi: ', '[ID Warehouse: 14][Nama: AAAA][Alamat: aa][Telepon: w][Deskripsi: s][ID Cabang: 3][Waktu Diedit: 21-06-16 11:58:58][Oleh: admin]', '', 5, '2021-06-16 11:58:58'),
(308, 'Data Warehouse dengan ID: 11 diubah. Waktu diubah: 21-06-16 11:59:31 . Data berubah menjadi: ', '[ID Warehouse: 11][Nama: asdzxcaa][Alamat: qweasdaa][Telepon: 123450987654aa][Deskripsi: asdasdasdasaa][ID Cabang: 1][Waktu Diedit: 21-06-16 11:59:31][Oleh: admin]', '', 5, '2021-06-16 11:59:31'),
(309, 'Data Warehouse dengan ID: 5 diubah. Waktu diubah: 21-06-16 11:59:34 . Data berubah menjadi: ', '[ID Warehouse: 5][Nama: erwer][Alamat: 34234][Telepon: 234234][Deskripsi: 24342][ID Cabang: 3][Waktu Diedit: 21-06-16 11:59:34][Oleh: admin]', '', 5, '2021-06-16 11:59:34'),
(310, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:01:48', '[ID Penerimaan: 51][Tanggal Penerimaan: 2021-06-26][Penerimaan Status: AKTIF][Tipe Penerimaan: pembelian][ID Pembelian: 29][ID Retur: ][Tempat: WAREHOUSE][Waktu Ditambahkan: 21-06-17 12:01:48][Oleh: admin]', '', 5, '2021-06-17 12:01:48'),
(311, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:01:48', '[ID Barang Penerimaan: 64][Jumlah: 10][Notes: -][ID Penerimaan: 51][ID Pembelian: 16][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:01:48][Oleh: admin]', '', 5, '2021-06-17 12:01:48'),
(312, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:01:48', '[ID Barang Penerimaan: 65][Jumlah: 10][Notes: -][ID Penerimaan: 51][ID Pembelian: 17][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:01:48][Oleh: admin]', '', 5, '2021-06-17 12:01:48'),
(313, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:01:48', '[ID Barang Penerimaan: 66][Jumlah: 10][Notes: -][ID Penerimaan: 51][ID Pembelian: 20][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:01:48][Oleh: admin]', '', 5, '2021-06-17 12:01:48'),
(314, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:01:48', '[ID Barang Penerimaan: 67][Jumlah: 10][Notes: -][ID Penerimaan: 51][ID Pembelian: 22][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:01:48][Oleh: admin]', '', 5, '2021-06-17 12:01:48'),
(315, 'Data Pembelian baru ditambahkan. Waktu penambahan: 21-06-17 12:02:52', '[ID Pembelian: 32][Nomor Pembelian: CBG1-PEMBELIAN-2021-06-24-000001][Tanggal: 2021-06-24][Status: AKTIF][ID Supplier: 2][ID Cabang: 3][Waktu Ditambahkan: 21-06-17 12:02:52][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-17 12:02:52'),
(316, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-17 12:02:52', '[ID Barang Pembelian: 23][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Status: AKTIF][ID Pembelian: 32][ID Barang: 24][Waktu Ditambahkan: 21-06-17 12:02:52][Oleh: admin]', '', 5, '2021-06-17 12:02:52'),
(317, 'Data Barang Pembelian baru ditambahkan. Waktu penambahan: 21-06-17 12:02:52', '[ID Barang Pembelian: 24][Jumlah: 2000][Satuan: Pcs][Harga: 2000][Notes: -][Status: AKTIF][ID Pembelian: 32][ID Barang: 23][Waktu Ditambahkan: 21-06-17 12:02:52][Oleh: admin]', '', 5, '2021-06-17 12:02:52'),
(318, 'Data Tambahan Pembelian baru ditambahkan. Waktu penambahan: 21-06-17 12:02:52', '[ID Tambahan Pembelian: 13][Tambahan: tambahan11][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Status: AKTIF][ID Pembelian 32][Waktu Ditambahkan: 21-06-17 12:02:52][Oleh: ]', '', 21, '2021-06-17 12:02:52'),
(319, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:03:34', '[ID Penerimaan: 52][Tanggal Penerimaan: 2021-06-18][Penerimaan Status: AKTIF][Tipe Penerimaan: pembelian][ID Pembelian: 32][ID Retur: ][Tempat: WAREHOUSE][Waktu Ditambahkan: 21-06-17 12:03:34][Oleh: admin]', '', 5, '2021-06-17 12:03:34'),
(320, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:03:34', '[ID Barang Penerimaan: 68][Jumlah: 10][Notes: -][ID Penerimaan: 52][ID Pembelian: 23][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:03:34][Oleh: admin]', '', 5, '2021-06-17 12:03:34'),
(321, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:03:34', '[ID Barang Penerimaan: 69][Jumlah: 10][Notes: -1][ID Penerimaan: 52][ID Pembelian: 24][ID Retur: ][ID Pengiriman: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:03:34][Oleh: admin]', '', 5, '2021-06-17 12:03:34'),
(322, 'Data Barang Warehouse baru ditambahkan. Waktu penambahan: 21-06-17 12:07:29', '[ID Barang Warehouse: 14][Jumlah: 1000][Notes: -][Status: AKTIF][ID Barang: 23][ID Warehouse: 5][Waktu Ditambahkan: 21-06-17 12:07:29][Oleh: admin]', '', 5, '2021-06-17 12:07:29'),
(323, 'Data Barang Warehouse baru ditambahkan. Waktu penambahan: 21-06-17 12:07:29', '[ID Barang Warehouse: 15][Jumlah: 1000][Notes: -][Status: AKTIF][ID Barang: 24][ID Warehouse: 5][Waktu Ditambahkan: 21-06-17 12:07:29][Oleh: admin]', '', 5, '2021-06-17 12:07:29'),
(324, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-17 12:08:02', '[ID Pengiriman: 42][No: CBG1-PENGIRIMAN-2021-06-17-000004][Tanggal: 2021-06-17 00:08:02][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: warehouse][Waktu Ditambahkan: 21-06-17 12:08:02][Oleh: admin][Nomor Control: 4][Bulan Control: ]', '', 5, '2021-06-17 12:08:02'),
(325, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-17 12:08:02', '[ID Barang Pengiriman: 114][Jumlah: 200.00][Notes: -][ID Pengiriman: 42][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 25][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:08:02][Oleh: admin]', '', 5, '2021-06-17 12:08:02'),
(326, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-17 12:08:05', '[ID Pengiriman: 43][No: CBG1-PENGIRIMAN-2021-06-17-000004][Tanggal: 2021-06-17 00:08:05][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: warehouse][Waktu Ditambahkan: 21-06-17 12:08:05][Oleh: admin][Nomor Control: 4][Bulan Control: ]', '', 5, '2021-06-17 12:08:05'),
(327, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-17 12:08:05', '[ID Barang Pengiriman: 115][Jumlah: 300.00][Notes: -][ID Pengiriman: 43][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 26][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:08:05][Oleh: admin]', '', 5, '2021-06-17 12:08:05'),
(328, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:18:27', '[ID Penerimaan: 53][Tanggal Penerimaan: 2021-06-17 00:18:27][Penerimaan Status: aktif][Tipe Penerimaan: permintaan][ID Pembelian: ][ID Retur: ][Tempat: Cabang][Waktu Ditambahkan: 21-06-17 12:18:27][Oleh: admin]', '', 5, '2021-06-17 12:18:27'),
(329, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:18:27', '[ID Barang Penerimaan: 70][Jumlah: 200][Notes: -][ID Penerimaan: 53][ID Pembelian: ][ID Retur: ][ID Pengiriman: 114][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:18:27][Oleh: admin]', '', 5, '2021-06-17 12:18:27'),
(330, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:18:31', '[ID Penerimaan: 54][Tanggal Penerimaan: 2021-06-17 00:18:31][Penerimaan Status: aktif][Tipe Penerimaan: permintaan][ID Pembelian: ][ID Retur: ][Tempat: Cabang][Waktu Ditambahkan: 21-06-17 12:18:31][Oleh: admin]', '', 5, '2021-06-17 12:18:31'),
(331, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:18:31', '[ID Barang Penerimaan: 71][Jumlah: 300][Notes: -][ID Penerimaan: 54][ID Pembelian: ][ID Retur: ][ID Pengiriman: 115][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:18:31][Oleh: admin]', '', 5, '2021-06-17 12:18:31'),
(332, 'Data Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:18:33', '[ID Penerimaan: 55][Tanggal Penerimaan: 2021-06-17 00:18:33][Penerimaan Status: aktif][Tipe Penerimaan: permintaan][ID Pembelian: ][ID Retur: ][Tempat: Cabang][Waktu Ditambahkan: 21-06-17 12:18:33][Oleh: admin]', '', 5, '2021-06-17 12:18:33'),
(333, 'Data Barang Penerimaan baru ditambahkan. Waktu penambahan: 21-06-17 12:18:33', '[ID Barang Penerimaan: 72][Jumlah: 600][Notes: -][ID Penerimaan: 55][ID Pembelian: ][ID Retur: ][ID Pengiriman: 111][ID Satuan: 1][Waktu Ditambahkan: 21-06-17 12:18:33][Oleh: admin]', '', 5, '2021-06-17 12:18:33'),
(334, 'Data Barang baru ditambahkan. Waktu penambahan: 21-06-20 04:34:10', '[ID Barang: 181][Kode: testbk1][Nama: bk][Keterangan: -][Minimal: 100][Status: AKTIF][Satuan: PCS][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: ][ID Jenis Barang: 0][ID Merek Barang: 3][Waktu Ditambahkan: 21-06-20 04:34:10][Oleh: admin]', '', 5, '2021-06-20 04:34:10'),
(335, 'Data Barang baru ditambahkan. Waktu penambahan: 21-06-20 04:34:39', '[ID Barang: 182][Kode: testbk2][Nama: bk2][Keterangan: -][Minimal: 230][Status: AKTIF][Satuan: PCS][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: ][ID Jenis Barang: 0][ID Merek Barang: 5][Waktu Ditambahkan: 21-06-20 04:34:39][Oleh: admin]', '', 5, '2021-06-20 04:34:39'),
(336, 'Data Merk Barang dengan ID:  diubah. Waktu diubah:  . Data berubah menjadi: ', '[ID Barang: ][Kode: testbk23][Nama: bk23][Keterangan: 123123][Minimal: 123123][Status: ][Satuan: LUSIN][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: ][ID Jenis Barang: 0][ID Merek Barang: 8][Waktu Diubah: 21-06-20 04:37:24][Oleh: admin]', '', 5, NULL),
(337, 'Data Merk Barang dengan ID:  diubah. Waktu diubah:  . Data berubah menjadi: ', '[ID Barang: ][Kode: testbk23][Nama: bk23][Keterangan: 123123][Minimal: 123123][Status: ][Satuan: LUSIN][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: ][ID Jenis Barang: 0][ID Merek Barang: 8][Waktu Diubah: 21-06-20 04:37:31][Oleh: admin]', '', 5, NULL),
(338, 'Data Barang dengan ID: 182 diubah. Waktu diubah: 21-06-20 04:39:39 . Data berubah menjadi: ', '[ID Barang: 182][Kode: testbk23][Nama: bk23][Keterangan: 123123][Minimal: 123123][Status: ][Satuan: LUSIN][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: ][ID Jenis Barang: 0][ID Merek Barang: 8][Waktu Diubah: 21-06-20 04:39:39][Oleh: admin]', '', 5, NULL),
(339, 'Data Barang dengan ID: 182 diubah. Waktu diubah: 21-06-20 04:39:56 . Data berubah menjadi: ', '[ID Barang: 182][Kode: testbk23][Nama: bk23][Keterangan: 123123][Minimal: 123123][Status: ][Satuan: LUSIN][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: ][ID Jenis Barang: 0][ID Merek Barang: 8][Waktu Diubah: 21-06-20 04:39:56][Oleh: admin]', '', 5, '2021-06-20 04:39:56'),
(340, 'Data Barang Cabang baru ditambahkan. Waktu penambahan: 21-06-20 05:18:08', '[ID Barang Cabang: 30][Jumlah: 10000][Notes: -][Status: AKTIF][ID Barang: 12][ID Cabang: 5][Waktu Ditambahkan: 21-06-20 05:18:08][Oleh: admin]', '', 5, NULL),
(341, 'Penambahan data barang Indotama Maju Mandiri cabang Jakarta Barat', 'Nama barang: 2111 H Jumlah barang: 10000, Catatan :-', '-', 5, '2021-06-20 17:18:09'),
(342, 'Data Barang dengan ID: 20 diubah. Waktu diubah: 21-06-25 09:15:11 . Data berubah menjadi: ', '[ID Barang: 20][Kode: -][Nama: 3111 H][Keterangan: test][Minimal: 0][Satuan: LUSIN][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: kombinasi][ID Jenis Barang: 14][ID Merek Barang: 5][Waktu Diubah: 21-06-25 09:15:11][Oleh: admin]', '', 5, '2021-06-25 09:15:11'),
(343, 'Data Jenis Barang Kombinasi baru ditambahkan. Waktu penambahan: 2021-06-25 21:15:12', '[ID Barang Kombinasi: 52][ID Barang Utama: 20][ID Barang Kombinasi: ][Jumlah: ][Status: aktif][Waktu Ditambahkan: 2021-06-25 21:15:12][Oleh: ->52]', '', 5, '2021-06-25 21:15:12'),
(344, 'Data Barang dengan ID: 20 diubah. Waktu diubah: 21-06-25 09:16:57 . Data berubah menjadi: ', '[ID Barang: 20][Kode: -][Nama: 3111 H][Keterangan: test][Minimal: 0][Satuan: LUSIN][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: kombinasi][ID Jenis Barang: 14][ID Merek Barang: 5][Waktu Diubah: 21-06-25 09:16:57][Oleh: admin]', '', 5, '2021-06-25 09:16:57'),
(345, 'Data Barang dengan ID: 20 diubah. Waktu diubah: 21-06-25 09:17:24 . Data berubah menjadi: ', '[ID Barang: 20][Kode: -][Nama: 3111 H][Keterangan: test][Minimal: 0][Satuan: LUSIN][File Gambar: noimage.jpg][Harga Satuan: 0][Harga Toko: 0][Harga Grosir: 0][Tipe: kombinasi][ID Jenis Barang: 14][ID Merek Barang: 5][Waktu Diubah: 21-06-25 09:17:24][Oleh: admin]', '', 5, '2021-06-25 09:17:24'),
(346, 'Data Customer baru ditambahkan. Waktu penambahan: 21-06-26 12:24:28', '[ID Customer: 3739][Nama: ][NPWP: ][Foto NPWP: ][Kartu Nama: ][Badan Usaha: ][No Rek: ][Panggilan: ][Perusahaan: ][Email: ][Telepon: ][No HP: ][Alamat: ][ID Toko: ][Keterangan: ][Status: ][Waktu Ditambahkan: 21-06-26 12:24:28][Oleh: admin]', '', 5, '2021-06-26 12:24:28'),
(347, 'Data Penjualan baru ditambahkan. Waktu penambahan: 21-06-26 12:25:45', '[ID Penjualan: 5][Nomor Penjualan: ][Tanggal: ][Dateline: ][Jenis Penjualan: ][Tipe Pembayaran: ][ID Customer: ][ID Cabang: ][Waktu Ditambahkan: 21-06-26 12:25:45][Oleh: admin]', '', 5, '2021-06-26 12:25:45'),
(348, 'Data Penjualan Online baru ditambahkan. Waktu penambahan: 21-06-26 12:25:45', '[ID Penjualan Online: 13][Marketplace: ][Resi: ][Kurir: ][Status: ][ID Penjualan: ][Tanggal Penjualan: 21-06-26 12:25:45]][Oleh: admin]', '', 5, '2021-06-26 12:25:45'),
(349, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-06-26 12:25:45', '[ID Barang Jenis: 7][Jumlah (real): 0][Satuan (real): Pcs][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Status: AKTIF][ID Penjualan: 5][ID Barang: 23][Waktu Ditambahkan: 21-06-26 12:25:45][Oleh: admin]', '', 5, '2021-06-26 12:25:45'),
(350, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-06-26 12:25:45', '[ID Barang Jenis: 8][Jumlah (real): 0][Satuan (real): Pcs][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Status: AKTIF][ID Penjualan: 5][ID Barang: 24][Waktu Ditambahkan: 21-06-26 12:25:45][Oleh: admin]', '', 5, '2021-06-26 12:25:45'),
(351, 'Data Pembayaran Penjualan baru ditambahkan. Waktu penambahan: 21-06-26 12:25:45', '[ID Pembayaran Penjualan: 8][ID Penjualan: 5][Nama: Down Payment 1][Persen Pembayaran: Cash][Nominal Pembayaran: 1200000][Notes: -][Dateline: 2021-06-26][Status: aktif][Waktu Ditambahkan: 21-06-26 12:25:45][Oleh: admin]', '', 5, '2021-06-26 12:25:45'),
(352, 'Data Pembayaran Penjualan baru ditambahkan. Waktu penambahan: 21-06-26 12:25:45', '[ID Pembayaran Penjualan: 9][ID Penjualan: 5][Nama: Down Payment 1][Persen Pembayaran: Debit][Nominal Pembayaran: 1000000][Notes: -][Dateline: 2021-07-10][Status: aktif][Waktu Ditambahkan: 21-06-26 12:25:45][Oleh: admin]', '', 5, '2021-06-26 12:25:45'),
(353, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:30:39', '[ID Pengiriman: 44][No: PS-PENGIRIMAN-2021-07-03-000001][Tanggal: 2021-07-03][Status: AKTIF][Tipe: ][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 12:30:39][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-26 12:30:39'),
(354, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:30:40', '[ID Barang Pengiriman: 116][Jumlah: 100][Notes: -][ID Pengiriman: 44][ID Barang Penjualan: 7][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:30:40][Oleh: admin]', '', 5, '2021-06-26 12:30:40'),
(355, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:30:40', '[ID Barang Pengiriman: 117][Jumlah: 100][Notes: -][ID Pengiriman: 44][ID Barang Penjualan: 8][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:30:40][Oleh: admin]', '', 5, '2021-06-26 12:30:40'),
(356, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:36:26', '[ID Pengiriman: 45][No: PS-PENGIRIMAN-2021-07-03-000001][Tanggal: 2021-07-03][Status: AKTIF][Tipe: ][ID Penjualan: 5][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 12:36:26][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-26 12:36:26'),
(357, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:36:27', '[ID Barang Pengiriman: 118][Jumlah: 100][Notes: -][ID Pengiriman: 45][ID Barang Penjualan: 7][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:36:27][Oleh: admin]', '', 5, '2021-06-26 12:36:27'),
(358, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:36:27', '[ID Barang Pengiriman: 119][Jumlah: 100][Notes: -][ID Pengiriman: 45][ID Barang Penjualan: 8][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:36:27][Oleh: admin]', '', 5, '2021-06-26 12:36:27'),
(359, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:43:19', '[ID Pengiriman: 46][No: PS-PENGIRIMAN-2021-07-03-000001][Tanggal: 2021-07-03][Status: AKTIF][Tipe: penjualan][ID Penjualan: 5][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 12:43:19][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-26 12:43:19'),
(360, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:43:19', '[ID Barang Pengiriman: 120][Jumlah: 10][Notes: -][ID Pengiriman: 46][ID Barang Penjualan: 7][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:43:19][Oleh: admin]', '', 5, '2021-06-26 12:43:19'),
(361, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:43:19', '[ID Barang Pengiriman: 121][Jumlah: 10][Notes: -][ID Pengiriman: 46][ID Barang Penjualan: 8][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:43:19][Oleh: admin]', '', 5, '2021-06-26 12:43:19'),
(362, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:43:38', '[ID Pengiriman: 47][No: PS-PENGIRIMAN-2021-06-26-000006][Tanggal: 2021-06-26][Status: AKTIF][Tipe: penjualan][ID Penjualan: 5][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 12:43:38][Oleh: admin][Nomor Control: 6][Bulan Control: ]', '', 5, '2021-06-26 12:43:38'),
(363, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:43:38', '[ID Barang Pengiriman: 122][Jumlah: 10][Notes: -][ID Pengiriman: 47][ID Barang Penjualan: 7][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:43:38][Oleh: admin]', '', 5, '2021-06-26 12:43:38'),
(364, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:43:38', '[ID Barang Pengiriman: 123][Jumlah: 10][Notes: -][ID Pengiriman: 47][ID Barang Penjualan: 8][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:43:38][Oleh: admin]', '', 5, '2021-06-26 12:43:38'),
(365, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:48:20', '[ID Pengiriman: 48][No: PS-PENGIRIMAN-2021-07-03-000001][Tanggal: 2021-07-03][Status: AKTIF][Tipe: penjualan][ID Penjualan: 5][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 12:48:20][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-26 12:48:20'),
(366, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:48:20', '[ID Barang Pengiriman: 124][Jumlah: 10][Notes: -][ID Pengiriman: 48][ID Barang Penjualan: 7][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:48:20][Oleh: admin]', '', 5, '2021-06-26 12:48:20'),
(367, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:48:20', '[ID Barang Pengiriman: 125][Jumlah: 10][Notes: -][ID Pengiriman: 48][ID Barang Penjualan: 8][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:48:20][Oleh: admin]', '', 5, '2021-06-26 12:48:20'),
(368, 'Data Pengiriman dengan ID: 48 diubah. Waktu diubah: 21-06-26 12:49:18 . Data berubah menjadi: ', '[ID Pengiriman: 48][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 12:49:18][Oleh: admin]', '', 5, '2021-06-26 12:49:18'),
(369, 'Data Barang Pengiriman dengan ID: 124 diubah. Waktu diubah: 21-06-26 12:49:18 . Data berubah menjadi: ', '[ID Barang Pengiriman: 124][Jumlah: 20][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Diubah: 21-06-26 12:49:18][Oleh: admin]', '', 5, '2021-06-26 12:49:18'),
(370, 'Data Barang Pengiriman dengan ID: 125 diubah. Waktu diubah: 21-06-26 12:49:18 . Data berubah menjadi: ', '[ID Barang Pengiriman: 125][Jumlah: 20][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Diubah: 21-06-26 12:49:18][Oleh: admin]', '', 5, '2021-06-26 12:49:18'),
(371, 'Data Retur baru ditambahkan. Waktu penambahan: 21-06-26 01:01:03', '[ID Retur: 10][ID Penjualan: 5][No Retur: PS-RETUR-2021-07-03-000001][Tanggal: 2021-07-03][Tipe Retur: BARANG][Status: menunggu konfirmasi][Oleh: admin][Waktu Diedit: 21-06-26 01:01:03][Nomor Control: 1][Bulan Control: ][Tahun Control: ]', '', 5, '2021-06-26 01:01:03'),
(372, 'Data Retur Barang baru ditambahkan. Waktu penambahan: 21-06-26 01:01:03', '[ID Retur Barang: 14][ID Retur: 10][ID Barang: 23][Jumalh Barang: 100][Satuan Barang: Pcs][Notes: -][Status: aktif][Waktu Ditambahkan: 21-06-26 01:01:03][Oleh: admin]', '', 5, '2021-06-26 01:01:03'),
(373, 'Data Retur Barang baru ditambahkan. Waktu penambahan: 21-06-26 01:01:03', '[ID Retur Barang: 15][ID Retur: 10][ID Barang: 24][Jumalh Barang: 100][Satuan Barang: Pcs][Notes: -][Status: aktif][Waktu Ditambahkan: 21-06-26 01:01:03][Oleh: admin]', '', 5, '2021-06-26 01:01:03'),
(374, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-26 01:01:03', '[ID Retur Kembali: 13][Jumlah: 200][Satuan: Pcs][Harga: 2000][Notes: -][Status: aktif][ID Retur: 10][ID Barang: 13][Waktu Ditambahkan: 21-06-26 01:01:03][Oleh: admin]', '', 5, '2021-06-26 01:01:03'),
(375, 'Data Retur Kembali baru ditambahkan. Waktu penambahan: 21-06-26 01:01:03', '[ID Retur Kembali: 14][Jumlah: 300][Satuan: Pcs][Harga: 3000][Notes: -][Status: aktif][ID Retur: 10][ID Barang: 15][Waktu Ditambahkan: 21-06-26 01:01:03][Oleh: admin]', '', 5, '2021-06-26 01:01:03'),
(376, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:02:09', '[ID Pengiriman: 49][No: PS-PENGIRIMAN-2021-07-03-000001][Tanggal: 2021-07-03][Status: AKTIF][Tipe: retur][ID Penjualan: ][ID Retur: 10][Tempat: cabang][Waktu Ditambahkan: 21-06-26 01:02:09][Oleh: admin][Nomor Control: 1][Bulan Control: ]', '', 5, '2021-06-26 01:02:09'),
(377, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:02:09', '[ID Barang Pengiriman: 126][Jumlah: 99][Notes: -][ID Pengiriman: 49][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 13][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 01:02:09][Oleh: admin]', '', 5, '2021-06-26 01:02:09'),
(378, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:02:09', '[ID Barang Pengiriman: 127][Jumlah: 99][Notes: -][ID Pengiriman: 49][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 14][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 01:02:09][Oleh: admin]', '', 5, '2021-06-26 01:02:09'),
(379, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:02:58', '[ID Pengiriman: 50][No: PS-PENGIRIMAN-2021-07-03-000002][Tanggal: 2021-07-03][Status: AKTIF][Tipe: retur][ID Penjualan: ][ID Retur: 10][Tempat: cabang][Waktu Ditambahkan: 21-06-26 01:02:58][Oleh: admin][Nomor Control: 2][Bulan Control: ]', '', 5, '2021-06-26 01:02:58'),
(380, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:02:58', '[ID Barang Pengiriman: 128][Jumlah: 1][Notes: -][ID Pengiriman: 50][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 13][ID Barang Pemenuhan: ][ID Satuan: 3][Waktu Ditambahkan: 21-06-26 01:02:58][Oleh: admin]', '', 5, '2021-06-26 01:02:58'),
(381, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:02:58', '[ID Barang Pengiriman: 129][Jumlah: 1][Notes: -][ID Pengiriman: 50][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: 14][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Ditambahkan: 21-06-26 01:02:58][Oleh: admin]', '', 5, '2021-06-26 01:02:58'),
(382, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 10:49:02', '[ID Pengiriman: 51][No: PRINDAH-PENGIRIMAN-2021-06-26-000003][Tanggal: 2021-06-26 10:49:02][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 10:49:02][Oleh: admin][Nomor Control: 3][Bulan Control: ]', '', 5, '2021-06-26 10:49:02'),
(383, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 10:49:03', '[ID Barang Pengiriman: 130][Jumlah: 1000.00][Notes: -][ID Pengiriman: 51][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 27][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 10:49:03][Oleh: admin]', '', 5, '2021-06-26 10:49:03'),
(384, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-26 11:03:34', '[ID Barang Permintaan: 21][Jumlah: 100][Notes: -][Deadline: 2021-06-26][Status: aktif][ID Barang: 23][ID Cabang: 5][Waktu Ditambahkan: 21-06-26 11:03:34][Oleh: admin]', '', 5, '2021-06-26 11:03:34'),
(385, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-26 11:03:42', '[ID Barang Permintaan: 22][Jumlah: 200][Notes: -][Deadline: 2021-06-26][Status: aktif][ID Barang: 24][ID Cabang: 5][Waktu Ditambahkan: 21-06-26 11:03:42][Oleh: admin]', '', 5, '2021-06-26 11:03:42'),
(386, 'Data Barang Permintaan baru ditambahkan. Waktu penambahan: 21-06-26 11:03:51', '[ID Barang Permintaan: 23][Jumlah: 123][Notes: -][Deadline: 2021-07-03][Status: aktif][ID Barang: 12][ID Cabang: 5][Waktu Ditambahkan: 21-06-26 11:03:51][Oleh: admin]', '', 5, '2021-06-26 11:03:51'),
(387, 'Penambahan data barang Maju Mandiri cabang -', 'Nama barang: 4007 H Jumlah barang: 3000, Catatan :-', '-', 5, '2021-06-26 11:04:35'),
(388, 'Penambahan data barang Maju Mandiri cabang -', 'Nama barang: 4008 H Jumlah barang: 3000, Catatan :-', '-', 5, '2021-06-26 11:04:36'),
(389, 'Data Barang Permintaan dengan ID: 21 diubah. Waktu diubah: 21-06-26 11:05:37 . Data berubah menjadi: ', '[ID Barang Permintaan: 21][Jumlah: 100.00][Notes: -][Deadline: 2021-07-10][ID Barang: ][ID Cabang: ][Waktu Diubah: 21-06-26 11:05:37][Oleh: admin]', '', 5, '2021-06-26 11:05:37'),
(390, 'Data Barang Permintaan dengan ID: 22 diubah. Waktu diubah: 21-06-26 11:05:42 . Data berubah menjadi: ', '[ID Barang Permintaan: 22][Jumlah: 200.00][Notes: -][Deadline: 2021-07-10][ID Barang: ][ID Cabang: ][Waktu Diubah: 21-06-26 11:05:42][Oleh: admin]', '', 5, '2021-06-26 11:05:42'),
(391, 'Data Barang Permintaan dengan ID: 23 diubah. Waktu diubah: 21-06-26 11:05:47 . Data berubah menjadi: ', '[ID Barang Permintaan: 23][Jumlah: 123.00][Notes: -][Deadline: 2021-07-10][ID Barang: ][ID Cabang: ][Waktu Diubah: 21-06-26 11:05:47][Oleh: admin]', '', 5, '2021-06-26 11:05:47'),
(392, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 11:07:24', '[ID Pengiriman: 52][No: PS-PENGIRIMAN-2021-06-26-000006][Tanggal: 2021-06-26 11:07:24][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 11:07:24][Oleh: admin][Nomor Control: 6][Bulan Control: ]', '', 5, '2021-06-26 11:07:24'),
(393, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 11:07:24', '[ID Barang Pengiriman: 131][Jumlah: 10.00][Notes: -][ID Pengiriman: 52][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 28][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 11:07:24][Oleh: admin]', '', 5, '2021-06-26 11:07:24'),
(394, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 11:07:27', '[ID Pengiriman: 53][No: PS-PENGIRIMAN-2021-06-26-000007][Tanggal: 2021-06-26 11:07:27][Status: aktif][Tipe: permintaan][ID Penjualan: ][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 11:07:27][Oleh: admin][Nomor Control: 7][Bulan Control: ]', '', 5, '2021-06-26 11:07:27'),
(395, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 11:07:27', '[ID Barang Pengiriman: 132][Jumlah: 20.00][Notes: -][ID Pengiriman: 53][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: 29][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 11:07:27][Oleh: admin]', '', 5, '2021-06-26 11:07:27'),
(396, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:51:05', '[ID Pengiriman: 54][No: PS-PENGIRIMAN-2021-07-03-000003][Tanggal: 2021-07-03][Status: AKTIF][Tipe: penjualan][ID Penjualan: 5][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 12:51:05][Oleh: admin][Nomor Control: 3][Bulan Control: ]', '', 5, '2021-06-26 12:51:05');
INSERT INTO `log_all` (`id_pk_log_all`, `log_all_msg`, `log_all_data_changes`, `log_all_it`, `log_all_user`, `log_all_tgl`) VALUES
(397, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:51:05', '[ID Barang Pengiriman: 133][Jumlah: 58][Notes: -][ID Pengiriman: 54][ID Barang Penjualan: 7][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:51:05][Oleh: admin]', '', 5, '2021-06-26 12:51:05'),
(398, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 12:51:05', '[ID Barang Pengiriman: 134][Jumlah: 58][Notes: -][ID Pengiriman: 54][ID Barang Penjualan: 8][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Ditambahkan: 21-06-26 12:51:05][Oleh: admin]', '', 5, '2021-06-26 12:51:05'),
(399, 'Data Pengiriman dengan ID: 54 diubah. Waktu diubah: 21-06-26 12:51:27 . Data berubah menjadi: ', '[ID Pengiriman: 54][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 12:51:27][Oleh: admin]', '', 5, '2021-06-26 12:51:27'),
(400, 'Data Barang Pengiriman dengan ID: 133 diubah. Waktu diubah: 21-06-26 12:51:27 . Data berubah menjadi: ', '[ID Barang Pengiriman: 133][Jumlah: 60][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Diubah: 21-06-26 12:51:27][Oleh: admin]', '', 5, '2021-06-26 12:51:27'),
(401, 'Data Barang Pengiriman dengan ID: 134 diubah. Waktu diubah: 21-06-26 12:51:27 . Data berubah menjadi: ', '[ID Barang Pengiriman: 134][Jumlah: 60][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Diubah: 21-06-26 12:51:27][Oleh: admin]', '', 5, '2021-06-26 12:51:27'),
(402, 'Data Pengiriman dengan ID: 54 diubah. Waktu diubah: 21-06-26 12:51:48 . Data berubah menjadi: ', '[ID Pengiriman: 54][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 12:51:48][Oleh: admin]', '', 5, '2021-06-26 12:51:48'),
(403, 'Data Barang Pengiriman dengan ID: 133 diubah. Waktu diubah: 21-06-26 12:51:48 . Data berubah menjadi: ', '[ID Barang Pengiriman: 133][Jumlah: 70][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Diubah: 21-06-26 12:51:48][Oleh: admin]', '', 5, '2021-06-26 12:51:48'),
(404, 'Data Barang Pengiriman dengan ID: 134 diubah. Waktu diubah: 21-06-26 12:51:48 . Data berubah menjadi: ', '[ID Barang Pengiriman: 134][Jumlah: 70][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Diubah: 21-06-26 12:51:48][Oleh: admin]', '', 5, '2021-06-26 12:51:48'),
(405, 'Data Pengiriman dengan ID: 54 diubah. Waktu diubah: 21-06-26 12:52:14 . Data berubah menjadi: ', '[ID Pengiriman: 54][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 12:52:14][Oleh: admin]', '', 5, '2021-06-26 12:52:14'),
(406, 'Data Barang Pengiriman dengan ID: 133 diubah. Waktu diubah: 21-06-26 12:52:14 . Data berubah menjadi: ', '[ID Barang Pengiriman: 133][Jumlah: 70.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 12:52:14][Oleh: admin]', '', 5, '2021-06-26 12:52:14'),
(407, 'Data Barang Pengiriman dengan ID: 134 diubah. Waktu diubah: 21-06-26 12:52:14 . Data berubah menjadi: ', '[ID Barang Pengiriman: 134][Jumlah: 70.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 12:52:14][Oleh: admin]', '', 5, '2021-06-26 12:52:14'),
(408, 'Data Pengiriman dengan ID: 50 diubah. Waktu diubah: 21-06-26 01:02:06 . Data berubah menjadi: ', '[ID Pengiriman: 50][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 01:02:06][Oleh: admin]', '', 5, '2021-06-26 01:02:06'),
(409, 'Data Barang Pengiriman dengan ID: 128 diubah. Waktu diubah: 21-06-26 01:02:06 . Data berubah menjadi: ', '[ID Barang Pengiriman: 128][Jumlah: 10][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 3][Waktu Diubah: 21-06-26 01:02:06][Oleh: admin]', '', 5, '2021-06-26 01:02:06'),
(410, 'Data Barang Pengiriman dengan ID: 129 diubah. Waktu diubah: 21-06-26 01:02:06 . Data berubah menjadi: ', '[ID Barang Pengiriman: 129][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 01:02:06][Oleh: admin]', '', 5, '2021-06-26 01:02:06'),
(411, 'Data Pengiriman dengan ID: 50 diubah. Waktu diubah: 21-06-26 01:02:17 . Data berubah menjadi: ', '[ID Pengiriman: 50][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 01:02:17][Oleh: admin]', '', 5, '2021-06-26 01:02:17'),
(412, 'Data Barang Pengiriman dengan ID: 128 diubah. Waktu diubah: 21-06-26 01:02:17 . Data berubah menjadi: ', '[ID Barang Pengiriman: 128][Jumlah: 1][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 3][Waktu Diubah: 21-06-26 01:02:17][Oleh: admin]', '', 5, '2021-06-26 01:02:17'),
(413, 'Data Barang Pengiriman dengan ID: 129 diubah. Waktu diubah: 21-06-26 01:02:17 . Data berubah menjadi: ', '[ID Barang Pengiriman: 129][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 01:02:17][Oleh: admin]', '', 5, '2021-06-26 01:02:17'),
(414, 'Data Pengiriman dengan ID: 50 diubah. Waktu diubah: 21-06-26 01:02:27 . Data berubah menjadi: ', '[ID Pengiriman: 50][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 01:02:27][Oleh: admin]', '', 5, '2021-06-26 01:02:27'),
(415, 'Data Barang Pengiriman dengan ID: 128 diubah. Waktu diubah: 21-06-26 01:02:27 . Data berubah menjadi: ', '[ID Barang Pengiriman: 128][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 01:02:27][Oleh: admin]', '', 5, '2021-06-26 01:02:27'),
(416, 'Data Barang Pengiriman dengan ID: 129 diubah. Waktu diubah: 21-06-26 01:02:27 . Data berubah menjadi: ', '[ID Barang Pengiriman: 129][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 01:02:27][Oleh: admin]', '', 5, '2021-06-26 01:02:27'),
(417, 'Data Pengiriman dengan ID: 50 diubah. Waktu diubah: 21-06-26 01:02:41 . Data berubah menjadi: ', '[ID Pengiriman: 50][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 01:02:41][Oleh: admin]', '', 5, '2021-06-26 01:02:41'),
(418, 'Data Barang Pengiriman dengan ID: 128 diubah. Waktu diubah: 21-06-26 01:02:41 . Data berubah menjadi: ', '[ID Barang Pengiriman: 128][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 1][Waktu Diubah: 21-06-26 01:02:41][Oleh: admin]', '', 5, '2021-06-26 01:02:41'),
(419, 'Data Barang Pengiriman dengan ID: 129 diubah. Waktu diubah: 21-06-26 01:02:41 . Data berubah menjadi: ', '[ID Barang Pengiriman: 129][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 01:02:41][Oleh: admin]', '', 5, '2021-06-26 01:02:41'),
(420, 'Data Pengiriman dengan ID: 50 diubah. Waktu diubah: 21-06-26 01:02:54 . Data berubah menjadi: ', '[ID Pengiriman: 50][Tanggal: 2021-07-03][Waktu Diedit: 21-06-26 01:02:54][Oleh: admin]', '', 5, '2021-06-26 01:02:54'),
(421, 'Data Barang Pengiriman dengan ID: 128 diubah. Waktu diubah: 21-06-26 01:02:54 . Data berubah menjadi: ', '[ID Barang Pengiriman: 128][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 01:02:54][Oleh: admin]', '', 5, '2021-06-26 01:02:54'),
(422, 'Data Barang Pengiriman dengan ID: 129 diubah. Waktu diubah: 21-06-26 01:02:54 . Data berubah menjadi: ', '[ID Barang Pengiriman: 129][Jumlah: 1.00][Notes: -][ID Pengiriman: ][ID Barang Penjualan: ][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Diubah: 21-06-26 01:02:54][Oleh: admin]', '', 5, '2021-06-26 01:02:54'),
(423, 'Data Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:08:45', '[ID Pengiriman: 55][No: PS-PENGIRIMAN-2021-07-03-000003][Tanggal: 2021-07-03][Status: AKTIF][Tipe: penjualan][ID Penjualan: 5][ID Retur: ][Tempat: cabang][Waktu Ditambahkan: 21-06-26 01:08:45][Oleh: admin][Nomor Control: 3][Bulan Control: ]', '', 5, '2021-06-26 01:08:45'),
(424, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:08:45', '[ID Barang Pengiriman: 135][Jumlah: 1][Notes: -][ID Pengiriman: 55][ID Barang Penjualan: 7][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Ditambahkan: 21-06-26 01:08:45][Oleh: admin]', '', 5, '2021-06-26 01:08:45'),
(425, 'Data Barang Pengiriman baru ditambahkan. Waktu penambahan: 21-06-26 01:08:45', '[ID Barang Pengiriman: 136][Jumlah: 1][Notes: -][ID Pengiriman: 55][ID Barang Penjualan: 8][ID Barang penjualan yang dikembalikan: ][ID Barang Pemenuhan: ][ID Satuan: 2][Waktu Ditambahkan: 21-06-26 01:08:45][Oleh: admin]', '', 5, '2021-06-26 01:08:45'),
(426, 'Data Admin Cabang baru ditambahkan. Waktu penambahan: 21-10-15 08:19:43', '[ID Admin Cabang: 12][ID Cabang: 5][ID User: 4][Status: AKTIF][Waktu Ditambahkan: 21-10-15 08:19:43][Oleh: calandra]', '', 4, '2021-10-15 08:19:43'),
(427, 'Data Admin Cabang baru ditambahkan. Waktu penambahan: 21-10-15 08:20:54', '[ID Admin Cabang: 13][ID Cabang: 3][ID User: 4][Status: AKTIF][Waktu Ditambahkan: 21-10-15 08:20:54][Oleh: calandra]', '', 4, '2021-10-15 08:20:54'),
(428, 'Data Penawaran baru ditambahkan. Waktu penambahan: 21-10-16 07:33:01', '[ID Penawaran: 11][Subject: ][Content: ][Notes: ][Referensi: ][Tanggal: ][Status: ][ID Cabang: ][Waktu Ditambahkan: 21-10-16 07:33:01[Oleh: calandra]', '', 4, '2021-10-16 07:33:01'),
(429, 'Data Barang Penawaran baru ditambahkan. Waktu penambahan: 2021-10-16 07:33:01', '[ID Barang Penawaran: 9][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Status: aktif][ID Penawaran: 11][Waktu Ditambahkan: 2021-10-16 07:33:01][Oleh: calandra]', '', 4, '2021-10-16 07:33:01'),
(430, 'Data Penawaran dengan ID:  diubah. Waktu diubah: 21-10-16 07:51:05 . Data berubah menjadi: ', '[ID Penawaran: ][No: ][Subject: ][Content: ][Notes: ][Referensi: ][Tanggal: ][ID Cabang: ][Waktu Diedit: 21-10-16 07:51:05[Oleh: calandra]', '', 4, '2021-10-16 07:51:05'),
(431, 'Data Barang Penawaran dengan ID: 9 diubah. Waktu diubah: date(\'Y-m-d H:i:s\') . Data berubah menjadi: ', '[ID Barang Penawaran: 9][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Waktu Diedit: 2021-10-16 07:51:05][Oleh: calandra]', '', 4, '2021-10-16 07:51:05'),
(432, 'Data Penawaran baru ditambahkan. Waktu penambahan: 21-10-16 07:53:53', '[ID Penawaran: 12][No: ][Subject: ][Content: ][Notes: ][Referensi: ][Tanggal: ][Status: ][ID Cabang: ][Waktu Ditambahkan: 21-10-16 07:53:53[Oleh: calandra]', '', 4, '2021-10-16 07:53:53'),
(433, 'Data Barang Penawaran baru ditambahkan. Waktu penambahan: 2021-10-16 07:53:53', '[ID Barang Penawaran: 10][Jumlah: 100][Satuan: Pcs][Harga: 2000][Notes: -][Status: aktif][ID Penawaran: 12][Waktu Ditambahkan: 2021-10-16 07:53:53][Oleh: calandra]', '', 4, '2021-10-16 07:53:53'),
(434, 'Data Barang Penawaran baru ditambahkan. Waktu penambahan: 2021-10-16 07:53:53', '[ID Barang Penawaran: 11][Jumlah: 340][Satuan: Pcs][Harga: 4300][Notes: -][Status: aktif][ID Penawaran: 12][Waktu Ditambahkan: 2021-10-16 07:53:53][Oleh: calandra]', '', 4, '2021-10-16 07:53:53'),
(435, 'Data Penjualan baru ditambahkan. Waktu penambahan: 21-10-16 08:01:06', '[ID Penjualan: 6][Nomor Penjualan: ][Tanggal: ][Dateline: ][Jenis Penjualan: ][Tipe Pembayaran: ][ID Customer: ][ID Cabang: ][Waktu Ditambahkan: 21-10-16 08:01:06][Oleh: calandra]', '', 4, '2021-10-16 08:01:06'),
(436, 'Data Penjualan Online baru ditambahkan. Waktu penambahan: 21-10-16 08:01:06', '[ID Penjualan Online: 14][Marketplace: ][Resi: ][Kurir: ][Status: ][ID Penjualan: ][Tanggal Penjualan: 21-10-16 08:01:06]][Oleh: calandra]', '', 4, '2021-10-16 08:01:06'),
(437, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-10-16 08:01:06', '[ID Barang Jenis: 9][Jumlah (real): 0][Satuan (real): Pcs][Jumlah: 100][Satuan: Pcs][Harga: 1000][Notes: -][Status: AKTIF][ID Penjualan: 6][ID Barang: 24][Waktu Ditambahkan: 21-10-16 08:01:06][Oleh: calandra]', '', 4, '2021-10-16 08:01:06'),
(438, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-10-16 08:01:06', '[ID Barang Jenis: 10][Jumlah (real): 0][Satuan (real): Pcs][Jumlah: 200][Satuan: Pcs][Harga: 200][Notes: -][Status: AKTIF][ID Penjualan: 6][ID Barang: 23][Waktu Ditambahkan: 21-10-16 08:01:06][Oleh: calandra]', '', 4, '2021-10-16 08:01:06'),
(439, 'Data Penjualan baru ditambahkan. Waktu penambahan: 21-10-16 08:02:21', '[ID Penjualan: 7][Nomor Penjualan: ][Tanggal: ][Dateline: ][Jenis Penjualan: ][Tipe Pembayaran: ][ID Customer: ][ID Cabang: ][Waktu Ditambahkan: 21-10-16 08:02:21][Oleh: calandra]', '', 4, '2021-10-16 08:02:21'),
(440, 'Data Penjualan Online baru ditambahkan. Waktu penambahan: 21-10-16 08:02:21', '[ID Penjualan Online: 15][Marketplace: ][Resi: ][Kurir: ][Status: ][ID Penjualan: ][Tanggal Penjualan: 21-10-16 08:02:21]][Oleh: calandra]', '', 4, '2021-10-16 08:02:21'),
(441, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-10-16 08:02:21', '[ID Barang Jenis: 11][Jumlah (real): 0][Satuan (real): Pcs][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Status: AKTIF][ID Penjualan: 7][ID Barang: 24][Waktu Ditambahkan: 21-10-16 08:02:21][Oleh: calandra]', '', 4, '2021-10-16 08:02:21'),
(442, 'Data Pembayaran Penjualan baru ditambahkan. Waktu penambahan: 21-10-16 08:02:21', '[ID Pembayaran Penjualan: 10][ID Penjualan: 7][Nama: Full Payment][Persen Pembayaran: Cash][Nominal Pembayaran: 154000][Notes: -][Dateline: 2021-10-23][Status: aktif][Waktu Ditambahkan: 21-10-16 08:02:21][Oleh: calandra]', '', 4, '2021-10-16 08:02:21'),
(443, 'Data Penjualan dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:06 . Data berubah menjadi: ', '[ID Penjualan: ][Nomor Penjualan: ][Tanggal: ][Dateline: ][Jenis Penjualan: ][Tipe Pembayaran: ][ID Customer: ][Waktu Diedit: 21-10-16 08:03:06][Oleh: calandra]', '', 4, '2021-10-16 08:03:06'),
(444, 'Data Penjualan Online dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:06 . Data berubah menjadi: ', '[ID Penjualan: ][Marketplace: ][Resi: ][Kurir: ][Waktu Diedit: 21-10-16 08:03:06]][Oleh: calandra]', '', 4, '2021-10-16 08:03:06'),
(445, 'Data Barang Penjualan dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:06 . Data berubah menjadi: ', '[ID Barang Jenis: ][Jumlah (real): ][Satuan (real): ][Jumlah: ][Satuan: ][Harga: ][Notes: ][ID Penjualan: ][ID Barang: ][Waktu Diubah: 4][Oleh: calandra]', '', 4, '2021-10-16 08:03:06'),
(446, 'Data Pembayaran Penjualan dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:06 . Data berubah menjadi: ', '[ID Pembayaran Penjualan: ][Nama: Full Payment][Persen Pembayaran: Cash][Nominal Pembayaran: 1540000][Notes: -][Dateline: 2021-10-16][Status: aktif][Waktu Diedit: 21-10-16 08:03:06][Oleh: calandra]', '', 4, '2021-10-16 08:03:06'),
(447, 'Data Jenis Barang baru ditambahkan. Waktu penambahan: 21-10-16 08:03:06', '[ID Barang Jenis: 12][Jumlah (real): 0][Satuan (real): Pcs][Jumlah: 2000][Satuan: Pcs][Harga: 200][Notes: -][Status: AKTIF][ID Penjualan: 7][ID Barang: 23][Waktu Ditambahkan: 21-10-16 08:03:06][Oleh: calandra]', '', 4, '2021-10-16 08:03:06'),
(448, 'Data Penjualan dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:38 . Data berubah menjadi: ', '[ID Penjualan: ][Nomor Penjualan: ][Tanggal: ][Dateline: ][Jenis Penjualan: ][Tipe Pembayaran: ][ID Customer: ][Waktu Diedit: 21-10-16 08:03:38][Oleh: calandra]', '', 4, '2021-10-16 08:03:38'),
(449, 'Data Penjualan Online dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:38 . Data berubah menjadi: ', '[ID Penjualan: ][Marketplace: ][Resi: ][Kurir: ][Waktu Diedit: 21-10-16 08:03:38]][Oleh: calandra]', '', 4, '2021-10-16 08:03:38'),
(450, 'Data Barang Penjualan dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:38 . Data berubah menjadi: ', '[ID Barang Jenis: ][Jumlah (real): ][Satuan (real): ][Jumlah: ][Satuan: ][Harga: ][Notes: ][ID Penjualan: ][ID Barang: ][Waktu Diubah: 4][Oleh: calandra]', '', 4, '2021-10-16 08:03:38'),
(451, 'Data Barang Penjualan dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:38 . Data berubah menjadi: ', '[ID Barang Jenis: ][Jumlah (real): ][Satuan (real): ][Jumlah: ][Satuan: ][Harga: ][Notes: ][ID Penjualan: ][ID Barang: ][Waktu Diubah: 4][Oleh: calandra]', '', 4, '2021-10-16 08:03:38'),
(452, 'Data Pembayaran Penjualan dengan ID:  diubah. Waktu diubah: 21-10-16 08:03:38 . Data berubah menjadi: ', '[ID Pembayaran Penjualan: ][Nama: Full Payment][Persen Pembayaran: Cash][Nominal Pembayaran: 1540000][Notes: -][Dateline: 2021-10-16][Status: aktif][Waktu Diedit: 21-10-16 08:03:38][Oleh: calandra]', '', 4, '2021-10-16 08:03:38'),
(453, 'Data Penawaran dengan ID:  diubah. Waktu diubah: 21-10-16 08:24:26 . Data berubah menjadi: ', '[ID Penawaran: ][No: ][Subject: ][Content: ][Notes: ][Referensi: ][Tanggal: ][ID Cabang: ][Waktu Diedit: 21-10-16 08:24:26[Oleh: calandra]', '', 4, '2021-10-16 08:24:26'),
(454, 'Data Barang Penawaran dengan ID: 9 diubah. Waktu diubah: date(\'Y-m-d H:i:s\') . Data berubah menjadi: ', '[ID Barang Penawaran: 9][Jumlah: 1000][Satuan: Pcs][Harga: 1000][Notes: -][Waktu Diedit: 2021-10-16 08:24:26][Oleh: calandra]', '', 4, '2021-10-16 08:24:26'),
(455, 'Data Barang Penawaran baru ditambahkan. Waktu penambahan: 2021-10-16 08:24:26', '[ID Barang Penawaran: 12][Jumlah: 2000][Satuan: Pcs][Harga: 2000][Notes: -][Status: aktif][ID Penawaran: 11][Waktu Ditambahkan: 2021-10-16 08:24:26][Oleh: calandra]', '', 4, '2021-10-16 08:24:26');

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
  `brg_harga_toko` int(11) NOT NULL,
  `brg_harga_grosir` int(11) NOT NULL,
  `brg_tipe` varchar(30) DEFAULT NULL,
  `brg_status` varchar(15) DEFAULT NULL,
  `brg_create_date` datetime DEFAULT NULL,
  `brg_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_brg_jenis` int(11) DEFAULT NULL,
  `id_fk_brg_merk` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mstr_barang`
--

INSERT INTO `mstr_barang` (`id_pk_brg`, `brg_kode`, `brg_nama`, `brg_ket`, `brg_minimal`, `brg_satuan`, `brg_image`, `brg_harga`, `brg_harga_toko`, `brg_harga_grosir`, `brg_tipe`, `brg_status`, `brg_create_date`, `brg_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_brg_jenis`, `id_fk_brg_merk`) VALUES
(1, 'BK-0012910', 'Plastik Packaging', '', 1000, 'psg', 'noimage.jpg', 125000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2021-06-10 01:47:59', 1, 5, 0, 25),
(2, '-', 'Sepatu', '-Sepatu pendek tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44', 0, 'psg', 'noimage.jpg', 165000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 25),
(3, '-', 'HELM CLIMB', '', 0, '', 'noimage.jpg', 550000, 0, 0, 'nonkombinasi', 'nonaktif', '2020-07-29 12:15:30', '2021-06-10 01:48:04', 1, 5, 0, 28),
(4, '-', 'Sepatu', '-Sepatu boots tinggi tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44', 0, 'psg', 'noimage.jpg', 260000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 25),
(5, '-', 'Sepatu', '-Sepatu sedang tali<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44', 0, 'psg', 'noimage.jpg', 200000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 25),
(6, '-', 'Sepatu', '-Sepatu boots di atas mata kaki<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44', 0, 'psg', 'noimage.jpg', 235000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 25),
(7, '-', 'Sepatu', '-Terdapat karet di samping<br>-Terdapat besi di depan<br>-Terbuat dari bahan kulit<br>-Sol bahan PVC<br>-Ukuran dari 39-44', 0, 'psg', 'noimage.jpg', 200000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 25),
(8, '-', '2001 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(9, '-', '2002 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(10, '-', '2101 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(11, '-', '2110 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(12, '-', '2111 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(13, '-', '2180 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(14, '-', '2286 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(15, '-', '2288 C/H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(16, '-', '2290 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(17, '-', '3001 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(18, '-', '3002 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(19, '-', '3110 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(20, '-', '3111 H', 'test', 0, 'LUSIN', 'noimage.jpg', 0, 0, 0, 'kombinasi', 'aktif', '2020-07-29 12:15:30', '2021-06-25 09:17:24', 1, 5, 14, 5),
(21, '-', '3180 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(22, '-', '3209 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(23, '-', '4007 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(24, '-', '4008 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(25, '-', '4108 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(26, '-', 'Rompi', '-Memiliki 4 kantong&nbsp;<br>- Bahan american drill<br>-Terdapat Zipper<br>-Memiliki reflective/Scotlight<br>-All Size / Hanya ada 1 ukuran<br>-Warna Merah,Orange,Hijau Stabilo,Hijau,dan biru', 0, 'pcs', 'noimage.jpg', 90000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 2, 13),
(27, '-', 'SEPATU', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(28, '-', '7001 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(29, '-', '7001 P', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(30, '-', '5101 CB/HA', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(31, '-', '5103 HA', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(32, '-', '5106 HA', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(33, '-', '7012 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(34, '-', '7106 H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(35, '-', '7112 C/H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(36, '-', '7288 C/H', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:30', '2020-07-29 12:15:30', 1, 1, 14, 5),
(37, '-', 'KWS 800 X', '', 0, '', 'noimage.jpg', 290000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(38, '-', 'KWS 803 X', '', 0, '', 'noimage.jpg', 330000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(39, '-', 'Sepatu', '-Single density light-weight and slip resistant polyurethane (PU) sole (SRC Outsole)<br>-Sole resistant to oils and acids/alkalis<br>Antistatic<br>-Printed Leather<br>-Breathable non-woven fabric lini', 0, 'psg', 'noimage.jpg', 305000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(40, '-', 'Sepatu', '<span>-Steel toe protection<br></span>-Dual density polyurethane (PU) sole with softer midsole to cushion shock impact (SRC Outsole)<br>-Lightweight &amp; slip resistant PU sole<br>-Sole resistant to ', 0, 'psg', 'noimage.jpg', 355000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(41, '-', 'Sepatu', '-Antistatic insole board<br>-Scuff cap for toe bumper protection<br>-Extra Wide 5-toe toecap provides maximum comfort for your toes<br>-200 Joules steel toe cap for impact and compression resistance<b', 0, 'Psg', 'noimage.jpg', 330000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(42, '-', 'Inspection mirror 8inc', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(43, '-', 'Wearpack', '-Bahan Drill\r\n<br>-Double zipper\r\n<br>-Terdapat scotlight<br>-Ukuran M-XL<br>-Harga di atas XL dikenakan biaya @10.000/X', 0, 'Pcs', 'noimage.jpg', 120000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(44, '-', 'Pakaian Kerja', '-Bahan Drill<br>-Terdapat Scotlight<br>-Baju dan celana terpisah<br>-Ukuran M-XL<br>-Harga di atas XL dikenakan biaya 10.000/X<br>', 0, 'pcs', 'noimage.jpg', 145000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(45, '-', 'Wearpack', '-Bahan Drill<br>-Double Zipper<br>-Terdapat Scotlight<br>-Ukuran M-XL<br>-Ukuran di atas XL dikenakan biaya 10.000/X', 0, 'pcs', 'noimage.jpg', 95000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 31),
(46, '-', 'Pakaian Kerja', '-Bahan Drill<br>-Terdapat Scotlight<br>-Hanya baju saja<br>-Ukuran M-XL<br>-Harga di atas XL dikenakan biaya 10.000/X', 0, 'pcs', 'noimage.jpg', 85000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(47, '-', 'KWD 706 X', '', 0, '', 'noimage.jpg', 330000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(48, '-', 'KWD 901 X', '', 0, '', 'noimage.jpg', 330000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(49, '-', 'KWD 807 X', '', 0, '', 'noimage.jpg', 290000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(50, '-', 'KWD 912 X', '', 0, '', 'noimage.jpg', 440000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(51, '-', 'KWD 804 X', '', 0, '', 'noimage.jpg', 420000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(52, '-', 'KWS 841 X', '', 0, '', 'noimage.jpg', 305000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(53, '-', 'KWS 941 X', '', 0, '', 'noimage.jpg', 350000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 15),
(54, '-', 'Rompi jaring X', '', 0, '', 'noimage.jpg', 13000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 6),
(55, '-', 'Rompi Jaring', '', 0, '', 'noimage.jpg', 13000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 13),
(56, '-', 'Rompi', '- memiliki 2 kantong&nbsp;<br>- memiliki reflective<br>- warna merah, orange, hijau stabilo, hijau, dan biru&nbsp;', 0, 'pcs', 'noimage.jpg', 75000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 13),
(57, '-', 'Wearpack', '-Bahan Catton 100%<br>-Tahan Percikan Api<br>-Double Zipper<br>-Sleting YKK<br>-Scotlight 3M<br>-Ukuran dari M-XL<br>-Ukuran di atas xl di kenakan biaya 50.000/X', 0, 'pcs', 'noimage.jpg', 430000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(58, '-', 'Pakaian Kerja', '-Bahan Catton 100%<br>-Tahan percikan api<br>-Scotlight 3M<br>-Sleting YKK<br>-Ukuran M-XL<br>-Harga di atas XL dikenakan biaya 50.000/X<br>', 0, 'pcs', 'noimage.jpg', 560000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(59, '-', 'Pakaian Kerja', '-Bahan Catton 100%<br>-Tahan percikan api<br>-Scotlight 3M<br>-Sleting YKK<br>-Ukuran M-XL<br>-Di atas XL di kenakan biaya 50.000/X', 0, '', 'noimage.jpg', 285000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(60, '-', 'Rompi', '- Memiliki 4 kantong depan<br>- Memiliki 1 kantong belakang&nbsp;<br>- Memiliki 1 kantong HT<br>- Memili 1 kantong pen&nbsp;<br>- Memiliki reflective 3M&nbsp;<br>-Single Zipper<br>-Untuk ukuran All Si', 0, 'pcs', 'noimage.jpg', 200000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 13),
(61, '-', 'ROMPI', '', 0, 'pcs', 'noimage.jpg', 37500, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 13),
(62, '-', 'Rompi Busa', '', 0, '', 'noimage.jpg', 31000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 13),
(63, '-', 'Rompi jaring Security', '', 0, 'pcs', 'noimage.jpg', 90000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 13),
(64, '-', 'Rompi polyster scotlight kombinasi', '', 0, '', 'noimage.jpg', 37500, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 11),
(65, '-', 'Wearpack', '<div>-Bahan Semi catton<br>-Terdapat Scotlight<br>-Double Zipper<br>-Coverall / Terusan<br>-Sleting YKK<br>-Ukuran M-XL<br>-Harga Di atas XL dikenakan biaya 35.000/X</div>', 0, 'pcs', 'noimage.jpg', 290000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(66, '12455', 'Wearpackkkkkkkk', '-Flaming Reterdant<br>-Double Zipper<br>-Sleting YKK<br>-NFPA 2112 :2012<br>-Harga di atas XL dikenakan biaya 150.000/X', 0, 'pcs', 'noimage.jpg', 1250000, 10000, 200000, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2021-05-09 07:39:34', 1, 2, 1, 9),
(67, '333', 'Wearpack333', '-Flaming Reterdant<br>-Double Zipper<br>-NFPA 2112 :2012<br>-Harga di atas XL dikenakan biaya Rp. 150.000 / X', 0, 'pcs', 'noimage.jpg', 1550000, 123455, 123123, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2021-05-09 05:31:39', 1, 2, 1, 9),
(68, '-', 'Rompi polyster scotlight abu-abu', '', 0, '', 'noimage.jpg', 32500, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 11),
(69, '-', 'BODY HARNESS', 'Terdapat 1 cantelan besi', 0, 'pcs', 'noimage.jpg', 195000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 3, 11),
(70, '-', 'BODY HARNESS', 'Terdapat 2 cantelan besi', 0, 'pcs', 'noimage.jpg', 235000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 3, 11),
(71, '-', 'Helm', '-Model V-Gard<br>-Tanpa Fasttrack<br>-Warna Merah,Kuning,Orange,Biru, dan Putih', 0, 'pcs', 'noimage.jpg', 26000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 23),
(72, '-', 'HELM CLIMB', '', 0, '', 'noimage.jpg', 600000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 7),
(73, '-', 'HELM CLIMB', '', 0, '', 'noimage.jpg', 750000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 24),
(74, '-', 'Helm', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 1),
(75, '-', 'HELM', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 32),
(76, '-', 'Rompi tanpa kantong', '', 0, '', 'noimage.jpg', 60000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 2, 13),
(77, '-', 'Seragam security PDL', '-Seragam Baju Celana<br>-Warna Seragam B.dongker<br>-Baju lengan panjang<br>-Bet Wilayah<br>-Bahan Drill', 0, 'pcs', 'noimage.jpg', 185000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(78, '-', 'Seragam secuirty PDH', '-Seragam Baju Celana<br>-Warna Seragam Putih Hitam<br>-Bet Wilayah<br>-Bahan Drill', 0, 'pcs', 'noimage.jpg', 185000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(79, '-', 'Wearpack', '-Double Zipper<br>-Terusan/Coverall', 0, 'pcs', 'noimage.jpg', 850000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 1),
(80, '-', 'Stiker Kuning Hitam 2inc', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(81, '-', 'Stiker Kuning Hitam 4inc', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(82, '-', 'Stiker Kuning Hitam 2inc', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(83, '-', 'Stiker Kuning Hitam 4inc', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(84, '-', 'Stiker Apar', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(85, '-', 'Sepatu', '-Sepatu Security Tinggi<br>-Terdapat Besi Di Depan<br>-Warna Hitam<br>-Samping Terdapat Sleting<br>-Di Depan Terdapat Tali', 0, 'psg', 'noimage.jpg', 280000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 12),
(86, '-', 'Sepatu', '-Sepatu security pendek<br>-Warna Hitam', 0, 'psg', 'noimage.jpg', 170000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 1),
(87, '-', 'LOGO K3', '', 0, '', 'noimage.jpg', 5000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(88, '-', 'LOG0 MERAH PUTIH', '', 0, '', 'noimage.jpg', 5000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(89, '-', 'BENDERA K3', '', 0, '', 'noimage.jpg', 40000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(90, '-', 'BENDERA MERAH PUTIH', '', 0, '', 'noimage.jpg', 45000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(91, '-', 'Pentungan', '', 0, '', 'noimage.jpg', 45000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(92, '-', 'Talikur+pluit', '<div>- sepasang talikur dan pluit&nbsp;</div><div>- ada warna hitam dan putih&nbsp;</div>', 0, 'pcs', 'noimage.jpg', 25000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(93, '-', 'Helm', '-Model V-Gard<br>-Inner tidak ada puteran<br><br>', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 22),
(94, '-', 'HELM FULLBRIM', '', 0, '', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 22),
(95, '-', 'Helm', '-Model V-Gard<br>-terdapat Puteran di belakang', 0, 'Pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 22),
(96, '-', 'Helm', '-Model V-Gard<br>-Menggunakan Fasttrack<br>-Warna Merah,Kuning,Orange,Biru, dan Putih<br>', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 6, 23),
(97, '-', 'KACAMATA KY2221 (P)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(98, '-', 'SARUNG TANGAN', '-Sarung Tangan Kain<br>-Terdapat bintik polkadot di telapak tangan', 0, 'Lusin', 'noimage.jpg', 35000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 21),
(99, '-', 'EARPLUG ULTRAFIT w/ CASE', '-Earplug with corded and case<br>-1 box isi 50pcs', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 12, 1),
(100, '-', 'Masker', '-penjualan per box\r\n-1 box isi 20', 0, 'Box', 'noimage.jpg', 185000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 10, 1),
(101, '-', 'JAS HUJAN', '-JAS HUJAN BAJU CELANA', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 9, 30),
(102, '-', 'WINDSOCK ORANGE', '', 0, 'PCS', 'noimage.jpg', 225000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 13),
(103, '-', 'SARUNG TANGAN LAS', '-SARUNG TANGAN LAS<br>-BAHAN KULIT<br>-PANAJNG 16INC', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 11),
(104, '-', 'Sepatu', '-Impact resistance<br>-Water resistance<br>-Non slip shoes sole<br>-Steel toe cap 200 JOULE<br>-Oil and chemical resistance<br>-Size 38-44', 0, 'psg', 'noimage.jpg', 125000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 11),
(105, '-', 'Sepatu', '-Sepatu boots air<br>-Ukuran 39-43<br>-tidak terdapat besi di depan nya', 0, 'psg', 'noimage.jpg', 67000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 19),
(106, '-', 'SARUNG TANGAN', '-KevlarÂ® thread on all the seams<br>-Embossed inside reinforcement on palm<br>-Cut resistance support with high performance polyethylene<span><br>-</span>Neoprene reinforcement on back and fingertips', 0, 'psg', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 8),
(107, '-', 'Sepatu', '-Sepatu Safety Boots<br>-Terdapat besi di depan/toecap<br>-PU Sole<br>-Bahan nylon campur Kulit<br>-Ukuran 4-13<br>-SNI 7079:2009', 0, 'psg', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 17),
(108, '-', 'Sign', '-Ukuran 10x30CM<br>-Stiker&nbsp;<br>-Bahan Fosfor', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 7, 1),
(109, '-', 'Sign', '-Ukuran 20X35CM<br>-Bahan akrilik', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 7, 1),
(110, '-', 'Sign', '-Ukuran 50X50CM<br>-Bahan akrilik', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 7, 1),
(111, '-', 'Barricade', '-1 Roll 300M<br>-Lebar 3inc<br>-Warna Kuning Hitam<br>-Warna Merah Putih', 0, 'Roll', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(112, '-', 'Sepatu', '', 0, 'Psg', 'noimage.jpg', 130000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 27),
(113, '-', 'Sepatu', '-Sepatu Tinggi Security<br>-Tidak terdapat besi di depan<br>-Warna Hitam<br>-Bahan Kulit Jeruk', 0, 'psg', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 14, 1),
(114, '-', 'borgol', '', 0, 'pcs', 'noimage.jpg', 50000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(115, '-', 'BODY HARNESS', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 3, 11),
(116, '-', 'BODY HARNESS', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 3, 11),
(117, '-', 'JAS HUJAN', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 9, 33),
(118, '-', 'JAS HUJAN', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 9, 3),
(119, '-', 'JAS HUJAN', '', 0, 'pcs', 'noimage.jpg', 85000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 9, 18),
(120, '-', 'Masker', '', 0, 'box', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 10, 29),
(121, '-', 'Masker', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 10, 20),
(122, '-', 'Masker', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 10, 10),
(123, '-', 'PELAMPUNG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 4),
(124, '-', 'PELAMPUNG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 4),
(125, '-', 'PELAMPUNG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 4),
(126, '-', 'SARUNG TANGAN', '', 0, 'psg', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 14),
(127, '-', 'ABSORBER', '', 0, 'Pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 3, 11),
(128, '-', 'BODYHARNESS', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 3, 2),
(129, '-', 'SEGITIGA HATI HATI', '', 0, 'pcs', 'noimage.jpg', 50000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(130, '-', 'Pakaian Kerja', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 1),
(131, '-', 'Kopel', '', 0, 'pcs', 'noimage.jpg', 45000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(132, '-', 'Kopel', '', 0, 'pcs', 'noimage.jpg', 35000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(133, '-', 'Sepatu', '', 0, '', 'noimage.jpg', 180000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(134, '-', 'Sepatu', '', 0, '', 'noimage.jpg', 180000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(135, '-', 'CONVEX MIRROR', '', 0, 'pcs', 'noimage.jpg', 450000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(136, '-', 'CONVEX MIRROR', '', 0, 'pcs', 'noimage.jpg', 650000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(137, '-', 'EARPLUG CASE KUNING', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 12, 1),
(138, '-', 'APAR  3KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(139, '-', 'APAR 3,5KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(140, '-', 'APAR 4,5KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(141, '-', 'APAR 5KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(142, '-', 'APAR 6KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(143, '-', 'APAR 9KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(144, '-', 'APAR 25KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(145, '-', 'APAR 50KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(146, '-', 'APAR 75KG', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 13, 1),
(147, '-', 'Bet Security', '', 0, 'set', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(148, '-', 'SARUNG TANGAN', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 16),
(149, '-', 'SARUNG TANGAN LAS', '', 0, 'psg', 'noimage.jpg', 85000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 26),
(150, '-', 'WINDSOCK ORANGE', '', 0, 'PCS', 'noimage.jpg', 250000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 13),
(151, '-', 'WINDSOCK ORANGE', '', 0, 'PCS', 'noimage.jpg', 165000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 13),
(152, '-', 'Sticker K3', '', 0, 'PCS', 'noimage.jpg', 10000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(153, '-', 'Cold Storage', '', 0, 'pcs', 'noimage.jpg', 350000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(154, '-', 'Cold Storage', '', 0, 'pcs', 'noimage.jpg', 300000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(155, '-', 'Cold Storage', '', 0, 'set', 'noimage.jpg', 600000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 1, 13),
(156, '-', 'WINDSOCK ORANGE PUTIH', '', 0, 'PCS', 'noimage.jpg', 225000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 13),
(157, '-', 'WINDSOCK ORANGE PUTIH', '', 0, 'PCS', 'noimage.jpg', 250000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 13),
(158, '-', 'WINDSOCK ORANGE PUTIH', '', 0, 'PCS', 'noimage.jpg', 375000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 13),
(159, '-', 'KACAMATA KY2222 (H)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(160, '-', 'KACAMATA KY2223 (P)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(161, '-', 'KACAMATA KY2224 (H)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(162, '-', 'KACAMATA KY8811A (P)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(163, '-', 'KACAMATA KY8812A (H)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(164, '-', 'KACAMATA KY8813A (P)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(165, '-', 'KACAMATA KY8814A (H)', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 11, 15),
(166, '-', 'SARUNG TANGAN LISTRIK', '', 0, 'PCS', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 34),
(167, '-', 'SARUNG TANGAN', '', 0, 'PSG', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 16),
(168, '-', 'SARUNG TANGAN', '', 0, 'PSG', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 8, 1),
(169, '-', 'KAOS SECURITY', '', 0, 'PCS', 'noimage.jpg', 70000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(170, '-', 'KAOS SECURITY', '', 0, 'PCS', 'noimage.jpg', 60000, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 4, 1),
(171, '-', 'Sticker Reflective Merah Putih', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(172, '-', 'Sticker Reflective Kuning Hitam', '', 0, 'pcs', 'noimage.jpg', 0, 0, 0, 'nonkombinasi', 'aktif', '2020-07-29 12:15:31', '2020-07-29 12:15:31', 1, 1, 5, 1),
(175, 'Kode 1 ', 'Barang 1', '-', 1000, 'PCS', 'noimage.jpg', 10000, 0, 0, 'kombinasi', 'AKTIF', '2020-07-31 01:18:32', '2020-07-31 01:18:32', 2, 2, 1, 1),
(176, 'Kode 2', 'Nama 2', '-', 120, 'PCS', 'noimage.jpg', 123, 0, 0, 'kombinasi', 'AKTIF', '2020-07-31 04:43:16', '2020-07-31 04:43:16', 2, 2, 1, 2),
(177, 'Kode 3', 'Nama 3', '-', 1000, 'PCS', 'noimage.jpg', 1000, 0, 0, 'nonkombinasi', 'AKTIF', '2020-07-31 05:12:23', '2020-07-31 05:12:23', 2, 2, 1, 1),
(178, 'wewe', 'qwe', 'eeee', 0, 'PCS', 'noimage.jpg', 45343554, 0, 0, 'nonkombinasi', 'AKTIF', '2021-05-07 09:32:57', '2021-05-07 09:32:57', 2, 2, 1, 2),
(179, 'qwe', 'qew', 'qwe', 3, 'PCS', 'noimage.jpg', 123456, 12345, 12345, 'nonkombinasi', 'AKTIF', '2021-05-09 03:25:20', '2021-05-09 03:25:20', 2, 2, 3, 3),
(180, 'QA-BRG-KTR-1', 'QA-BRG-KTR-1', '-', 0, 'PCS', 'noimage.jpg', 1000, 1000, 1000, 'nonkombinasi', 'nonaktif', '2021-06-05 01:13:04', '2021-06-10 01:48:07', 2, 5, 0, 2),
(181, 'testbk1', 'bk', '-', 100, 'PCS', 'noimage.jpg', NULL, 0, 0, NULL, 'AKTIF', '2021-06-20 04:34:10', '2021-06-20 04:34:10', 5, 5, 0, 3),
(182, 'testbk23', 'bk23', '123123', 123123, 'LUSIN', 'noimage.jpg', 0, 0, 0, NULL, 'nonaktif', '2021-06-20 04:34:39', '2021-06-20 04:40:03', 5, 5, 0, 8);

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
-- Dumping data for table `mstr_barang_jenis`
--

INSERT INTO `mstr_barang_jenis` (`id_pk_brg_jenis`, `brg_jenis_nama`, `brg_jenis_status`, `brg_jenis_create_date`, `brg_jenis_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'COVERALL', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(2, 'ROMPI', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(3, 'BODYHARNESS', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(4, 'SECURITY', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(5, 'ETC', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(6, 'HELM', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(7, 'MARKA JALAN', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(8, 'SARUNG TANGAN', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(9, 'JAS HUJAN', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(10, 'MASKER', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(11, 'KACAMATA', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(12, 'EARPLUG & EARMUFF', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(13, 'PEMADAM', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(14, 'SEPATU', 'aktif', '2020-07-29 12:14:52', '2020-07-29 12:14:52', 1, 1),
(15, 'TEST2', 'nonaktif', '2020-07-31 12:40:39', '2020-07-31 12:53:07', 2, 2),
(16, 'TEST 3', 'nonaktif', '2020-07-31 12:52:58', '2020-07-31 12:53:01', 2, 2),
(17, 'TEST', 'nonaktif', '2020-07-31 04:06:43', '2021-06-09 01:24:20', 2, 2),
(18, 'TEST 4', 'nonaktif', '2020-07-31 04:10:31', '2021-06-09 01:24:17', 2, 2),
(0, 'BARANG KANTOR', 'aktif', '2021-04-25 22:51:31', '2021-04-25 22:51:31', 1, 1),
(20, 'asdfasdfsadfasdfasdfasfffffffffffff', 'nonaktif', '2021-05-24 09:20:41', '2021-05-24 09:20:51', 2, 2),
(21, 'Seminar Hello', 'nonaktif', '2021-05-24 09:24:37', '2021-06-09 01:24:14', 2, 2),
(22, 'CUSTOM', 'nonaktif', '2021-06-09 01:24:03', '2021-06-09 01:24:24', 2, 2);

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
-- Dumping data for table `mstr_barang_merk`
--

INSERT INTO `mstr_barang_merk` (`id_pk_brg_merk`, `brg_merk_nama`, `brg_merk_status`, `brg_merk_create_date`, `brg_merk_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, '3M', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(2, 'A-stabil', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(3, 'ALXO', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(4, 'ATUNAS', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(5, 'CHEETAH', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(6, 'CINA', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(7, 'CLIMB', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(8, 'DELTA', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(9, 'DUPONT', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(10, 'DZMASK', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(11, 'GOSAVE', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(12, 'Handyman', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(13, 'IMJ', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(14, 'JOGGER', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(15, 'KINGS', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(16, 'KONG', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(17, 'KRUSHERS', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(18, 'LAYAR', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(19, 'Mackers', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(20, 'MASKR', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(21, 'Matahari', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(22, 'MSA', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(23, 'NSA', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(24, 'RANGER', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(25, 'RED PARKER', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(26, 'REDRAM', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(27, 'REMIGIO', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(28, 'ROCKSTAR', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(29, 'SENSI', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(30, 'TIGER', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(31, 'TOMMY', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(32, 'TS', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(33, 'ULTRA', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(34, 'VICTOR', 'aktif', '2020-07-29 12:15:22', '2020-07-29 12:15:22', 1, 1),
(35, 'asdfasdfadfbabberbaerb', 'nonaktif', '2021-05-24 10:11:20', '2021-05-24 10:12:12', 2, 2),
(36, '1ACAII TEA CO.', 'nonaktif', '2021-06-09 01:26:56', '2021-06-09 01:27:32', 2, 2),
(37, '1ACAII TEA CO.', 'nonaktif', '2021-06-09 01:26:58', '2021-06-09 01:27:37', 2, 2),
(38, '1ACAII TEA CO.', 'nonaktif', '2021-06-09 01:27:02', '2021-06-09 01:27:39', 2, 2),
(39, '1ACAII TEA CO.', 'nonaktif', '2021-06-09 01:27:05', '2021-06-09 01:27:41', 2, 2),
(40, '1ACAII TEA CO.', 'nonaktif', '2021-06-09 01:27:05', '2021-06-09 01:27:47', 2, 2),
(41, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:07', '2021-06-09 01:27:43', 2, 2),
(42, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:07', '2021-06-09 01:27:49', 2, 2),
(43, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:07', '2021-06-09 01:27:52', 2, 2),
(44, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:07', '2021-06-09 01:28:14', 2, 2),
(45, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:07', '2021-06-09 01:28:16', 2, 2),
(46, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:08', '2021-06-09 01:28:19', 2, 2),
(47, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:08', '2021-06-09 01:28:21', 2, 2),
(48, 'SIMPLICITDY - INGENUITY - COMMUNITY', 'nonaktif', '2021-06-09 01:27:08', '2021-06-09 01:28:34', 2, 2),
(49, '1ACAII TEA CO.sdas', 'nonaktif', '2021-06-09 01:27:08', '2021-06-09 01:27:30', 2, 2),
(50, 'asdf', 'nonaktif', '2021-06-09 10:04:13', '2021-06-09 10:04:36', 5, 5),
(51, 'asdf', 'nonaktif', '2021-06-09 10:04:19', '2021-06-09 10:04:38', 5, 5),
(52, 'test', 'AKTIF', '2021-06-09 09:06:12', '2021-06-09 09:06:12', 5, 5);

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
-- Dumping data for table `mstr_cabang`
--

INSERT INTO `mstr_cabang` (`id_pk_cabang`, `cabang_nama`, `cabang_kode`, `cabang_daerah`, `cabang_kop_surat`, `cabang_nonpkp`, `cabang_pernyataan_rek`, `cabang_notelp`, `cabang_alamat`, `cabang_status`, `cabang_create_date`, `cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_toko`) VALUES
(1, 'Pusat Safety', 'PS', '-', 'Pendaftaran_SYNC_STUDY.png', 'Pendaftaran_SYNC_STUDY.png', 'Pendaftaran_SYNC_STUDY.png', '-', '-', 'AKTIF', '2020-07-02 10:10:14', '2021-06-09 01:37:06', 1, 2, 1),
(2, 'CABANG3', 'MSTRCABANG3', 'Kota2', 'granite_floor.jpg', 'Render3.png', 'Pendaftaran_SYNC_STUDY.png', '12345', 'Kota', 'nonaktif', '2020-07-03 07:14:26', '2021-06-09 01:36:38', 1, 2, 1),
(3, 'CBG1', 'CBG1', 'Cabang 1', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', '123456', 'Alamat ', 'AKTIF', '2020-07-06 09:24:50', '2021-06-16 10:29:38', 1, 5, 2),
(4, 'cabang2', 'CBG2', 'Pasar Baru', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', '-', '-', 'nonaktif', '2020-07-11 10:46:11', '2021-06-09 01:36:40', 1, 2, 1),
(5, 'Puri Indah', 'PRINDAH', 'Jakarta Barat', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', '-', '-', 'AKTIF', '2021-06-16 09:48:34', '2021-06-16 09:48:34', 5, 5, 5);

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
-- Dumping data for table `mstr_customer`
--

INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Bpk purwanto', '', '', '', '', '', '', '', '', '081314255457', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(2, 'yo', '123', 'noimage.jpg', 'noimage.jpg', 'Toko', '123', 'Tn', 'PD Lestari', 'yo@pdlestari.com', '123', '123', '123', '-', 1, 'aktif', '2020-07-29 12:15:02', '2021-06-09 09:03:53', 1, 5),
(3, 'Bpk Hary', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(4, '', '', '', '', '', '', '', 'PT.SAB', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(5, 'Bpk H.Muslimin', '', '', '', '', '', '', '', '', '082123239933/081223239933', '', 'PT.Anugerah Perdana Teweh', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(6, '', '', '', '', '', '', '', 'PT.lensa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(7, 'Bpk Wiendra', '', '', '', '', '', '', '', '', '', '', 'PT. EMP', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(8, 'Bpk Nurdin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(9, '', '', '', '', '', '', '', 'Manunggal jaya teknik', '', '', '', 'UG ', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(10, '', '', '', '', '', '', '', 'cv. rachmat', '', '', '', 'Sulawesi selatan ', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(11, 'Bpk Eka saputri burhanuddin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(12, 'Bpk Rahmat', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(13, 'Bpk Misran', '', '', '', '', '', '', 'PT.Indo Maritim Pratama', '', '085264907778', '', 'Ruko Telaga Tujuh No.12\r\nKolong,Tg Balai Karimun\r\nKepri', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(14, 'Bpk Wahyu Herry', '', '', '', '', '', '', 'PT.Adhi Karya (Persero) tbk', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(15, 'Bpk Yurnato', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(16, 'Bpk Adi', '', '', '', '', '', '', '', '', '081210663567', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(17, 'Valeria', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(18, 'Bpk Yerno', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(19, 'Bpk Gumaka', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(20, 'Bpk Alfan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(21, 'Bpk Budi', '', '', '', '', '', '', '', '', '081290947382', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(22, 'Bpk Deden', '', '', '', '', '', '', 'TBINA', '', '081213784558', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(23, 'Bpk Deni', '', '', '', '', '', '', 'PT. Tribuana Gasindo', '', '081319202037', '', 'PT. Tribuana Gasindo ', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(24, 'Bpk Tanto', '', '', '', '', '', '', 'PT. Teknik Alum Service', '', '08119747474', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(25, 'Bpk Ismanto', '', '', '', '', '', '', 'PT. Ega Meckinka', '', '081298559802', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(26, 'Bpk Adrian', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(27, 'Bpk Lucky', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(28, 'Bpk Erlist', '', '', '', '', '', '', '', '', '', '', 'PT. PLN (persero) upp kitsum 7', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(29, 'bpk Andi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(30, 'bpk Ardi', '', '', '', '', '', '', 'PT.Megatama Abadi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(31, 'bpk Ahmad', '', '', '', '', '', '', 'SEMI', '', '081213333701', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(32, 'bpk Shodiq', '', '', '', '', '', '', 'PT.prosys', '', '', '', '', 'Bordir nama 10.000\r\nBordir 35.000\r\nnaik size 10.000\r\nwp 140.000\r\nBc 150.000', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(33, 'Ibu Anis', '', '', '', '', '', '', 'PT. Global Maritim', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(34, 'Bpk Heru', '', '', '', '', '', '', '', '', '081381318350', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(35, '', '', '', '', '', '', '', 'Jaya Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(36, 'Ibu Isah', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(37, 'Bpk sandy', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(38, 'Bpk Idris', '', '', '', '', '', '', 'PT.AC Global Teknindo', '', '082114139349', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(39, '', '', '', '', '', '', '', 'Jaya Abadi', '', '', '', 'GF 2', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(40, 'Bpk Kikim', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(41, 'Bpk Erwin', '', '', '', '', '', '', '', '', '', '', '', 'nomex cotton', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(42, 'Bpk Adi', '', '', '', '', '', '', '', '', '081299269297', '', '', 'Rompi 4 kantong merah ', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(43, 'My Safety', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(44, 'bpk Tumpal', '', '', '', '', '', '', 'PT.Ragat Saran Teknik (ERESTE)', '', '08158967022', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(45, 'bpk Hardi Wijaya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(46, 'Triono', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(47, 'bpk edi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(48, 'Bpk Anwar', '', '', '', '', '', '', 'PT.EMC', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(49, 'Bpk Heru', '', '', '', '', '', '', 'PT.Total Pola', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(50, 'bpk Samuel', '', '', '', '', '', '', 'bali', '', '081389848988', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(51, 'bpk Febri', '', '', '', '', '', '', '', '', '089606113515', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(52, 'bpk Yunus', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(53, 'Bpk Helmi', '', '', '', '', '', '', '', '', '081328008161', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(54, 'Bpk Pranoko', '', '', '', '', '', '', '', '', '081222104646', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(55, 'Bpk Toto', '', '', '', '', '', '', '', '', '021-29298686', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(56, 'Bpk Agil', '', '', '', '', '', '', 'PT.Nittoc Construction Indonesia', '', '0818428777 / 081328431911(wa)', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(57, 'Bpk Joko', '', '', '', '', '', '', 'PT. Petrotekno', '', '081381872118', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(58, 'Bpk Wendra', '', '', '', '', '', '', 'PT.Nindya Karya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(59, 'Bpk Samsul', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(60, 'Bpk Nyubik Ismanto', '', '', '', '', '', '', 'PT. tunas karya aditama', '', '081317223551', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(61, 'Bpk Joni', '', '', '', '', '', '', '', '', '081212335070', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(62, 'Bpk Wahab', '', '', '', '', '', '', '', '', '085773012256', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(63, 'Bpk Widodo', '', '', '', '', '', '', '', '', '0857148088', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(64, 'Bpk Majen', '', '', '', '', '', '', '', '', '08174997877', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(65, 'Bpk Wawan', '', '', '', '', '', '', 'adhi karya', '', '081317772860', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(66, 'Bpk Hari', '', '', '', '', '', '', '', '', '085214365775', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(67, 'Bpk Triono', '', '', '', '', '', '', '', '', '081317582548', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(68, 'Bpk Achmad Khoiri Hidayat', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(69, 'Bpk Tangguh Primandaru', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(70, 'Bpk Gigix', '', '', '', '', '', '', '', '', '081291090809', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(71, 'Bpk Bayu', '', '', '', '', '', '', 'PT. Psi', '', '081212398064', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(72, 'Bpk Wahyu', '', '', '', '', '', '', '', '', '081284117781', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(73, 'Bpk Somantri', '', '', '', '', '', '', 'PT. Ara Samudra', '', '081380516656', '', 'Jl.Bukit Gading Raya\r\nKomplek Gading Bukit Indah blok RB No 6\r\nkelapa gading,jakarta utara', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(74, '', '', '', '', '', '', '', 'PT. Langgeng Mandiri', '', '082111297779', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(75, 'Bpk Ober', '', '', '', '', '', '', '', '', '08129294644', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(76, 'Bpk AIP', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(77, 'Bpk Nanang', '', '', '', '', '', '', 'PT. DRU', '', '081210779224', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(78, 'Bpk Arianto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(79, 'Bpk Handoko', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(80, 'Bpk Iyon', '', '', '', '', '', '', '', '', '081382301537', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(81, 'Bpk Rudy', '', '', '', '', '', '', '', '', '081331821618', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(82, 'Bpk Fuad', '', '', '', '', '', '', '', '', '08128831951', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(83, 'Bpk Eric', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(84, '', '', '', '', '', '', '', 'PT.Grand Kartel', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(85, 'Bpk Erwinn', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(86, 'Bpk Aming', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(87, 'Bpk Setiady', '', '', '', '', '', '', 'PT.Toto Indonesia', '', '081281358989', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(88, 'Bpk Erijal', '', '', '', '', '', '', '', '', '081383923939', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(89, 'Bpk Komarudin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(90, 'Bpk Aan', '', '', '', '', '', '', '', '', '081585090659', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(91, 'Bpk Toro', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(92, 'Bpk Wardi', '', '', '', '', '', '', '', '', '081250939150', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(93, 'Bpk Cahyo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(94, 'Bpk Alfian', '', '', '', '', '', '', '', '', '089992110646', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(95, 'Bpk Arjani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(96, 'Bpk Rojak', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(97, '', '', '', '', '', '', '', 'PT. Multi prima', '', '66690113', '', '', '08997804212\r\n', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(98, 'Bpk Harris', '', '', '', '', '', '', '', '', '085726416999', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(99, 'Bpk Dwi', '', '', '', '', '', '', '', '', '081284221134', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(100, 'Bpk.Fadil', '', '', '', '', '', '', '', '', '082311202426', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(101, 'Ibu Dwi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(102, 'Bpk Marsal', '', '', '', '', '', '', '', '', '081383774024', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(103, 'Bpk Amir', '', '', '', '', '', '', '', '', '085776256889', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(104, 'Bpk Agus', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(105, 'Bpk Parman', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(106, 'bPK Eki', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(107, 'Bpk Syaiful', '', '', '', '', '', '', '', '', '081210282386', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(108, 'Bpk Thomas', '', '', '', '', '', '', '', '', '081249547494', '', '', 'PAPUA', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(109, 'Bpk Sutan Aziz', '', '', '', '', '', '', '', '', '081226906854', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(110, 'Bpk Hendra', '', '', '', '', '', '', 'PT.MMI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(111, 'Bpk Tiyo', '', '', '', '', '', '', '', '', '081212595569', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(112, 'Bpk Tikno', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(113, 'Bpk Chandra', '', '', '', '', '', '', '', '', '081298956790', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(114, 'Bpk Fahrul', '', '', '', '', '', '', '', '', '087777081920', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(115, '', '', '', '', '', '', '', 'PT.Panasonic', '', '081321221979', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(116, '', '', '', '', '', '', '', 'PT.Karya Guna', '', '081316361860', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(117, 'Bpk Ami', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(118, 'Bpk Nanda', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(119, 'Bpk Rhido', '', '', '', '', '', '', '', '', '081295776707', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(120, 'Bpk Asep', '', '', '', '', '', '', 'PT. anugrah analisis sempurna', '', '081297353348', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(121, 'Bpk Caca', '', '', '', '', '', '', '', '', '0811103510', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(122, 'Bpk David', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(123, 'Bpk Samuel joevan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(124, '', '', '', '', '', '', '', 'Indonesia river engineering', '', '', '', '', 'china', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(125, 'Bpk Hendri', '', '', '', '', '', '', 'PT. master pancana', '', '085714648290', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(126, 'Bpk Rizky', '', '', '', '', '', '', '', '', '08158093730', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(127, 'Bpk Hendra', '', '', '', '', '', '', '', '', '081310288206', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(128, 'Bpk Komar', '', '', '', '', '', '', 'PT. sarinah', '', '08119001319', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(129, 'Bpk Anda', '', '', '', '', '', '', '', '', '087882544015', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(130, 'Bpk Robert', '', '', '', '', '', '', '', '', '081213575999', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(131, 'Bpk David Hou', '', '', '', '', '', '', 'PT.Karya Bayu Abadi', '', '081210505836', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(132, 'Bpk Dion', '', '', '', '', '', '', 'PT. surveyor indonesia', '', '081382301537', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(133, 'Bpk Aguang', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(134, 'Bpk Ramadhan', '', '', '', '', '', '', '', '', '082247545457', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(135, 'Bpk Indra', '', '', '', '', '', '', '', '', '081282932044', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(136, 'Bpk Zainal', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(137, 'Bpk Imron', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(138, 'Bpk Arif', '', '', '', '', '', '', '', '', '081285539066', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(139, 'Bpk Rohmat', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(140, 'Ibu Novi', '', '', '', '', '', '', '', '', '085777743301', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(141, 'Bpk Aldi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(142, 'Bpk Duha', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(143, 'Bpk Maringan', '', '', '', '', '', '', '', '', '08128710826', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(144, 'Bpk Ahmad murnir', '', '', '', '', '', '', 'PT.Prima Sentosa Abadi', '', '081381939288', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(145, 'Bpk iqbal', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(146, 'Bpk Surya', '', '', '', '', '', '', '', '', '081287807977', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(147, '', '', '', '', '', '', '', 'PT. Digitalindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(148, 'Bpk Aboi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(149, 'Ibu Novita', '', '', '', '', '', '', '', '', '08179927162', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(150, 'Bpk Iwan', '', '', '', '', '', '', '', '', '082126664149', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(151, 'Bpk Yanto', '', '', '', '', '', '', 'PT.kalimas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(152, 'Bpk andri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(153, 'Ibu Eflin', '', '', '', '', '', '', '', '', '081222200858', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(154, 'Bpk Erdhi', '', '', '', '', '', '', '', '', '08211338808', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(155, 'Bpk Hobik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(156, 'Bpk Hakim', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(157, 'Ibu Anna', '', '', '', '', '', '', '', '', '082113427823', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(158, 'Bpk Aven', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(159, 'Bpk Geral', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(160, 'Bpk Anton', '', '', '', '', '', '', 'PT.Madia Kreasi Perdana', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(161, 'Bpk Yusuf', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(162, 'Bpk Edward', '', '', '', '', '', '', '', '', '081321750742', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(163, '', '', '', '', '', '', '', 'Sim Brother', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(164, 'Bpk Feri', '', '', '', '', '', '', 'PT. BKI', '', '081241234440', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(165, 'Bpk Roy', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(166, 'Bpk Rendra', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(167, 'Bpk Edi', '', '', '', '', '', '', 'PT. Kalindo Teknik', '', '081384619099', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(168, 'Bpk Dani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(169, 'Bpk Restu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(170, '', '', '', '', '', '', '', 'lengkap teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(171, 'Bpk Hendro', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(172, 'Bpk Hendro', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(173, 'Bpk Hendi candra', '', '', '', '', '', '', '', '', '081281226542', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(174, '', '', '', '', '', '', '', 'PT. Sarana global indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(175, '', '', '', '', '', '', '', 'CV. Delta Teknologi', '', '024-76671147', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(176, 'Jaya Mas HWI', '', '', '', '', '', '', '', '', '6242601', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(177, 'Bpk Ato', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(178, '', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(179, '', '', '', '', '', '', '', 'PT. PPS', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(180, 'Bpk malik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(181, 'Bpk Asep Prana Purwadi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(182, 'Bpk Erik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(183, 'Bpk Putra', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(184, 'Bpk Parjo', '', '', '', '', '', '', '', '', '082113000243', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(185, 'Bpk wahyuu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(186, 'Bpk Marif', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(187, 'Bpk Ipung', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(188, 'Ibu Meyta', '', '', '', '', '', '', '', '', '08157777191', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(189, '', '', '', '', '', '', '', 'PT.Bhineka avian Service', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(190, '', '', '', '', '', '', '', 'PT.Mega Abadi Indonesia', '', '082169000808', '', '', '081287828889', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(191, '', '', '', '', '', '', '', 'PT.Arga Pura', '', '081217583594', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(192, 'Bpk Tommy', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(193, 'Bpk Sedo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(194, 'Bpk Yono', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(195, 'Bpk Hendri', '', '', '', '', '', '', '', '', '081283141945', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(196, 'Bpk daniel', '', '', '', '', '', '', 'Multi co', '', '081314485754', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(197, 'Bpk Agus (n)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(198, 'Bpk Zaenal Abidin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(199, 'Bpk Yoga', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(200, 'Bpk Andri', '', '', '', '', '', '', 'DPPU Banjarmasin', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(201, 'Bpk Pandu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(202, 'Bpk M. yatim', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(203, 'Bpk Reno', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(204, 'Bpk Anton', '', '', '', '', '', '', 'centralink', '', '08164611624', '', '', 'langsung bordir ke baju ', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(205, 'Bpk. Renaldi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(206, '', '', '', '', '', '', '', 'PT. Karunia', '', '3150605', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(207, 'Bpk. Togap', '', '', '', '', '', '', '', '', '081281797697', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(208, 'Bpk. Junaedi', '', '', '', '', '', '', 'Toko wiltec', '', '0216260811', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(209, 'ko alvin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(210, 'Bpk Dimas', '', '', '', '', '', '', '', '', '085711126388', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(211, '', '', '', '', '', '', '', 'Sumber Teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(212, 'bpk herman', '', '', '', '', '', '', '', '', '087808074803', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(213, 'Bpk Trifa', '', '', '', '', '', '', '', '', '081289815735', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(214, '', '', '', '', '', '', '', 'PT.Matrix', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(215, '', '', '', '', '', '', '', 'PT.Eco system Internasional', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(216, '', '', '', '', '', '', '', 'PT.Tugu mas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(217, 'Bpk Agi Negara', '', '', '', '', '', '', 'PT.Fatiha Alam Semesta', '', '081296800184', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(218, 'Bpk dili', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(219, 'Bpk hendraa', '', '', '', '', '', '', '', '', '0817776404', '', '', '', 1, 'aktif', '2020-07-29 12:15:02', '2020-07-29 12:15:02', 1, 1),
(220, 'Bpk Taufiq', '', '', '', '', '', '', 'PT. Gaspro', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(221, '', '', '', '', '', '', '', 'PT.Multindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(222, '', '', '', '', '', '', '', 'PT.Shenyangyuanda.a.i.e', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(223, 'Bpk Stanly', '', '', '', '', '', '', '', '', '08692611364', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(224, 'Bpk Yudi', '', '', '', '', '', '', '', '', '081291130580', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(225, 'Mr. Liu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(226, '', '', '', '', '', '', '', 'PT. Setra Sari', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(227, 'Bpk Panca', '', '', '', '', '', '', 'PT. Anagata Visi Teknologi', '', '0811862239', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(228, '', '', '', '', '', '', '', 'CV.Rizqi Tirtamas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(229, '', '', '', '', '', '', '', 'PT.Ambe', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(230, 'Bpk Isman', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(231, '', '', '', '', '', '', '', 'YAP makmur', '', '', '', '', 'orang toko', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(232, 'Bpk Widiya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(233, 'Bpk Gunawan', '', '', '', '', '', '', 'Toko Indo Safety', '', '', '', 'Toko Indo Safety\r\nJl.Raden Mattaher No.1\r\nJambi', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(234, '', '', '', '', '', '', '', 'PT. Mitra Adikarsa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(235, 'Bpk Eddy', '', '', '', '', '', '', '', '', '085781222628', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(236, 'Ibu Heni', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(237, 'Bpk. Richard', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(238, 'Bpk Arif', '', '', '', '', '', '', 'PT.Maritim Prima Mandiri', '', '', '', 'JL.Senopati Raya No.8B\r\nKebayoran Baru', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(239, 'Bpk Ryan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(240, 'Bpk Halim', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(241, 'Bpk Jimmy', '', '', '', '', '', '', '', '', '08128950063', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(242, 'Bpk. Emil', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(243, 'Bpk Rusdianto', '', '', '', '', '', '', 'SMK Manggar 1 Belitung', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(244, 'Ibu Anisa', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(245, 'Bpk Ibnu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(246, 'Bpk Devi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(247, '', '', '', '', '', '', '', 'PT. Visitek Asia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(248, 'Bpk Slamet', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(249, 'Bpk Agus (a)', '', '', '', '', '', '', '', '', '085694945365', '', '', 'pakai kacamata', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(250, 'Bpk Ridwan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(251, 'Bpk Fras', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(252, '', '', '', '', '', '', '', 'PT. Es Puga', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(253, 'Bpk Amul', '', '', '', '', '', '', '', '', '08122185476', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(254, 'Bpk Rio', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(255, '', '', '', '', '', '', '', 'PT. Rajawali Solusi Energindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(256, 'Ibu Olin', '', '', '', '', '', '', '', '', '081310202204', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(257, 'Bpk Pujiono', '', '', '', '', '', '', '', '', '082148129309', '', 'banjarmasin', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(258, 'AEFP', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(259, 'Bpk Ichwamudi prasetya', '', '', '', '', '', '', 'PT. surveyor indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(260, 'Bpk Aji', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(261, 'Bpk Ade', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(262, '', '', '', '', '', '', '', 'PT. Global', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(263, '', '', '', '', '', '', '', 'KGTM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(264, 'Bpk Najib', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(265, 'Bpk Suradi', '', '', '', '', '', '', 'PT. Fortuna', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(266, 'Bpk Anto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(267, 'Bpk Yuming', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(268, 'Bpk Sumarto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(269, 'Bpk Maman', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(270, 'Bpk Duwi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(271, 'Ibu Dede', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(272, 'Bpk Joko', '', '', '', '', '', '', 'CV. Radika Mitra Jaya', '', '081347343818', '', '', '087877420101', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(273, 'Bpk M. Sadri', '', '', '', '', '', '', '', '', '', '', 'bogor', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(274, '', '', '', '', '', '', '', 'pt. eskate energi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(275, 'Bpk Chandra', '', '', '', '', '', '', 'PT. ASDAR', '', '082220077838', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(276, 'Ibu Lani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(277, 'Bpk Erwin', '', '', '', '', '', '', 'PT. Armi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(278, 'Bpk Agus', '', '', '', '', '', '', 'cakra group', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(279, '', '', '', '', '', '', '', 'PT. MGS', '', '089605417015', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(280, 'Bpk Yusuf', '', '', '', '', '', '', 'Jakarta prima cranes', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(281, 'Bpk Teguh', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(282, 'Bpk Ely Wanto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(283, '', '', '', '', '', '', '', 'PT. Alfa Focus Indonesia', '', '081296095141', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(284, 'Bpk Sokip', '', '', '', '', '', '', '', '', '082125002007', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(285, 'Bpk Hendi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(286, 'Bpk Tommi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(287, '', '', '', '', '', '', '', 'PT.Sejahtera alam energy', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(288, '', '', '', '', '', '', '', 'PT.TEKMA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(289, '', '', '', '', '', '', '', 'PT.Sinar Surya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(290, 'Bpk Mario', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(291, '', '', '', '', '', '', '', 'PT. Inti Nusa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(292, '', '', '', '', '', '', '', 'PT. melconda', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(293, '', '', '', '', '', '', '', 'PT. kurnia fajar', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(294, 'Bpk Andien', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(295, '', '', '', '', '', '', '', 'PT. Maxiair Indosurya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(296, '', '', '', '', '', '', '', 'PT.MAXIAIR INDOSURYA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(297, 'Ibu Merry Lin', '', '', '', '', '', '', '', '', '08119800181', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(298, '', '', '', '', '', '', '', 'PT. karunia sinerji', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(299, 'Bpk Ramli', '', '', '', '', '', '', '', '', '08176972654', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(300, 'Bpk Wangun', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(301, 'Ibu Ina', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(302, 'Bpk didi', '', '', '', '', '', '', '', '', '089622031982', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(303, 'Ibu Erindang', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(304, 'Bpk Ahmad ramanda', '', '', '', '', '', '', 'PT. Treepark', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(305, 'Bpk Jumantri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(306, '', '', '', '', '', '', '', 'PT. Asiatik Buana Citra', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(307, '', '', '', '', '', '', '', 'PT. Anugrah Persada Alam', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(308, 'Ibu Marsya', '', '', '', '', '', '', 'PT. Valutrol', '', '081520425552', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(309, 'Bpk Sudijro', '', '', '', '', '', '', 'PT. Sinarindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(310, 'Bpk Christanto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(311, 'Bpk Arief', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(312, 'Bpk Abdullah', '', '', '', '', '', '', '', '', '085213575755', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(313, 'Bpk Dendi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(314, 'Bpk Temi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(315, 'Bpk Fauzi', '', '', '', '', '', '', '', '', '085216511851', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(316, 'Bpk Sasongko', '', '', '', '', '', '', '', '', '087812239136', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(317, '', '', '', '', '', '', '', 'PT. Karya Jenisto Putera', '', '081299357000', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(318, 'Bpk Nurul', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(319, '', '', '', '', '', '', '', 'PT.Hirose', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(320, '', '', '', '', '', '', '', 'Rs.Elisabet', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(321, '', '', '', '', '', '', '', 'Surya teknik', '', '', '', 'glodok jaya ', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(322, 'Bpk Robby', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(323, 'Bpk Katijan', '', '', '', '', '', '', '', '', '085218004995', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(324, '', '', '', '', '', '', '', 'toko boss', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(325, 'Bpk Kaoul', '', '', '', '', '', '', '', '', '081338133995', '', 'bekasi', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(326, 'Bpk Yatin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(327, '', '', '', '', '', '', '', 'PT.Sarinah', '', '081218594209', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(328, 'Bpk Hasdik Usman', '', '', '', '', '', '', 'Toko Bintang Sport', '', '087786172569/081389767836', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(329, 'Bpk Arfan', '', '', '', '', '', '', '', '', '085311578949', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(330, 'Bpk antony', '', '', '', '', '', '', 'strom', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(331, 'Bpk Pamuji', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(332, 'Bpk Jito', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(333, '', '', '', '', '', '', '', 'PT. Bali nirwana', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(334, 'Bpk Dedi', '', '', '', '', '', '', '', '', '081282017786', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(335, 'Bpk Arif (n)', '', '', '', '', '', '', '', '', '082185879212', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(336, 'Bpk. Sukamto', '', '', '', '', '', '', '', '', '08567037108', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(337, 'Bpk dwi', '', '', '', '', '', '', 'tokopedia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(338, 'Bpk Andry', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(339, 'Bpk Cecep', '', '', '', '', '', '', 'jaya konstruksi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(340, 'Bpk Suba', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(341, 'Ibu Santi', '', '', '', '', '', '', 'MD Printing', '', '087878975711', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(342, 'Bu Lena', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(343, '', '', '', '', '', '', '', 'PT. Data Energy Infomedia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(344, '', '', '', '', '', '', '', 'PT. Adi Sejahtera', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(345, '', '', '', '', '', '', '', 'PT. Alta', '', '081219387015', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(346, 'Bpk Yohan', '', '', '', '', '', '', 'PT. Agrindo Prima Lestari', '', '0812200025313', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(347, 'Ibu Riana', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(348, 'Ibu Fisenna', '', '', '', '', '', '', 'PT. Bhineka mentari dimensi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(349, 'Bpk. Uki', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(350, 'Bpk. Acong', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(351, 'caltex', '', '', '', '', '', '', 'PT. Bintang Sanpillar Artha', '', '02129032328/08117204298', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(352, 'Bpk rodi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(353, '', '', '', '', '', '', '', 'Water Land', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(354, 'Bpk Koprianto', '', '', '', '', '', '', 'surya anshor yanindo', '', '', '', 'ltc glodok SB ', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(355, '', '', '', '', '', '', '', 'Mega Jaya', '', '', '', 'GF2', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(356, 'Bpk. Tri Cahyono', '', '', '', '', '', '', '', '', '08131090377', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(357, 'Bpk Ari mukri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(358, '', '', '', '', '', '', '', 'PT. bina bangun wibawa mukti', '', '081316691799', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(359, '', '', '', '', '', '', '', 'Bulog', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(360, 'Bpk Maksi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(361, 'Ibu Ita', '', '', '', '', '', '', '', '', '08118044235', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(362, 'Bpk Caksono', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(363, '', '', '', '', '', '', '', 'PT. Sumber Karya Berlimpah', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(364, 'Bpk Midi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(365, 'Bpk Kharisna', '', '', '', '', '', '', 'PT.Komatsu', '', '085646474880', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(366, 'Bpk Yusup', '', '', '', '', '', '', '', '', '081392450460', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(367, '', '', '', '', '', '', '', 'PT. Rukindo', '', '085810783570', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(368, '', '', '', '', '', '', '', 'PT.JHS', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(369, '', '', '', '', '', '', '', 'Pak Wilton', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(370, 'Bpk. Bubun', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(371, 'Bpk. Bima', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(372, 'Ibu eli', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(373, '', '', '', '', '', '', '', 'PT. Samudra jaya raya', '', '087854006320', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(374, 'Bpk Selamet', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(375, '', '', '', '', '', '', '', 'PT. albatros sarana teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(376, 'Bpk Sukardi', '', '', '', '', '', '', 'karawang', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(377, '', '', '', '', '', '', '', 'PT.Densa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(378, 'Ibu Ingrit', '', '', '', '', '', '', '', '', '081295101188', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(379, 'Ibu Erni', '', '', '', '', '', '', '', '', '085691380069', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(380, '', '', '', '', '', '', '', 'PT. Mitra Sempurna Abadi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(381, 'Ibu Dini', '', '', '', '', '', '', 'JL Production', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(382, 'Bpk Trismardani', '', '', '', '', '', '', '', '', '081314786615', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(383, '', '', '', '', '', '', '', 'PT.Mitsu Sinar Teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(384, 'Ibu Ani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(385, '', '', '', '', '', '', '', 'Suku Dinas Ketahanan Pangan, Kelautan, dan Pertanian Kota Administrasi Jakarta Barat', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(386, 'Bpk. Ali', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(387, 'Bu meike', '', '', '', '', '', '', 'CV. Pratama Abadi Jaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(388, 'Bpk bisma', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(389, '', '', '', '', '', '', '', 'PT. Sinar Lentera Kencana', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(390, 'Bpk. danil', '', '', '', '', '', '', 'PT. Budi Jaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(391, 'Bpk Dede', '', '', '', '', '', '', 'PT. Bina Nusa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(392, '', '', '', '', '', '', '', 'PT. Patra Dinamika', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(393, '', '', '', '', '', '', '', 'PT. Medikon', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(394, '', '', '', '', '', '', '', 'PT. UAJ', '', '081297459848', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(395, 'Bpk omin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(396, 'Ibu fitri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(397, 'Bpk raras', '', '', '', '', '', '', '', '', '085213664251', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(398, 'Bpk Makbul', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(399, 'Ibu Sari', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(400, 'Bpk Eric Siahaan', '', '', '', '', '', '', 'PT. Esa Krida Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(401, 'Ibu Julia', '', '', '', '', '', '', 'Manado', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(402, '', '', '', '', '', '', '', 'PT. Mandiri Multi Adijaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(403, 'Bpk. Haji Sutrisno', '', '', '', '', '', '', '', '', '087882357207', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(404, 'Bpk Rido', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(405, '', '', '', '', '', '', '', 'Prodi Teknik Kimia Unpar', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(406, '', '', '', '', '', '', '', 'PT. Foamindo', '', '081398854053', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(407, 'Bpk Riski', '', '', '', '', '', '', '', '', '082132784095', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(408, 'Ibu Dahme', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(409, 'Bpk H. Haryadi', '', '', '', '', '', '', '', '', '081806568757', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(410, '', '', '', '', '', '', '', 'PT. Tampomas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(411, 'Bpk Puguh', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(412, 'Bpk Sudirman', '', '', '', '', '', '', '', '', '085395894072', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(413, 'Bpk. Yunarto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(414, 'Bpk. Sugi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(415, 'Bpk. Fandy', '', '', '', '', '', '', 'PT. William Jaya Sentosa', '', '02162317384', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(416, 'Bpk. Suwinyo', '', '', '', '', '', '', '', '', '08129693569', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(417, '', '', '', '', '', '', '', 'PRO safety', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(418, 'Bpk. Lisahto', '', '', '', '', '', '', '', '', '08128013696', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(419, 'Bpk Didik Chandra', '', '', '', '', '', '', 'PT.Petronesia Benimel', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(420, 'Bpk. Indra', '', '', '', '', '', '', 'Cilegon', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(421, 'Bpk antoo', '', '', '', '', '', '', '', '', '08129029906', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(422, 'Bpk Agus Budi', '', '', '', '', '', '', 'PT.Mukti Rejo Abadi', '', '08111127760', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(423, '', '', '', '', '', '', '', 'PT. Jimatara', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(424, 'Bpk Lukman', '', '', '', '', '', '', '', '', '0818920505', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(425, 'Bpk Malvin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(426, 'Bpk. Aries Mulyadi', '', '', '', '', '', '', '', '', '0852-9060-5453', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(427, '', '', '', '', '', '', '', 'PT.Berca Hardayaperkasa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(428, '', '', '', '', '', '', '', 'PT. EDU', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(429, 'Bpk Yeriko', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(430, 'Bpk  Jainudin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(431, 'Bpk. Setyo Wiarto', '', '', '', '', '', '', '', '', '081289863367', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(432, 'Bpk Heru', '', '', '', '', '', '', 'Surabaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(433, 'Bpk. Deny', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(434, 'Bpk. Hendra', '', '', '', '', '', '', 'PT. Hida Daerah Baru', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(435, '', '', '', '', '', '', '', 'PT. DRACO INTERNATIONAL', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:03', '2020-07-29 12:15:03', 1, 1),
(436, 'Bpk. Hasan', '', '', '', '', '', '', 'PT. Multi Prima Indo Sejahtera', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(437, '', '', '', '', '', '', '', 'PT. Karang Kumaritis', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(438, 'Bpk. Roni', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(439, 'Bpk. Budiyanto', '', '', '', '', '', '', 'PT. DS', '', '08159115799', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(440, '', '', '', '', '', '', '', 'PT. Mitra Selaras', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(441, 'Bpk. Heri', '', '', '', '', '', '', '', '', '087741499145', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(442, '', '', '', '', '', '', '', 'PT. INDOVISUAL', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(443, 'Bpk. Agung', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(444, 'Bpk. Yusak', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(445, 'Bpk. Bari', '', '', '', '', '', '', 'PT. Tribuana Gasindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(446, 'Bpk. Usef', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(447, 'Bpk. Teguh', '', '', '', '', '', '', 'cikarang', '', '', '', 'workshop', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(448, '', '', '', '', '', '', '', 'PT. Yakin Karya Kencana', '', '085100449777', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(449, 'Bpk. Ganda', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(450, 'Bpk. Rusdianto', '', '', '', '', '', '', 'Manado', '', '081299254025', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(451, 'Bpk Rizky', '', '', '', '', '', '', 'hakaaston', '', '082226422247', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(452, '', '', '', '', '', '', '', 'PT. Mahakarya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(453, '', '', '', '', '', '', '', 'Mobilekom', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(454, '', '', '', '', '', '', '', 'Berkat Jaya Electronic', '', '', '', 'Lantai 1 blok c32 no 17 ', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(455, 'Bpk Teddy', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(456, '', '', '', '', '', '', '', 'Bina cahaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(457, '', '', '', '', '', '', '', 'PT. SMS', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(458, 'Bpk. Yasir', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(459, '', '', '', '', '', '', '', 'Catur Mitra Teknologi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(460, 'Bpk. Agung', '', '', '', '', '', '', 'PT. Fabrindo Asti Guna', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(461, 'Bpk.Andi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(462, 'Alfa One', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(463, 'Bpk. Toni Sabbarudin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(464, 'Bpk. Hendra', '', '', '', '', '', '', 'Geo Service', '', '081389493191 / 081315888911', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(465, '', '', '', '', '', '', '', 'PT.Precision Tools Service Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(466, 'Bpk Salvalor', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(467, 'Bpk. Ahmad', '', '', '', '', '', '', 'ambon', '', '081240789444', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(468, 'Bpk. Yuda', '', '', '', '', '', '', '', '', '081286155647', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(469, '', '', '', '', '', '', '', 'PT. First Security Services Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(470, '', '', '', '', '', '', '', 'Toko matahari', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(471, '', '', '', '', '', '', '', 'PT. KTM', '', '083807141379', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(472, '', '', '', '', '', '', '', 'PT. ALTRAX', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(473, 'Bpk. Rudi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(474, 'Bpk Steven', '', '', '', '', '', '', '', '', '082114137181', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(475, '', '', '', '', '', '', '', 'PT. Loyal Jaya Energi', '', '081383247979', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(476, 'Bpk Donny', '', '', '', '', '', '', 'PT.Global Protectsindo', '', '', '', 'LTC Glodok', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(477, 'Bpk. Junaidi', '', '', '', '', '', '', '', '', '081311310488', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(478, 'Bpk. Nicholas', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(479, 'Ibu Siti Aminah', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(480, 'Bpk. Daniel', '', '', '', '', '', '', 'PT. Anugerah', '', '087877717172', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(481, 'Bpk. Wawan', '', '', '', '', '', '', 'NINDYA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(482, 'Bpk. Hari', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(483, 'Bpk Abidin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(484, 'Bpk. Hadi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(485, 'Bpk. Ali Mukri', '', '', '', '', '', '', 'PT. Akma Jaya Kontruksi', '', '081377315889', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(486, 'Ibu Rianti', '', '', '', '', '', '', '', '', '087784972881', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(487, '', '', '', '', '', '', '', 'Balai Penelitian Ternak', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(488, '', '', '', '', '', '', '', 'CV. Dirgantara Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(489, 'Bpk. Eko Suhardi', '', '', '', '', '', '', '', '', '085778630090', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(490, '', '', '', '', '', '', '', 'PT. Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(491, 'CV. Trimitra Indonesia', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(492, '', '', '', '', '', '', '', 'PT. Beton Perkasa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(493, 'Bpk. Juni Ihwanda', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(494, 'Bpk. Herlan', '', '', '', '', '', '', '', '', '081294403313', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(495, 'Bpk. Masudi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(496, 'Bpk. Suprato', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(497, 'Ibu Sylvia', '', '', '', '', '', '', 'Semarang', '', '08164883115', '', 'Ruko THD Blok B no 24\r\nJln.KH Agus Salim\r\nSemarang', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(498, '', '', '', '', '', '', '', 'PT. Samudra', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(499, 'Bpk Sutrisna', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(500, 'Bpk. Yuni', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(501, 'Bpk. Maricus', '', '', '', '', '', '', '', '', '081286675715', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(502, '', '', '', '', '', '', '', 'PT. Multikarya Asia Pasifik Raya', 'imas@mkapr.co.id', '', '', 'LONDON CENTER BLOK M-3A\r\nJL.LODAN RAYA NO.2 ANCOL, JAKARTA UTARA\r\n', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(503, '', '', '', '', '', '', '', 'PT. Lim', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(504, 'Ibu Dian', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(505, 'Bpk. Manel', '', '', '', '', '', '', '', '', '085385776477', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(506, 'Mega star', '', '', '', '', '', '', 'glodok jaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(507, 'Ibu mery', '', '', '', '', '', '', 'gosyen', '', '081806542412', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(508, '', '', '', '', '', '', '', 'PT. PR ROLLS INDONESIA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(509, 'Bpk Aimam', '', '', '', '', '', '', '', '', '08986543184', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(510, 'Bpk Roger', '', '', '', '', '', '', 'Toko pilar mas senin', '', '', '', 'senen', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(511, '', '', '', '', '', '', '', 'zen teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(512, '', '', '', '', '', '', '', 'saga makmur', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(513, 'Bpk Tri Mulyanto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(514, '', '', '', '', '', '', '', 'PT. MHI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(515, 'Bpk Joko', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(516, '', '', '', '', '', '', '', 'Safety Mart Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(517, 'Bpk andy kurniawan', '', '', '', '', '', '', '', '', '08170025888', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(518, '', '', '', '', '', '', '', 'link safety', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(519, 'Bpk Michael', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(520, 'Bpk Soleh', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(521, '', '', '', '', '', '', '', 'Bie Store', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(522, 'Bpk Yohanes', '', '', '', '', '', '', '', '', '0818605750', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(523, '', '', '', '', '', '', '', 'Sinarmas', '', '(021) 6230 3275 72, 0816 4803 ', '', 'LTC Lt. GF2 Blok A21 No. 2-3-5', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(524, '', '', '', '', '', '', '', 'Boss', '', '0819 0512 5584, 0856 1555 125', '', 'LTC Lt. GF2 Pameran Blok A No.35', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(525, '', '', '', '', '', '', '', 'HDR', '', '+6221 2607 1143, 0812 9571 727', '', 'LTC Lt.1 Blok B23 No.1', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(526, 'Zen Raharja', '', '', '', '', '', '', 'Zen Teknik', '', '0878 7736 8226', '', 'Lt. GF 1 A28  Pameran', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(527, 'thomas k', '', '', '', '', '', '', 'Prima Dinamika', '', '0817 6779 530 / 021 62320435', '', 'ltc sb bliok c.1 no.16', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(528, '', '', '', '', '', '', '', 'Sinar Sejahtera Jaya (SSJ)', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(529, '', '', '', '', '', '', '', 'Mitratama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(530, 'joko rahmad', '', '', '', '', '', '', 'Anugrah Sumber Sejati  (ASS)', '', '0818 6621 57, 08161135507, 081', '', 'ltc lt 2 blok b.16 no.7-8 ', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(531, '', '', '', '', '', '', '', 'GSS', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(532, 'Kogun', '', '', '', '', '', '', 'PD Lestari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(533, '', '', '', '', '', '', '', 'Karya Mitra Gemilang (KMG)', '', '+62-21 62320737-38, 62303539', '', 'LTC Lt. UG Blok C30 No.2-3', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(534, '', '', '', '', '', '', '', 'Link safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(535, '', '', '', '', '', '', '', 'Ratu Safety Indonesia', '', '021-62320720', '', 'GF 2 BLOK B16 NO 2', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(536, '', '', '', '', '', '', '', 'MMP', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(537, '', '', '', '', '', '', '', 'My Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(538, '', '', '', '', '', '', '', 'Mitra Safety', '', '021 6230 3532, 0812 9188 3355', '', 'LTC Lt. GF1 Blok C7 No.9', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(539, '', '', '', '', '', '', '', 'Jack Anugrah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(540, '', '', '', '', '', '', '', 'Surya Mas', '', '087777566956, 081 6183 6183', '', 'LTC Lt. GF2 Blok B2 No. 127', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(541, '', '', '', '', '', '', '', 'Planet Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(542, '', '', '', '', '', '', '', 'Syariah Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(543, '', '', '', '', '', '', '', 'Sarana Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(544, '', '', '', '', '', '', '', 'SAFETY  123', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(545, '', '', '', '', '', '', '', 'Berkat Sarana Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(546, '', '', '', '', '', '', '', 'Baja Teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(547, 'shilva dwi', '', '', '', '', '', '', 'Safety Mart Indonesia', '', '021 2607 1186, 0821 8596 6316', '', 'ltc gf.1 blok b.10 no.1', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(548, 'wini', '', '', '', '', '', '', 'Satria Safety', '', '0822 1000 9186', '', 'LTC Lantai 1 Blok B1 No. 20', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(549, '', '', '', '', '', '', '', 'Sumber Mas Teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(550, '', '', '', '', '', '', '', 'Toko Utama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(551, '', '', '', '', '', '', '', 'Bens Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(552, '', '', '', '', '', '', '', 'Putratama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(553, '', '', '', '', '', '', '', 'Bina Cahaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(554, 'Ci Emi', '', '', '', '', '', '', 'Green Safety', '', '62201068', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(555, '', '', '', '', '', '', '', 'Golden Safety', '', '021 6231 1065, 021 6230 5557', '', 'LTC Lt. GF 1 Blok A2 No.5', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(556, '', '', '', '', '', '', '', 'Lestari Indah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(557, 'Sri', '', '', '', '', '', '', 'Maju Jaya Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(558, 'susi', '', '', '', '', '', '', 'Lestari Saftindo', '', '0853 6946 1209', '', 'ltc lt 2 blok c.1 no.2', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(559, '', '', '', '', '', '', '', 'Aneka Sarana Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(560, '', '', '', '', '', '', '', 'PT. Swakarsa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(561, '', '', '', '', '', '', '', 'SMT/KMG', '', '021 62310904 / 05', '', 'LTC LT. 2 BLOK C 9 NO. 9', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(562, '', '', '', '', '', '', '', 'Karunia Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(563, 'Bpk Fery', '', '', '', '', '', '', '', '', '08121057886', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(564, 'Bpk Rudy', '', '', '', '', '', '', 'triartha', '', '081210934957', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(565, 'Ibu vera', '', '', '', '', '', '', '', '', '081288152292', '', '', '0811868467', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(566, 'Bpk Nardi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(567, 'vatri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(568, '', '', '', '', '', '', '', 'PT. PLN Bandengan', '', '08979542842', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(569, '', '', '', '', '', '', '', 'ACE Jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(570, '', '', '', '', '', '', '', 'Matahari Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(571, 'Bpk Teguh', '', '', '', '', '', '', 'Teguh Safety', '', '', '', 'Senen', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(572, 'Bpk dede', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(573, 'Ibu Anty', '', '', '', '', '', '', 'PT. Synergy', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(574, 'Bpk Wahyu talia', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(575, '', '', '', '', '', '', '', 'Citrayasa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(576, '', '', '', '', '', '', '', 'Harapan Jaya Teknik', '', '021- 6232 0482, 6230 3525', '', 'ltc lt 1 c.30 no. 36', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(577, '', '', '', '', '', '', '', 'Ganda berkat usaha', '', '', '', 'lt gf1', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(578, 'Bpk Rifa\'i', '', '', '', '', '', '', '', '', '081293223233', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(579, '', '', '', '', '', '', '', 'Mulya Berkat Abadi', '', '085890301681', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(580, 'Bpk Joy', '', '', '', '', '', '', '', '', '082299577841', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(581, 'Ibu Sekar', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(582, 'Bu Dita', '', '', '', '', '', '', 'PT. Dalzon', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(583, 'Bpk Prastowo', '', '', '', '', '', '', '', '', '085867600688', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(584, '', '', '', '', '', '', '', 'bintang krakatau', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(585, '', '', '', '', '', '', '', 'mandiri prtama jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(586, '', '', '', '', '', '', '', 'pro teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(587, '', '', '', '', '', '', '', 'wisesa karya prima', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(588, '', '', '', '', '', '', '', 'cahaya mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(589, 'diana', '', '', '', '', '', '', 'Citrayasa Makmurindo', '', '0815 8561 0865, 0811 999 5628', '', 'ltc gf 1 blok .30 no.1', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(590, 'tjioe tjhauw lim', '', '', '', '', '', '', 'Sumber Teknik Abadi', '', '0812 1943 5685', '', 'ltc lt.2 blok c 30 no 32e ', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(591, 'carolina', '', '', '', '', '', '', 'Supplier Glodok', '', '0878 7556 4449', '', 'ltc lt 2 blok b7 no 1', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(592, '', '', '', '', '', '', '', 'Sumber Usaha Bersama (SUB)', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(593, 'Bpk eko', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(594, 'Bpk sulistio', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(595, 'Bpk mardiansiah', '', '', '', '', '', '', '', '', '081289491005', '', '', '081288999382', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(596, '', '', '', '', '', '', '', 'PT.Gading Serpong', '', '081288084142', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(597, 'Bpk Irul', '', '', '', '', '', '', '', '', '08117675053', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(598, '', '', '', '', '', '', '', 'Safety Jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(599, '', '', '', '', '', '', '', 'Sinar Abadi Mandiri', '', '(62-21) 62201357, 29611560', '', 'LTC Lt. GF2 Blok A 27 No.5-6', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(600, '', '', '', '', '', '', '', 'Taruna  Jaya teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(601, '', '', '', '', '', '', '', 'My Safety Wear', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(602, 'Bpk syelfian dwiputra', '', '', '', '', '', '', 'PT. Pralu Pratama Mandiri', '', '085310457758', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(603, 'Bpk saeful muharam', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(604, 'Ibu Gina', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(605, '', '', '', '', '', '', '', 'jkm', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(606, '', '', '', '', '', '', '', 'Putra Jaya Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(607, '', '', '', '', '', '', '', 'Animo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(608, '', '', '', '', '', '', '', 'Utama Jaya Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(609, 'Purwanto', '', '', '', '', '', '', 'PT. Syntegra', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(610, 'Bpk Affrul', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(611, '', '', '', '', '', '', '', 'Cobra Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(612, '', '', '', '', '', '', '', 'PD victoria indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(613, '', '', '', '', '', '', '', 'PT. SSI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(614, '', '', '', '', '', '', '', 'Sim Brother', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(615, '', '', '', '', '', '', '', 'satu safety indonesia', '', '021 62232 0720', '', 'lt gf 2 blok 16 no 2', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(616, '', '', '', '', '', '', '', 'PD. Saudara', '', '(+62)21- 29562536', '', 'LTC Lt. GF2 Blok B-16 No.8', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(617, '', '', '', '', '', '', '', 'cv.mitra tehnik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(618, '', '', '', '', '', '', '', 'PT. Harapan Utama', '', '021- 6231 7837, 6230 3292', '', 'LTC Lt. 2 Blok C8 no.6', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(619, '', '', '', '', '', '', '', 'Jaya Makmur', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(620, '', '', '', '', '', '', '', 'PT.samamantap sejahtra', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(621, '', '', '', '', '', '', '', 'PT.grandminindo anugrah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(622, '', '', '', '', '', '', '', 'enam delapan', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(623, '', '', '', '', '', '', '', 'faustino computer', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(624, '', '', '', '', '', '', '', 'PT. Juragan Kapal', '', '081396427730', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(625, 'Bpk Rizal (Senen)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(626, '', '', '', '', '', '', '', 'Bpk Parmono', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(627, 'Bpk Yudi', '', '', '', '', '', '', 'PT.SSR', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(628, 'Bpk. Rizal', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(629, 'cina fave hotel', '', '', '', '', '', '', '', '', '', '', '2 orang gede cowo ', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(630, 'Bpk iwa', '', '', '', '', '', '', 'bogor', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(631, 'Bpk agus bekasi', '', '', '', '', '', '', '', '', '082226704114', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(632, 'Bpk gorgon', '', '', '', '', '', '', '', '', '0817717110', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(633, 'Ibu A.Fitrianti', '', '', '', '', '', '', 'makasar', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(634, 'Olim / Jambi', '', '', '', '', '', '', 'Thermalindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(635, 'SUSI', '', '', '', '', '', '', 'Aim', '', '', '', '', 'PEMBAYARAN HARUS CASH', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(636, '', '', '', '', '', '', '', 'PT. KARYA PACIFIC SHIPPING', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(637, '', '', '', '', '', '', '', 'TOTAL SAFETY', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(638, '', '', '', '', '', '', '', '29 SAFETY', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(639, '', '', '', '', '', '', '', 'BERKAT MANDIRI', '', '021 6231 6916 - 6231 6922, 021', '', 'LTC Lt. GF 1 Blok B No.6\r\nLt. 1 Blok C No.3', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(640, '', '', '', '', '', '', '', 'BIONDI TEKNIK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(641, '', '', '', '', '', '', '', 'PT. MKE', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(642, 'Martin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(643, 'Bpk Ahmad Sahudin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(644, 'Bpk Wakijo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(645, '', '', '', '', '', '', '', 'PT. FL Logix', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(646, 'Ibu Mita', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(647, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM PROJECT TEAM SINOHYDRO SECTION 3', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(648, '', '', '', '', '', '', '', 'bintang matahari teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(649, '', '', '', '', '', '', '', 'RIVAL JAYA UTAMA, PT', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(650, '', '', '', '', '', '', '', 'rumah api', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(651, '', '', '', '', '', '', '', 'R.M.B', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(652, '', '', '', '', '', '', '', 'budy tiffani photo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(653, '', '', '', '', '', '', '', 'adovelin raharja', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(654, 'ci andrie', '', '', '', '', '', '', '', '', '62308067', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(655, '', '', '', '', '', '', '', 'dunia cakrawala', '', '021 2268 4520, 2268 6', '', 'ltc glodok gf 2 a 26 no 6-7', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(656, 'ci amoy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(657, '', '', '', '', '', '', '', 'pt dunia saftindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:04', '2020-07-29 12:15:04', 1, 1),
(658, '', '', '', '', '', '', '', 'kendari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(659, 'joshua', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(660, '', '', '', '', '', '', '', 'PT. Furukawa Indomobil', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(661, '', '', '', '', '', '', '', 'PT. Sinoma Engineering Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(662, 'bu mei', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(663, 'ricky', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(664, '', '', '', '', '', '', '', 'pt surya karsa mediaformasindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(665, '', '', '', '', '', '', '', 'mitsubishi corporation muara karang', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(666, '', '', '', '', '', '', '', 'sentra teknik utama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(667, '', '', '', '', '', '', '', 'safety ci bubur', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(668, 'hardi tama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(669, 'ana', '', '', '', '', '', '', 'lampung', '', '085212376158', '', 'komplek yuka lk ll rt 2 no 3 karang maritim panjang selatan bandar lampung 35243 nm ita daeng', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(670, 'reynaldi', '', '', '', '', '', '', 'PT PRATAMA GRAHA SEMESTA', '', '081387137768', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(671, '', '', '', '', '', '', '', 'fino safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(672, '', '', '', '', '', '', '', 'sentra aneka sarana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(673, 'pak deni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(674, '', '', '', '', '', '', '', 'total makmur', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(675, '', '', '', '', '', '', '', 'pt binakarindo yacoagug', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(676, '', '', '', '', '', '', '', 'handiman', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(677, '', '', '', '', '', '', '', 'ahen', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(678, '', '', '', '', '', '', '', 'utama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(679, '', '', '', '', '', '', '', 'sinar purnama teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(680, '', '', '', '', '', '', '', 'pt nur kassatama indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(681, '', '', '', '', '', '', '', 'kementrian LHK', '', '', '', '', 'Santo', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(682, 'ibu vini', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(683, '', '', '', '', '', '', '', 'KRAZU NUSANTARA,PT', '', '02186607087', '', 'PURI SENTRA NIAGA BLOK E76 JL RAYA INSPEKSI KALIMALANG ', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(684, 'BPK EKO', '', '', '', '', '', '', 'PT. MEKAR SEMPURNA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(685, '', '', '', '', '', '', '', 'karawaci warehouse', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(686, 'Bpk Yadie', '', '', '', '', '', '', 'brunei', '', '081213145033', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(687, '', '', '', '', '', '', '', 'pt victoria cemerlang', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(688, '', '', '', '', '', '', '', 'andalas safety equpment', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(689, '', '', '', '', '', '', '', 'MAHKOTA', '', '021- 6232 1034', '', 'LTC Lt GF2 A7/5', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(690, '', '', '', '', '', '', '', 'UPP BBB2', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(691, '', '', '', '', '', '', '', 'jakarta safety', '', '0856 9139 8333, 0813 1781 5151', '', 'ltc gf.1 blok c.28 no.9', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(692, 'Mr.Tang', '', '', '', '', '', '', 'Huachuang International', '', '021 6230-5565, 081297973366', '', 'LTC Lt. GF2 Blok A17 No.2', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(693, '', '', '', '', '', '', '', 'PT GARDATAMA MANDARA LINE', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(694, 'Helen', '', '', '', '', '', '', '', '', '', '', 'jl.candi welang no 54 cinde Palembang (0711) 316274', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(695, 'Bpk Priadi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(696, '', '', '', '', '', '', '', 'Buana Safety', '', '02162307186', '', 'Lt.2 Blok B2 No. 3-5', '', 2, 'nonaktif', '2020-07-29 12:15:05', '2021-05-10 12:40:36', 1, 2);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(697, 'r34r', '34234', 'noimage.jpg', 'noimage.jpg', 'CV', '234234', 'Tn', 'PT. Sumber Daya Kelolah', 'wer@sf.d', '234234', '34234', 'dfsfgd', '23423', 0, 'aktif', '2020-07-29 12:15:05', '2021-05-10 12:42:06', 1, 2),
(698, 'chang hai', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(699, '', '', '', '', '', '', '', 'PT shp', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(700, 'Erlin', '', '', '', '', '', '', 'Maju Sukses mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(701, '', '', '', '', '', '', '', 'Rian Jaya', '', '021 2961 7956, 081287591167', '', 'lTC Lt.1 Blok B27 No.1-2', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(702, '', '', '', '', '', '', '', 'sinar indo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(703, '', '', '', '', '', '', '', 'saga makmur', '', '62201028', '', 'Lt GF.2 Blok A.1 NO. 9', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(704, '', '', '', '', '', '', '', 'T.W.A', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(705, 'RIZAL', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(706, '', '', '', '', '', '', '', 'jaya teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(707, '', '', '', '', '', '', '', 'AZIZY', '', '082213571550, 0812 100 49755', '', 'Senen, Blok 1 lt, 2 no.97', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(708, '', '', '', '', '', '', '', 'koperasi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(709, '', '', '', '', '', '', '', 'Star Safety', '', '021 2607 1290', '', 'LTC Lt. GF 1 Blok C17 no. 1/2', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(710, 'YANUAR', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(711, '', '', '', '', '', '', '', 'SP', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(712, 'Bp.Ray', '', '', '', '', '', '', 'Pt. So Good Food Manufacturing', '', '02159400610 / 0215961285', '', 'Jl.Daan Mogot km 12 no.9 jakarta 11730', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(713, '', '', '', '', '', '', '', 'pt.supreme cable', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(714, '', '', '', '', '', '', '', 'Roda Konstruksi Utama', '', '(021) 5812354, 58353837, 58355', '', 'Ruko Green Garden Blok Y3 No.48 Kedoya Utara, Jakarta Barat', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(715, '', '', '', '', '', '', '', 'RMB ( rukun maju bersama)', '', '', '', '', 'PEMBAYARAN HARUS CASH ', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(716, '', '', '', '', '', '', '', 'METRO TEHNIK UTAMA', '', '021- 62201005, 62201005', '', 'LTC Lt, GF 1 Blok B11 no.5', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(717, '', '', '', '', '', '', '', 'profitama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(718, 'Bpk Yush', '', '', '', '', '', '', '', '', '081220067067', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(719, 'Bpk Winarto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(720, 'adib', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(721, 'suwarto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(722, '', '', '', '', '', '', '', 'pt.bucp', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(723, 'TEGUH', '', '', '', '', '', '', 'TUGU PERMATA', '', '02129070470', '', 'gedung mega glodok kemayaoran [MGK] LT.GF blok C.5 NO.3 jakarta pusat', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(724, '', '', '', '', '', '', '', 'inti mulia jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(725, '', '', '', '', '', '', '', 'PT. petronesia benimel', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(726, '', '', '', '', '', '', '', 'PT.wahana safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(727, '', '', '', '', '', '', '', 'pt. harmand linti marin indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(728, 'sholahuddin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(729, '', '', '', '', '', '', '', 'bamboo jaya teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(730, 'Bpk Raden', '', '', '', '', '', '', 'PT. Arkadiya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(731, 'Bpk Tujiono', '', '', '', '', '', '', '', '', '082148129309', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(732, 'Bpk Asep Suhendi', '', '', '', '', '', '', '', '', '082170778889', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(733, '', '', '', '', '', '', '', 'Jobra safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(734, '', '', '', '', '', '', '', 'sumber pengharapan', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(735, 'Bpk Farrel', '', '', '', '', '', '', 'PT.farrel', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(736, 'mukti', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(737, 'pak ilham', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(738, '', '', '', '', '', '', '', 'melpura', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(739, '', '', '', '', '', '', '', 'Sahabat Makmur', '', '', '', 'GF1 C9 No.6', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(740, '', '', '', '', '', '', '', 'KOP JKLR', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(741, 'nur huda', '', '', '', '', '', '', 'INDO TEHNIK', '', '0812 9165 3435', '', 'ltc lt 2 blok a6 no 2-3', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(742, 'JEK', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(743, '', '', '', '', '', '', '', 'PUTRA TEKNIK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(744, '', '', '', '', '', '', '', 'CI TING-TING', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(745, '', '', '', '', '', '', '', 'MAJU MANDIRI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(746, '', '', '', '', '', '', '', 'PT. Multi Ardecorn', '', '085227337736', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(747, 'Bpk Hadi', '', '', '', '', '', '', 'CV. Mandiri Makmur', '', '081285000623', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(748, '', '', '', '', '', '', '', 'Toko sahabat makmur', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(749, '', '', '', '', '', '', '', 'Sinar abadi mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(750, '', '', '', '', '', '', '', 'elnusa petrofin PT', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(751, 'JANUAR', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(752, 'SOFIAN', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(753, '', '', '', '', '', '', '', 'PT. BNN', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(754, '', '', '', '', '', '', '', 'PT. DIS', '', '085792513280', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(755, '', '', '', '', '', '', '', 'Toko terus maju', '', '62200940', '', 'lt sb b1 no 16', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(756, '', '', '', '', '', '', '', 'KMP OG', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(757, 'MARTIN', '', '', '', '', '', '', '', '', '085853699921', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(758, '', '', '', '', '', '', '', 'Winning Logistic (AFRICA) Company Limited', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(759, 'Bpk fadilah', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(760, '', '', '', '', '', '', '', 'Proquillid harapan semesta , pt', '', '081212231997', '', 'ltc glodok\r\nlt.2 C19 NO 3 ', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(761, '', '', '', '', '', '', '', 'TAKUMI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(762, '', '', '', '', '', '', '', 'MULTI TEKNIK MANDIRI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(763, '', '', '', '', '', '', '', 'PT.ADI KARYA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(764, 'UDIN', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(765, '', '', '', '', '', '', '', 'PT. Kencana Mitra', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(766, '', '', '', '', '', '', '', 'AEROFLY', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(767, 'BPK YADIE', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(768, 'GLORIA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(769, 'BOBY', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(770, 'BPK UINCENT', '', '', '', '', '', '', '', '', '6930401/0816716820', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(771, '', '', '', '', '', '', '', 'TBIMA', '', '081213784558', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(772, 'Dani, Bpk', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(773, '', '', '', '', '', '', '', 'fajar teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(774, '', '', '', '', '', '', '', 'PT. Momozen', '', '081331897431', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(775, '', '', '', '', '', '', '', 'INSAN MANDIRI.PT', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(776, 'PUTRA', '', '', '', '', '', '', '', '', '081219915933', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(777, '', '', '', '', '', '', '', 'PT JAYAKUSUMA PERDANA LINES', '', '801212784277', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(778, '', '', '', '', '', '', '', 'IBU ANITA', '', '087876259779', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(779, 'PARIS', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(780, 'bpk iwan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(781, '', '', '', '', '', '', '', 'Toko indohusuma', '', '', '', 'HWI', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(782, 'Bpk Iwan', '', '', '', '', '', '', 'tokped', '', '087808118000', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(783, 'PETER', '', '', '', '', '', '', '', '', '08128363639', '', 'Wesling Kedoya lt 20 No.26', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(784, '', '', '', '', '', '', '', 'JOY SAFETY', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(785, 'YANTO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(786, 'SHIREN', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(787, 'HIMALAYA EVEREST', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(788, '', '', '', '', '', '', '', 'PT.KLINE', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(789, '', '', '', '', '', '', '', 'safety network indonesia (sni)', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(790, 'Bpk Yadi', '', '', '', '', '', '', '', '', '0816633919', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(791, 'Bpk candra', '', '', '', '', '', '', '', '', '08161318824', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(792, '', '', '', '', '', '', '', 'BUKAKA.PT', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(793, '', '', '', '', '', '', '', 'SEMARANG', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(794, '', '', '', '', '', '', '', 'MANDIRI PRTMA JAYA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(795, '', '', '', '', '', '', '', 'PT.UNITED TRACKTOR', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(796, '', '', '', '', '', '', '', 'SAYAP MAS UTAMA.PT', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(797, '', '', '', '', '', '', '', 'pt.tripela isprima salvus', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(798, '', '', '', '', '', '', '', 'andalan teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(799, 'Bpk. Budi PLN', '', '', '', '', '', '', '', '', '08121079422', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(800, 'Bpk fahrul', '', '', '', '', '', '', 'PT. celebes engineering services', '', '0818622472', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(801, 'kuasa pengguna anggaran PPK GBK', '', '', '', '', '', '', '', '', '', '', '087883417894', 'go save 125000\r\njas hujan 125000\r\n081289290366', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(802, 'Bpk Slamet', '', '', '', '', '', '', 'PT SKP', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(803, 'Bpk Sigit', '', '', '', '', '', '', '', '', '081291780421', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(804, 'Bpk sugeng', '', '', '', '', '', '', 'PT. Kretindo agape', '', '0811127824', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(805, '', '', '', '', '', '', '', 'PT CGIC TRADE INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(806, '', '', '', '', '', '', '', 'Hartson', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(807, 'Bpk agus n', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(808, '', '', '', '', '', '', '', 'Sugi Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(809, '', '', '', '', '', '', '', 'putra mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(810, 'purnadi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(811, '', '', '', '', '', '', '', 'mandiri jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(812, 'dian', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(813, '', '', '', '', '', '', '', 'JEK/HWI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(814, '', '', '', '', '', '', '', 'toko_mangga 2', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(815, 'cucu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(816, 'bp toto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(817, '', '', '', '', '', '', '', 'pt.pajar tri intansi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(818, 'bp.badran', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(819, 'adi.p', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(820, '', '', '', '', '', '', '', 'kharisma jaya mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(821, 'wata', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(822, 'bp sonti', '', '', '', '', '', '', '', '', '', '', 'jambi', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(823, '', '', '', '', '', '', '', 'pilot teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(824, '', '', '', '', '', '', '', 'PT BAHARI MAKMUR SEJAHTERA (BMS)', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(825, '', '', '', '', '', '', '', 'Alpa Prima', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(826, 'anton', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(827, 'irawan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(828, 'bp dhany', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(829, 'NINDY', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(830, '', '', '', '', '', '', '', 'Pt. Perdana mitra indonesia (PERMINDO)', '', '542 747678', '', 'Jl Mulawarman No. 22 RT 16 Lamaru - Balikpapan Timur , Balikpapan 76117 - Indonesia', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(831, '', '', '', '', '', '', '', 'pt.jebsen-jessen', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(832, 'alpha pnina', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(833, 'sheren', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(834, 'Bp.Sugi', '', '', '', '', '', '', 'PT.MH Power System Indonesia', '', '', '', '', 'MH power', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(835, '', '', '', '', '', '', '', 'pt.jhalt', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(836, '', '', '', '', '', '', '', 'bp. novan', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(837, 'lufiwati', '', '', '', '', '', '', 'mitra parama', '', '0858 6052 5449', '', 'ltc gf.1 blok c.18 no.3', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(838, '', '', '', '', '', '', '', 'anugrah safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(839, 'kocoir', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(840, '', '', '', '', '', '', '', 'PT,SANS SEDAYA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(841, '', '', '', '', '', '', '', 'PT.KRIYA INDO MAHMUK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(842, '', '', '', '', '', '', '', 'Adidaya Tekhnik', '', '0812 9500 7724', '', 'LTC Lt. GF2 Blok B10 No.2\r\n', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(843, 'angga', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(844, 'danu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(845, 'bpk pramono', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(846, '', '', '', '', '', '', '', 'ssyi/ltc glook', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(847, 'hendra', '', '', '', '', '', '', '', '', '081282525325', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(848, '', '', '', '', '', '', '', 'mandiry safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(849, '', '', '', '', '', '', '', 'Gizi Utama', '', '', '', 'Bandar Lampung', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(850, '', '', '', '', '', '', '', 'MANDIRI SAFETY', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(851, 'HARPY', '', '', '', '', '', '', '', '', '082229407499', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(852, 'BPK DWI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(853, 'indra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(854, 'Bpk fajar', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(855, 'Ibu Gaby', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(856, 'Bpk arif', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(857, 'HERI Saga', '', '', '', '', '', '', '', '', '085695959013', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(858, 'Ibu ari', '', '', '', '', '', '', '', '', '085695694605', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(859, '', '', '', '', '', '', '', 'pt. bumi metalindo prsada', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(860, '', '', '', '', '', '', '', 'E.K.P', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(861, '', '', '', '', '', '', '', 'takumi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(862, 'Ibu maya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(863, '', '', '', '', '', '', '', 'total teknik', '', '62310933', '', 'lt 2 blok b 20 no 3', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(864, 'nata', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(865, 'ci wenny', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(866, '', '', '', '', '', '', '', 'RMP', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(867, 'ci cvi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(868, 'Ibu Jea', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(869, '', '', '', '', '', '', '', 'Citra agung abadi', '', '', '', 'hwi', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(870, 'Bpk Irawadi', '', '', '', '', '', '', '', '', '08121365530', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(871, 'Bpk Fahmi', '', '', '', '', '', '', '', '', '085215047570', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(872, '', '', '', '', '', '', '', 'toko dunia k3', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(873, 'Bpk Salidin', '', '', '', '', '', '', '', '', '0818547876', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(874, '', '', '', '', '', '', '', 'pt.andalan k3', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(875, 'Bpk Angga', '', '', '', '', '', '', 'focus', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(876, 'Ibu Usri', '', '', '', '', '', '', '', '', '081290585132', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(877, 'Bpk joko', '', '', '', '', '', '', 'Surabaya', '', '081330540612', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(878, 'Bpk Riza', '', '', '', '', '', '', '', '', '081219158705', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(879, '', '', '', '', '', '', '', 'GOLDEN TEKNIK', '', '021-62320223 , 62307205', '', 'Lt GF 1 BLOK C30 NO. 8', '', 2, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(880, 'Bpk edy susanto', '', '', '', '', '', '', '', '', '081219594744', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(881, 'Bpk khairul', '', '', '', '', '', '', '', '', '081286421915', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(882, 'Bpk ronald', '', '', '', '', '', '', 'bogor', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(883, 'Bpk Jeral', '', '', '', '', '', '', '', '', '085731349079', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(884, '', '', '', '', '', '', '', 'toko animo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(885, 'Pak anggit', '', '', '', '', '', '', 'cotton', '', '085892220907', '', '', '', 1, 'aktif', '2020-07-29 12:15:05', '2020-07-29 12:15:05', 1, 1),
(886, 'Bpk Tito', '', '', '', '', '', '', '', '', '081331933932', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(887, 'Bpk Ijal', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(888, '', '', '', '', '', '', '', 'PT. Nicklaus', '', '08121072523', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(889, 'Bpk Bahar', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(890, 'PT Wijaya karya', '', '', '', '', '', '', '', '', '085697053360', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(891, 'Bpk Joy', '', '', '', '', '', '', 'security', '', '08129959932', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(892, 'hartono', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(893, 'hada', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(894, 'Bpk Asikin', '', '', '', '', '', '', '', '', '081807948801', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(895, 'Ibu retno', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(896, '', '', '', '', '', '', '', 'toko jakarta prima teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(897, 'bp.suwandy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(898, 'bu cidya/bali', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(899, '', '', '', '', '', '', '', 'pt.fajar tri insani', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(900, 'FATMA', '', '', '', '', '', '', 'TWA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(901, '', '', '', '', '', '', '', 'TOKO SSYI TILTOTIUS', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(902, '', '', '', '', '', '', '', 'PT.cemani karya mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(903, 'jonathan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(904, '', '', '', '', '', '', '', 'pt.panca duta prakarsa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(905, '', '', '', '', '', '', '', 'jaya eka', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(906, 'debdy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(907, 'ny.ricka', '', '', '', '', '', '', 'multico', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(908, 'Ibu Yati tunas', '', '', '', '', '', '', '', '', '08984006896', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(909, 'Bpk Maman / Hendri simatupang', '', '', '', '', '', '', 'batam', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(910, 'Bpk abraham', '', '', '', '', '', '', '', '', '085728527558', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(911, 'Bpk Alan', '', '', '', '', '', '', 'AGALSCO', '', '08121956234 / 087770192999', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(912, 'Bpk heru', '', '', '', '', '', '', 'rompi 3m', '', '08129992032', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(913, '', '', '', '', '', '', '', 'toko gss', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(914, '', '', '', '', '', '', '', 'pt.multi ocean shipyard', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(915, '', '', '', '', '', '', '', 'toko mitra safety', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(916, 'Ibu kemal', '', '', '', '', '', '', '', '', '081342209894', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(917, '', '', '', '', '', '', '', 'SangSang Univ KT&G', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(918, 'hanudil teknik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(919, 'Ibu tantri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(920, 'Bpk Maulana', '', '', '', '', '', '', '', '', '085814575965', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(921, '', '', '', '', '', '', '', 'toko mitra teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(922, 'Awong jaya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(923, '', '', '', '', '', '', '', 'pt. putra artha mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(924, '', '', '', '', '', '', '', 'pt.trit indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(925, '', '', '', '', '', '', '', 'sentral teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(926, 'Risky', '', '', '', '', '', '', 'Pt.Group Mitra Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(927, '', '', '', '', '', '', '', 'ibu yuli', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(928, '', '', '', '', '', '', '', 'PT. Berkah Logam', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(929, 'Ko gery', '', '', '', '', '', '', 'toko utama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(930, 'sudg', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(931, '', '', '', '', '', '', '', 'fibs pt', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(932, 'sude', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(933, 'pt.egameanka pratama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(934, 'Bpk Ferdian', '', '', '', '', '', '', '', '', '', '', 'Depok ', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(935, 'Bpk ugi', '', '', '', '', '', '', '', '', '081297833375', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(936, '', '', '', '', '', '', '', 'PT. Sinar agung lestari', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(937, '', '', '', '', '', '', '', 'Artha Safety', '', '0812 1261 2791', '', 'LTC Lt. GF 1 Blok B no.62 Pameran', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(938, 'pelita sejahtera', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(939, 'SSJ', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(940, 'ADI (SKS)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(941, 'SOLAHUDIN', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(942, 'ANDY', '', '', '', '', '', '', 'PT. ADLERINDO', '', '085299919133', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(943, '', '', '', '', '', '', '', 'LOBU TEKNIK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(944, '', '', '', '', '', '', '', 'PT. Sumber Mitra Jaya', '', '', '', 'Gedung Graha Irama (INDORAMA) LT.14\r\nJL. Rasuna Said Blok X-1 kav 1-2', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(945, '', '', '', '', '', '', '', 'CV. Alfindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(946, 'BPK ENDANG', '', '', '', '', '', '', 'PT. KING INDONESIA MULIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(947, '', '', '', '', '', '', '', 'INDO KARYA SAFETY', '', '', '', 'HARKO GLODOK LT.5 BLOK H-3', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(948, '', '', '', '', '', '', '', 'CITRA TEKNIK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(949, 'lukman lt 3', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(950, 'Bpk Nurdin', '', '', '', '', '', '', 'aceh', '', '081377272701', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(951, '', '', '', '', '', '', '', 'pt.trans continent', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(952, '', '', '', '', '', '', '', 'JS SAFETY', '', '08118989588', '', 'Andre Pamungkas \r\nJS SAFETY \r\nJL.Raya Serang KM13 NO.8F\r\nCikupa,Tanggerang\r\n15710', 'xxl 10.000\r\n3xl 20.000', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(953, 'Bpk Ardianto', '', '', '', '', '', '', '', '', '081215291285', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(954, 'muliaddy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(955, '', '', '', '', '', '', '', 'my.poppy', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(956, '', '', '', '', '', '', '', 'pelita sehahtra', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(957, '', '', '', '', '', '', '', 'PT. Daya Radar Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(958, 'Shilva Dwi', '', '', '', '', '', '', 'SERAGAM SAFETY', '', '0858 9426 7596', '', 'ltc lt 2 blok b.17 no.1-2', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(959, '', '', '', '', '', '', '', 'BAUT TEKNIK SAMUDRA', '', '0231 207322', '', ' jl karanggetas no 36 CIREBON (45118)', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(960, '', '', '', '', '', '', '', 'high speed railway contractor consortium', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(961, '', '', '', '', '', '', '', 'PT.technipifmc', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(962, 'aptalisman', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(963, '', '', '', '', '', '', '', 'pt.ads', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(964, 'ny.dewi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(965, 'Ruchi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(966, '', '', '', '', '', '', '', 'pt. ega mekinkagtup', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(967, 'ssp', '', '', '', '', '', '', 'suwis', '', '081380113445', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(968, 'ibu rini', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(969, '', '', '', '', '', '', '', 'PT SH Machinery indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(970, 'Bpk acil', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(971, 'wawa', '', '', '', '', '', '', 'pt. Nebraska Pratama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(972, '', '', '', '', '', '', '', 'pt. L.I,M', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(973, 'wiliam', '', '', '', '', '', '', 'damai', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(974, 'Bpk candra', '', '', '', '', '', '', 'ATASAN', '', '08195106844', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(975, '', '', '', '', '', '', '', 'PT. Marunda Jaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(976, 'diana', '', '', '', '', '', '', '', '', '0818918778', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(977, 'kiki', '', '', '', '', '', '', 'pt. pln (persero) uup jjbb2', '', '081224999488', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(978, '', '', '', '', '', '', '', 'jaga eka', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(979, '', '', '', '', '', '', '', 'pt.prakarasa tunggal usaha mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(980, '', '', '', '', '', '', '', 'Toko Berkat Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(981, 'Bpk Ganyong', '', '', '', '', '', '', '', '', '087770990113', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(982, '', '', '', '', '', '', '', 'dunia katiga', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(983, 'melki', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(984, 'ibu tari', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(985, '', '', '', '', '', '', '', 'pt.iwaktani', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(986, 'ikhsah', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(987, '', '', '', '', '', '', '', 'pt.gerinda', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(988, 'Ibu Indri Mayenti', '', '', '', '', '', '', '', '', '081213654763', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(989, 'ageng jasamarja', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(990, '', '', '', '', '', '', '', 'PT. Nindya Karya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(991, 'Bpk Ika', '', '', '', '', '', '', '', '', '081388194966', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(992, 'Bpk andrian joko', '', '', '', '', '', '', '', '', '085217611169', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(993, '', '', '', '', '', '', '', 'PT. INDO CIPTA MITRA SOLUSI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(994, 'Bpk redick geriyanto', '', '', '', '', '', '', '', '', '082279022527', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(995, 'Rohma Kurnia Ningsih', '', '', '', '', '', '', '', '', '081514188760', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(996, 'ny pebby', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(997, 'mr santhosh', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(998, 'bpk acung', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(999, '', '', '', '', '', '', '', 'pt.amtek indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1000, 'sabrina', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1001, 'Christian', '', '', '', '', '', '', 'Glodok Teknik', '', '021-62201063, 021- 62320340', '', 'UG Blok C26 No.3', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1002, '', '', '', '', '', '', '', 'KIR', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1003, '', '', '', '', '', '', '', 'ltc', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1004, '', '', '', '', '', '', '', 'diamondindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1005, 'josep', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1006, 'dessy', '', '', '', '', '', '', 'MHP', '', '021 2268 4193, 0813 8221 5850', '', 'LTC Glodok Lt.2 Blok C10 No.1', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1007, '', '', '', '', '', '', '', 'PT. Mega Alfarizki', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1008, 'Daniel', '', '', '', '', '', '', '', '', '0838 4789 1333', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1009, 'safarul', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1010, '', '', '', '', '', '', '', 'm,h,p', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1011, '', '', '', '', '', '', '', 'citra sejati', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1012, '', '', '', '', '', '', '', 'qiu richie', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1013, 'Bpk kiki', '', '', '', '', '', '', '', '', '', '', '', 'harga markup harga bapak kiki\r\nharga asli harga surya mas ', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1014, 'bu iman', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1015, 'Bpk boy', '', '', '', '', '', '', 'Bandung', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1016, 'Bpk Rimron', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1017, 'ari', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1018, 'bp iwan', '', '', '', '', '', '', 'sucofindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1019, 'pupr', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1020, 'sniah udin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1021, '', '', '', '', '', '', '', 'inox perima 3m', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1022, 'eko', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1023, '', '', '', '', '', '', '', 'Toko Hosana Doho', '', '082247104647', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1024, '', '', '', '', '', '', '', 'Surya Mas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1025, '', '', '', '', '', '', '', 'maju jaya mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1026, '', '', '', '', '', '', '', 'PT. NAV  INSPECTION', '', '08113436866', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1027, 'ny nuraini', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1028, '', '', '', '', '', '', '', 'pt.kadea', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1029, 'ibu poppy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1030, 'helmi', '', '', '', '', '', '', '', '', '0895325529438', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1031, '', '', '', '', '', '', '', 'Divimas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1032, 'yunarto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1033, 'novi', '', '', '', '', '', '', 'sinar kinda utama', '', '', '', 'Palembang ', 'CUSTOMER ERLIN/ MAJU SUKES MANDIRI ', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1034, '', '', '', '', '', '', '', 'trimukti', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1035, '', '', '', '', '', '', '', 'PT MERAK BANGUN SAMUDERA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1036, 'ardi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1037, 'Bpk sangkot', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1038, '', '', '', '', '', '', '', 'PT.CKPAKAN', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1039, 'Mr.Chen Ce Nan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1040, '', '', '', '', '', '', '', 'pt ega melcinaka', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1041, '', '', '', '', '', '', '', 'PT.SKP', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1042, '', '', '', '', '', '', '', 'ADI DAYA TEKNIK', '', '', '', 'GF.2 BLOK B10 NO 2', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1043, 'CHEN JING KE', '', '', '', '', '', '', '', '', '082122309232', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1044, '', '', '', '', '', '', '', 'karya makmur abadi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1045, 'erwin', '', '', '', '', '', '', '', '', '081533449980', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1046, 'Bpk hasan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1047, '', '', '', '', '', '', '', 'pt.himalaya citra abadai', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1048, 'ko danil/jancuk', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1049, 'rio', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1050, 'Bpk edwin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1051, 'Bpk Ridwan boots', '', '', '', '', '', '', '', '', '081310578855', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1052, '', '', '', '', '', '', '', 'catig hai', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1053, '', '', '', '', '', '', '', 'selalu berjaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1054, 'ATA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1055, 'Bpk dede rahmat', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1056, 'Bpk. Adang', '', '', '', '', '', '', '', '', '081398325884', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1057, '', '', '', '', '', '', '', 'fajar sentosa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1058, '', '', '', '', '', '', '', 'pt.naga laja lestari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1059, 'lukman', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1060, '', '', '', '', '', '', '', 'hidayah safety indonesia', '', '02122686990', '', 'ltc glodok lantai gf 1 blok c8 no 6', '089649933542\r\n085889495649\r\n081808760079', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1061, 'cash', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1062, 'arifin', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1063, 'Bpk Imam', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1064, '', '', '', '', '', '', '', 'TOKO HDR SAFETY', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1065, 'chaerul', '', '', '', '', '', '', '', '', '081310701879', '', 'jl.percetakan negara XB blok G 17 rt/01/04 rawasari, jkt pusat', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1066, '', '', '', '', '', '', '', 'TOKO METRO TEHNIK UTAMA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1067, 'Bpk Ating', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1068, '', '', '', '', '', '', '', 'assyifah safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1069, 'nanang', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1070, 'hargo mandiri', '', '', '', '', '', '', '', '', '', '', 'lt. ug', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1071, 'BPK RICKY', '', '', '', '', '', '', 'PT KUTILANG BANGUN PERKASA', '', '082116205277', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1072, 'ima', '', '', '', '', '', '', '', '', '08129784388', '', 'jl.achmad adnawijaya D 1 no.2 \r\nbogor 16152', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1073, 'pak walid(kantor dinas pu)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1074, 'away', '', '', '', '', '', '', 'sintar safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1075, 'rizky', '', '', '', '', '', '', '', '', '', '', 'serpong', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1076, 'zukir', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1077, 'timotius', '', '', '', '', '', '', 'saksi-saksi yehuwa indonesia', '', 'wa 081806252727', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1078, 'okta', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1079, 'DETIGA', '', '', '', '', '', '', '', '', '', '', 'LTC GLODOK\r\nLT.1 BLOK B6 NO.3', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1080, 'TRUBA', '', '', '', '', '', '', '', '', '087880590623', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1081, '', '', '', '', '', '', '', 'PT.KUTILANG BANGUN PERKASA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1082, 'A.Yani', '', '', '', '', '', '', '', '', '08111018969', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1083, '', '', '', '', '', '', '', 'pt indotrada', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1084, 'boy tampubolon', '', '', '', '', '', '', 'siantar safety', '', '085213038804', '', 'lt 2 blok c2 no 5\r\nfax 02162317853', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1085, 'adimix', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1086, '', '', '', '', '', '', '', 'fave hotel', '', '', '', 'kelapa gading', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1087, 'Dinas PU kota tangerang selatan', '', '', '', '', '', '', '', '', '', '', 'up.ibu Neni/ratih\r\nJl raya puspitek serpong no.1 kav 51 B \r\nsetu\r\ntangerang selatan', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1088, 'niko', '', '', '', '', '', '', '', '', '08128606707', '', '', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1089, 'arif', '', '', '', '', '', '', '', '', '081317807768', '', 'kantor BM Apartement sky view\r\njl.raya lengkong gudang timur\r\nserpong\r\ntangerang selatan', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1090, '', '', '', '', '', '', '', 'PT.TSP', '', '0811966179', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1091, '', '', '', '', '', '', '', 'cv. akma', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1092, 'pt Samba Arnavat Indonesia', '', '', '', '', '', '', '', '', '081336459949', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1093, 'Bpk Hendri purnomo', '', '', '', '', '', '', '', '', '082299742001', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1094, '', '', '', '', '', '', '', 'PT. link bintang line', 'deddysetiawan899@yahoo.co.id', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1095, 'Bpk Praja', '', '', '', '', '', '', 'PT. Hutama Karya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1096, 'Bpk abusalah', '', '', '', '', '', '', '', '', '085691333456', '', '', '', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1097, 'ardi', '', '', '', '', '', '', 'cv.aditya pratama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1098, 'adrian', '', '', '', '', '', '', '', '', '', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1099, 'Bpk afriansiah', '', '', '', '', '', '', '', '', '', '', '', 'sodara trismadani ', 1, 'aktif', '2020-07-29 12:15:06', '2020-07-29 12:15:06', 1, 1),
(1100, 'irwah', '', '', '', '', '', '', 'anugrah berkat gemilang', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1101, 'dwi/kemenhub', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1102, 'ropi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1103, 'wahid/semarang', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1104, '', '', '', '', '', '', '', 'pt karya bayu abadai lines', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1105, '', '', '', '', '', '', '', 'pt marga putri raya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1106, '', '', '', '', '', '', '', 'pt bda', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1107, 'ibu ita', '', '', '', '', '', '', '', '', '', '', 'bekasi', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1108, '', '', '', '', '', '', '', 'labu teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1109, 'baruna jaya', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1110, 'maulana', '', '', '', '', '', '', '', '', '', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1111, 'michael', '', '', '', '', '', '', 'PT EXXA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1112, '', '', '', '', '', '', '', 'Bintang Terang', '', '0711-417004', '', 'Jl.Kol Haji Berlian KM9 No.98A \r\nPalembang', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1113, 'Nano', '', '', '', '', '', '', 'PT ADF', '', '081310940277', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1114, 'M. Satibi', '', '', '', '', '', '', 'pt sapta krida mandiri', '', '082114809776', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1115, 'ongen', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1116, '', '', '', '', '', '', '', 'wijaya teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1117, '', '', '', '', '', '', '', 'cv.dwi rangga teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1118, '', '', '', '', '', '', '', 'So Good Food Lampung', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1119, '', '', '', '', '', '', '', 'matrix', '', '', '', 'pekanbaru ', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1120, 'PT ADEMCO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1121, 'ANDRY', '', '', '', '', '', '', 'TOKO CENTRAL', '', '08127118438', '', 'BELITUNG TANJUNG PANDAN JLN SRIWIJAYA NO 28 ', 'EXPEDISI DARMA PUTRA BILITON JLN GUNUNG SAHARI RAYA RUKO MARINA TAMA BLOCK H NO 12B \r\nTELP : 021 64702825 ', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1122, 'Widyantoro,S.T', '', '', '', '', '', '', 'ADHI-KARYA PRIMA(KSO)', '', '', '', 'contact person : Dimas Diandaru (0813-9870-8015)', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1123, 'Didi', '', '', '', '', '', '', '', '', '0878774313', '', 'cilegon', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1124, '', '', '', '', '', '', '', 'PT GUNUNG RAJA PAKSI', '', '0216298031', '', 'JL P. JAYAKARTA NO 105G', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1125, '', '', '', '', '', '', '', 'PT.INDO CHANGHAI KONTRUKSI', '', '', '', 'GLOF LAKE RESIDENCE RUKAN PARIS A NO.21 RT/010 RW/014 CENGKARENG TIMUR CENGKARENG JAKARTA BARAT', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1126, 'PETRUS', '', '', '', '', '', '', 'PT. SATYAMITRA KEMAS LESTARI', '', '087808064224', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1127, 'Darikin', '', '', '', '', '', '', '', '', '087721471102', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1128, 'umar', '', '', '', '', '', '', '', '', '081219211171', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1129, 'Heri', '', '', '', '', '', '', 'HIJRAH UTAMA', '', '081295502266', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1130, 'Adam', '', '', '', '', '', '', '', '', '', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1131, '', '', '', '', '', '', '', 'Adipurusa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1132, 'sammi', '', '', '', '', '', '', '', '', '', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1133, 'iskandar', '', '', '', '', '', '', 'seaman jaya', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1134, '', '', '', '', '', '', '', 'PT. Hadaida Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1135, 'hariri', '', '', '', '', '', '', '', '', '085238247266', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1136, 'Ibu Linda', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1137, 'Bpk Zetta', '', '', '', '', '', '', '', '', '081319093627', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1138, '', '', '', '', '', '', '', 'ilah sarana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1139, '', '', '', '', '', '', '', 'pt jawimas', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1140, 'pak bertho', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1141, '', '', '', '', '', '', '', 'PT. Dwitunggal Karunia Gemilang', '', '02185913537', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1142, 'Anto', '', '', '', '', '', '', 'NALCO', '', '081288333722', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1143, '', '', '', '', '', '', '', 'PT. Pratama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1144, 'Bpk Awen', '', '', '', '', '', '', 'Berjaya Nusantara', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1145, 'Ibu Mila', '', '', '', '', '', '', 'Teguh Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1146, '', '', '', '', '', '', '', 'Toko Karya Abadi', '', '', '', 'LTC GLODOK Lt GF2 C28 no.6', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1147, '', '', '', '', '', '', '', 'PT. INDOTAMA MAJU MANDIRI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1148, '', '', '', '', '', '', '', 'jocelindo mandiri', '', '', '', 'lt 1 blok b no 25', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1149, 'Bpk ahmad ruri', '', '', '', '', '', '', '', '', '08158849734', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1150, 'Alex', '', '', '', '', '', '', '', '', '081399252038', '', 'jl.H Awi no.49 rt 01/09 \r\nsrengseng sawah\r\njagakarsa\r\njakarta selatan', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1151, '', '', '', '', '', '', '', 'PT Anugrah Kasih Karunia Abadi', '', '', '', 'Jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1152, '', '', '', '', '', '', '', 'globaltv', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1153, '', '', '', '', '', '', '', 'PT. TEREOS FKS INDONESIA', '', '', '', '', 'Klien Santo', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1154, 'bpk Edy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1155, 'Faqih', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1156, 'Ibu Lisa', '', '', '', '', '', '', '', '', '081210494765', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1157, 'Bpk herman', '', '', '', '', '', '', 'AMP', '', '081219845360', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1158, 'bu aita', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1159, '', '', '', '', '', '', '', 'pt greatwall drilling asia pacific', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1160, 'darim', '', '', '', '', '', '', 'PT Kalimutu', '', '081218805986', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1161, 'mulyono', '', '', '', '', '', '', 'multi pabrindo gemilang', '', '082213081156', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1162, 'LUMIN LED', '', '', '', '', '', '', '', '', '', '', 'LTC glodok lantai ug', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1163, 'deden', '', '', '', '', '', '', 'PT Apriliza Sukses Pratama', '', '081210975346', '', 'Jl. raya PLP curug KM 3\r\ngrand puri asih c4/14\r\nkadujaya, curug-tangerang', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1164, 'syamsir', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1165, 'GNAMO', '', '', '', '', '', '', '', '', '0817881420023', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1166, '', '', '', '', '', '', '', 'aimen lemigos', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1167, '', '', '', '', '', '', '', 'pt. edu', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1168, 'Bpk wiwit', '', '', '', '', '', '', '', '', '082114594677', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1169, '', '', '', '', '', '', '', 'betesda', '', '081284486672', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1170, 'pak hilman', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1171, 'MCW', '', '', '', '', '', '', '', '', '081947383179', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1172, '', '', '', '', '', '', '', 'KONSORSIUM RABANA - EUROASIATIC - LIMAN', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1173, '', '', '', '', '', '', '', 'Safety 123', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1174, 'santi', '', '', '', '', '', '', 'PT PUTRA SANBAY PERKASA', '', '0811488700', '', 'ternate', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1175, '', '', '', '', '', '', '', 'pt.sato tex', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1176, 'teddy', '', '', '', '', '', '', 'pemda condet', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1177, 'dayat', '', '', '', '', '', '', '', '', '081310540101', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1178, '', '', '', '', '', '', '', 'dwi jaya mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1179, 'Arum', '', '', '', '', '', '', 'PT SUPLINTAMA MAJU SEMESTA', '', '089603013251', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1180, '', '', '', '', '', '', '', 'PT TENSINDO KREASI NUSANTARA', '', '', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1181, 'Widi Wijayanto', '', '', '', '', '', '', 'PT. SUCOFINDO', 'widiw@sucofindo.co.id', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1182, 'yosef', '', '', '', '', '', '', '', '', '08119925767', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1183, 'Bayu', '', '', '', '', '', '', 'EMP.id', 'bayu.adhi@emp.id', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1184, '', '', '', '', '', '', '', 'pt.bing mandiri perkasa drill', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1185, '', '', '', '', '', '', '', 'pt.indra jaya lestari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1186, '', '', '', '', '', '', '', 'pt.bintang mandiri perkasa drill', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1187, 'Bpk Sukarno', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1188, '', '', '', '', '', '', '', 'BPJS KETENAGAKERJAAN JAKARTA CILINCING', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1189, 'iwan', '', '', '', '', '', '', '', '', '', '', 'LTC', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1190, 'EDI', '', '', '', '', '', '', '', '', '081315933387', '', 'jakarta', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1191, '', '', '', '', '', '', '', 'duta sentosa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1192, '', '', '', '', '', '', '', 'TOKO INDO JAYA', '', '', '', 'GF2 B 19 NO 1', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1193, 'medi', '', '', '', '', '', '', '', '', '08117866279', '', 'bengkulu', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1194, '', '', '', '', '', '', '', 'pt.crew', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1195, '', '', '', '', '', '', '', 'abadai jaya teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1196, '', '', '', '', '', '', '', 'berdir', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1197, 'china railway signal & communication', '', '', '', '', '', '', 'CRSC', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1198, 'ADE', '', '', '', '', '', '', '', '', '08119394999', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1199, '', '', '', '', '', '', '', 'TUNGGAL PANTES SOLO', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1200, 'sebastian', '', '', '', '', '', '', '', '', '08562007788', '', 'komplek episentrum\r\njl. HR rasuna said rt.2/5 karet kuningan\r\nsetia budi\r\njakarta selatan , jakarta 12940', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1201, 'ISHAK', '', '', '', '', '', '', '', '', '081808892012', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1202, 'RONI', '', '', '', '', '', '', 'PT TIRTA INTIMIZU NUSANTARA', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1203, 'Ibu iriani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1204, 'KEZILLAZ', '', '', '', '', '', '', '', '', '08567893191', '', 'JL PETOJO PY1 NO.32\r\nCIDENG\r\nJAKARTA PUSAT', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1205, 'Bpk Indra Syafrin', '', '', '', '', '', '', '', '', '08976737107', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1206, 'FRANGKY', '', '', '', '', '', '', '', '', '081310636242', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1207, 'YAN', '', '', '', '', '', '', '', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1208, '', '', '', '', '', '', '', 'PT. Semen Padang', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1209, '', '', '', '', '', '', '', 'PT PESONA GERBANG', '', '0267-8450988', '', 'KARAWANG', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1210, '', '', '', '', '', '', '', 'SAFETY WORLD', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1211, '', '', '', '', '', '', '', 'pt.karisma', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1212, '', '', '', '', '', '', '', 'PT. Makmur Meta Graha Dinamika', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1213, 'Bpk. Dwi restu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1214, 'fagih putra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1215, '', '', '', '', '', '', '', 'pt gunung mas jaya indah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1216, '', '', '', '', '', '', '', 'TOKO UTAMA JAYA MANDIRI', '', '', '', 'gf2 blok b3 no 2 ', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1217, '', '', '', '', '', '', '', 'CV. CEZA UTAMA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1218, '', '', '', '', '', '', '', 'MASTERINDO JAYA PONTIANAK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1219, 'nico', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1220, '', '', '', '', '', '', '', 'pt indah jaya lestari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1221, '', '', '', '', '', '', '', 'PT SEMAR GEMILANG', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1222, 'ARI', '', '', '', '', '', '', '', '', '081210606465', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1223, 'susi', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1224, 'puguh', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1225, '', '', '', '', '', '', '', 'PT Rajawali Amarta Semesta', '', '021-84594994', '', 'Ruko rajawali\r\njl raya cimatis \r\npintu masuk 2\r\ncitragrand', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1226, 'Bpk Adhidyo Ryanto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1227, '', '', '', '', '', '', '', 'PUTRA SUKSES MAKMUR', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1228, 'Bpk Agus Supriyadi', '', '', '', '', '', '', 'PT. SOLUSI ELEVATOR INDONESIA', '', '085813473511', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1229, '', '', '', '', '', '', '', 'MAJU JAYA CILEGON', '', '081511048729', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1230, 'PNI', '', '', '', '', '', '', '', '', '', '', 'JAKARTA', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1231, 'ARDI', '', '', '', '', '', '', 'WASKITA BETON PRECAST,TBK', '', '081285496995', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1232, '', '', '', '', '', '', '', 'PT. HUA HUI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1233, 'Bpk Rafdy', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1234, '', '', '', '', '', '', '', 'MATRIX', '', '0812 757 1657', '', 'JL.RIAU 28C DEPAN RBC PEKANBARU -RIAU', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1235, 'Remkho Tangkilisan', '', '', '', '', '', '', '', '', '', '', 'jl siswa no 38 lingkungan 1 kecamatan tikala kelurahan taas manado, 95129', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1236, '', '', '', '', '', '', '', 'pt.hardinata prabujaya prabumulih', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1237, 'charles', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1238, 'Bu Yuliana', '', '', '', '', '', '', '', '', '087883950746', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1239, '', '', '', '', '', '', '', 'Mandiri Tekhnik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1240, '', '', '', '', '', '', '', 'PT. Jali Indonesia Utama', '', '', '', 'LT.UG\r\nC23 no 2', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1241, 'soraya', '', '', '', '', '', '', '', '', '081281020064', '', 'bogor', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1242, '', '', '', '', '', '', '', 'Pan Pasifik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1243, 'Ibu detha', '', '', '', '', '', '', 'PT. Wahana prestasi logistik', '', '081282103611', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1244, '', '', '', '', '', '', '', 'PT. WIRA SETYA INDO PUTRA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1245, '', '', '', '', '', '', '', 'PT. MATA GARUDA INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1246, 'pro safety', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1247, '', '', '', '', '', '', '', 'PT. HSA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1248, '', '', '', '', '', '', '', 'inti jaya', '', '087886022278', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1249, 'RATNA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1250, '', '', '', '', '', '', '', 'victory abadi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1251, '', '', '', '', '', '', '', 'pt.s.k.p', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1252, 'budi suryanto', '', '', '', '', '', '', 'pt.global haditech', '', '0813-8317-4828', '', 'jl.raya caman no 20a jati bening bekasi', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1253, 'BENI', '', '', '', '', '', '', '', '', '08129871668', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1254, 'PT DELTA', '', '', '', '', '', '', '', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1255, 'YOSI', '', '', '', '', '', '', 'PT ANUGRAH TEKNIK ASIA', '', '081393491479', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1256, 'Bpk Adit', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1257, 'LULU', '', '', '', '', '', '', '', '', '081286668117', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1258, 'Bpk Hatariadi', '', '', '', '', '', '', '', '', '081806568757', '', 'Cilegon', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1259, 'wandy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1260, '', '', '', '', '', '', '', 'Citra Karya Sentosa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1261, '', '', '', '', '', '', '', 'pt mutiara indah anugrah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1262, 'INDRA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1263, 'ALVIAN', '', '', '', '', '', '', '', '', '082113876509', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1264, '', '', '', '', '', '', '', 'PT. Sindo Utama Jaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1265, 'eric', '', '', '', '', '', '', 'PT.KARYA PERSADA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1266, 'BANDARA KAIMANA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1267, '', '', '', '', '', '', '', 'PT. Benua Cakra Petrolindo', '', '0217507565', '', 'Jl. Roxy Mas D5 No.3', '085811212333', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1268, 'Ibu Anita', '', '', '', '', '', '', 'Papua', '', '08124800330', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1269, '', '', '', '', '', '', '', 'PT. TEGUH PRATAMA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1270, 'ko aan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1271, 'Stian Farai Toddy', '', '', '', '', '', '', 'PT. HSG Teknologi Indonesia', 'toddy.stian@hsg-teknologi.com', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1272, '', '', '', '', '', '', '', 'PT VAUTID INDONESIA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1273, 'halim', '', '', '', '', '', '', '', '', '0812-8234-5356', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1274, 'ARIF', '', '', '', '', '', '', 'BAHTERA ADIGUNA', '', '012-6912547', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1275, '', '', '', '', '', '', '', 'pt lestari indah mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1276, 'Bpk Alex', '', '', '', '', '', '', '', '', '0816707251', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1277, 'susi profitama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1278, 'PT MAGNA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1279, 'santoso', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1280, 'Bpk. Uman', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1281, '', '', '', '', '', '', '', 'PT. Warterien Nusantara', '', '081281174358', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1282, 'yumadi', '', '', '', '', '', '', '', '', '081314315842', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1283, 'DUNIA CAKRAWALA', '', '', '', '', '', '', '', '', '', '', 'LT. GF 2\r\nLTC GLODOK', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1284, 'SUMBER JAYA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1285, '', '', '', '', '', '', '', 'Ruas Pekanbaru Dumai Seksi 1,2', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1286, '', '', '', '', '', '', '', 'Ruas Pekanbaru Dumai Seksi 3,4', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1287, 'imam palu', '', '', '', '', '', '', 'pt belona jaya mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1288, '', '', '', '', '', '', '', 'PT. Guteg Harindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1289, 'IVAN', '', '', '', '', '', '', 'T APARTEMENT', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1290, '', '', '', '', '', '', '', 'PT WASA MITRA ENGINEERING', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1291, '', '', '', '', '', '', '', 'SMK MUHAMMADIYAH KANDANGHAUR', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1292, 'Bpk. Imam (ps)', '', '', '', '', '', '', '', '', '', '', '', 'pusat safety', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1293, 'wandy', '', '', '', '', '', '', 'creative', '', '0812 8184 1572', '', 'Pasar Senen\r\nLT.2 Blok 5 No. 97', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1294, '', '', '', '', '', '', '', 'pt.mayekawa indonesia', '', '(62)21 831 2360', '', 'synthesis square tower 2 lt 7 unit a,b dan c jl.jend gatot subroto kav 64 no 177a menteng dalam tebet 12870 jakarta selatan ', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1295, 'muhazir', '', '', '', '', '', '', 'pt. gearindo tiga utama', '', '08116828110-30014949', '', 'menara kuningan lt;ll unit e&f ', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1296, 'ifzak', '', '', '', '', '', '', 'gadeng collection', '', '', '', 'jl. t.iskandar no 5 lambhuk kec ulekareng b.aceh', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1297, '', '', '', '', '', '', '', 'SAFETY WORLD', '', '', '', 'LT UG BLOK C 30 NO 8 \r\n', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1298, 'ATIK-MATAHARI', '', '', '', '', '', '', '', '', '', '', 'LTC', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1299, 'Bpk Ali Amin', '', '', '', '', '', '', 'PT. SRI Purna Karya', '', '081373648325', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1300, '', '', '', '', '', '', '', 'PT. Mitra Indah', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1301, 'Bpk Ananta', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1302, '', '', '', '', '', '', '', 'PT. Sanbel Satria Wardana', '', '', '', '', 'bpk ayi', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1303, '', '', '', '', '', '', '', 'cibubur safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1304, '', '', '', '', '', '', '', 'bintang satu nur', '', '085777680649', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1305, '', '', '', '', '', '', '', 'sumber jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1306, 'Bpk andiki', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1307, '', '', '', '', '', '', '', 'tri patra', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1308, '', '', '', '', '', '', '', 'pt. indonesia power up bali by pas ngurahrai 535', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1309, 'jimmy', '', '', '', '', '', '', 'pt.tricipta dianika ltc', '', '0857 9317 1799', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1310, 'Bpk. Ardji Rahardjo', '', '', '', '', '', '', '', '', '081212661935', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1311, 'ALDY', '', '', '', '', '', '', '', '', '085370044488', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1312, 'Bpk. Yulius', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1313, 'HISYAM', '', '', '', '', '', '', '', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1314, 'yosua', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1315, '', '', '', '', '', '', '', 'm.h. power', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1316, 'ANTON', '', '', '', '', '', '', '', '', '085280890890', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1317, 'SCBD SUIT', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1318, 'Bpk. Surya', '', '', '', '', '', '', 'PT. Karya Atap', '', '081284152341', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1319, 'TRIMEGA SARANA GASINDO', '', '', '', '', '', '', '', '', '', '', 'JL. GADING INDAH UTARA III\r\nBLOK NH4 NO.7\r\nKELAPA GADING\r\nJAKARTA 14250', '', 4, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1320, '', '', '', '', '', '', '', 'wira sentosa makmur', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1321, '', '', '', '', '', '', '', 'cipta agung', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1322, 'Bpk. Guruh', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:07', '2020-07-29 12:15:07', 1, 1),
(1323, 'Bpk agus dwiyono', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1324, '', '', '', '', '', '', '', 'Post Energy', '', '', '', 'Plaza Permata Building 8TH Suite 801\r\nJL.MH Thamrin No.57', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1325, '', '', '', '', '', '', '', 'Teguh Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1326, '', '', '', '', '', '', '', 'sissy', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1327, '', '', '', '', '', '', '', 'gma', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1328, 'H. ridwan', '', '', '', '', '', '', '', '', '', '', '', 'customer santo ', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1329, 'Surya Jaya Teknik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1330, '', '', '', '', '', '', '', 'PT. Bangun Persada Tehknik Energy', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1331, 'Bpk yogi', '', '', '', '', '', '', 'mitra', '', '021 9288 7477 -0815 1668 915', '', 'gedung murata blok f 39 kios no 12 ', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1332, 'JAMESON', '', '', '', '', '', '', '', '', '021-64716808', '', 'SUNTER', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1333, 'JONY/AMU', '', '', '', '', '', '', '', '', '087786745263', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1334, 'Bpk Cendro', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1335, 'Bpk Rahmat', '', '', '', '', '', '', 'PT. DEA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1336, 'Bpk junaidi', '', '', '', '', '', '', 'PT. SBS', '', '081331594123', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1337, 'Bp.Nino', '', '', '', '', '', '', 'OSCT', 'nino@osct.com', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1338, 'Budiyanto', '', '', '', '', '', '', 'Pt.Kwikindo Cahaya Prima', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1339, 'Bpk anhar gh', '', '', '', '', '', '', 'PT. SDA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1340, 'Andika', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1341, 'Bpk Johanes', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1342, 'MARSONO', '', '', '', '', '', '', '', '', '08122688991', '', 'KALIRAHMAN RT 02/05 \r\nGANDEKAN \r\nJEBRES\r\nSURAKARTA', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1343, 'SYARIF', '', '', '', '', '', '', '', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1344, 'PT PICO REKSA PRATAMA', '', '', '', '', '', '', '', '', '5910810', '', 'TANGERANG', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1345, 'AGUS', '', '', '', '', '', '', '', '', '08129451946', '', 'TANGERANG', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1346, 'Bpk. Kusnadi', '', '', '', '', '', '', '', '', '082118678753', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1347, '', '', '', '', '', '', '', 'Toko Fiktor Teknik', '', '087888422355', '', 'lt ug c 9 no 2 ', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1348, '', '', '', '', '', '', '', 'Bpk Isnayini', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1349, '', '', '', '', '', '', '', 'AERODILI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1350, 'Bp.Armen', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1351, '', '', '', '', '', '', '', 'PT. Utama Telekomindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1352, 'Bu suci', '', '', '', '', '', '', 'Pt pertalahan arnebatara', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1353, '', '', '', '', '', '', '', 'PT. Satu Nusa Biru', '', '089637292551', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1354, 'HALIDE', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1355, 'ridwin', '', '', '', '', '', '', '', '', '0811757669', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1356, 'Bpk Ali Amin', '', '', '', '', '', '', 'KM Pusri Indonesia I', '', '081373648325', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1357, 'Bpk Ali Amin', '', '', '', '', '', '', 'Koperasi Karyawan Pusri', '', '081373648325', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1358, 'dian', '', '', '', '', '', '', 'pt surveyor indonesa', '', '0878 7806 5708', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1359, 'renaldy', '', '', '', '', '', '', '', '', '0813 8713 7768', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1360, '', '', '', '', '', '', '', 'viho safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1361, 'BENI ARIP SUTOPO', '', '', '', '', '', '', '', '', '', '', 'GEDUNG TAMANSARI SEMANGGI\r\nLT 2 TOWER A\r\nJL AKRI NO.134\r\nKARET, SEMANGGI', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1362, 'DAILAMIANUS', '', '', '', '', '', '', '', '', '08121919513', '', 'KANTOR BPTD WILAYAH XVI\r\nKALIMANTAN TENGAH\r\nJL. RAJAWALI VII NO.2\r\nPALANGKARAYA', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1363, 'Bpk. Maman', '', '', '', '', '', '', '081380371675', '', '081380371675', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1364, '', '', '', '', '', '', '', 'Trans Ocean Maritim', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1365, '', '', '', '', '', '', '', 'PT. Hydra Asia Energy', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1366, 'Bpk. Benny', '', '', '', '', '', '', 'PT. Tribestari Karya Agung', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1367, 'CASH', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1368, '', '', '', '', '', '', '', 'PD. SLAMET', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1369, 'Bpk. Haryanto', '', '', '', '', '', '', '', '', '081294774424', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1370, '', '', '', '', '', '', '', 'Pt.Supraco Mitra energi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1371, 'SUNTI', '', '', '', '', '', '', '', '', '+62 811 744406', '', 'JAMBI', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1372, 'BANDARA WARUKIN', '', '', '', '', '', '', '', '', '', '', 'KALIMANTAN TENGAH', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1373, 'Bu Sendy', '', '', '', '', '', '', '', '', '081318116688', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1374, 'Margaret', '', '', '', '', '', '', 'Sumber Makmur Marine,Pt', '', '', '', 'jalan raja ampat no 9, kampung baru,\r\nsorong 98413 \r\npapua barat ', 'kirim via expedisi catelya express (fitri)\r\nruko bojong permai indah 47d\r\nrawa buaya \r\n5804995\r\n\r\ncustomer : ERLIN/ MAJU SUKES MANDIRI ', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1375, 'alex', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1376, '', '', '', '', '', '', '', 'PT. Mina Fajar Abadi', '', '', '', '', 'bpk ayi', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1377, 'Veripa Teknik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1378, '', '', '', '', '', '', '', 'PT. Asta Bumi Cipta', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1379, '', '', '', '', '', '', '', 'Indo Jaya Supplindo', '', '021 2268 2171', '', 'gf 2 b.19 no 1', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1380, '', '', '', '', '', '', '', 'cv mkm', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1381, 'AFRUL,MR', '', '', '', '', '', '', '', '', '', '', 'ACEH ', 'SANTO', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1382, 'Bpk. Daniel', '', '', '', '', '', '', '', '', '081316335370', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1383, '', '', '', '', '', '', '', 'PT.CRI Fluid Systems', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1384, '', '', '', '', '', '', '', 'PT EGA MEKINKA PRATAMA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1385, 'pak hari chelsea', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1386, 'Bpk Rifky', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1387, '', '', '', '', '', '', '', 'PT PELAYARAN GLOBAL LINTAS', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1388, 'Bpk. Jajang', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1389, 'kreatil', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1390, 'Ibu.Nicole', '', '', '', '', '', '', 'Pt.Ridho Wira Pratama', '', '', '', 'Pluit Raya', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1391, '', '', '', '', '', '', '', 'Pt. Tri Jaya Cemerlang', '', '', '', '', 'SANTO ', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1392, 'golden teknik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1393, 'iwan', '', '', '', '', '', '', 'warga katamaran', '', '', '', 'PIK', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1394, 'GTV', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1395, 'EDI', '', '', '', '', '', '', 'TOKO', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1396, 'Bpk Haryanto', '', '', '', '', '', '', 'Waskita', '', '', '', 'JAKARTA', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1397, 'ibu rahmah', '', '', '', '', '', '', 'ud reyhan army collection', '', '0812 8493 4475 / 0818 0629 916', '', 'jl.gunung sahari raya no 1, pademanganbarat, jakarta utara \r\nmangga 2 square blok b lantai 2 no 163', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1398, 'ENDANG', '', '', '', '', '', '', '', '', '', '', 'CIKARANG', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1399, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM PROJECT TEAM SINOHYDRO SECTION 2', '', '', '', 'DI PANJAITAN KAV 9 - 10 \r\nCIPINANG CEMPEDAK, JATINEGARA\r\nJAKARTA TIMUR DKI JAKARTA ', '', 5, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1400, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM', '', '', '', 'DI PANJAITAN KAV 9 - 10 \r\nCIPINANG CEMPEDAK, JATINEGARA\r\nJAKARTA TIMUR DKI JAKARTA ', 'SE1', 5, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1401, '', '', '', '', '', '', '', 'PT INDO CHANGHAI KONSTRUKSI', '', '', '', 'GOLF LAKE RESIDENCE RUKAN PARIS A BLOK NO.21 RT/010 RW/014\r\nKELURAHAN CENGKARENG\r\nJAKARTA', '', 5, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1402, '', '', '', '', '', '', '', 'PT GUNUNG MAS JAYA INDAH', '', '', '', 'JL. ADITYAWARMAN  KAV 55 KEBAYORAN BARU\r\nJAKARTA 12160', '', 5, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1403, 'Bpk. Julio', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1404, '', '', '', '', '', '', '', 'Betawi Teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1405, 'bp sapto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1406, 'cobain tes', '', '', '', '', '', '', 'cobain lg', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1407, 'Ibu Iryani', '', '', '', '', '', '', 'Sulawesi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1408, '', '', '', '', '', '', '', 'Proyek Wismaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1409, '', '', '', '', '', '', '', 'Abadi Utama Teknik', '', '0856 9139 833, 0813 1781 5151', '', 'LTC Lt. GF2 Blok C28 No.9', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1410, '', '', '', '', '', '', '', 'Media Mitra Pratama', '', '0813 1120 2776, 0877 3159 0462', '', 'LTC Lt.1 Blok B21 No.1', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1411, '', '', '', '', '', '', '', 'PT. Orindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1412, 'Mba Ambar', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1413, '', '', '', '', '', '', '', 'Pt HDS', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1414, 'Bpk. Acin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1415, 'Ibu.Fitri', '', '', '', '', '', '', 'Cv Tunggal Karsa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1416, 'MIDI', '', '', '', '', '', '', '', '', '081314227796', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1417, '', '', '', '', '', '', '', 'PT.JEEVESINDO GEMILANG', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1418, '', '', '', '', '', '', '', 'mitra bersama sejahtra', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1419, 'Bpk. Yoga', '', '', '', '', '', '', 'PT. Conspec', '', '', '', '', 'gosave  145000\r\n', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1420, 'Jupe', '', '', '', '', '', '', 'Bintang Tunas Jaya', '', '08561317531', '', 'HWI Lt.2 BKS 40', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1421, '', '', '', '', '', '', '', 'sumber utama', '', '', '', 'b.lampung ', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1422, 'Bpk. Rudi Lating', '', '', '', '', '', '', '', '', '081240789444', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1423, 'Bpk. Yana', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1424, 'winny', '', '', '', '', '', '', 'Sentralnet', '', '', '', 'lt 2 blok c.16 no1', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1425, 'Bp.Arga (Muara Karang )', '', '', '', '', '', '', 'PLN UPP PJBB2', '', '08119938193', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1426, 'Ibu Acin', '', '', '', '', '', '', 'HWI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1427, 'Bpk. Firman', '', '', '', '', '', '', '', '', '081313995667', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1428, 'TAUFIK', '', '', '', '', '', '', '', '', '081333554417', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1429, 'Bpk Glen', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1430, 'Bp.Indra (Palembang)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1431, '', '', '', '', '', '', '', 'shu fa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1432, 'Bpk. Dayat', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1433, '', '', '', '', '', '', '', 'Pt ads', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1434, '', '', '', '', '', '', '', 'pt fti', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1435, 'Mr.Ryuichi Sato', '', '', '', '', '', '', 'Pt.Mitsubishi Corporation', '', '', '', 'Sentral Senayan II  18-19th Floor\r\nJl Asia Africa No.8\r\nJakarta 10270', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1436, 'Bpk Irvan', '', '', '', '', '', '', 'PT. Kontrol Indo Raya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1437, 'Bp.Andi Christ', '', '', '', '', '', '', '', '', '081316997728', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1438, 'Bpk Suryono', '', '', '', '', '', '', 'Waskita', '', '', '', 'LAMPUNG', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1439, 'WIN ELECTRIC', '', '', '', '', '', '', '', '', '', '', 'LTC LT UG', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1440, 'Bpk Sufyan AS', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1441, 'man diesel', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1442, '', '', '', '', '', '', '', 'PT SIDOMULYO SELARAS', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1443, 'Bp.Rawindra', '', '', '', '', '', '', 'Pt.Bintang Energi Pratama', '', '', '', 'Rasuna Office Park  Unit RO-01                                                                              \r\nKomplek Rasuna Epicentrum               ', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1444, 'Bpk. Risman', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1445, '', '', '', '', '', '', '', 'PT. DSI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1446, 'Rikky', '', '', '', '', '', '', 'PT.Instrucom', 'rikkymanik@gmail.com', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1447, 'tesgusmates', '', '', '', '', '', '', 'tesgusmates', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1448, 'gusmavin test', '', '', '', '', '', '', 'PT.jaling jaling', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1449, '', '', '', '', '', '', '', 'Samugara', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1450, 'Bpk Ali', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1451, 'Mr.Wang', '', '', '', '', '', '', 'Pt.Proteksindo Aman Sejahtera', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1452, 'SMS', '', '', '', '', '', '', '', '', '', '', 'LT UG', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1453, 'Koh Rudi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1454, 'Bpk. Fendi', '', '', '', '', '', '', '', '', '085286349235', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1455, 'Bpk. Denhard', '', '', '', '', '', '', '', '', '085270564488', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1456, '', '', '', '', '', '', '', 'PT. Karya Rachman Makmur', '', '081388171754', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1457, '', '', '', '', '', '', '', 'tricipta dinamika ltc', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1458, '', '', '', '', '', '', '', 'Pt.Wira Gulfindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1459, 'bpk billy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1460, '', '', '', '', '', '', '', 'PT INKASA JAYA ALUMUNIUM', '', '', '', 'JL KERTAJAYA NO.150 \r\nSURABAYA', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1461, 'tes disini coba', '', '', '', '', '', '', 'tes disini coba', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1462, 'bpk Said', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1463, '', '', '', '', '', '', '', 'PT TYAES', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1464, '', '', '', '', '', '', '', 'SAP', '', '081288161600', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1465, 'bu aini', '', '', '', '', '', '', 'Tk metro senen', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1466, '', '', '', '', '', '', '', 'PT. AKK TELECOM', '', '08121003805', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1467, 'ibu neti', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1468, '', '', '', '', '', '', '', 'pt jebsen & jessen teknologi indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1469, '', '', '', '', '', '', '', 'ghawth society', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1470, 'Masjid Al.hamdal', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1471, 'Bp.Anthony', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1472, '', '', '', '', '', '', '', 'Stasiun Bearing', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1473, '', '', '', '', '', '', '', 'PT. JGC INDONESIA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1474, '', '', '', '', '', '', '', 'Dunia Flowmeter', '', '', '', 'lt. UG blok C 7 no.3', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1475, 'SINAR ABADI MANDIRI', '', '', '', '', '', '', '', '', '', '', 'GF2\r\nLTC GLODOK', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1476, 'Ibu.Astrid', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1477, 'Bpk Ari', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1478, 'bpk buang dwiyono', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1479, 'bpk suratman', '', '', '', '', '', '', '', '', '081311091122', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1480, '', '', '', '', '', '', '', 'Trimitra Wisesa Abadi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1481, 'Ibu.Sabrina', '', '', '', '', '', '', 'Pt.Amorojo Putra Pewaris', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1482, '', '', '', '', '', '', '', 'PT. HYDRO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1483, '', '', '', '', '', '', '', 'Toko Alex', '', '', '', 'Kapuk', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1484, 'Anugrah Jaya Mandiri', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1485, 'bu ita radja bordir', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1486, '', '', '', '', '', '', '', 'pt.sumberjaya Tekstrabadi', '', '021-6242154', '', 'UG B7 No.6', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1487, 'TOKO CTM', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1488, 'Bpk. Dicky', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1489, 'Bpk. Herry', '', '', '', '', '', '', 'PT. Trivindo', '', '087884715405', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1490, '', '', '', '', '', '', '', 'PT. UNITED METAL INDONESIA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1491, '', '', '', '', '', '', '', 'PT. Utama Telekomindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1492, 'PANJAITAN', '', '', '', '', '', '', 'PT FEDERAL KARYATAMA', '', '08129438120', '', 'JL. AUSTRALIA II KAV R1 KIEC \r\nCILEGON\r\nKEL. WARNASARI \r\nKEC. CITANGKIL\r\nBANTEN 42443', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1493, 'yuliana', '', '', '', '', '', '', '', 'yuliana@slickbar.com', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1494, '', '', '', '', '', '', '', 'PT. Anugrah spectra', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1495, 'Suparto.P', '', '', '', '', '', '', 'Putra Siduasaudara Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1496, '', '', '', '', '', '', '', 'PT. Cipta Total Solusindo', '', '081298160335', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1497, 'Bpk Iman PS', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1498, 'Bpk dede', '', '', '', '', '', '', 'lengan pendek', '', '081261359963', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1499, 'DEDE', '', '', '', '', '', '', '', '', '081511237604', '', '', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1500, 'Bpk Heru Manto', '', '', '', '', '', '', '', '', '081213910366', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1501, '', '', '', '', '', '', '', 'PT. Esa Karunia Abadi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1502, '', '', '', '', '', '', '', 'PT. Halahati Naposobulung', '', '', '', '', 'Bpk ayi', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1503, 'pjm', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1504, '', '', '', '', '', '', '', 'pt indo bangun wahana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1505, 'TMS', '', '', '', '', '', '', '', '', '', '', 'TANGERANG', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1506, 'PT DELTA', '', '', '', '', '', '', '', '', '', '', 'JAKARTA', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1507, 'gemilang safety', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1508, 'ANOM', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1509, 'BPK DONI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1510, '', '', '', '', '', '', '', 'TOKO LESTARI SAFETINDO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1511, 'Bpk Ramalan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1512, 'Bpk Wandi', '', '', '', '', '', '', '', '', '082351705353', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1513, 'PT PUTRA PERDANA', '', '', '', '', '', '', '', '', '021-6267979', '', 'LTC GLODOK \r\nLT.GF1 R 19', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1514, '', '', '', '', '', '', '', 'PT. Global Sukses Selalu', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1515, 'Bpk Zulkarnen', '', '', '', '', '', '', '', '', '08126986783', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1516, 'Desi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1517, 'Bp.Muslih', '', '', '', '', '', '', 'Pt.Triguna Internusa Pratama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1518, 'Agan', '', '', '', '', '', '', 'Glodok Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1519, '', '', '', '', '', '', '', 'PT. Toyota Boshoku Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1520, '', '', '', '', '', '', '', 'PT. LONG XIN INDONESIA (PT. LXI)', 'vnchenyu@gmail.com', '021 - 2201 8427', '', 'Jl. Ecopolis boulevard Utara VE 05/67\r\nTangerang - Banten 15710', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1521, 'Ibu Ria', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1522, 'eko', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1523, 'PT. SEAN ALFA NUSANTARA', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1524, 'davindo surya pratama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1525, 'FABILA', '', '', '', '', '', '', '', '', '081377682075', '', '021-29619489\r\nSUNTER\r\nJAKARTA', '', 4, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1526, 'pt santani agro perkasa', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1527, 'Ibu. Amelia', '', '', '', '', '', '', 'PT. BJM GLOBAL INDONESIA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1528, 'BPK. TUSLAM', '', '', '', '', '', '', '', '', '081280327988', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1529, '', '', '', '', '', '', '', 'petro merin', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1530, '', '', '', '', '', '', '', 'THC', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1531, 'Bpk. Ady (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1532, 'Bpk. Tisco (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1533, 'Bpk. Budi (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1534, 'Bpk Johner', '', '', '', '', '', '', '', '', '081311103116', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1535, 'Bpk. Purwedi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:08', '2020-07-29 12:15:08', 1, 1),
(1536, 'manggala utama lt 2', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1537, 'Ibu.Nisrina Nurul', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1538, 'ANDRY', '', '', '', '', '', '', 'MAJU BERSAMA PERSADA DAYAMU (MBP)', '', '', '', 'TAMAN PALEM LESTARI B/18 NO 37', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1539, '', '', '', '', '', '', '', 'Pt. Azora Reka Pratama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1540, '', '', '', '', '', '', '', 'Kosan Niaga Makmur', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1541, 'HENDRA IRAWAN', '', '', '', '', '', '', '', '', '0811550930/085247390000', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1542, '', '', '', '', '', '', '', 'PT. Biru International', '', '0895350985305', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1543, '', '', '', '', '', '', '', 'Suplindo Teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1544, 'Dessy Hermayanti', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1545, 'Bpk. Richi (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1546, 'pt nhp', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1547, 'Bpk Iwan', '', '', '', '', '', '', 'PT. IDETO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1548, '', '', '', '', '', '', '', 'PT.CHI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1549, '', '', '', '', '', '', '', 'Cv Roda Karya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1550, 'Ibu Lina', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1551, 'pt maslim pratama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1552, '', '', '', '', '', '', '', 'Pt.Rianti Chemindo Perkasa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1553, 'pt kosan niaga makmur', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1554, 'pt sanvu metal industri', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1555, '', '', '', '', '', '', '', 'PT. Join Teknik Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1556, 'Ibu.Aita', '', '', '', '', '', '', '', '', '087876259779', '', 'Pengadegan selatan', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1557, 'Bpk Yunus (ps)', '', '', '', '', '', '', '', '', '08118163075', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1558, 'gunung moria megaprima', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1559, 'roby', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1560, '', '', '', '', '', '', '', 'Pt.Nusantara Hamparan Mineral', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1561, 'doni', '', '', '', '', '', '', 'wika gedung', '', '08111819903', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1562, 'Mr.Kang', '', '', '', '', '', '', 'Pt.Phing Xiang', '', '085380888288', '', 'BENGKULU ', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1563, 'Joselindo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1564, 'Ibu Titin (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1565, 'Bpk Rangga', '', '', '', '', '', '', '', '', '087877122201', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1566, 'NURUL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1567, 'NURUL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1568, 'pt bina arta mulia sejahtra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1569, 'Dayat', '', '', '', '', '', '', 'Jaya Buana', '', '081310540101', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1570, 'bp dede/aan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1571, 'Slamet', '', '', '', '', '', '', 'Pt.Sumber Kencana Patria', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1572, '', '', '', '', '', '', '', 'PT Inco Global Nusantara', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1573, 'MR HONG', '', '', '', '', '', '', 'BUT. GUANDONG ELECTRIC POWER FIRST ENGINEERING BEREAU OF CHINA ENERGY ENGINEERING GROUP CO.,LTD', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1574, 'TK.Aneka Jaya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1575, 'DIKA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1576, 'Bpk Kristanto', '', '', '', '', '', '', 'PT. Paku Bumi Semesta', '', '08161851074', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1577, 'Anugerah Teknik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1578, 'Bpk Odang Priatna', '', '', '', '', '', '', 'PT Daka Megaperkasa', '', '081294430066', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1579, 'maju mandiri', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1580, 'Bpk setiawan', '', '', '', '', '', '', 'PT. Cahaya pasifik utama', '', '081272126229', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1581, 'Bpk. Idil Fitri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1582, 'Sony DS (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1583, 'Bpk deny', '', '', '', '', '', '', '', '', '087881135988', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1584, 'Ibu. Yudith(PS)', '', '', '', '', '', '', 'PT. Yekapepe', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1585, 'BAJA UTAMA TEKNIK(PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1586, 'SINOHYDRO', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1587, 'Bpk.Iwan Liando (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1588, 'PD. LESTARI', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1589, 'katamaran indah PIK', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1590, 'pt furukawa indomobil battery sales', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1591, 'ahmat', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1592, 'Ibu Erni Susilawati', '', '', '', '', '', '', 'PT Multi Fabrindo Gemilang', '', '081288061115', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1593, 'Ipranta', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1594, 'cahaya mas cemerlang', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1595, '', '', '', '', '', '', '', 'Bethesda', '', '', '', 'Sorong', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1596, 'Aprilia', '', '', '', '', '', '', 'JD.ID', '', '081314598561', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1597, '', '', '', '', '', '', '', 'PT. ERSALINDO BANGUN LESTARI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1598, 'Bpk Hasyim', '', '', '', '', '', '', '', '', '081293253495', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1599, '', '', '', '', '', '', '', 'SAFETY INDONESIA', '', '', '', 'LTC Lantai 1 Blok B17 No.7', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1600, 'PAK NANA', '', '', '', '', '', '', 'HEAVEN', '', '', '', 'PLUIT\r\nRUANGAN ME', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1601, 'ARENA TEHNIK', '', '', '', '', '', '', '', '', '', '', 'LT 2\r\nLTC GLODOK', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1602, '', '', '', '', '', '', '', 'PT JALI INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1603, 'Bpk Ayung', '', '', '', '', '', '', '', '', '081318568830', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1604, '', '', '', '', '', '', '', 'TOKO DTM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1605, 'YUDI', '', '', '', '', '', '', 'PT MTI', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1606, 'Ibu Citra', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1607, 'Priyani Ilyas', '', '', '', '', '', '', 'PT. Lgrande Global Teknologindo', 'priyani.ilyas@lgt-indonesia.com', '+62 812 6625 2486', '', '', 'pusat safety', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1608, 'sumalok', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1609, 'pt delta global teknologi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1610, '', '', '', '', '', '', '', 'PT. NURA CITRA GROUP', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1611, '', '', '', '', '', '', '', 'PT. ATLANTIK ALAMI INDUSTRI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1612, 'SUMMERECON GD', '', '', '', '', '', '', '', '', '081511589062', '', 'SERPONG', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1613, 'RIVA', '', '', '', '', '', '', '', '', '08118404995', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1614, 'Bp. Nanang', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1615, 'Bpk Galih Nugroho', '', '', '', '', '', '', '', '', '085237710160', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1616, 'Bpk Yunus Nurhakim', '', '', '', '', '', '', 'CV. Obsvs.co', '', '081288741919', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1617, '', '', '', '', '', '', '', 'PT Tencore Metal Indonesia', '', '', '', '', '', 5, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1618, 'Bpk Johan', '', '', '', '', '', '', 'Global Intertama', '', '08159977754', '', '', 'Supplier PT.BERCA', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1619, 'Bpk. Usman', '', '', '', '', '', '', 'PT. Megah Sarana Beton', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1620, 'Bpk. Fadli', '', '', '', '', '', '', '', '', '082112962560', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1621, 'marwan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1622, 'SRI MORTIMER', '', '', '', '', '', '', '', '', '08111775789', '', 'JL BENDA LANGGAR RT 006/04 NO.4C\r\nCILANDAK TIMUR\r\nPASAR MINGGU\r\nJKT SELATAN 12560', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1623, 'Ibu Lia', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1624, 'umar', '', '', '', '', '', '', '', '', '085283008771', '', 'ruko grand royal residence no.rk.10, kel.karang malang,kec.indramayu,kab.indramayu', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1625, 'Bp Budiyanto', '', '', '', '', '', '', 'SCM', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1626, 'BETAWI TEHNIK', '', '', '', '', '', '', '', '', '', '', 'LTC GLODOK \r\nLANTAI UG BLOK C5 NO.1', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1627, 'Pak Yanie', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1628, 'Ibu Liche', '', '', '', '', '', '', 'Pt.IFFCO', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1629, 'Herlin', '', '', '', '', '', '', 'Pt.Poyumaru', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1630, 'mpok nita', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1631, '', '', '', '', '', '', '', 'PT TATAR KERTABUMI', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1632, 'Ci Ayen', '', '', '', '', '', '', '', '', '0818 600 699', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1633, 'Ibu Yusthina (ps)', '', '', '', '', '', '', '', 'rainaugerah15@gmail.com', '+62 821-9945-3551', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1634, 'Bp.Darwin', '', '', '', '', '', '', 'Pt.Adhi Karya Guna Nusantara', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1635, 'pt.golobal', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1636, 'lia', '', '', '', '', '', '', 'pt global total lubrindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1637, 'bu sari', '', '', '', '', '', '', 'pt tiara wahana bersama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1638, 'ERNA', '', '', '', '', '', '', '', '', '081254986363', '', 'RUKO BALIKPAPAN BARU BLOK B3 NO.25\r\nBALIKPAPAN', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1639, 'rony', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1640, '', '', '', '', '', '', '', 'PT. Berkat Nusantara Abadi', '', '', '', '', 'ayi', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1641, 'Bpk aditya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1642, 'SUMMERECON AGUNG', '', '', '', '', '', '', '', '', '', '', 'KELAPA GADING', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1643, 'Bpk. Firmanb', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1644, 'faulis adrianto riau', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1645, 'Doni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1646, 'Bapak Ika', '', '', '', '', '', '', '', '', '0813 1920 1861', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1647, 'Edi', '', '', '', '', '', '', 'Rezeki Surya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1648, 'Pak oni', '', '', '', '', '', '', '', '', '081344854730', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1649, '', '', '', '', '', '', '', 'PT. ANUGRAH MANDIRI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1650, 'gautama siskandar', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1651, '', '', '', '', '', '', '', 'Pt.Anugerah Langit Nusantara', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1652, 'BOWO', '', '', '', '', '', '', '', '', '081290945077', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1653, '', '', '', '', '', '', '', 'Marconi Daya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1654, 'Nezo Andriko', '', '', '', '', '', '', '', 'nezoandriko@gmail.com', '085274152208', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1655, 'Ibu Lisa', '', '', '', '', '', '', '', '', '081210494765', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1656, '', '', '', '', '', '', '', 'Bintang Rasa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1657, 'pt tcm', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1658, 'Ibu Tasha', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1659, '', '', '', '', '', '', '', 'PT. ONI UTAMA SUKSES', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1660, '', '', '', '', '', '', '', 'TOKO GLOBAL TECH', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1661, 'Bpk Supri', '', '', '', '', '', '', 'PT. Arga Arta Utama', '', '087876386321', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1662, '', '', '', '', '', '', '', 'Pt.Tencore Metal Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1663, 'herry abri', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1664, 'Ibu Nova Risandi', '', '', '', '', '', '', 'PT. Han Jaya Promosi', '', '081355986648', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1665, '', '', '', '', '', '', '', 'pt.darma putra buana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1666, '', '', '', '', '', '', '', 'Pt.Dharma Putra Buana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1667, 'pt sumber daya mandiri (sdm)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1668, 'fandi', '', '', '', '', '', '', '', '', '081617353938', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1669, 'Ibu.Ben', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1670, 'Bpk Bima', '', '', '', '', '', '', 'PT. TECHNODRIVES', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1671, 'didi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1672, '', '', '', '', '', '', '', 'PT. Magna Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1673, 'ibu ida marbun', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1674, 'Bpk Rendy Sesario', '', '', '', '', '', '', 'PT. Teknik Solusi Utama', '', '081319362029', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1675, 'Mail', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1676, 'pt pelayaran salam bahagia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1677, 'EDWIT', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1678, '', '', '', '', '', '', '', 'PT. Citra Indobeton Abadi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1679, '', '', '', '', '', '', '', 'mitra', '', '62203858', '', 'glodok plaza gedung murata blok f 39 kios no 12', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1680, '', '', '', '', '', '', '', 'CV. Sahabat Teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1681, '', '', '', '', '', '', '', 'Gautama Putra Abadi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1682, 'Bpk Hardi Purnomo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1683, 'Ibu Dita', '', '', '', '', '', '', 'PT. REKAYASA ENGINEERING', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1684, 'DECKY', '', '', '', '', '', '', '', '', '08128611080', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1685, '', '', '', '', '', '', '', 'PT SUPER ANDALAS STEEL', 'purchasing@superandalassteel.com', '', '', '', '', 5, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1686, '', '', '', '', '', '', '', 'Toko Wijaya Global', '', '', '', 'LT 2 ', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1687, '', '', '', '', '', '', '', 'Toko Inti Jaya', '', '', '', 'LT. UG ', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1688, 'Bp.Muliadi', '', '', '', '', '', '', 'Pt.IndoPangan Sentosa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1689, 'PT DARMA ABADI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1690, 'sumedis diesel', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1691, 'Puspa Kanden', '', '', '', '', '', '', 'PT.Indo-Bharat Rayon', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1692, '', '', '', '', '', '', '', 'aten jaya electric', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1693, '', '', '', '', '', '', '', 'Toko SANTEX', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1694, 'Bpk Adam', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1695, 'Bpk Arfin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1696, '', '', '', '', '', '', '', 'PT. ARENA TEKNIK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1697, 'INDRA', '', '', '', '', '', '', '', '', '08561801689', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1698, 'hosana bordir', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1699, 'IMAN', '', '', '', '', '', '', '', '', '08151620678', '', 'JL. PAUS DALAM \r\nKOMPLEK PERTAMINA\r\nNO. 48\r\nRAWAMANGUN\r\nJAKARTA TIMUR', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1700, 'PT Adyawinsa Telecommunication & Electrical', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1701, 'Bpk. Firdaus', '', '', '', '', '', '', 'PT. Kosindo', '', '085887912717', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1702, '', '', '', '', '', '', '', 'Pt.Garda Tujuh Buana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1703, 'Amanda', '', '', '', '', '', '', 'cv young water techonolgy', '', '087880160881', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1704, '', '', '', '', '', '', '', 'PT. kU', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1705, 'PT SADEWA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1706, 'PT SINAR JAYA', '', '', '', '', '', '', '', '', '', '', 'CITERES\r\nBANTEN', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1707, 'DEDI WONG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1708, 'Edi Supriadin', '', '', '', '', '', '', 'Pt.Inti Graha Sembada', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1709, 'Ibu Syalmi Canra', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1710, 'Bpk Irwan', '', '', '', '', '', '', 'PT. Adicipta', '', '085814375311', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1711, 'OLIN', '', '', '', '', '', '', '', '', '085780280642', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1712, 'Bpk. Lewi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1713, 'Bpk Zulfadli', '', '', '', '', '', '', '', '', '081373761188', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1714, 'Belen', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1715, 'Tatang', '', '', '', '', '', '', '', '', '', '', 'Bandung', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1716, 'Ibu Sartika(PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1717, 'Bpk Adip', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1718, 'UD TMS', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1719, 'PT OMEGA TRAININDO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1720, 'Ibu Ike Oktafianti', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1721, 'Bpk Wahyu', '', '', '', '', '', '', 'PT. Vigano', '', '087878354992', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1722, 'Bapak Iday', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1723, 'Bpk Sumantari', '', '', '', '', '', '', 'PT. Vikara Indoprima Perkasa', '', '08128382813', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1724, 'Bpk. Fery', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1725, '', '', '', '', '', '', '', 'PT. Tunas Segara Cahya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1726, 'Nisa', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1727, 'PT SURYA ATHA SOLUSINDO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1728, 'HAIS AYUWA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1729, 'PT AKE', '', '', '', '', '', '', '', '', '021-64717850', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1730, '', '', '', '', '', '', '', 'FARASINDO', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1731, 'Bpk Azmy', '', '', '', '', '', '', 'PT. DVM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1732, 'alfinda nucifera', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1733, '', '', '', '', '', '', '', 'Toko Sinar Mas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1734, 'Bpk. Gugum', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1735, 'Ibu Fahri (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1736, 'Sulistyo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1737, '', '', '', '', '', '', '', 'PT. KELSRI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1738, '', '', '', '', '', '', '', 'Wijaya Global Tama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1739, 'IWAN', '', '', '', '', '', '', '', '', '08122481028', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1740, 'pt anugrah tangkas', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1741, '', '', '', '', '', '', '', 'Teras Benhil', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1742, 'Edy Susanto', '', '', '', '', '', '', '', '', '', '', 'Solo Jawa Tengah', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1743, '', '', '', '', '', '', '', 'Makmur Jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1744, 'Dwi Cahya', '', '', '', '', '', '', 'Pt Petro Daya Energi', '', '021-22474504', '', 'Jl.Bangunan Barat No.18/36 Kel.Kayu Putih\r\nKec.PuloGadung\r\nJakarta 13210', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1745, 'Bp.Joni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1746, 'Megi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1747, '', '', '', '', '', '', '', 'PT Kuanfu Cahaya Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1748, 'metro teknik G (blustru)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1749, 'Agus Irpan', '', '', '', '', '', '', 'Pt.Trimitra Cipta Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1750, 'ROSADI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1751, '', '', '', '', '', '', '', 'PT Radius allkindo elektrik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1752, 'PT PANUTAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1753, 'pt hirdy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1754, 'agus bekasi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1755, 'SITA', '', '', '', '', '', '', '', '', '', '', 'GF 2 BLOK A', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1756, 'Ibu Mayang', '', '', '', '', '', '', 'PT. Jaya Sakti Mandiri Unggul', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1757, 'pt GSM', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1758, '', '', '', '', '', '', '', 'PT. EDS', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1759, 'Zacky', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1760, 'Bpk. Arif mendrofa (PS)', '', '', '', '', '', '', 'DLN', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1761, 'Bpk Bambang', '', '', '', '', '', '', 'PT RRA Aeropolis', '', '081296010755', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1762, 'Bu Anissa Asril', '', '', '', '', '', '', 'PT Enindo', '', '081294949176', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1763, 'Rifki', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1764, 'Bpk Hendi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1765, 'Bpk. Syarif (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1766, 'kharisma cita tunggal', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1767, '', '', '', '', '', '', '', 'Detiga', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1768, 'Bpk Andri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1769, '', '', '', '', '', '', '', 'Toko Duta Perkakas', '', '', '', 'Glodok jaya lt 4 blok D no 18 ', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1770, 'Bpk Erwin semi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1771, '', '', '', '', '', '', '', 'Direktorat Kerjasama Dan Pemberdayaan', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1772, '', '', '', '', '', '', '', 'CV. DUA SATU BADAK', '', '', '', 'BELITUNG', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1773, '', '', '', '', '', '', '', 'Teratai Indah', '', '', '', 'Tangerang', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1774, 'Ibu Dini', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1775, 'pt.adlerindo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1776, 'ABDUL KODIR', '', '', '', '', '', '', 'PT BLUE POWER INDONESIA', '', '', '', 'JL. KAVLING POLRI BLOK G NO.48\r\nJAGAKARSA\r\nJAKARTA SELATAN\r\n12620', '', 4, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1777, 'Bpk Chengho', '', '', '', '', '', '', 'PBG', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1778, 'Bpk Sandi Rusliandi', '', '', '', '', '', '', '', '', '082214700500', '', '', '', 1, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1779, 'haris', '', '', '', '', '', '', 'djm', '', '087783940116', '', '', '', 2, 'aktif', '2020-07-29 12:15:09', '2020-07-29 12:15:09', 1, 1),
(1780, '', '', '', '', '', '', '', 'ASSAKIBE', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1781, 'Bpk. Asep (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1782, 'Kuswanto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1783, '', '', '', '', '', '', '', 'Tricipta Dinamika', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1784, '', '', '', '', '', '', '', 'Pt.Egamekinka Pratama', '', '', '', 'Jl.P.Jayakarta 141 Blok B No.7', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1785, 'Bpk Muharri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1786, 'Bpk. Tanto sutiyanto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1787, '', '', '', '', '', '', '', 'PT SINDAWAR PERSADA SEJAHTERA', '', '08175071975', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1788, '', '', '', '', '', '', '', 'Pt.Wiratama Jaya Karya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1789, 'bpk rana', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1790, 'Sholeh', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1791, 'Bpk. Eko Prasojo', '', '', '', '', '', '', 'PT. Teknik Andalas', '', '08129614114', '', 'Jln. Tgk. Chik Dipineung Raya X no. 1 ABC \r\nBanda Aceh', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1792, '', '', '', '', '', '', '', 'PT. CIPTA GUNA INDOTEK', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1793, '', '', '', '', '', '', '', 'Max Store', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1794, 'bpk aji', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1795, 'BP SAMSUL', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1796, 'Arfan', '', '', '', '', '', '', 'Pt.Kalyanamitra Adhara Mahardhikak', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1797, 'cipta agung ltc', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1798, 'elmi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1799, 'pt inter abadi trans logistik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1800, '', '', '', '', '', '', '', 'Toko Mantap Jaya Teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1801, 'Bpk Agus Rompi', '', '', '', '', '', '', '', '', '08814566366', '', '', 'Buat Rompi 3M B.Dongker Uk. 9XL', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1802, '', '', '', '', '', '', '', 'PT. ICA TEKNIK MANDIRI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1803, '', '', '', '', '', '', '', 'Satuan Harapan Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1804, 'Ibu Sofi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1805, 'Bpk benny', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1806, 'Ny Yanti', '', '', '', '', '', '', 'Bintang Perkasa Safety', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1807, '', '', '', '', '', '', '', 'Pt.Kimpo Indotama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1808, 'Bpk. Yanto', '', '', '', '', '', '', '', '', '087781552778', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1809, 'Bpk Eko', '', '', '', '', '', '', 'PT Jaya Raya Mandiri', '', '087784580181', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1810, 'Dita', '', '', '', '', '', '', 'PLN UPP JJBB 2', '', '', '', 'Cawang', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1811, 'BAROKAH ABADI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1812, 'Bpk Edu', '', '', '', '', '', '', 'PT. Gowima', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1813, 'MATAHARI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1814, '', '', '', '', '', '', '', 'Xing jiang Tian Xin Boiler', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1815, 'Bpk.Edi', '', '', '', '', '', '', 'PT Jati Indah Permai', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1816, 'PT INDOASIA TIRTA MANUNGGAL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1817, 'Yuni', '', '', '', '', '', '', '', '', '', '', 'Makassar', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1818, 'H.Komaruddin/Bp.Mariono', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1819, 'PT sinar surya putra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1820, 'Ibu Atu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1821, 'pt prima tunggal javaland', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1822, 'cv alat betula tropis', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1823, '', '', '', '', '', '', '', 'Toko Guntur', '', '', '', 'UG A19 No.6', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1824, '', '', '', '', '', '', '', 'PT. Hanesa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1825, 'Bpk. Aldi Renaldi', '', '', '', '', '', '', 'palembang', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1826, 'Ibu glaudya', '', '', '', '', '', '', 'CV. Hai Tien Concrete Solution', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1827, 'NOVA', '', '', '', '', '', '', 'PT PRIMA KARYA SARANA SENTRA', '', '089508047023', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1828, 'Bpk Dimas', '', '', '', '', '', '', 'Tohaga', '', '085886700632', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1829, 'CARGLOSS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1830, '', '', '', '', '', '', '', 'PT. QDC TECHNOLOGIES', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1831, 'logam jaya', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1832, 'bpk haji', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1833, 'JUHRI SUPIR MM', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1834, 'wattari', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1835, 'Bpk. Andi', '', '', '', '', '', '', '', '', '081994881415', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1836, '', '', '', '', '', '', '', 'PT CIE', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1837, 'Bpk. Alvian', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1838, 'Ardi', '', '', '', '', '', '', 'Cv.Aditya Pratama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1839, '', '', '', '', '', '', '', 'Orionowl', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1840, 'Anastasya', '', '', '', '', '', '', '', '', '081229545022', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1841, '', '', '', '', '', '', '', 'Mitra teknik Sentosa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1842, '', '', '', '', '', '', '', 'toko sevtindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1843, 'KODIR', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1844, 'alva enginering', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1845, 'Kuswandi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1846, '', '', '', '', '', '', '', 'Mastedaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1847, '', '', '', '', '', '', '', 'Indo Persada Marine', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1848, '', '', '', '', '', '', '', 'Karisma Jaya Mandiri', '', '', '', 'UG C27/8', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1849, 'Ibu nisa', '', '', '', '', '', '', 'Toko Qirbal', '', '081310037525', '', '', 'DARI HARGA TOKO NAIK 5 RIBU KARENA RIBET ', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1850, '', '', '', '', '', '', '', 'Metro', '', '', '', 'Proyek Senen Blok 3 Lt.1 Cks No.116', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1851, 'pt cahaya arif abadi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1852, 'Bu Salsa', '', '', '', '', '', '', 'PT. Flowric Bintang Adi Sentosa', '', '0895343409822', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1853, 'pt beta multi teknika', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1854, 'KO HENDRA', '', '', '', '', '', '', 'PT. TRI PUTRA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1855, 'Bpk. Andreas', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1856, '', '', '', '', '', '', '', 'Pt Tira Austenite tbk', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1857, '', '', '', '', '', '', '', 'PT BELA CO', '', '', '', '', 'ayi', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1858, '', '', '', '', '', '', '', 'PT WWS', '', '081381192079', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1859, 'ARAB', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1860, '', '', '', '', '', '', '', 'Pt Harditama Multi Sarana', '', '', '', 'Ltc Glodok lt,2', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1861, '', '', '', '', '', '', '', 'PT PLN ( persero ) UPP JJBB 2', '', '', '', 'Jl.Mayjend sutoyo No.1 Cililitan \r\nJakarta Timur\r\nKode Pos 13640', '', 5, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1862, 'Sofyan Kadis', '', '', '', '', '', '', 'KOMINFO', '', '', '', 'Halmahera Selatan', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1863, '', '', '', '', '', '', '', 'PT. TANG MOBIL DUNIA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1864, '', '', '', '', '', '', '', 'Bpk. Hidayat', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1865, 'Bpk Teguh Santoso', '', '', '', '', '', '', '', '', '08119105164', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1866, 'PT TRIPUTRA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1867, '', '', '', '', '', '', '', 'PT.AZTA PRIMA INDONESIA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1868, 'PT DIVA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1869, 'bang kumis', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1870, 'Henry', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1871, 'Cv yoderi safety', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1872, '', '', '', '', '', '', '', 'PT. ARI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1873, 'SOFOCO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1874, 'Bpk Bobbi', '', '', '', '', '', '', 'PT. Fifan Jaya Makmur', '', '081290015455', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1875, 'Bpk. Chandra', '', '', '', '', '', '', 'PT. PACIFIC TIMUR', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1876, 'Ibu Dini', '', '', '', '', '', '', 'PT. Heint Logistik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1877, '', '', '', '', '', '', '', 'Toko Aneka Sarana Safety', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1878, 'bp tio tony (lampung)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1879, 'Bpk Tri Nugraha (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1880, '', '', '', '', '', '', '', 'PT ENI INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1881, 'inkasa', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1882, 'Bpk Sutejo (ps)', '', '', '', '', '', '', 'PT. PROFLUID', '', '081293774308', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1883, 'PICO REKSA PRATAMA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1884, 'Bpk Timbul', '', '', '', '', '', '', 'KJPP NDR', '', '+6281316411004', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1885, 'pt mitra infoparama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1886, 'IRIANA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1887, 'YANTO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1888, 'ujang jamba', '', '', '', '', '', '', 'samd0ria', '', '', '', 'senen blok 111 lantai a lo 1 bks no 27', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1889, 'WORO EDGAR WIKANTI', '', '', '', '', '', '', 'PT RCSI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1890, '', '', '', '', '', '', '', 'PT. Vallianz', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1891, '', '', '', '', '', '', '', 'PT. Wika Intinusa Niagatama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1892, 'DEWI-sby', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1893, 'SANTO', '', '', '', '', '', '', '', '', '021-8198708', '', 'OTISTA 3 \r\nJL.KEBON NANAS SELATAN 8 N0.11\r\nCIPINANG CEMPEDAK\r\nJAKARTA TIMUR', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1894, 'JOSHUA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1895, 'Bpk. william sudiyono', '', '', '', '', '', '', 'PT. Sudi Jaya Globalindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1896, 'PT ARISTEK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1897, 'fajar', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1898, 'Bpk Tajudin', '', '', '', '', '', '', 'PT. Batel Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1899, 'Ibu  azizah', '', '', '', '', '', '', 'PT. DUNIA TEKNIK SEJAHTERA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1900, '', '', '', '', '', '', '', 'PT. DIAN MUSTIKA AGUNG', '', '', '', 'LTC LT 1 BLOK C 16 NO 3 & 5 ', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1901, '', '', '', '', '', '', '', 'PT Askon Mining Kendari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1902, '', '', '', '', '', '', '', 'Kementerian Pekerjaan Umum dan Perumahan Rakyat', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1903, '', '', '', '', '', '', '', 'PT Indo Cipta Gemilang', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1904, 'Bpk. Matius', '', '', '', '', '', '', '', '', '081219979775', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1905, '', '', '', '', '', '', '', 'PT Persada Global Safety', '', '081808176768', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1906, 'Andi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1907, '', '', '', '', '', '', '', 'DMP SHIPPING SERVICE', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1908, 'OM ACUNG', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1909, 'Ibu Dhiny', '', '', '', '', '', '', 'PT Heint Logistics', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1910, '', '', '', '', '', '', '', 'PT. Hua Long Nusantara', '', '', '', '', 'orang china ', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1911, 'PT TRIDAYA LT 2 LTC GLODOK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1912, 'Pt buana', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1913, '', '', '', '', '', '', '', 'PT. Silkargo Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1914, 'HASAN/TARUNA JAYA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1915, 'BARUS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1916, 'sinar baru lt ug', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1917, 'Sinohydro Section 1 Bekasi', '', '', '', '', '', '', 'Sinohydro Section 1 Bekasi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1918, 'SINTA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1919, 'WIKA REALTY', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1920, 'zuhal gemilang abadi', '', '', '', '', '', '', 'lt 1 C31 no3', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1921, 'Ibu Rita', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1922, 'BUDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1923, 'herman', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1924, '', '', '', '', '', '', '', 'pt multi graha lestari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1925, 'epi', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1926, 'GROKINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1927, 'central nett', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1928, 'pak edo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1929, '', '', '', '', '', '', '', 'GEMA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1930, 'PT INDOMAK KITA CIPTA KARYA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1931, 'PT IMPIAN KITACIPTA KARYA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1932, 'Lela', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1933, 'lili sinar mas', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1934, '', '', '', '', '', '', '', 'PT. Mandala Pratama Teknik (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1935, '', '', '', '', '', '', '', 'grand hebel', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1936, 'Bpk. Andres', '', '', '', '', '', '', 'PT. Kayangan Sukses', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1937, 'RUSKA', '', '', '', '', '', '', '', '', '0812 8986 2800', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1938, 'MURTOPO', '', '', '', '', '', '', 'GEMILANG TEHNIK JL.NUSA INDAH 1 BLOK B NO.20 PONTIANAK', '', '0811564706', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1939, 'lewi', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1940, 'ko akin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1941, '', '', '', '', '', '', '', 'Jakarta Prima', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1942, 'danil ramadani', '', '', '', '', '', '', 'PT. KAWAN LAMA SEJAHTERA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1943, 'puad', '', '', '', '', '', '', 'PT. KAWAN LAMA SEJAHTERA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1944, 'adi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1945, 'karya darma (senen)', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1946, '', '', '', '', '', '', '', 'CV Alva Engineering', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1947, 'GITO', '', '', '', '', '', '', 'PT BAJRAGRAHA SENTRANUSA GRAHA YPK PLN', '', '', '', 'JL LEBAK BULUS TENGAH NO.5 CILANDAK BARAT\r\nJAK SEL', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1948, 'toko listrik lt ug', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1949, '', '', '', '', '', '', '', 'PT. Lingga Indoteknik Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1950, 'Bpk. Syarif', '', '', '', '', '', '', 'PT. Langit Teknologi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1951, 'Bpk. Bovi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1952, '', '', '', '', '', '', '', 'PT. Supreme Energy', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1953, 'Bpk. darsim', '', '', '', '', '', '', 'BENGKEL INTAN MOTOR', '', '0818781210', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1954, 'PT AKSES PRIMA', '', '', '', '', '', '', 'TANJUNG BARAT INDAH JL TERATAI VI NO E11, JAGAKARSA, JAKSEL', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1955, 'Xu Ding', '', '', '', '', '', '', 'Bintang Lima Kontruksi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1956, '', '', '', '', '', '', '', 'Citra Pamindo Riguna', '', '', '', '', 'Group TWA', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1957, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM SUMMARECON', '', '', '', 'DI PANJAITAN KAV 9 - 10 \r\nCIPINANG CEMPEDAK, JATINEGARA\r\nJAKARTA TIMUR DKI JAKARTA ', 'SUMMARECON\r\nALAMAT ANTAR \r\nSUMMARECON LOTUS RESIDENCE\r\nJL.LOTUS III BLOK IC NO 10 \r\nHARAPAN MULYA, MEDAN SATRIA \r\nBEKASI, JAWA BARAT 17143', 5, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1958, 'SARA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1959, 'Bpk. Johan Akbar', '', '', '', '', '', '', '', '', '08117388585', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1960, '', '', '', '', '', '', '', 'PT. Provis Garuda Services', '', '', '', 'jl. Tanah abang 1 no. 8 ', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1961, 'Bpk. Darto', '', '', '', '', '', '', 'Global Doctor', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1962, 'TANTO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1963, 'Manatap Ariesta (ps)', '', '', '', '', '', '', 'PT. Dharma Polimetal', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1964, 'Bpk. Taswin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1965, '', '', '', '', '', '', '', 'PT. HUTAMA KARYA ( Persero )', '', '', '', '', 'tokped', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1966, 'Bpk. Pranoto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1967, '', '', '', '', '', '', '', 'Colombia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1968, 'PERSADA GLOBAL', '', '', '', '', '', '', 'LT UG BLOK A NO.6', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1969, 'Bpk. Ega Megananda', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1970, '', '', '', '', '', '', '', 'Pt.dwida jaya tama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1971, '', '', '', '', '', '', '', 'Toko Prima Jaya', '', '087722157338', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1972, 'Fahrul', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1973, 'Bpk. Enos', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1974, 'Bpk Dul', '', '', '', '', '', '', 'PT Surama', '', '02122520556', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1975, 'WILTON', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1976, 'RENDRA', '', '', '', '', '', '', '', '', '089634370405', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1977, 'RIO', '', '', '', '', '', '', 'AMBON', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1978, 'CHAMIM', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1979, '', '', '', '', '', '', '', 'PT. LIMIN MARINE & OFFSHORE', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1980, '', '', '', '', '', '', '', 'Cikarang Listrindo Energy', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1981, 'AGUS', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1982, 'DEDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1983, 'bpk Rudi', '', '', '', '', '', '', '', '', '085211429613', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1984, 'Bpk Hendra Gunawan', '', '', '', '', '', '', '', '', '081285550055', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1985, 'edy', '', '', '', '', '', '', 'pt fabrindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1986, 'bpk Madrus', '', '', '', '', '', '', '', '', '087888531985', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1987, 'harapan agung ltc lt 2', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1988, '', '', '', '', '', '', '', 'PT. Balsa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1989, '', '', '', '', '', '', '', 'PT SUPREME ENERGI RANTAU DEDAP', '', '08128563798', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1990, '', '', '', '', '', '', '', 'PT AMP', '', '08170097903', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1991, '', '', '', '', '', '', '', 'PT. Appro indonesia (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1992, '', '', '', '', '', '', '', 'PT. Triton kencana tirta (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1993, 'Ibu Laras', '', '', '', '', '', '', '', '', '081285656753', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1994, 'Bpk Suparyo', '', '', '', '', '', '', '', '', '081293236677', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1995, 'Bpk. mudi yanto (PS)', '', '', '', '', '', '', '', '', '081238852721', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1996, 'bpk Agus', '', '', '', '', '', '', '', '', '081310461500', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1997, '', '', '', '', '', '', '', 'Toko Victory Lite', '', '62201124', '', 'Lt. UG Blok. C10 No. 2-3', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1998, 'DJM Riki', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(1999, 'Bpk. Tarka', '', '', '', '', '', '', '', '', '081319846667', '', '', '', 1, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(2000, 'koh handri', '', '', '', '', '', '', 'cipta mitra                           hwi lt 3 blok c no 11', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:10', '2020-07-29 12:15:10', 1, 1),
(2001, 'Bpk. Imam', '', '', '', '', '', '', '', '', '087830362032', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2002, 'Sujudi', '', '', '', '', '', '', 'New City Motor', '', '', '', 'Jl.Lautze No.19', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2003, 'Ibu Nabillah', '', '', '', '', '', '', 'PT. Technodrives', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2004, 'ratih', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2005, '', '', '', '', '', '', '', 'Ray Saftindo', '', '', '', 'Lt GF 1 Blok B16 no.2', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2006, 'Ibu Lena', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2007, 'Bpk. Jumiadi', '', '', '', '', '', '', '', '', '081291770607', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2008, 'BUDI', '', '', '', '', '', '', '', '', '081387737383', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2009, 'SRI', '', '', '', '', '', '', 'PT CRAMMS', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2010, 'TRI', '', '', '', '', '', '', 'APARTEMEN SCBD SUITE', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2011, '', '', '', '', '', '', '', 'PT Cigading Habeam Centre', '', '', '', 'Jl K.H Hasyim Ashari No.2\r\nPetojo Utara - Gambir \r\nJakarta Pusat ', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2012, 'PT NAKAMA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2013, 'gemilang arumindo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2014, 'Bpk. Rinto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2015, 'Ibu. Siska', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2016, 'PURWO TEHNIK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2017, 'eko', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2018, 'Le chai', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2019, 'BAHTERA ADIGUNA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2020, 'Alex', '', '', '', '', '', '', '', '', '', '', 'Cikarang', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2021, 'elia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2022, '', '', '', '', '', '', '', 'PT Ardya Prima Internusa', '', '08161837888', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2023, 'kjpp akr', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2024, 'Bpk. Alfi (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2025, 'Bpk. Irbar', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2026, 'Ibu Niza', '', '', '', '', '', '', '', '', '', '', '', 'cheetah uk 10 tambah 35000', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2027, 'Bpk. Deli', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2028, '', '', '', '', '', '', '', 'PT. Putra Ganda', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2029, 'PT TRIDIANTARA ALVINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2030, 'Bpk Widi', '', '', '', '', '', '', '', '', '08159025559', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2031, 'Bpk Lodi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2032, '', '', '', '', '', '', '', 'PT.Lucky Jaya Sentosa', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2033, 'Bpk. H.Tariadi', '', '', '', '', '', '', 'PT. Megasaktihak', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2034, 'aneza persada abadi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2035, 'PT.Onasis Indonesia(PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2036, '', '', '', '', '', '', '', 'Emina Multi Inovasi (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2037, 'mba dwi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2038, 'CV INOVASI ANAK NEGERI(PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2039, 'RURI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2040, 'Bpk Usman', '', '', '', '', '', '', '', '', '081383309141', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2041, '', '', '', '', '', '', '', 'CV. Golden Star', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2042, '', '', '', '', '', '', '', 'Sumber Karya Mandiri', '', '', '', 'Lt.2 C43/48', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2043, 'IBU SINTA', '', '', '', '', '', '', 'SECURITAS INDONESIA', '', '021-5268466', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2044, 'Mr Zhang', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2045, 'AAL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2046, '', '', '', '', '', '', '', 'Pt.Grand Kartech', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2047, 'PT. Kranindo (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2048, 'ALDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2049, '', '', '', '', '', '', '', 'PT. Mitra timur jaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2050, 'Bpk dedi', '', '', '', '', '', '', '', '', '08999977510', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2051, 'AMRIL', '', '', '', '', '', '', '', '', '081284605299', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2052, '', '', '', '', '', '', '', 'PT.EUROASIATIC HEAT & POWER SYSTEMS', '', '', '', 'Taman Tekno Blok G3 No.15-16\r\nSektor XI Serpong BSD City', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2053, 'Ibu Johani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2054, '', '', '', '', '', '', '', 'PT. Lintas Sarana Elektrika', '', '081288128895', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2055, 'Ibu Yunita', '', '', '', '', '', '', '', '', '0812-8482-8695', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2056, 'yst Ltc lt 2', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2057, 'EGA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2058, 'asep', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2059, 'ANWAR', '', '', '', '', '', '', 'ERA MANDIRI CEMERLANG', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2060, 'BURHAN', '', '', '', '', '', '', 'PT INDO ASIA TIRTA MANUNGGAL', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(2061, '', '', '', '', '', '', '', 'PT. DJM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2062, 'Ibu Anik (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2063, 'Bpk. Adhitya Syachputra', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2064, 'arta mandiri teknik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2065, 'Bpk Andi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2066, 'Bpk. Abdul majid', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2067, '', '', '', '', '', '', '', 'PT. AMOS', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2068, 'BUDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2069, '', '', '', '', '', '', '', 'PT. ETERNITI SARANA BERKAT (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2070, 'Bpk. Encu', '', '', '', '', '', '', 'PT. NALIKA UTAMA', '', '0816886841', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2071, 'Rana', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2072, 'ELNUSA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2073, 'bpk. eko', '', '', '', '', '', '', '', '', '083813518005', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2074, 'Bpk Victor', '', '', '', '', '', '', '', '', '081211629779', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2075, '', '', '', '', '', '', '', 'PT shun fa langgeng jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2076, 'Dany', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2077, 'DENAPELLA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2078, '', '', '', '', '', '', '', 'CONSORTIUM PT. RAGA PERKASA EKAGUNA - PT. JGC', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2079, 'TIAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2080, '', '', '', '', '', '', '', 'PT IKA ARTA SUKSES SENTOSA', '', '081288957783', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2081, 'karya jaya', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2082, 'Bpk Java', '', '', '', '', '', '', '', '', '081213301179', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2083, 'PT balikpapan ready mix pile', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2084, 'Bpk Marwan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2085, 'PT Sumbetri megah', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2086, '', '', '', '', '', '', '', 'Lautan Jaya Berlian', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2087, 'shin an', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2088, '', '', '', '', '', '', '', 'PT. CSM', '', '085966708465', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2089, 'vins safety lt 1', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2090, 'Bpk. Antonius (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2091, 'Bpk Praja', '', '', '', '', '', '', 'KSO CCM INDEC CCME', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2092, 'alano teknik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2093, 'semangat jaya', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2094, '', '', '', '', '', '', '', 'Primadinamika', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2095, 'HADI', '', '', '', '', '', '', 'PAPUA', '', '0811483073', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2096, '', '', '', '', '', '', '', 'VIN\'S SAFETY', '', '', '', 'LT 1 BLOK B10 NO.1', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2097, 'DEWI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2098, 'HABIBI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2099, '', '', '', '', '', '', '', 'Pt.Kayu Permata', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2100, 'Bpk Jacky', '', '', '', '', '', '', 'PT. Indonesia Ocean Truck', '', '08128287007', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2101, '', '', '', '', '', '', '', 'PT.Budi Makmur Jayamurni (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2102, 'Bpk Danny', '', '', '', '', '', '', 'CV Indosupply', '', '081221103090', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2103, 'muhamad solihin', '', '', '', '', '', '', 'jl masjid attaqwa utama no.94 rt 05/08 kembangan utara, jkt barat', '', '082184488045', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2104, 'PT WJL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2105, 'Bpk Josyes', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2106, 'Ibu Sri Dewanty', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2107, 'RIRI', '', '', '', '', '', '', '', '', '0216501160', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2108, 'max store', '', '', '', '', '', '', '', '', '', '', 'gf 1 blok a25 no 6', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2109, 'Bpk alim', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2110, '', '', '', '', '', '', '', 'PT. BPE GROUP (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2111, '', '', '', '', '', '', '', 'Pt.AICE ICE CREAM', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2112, 'bpk karel', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2113, 'RUDI', '', '', '', '', '', '', 'JAKARTA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2114, 'Bpk. Alex (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2115, 'Bpk. Gega (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2116, '', '', '', '', '', '', '', 'TOKO STAR SAFETY', '', '02126071290', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2117, 'Bpk. Andri', '', '', '', '', '', '', 'PT BETON ELEMENINDO PERKASA', '', '58905200 / 08567758678', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2118, 'bpk.antoni', '', '', '', '', '', '', 'MKP', '', '082112578000', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2119, 'sugeng', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2120, 'PT BRM MARINE', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2121, '', '', '', '', '', '', '', 'Pt.Multi Pratama Sarana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2122, 'Ibu Yuni', '', '', '', '', '', '', 'PT. Yun Sung Indonesia', '', '082121554090', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2123, 'bpk.jerry', '', '', '', '', '', '', '', '', '08111200338', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2124, 'Pt prima nusantara', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2125, 'hj.Mamah maryamah (ps)', '', '', '', '', '', '', 'CV. ANSORI JAYA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2126, 'Ibu. Ita', '', '', '', '', '', '', '', '', '082139204970', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2127, 'MST', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2128, '', '', '', '', '', '', '', 'Pt.Inter Asia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2129, 'malaba', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2130, 'Ibu Rina', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2131, 'Ibu Wenny Tan', '', '', '', '', '', '', '', '', '081282272773', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2132, 'Bpk Topik', '', '', '', '', '', '', '', '', '087770857779', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2133, 'NUR', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2134, 'GRAHA NIAGA TATA UTAMA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2135, '', '', '', '', '', '', '', 'PT Transmeka Inti Mulia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2136, 'Ibu Susan Sumarlin', '', '', '', '', '', '', 'PT. MJ Indah Perkasa', '', '08154529983', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2137, 'MR SONG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2138, 'pak athan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2139, 'Bpk khusnul (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2140, '', '', '', '', '', '', '', 'Toko Aneka Safety (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2141, '', '', '', '', '', '', '', 'CV. FORTUNA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2142, 'pt perissos', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2143, 'SANDRO', '', '', '', '', '', '', 'JL IMAM BONJOL RT 009/005 KEL BUNGIN KEC LUWUK KAB BANGGAI- SULAWESI TENGAH', '', '085341870588', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2144, 'SWADAYA GRAHA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2145, 'Bpk Eli Hia', '', '', '', '', '', '', 'PT. WB Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2146, '', '', '', '', '', '', '', 'PT. Berkat Karunia Phala (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2147, '', '', '', '', '', '', '', 'toko makmur mulia  safety', '', 'lt 2', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2148, '', '', '', '', '', '', '', 'PT PLN (persero)Pusmanpro', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2149, 'PT GLOBAL ENVIRO TECHNOLOGY', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2150, 'PT GUNA ELEKTRO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2151, '', '', '', '', '', '', '', 'PT. Dakara Citra Tangguh (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2152, 'PURNADI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2153, 'bp yasid lubis', '', '', '', '', '', '', 'pembangkit rezeki utama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2154, 'MANSUR', '', '', '', '', '', '', '', '', '082397876359', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2155, '', '', '', '', '', '', '', 'PT.Hengjaya Mineralindo', '', '', '', 'Menara Rajawali 22nd Floor \r\nMega Kuningan', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2156, 'Leonard', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2157, '', '', '', '', '', '', '', 'Fresh klindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2158, 'Pt indra jaya lestari', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2159, 'sukses jaya', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2160, 'PT EHUA', '', '', '', '', '', '', '', '', '081311496452', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2161, '', '', '', '', '', '', '', 'PT PRATAMA GRAHA SEMESTA', '', '081387137768', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2162, 'bu sinta', '', '', '', '', '', '', 'PT YONMING', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2163, '', '', '', '', '', '', '', 'Pt tenaga listrik bengkulu', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2164, 'NASRUN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2165, '', '', '', '', '', '', '', 'PT. Anugerah Bangun Persada', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2166, 'Bpk. Dadi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2167, 'bu mia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2168, '', '', '', '', '', '', '', 'cv samudra medika laboratories', '', '', '', 'bandung', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2169, 'Ibu ninik widyarti (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2170, '', '', '', '', '', '', '', 'TOKO BERKAT SARANA SAFETY', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2171, 'Bpk. Viki (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2172, '', '', '', '', '', '', '', 'CV. Trans newstar bright', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2173, 'Bpk Edi', '', '', '', '', '', '', 'PT SURYA SEMESTA PERMAI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2174, 'bpk.unan (PRIORINDO)', '', '', '', '', '', '', '', '', '087771969024', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2175, 'ANDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2176, 'Bpk Faldi', '', '', '', '', '', '', '', '', '081385081707', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2177, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM PROJECT TEAM SINOHYDRO MALANENGAH', '', '', '', 'DI PANJAITAN KAV 9 - 10 \r\nCIPINANG CEMPEDAK, JATINEGARA\r\nJAKARTA TIMUR DKI JAKARTA ', '', 5, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2178, 'Bpk. Sultan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2179, 'Bpk. Rangga', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2180, 'BCA JAKARTA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2181, '', '', '', '', '', '', '', 'PT.MAN', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2182, 'Bpk. Gumilang', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2183, 'M abdu abubakar', '', '', '', '', '', '', 'dinas lingkungan hidup', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2184, 'smk yosua timika papua', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2185, '', '', '', '', '', '', '', 'prima nusantara', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2186, 'SINOHYDRO MALANEGAH', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2187, '', '', '', '', '', '', '', 'PT. Dongjin Marine Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2188, 'IMANUEL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2189, 'DEVI', '', '', '', '', '', '', 'RUKO TAMAN MAHKOTA A1/26 KEL.BENDA- TANGERANG', '', '085782222803', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2190, 'Ibu citra', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2191, '', '', '', '', '', '', '', 'toko prima safety', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2192, 'Bpk.  Anggi nasir', '', '', '', '', '', '', 'PT. INTI AC', '', '085256740008', '', 'PALU', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2193, 'LAUTAN SAFETY', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2194, '', '', '', '', '', '', '', 'MS MANDIRI PERKASA CIKARANG', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2195, 'Bpk. Hengki', '', '', '', '', '', '', '', '', '081315821344', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2196, 'Bpk. Iswaluyi (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2197, '', '', '', '', '', '', '', 'CV.Segitiga Nusantara', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2198, 'DEKA GONDOLA', '', '', '', '', '', '', 'JL RAYA KECAPI HIJAU NO 36 BEKASI', '', '085715183960C', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2199, '', '', '', '', '', '', '', 'TOKO HCK', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2200, '', '', '', '', '', '', '', 'Pt.Pasadena Metric Indonesia', '', '', '', 'Cikarang Baru', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2201, 'GUNTAR', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2202, 'BP.YUDHO (ENGINEERING SCBD SUITES)', '', '', '', '', '', '', 'APARTEMENT SCBD SUITES KAWASAN SCBD LOT 23B', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2203, 'PT Mitra Global', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2204, '', '', '', '', '', '', '', 'PT. KJB', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2205, 'suryadi', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2206, 'Bernard', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2207, 'jeni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2208, 'MAY TEK', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2209, 'Pt sibrama sakti', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2210, 'Bpk. Mandes', '', '', '', '', '', '', '', '', '081218490045', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2211, '', '', '', '', '', '', '', 'Pt.Sentra Sinar Baru', '', '', '', 'Jl.Raya Cilincing No.18', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2212, '', '', '', '', '', '', '', 'CV Karya Tirta Perdana', '', '081355541404', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2213, 'Bpk. Yudi (ps)', '', '', '', '', '', '', 'ternate', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2214, 'dock 21', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2215, '', '', '', '', '', '', '', 'PT BINA AN NAFI (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2216, 'bpk wawan', '', '', '', '', '', '', 'mitra edv senen', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2217, '', '', '', '', '', '', '', 'pt air rindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2218, 'Pt ABH', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2219, 'Ibu Widyastuti', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2220, 'Pt pertamina persero marine region v', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2221, 'simon manado', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2222, '', '', '', '', '', '', '', 'Pt Alda Berta Indonesia', '', '08380773641', '', '', '', 2, 'aktif', '2020-07-29 12:15:11', '2020-07-29 12:15:11', 1, 1),
(2223, '', '', '', '', '', '', '', 'PT.WONOKOYO', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2224, 'WORO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2225, 'JOHANES', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2226, '', '', '', '', '', '', '', 'CMI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2227, '', '', '', '', '', '', '', 'NURPADI', '', '', '', 'Lt.2 C43 No.57', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2228, '', '', '', '', '', '', '', 'PT MULTI MITRA INDOTAMA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2229, '', '', '', '', '', '', '', 'PT MULTI  MITRA INDOTAMA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2230, '', '', '', '', '', '', '', 'LEMIGAS', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2231, '', '', '', '', '', '', '', 'PD. BERKAT MITRA ABADI', '', '', '', 'LT. UG C8 NO.7', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2232, 'DENI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2233, 'bp indra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2234, 'aneka global safety', '', '', '', '', '', '', '', '', '', '', 'GF 2BLOK A 15 NO 1\r\n', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2235, '', '', '', '', '', '', '', 'PT. Protelindo (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2236, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM PROJECT TEAM SINOHYDRO CIBENING', '', '', '', 'DI PANJAITAN KAV 9 - 10 \r\nCIPINANG CEMPEDAK, JATINEGARA\r\nJAKARTA TIMUR DKI JAKARTA ', 'CIBENING / HOTEL PURI AYU', 5, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2237, 'Bpk. Fery', '', '', '', '', '', '', 'PT. KTU', '', '0817714588', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2238, 'PT BRM PILE', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2239, '', '', '', '', '', '', '', 'KOPEGA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2240, 'toko cahaya riau', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2241, '', '', '', '', '', '', '', 'Toko Duta Safety Jakarta', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2242, '', '', '', '', '', '', '', 'PT. Tirta Cipta Group', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2243, 'okky novriyansyah', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2244, 'Bpk. Rusli', '', '', '', '', '', '', 'PT. Cipta Selamat Mandiri (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2245, 'pandu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2246, 'Ibu Melany jong (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2247, '', '', '', '', '', '', '', 'PT. Danora Argo Prima', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2248, 'Bpk. Rusli', '', '', '', '', '', '', 'PT. SAM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2249, 'Bpk Fajar Budi Angga (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2250, 'pt bumi biru konstruksi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2251, '', '', '', '', '', '', '', 'PT. Turbo Daya Mekanika (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2252, 'Ibu dewi wang', '', '', '', '', '', '', 'PT.Sinotrans Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2253, 'FERI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2254, '', '', '', '', '', '', '', 'Toko kubera perlengkapan safety', '', '', '', 'GF2', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2255, '', '', '', '', '', '', '', 'Pt.Wiryo Cranes Perkasa', '', '082232995143', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2256, 'Bpk. Hadi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2257, '', '', '', '', '', '', '', 'GL', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2258, 'WIHARTONO', '', '', '', '', '', '', 'PIL', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2259, 'Bpk Muhammad', '', '', '', '', '', '', 'PT. CPMM', '', '081283436661', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2260, 'SURYADI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2261, 'Bpk. Durahman', '', '', '', '', '', '', '', '', '08567862448', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2262, 'Pt lintas bahari nusantara', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2263, 'Cv cipta sarana teknik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2264, 'budy raharjo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2265, '', '', '', '', '', '', '', 'PT SKF Industrial Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2266, 'miraj', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2267, 'Bpk. Gunarko', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2268, 'bpk wanto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2269, 'Pt vantri murni anatory', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2270, 'Bpk Eko', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2271, '', '', '', '', '', '', '', 'PT INTEGRA', '', '08121064104', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2272, 'sumber makmur lt 2 C43 no 46', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2273, 'Pt clasik unifom', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2274, 'kenindo', '', '', '', '', '', '', 'lt ug', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2275, '', '', '', '', '', '', '', 'Duta Safety Jakarta', '', '', '', 'UG C18 No.1', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2276, 'GALIH', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2277, '', '', '', '', '', '', '', 'PT. CMB', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2278, 'Ibu. Elis', '', '', '', '', '', '', '', '', '087775692882', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2279, '', '', '', '', '', '', '', 'Pt Nexan', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2280, 'PT CAHAYA ARIF ABADI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2281, 'Bpk Taufik', '', '', '', '', '', '', '', '', '087776215959', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2282, 'Bpk Okta', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2283, 'Tumiba', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2284, '', '', '', '', '', '', '', 'PT. TEKNO MARINDO UTAMA', '', '081347752284', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2285, '', '', '', '', '', '', '', 'Pt Control Tech International', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2286, 'BUDHI SANTOSO', '', '', '', '', '', '', 'PT ANTAM UBPP LOGAM MULIA', '', '081387737383', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2287, '', '', '', '', '', '', '', 'PT. coco jaya lestari', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2288, 'MARLEV', '', '', '', '', '', '', '', '', '082213051116', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2289, 'bp harun', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2290, 'Bpk. Krisna', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2291, 'Bpk. Santo', '', '', '', '', '', '', '', '', '081298675751', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2292, 'Bpk. Wawan', '', '', '', '', '', '', 'CV. NITA GEMILANG', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2293, 'Ibu cici', '', '', '', '', '', '', '', '', '081314469861', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2294, 'fajri', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2295, 'WAHYU', '', '', '', '', '', '', '', '', '081212345297', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2296, 'Bpk. Wahyu', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2297, 'SSD', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2298, 'MR LING', '', '', '', '', '', '', 'LT UG BLOK C30 NO.10-11', '', '081283458888', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2299, 'FERI', '', '', '', '', '', '', '0812412344440', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2300, '', '', '', '', '', '', '', 'PT GRAHA ADI', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2301, 'Arief Tanto Widodo (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2302, 'Tn.Syamsu', '', '', '', '', '', '', 'PAM JAYA', '', '', '', 'Pejompongan', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2303, '', '', '', '', '', '', '', 'Anugerah Pelita IndoTeknik', '', '', '', 'LTC Lt.1 c32/16', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2304, 'Bpk Boedi sutrisno (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2305, '', '', '', '', '', '', '', 'Pt.Indira Dwi Mitra', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2306, 'pak wanto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2307, '', '', '', '', '', '', '', 'SNS', '', '', '', 'UG 32/3', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2308, 'SUTARDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2309, '', '', '', '', '', '', '', 'Antika Interior', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2310, '', '', '', '', '', '', '', 'CAN Indonesia', '', '', '', 'Lt.2 C18/3', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2311, 'Bpk. Edi', '', '', '', '', '', '', 'PT. Daya Perkasa', '', '085782618672', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2312, 'bpk boy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2313, 'iwan', '', '', '', '', '', '', 'grand poris', '', '08129265491', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2314, 'ummi kalsum', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2315, 'Herry', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2316, 'DENY SUBIANTORO', '', '', '', '', '', '', 'PT PETRO MUBA ENERGI', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2317, 'BINTORO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2318, 'Bpk Jacky', '', '', '', '', '', '', 'PT. Logistik Pelabuhan Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2319, 'NINDYATANTRI', '', '', '', '', '', '', 'bali bird park', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2320, '', '', '', '', '', '', '', 'PT. Agung Sukses Jaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2321, 'Bpk. Gumilang', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2322, '', '', '', '', '', '', '', 'PT.Pelangi Sukses Perkasa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2323, 'Bpk. Asep Mulyadi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2324, '', '', '', '', '', '', '', 'PT. Astra Graphia Tbk (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2325, 'bpk.erik', '', '', '', '', '', '', '', '', '08121800586', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2326, 'pt mafati group', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2327, 'Bpk. Ludiro pamungkas (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2328, 'makmur mulia safety lt2 B8 no1', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2329, 'RASUN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2330, 'Bpk. Bari (ps)', '', '', '', '', '', '', '', '', '087775320409', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2331, 'IPUNG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2332, 'WAHYUDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2333, 'PT AUSICO MAHENDRA', '', '', '', '', '', '', 'JL.RY TAPOS NO.39 RT 001/011 KEL TAPOS, DEPOK', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2334, 'Bpk. Setyo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2335, 'Bpk. Roni', '', '', '', '', '', '', '', '', '08128934296', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2336, 'bp firman banjarmasin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2337, 'Bpk. Asep Ridwan (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2338, '', '', '', '', '', '', '', 'Dinas Teknik Umum Bandara Supadio (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2339, 'Bpk. Cahyo Nugroho (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2340, 'PT pabolak arta utama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2341, 'Bpk Imang', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2342, '', '', '', '', '', '', '', 'SENTRAL KATIGA', '', 'GF1 BLOK C3 NO.1', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2343, '', '', '', '', '', '', '', 'JST', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2344, 'Bpk. Jonathan', '', '', '', '', '', '', '', '', '082122850000', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2345, 'RIZAL', '', '', '', '', '', '', 'CV KANIA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2346, '', '', '', '', '', '', '', 'CV.Ketelindo Pratama', '', '', '', 'Bubutan Surabaya', 'Group TWA', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2347, '', '', '', '', '', '', '', 'PT Lembaga Energi Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2348, '', '', '', '', '', '', '', 'PT. Margo mulio', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2349, 'WALI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2350, '', '', '', '', '', '', '', 'PT. Prima Mitra Indonesia (ps)', '', '', '', 'gf1 blok a 27 no 3', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2351, 'Bpk. Dian Firdaus (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2352, 'RAHMAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2353, 'Bpk. Rey', '', '', '', '', '', '', '', '', '08114335113', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2354, 'Ibu Sarah (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2355, 'Pt Sapex servis indonesia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2356, '', '', '', '', '', '', '', 'PT. Global Bangun Sukses (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2357, 'JUHRI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2358, 'MUSTOFA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2359, 'Bpk. Sigit', '', '', '', '', '', '', '', '', '081216333593', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2360, 'rauf', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2361, 'Bpk. Kurniawan', '', '', '', '', '', '', '', '', '08179811621', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2362, 'ANTO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2363, 'onligerald', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2364, '', '', '', '', '', '', '', 'PT. MONALISA TUNGGAL JAYA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2365, 'Bpk.Fredy', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2366, 'Pt halmahera karya mandiri', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2367, 'ANINDO', '', '', '', '', '', '', '', '', '087737720700', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2368, 'Mursal', '', '', '', '', '', '', 'Pt.Salsabila Indah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2369, 'PT SUMMARECON AGUNG TBK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2370, 'Ibu. Santy Wirajaya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2371, '', '', '', '', '', '', '', 'Mahfud', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2372, 'Bpk.Roland', '', '', '', '', '', '', '', '', '089517124742', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2373, 'Bpk Fuad Fajrin (ps)', '', '', '', '', '', '', '', '', '08114406644', '', 'makassar', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2374, 'Ny.Shanty', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2375, 'Ibu Tiara (ps)', '', '', '', '', '', '', 'PT. ALP', '', '085884606399', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2376, '', '', '', '', '', '', '', 'CV Muliatama Sejahtera (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2377, '', '', '', '', '', '', '', 'Pt.Bagus Teknik', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2378, 'Bpk. Maryo (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2379, 'Mulyadi', '', '', '', '', '', '', 'Pt.So Good Food', '', '', '', 'Jl.Daan Mogot km 12 No.9 \r\nJakarta', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2380, 'Bpk. Ryan', '', '', '', '', '', '', '', '', '08128881243', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2381, 'Bpk. Oyama (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2382, 'Bpk Iman (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2383, '', '', '', '', '', '', '', 'PT POHON MAS SEJAHTERA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2384, 'CV DINAMIKA AIRCONE', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2385, 'Bpk. Sudarmaji (ps)', '', '', '', '', '', '', '', '', '081347272433', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2386, '', '', '', '', '', '', '', 'PT. BIRU', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2387, '', '', '', '', '', '', '', 'PT. PENTAL GLOBAL (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2388, 'Bpk Handi Firmansyah (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2389, 'cahaya mandiri', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2390, 'YOGI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2391, 'AGUNG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2392, 'KASIRIN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2393, 'PT FAJAR RAWAYAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2394, '', '', '', '', '', '', '', 'PT. ENERCO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2395, 'mr ibing', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2396, '', '', '', '', '', '', '', 'INTI JAYA ABADI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2397, 'Ibu. Lisa', '', '', '', '', '', '', '', '', '081210494765', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2398, 'Pt Perdana mitra indonesia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2399, '', '', '', '', '', '', '', 'Pt.Indo Karsa', '', '08119922709', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2400, 'Bpk. Andi', '', '', '', '', '', '', '', '', '081285327157', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2401, '', '', '', '', '', '', '', 'PUSTEK', '', '087848354800', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2402, 'Ibu. Aci', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2403, '', '', '', '', '', '', '', 'CV INTI MULIA JAYA', '', '', '', 'JL. JAWA NO.99 RT.022 KEBUN HANDIL JELUTUNG KOTA JAMBI, JAMBI 36137', '', 5, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2404, 'Mr. Libin (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2405, 'Bpk. Suparjo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2406, 'Bpk. Arif (PS)', '', '', '', '', '', '', '', '', '081318465801', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2407, 'Bpk. Ryan Nitisastro', '', '', '', '', '', '', 'PT. NAGA SURYA INDAH', '', '081290144035', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(2408, 'M BAHRI', '', '', '', '', '', '', 'PT AUSICO MAHENDRA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2409, 'EKA (CILEGON)', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2410, 'mr zhang', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2411, 'kim chun (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2412, 'putut', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2413, '', '', '', '', '', '', '', 'PUTRA WIJAYA MANDIRI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2414, '', '', '', '', '', '', '', 'PT. PERSADA BATAVIA DIESEL (PS)', '', '081319437855', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2415, 'Bpk. Syarif', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2416, 'Hafidh Kurniawan', '', '', '', '', '', '', '', '', '082233649494', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2417, '', '', '', '', '', '', '', 'PT. PERTAMINA RETAIL', '', '08161123557', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2418, 'Bpk. Haswin (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2419, 'Ibu Yatrin', '', '', '', '', '', '', 'Pt.Berkat Putra Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2420, 'Ibu fella', '', '', '', '', '', '', 'PT. lekom maras', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2421, 'VASTORINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2422, '', '', '', '', '', '', '', 'PT persada arkana buana', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2423, 'Bpk Ipang', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2424, 'Bpk. Ing Marten', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2425, '', '', '', '', '', '', '', 'Asia Pilar', '', '085384116846', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2426, '', '', '', '', '', '', '', 'PT. Mitra Global Niaga', '', '6912289', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2427, 'Bpk. Loan', '', '', '', '', '', '', '', '', '087777637401', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2428, 'Bpk. Dimi (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2429, 'Bpk. Aldi', '', '', '', '', '', '', '', '', '081288194461', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2430, 'Bpk Ari', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2431, 'jack', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2432, 'safetyndo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2433, '', '', '', '', '', '', '', 'PUTRA NUSANTARA', '', '(021) 6240809, 6240810, 639884', '', 'Jl. Mangga Besar 1 No.88A Jakarta Barat 11180', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2434, 'Bpk.Rian', '', '', '', '', '', '', '', '', '08128881243', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2435, 'Bpk. Sujana', '', '', '', '', '', '', '', '', '081808683430', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2436, '', '', '', '', '', '', '', 'BOILER INDO KARYA PERKASA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2437, 'RIO/IBU TIN', '', '', '', '', '', '', '081247018693', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2438, 'Bpk. Amrias', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2439, 'IBU TIKA (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2440, 'Bpk Alex', '', '', '', '', '', '', 'PT. Crown Beverage Cans Indonesia', 'andrew-siregar@crowncork.com.sg', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2441, 'muhtar', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2442, 'Andi', '', '', '', '', '', '', '', '', '', '', 'Duren Sawit', '', 2, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2443, 'LIAS', '', '', '', '', '', '', 'PT INDRA ANGKOLA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:12', '2020-07-29 12:15:12', 1, 1),
(2444, 'agus cijantung', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2445, 'Topan', '', '', '', '', '', '', '', '', '08119544570', '', 'Pemda Cibinong', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2446, '', '', '', '', '', '', '', 'PT. Puan Ramadha Karya', '', '087854300863', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2447, 'Bpk. Naufal (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2448, 'Ibu marwana (ps)', '', '', '', '', '', '', '', '', '085265224686', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2449, '', '', '', '', '', '', '', 'PT BANGUNAN GUNUNG RAJA', '', '', '', 'RUKAN SEDAYU SQUARE JL KAMAL RAYA OUTER RING ROAD BLOK 1 NO 31 RT 004 RW 012 \r\nCENGKARENG BARAT, CENGKARENG JAKARTA BARAT DKI JAKARTA ', '', 5, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2450, '', '', '', '', '', '', '', 'Pt Heat Transfer Solutions Indonesia', '', '031-85582243', '', 'Ruko Green Mansion Residence Blok P 10 Ngingas Waru\r\nSidoarjo - Surabaya', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2451, '', '', '', '', '', '', '', 'Cv Bunga Toba', '', '', '', 'Lt.1 C37 No.5', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2452, 'hendrik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2453, 'BUDIMAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2454, 'Bpk.Budi', '', '', '', '', '', '', '', '', '085717136975', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2455, 'RONNY', '', '', '', '', '', '', 'MULIA LOGAM INDONESIA', '', '0811374598', '', 'PT TERANG LOGAM \r\nJL RAYA INDUSTRI III BLOK AD NO.33 JATAKE TANGERANG', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2456, '', '', '', '', '', '', '', 'PT. Pgasol', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2457, 'Bpk. Aniq (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2458, 'Eric', '', '', '', '', '', '', 'Cv Mitra Karya Bersama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2459, 'Bpk. Ridwan (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2460, 'Ibu Ika', '', '', '', '', '', '', 'PPPTMGB Lemigas', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2461, 'EVI', '', '', '', '', '', '', 'SECURITY', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2462, 'ARIF', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2463, 'PT seirama lagu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2464, 'Yogi Pramudito (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2465, '', '', '', '', '', '', '', 'PT. Mahakarya Sedaya', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2466, 'budi', '', '', '', '', '', '', '', '', '081383174828', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2467, 'PT pratama sinergi mandiri', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2468, 'Bpk Laimin', '', '', '', '', '', '', '', '', '08118801054', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2469, 'Deko', '', '', '', '', '', '', '', '', '087883440269', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2470, 'PT MUSTIKA SAMUDRA LESTARI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2471, 'PT GKM', '', '', '', '', '', '', '', '', '081284768205', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2472, 'IRFAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2473, 'erwer', '45345', 'noimage.jpg', 'noimage.jpg', 'Toko', '34534534', 'MR', 'EKA TEHNIK GF 1 B.21 NO 9', 'werw@sef.c', '324234', '34345', '5345345', '345345', 0, 'aktif', '2020-07-29 12:15:13', '2021-05-10 12:44:17', 1, 2),
(2474, 'bpk. amriel (danel)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2475, 'tridaya lt 2 blk B 17 no 8', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2476, '', '', '', '', '', '', '', 'PT. Ina Multi Trans', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2477, '', '', '', '', '', '', '', 'PT. Putindo Trada Wisesa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2478, 'Ibu Tiara (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2479, '', '', '', '', '', '', '', 'TULUS HARAPAN', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2480, 'Bpk. Rengga', '', '', '', '', '', '', '', '', '085213744833', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2481, '', '', '', '', '', '', '', 'PT DINGXIN BOGA INDONESIA   ( MAGELANG )', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2482, '', '', '', '', '', '', '', 'PT SHANGLI JAYA ABADI', '', '', '', 'Jl. Dr. Makaliwe Raya No. 48 A Grogol - Jakarta Barat 11450', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2483, 'alex', '', '', '', '', '', '', '', '', '081399252038', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2484, '', '', '', '', '', '', '', 'Pt Dingxin Boga', '', '', '', 'Magelang', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2485, '', '', '', '', '', '', '', 'Daswin PNA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2486, 'CV tjipta asia perkasa', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2487, 'Bpk. Bimo (ps)', '', '', '', '', '', '', '', '', '0811716100', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2488, '', '', '', '', '', '', '', '', '', '081295199008', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2489, 'Bpk.Gilang', '', '', '', '', '', '', '', '', '081296253788', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2490, 'BPK.RONI', '', '', '', '', '', '', '', '', '081382626638', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2491, 'Bpk. Reza', '', '', '', '', '', '', '', '', '0895332347088', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2492, 'bu join', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2493, '', '', '', '', '', '', '', 'PT. ARKOM', '', '087716611717', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2494, 'Bpk. Edwid', '', '', '', '', '', '', '', '', '08111737777', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2495, 'adit', '', '', '', '', '', '', 'PT Aneza persada abadi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2496, '', '', '', '', '', '', '', 'PT. Indonesia Technical Machinery', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2497, '', '', '', '', '', '', '', 'Pt.Asia New World Business', '', '', '', 'Kediri', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2498, 'Bpk Gultom', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2499, 'Bpk. Johanes', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2500, '', '', '', '', '', '', '', 'PT. Cipta Design Indonesia', '', '081314541854', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2501, 'Novi ex tripela', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2502, '', '', '', '', '', '', '', 'Cv Rawikara Sukses Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2503, 'Ibu. Suli', '', '', '', '', '', '', '', '', '082298679954', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2504, 'bpk budi', '', '', '', '', '', '', '', '', '0215823114', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2505, 'tosuya', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2506, '', '', '', '', '', '', '', 'PT. ADHIM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2507, 'RASINDO TATA LAKSANA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2508, 'TINA-', '', '', '', '', '', '', 'MANDIRI PERSADA INDONESIA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2509, 'YANTI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2510, 'Bpk. Tedi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2511, 'Ibu. Intan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2512, 'Bpk. Deni', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2513, 'Widiaryanto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2514, 'PT POLARINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2515, 'PT. prayoga mandiri sukses (ps)', '', '', '', '', '', '', '', '', '02182482710', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2516, 'Bpk. Andri', '', '', '', '', '', '', '', '', '085711256680', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2517, 'Ibu fara', '', '', '', '', '', '', '', '', '081217423879', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2518, 'IRFAN', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2519, 'Bpk. Alan', '', '', '', '', '', '', 'PT. Quan Kontena Logistics', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2520, 'Bpk. Nugroho Maulana', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2521, 'Bpk. Iwan', '', '', '', '', '', '', '', '', '081310663873', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2522, '', '', '', '', '', '', '', 'Toko Safety Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2523, 'Kahfi', '', '', '', '', '', '', '', '', '08561122530', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2524, '', '', '', '', '', '', '', 'TOKO SATRIA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2525, 'Bpk. Dodo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2526, '', '', '', '', '', '', '', 'Toko Sahabat Makmur', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2527, 'Ibu Susan', '', '', '', '', '', '', 'Toko Metro Tehnik G', '', '081287883377', '', 'gedung glodok blustru lantai dasar no 189 ', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2528, 'Bpk. Tedi Gunaidi (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2529, 'Yani kiat (ps)', '', '', '', '', '', '', '', '', '081241352734', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2530, 'Bpk. Omon', '', '', '', '', '', '', '', '', '081585455312', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2531, 'Bpk. Setyo adi nugroho', '', '', '', '', '', '', '', '', '082221149459', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2532, 'pt energi kreatif', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2533, '', '', '', '', '', '', '', 'SIC', '', '(021) 6268820', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2534, 'Bpk. Sandy', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2535, 'PT. Sun lee jaya', '', '', '', '', '', '', '', '', '02186862722', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2536, 'PT exytos mitra aditama', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2537, 'INDECO METAL JAYA INDO', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2538, '', '', '', '', '', '', '', 'PT ASCENDS GROUP INDONESIA', '', '', '', 'Ruko Grand Palace B-10 , Jl. Benyamin Sueb Blok. A5, Kebon Kosong Kemayoran , Jakarta Pusat , DKI Jakarta', '', 5, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2539, 'YUMAINI', '', '', '', '', '', '', '', '', '087773538065', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2540, 'Bpk. Bebi Pranata (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2541, 'bona sakti', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2542, '', '', '', '', '', '', '', 'PT.Inti Makmur', '', '6621582', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2543, 'warno', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2544, 'berkah eco jl labu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2545, 'evi', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2546, 'FIANTO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2547, 'Bpk. Leo', '', '', '', '', '', '', '', '', '081219178590', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2548, 'cash', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2549, 'Bp Yakup', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2550, '', '', '', '', '', '', '', 'Pt Himalaya Transmeka', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2551, 'Bpk. Rudi (ps)', '', '', '', '', '', '', '', '', '085242523323', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2552, 'dedi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2553, 'Bpk Syaifuddin nur (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2554, 'Bpk Ibni', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2555, '', '', '', '', '', '', '', 'PT. Nohara Alta Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2556, '', '', '', '', '', '', '', 'PT. Hwasung Thermoindo (ps)', '', '0213504550', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2557, '', '', '', '', '', '', '', 'Nusantara Tehnik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2558, '', '', '', '', '', '', '', 'PT. HUA XIA', '', '082261615966', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2559, 'PT. Hutama Karya (Persero) (ps)', '', '', '', '', '', '', '', '', '082176609513', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2560, 'agung pramudya (Ps)', '', '', '', '', '', '', '', '', '0858-8211-5380/0815-7453-8865', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2561, 'robi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2562, 'LT UG BLOK C28 NO.1-2', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2563, 'Bpk. E.D.Lambri', '', '', '', '', '', '', '', '', '0811136010', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2564, 'DAMAI', '', '', '', '', '', '', 'LT UG', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2565, 'HENGKY', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2566, '', '', '', '', '', '', '', 'Inti Makmur', '', '6621582', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2567, 'Bpk. Adrian', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2568, 'Bpk. Ersan (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2569, 'Iwatani', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2570, 'PT PERMATA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2571, 'Bpk dwi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2572, 'pt jamelca indo kaja', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2573, '', '', '', '', '', '', '', 'PT. SENA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2574, 'PT SINAR SURYA PUTRA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2575, 'Bpk. Alex', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2576, 'PT prima tunggal', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2577, 'Bpk. Ani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2578, '', '', '', '', '', '', '', 'PT INDONESIA PLANTATION SYNERGY', '', '', '', 'KOMP BALIKPAPAN BARU, SENTRA EROPA BLOK AA 1B NO 1 , BALIKPAPAN', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2579, 'PT.GLOBAL TEKNIK SENTOSA (GF2)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2580, 'Bpk.Rudi', '', '', '', '', '', '', '', '', '085211429613', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2581, 'Bpk.andi zabur (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2582, '', '', '', '', '', '', '', 'Balai Samudera', '', '455851721', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2583, 'IBU SITI / RESSA', '', '', '', '', '', '', '', '', '081314197999 , 085697421516', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2584, 'bpk eko', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2585, 'Bpk.ivan (ps)', '', '', '', '', '', '', '', '', '081321333361', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2586, 'KAOLIN INDONESIA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2587, 'Bpk.agung (ps)', '', '', '', '', '', '', '', '', '082141442880', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2588, 'NATALINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2589, 'HAI CHEN TALC POWDER INDONESIA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2590, '', '', '', '', '', '', '', 'PT.SBS', '', '081908060617', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2591, 'adam', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2592, 'DENY-PAPUA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2593, 'Sherly', '', '', '', '', '', '', '', '', '', '', 'mangga 2 Pasar pagi', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2594, 'asau', '', '', '', '', '', '', 'jl kalianyar 1 no.04 rt 08/10 jembatan besi-tambora . jakbar', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2595, '', '', '', '', '', '', '', 'PT. ANUGRAH BARLIAN', '', '085624094321', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2596, 'tanto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2597, 'RONNY/ BU ENDAH', '', '', '', '', '', '', 'TERANG LOGAM', '', '02159306964/081319922512', '', 'JL INDUSTRI III BLOK AD NO.33 \r\nKAWASAN INDUSTRI JATAKE\r\nCIKUPA\r\nTANGERANG', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2598, 'Bpk.jeffry hidayat (ps)', '', '', '', '', '', '', '', '', '08116200111', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2599, '', '', '', '', '', '', '', 'LILO Sport', '', '08991105555', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2600, '', '', '', '', '', '', '', 'Toko Stainless Steel', '', '', '', 'LTC GLODOK\r\nSEMI BASEMENT Blok E 6-7', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2601, 'Bp Fauzy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2602, 'Bpk. Supri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2603, 'ALAM TEHNIK SEMESTA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2604, 'Bpk. Aryo', '', '', '', '', '', '', '', '', '081388788979', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2605, 'ANTON', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2606, 'Bpk. Gama', '', '', '', '', '', '', '', '', '081213222293', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2607, '', '', '', '', '', '', '', 'PT. ALBORG', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2608, 'Bpk. Gama', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2609, '', '', '', '', '', '', '', 'PT. Ace Nusa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2610, 'Ibu Ima (ps)', '', '', '', '', '', '', 'PT. Rekakomindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2611, 'SETIAWAN', '', '', '', '', '', '', '', '', '081314640898', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2612, 'Bpk sony', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2613, 'Bpk. Ahen', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2614, 'PT.MMS (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2615, 'Setiawan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2616, 'Ibu. Sari', '', '', '', '', '', '', '', '', '085697379738', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2617, 'Bpk. Nino Agung (ps)', '', '', '', '', '', '', 'basarnas', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2618, 'pt balikpapan ready mix', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2619, '', '', '', '', '', '', '', 'PT. Darma Adiyasa Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2620, 'Bpk. Ardin', '', '', '', '', '', '', '', '', '08562120505 / 081316160505', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2621, 'Bpk. Andre (ps)', '', '', '', '', '', '', '', '', '08122626259', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2622, 'PT.Andarawinatama kerta harja (ps)', '', '', '', '', '', '', '', '', '+62 813-1700-2725', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2623, 'PT CGICOP', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2624, 'Ibu. Rentina', '', '', '', '', '', '', '', '', '085362988129', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2625, 'TOKO ASIA JAYA SAFETY', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2626, 'Bpk. Kuswanto', '', '', '', '', '', '', '', '', '081281330261', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2627, '', '', '', '', '', '', '', 'CV Wira Arya Sejahtera', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2628, '', '', '', '', '', '', '', 'CV. DWIJAYA MANDIRI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2629, '', '', '', '', '', '', '', 'MAJU ABADI', '', '081366111155', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2630, 'Ibu. Yanti', '', '', '', '', '', '', '', '', '0811738190', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2631, 'Bpk. Edmond', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2632, 'Ibu angelrina', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2633, 'RINA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2634, '', '', '', '', '', '', '', 'Bahtera Safety', '', '', '', 'UG ', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2635, 'PT. Andalan Tiga Berlian', '', '', '', '', '', '', '', '', '089513415786', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2636, 'CV ALAT BELUGA', '', '', '', '', '', '', '', '', '', '', 'LT UG C28 N0.1-2\r\nLTC GLODOK', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2637, '', '', '', '', '', '', '', 'PT. NITRA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2638, 'IAN', '', '', '', '', '', '', 'PT PRAKARSA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2639, 'ridho', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2640, 'Bpk. Ibrahim (ps)', '', '', '', '', '', '', '', '', '081389692595', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2641, 'Bpk. Carli', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2642, 'ERNA, TOKO BINTANG PRIMA', '', '', '', '', '', '', 'JL MT HARYONO , RUKO BALIKPAPAN BARU BLOK B-3 NO.25 BALIKPAPAN', '', '081254986363', '', '', '', 4, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2643, 'BPK.BAYU', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2644, 'Dito', '', '', '', '', '', '', 'pt hanindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2645, '', '', '', '', '', '', '', 'STAR JAYA LESTARI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2646, 'Bpk Abdul', '', '', '', '', '', '', 'PT. Wahana Pamunah Limbah Industri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:13', 1, 1),
(2647, 'setar safety', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:13', '2020-07-29 12:15:14', 1, 1),
(2648, 'Bpk. Usman', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2649, 'SUMAR', '', '', '', '', '', '', 'TAMAN VILLA BARU BLOK G NO.3 RT 08/02 ,PEKAYON JAYA, BEKASI SELATAN', '', '081519227210', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2650, 'Bpk. Abdul', '', '', '', '', '', '', 'PT. MITRA BOR NUSANTARA', '', '081213500651', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2651, 'LUKMAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2652, 'PAK ZUHRI', '', '', '', '', '', '', 'PT INTAN CIPTA PERDANA', '', '081519227210/081399142717', '', 'TAMAN VILA BARU BLOK G NO.3 RT 08/02 \r\nPEKAYON JAYA\r\nBEKASI SELATAN 17148', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2653, 'VERO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2654, '', '', '', '', '', '', '', 'Pt Intercoach Safety Services', '', '0542-875090', '', 'Balikpapan Kalimantan Timur', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2655, 'BP.DIAN', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2656, '', '', '', '', '', '', '', 'PT. INDOBANGUN', '', '081310222377', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2657, 'Ibu. Helen (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2658, '', '', '', '', '', '', '', 'PT. DUTA PERKAKAS (PS)', '', '082122400866', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2659, 'benteng mandiri lt 2', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2660, 'PT MODERN WIDYA TEHNICAL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2661, 'PT JAYAPURA PASIFIK PERMAI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2662, 'RAFIYUDIN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2663, '', '', '', '', '', '', '', 'PT. DARMA PREMAMANDALA', '', '081317900045', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2664, '', '', '', '', '', '', '', 'PT. ALAM RAYA ELYNDO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2665, '', '', '', '', '', '', '', 'PT SANWA PREFAB TECHNOLOGY', '', '', '', 'JL RAYA INDUSTRI III BLOK AE NO.6\r\nBUNDER CIKUPA\r\nKAB. TANGERANG BANTEN', '', 5, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2666, 'PT MEGANTARA AGUNG PERSADA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2667, 'bpk. hafizh', '', '', '', '', '', '', '', '', '081317002725', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2668, 'Bpk. Dwi (ps)', '', '', '', '', '', '', '', '', '081298366445', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2669, '', '', '', '', '', '', '', 'Pt pangada maju berkah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2670, 'pt solusi mitra prsada', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2671, 'andriansyah', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2672, 'PT KELINCI MAS UNGGUL', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2673, '', '', '', '', '', '', '', 'PT. TRISINDO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2674, 'Bpk. Sain', '', '', '', '', '', '', '', '', '082112244801', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2675, 'LT UG BLOK B7 NO.1', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2676, 'Bpk. Rahman', '', '', '', '', '', '', '', '', '082113822290', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2677, 'Bpk. Rahman', '', '', '', '', '', '', '', '', '082113822290', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2678, 'Ibu. Clarus', '', '', '', '', '', '', '', '', '082114691992', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2679, 'wahyu indrajaya', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2680, 'bp nanasetiana', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2681, 'DANIEL LT UG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2682, 'apit', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2683, 'Bpk. Agus', '', '', '', '', '', '', '', '', '082123279980', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2684, 'Bpk. Adit', '', '', '', '', '', '', '', '', '081290929342', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2685, 'bu nurhayati', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2686, '', '', '', '', '', '', '', 'PT. LUNTO', '', '082114258989', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2687, '', '', '', '', '', '', '', 'PT. DVS', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2688, '', '', '', '', '', '', '', 'Toko Laser', '', '', '', 'Jakarta', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2689, '', '', '', '', '', '', '', 'Cahaya Baru Cinde', '', '0811715812', '', 'jl Letnan jaimas n0.823 \r\nPalembang', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2690, 'Dito HI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2691, 'sodik', '', '', '', '', '', '', 'pt nitro', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2692, '', '', '', '', '', '', '', 'Pt Unicorn', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2693, 'Bpk sofian (ps)', '', '', '', '', '', '', 'PT. Kawan Lama Sejahtera', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2694, 'Bpk. Rudyharto', '', '', '', '', '', '', 'kapuk baja (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2695, 'karya makmur lt 1 c30 no 31', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2696, 'ARUM', '', '', '', '', '', '', 'PT SUPLINTAMA MAJUSEMESTA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2697, 'PT WASKITA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2698, '', '', '', '', '', '', '', 'PT LANGGAI AGRINDO AGUNG (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2699, 'TOKO HR', '', '', '', '', '', '', '', '', '081286003530', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2700, 'GMP', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2701, 'Ibu. Intan', '', '', '', '', '', '', 'PT.Technodrives', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2702, 'Ibu. Widi', '', '', '', '', '', '', '', '', '085694366869', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2703, '', '', '', '', '', '', '', 'Anugrah Elektrik', '', '', '', 'Lt.2 c30 no.2', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2704, 'pt jasa mora sejahtra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2705, '', '', '', '', '', '', '', 'SURYA TOOLINDO', '', '021-62201420', '', 'GF2 BLOK B20 NO 9', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2706, '', '', '', '', '', '', '', 'PT. Sasana Teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2707, 'GARDEN HOTEL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2708, 'Nabel', '', '', '', '', '', '', 'Toko bukit barisan', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2709, 'Bp.Sanusi BEP', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2710, 'Bpk. Rafli ramadhan (ps)', '', '', '', '', '', '', '', '', '0895373610338', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2711, 'Bpk. Rastami (ps)', '', '', '', '', '', '', '', '', '0818589127', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2712, 'Bpk. Tang bin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2713, 'bpk. iwan iskandar', '', '', '', '', '', '', '', '', '085691333456', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2714, 'Ibu Rusmaniur Sinambela (ps)', '', '', '', '', '', '', '', '', '081371370646/081365677634', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2715, '', '', '', '', '', '', '', 'RED PARKER', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2716, 'Bpk. Irawan', '', '', '', '', '', '', '', '', '08161116215', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2717, '', '', '', '', '', '', '', 'PT. Cahaya Sekar Cargo (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2718, 'Pak Yono', '', '', '', '', '', '', 'Cv Taluma Eka Jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2719, 'Akim', '', '', '', '', '', '', 'Cendrawasih', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2720, 'DAVIS', '', '', '', '', '', '', 'TOKO SUMBER JAYA 87', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2721, 'PT surya teknindo perkasa', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2722, 'SKA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2723, 'DAUD', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2724, '', '', '', '', '', '', '', 'Mitra Buana Gasindo (BEKASI)', '', '081315394747', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2725, 'Bpk. Aditya Pramudita (ps)', '', '', '', '', '', '', '', '', '081290929342', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2726, 'Ci Evi', '', '', '', '', '', '', 'Harapan Agung', '', '', '', 'LTC Lt.2', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2727, 'sinnohydro sumedang', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2728, 'Bpk. Asep', '', '', '', '', '', '', '', '', '083819186400', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2729, 'Bpk. Richard Aifil F (ps)', '', '', '', '', '', '', '', '', '085267928547', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2730, 'ILHAM', '', '', '', '', '', '', 'KEPUNDUNG 31A DEPAN FIT REFLEXY MALANG', '', '0816708470', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2731, '', '', '', '', '', '', '', 'Naudil Teknik', '', '', '', 'Lt.1 c30 23', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2732, '', '', '', '', '', '', '', 'PT. Ananta Mitra Selaras (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2733, 'Ny. Elna', '', '', '', '', '', '', 'Azizy', '', '081210049755', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2734, '', '', '', '', '', '', '', 'PT. Tirta', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2735, '', '', '', '', '', '', '', 'waskita teknik', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2736, 'SURYA', '', '', '', '', '', '', 'TOKO SURYA', '', '085693518207', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2737, 'Bpk. Fernando Charles Gunawan (ps)', '', '', '', '', '', '', '', '', '0881351943798', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2738, '', '', '', '', '', '', '', 'KUBERA SAFETY', '', '62320515', '', 'LT GF2 C16 NO 2', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2739, 'Ibu. Yuana', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2740, '', '', '', '', '', '', '', 'PT LAUTAN JAYA BERLIAN', '', '02122673019   -  081296164984', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2741, '', '', '', '', '', '', '', 'PT KARYA CIPTA METALINDO PERKASA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2742, 'yeni', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2743, '', '', '', '', '', '', '', 'Sentral Net', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2744, 'Bpk. Handoko Wiadji (ps)', '', '', '', '', '', '', '', '', '0816655226', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2745, 'DEDE', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2746, 'Margareta', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2747, 'bpk alex', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(2748, 'Bpk. Herlan Husada (ps)', '', '', '', '', '', '', '', '', '081395586939', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2749, '', '', '', '', '', '', '', 'Kawan Lama Sejahtera pt', '', '', '', 'Jl.Puri Kencana No.1 Meruya \r\nJakarta 11610', '', 5, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2750, 'mahkota elang enternusa', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2751, 'Bpk. Abdul', '', '', '', '', '', '', '', '', '082213383399', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2752, 'nathan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2753, '', '', '', '', '', '', '', 'Suratelindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2754, '', '', '', '', '', '', '', 'pt.Eka Sinar Abadi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2755, 'Bpk. Fajar Kawolu (ps)', '', '', '', '', '', '', 'PT. LEN Industri', '', '081322186335', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2756, '', '', '', '', '', '', '', 'PT. INDOKARSA', '', '087877286601', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2757, 'KO ASENG', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2758, 'Bpk. Sapta', '', '', '', '', '', '', '', '', '085216251650', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2759, 'Bpk HERU', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2760, '', '', '', '', '', '', '', 'PT. Aras Global Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2761, '', '', '', '', '', '', '', 'Bpk. Andry harmawan (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2762, '', '', '', '', '', '', '', 'CV. Cahaya Gemilang (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2763, '', '', '', '', '', '', '', 'PT. Bina Baja Sejati (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2764, '', '', '', '', '', '', '', 'PT. China Comservice Indonesia (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2765, 'JANURI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2766, 'Bpk. Harul', '', '', '', '', '', '', '', '', '081297115453', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2767, 'Bpk. Karyanto', '', '', '', '', '', '', '', '', '082115857421', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2768, '', '', '', '', '', '', '', 'Toko Total Safety 86', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2769, 'Ibu. Amelia (ps)', '', '', '', '', '', '', '', '', '08119805123', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2770, '', '', '', '', '', '', '', 'AB 3 SERVICE', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2771, 'Bpk. Muhammad Amin (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2772, '', '', '', '', '', '', '', 'PT. Bintai Kindenko Engineering (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2773, 'GARUDA ENERGI LOGISTIK KOMERSIAL', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2774, '', '', '', '', '', '', '', 'PT CAHAYA ABADI TEHNIK', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2775, 'PT china harbour indonesia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2776, 'Bpk. Lukman', '', '', '', '', '', '', '', '', '081316658182', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2777, 'Le La', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2778, 'Bpk. Billy', '', '', '', '', '', '', 'MYGAS', '', '081517132321', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2779, '', '', '', '', '', '', '', 'Pt.Persada Batavia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2780, 'DULMARINE', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2781, 'Bpk Bashir', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2782, 'Bpk Dede', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2783, 'Ibu. Eka', '', '', '', '', '', '', '', '', '081314663205', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2784, 'H. Firdaus', '', '', '', '', '', '', 'Bukit Barisan', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2785, 'Bpk. Wandi', '', '', '', '', '', '', '', '', '082292343344', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2786, 'Bpk. Asrifal', '', '', '', '', '', '', '', '', '085222881549', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2787, 'pt berkah anugrah makmur sejati', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2788, 'KALIMANTAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2789, '', '', '', '', '', '', '', 'PT. Kharisma Multikarya Abadi', '', '0217818215', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2790, 'Bpk. Fahdan', '', '', '', '', '', '', '', '', '081213481241', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2791, 'Bpk. Yanwar Effendi (ps)', '', '', '', '', '', '', '', '', '081294590610', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2792, 'Pak de', '', '', '', '', '', '', 'wonogiri sukses', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2793, 'Ibu. Nita', '', '', '', '', '', '', '', '', '083876356140', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2794, 'haji samlawi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2795, '', '', '', '', '', '', '', 'PT. Elhifa (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2796, 'pak tiko', '', '', '', '', '', '', 'kodam', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2797, 'H. DODI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2798, 'GARDA PRIMA JAYA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2799, 'Bpk. Feri', '', '', '', '', '', '', '', '', '087887112111', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2800, '', '', '', '', '', '', '', 'ASIA JAYA SAFETY', '', '08118391000', '', 'GF1 B105', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2801, 'bpk tian', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2802, 'Bpk. Mukhlisim', '', '', '', '', '', '', '', '', '085719673444', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2803, 'PT. Marindotech Gresik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2804, '', '', '', '', '', '', '', 'Pt Samba Arnavat Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2805, '', '', '', '', '', '', '', 'Pt Mulia Jaya Mandiri Jakarta', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2806, 'PT.MANDIRI ABADI GASINDO', '', '', '', '', '', '', '', '', '081315394747', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2807, '', '', '', '', '', '', '', 'PT. ANUGRAH GASINDO ABADI', '', '081315394747', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2808, 'Bpk. Jon', '', '', '', '', '', '', '', '', '081274738474', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2809, '', '', '', '', '', '', '', 'Pt Pristine Prima Lestari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2810, 'Ibu. Fransisca', '', '', '', '', '', '', '', '', '081249777804', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2811, 'Bpk. Asep', '', '', '', '', '', '', '', '', '0817808770001', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2812, 'Bpk. Irsyad (ps)', '', '', '', '', '', '', '', '', '081240001145', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2813, 'Bpk. Eka Sutisna (ps)', '', '', '', '', '', '', '', '', '087771119224', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2814, '', '', '', '', '', '', '', 'CV. Bintang Kemilau Nusantara (ps)', '', '08179132813', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2815, '', '', '', '', '', '', '', 'PT HANATA PRATAMA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2816, 'RAS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2817, '', '', '', '', '', '', '', 'PT. HI-COOK (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2818, 'APNER', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2819, 'Bpk Edward Geong', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2820, 'Ibu. Sofiawati (ps)', '', '', '', '', '', '', '', '', '081380921976', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2821, 'Ibu Lulu (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2822, '', '', '', '', '', '', '', 'PT. Catur Andalan Persada', '', '082216810566', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2823, '', '', '', '', '', '', '', 'Valvio Inti Prima', '', '082216436333', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2824, 'MAHESA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2825, 'PT TABGHA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2826, 'TMC', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2827, 'Bpk. Mulyadi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2828, 'Bpk. Unggul', '', '', '', '', '', '', '', '', '081286890528', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2829, 'usep', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2830, 'vivi dr osha', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2831, 'PT GLOBAL JET EXPRESS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2832, 'Bpk. Raka (ps)', '', '', '', '', '', '', 'Pamadya Catering', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2833, 'Bpk. Riki (ps)', '', '', '', '', '', '', '', '', '081389846626', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2834, 'Bpk. Hans', '', '', '', '', '', '', '', '', '085781850500', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2835, 'PT FOKUS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2836, 'pt sukses jaya', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2837, '', '', '', '', '', '', '', 'Kepala Biro Operasi Polda Jambi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2838, '', '', '', '', '', '', '', 'JAKET BAJA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2839, 'Bpk. Sukarso', '', '', '', '', '', '', '', '', '081519407050', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2840, 'Pak Agung', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2841, '', '', '', '', '', '', '', 'PT. GAZINDO RAYA', '', '082312301432', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2842, 'Bpk. Abdul', '', '', '', '', '', '', '', '', '082215511170', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2843, 'Bpk. Burhan', '', '', '', '', '', '', 'PT. FASCO', '', '08561366604', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2844, 'Putri Peron (ps)', '', '', '', '', '', '', '', '', '089674561425', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2845, 'Bpk.Rizki', '', '', '', '', '', '', '', '', '08111820603', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2846, 'Ibu. Rosdiana (ps)', '', '', '', '', '', '', 'PT. KINTETSU', '', '089601904698', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2847, 'Bpk. Ari', '', '', '', '', '', '', '', '', '087808789529', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2848, 'SUKSES MAJU SEJAHTERA', '', '', '', '', '', '', 'LT 1 LTC GLODOK', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2849, 'Bpk. Berti / Angki', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2850, 'YATI', '', '', '', '', '', '', 'PERUM LIMUS PRATAMA JL. BLITAR 3 BLOK E6 NO.11 CILEUNGSI, BOGOR', '', '081383354976', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2851, 'Ibu. Devi (ps)', '', '', '', '', '', '', 'ACSET', '', '081210645214', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2852, 'gren hosana', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2853, 'Pt bhineka kontraktor persada', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2854, 'EKA RIVALSON', '', '', '', '', '', '', 'JL KAMBOJA II TANJUNG PANDAN , BELITUNG', '', '08197898136', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2855, '', '', '', '', '', '', '', 'Pt Jaya Bintang Semesta', '', '', '', 'Jl Bandengan Utara', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2856, '', '', '', '', '', '', '', 'SMK AL-INTISAB (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2857, 'Bpk. Deri septiadi (ps)', '', '', '', '', '', '', '', '', '087820053727', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2858, '', '', '', '', '', '', '', 'Sekolah Relawan', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2859, '', '', '', '', '', '', '', 'PT. Mulia Anugerah Sejahtera (ps)', '', '08112663905', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2860, 'HENGKY TJHAI', '', '', '', '', '', '', 'STAR FOLDING GATE  JL.LETKOL H ASNAWI ( JL MT HARYONO DALAM RT 51 NO.125 BALIKPAPAN', '', '081254947399/085737390808', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2861, 'RUDY', '', '', '', '', '', '', 'HTC', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2862, 'Bpk. Suwandi', '', '', '', '', '', '', '', '', '089681435191', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2863, 'Bpk. Rahardian', '', '', '', '', '', '', '', '', '08121375520', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2864, '', '', '', '', '', '', '', 'RITRA CARGO', '', '08158357825', '', '', '', 1, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2865, 'MANAZEL MASHAER', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:14', '2020-07-29 12:15:14', 1, 1),
(2866, 'ZUL', '', '', '', '', '', '', 'TOKO HR', '', '081286003530', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2867, 'Bpk. Arwan (ps)', '', '', '', '', '', '', '', '', '081213513169', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2868, 'HARMEN', '', '', '', '', '', '', 'TOKO HARMEN', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2869, 'RAS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2870, 'Bpk. Heri (ps)', '', '', '', '', '', '', '', '', '081318723451', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2871, 'Bpk. Fajar Alfianto', '', '', '', '', '', '', '', '', '081315133002', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2872, 'Bpk. Purwanto', '', '', '', '', '', '', '', '', '081314255457', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2873, 'Harsing', '', '', '', '', '', '', 'Sentral Perkakas', '', '082284768881', '', 'Jl.Ir H.Juanda No.64\r\nPekanbaru', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2874, 'Bpk. Dahlan', '', '', '', '', '', '', '', '', '081282151849', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2875, 'Bpk. Saat (ps)', '', '', '', '', '', '', '', '', '081297329589', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2876, 'bpk. andi idris', '', '', '', '', '', '', '', '', '081288876617', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2877, 'Ibu Romy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2878, 'Ibu. Eva (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2879, 'Ibu. Vita', '', '', '', '', '', '', '', '', '087887186677', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2880, 'OLIME TJU', '', '', '', '', '', '', 'THERMALINDO JL.ERLANGGA NO.49 RT.12 LORONG BUDIMAN , TALANG BANJAR KEL.SULANJANA. KEC JAMBI TIMUR (B', '', '08127404028', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2881, 'PT Adikari', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2882, '', '', '', '', '', '', '', 'PT. SI', '', '085695279563', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2883, 'Ibu. Eny (ps)', '', '', '', '', '', '', '', '', '08561201360', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2884, '', '', '', '', '', '', '', 'TOKO SINAR PURNAMA TEKNIK', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2885, '', '', '', '', '', '', '', 'Duta Mas Auto', '', '081248356789', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2886, '', '', '', '', '', '', '', 'PT. Cakra Nusa Perkasa', '', '0216907408', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2887, 'Bpk. Asep', '', '', '', '', '', '', '', '', '081312129412', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2888, '', '', '', '', '', '', '', 'PT JARING', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2889, 'Bpk. Wawan', '', '', '', '', '', '', '', '', '083815370029', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2890, 'CV.ENCLE BERKAH JAYA', '', '', '', '', '', '', '', '', '0812608970', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2891, '', '', '', '', '', '', '', 'Toko Samstek Kuliner Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2892, 'Bpk.Iman (ps)', '', '', '', '', '', '', '', '', '087888333303', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2893, 'Bpk. Maman', '', '', '', '', '', '', 'PT. SPL', '', '085217551477', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2894, '', '', '', '', '', '', '', 'PT. NUTRINDO JOGASIMA', '', '087777691986', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2895, 'Radja Mandala (ps)', '', '', '', '', '', '', '', '', '089631315449', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2896, 'Bpk. Alif', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2897, 'Bpk. Noval', '', '', '', '', '', '', '', '', '085313833199', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2898, 'Pt biro klasifikasi indonesia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2899, 'PT SIO', '', '', '', '', '', '', '', '', '0816637776', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2900, 'LIFTINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2901, 'Mr Wang', '', '', '', '', '', '', '', '', '', '', 'UG C28 No.1 - 2', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2902, 'bpk adin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2903, 'Bpk. Asep (hemirat)', '', '', '', '', '', '', '', '', '0811931937', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2904, 'Bpk. Nana', '', '', '', '', '', '', '', '', '081287314380', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2905, 'costumer', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2906, 'Bpk. Firman', '', '', '', '', '', '', '', '', '085921257356', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2907, '', '', '', '', '', '', '', 'PT. MARSHARI SOLUSI PRATAMA', '', '081218414502', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2908, 'pt tegap mitra nusantara', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2909, 'Ibu Ida', '', '', '', '', '', '', '', '', '', '', 'Bekasi', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2910, 'Toko Baja Tehnik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2911, '', '', '', '', '', '', '', 'Sinar Makmur', '', '', '', 'gf 2 B7/8', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2912, 'Bp Rudi', '', '', '', '', '', '', '', '', '082110673700', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2913, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM PROJECT TEAM SINOHYDRO SUKAJAYA', '', '', '', 'DI PANJAITAN KAV 9 - 10 CIPINANG CEMPEDAK, JATINEGARA JAKARTA TIMUR DKI JAKARTA', 'SUKAJAYA', 5, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2914, 'RIRI', '', '', '', '', '', '', 'TOKO RIRI', '', '081383366109', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2915, 'Ibu Novi', '', '', '', '', '', '', 'Toko Victor Teknik', '', '0817795585', '', 'lt ug blok c9 no 2', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2916, '', '', '', '', '', '', '', 'PT. INTI MULIA PROFILINDO', '', '085715093116', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2917, 'Bpk. Kelvin', '', '', '', '', '', '', '', '', '085773473473', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2918, 'Bpk. Mulyadi Saputra', '', '', '', '', '', '', '', '', '085215153223', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2919, 'Bpk. Kim Chun', '', '', '', '', '', '', '', '', '082124960010', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2920, 'Bp.Dodiek', '', '', '', '', '', '', 'Pt Hilti', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2921, 'Bpk. ilham nusa', '', '', '', '', '', '', '', '', '081336823540', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2922, 'Bpk. Aris', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2923, 'Personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2924, 'Personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2925, 'UDA', '', '', '', '', '', '', 'TOKO REKLAME', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2926, '', '', '', '', '', '', '', 'PT. Rajawali', '', '082198676587', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2927, 'Bp Maman', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2928, 'Mr Liu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2929, 'ADI GUNA', '', '', '', '', '', '', 'TOKO ADIGUNA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2930, '', '', '', '', '', '', '', 'CV. Prima Utama (ps)', '', '08112995429', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2931, '', '', '', '', '', '', '', 'PT.Plaza Tirta', '', '081317557099', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2932, '', '', '', '', '', '', '', 'PT. CENTRATAMA MENARA INDONESIA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2933, 'ERWIN DEGUCAI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2934, 'COSTUMER', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2935, '', '', '', '', '', '', '', 'Ridho Sahada', '', '', '', 'Kalimantan', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2936, 'Bpk. Wawan (ps)', '', '', '', '', '', '', '', '', '085781225913', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2937, '', '', '', '', '', '', '', 'PT KORPUS PRIMA ENERGI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2938, '', '', '', '', '', '', '', 'PT. Arianto Darmawan', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2939, 'Ibu. Herlinda (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2940, 'Bpk. Hermanto', '', '', '', '', '', '', '', '', '081291781993', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2941, '', '', '', '', '', '', '', 'CLYDE BERGEMANN', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2942, 'tri jaya       lt b20 no 6', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2943, 'budi', '', '', '', '', '', '', '', '', '087774293259', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2944, '', '', '', '', '', '', '', 'PT. BAUER Pratama Indonesia (PS)', '', '(021) 29661988', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2945, 'pt inti map', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2946, 'Bpk. Sidik', '', '', '', '', '', '', '', '', '081218197821', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2947, '', '', '', '', '', '', '', 'Bpk Moch Soegeng (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2948, 'Aris', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2949, '', '', '', '', '', '', '', 'PT. Aneka Jaya Langgeng Sentosa (ps)', '', '0811915898', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2950, 'Bpk. Wely', '', '', '', '', '', '', '', '', '08111017709', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2951, 'LINA', '', '', '', '', '', '', 'TOKO STARINDO', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2952, '', '', '', '', '', '', '', '', '', '081287869789', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2953, '', '', '', '', '', '', '', 'Pt Pilar Niaga Makmur', '', '', '', 'Batu Ceper\r\nTangerang', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2954, 'Bpk El', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2955, '', '', '', '', '', '', '', 'PUNDI MAS ARYOTA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2956, 'Ibu Yulie (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2957, 'Bpk. Gilang (ps)', '', '', '', '', '', '', '', '', '083817184929', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2958, '', '', '', '', '', '', '', 'CV. RIZA PRATAMA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2959, 'DEDY WONG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2960, 'Bpk. Rohmani', '', '', '', '', '', '', '', '', '081218599240', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2961, 'Bpk. Sudrajat', '', '', '', '', '', '', '', '', '081222104720', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2962, 'Bpk. Irham Daud (ps)', '', '', '', '', '', '', '', '', '0811433767', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2963, 'Bpk. Hotnikon Aritonang (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2964, 'AGUS', '', '', '', '', '', '', 'PT EHUA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2965, '', '', '', '', '', '', '', 'PT. Alpha Zamasto (ps)', '', '081281229173', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2966, 'bpk anwar', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2967, 'PT KUSUMA INDOTEKNIK- PASPAMPRES', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2968, 'PT KUSUMA INDOTEKNIK-CAWANG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2969, 'PT KUSUMA INDOTEKNIK-DEPOK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2970, 'PT DSU', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2971, 'CV EKA SURYA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2972, 'Bpk. Ilmi', '', '', '', '', '', '', '', '', '082140266400', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2973, 'Bp Soni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2974, '', '', '', '', '', '', '', 'LOBRA SAFETY', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2975, 'TB PUTRA NUSANTARA (HAN KAUW)', '', '', '', '', '', '', 'JL MANGGA BERSAR 1 NO.88A', '', '021-6240809/10', '', '', '', 0, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2976, 'TB PUTRA NUSANTARA (HAN KAUW)', '', '', '', '', '', '', 'JL MANGGA BESAR 1 NO.88 A', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2977, 'IWAN', '', '', '', '', '', '', 'DJM', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2978, 'Bpk YUDIANTO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2979, 'Bpk. Dian Yudiyanto', '', '', '', '', '', '', '', '', '081227277780', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2980, '', '', '', '', '', '', '', 'Tri Putra Makmur (Tanggerang)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2981, 'WENY', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2982, 'AAR', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2983, 'cv berkat timah sejahtera', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2984, '', '', '', '', '', '', '', 'PT BARADINAMIKA MUDASUKSES', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2985, 'hasta prima', '', '', '', '', '', '', '', '', '08119109679', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2986, '', '', '', '', '', '', '', 'PT. PERENTJANA DJAJA', '', '081311395192', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2987, '', '', '', '', '', '', '', 'PT PLN (persero ) UPP PJBB 2', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2988, '', '', '', '', '', '', '', 'RAY Cargo', '', '087887236003', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2989, '', '', '', '', '', '', '', 'Sukabumi Semesta', '', '75905115 / 081932538616', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2990, 'RIO TANAKA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2991, '', '', '', '', '', '', '', 'PT ARTISAN WAHYU', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2992, 'YOPI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2993, 'JERINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2994, 'HDR', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2995, 'JAJANG NURZAMAN', '', '', '', '', '', '', 'TOKO TRI ARGA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2996, 'TEDJO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2997, 'MSS-LT UG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2998, 'sinar gemilang lt gf1 rb no 23', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(2999, '', '', '', '', '', '', '', 'PT. Intersafe Prima Nusa (ps)', '', '087882258499', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3000, '', '', '', '', '', '', '', 'IMJP', '', '081310587931', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3001, 'GKM', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3002, 'Ibu. Nadia', '', '', '', '', '', '', '', '', '081398918601', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3003, 'ernes', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3004, 'Bpk Walid', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3005, '', '', '', '', '', '', '', 'PT. IMECO', '', '081219766618', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3006, 'Bpk. Byan (ps)', '', '', '', '', '', '', '', '', '085397372237', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3007, '', '', '', '', '', '', '', 'PT ISCO TEKNIK RUISINDO', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3008, 'Hatim husyaini (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3009, 'Ibu Andriawati', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3010, 'JATMIKO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3011, '', '', '', '', '', '', '', 'PT PRISTINE PRIMA LESTARI', '', '', '', 'Gd. Graha Prabada Samanta 1\r\nJl. Daan Mogot KM.12 No.9\r\nCengkareng, Jakarta Barat', '', 5, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3012, 'Bpk Nurdin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3013, '', '', '', '', '', '', '', 'Toko Hidayah Safety Indonesia', '', '', '', 'GF.1 Blok C.8 No.6', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3014, '', '', '', '', '', '', '', 'PT. TALC INDONESIA', '', '0816831877', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3015, 'SAMSUL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3016, '', '', '', '', '', '', '', 'PT. SAHABAT SURYA ELEKTRINDO', '', '08119952097', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3017, '', '', '', '', '', '', '', 'SUMARECON AGUNG TBK', '', '087887603524', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3018, 'bu endang', '', '', '', '', '', '', 'Pt amerkon teknindo', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3019, 'Bpk. Heru Rustanto (ps)', '', '', '', '', '', '', '', '', '082145375095', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3020, 'Bpk.Dwi (ps)', '', '', '', '', '', '', '', '', '089665505783', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3021, 'Personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3022, 'Personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3023, 'Personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3024, 'Ibu. Eni', '', '', '', '', '', '', '', '', '08128273168', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3025, 'PT BUANA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3026, 'Bpk. Erfan', '', '', '', '', '', '', '', '', '081285685100', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3027, 'Bpk. Pujo', '', '', '', '', '', '', '', '', '085218967470', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3028, '', '', '', '', '', '', '', 'Bpk Khairudin', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3029, '', '', '', '', '', '', '', 'PT GLOBAL PRATAMA WIJAYA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3030, 'Bpk. Edi', '', '', '', '', '', '', '', '', '081255661967', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3031, 'Bpk. Mufti (ps)', '', '', '', '', '', '', '', '', '081392820352', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3032, '', '', '', '', '', '', '', 'HIGH SPEED RAILWAY CONTRACTOR CONSORTIUM CHINA RAILWAY SIGNAL & COMMUNICATION (CRSC)', '', '', '', '', '', 5, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3033, 'Bpk. Farel', '', '', '', '', '', '', '', '', '081330747322', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3034, 'Bpk. Ari', '', '', '', '', '', '', '', '', '085713611159', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3035, 'Ibu susan (ps)', '', '', '', '', '', '', '', '', '082347506214', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3036, 'ATHAN', '', '', '', '', '', '', 'PT SINERGI DELAPAN MITRA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3037, 'Bpk. Fajri', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3038, 'AGUNG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3039, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3040, 'Bpk. Sosiawan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3041, 'marine maju mandiri lt 1', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3042, '', '', '', '', '', '', '', 'PT. PLN PUSHARLIS UP2W II (ps)', '', '081319152505', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3043, 'Bpk Budi', '', '', '', '', '', '', '', '', '08161988213', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3044, 'Bpk. Dwi', '', '', '', '', '', '', 'NAKINDO', '', '08111979616', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3045, '', '', '', '', '', '', '', 'CRRC', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3046, 'Bpk. Eddy', '', '', '', '', '', '', 'PT. DASATRIA UTAMA', '', '0215803334 / 08128490694', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3047, 'Bpk. Reza pahlevi (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3048, 'Bpk. Rendi', '', '', '', '', '', '', '', '', '08999271535', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3049, 'budy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3050, 'Bpk. Dimas Sindo (ps)', '', '', '', '', '', '', '', '', '082298272218', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3051, 'DONI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3052, 'PT JASA PRIMA MANDIRI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3053, 'Bpk. Ismanto', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3054, '', '', '', '', '', '', '', 'PT. CAKRA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3055, '', '', '', '', '', '', '', 'PT. AMS', '', '085267176717', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3056, 'Bpk. Faisal', '', '', '', '', '', '', '', '', '085215313102', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3057, 'Albert Liono', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3058, 'PT PRATAMA GRAHA SEMESTA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3059, '', '', '', '', '', '', '', 'Toko Putratama', '', '', '', 'Lantai GF1 Blok A16 No5', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3060, 'Martahan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3061, 'ANDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3062, '', '', '', '', '', '', '', 'PT. Appro Indonesia (ps)', '', '081315135040', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3063, 'Bpk. Abdulloh H A Malik', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3064, 'HARIANTO', '', '', '', '', '', '', 'TOKO CITRA MANDIRI', '', '082238588488', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3065, 'bpk sony', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3066, '', '', '', '', '', '', '', 'MITRA UTAMA ENERGI', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3067, 'Bpk. Kiki', '', '', '', '', '', '', '', '', '0811804377', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3068, 'Bpk.Dodi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3069, '', '', '', '', '', '', '', 'PT. Pratama Daya Cahya Manunggal', '', '085546394282', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3070, 'MULIA AGRA', '', '', '', '', '', '', 'LTC LT SB BLOK A NO.2', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3071, 'SUGENG', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3072, 'Bpk. Aris', '', '', '', '', '', '', '', '', '081234561230', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3073, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3074, 'PT SWAKARSA ENERGI PERSADA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3075, 'Bpk. Biliater Bagus Wicaksono', '', '', '', '', '', '', '', '', '081380570288', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3076, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3077, 'mekarindo lt ug blok A19 no 9', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3078, 'Bpk. Eko', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3079, '', '', '', '', '', '', '', 'PT JIA MAN XING', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3080, 'MAGGI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3081, '', '', '', '', '', '', '', 'PT. Lasallefood Indonesia (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3082, '', '', '', '', '', '', '', 'PT. LANCAR (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3083, '', '', '', '', '', '', '', 'PT. Jelajah Bahari Utama', '', '085692252098', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3084, 'Toko Ratu Safety Indonesia', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3085, 'pt indosterling sentra boga', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3086, 'Bpk. Efrain (ps)', '', '', '', '', '', '', '', '', '081218801934', '', '', '', 1, 'aktif', '2020-07-29 12:15:15', '2020-07-29 12:15:15', 1, 1),
(3087, 'Bpk. Hartanto', '', '', '', '', '', '', '', '', '081320917023', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3088, 'Hendra', '', '', '', '', '', '', 'Tunas Warna', '', '0732-3344657', '', 'JL Merdeka 712 Pasar . Tengah Kota Curup - Rejang Lebong', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(3089, 'Angga SK', '', '', '', '', '', '', 'EMP Bentu', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3090, '', '', '', '', '', '', '', 'UNIVERSAL DIESEL', '', '3844870 , 350 1424', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3091, 'arta mandiri teknik lt 1', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3092, 'SLAMET', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3093, 'Bpk Wahyu', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3094, 'Bpk. Wahyu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3095, 'Ibu. Fitri', '', '', '', '', '', '', '', '', '0895636031738', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3096, '', '', '', '', '', '', '', 'CV PUTRA BANGSA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3097, '', '', '', '', '', '', '', 'pt sinar suksers mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3098, '', '', '', '', '', '', '', 'PT. TMEIC ASIA INDONESIA', '', '021 29661699', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3099, 'Bpk. Subandi Josoprawiro', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3100, 'ANDIKA', '', '', '', '', '', '', 'PT KARUNIA JAYA GLOBAL', '', '081211616263', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3101, '', '', '', '', '', '', '', 'PT. Inakko (Harianto Muniran)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3102, 'TOKO KOLUMBIA', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3103, '', '', '', '', '', '', '', 'PT. Gavinco Tri Energi', '', '085959705549 / 081806991816', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3104, 'MUKSIN', '', '', '', '', '', '', '', '', '081210822582', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3105, '', '', '', '', '', '', '', 'PT. EGE', '', '081322915577', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3106, 'Bpk. Aryo', '', '', '', '', '', '', '', '', '087775543321', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3107, '', '', '', '', '', '', '', 'PT. MAHKOTA', '', '085282062882', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3108, 'Bpk. Adit', '', '', '', '', '', '', 'toko kaneza / tower', '', '082139399932', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3109, 'Bpk. Putra', '', '', '', '', '', '', '', '', '081235000889', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3110, 'SAM', '', '', '', '', '', '', '', '', '02122684309', '', 'LT 1 BLOK A1 NO 11', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3111, '', '', '', '', '', '', '', 'PT. DSS', '', '085614341333', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3112, 'Bpk. Fani', '', '', '', '', '', '', '', '', '082258415817', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3113, '', '', '', '', '', '', '', 'PT.TMU', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3114, 'Bpk. Basir', '', '', '', '', '', '', '', '', '085770491500', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3115, 'Pt desra banyu enjinering', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3116, 'Bpk Sasmito Hadi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3117, '', '', '', '', '', '', '', 'MANDALA JAYA (ps)', '', '08127123190', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3118, '', '', '', '', '', '', '', 'Toko Sita Safety', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3119, 'Bpk. Arfan', '', '', '', '', '', '', '', '', '0812283000647', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3120, 'ibu sunan', '', '', '', '', '', '', '', '', '08121328715', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3121, 'Bpk. Riski', '', '', '', '', '', '', '', '', '081310391029', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3122, '', '', '', '', '', '', '', 'PT. WIDYAPERKASA JAYA', '', '081808018228', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3123, 'bpk heri', '', '', '', '', '', '', '', '', '082177962793', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3124, 'prima kare', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3125, '', '', '', '', '', '', '', 'PT. Dwida Jaya Tama', '', '0895605296310', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3126, 'Bpk.Yanto Tekun', '', '', '', '', '', '', '', '', '081806367574', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3127, 'Septy Endah Rahayu (ps)', '', '', '', '', '', '', 'Badan PPSDM Kesehatan', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3128, 'cas', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3129, 'Bpk. Dodi', '', '', '', '', '', '', '', '', '08158979856', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3130, '', '', '', '', '', '', '', 'PT. Roket Jaya Abadi (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3131, 'PT SUKSES MAJU SEJAHTERA', '', '', '', '', '', '', 'LT 1 LTC', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3132, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3133, '', '', '', '', '', '', '', 'PT. Datong Lightway International Technology', '', '081370724225', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3134, '', '', '', '', '', '', '', 'PT.PARAGON PRATAMA TEKNOLOGI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3135, '', '', '', '', '', '', '', 'Aneka Topi', '', '', '', 'Senen', 'customer acung', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3136, 'Ibu. Ira (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3137, 'Ibu Anita', '', '', '', '', '', '', '(Geo Service)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3138, 'Bpk. Tono', '', '', '', '', '', '', '', '', '081317713765', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3139, '', '', '', '', '', '', '', 'Pt SMU Transliner', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3140, 'aju', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3141, 'Bpk. Fajar', '', '', '', '', '', '', '', '', '082227877601', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3142, 'HALIM-JAKARTA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3143, 'Mei Mei', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3144, 'bp iwan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3145, '', '', '', '', '', '', '', 'PT MATRIKON JAYA MANDIRI', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3146, 'holden', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3147, 'Bpk. Ikbal', '', '', '', '', '', '', '', '', '082113202230', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3148, 'PT ASIA TRACTORS', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3149, 'Bpk. Haryanto', '', '', '', '', '', '', '', '', '085726230036', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3150, 'ko ahong', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3151, 'Bpk. Dendi', '', '', '', '', '', '', '', '', '081314621819', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3152, 'Bpk. Reza', '', '', '', '', '', '', 'PT. FIFAN JAYA MAKMUR', '', '082128792377', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3153, 'ANNAS QAHHAR', '', '', '', '', '', '', '', '', '081212767792', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3154, 'PAK BAMBANG', '', '', '', '', '', '', 'TOKO MIRA SAFETY', '', '082331550647', '', 'DESA KATUR RT 024 RW 006\r\nKEC. GAYAM, BOJONEGORO\r\nJAWA TIMUR', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3155, 'PT TRI JAYA MANDIRI SENTOSA', '', '', '', '', '', '', '', '', '', '', 'gf 1 c30 no 9', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3156, 'Bpk. Berri', '', '', '', '', '', '', '', '', '081219585829', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3157, '', '', '', '', '', '', '', 'PT INDONESIA JIAZHOUDE AGRICULTURE AND FID', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3158, 'Bpk. Bayu Oktama', '', '', '', '', '', '', '', '', '081217107576', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3159, 'RENDI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3160, 'KUBERA SAFETY', '', '', '', '', '', '', 'LT GF 2 C16 NO 2', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3161, 'Bpk. Eko', '', '', '', '', '', '', 'PT. SINAR BINTANG MULIA', '', '0818672445', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3162, 'Mr Rury Widjaja', '', '', '', '', '', '', 'Buzi Hydrocarbons PTE LTD CO PT.ENERGI MEGA PERSADA', '', '', '', 'Bakrie Tower 27th Floor Rasuna Epicentrum Kuningan\r\nJL.HR Rasuna Said \r\nJakarta\r\nIndonesia 12910', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3163, '', '', '', '', '', '', '', 'PT.LPI', '', '22682244', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3164, 'ADRI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3165, '', '', '', '', '', '', '', 'PT. PLN UP3 BAUBAU', '', '082193222322', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3166, '', '', '', '', '', '', '', 'PT. KINARYA KOMPEGRITI REKANUSA (ps)', '', '02122975030', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3167, 'udin', '', '', '', '', '', '', '', '', '081213858842', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3168, 'TUJUH TUNAS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3169, 'Abun Sinoma', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3170, '', '', '', '', '', '', '', 'Team Anugerah', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3171, 'Bpk. Mario', '', '', '', '', '', '', 'PT. KMM', '', '0817550816', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3172, 'RONAL', '', '', '', '', '', '', 'TOKO GUNUNG MAS', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3173, '', '', '', '', '', '', '', 'Toko CCB', '', '02162203683', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3174, 'AZIS', '', '', '', '', '', '', 'MAJU ABADI', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3175, 'ibu ine', '', '', '', '', '', '', '', '', '', '', 'JL. Mahakeret Barat Ling 4 No. 28 ( Lorong Lembang ) depan Gereja GMIM Karmel Mahakeret Barat , Kota Manao', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3176, 'aska', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3177, 'Bpk Susilo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3178, '', '', '', '', '', '', '', 'PT Nichias Sunijaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3179, 'HANDOKO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3180, 'Bpk. Agung', '', '', '', '', '', '', '', '', '085102203609', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3181, 'Bpk. Roby (ps)', '', '', '', '', '', '', '', '', '085279398039', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3182, '', '', '', '', '', '', '', 'PT. TAKARA', '', '082161883931', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3183, '', '', '', '', '', '', '', 'PT. Sentral Conveyor Belting', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3184, '', '', '', '', '', '', '', 'CV. IKHTIAR TRANS JAYA (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3185, 'erlin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3186, '', '', '', '', '', '', '', 'PT. TB INA', '', '081218882558', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3187, 'Bpk. Ferdy', '', '', '', '', '', '', '', '', '087786615676', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3188, 'NAJIB', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3189, 'Ibu Meisya', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3190, 'Bpk. Mustofa', '', '', '', '', '', '', 'PT. Anggun Jaya Teknik', '', '081366923151', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3191, '', '', '', '', '', '', '', 'PT. ME TECHNICAL BACEHL', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3192, 'mulia teknik mandiri lt 2', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3193, 'BAPAK AZLAN', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3194, 'ineke', '', '', '', '', '', '', 'persada bordir', '', '081806104241', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3195, 'Bpk ibnu', '', '', '', '', '', '', '', '', '081245490644', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3196, 'MGE', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3197, 'DAYAT', '', '', '', '', '', '', 'TOKO RK PROMOTION', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3198, '', '', '', '', '', '', '', 'Pt Hobashita Mega Impola', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3199, 'pt beta gas', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3200, 'ALI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3201, 'Bpk. Subiyanto (ps)', '', '', '', '', '', '', '', '', '082232256708', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3202, 'alfat sejahtra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3203, 'Bpk. Amir', '', '', '', '', '', '', '', '', '081389220246', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3204, '', '', '', '', '', '', '', 'PT TOSON PERKASA JAYA', '', '', '', 'JL.Bandengan Utara No.32 B Rt.008 Rw.001 \r\nPekojan Tambora\r\nKota ADM Jakarta Barat \r\nDKI Jakarta 11240', '', 5, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3205, 'UMT', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3206, '', '', '', '', '', '', '', 'PT. Intermitra Trasindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3207, 'HIDAYAT', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3208, '', '', '', '', '', '', '', 'PT. BKI', '', '082112789403', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3209, 'Pt vema', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3210, 'Ibu. Santi', '', '', '', '', '', '', 'PT. PETEKA KARYA TIRTA', '', '085216424666', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3211, '', '', '', '', '', '', '', 'PT. SAMBAS ALAM LESTARI', '', '081383750750', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3212, 'Wibowo', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3213, 'Ibu. Kiki', '', '', '', '', '', '', '', '', '0818205290', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3214, 'PT. PANCA DUTA PRAKARSA', '', '', '', '', '', '', '', '', '087878939221', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3215, 'VENI', '', '', '', '', '', '', 'SUMBER JAYA87', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3216, 'CMS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3217, '', '', '', '', '', '', '', 'PT.Oilfield Services & Supplies Indonesia', '', '021-82415094', '', 'Jl.Cikunir Raya No.8 Rt.003 Rw.011 JatiMekar,Jatiasih \r\nBekasi', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3218, '', '', '', '', '', '', '', 'CITRA SUKSES MANDIRI', '', '', '', 'LT 1 B15 NO 7-8\r\n', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3219, 'Bpk. Dennis (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3220, 'SUPRIANTO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3221, 'DYNASTI', '', '', '', '', '', '', 'PEKAN BARU', '', '082383111467', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3222, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3223, '', '', '', '', '', '', '', 'PT. AJM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3224, '', '', '', '', '', '', '', 'Toko Karunia Safety', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3225, 'Bpk. SUHA', '', '', '', '', '', '', '', '', '081285603385', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3226, '', '', '', '', '', '', '', 'Pt Tripam Mandiri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3227, '', '', '', '', '', '', '', 'PT.BINTANG TERANG KEMASINDO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3228, 'Bpk. Alfonsus Irjanto T', '', '', '', '', '', '', 'PT. Moveyor Indotech Mandiri', '', '08119981715', '', 'LTC GLODOK \r\nLt.2 Blok A2 No.6', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3229, 'PERSONAL', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3230, 'bp ardi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3231, 'Bpk. Ahmad', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3232, 'Bpk. Untung', '', '', '', '', '', '', '', '', '087770067303', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3233, '', '', '', '', '', '', '', 'PLATINUM', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3234, '', '', '', '', '', '', '', 'PD. LANCAR USAHA TEHNIK', '', '02162310529', '', 'LTC GLODOK UG Blok A6 No. 3-5 ', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3235, 'FAIZAH', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3236, 'Bpk. Ikhsan', '', '', '', '', '', '', '', '', '081271063188', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3237, 'ARIFIN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3238, '', '', '', '', '', '', '', 'Pt.CGIC', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3239, '', '', '', '', '', '', '', 'PT. SKY PARKING UTAMA (ps)', '', '081383352962', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3240, '', '', '', '', '', '', '', 'PT. MITRAMULTI HIJAU ABADI', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3241, '', '', '', '', '', '', '', 'PT. KASHIWABARA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3242, 'Bpk. Rian (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3243, '', '', '', '', '', '', '', 'PT SIMOLUS HARMONI ENERGI', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3244, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3245, 'PAK HANIF', '', '', '', '', '', '', 'PT CITRA MANDIRI', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3246, 'Agus Kahaya', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3247, 'Bpk. Ade Adrian (ps)', '', '', '', '', '', '', '', '', '082136794213', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3248, '', '', '', '', '', '', '', 'KARYA MAKMUR ABADI', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3249, 'Bpk. Dwi', '', '', '', '', '', '', '', '', '082122365723', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3250, 'pt.sarana katiga nusantara', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3251, '', '', '', '', '', '', '', 'Kaiser Chen', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3252, 'Bpk. Irfan', '', '', '', '', '', '', '', '', '085312975581', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3253, 'm.alhabsi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3254, 'Ibu Titi (ps)', '', '', '', '', '', '', '', '', '082312022012', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3255, '', '', '', '', '', '', '', 'RGM', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3256, '', '', '', '', '', '', '', 'Bintang Maju Sejahtera', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3257, '', '', '', '', '', '', '', 'PT. Batu Karya Indonesia', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3258, 'JAYA', '', '', '', '', '', '', 'JAYA MANDIRI', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3259, 'Bpk. Herman', '', '', '', '', '', '', '', '', '085711094032', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3260, 'Bpk Ronald', '', '', '', '', '', '', 'papua', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3261, '', '', '', '', '', '', '', 'usaha jaya', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3262, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3263, 'Bpk. Ucup', '', '', '', '', '', '', '', '', '081310781234', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3264, 'Bpk. Ujang', '', '', '', '', '', '', '', '', '081212060304', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3265, 'PT SHUI ENGINERING PERKASA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3266, 'yellow pro', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3267, 'PAK ACEP', '', '', '', '', '', '', 'PD RIVA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3268, '', '', '', '', '', '', '', 'HWA HWA JAYA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3269, 'Bpk. Yogi Fatkhurohmar', '', '', '', '', '', '', '', '', '089696617697', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3270, 'Bpk. Kholil', '', '', '', '', '', '', 'PT. LANA GLOBAL INDOTAMA', '', '0219690637 / 08129660637', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3271, 'Bpk. Rian', '', '', '', '', '', '', '', '', '085798905034', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3272, 'Ibu Merlin (ps)', '', '', '', '', '', '', '', '', '087776767126', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3273, '', '', '', '', '', '', '', 'TOKO PONO BAYANG', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3274, '', '', '', '', '', '', '', 'Toko Delta Teknik Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3275, '', '', '', '', '', '', '', 'bapak ugi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3276, 'Bpk. Fendri', '', '', '', '', '', '', 'spu', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3277, 'Bpk. Calvin J', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3278, 'bpk rohmat', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3279, 'Personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3280, '', '', '', '', '', '', '', 'SAM SAFETY', '', '', '', 'LT 1 Blok A1 No.11', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3281, '', '', '', '', '', '', '', 'Raja Promo', '', '', '', 'Bogor', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3282, 'MORTEN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3283, 'Bpk Heri', '', '', '', '', '', '', 'berka mitra', '', '081388558668', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3284, 'Bpk. Ano', '', '', '', '', '', '', '', '', '085691870690', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3285, '', '', '', '', '', '', '', 'PROFESIONAL TEKNIK', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3286, 'YAZID LUBIS', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3287, '', '', '', '', '', '', '', 'PT TEGUH USAHA BERSAMA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3288, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3289, '', '', '', '', '', '', '', 'Toko Diamond Jack', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3290, 'SURURY FARID', '', '', '', '', '', '', 'CV INDO PRATAMA - MALANG', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3291, 'Budi Petrova', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3292, 'Bpk. Andri', '', '', '', '', '', '', '', '', '081294451906', '', '', '', 1, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3293, 'ROMPI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3294, 'ANEKA TOPI', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3295, 'TEHNIK JAYA MANDIRI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3296, 'NITA-USGUARD', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3297, 'Handoko', '', '', '', '', '', '', 'PDP', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:16', '2020-07-29 12:15:16', 1, 1),
(3298, 'Bpk. Agus Prasetyo (ps)', '', '', '', '', '', '', '', '', '082260974970', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3299, 'PT. SANLAND', '', '', '', '', '', '', '', '', '085313056326', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3300, 'Bpk. Eko', '', '', '', '', '', '', '', '', '0819396991', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3301, 'ERWIN IMANSYAH', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3302, 'APID', '', '', '', '', '', '', 'PT LUNTO', '', '082125148989', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3303, 'TOKO BUTAN', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3304, '', '', '', '', '', '', '', 'PT ISTAMA MITRA RIAU', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3305, 'BAPAK PRIO', '', '', '', '', '', '', 'PT PANCA DUTA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3306, '', '', '', '', '', '', '', 'LESTARI SAFETY INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3307, '', '', '', '', '', '', '', 'Pt Future Star', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3308, '', '', '', '', '', '', '', 'TOKO LESTARI', '', '087877181232', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3309, 'Bpk Al Suria', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3310, 'SMP KANISIUS', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3311, 'ANDIKA-MANADO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3312, 'IDL', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3313, 'LSPMI BOGOR', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3314, '', '', '', '', '', '', '', 'SAMMUTIAR EMP', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3315, 'Bpk. Iwan', '', '', '', '', '', '', '', '', '082232314422', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3316, 'Bpk. Irfan', '', '', '', '', '', '', '', '', '083898761200', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3317, 'Bpk Fabian', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3318, 'AMSARI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3319, 'Bpk. Dadan', '', '', '', '', '', '', '', '', '081319082189', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3320, 'Mr. Ramin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3321, 'HAMDAN', '', '', '', '', '', '', 'TOKO HAMDAN KUMBA', '', '081288147075', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3322, '', '', '', '', '', '', '', 'PT. Laris Sejahtera', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3323, 'Bpk. Robi', '', '', '', '', '', '', '', '', '087782228332', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3324, '', '', '', '', '', '', '', 'PT. Pratama Citra Mandiri', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3325, '', '', '', '', '', '', '', 'PT. SATOIL', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3326, 'PT PARAHITA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3327, 'REFLI', '', '', '', '', '', '', 'SUMBER JAYA 87 (1)', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3328, 'PERSONAL', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3329, 'FAUZAN', '', '', '', '', '', '', 'PT WAHANA INDO PERKASA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3330, '', '', '', '', '', '', '', 'PT Sinergi Prima Mandiri', '', '', '', 'Tamansari Hive Office 7th Floor \r\nJL.DI Panjaitan Kav 2 \r\nJakarta Timur', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3331, '', '', '', '', '', '', '', 'PT. PIPING SYSTEM INDONESIA', '', '081311122154', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3332, '', '', '', '', '', '', '', 'BERSAMA SAFETY', '', '081293108967', '', 'LTC GLODOK Lt. UG Blok. A2 No. 19', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3333, '', '', '', '', '', '', '', 'PT. Anugrah Abadi Baru', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3334, 'Bpk. Paulus', '', '', '', '', '', '', '', '', '081806721788', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3335, 'Bapak Syarif', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3336, '', '', '', '', '', '', '', 'PT. HWASUNG TREMOINDO', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3337, 'Bpk. M. Saroji', '', '', '', '', '', '', '', '', '082392484258', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3338, 'Bpk. Santo', '', '', '', '', '', '', 'PT. DSP', '', '082230000100', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3339, '', '', '', '', '', '', '', 'TOKO SUPPLIER GLODOK', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3340, 'Bpk. Erwin', '', '', '', '', '', '', '', '', '081285842285', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3341, 'IBU FITRIAH', '', '', '', '', '', '', 'JAYA MAKMUR', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3342, '', '', '', '', '', '', '', 'TOKO ARAFAH PS SENEN', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3343, '', '', '', '', '', '', '', 'ANUGRAH MAKMUR SEJATRA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3344, '', '', '', '', '', '', '', 'PT. Matahari wasiso tama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3345, 'Bpk. Harno', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3346, 'Bpk. Siswanto', '', '', '', '', '', '', '', '', '081251538997', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3347, 'Bpk. Helmi', '', '', '', '', '', '', '', '', '081818888010', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3348, 'Bpk Bahrun', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3349, 'ARIN MANDIRI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3350, 'Bpk. Jimmi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3351, '', '', '', '', '', '', '', 'ISKABA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3352, '', '', '', '', '', '', '', 'PT. Putra Alam (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3353, '', '', '', '', '', '', '', 'PT. Supreme Belting Perkasa', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3354, 'TIWI', '', '', '', '', '', '', 'GREEN HILL RESORT', '', '087779756600', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3355, 'rosid', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3356, 'SILVI', '', '', '', '', '', '', 'TANKS STATION INDONESIA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3357, 'Bpk. Adhika', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3358, 'MADCO TEHNIK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3359, '', '', '', '', '', '', '', 'TAISEI PULAU INTAN (ps)', '', '081294433913', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3360, 'Bpk. Reza', '', '', '', '', '', '', '', '', '081294510567', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3361, '', '', '', '', '', '', '', 'Engineering', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3362, '', '', '', '', '', '', '', 'TOKO AZURA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3363, 'M. YUSUF', '', '', '', '', '', '', '', '', '08170753020', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3364, '', '', '', '', '', '', '', 'CMM', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3365, '', '', '', '', '', '', '', 'PT WISH', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3366, 'PT ZTE CORPORATION', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3367, '', '', '', '', '', '', '', 'TOKO RDN', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3368, '', '', '', '', '', '', '', 'PT. MAHAKARYA', '', '085655731767', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3369, 'bpk.randal', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3370, '', '', '', '', '', '', '', 'Adi Persada Gedung', '', '087883553488', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3371, 'Bpk. Saipul', '', '', '', '', '', '', '', '', '085330169023', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3372, '', '', '', '', '', '', '', 'PT. BATAVIA', '', '081316880512', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3373, 'REKTOR IPB', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3374, 'Bpk. Hanafi', '', '', '', '', '', '', '', '', '08111561927', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3375, '', '', '', '', '', '', '', 'Toko Devalindo Jaya', '', '082247491911 / 02162201348', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3376, 'Bpk. Naryo', '', '', '', '', '', '', '', '', '081310102124', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3377, 'Bpk. Reza', '', '', '', '', '', '', '', '', '087785373002', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3378, 'Bpk. Solihin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3379, 'Ci Yeni ( jahit )', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3380, 'KOSAMA', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3381, 'nano', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3382, 'Bpk Alvin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3383, 'bp ahmad', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3384, 'Bpk Nur', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3385, '', '', '', '', '', '', '', 'PT Citra Karya Bersama', '', '', '', 'Gedung STC Senayan Lt 4 R. 31-34\r\nJl Asia Afrika Pintu IX Senayan\r\nJakarta', 'Group EMP BEP', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3386, 'Bpk. M. Alim Mahmudi', '', '', '', '', '', '', '', '', '081513527558', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3387, 'Bp.Gatot', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3388, 'Bpk. Eka', '', '', '', '', '', '', 'PT.TBP', '', '082296351870', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3389, '', '', '', '', '', '', '', 'PT. Surya langgeng indonesia', '', '087780938428', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3390, '', '', '', '', '', '', '', 'PT. BOTON', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3391, 'PT total fire', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3392, 'Bpk Piter', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3393, 'Bpk. Eko', '', '', '', '', '', '', '', '', '08126914114', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3394, '', '', '', '', '', '', '', 'Pt Sapta Karsa Inti', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3395, 'Bpk Junaidi', '', '', '', '', '', '', '', '', '082310379000', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3396, 'PAINT BALL CANGGU', '', '', '', '', '', '', 'PT. INNOVATIVE SPIRIT SPORT', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3397, '', '', '', '', '', '', '', 'TOKO CELSIE', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3398, 'PT TBU', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3399, '', '', '', '', '', '', '', 'PT. Mulia Gunung Mas', '', '081314442968', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3400, 'Bpk. Pratomo (ps)', '', '', '', '', '', '', 'KM. INDAH 88', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3401, 'PT TIRTATAMA ELPINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3402, 'PT CEMPAKA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3403, 'Bpk Edi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3404, 'Ibu. Risma (ps)', '', '', '', '', '', '', '', '', '085608643176', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3405, 'ZHENGCAI MATERIAL-JKT', '', '', '', '', '', '', '', '', '', '', 'LT UG ', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3406, 'Ibu. Venjen', '', '', '', '', '', '', '', '', '0895358030191', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3407, '', '', '', '', '', '', '', 'Toko utama berdikari', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3408, '', '', '', '', '', '', '', 'Pt Catur MT', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3409, '', '', '', '', '', '', '', 'TOKO 57 SENEN', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3410, 'Bp Rama/Anton', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3411, 'Bpk. Gumilang', '', '', '', '', '', '', '', '', '081258995000', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3412, '', '', '', '', '', '', '', 'Karya Abadi', '', '', '', 'GF2 C28 No.6', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3413, '', '', '', '', '', '', '', 'PT. HUKAWA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3414, 'Pak darto', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3415, 'Bpk. Yudi', '', '', '', '', '', '', '', '', '081293644285', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3416, 'Ibu tania', '', '', '', '', '', '', '', '', '0818770011', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3417, 'PT PELANGI PRIMA UTAMA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3418, 'Bpk. Muhammad sholikhin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3419, '', '', '', '', '', '', '', 'Pt Habco Primatama', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3420, 'Bpk. Sumar', '', '', '', '', '', '', '', '', '08112447311', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3421, 'PT EGATEK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3422, 'BMP', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3423, 'nurdin', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3424, '', '', '', '', '', '', '', 'BALITNAK', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3425, '', '', '', '', '', '', '', 'PT. MONOTARO INDONESIA', '', '0218971121 / 897170', '', '', 'DARI HARGA TOKO NAIK 10.000 \r\npembelian d atas 500 WAJIB PAKAI MATERAI', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3426, 'PT VEMA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3427, 'PT INDOTAMA MAJU MANDIRI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3428, '', '', '', '', '', '', '', 'BESTINDO', '', '081219975001', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3429, 'Bpk. Angga', '', '', '', '', '', '', '', '', '08161382447', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3430, 'beri manoro', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3431, '', '', '', '', '', '', '', 'Pt Lax acon Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3432, '', '', '', '', '', '', '', 'CV. AGUSBABA SUVERY TEKNIK', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3433, '', '', '', '', '', '', '', 'YANMAR (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1);
INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(3434, '', '', '', '', '', '', '', 'PT. MULTI GUNA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3435, '', '', '', '', '', '', '', 'Pt TriDaya', '', '', '', 'Lantai 2 Blok B17 No.8', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3436, '', '', '', '', '', '', '', 'PT STAR LASER', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3437, '', '', '', '', '', '', '', 'Pt Mahkota Elang Internusa', '', '', '', 'Lantai 2 C23 No.6-7', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3438, 'Bpk. Ryndo (ps)', '', '', '', '', '', '', '', '', '082252915211', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3439, 'Bpk. Eko', '', '', '', '', '', '', 'PT. JAYA KENCANA', '', '085640456035', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3440, 'Bpk. Bambang Setiyo Adji', '', '', '', '', '', '', '', '', '08118591010', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3441, 'bu ulfa', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3442, 'PT BIONXI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3443, 'Bpk. Yusev (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3444, 'Bpk. Nicko (PS)', '', '', '', '', '', '', 'INTERAKTIF MEDIA KOMPUTER', '', '081381055821', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3445, 'Bpk. Eko Wijoyo', '', '', '', '', '', '', 'PT. INDOPRIMA PERKASA', '', '08129396991', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3446, 'bpk toni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3447, 'PT KARTIKA JAYA MAKMUR', '', '', '', '', '', '', '', '', '021-86606300', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3448, 'Bpk. Rama (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3449, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3450, '', '', '', '', '', '', '', 'BINTANG HARAPAN', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3451, 'dian', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3452, 'Bpk. Suparno', '', '', '', '', '', '', '', '', '081284042385', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3453, '', '', '', '', '', '', '', 'UD GUNUNG MAS (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3454, 'cv sinar karya mandiri Lt UG blok B10 no3', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3455, '', '', '', '', '', '', '', 'PT. PLAZA INDO STEEL', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3456, '', '', '', '', '', '', '', 'PT. MULTI KARYA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3457, 'Bp taufik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3458, 'bpk tofik', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3459, 'MUTIA', '', '', '', '', '', '', 'UNDP', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3460, 'Bpk. Andre', '', '', '', '', '', '', '', '', '081911211653', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3461, '', '', '', '', '', '', '', 'ARTA ANUGERAH', '', '6267248', '', 'HWI LT DASAR BLOK D96', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3462, 'Bpk. Tedi', '', '', '', '', '', '', '', '', '08122348222', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3463, 'Bpk. Julianto', '', '', '', '', '', '', '', '', '081320913679', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3464, 'PT C.M.W', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3465, 'bp bushram mubarak parepare', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3466, 'bapak farizkal', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3467, 'Bp/Ibu Sukma', '', '', '', '', '', '', 'PT Global Inti Kesemakmuran Perkasa', '', '', '', 'Beltway Office Park Tower B 5th Floor \r\nJl Letjen TB Simatupang Ragunan\r\nPasar Minggu \r\nPh.021 2985 7337 ', 'Group BEP', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3468, 'Ibu. Selly', '', '', '', '', '', '', '', '', '081315424949', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3469, 'PT SANTOTEX', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3470, 'CA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3471, '', '', '', '', '', '', '', 'PABRIK ES', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3472, 'Bpk. Agung Supratanto', '', '', '', '', '', '', 'PT. REKAYASA INDUSTRI', '', '081318509966', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3473, 'Bpk Rici', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3474, 'IVAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3475, 'SARANA TEHNIK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3476, 'ADI RUSMAN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3477, 'JAROD', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3478, '', '', '', '', '', '', '', 'Pt Geoprima Solusi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3479, '', '', '', '', '', '', '', 'China Petroleum Pipeline Engineering co.ltd', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3480, 'Bpk. Erwin', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3481, 'DJM ( rahmat )', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3482, '', '', '', '', '', '', '', 'Sentral restoran', '', '085776769676', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3483, 'Bpk. Muhammad Qausar (ps)', '', '', '', '', '', '', '', '', '081250503048', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3484, 'Bpk Hari', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3485, 'bapak agung', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3486, 'pt anugrah tangkas', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3487, '', '', '', '', '', '', '', 'PERHIMPUNAN JIN JIANG INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3488, 'CV mjm', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3489, 'Gusmavin (PS)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3490, 'HARAPAN JAYA TEHNIK', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3491, 'Ibu Angela', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3492, 'Bpk. Andi', '', '', '', '', '', '', 'PT. RECONSULT', '', '081296065643', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3493, 'LINK SAFETY', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3494, 'ADE', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3495, 'KO SANTO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3496, 'Bpk. Sumarno', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3497, 'Bpk. Dennis Liuwinta', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3498, 'Bpk. Steven Diki', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3499, 'Bpk Angga', '', '', '', '', '', '', '', '', '', '', 'LT GF 1 C30 NO 9', '', 2, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3500, 'Ibu marissa', '', '', '', '', '', '', 'PT. Brenntag', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:17', '2020-07-29 12:15:17', 1, 1),
(3501, 'Ibu. Khara Astiani S (ps)', '', '', '', '', '', '', '', '', '082110263049', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3502, 'Ibu. Vina', '', '', '', '', '', '', '', '', '081222400117', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3503, 'Bpk. Fajar', '', '', '', '', '', '', '', '', '087888887217', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3504, 'Bpk. Juan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3505, 'Bpk. Novi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3506, '', '', '', '', '', '', '', 'PT. Andalan tiga', '', '087786998638', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3507, 'ibu diana', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3508, 'panri setiawan', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3509, 'Bpk engdra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3510, 'Ibu. Vina Christina', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3511, '', '', '', '', '', '', '', 'PT. Aneka Minera Indonesia', '', '085692252098', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3512, 'Ibu. Ika', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3513, 'Bpk. Fajar budiyanto (ps)', '', '', '', '', '', '', '', '', '087888887217', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3514, '', '', '', '', '', '', '', 'PT NAVYA RETAIL INDONESIA', '', '', '', '', 'CUST NON ERLIN', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3515, 'ongki', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3516, 'Bpk Ruli', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3517, 'PT PACIFIC SP', '', '', '', '', '', '', 'PURWAKARTA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3518, 'Bpk. M.Rifki', '', '', '', '', '', '', '', '', '085717372269', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3519, 'andika', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3520, '', '', '', '', '', '', '', 'TOKO MATAHARI', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3521, 'Theo Chandra Hutomo', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3522, 'Dicha', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3523, 'Bpk. Yusuf ibrani', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3524, 'Bpk Faldo', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3525, '', '', '', '', '', '', '', 'PT. Global Niaga Elektrik', '', '66603550', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3526, 'Ibu. Vitina', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3527, 'YANDRI SUSANTO', '', '', '', '', '', '', 'PT APL LOGISTICS RDTX TOWER 2ND FLOOR JL PROF RD SATRIO KAV E IV NO.6 JAKSEL', '', '08111711658', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3528, 'Bpk. Noval', '', '', '', '', '', '', '', '', '082313833199', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3529, 'Engdra', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3530, 'YANTO', '', '', '', '', '', '', 'NUSA CIPTA', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3531, 'PT INDO FUDONG KONSTRUKSI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3532, 'Bpk. Suherman', '', '', '', '', '', '', '', '', '0857-1109-4032', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3533, '', '', '', '', '', '', '', 'PT. Erka Interindo', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3534, 'CV GUNINDO', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3535, 'Bp Didik Petro', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3536, 'deni', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3537, 'bapak maryono', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3538, 'Ibu. Amalia', '', '', '', '', '', '', 'CV. Avi Gemilang', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3539, 'selly', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3540, 'Bpk. Wanto (ps)', '', '', '', '', '', '', '', '', '081218422516', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3541, '', '', '', '', '', '', '', 'PT. SINAC TRANS', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3542, 'PT hilti nusantara', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3543, 'Bpk. Ahmad', '', '', '', '', '', '', '', '', '08119290046', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3544, '', '', '', '', '', '', '', 'PT. SMC', '', '081290323266', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3545, '', '', '', '', '', '', '', 'Pt Makmur Kontruksi Indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3546, 'Bpk Deni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3547, '', '', '', '', '', '', '', 'TOKO HERLY', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3548, 'KO ALUNG', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3549, 'DARWATI MARINA', '', '', '', '', '', '', 'SERPONG', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3550, 'Veronica', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3551, '', '', '', '', '', '', '', 'Toko surya gemilang', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3552, '', '', '', '', '', '', '', 'Pt Teguharta Lestari', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3553, '', '', '', '', '', '', '', 'PT. Jatropha Solution', '', '081296915335', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3554, 'Bpk. Raynaldi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3555, 'NISA', '', '', '', '', '', '', 'IMUT SHOP', '', '082315598335', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3556, 'PT BKI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3557, 'Ibu Lina Karlina (ps)', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3558, 'FOKUS SAFETY', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3559, 'Bpk Yasar', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3560, 'Bpk. Kusnohati', '', '', '', '', '', '', '', '', '081316244564', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3561, 'Bpk. Budi', '', '', '', '', '', '', '', '', '082226613022', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3562, 'Bpk. Faridh (ps)', '', '', '', '', '', '', '', '', '082244449006', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3563, 'Mr Fu pui wai', '', '', '', '', '', '', '', '', '', '', 'Hongkong', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3564, 'Mr Ming Xin', '', '', '', '', '', '', '', '', '', '', 'Plaza uob', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3565, 'SIBAT', '', '', '', '', '', '', 'PESONA TAYLOR', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3566, 'MAJU MANDIRI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3567, 'TOKO MAJU MANDIRI', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3568, 'BIONDI TEKNIK', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3569, '', '', '', '', '', '', '', 'PT. LASMA SURYA NUGRAHA (PS)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3570, 'bpk sugeng', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3571, 'PT CSI', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3572, 'ARUNG JAYA PERKASA', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3573, 'Bpk. Izul', '', '', '', '', '', '', '', '', '081383916810', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3574, 'Mr Cheng Lei', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3575, 'optima', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3576, 'personal', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3577, '', '', '', '', '', '', '', 'BERKAH ISHATI TEHNIK', '', '', '', 'LT 2 BLOK A16 NO 10', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3578, 'Ibu Niza', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3579, 'Bpk Adit', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3580, 'Bp.Roni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3581, '', '', '', '', '', '', '', 'PT HASIL MITRA UNGGUL', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3582, 'Bpk Alex', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3583, 'Bok Tonny', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3584, 'Bpk. Chandra Nababan', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3585, 'HSL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3586, 'Bpk. Bazki Chau', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3587, 'Bpk. Supriyono', '', '', '', '', '', '', 'PT. Hasta Prajatama', '', '085236959989', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3588, '', '', '', '', '', '', '', 'PT. BKA', '', '08176568470', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3589, '', '', '', '', '', '', '', 'BUT HEBEI RESEARCH', '', '081513912355', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3590, 'Chandra', '', '', '', '', '', '', '', '', '', '', 'Jagakarsa', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3591, 'ci poni', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3592, 'Bpk Syarif', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3593, '', '', '', '', '', '', '', 'PT. Mahkota Paduyasa', '', '02178883939', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3594, 'MANDIRI GROUP', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3595, 'pt.usuno', '', '', '', '', '', '', '', '', '0811123959', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3596, 'bpk Uki', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3597, 'Bpk. Sularso', '', '', '', '', '', '', '', '', '082298301002', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3598, 'ARIF-JKT', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3599, 'Mr Yorke', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3600, '', '', '', '', '', '', '', 'Vici Jaya Sukses', '', '081281971826', '', 'Kenari Baru Blok.C1 Lt.3', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3601, 'Bpk. Hendy Suprayitno (ps)', '', '', '', '', '', '', '', '', '081253110778', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3602, 'FAIZAL - MAJU JAYA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3603, 'Ibu selvia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3604, '', '', '', '', '', '', '', 'PT EUROASIATIC JAYA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3605, '', '', '', '', '', '', '', 'PT WINNING LOGISTIK INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3606, 'Bpk Dondy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3607, 'PT LAMARU BANGUN PERSADA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3608, 'Lia', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3609, 'Bpk.Arif', '', '', '', '', '', '', '', '', '081222058910', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3610, 'PDAM TIRTA LANGKISAU', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3611, 'PERSONAL', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3612, 'Mr Ailun', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3613, 'AMNESTI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3614, 'HK', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3615, 'Bpk. Yofi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3616, 'Bpk. Anto', '', '', '', '', '', '', '', '', '081280028668', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3617, '', '', '', '', '', '', '', 'PT. Mega Daya', '', '08888415884', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3618, 'Mrs cei', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3619, 'TIKA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3620, 'CHINDY', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3621, 'Ibu. Ine', '', '', '', '', '', '', '', '', '08159154105', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3622, '', '', '', '', '', '', '', 'CIPTA BAJA TRIMATRA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3623, 'Bpk. Aji', '', '', '', '', '', '', '', '', '081617524507', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3624, 'bpk Ronny', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3625, 'Saiful', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3626, 'bpk Yadi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3627, 'bpk Agus Vivi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3628, '', '', '', '', '', '', '', 'Toko CV Gunung Intan', '', '081218002741', '', 'LT. UG Blok C21 No.9', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3629, 'Bpk. Edi Mardianto (ps)', '', '', '', '', '', '', '', '', '085339186565', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3630, '', '', '', '', '', '', '', 'TOKO PERMATA JAYA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3631, 'Ibu. Amalia', '', '', '', '', '', '', 'PT. PERI', '', '081808050547', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3632, 'bpk Ahmad Hasan', '', '', '', '', '', '', '', '', '08129466823', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3633, 'DENI-GLODOK JAYA', '', '', '', '', '', '', '087881135988', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3634, '', '', '', '', '', '', '', 'PT. MUTIARA GADING MULIA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3635, 'Bpk. Jemi', '', '', '', '', '', '', '', '', '081289250063', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3636, '', '', '', '', '', '', '', 'PT . BARAHANA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3637, 'Pak Kendy', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3638, 'Mr Yen HK', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3639, '', '', '', '', '', '', '', 'Fokus Konveksi', '', '', '', '', '', 0, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3640, '', '', '', '', '', '', '', 'Focus Konveksi', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3641, 'PAK IDRIS', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3642, 'Ibu. Hana', '', '', '', '', '', '', 'RUKUN', '', '082210991919', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3643, 'RATNA', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3644, 'Ibu. Pipih', '', '', '', '', '', '', '', '', '081282787057', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3645, 'IBU BETY', '', '', '', '', '', '', 'TOKO NN', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3646, 'Mr Wu', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3647, '', '', '', '', '', '', '', 'SUNLIJAYA', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3648, 'INDERA SADIKIN', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3649, '', '', '', '', '', '', '', 'PT. Cahaya Pasifik Utama', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3650, 'Bpk. Saefudin', '', '', '', '', '', '', 'PT.Reefconindo Cemerlang Inti', '', '081294420474', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3651, 'mia', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3652, 'Mr Jhony aan', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3653, 'Bpk. Abdi', '', '', '', '', '', '', '', '', '08126080378', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3654, 'Ibu. Eva', '', '', '', '', '', '', '', '', '081299239028', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3655, 'FEDEX EXPRESS', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3656, 'jimmy', '', '', '', '', '', '', '', '', '082122879902', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3657, 'ibu merry', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3658, '', '', '', '', '', '', '', 'PT.Outletz Worldwide  indonesia', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3659, '', '', '', '', '', '', '', 'PT JJLURGI ENGINEERING INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3660, 'bpk roman URC', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3661, '', '', '', '', '', '', '', 'TOKO SAMDORIA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3662, '', '', '', '', '', '', '', 'FRESH UP', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3663, '', '', '', '', '', '', '', 'PT. Quadro Indonesiaperkasa', '', '087889398015', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3664, 'Bpk. Budi', '', '', '', '', '', '', '', '', '081210466369', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3665, '', '', '', '', '', '', '', 'PT BERKAH PERKASA NUSANTARA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3666, 'Bpk Hardy', '', '', '', '', '', '', 'Hatric Medika', '', '085245748948', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3667, 'DIAN USGUARD', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3668, 'Bpk Udin', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3669, 'Bpk miyadi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3670, 'Sukses Metal Sentosa', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3671, '', '', '', '', '', '', '', 'PT. Panca Inti Nusantara Sejahtera (ps)', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3672, 'bpk Peter', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3673, 'DIDI', '', '', '', '', '', '', '', '', '082125010775', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3674, 'Mr Shencheng', '', '', '', '', '', '', 'PT DOVER CHEMICAL', '', '0811941817', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3675, 'ENGKUS', '', '', '', '', '', '', 'FAMILI BORDIR', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3676, 'BAHARI PRIMA MANUNGGAL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3677, 'Bpk. Sugeng', '', '', '', '', '', '', '', '', '081317458688', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3678, 'Bpk. Andi', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3679, '', '', '', '', '', '', '', 'PT.Intitech Solutions', '', '081296081339', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3680, 'Mr Wei', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3681, 'melina', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3682, 'Hafizh Radritiawan', '', '', '', '', '', '', 'PT Caturdaya Gema Industri (ps)', '', '0821-7873-1423', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3683, 'Ibu Dea Febrianti', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3684, 'nasir', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3685, '', '', '', '', '', '', '', 'PT. UMETOKU IND ENGINEERING', '', '02128080220', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3686, 'TERE', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3687, 'ASTRI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3688, '', '', '', '', '', '', '', 'Cv trikarya teknik enginering', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3689, 'ibu lina', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3690, 'Bpk. Agus', '', '', '', '', '', '', '', '', '081382111037', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3691, 'arman', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3692, 'PT TARUNA TANGGUH MANDIRI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3693, 'Bpk. Ridho', '', '', '', '', '', '', '', '', '081284753746', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3694, '', '', '', '', '', '', '', 'BUANA SAKTI', '', '', '', 'LT 1 BLOK B9 NO 1-2 , 7-8', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3695, '', '', '', '', '', '', '', 'PT. MTI', '', '082210449561', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3696, 'AGUNG-BEKASI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3697, 'RENAL', '', '', '', '', '', '', 'TOKO CANDRA BINTANG', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3698, '', '', '', '', '', '', '', 'PT WASKITA KARYA', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3699, 'Ibu. Evy nawangwulan', '', '', '', '', '', '', '', '', '081228409509', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3700, 'Ny Nining', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3701, '', '', '', '', '', '', '', 'Jakarta Safety Center', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3702, 'Bpk. Susanto', '', '', '', '', '', '', '', '', '081219125899', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3703, 'Bpk. Saiful', '', '', '', '', '', '', '', '', '087880379561', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3704, 'Bpk Adul', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3705, '', '', '', '', '', '', '', 'CV Teknindo Abadi', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3706, 'Bpk. Soni', '', '', '', '', '', '', '', '', '081288144218', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3707, '', '', '', '', '', '', '', 'PT. ICS MEDIKA LESTARI (PS)', '', '0811866077', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3708, 'Harry ( Sunter )', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3709, 'bpk dedi', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3710, 'PERSONAL', '', '', '', '', '', '', '', '', '', '', '', '', 6, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3711, 'Bpk. Zidan Dwi Nur (ps)', '', '', '', '', '', '', '', '', '08979710854', '', '', '', 1, 'aktif', '2020-07-29 12:15:18', '2020-07-29 12:15:18', 1, 1),
(3712, '', '', '', '', '', '', '', 'PT Pradana Persada', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3713, 'Bpk. Imam', '', '', '', '', '', '', '', '', '085283782370', '', '', '', 1, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3714, 'PT JEMBATAN MAS ENGINEERING', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3715, 'RIKO', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3716, 'Stanley', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3717, 'TRIMUKTI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3718, '', '', '', '', '', '', '', 'PERLENGKAPAN CLEMBING INDONESIA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3719, 'BERJAYA MAHANUGRAH ABADI', '', '', '', '', '', '', '', '', '', '', '', '', 1, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3720, '', '', '', '', '', '', '', 'PT.INDOMOBIL PRIMA NIAGA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3721, 'Ibu Melly', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3722, '', '', '', '', '', '', '', 'PT FIRST MEDIA NEWS', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3723, '', '', '', '', '', '', '', 'gastro', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3724, '', '', '', '', '', '', '', 'payeri', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3725, '', '', '', '', '', '', '', 'PRIMA WAHANA CARAKA', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3726, '', '', '', '', '', '', '', 'EDISON', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3727, 'PT G TEKT INDONESIA MANUFAKTURING', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3728, 'PT DWI NAGA SAKTI ABADI', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3729, 'Bp Ibrahim', '', '', '', '', '', '', '', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3730, '', '', '', '', '', '', '', 'wayan', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3731, 'sdf', '234', 'noimage.jpg', 'noimage.jpg', 'Toko', '234', 'MR', 'KOH SUN CITY', 'fgdfg@fd.d', 'ert', '234', 'dgdf', '243', 0, 'aktif', '2020-07-29 12:15:19', '2021-05-10 12:42:53', 1, 2),
(3732, '', '', '', '', '', '', '', 'renata', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3733, 'AAL', '', '', '', '', '', '', '', '', '', '', '', '', 4, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3734, '', '', '', '', '', '', '', 'danang', '', '', '', '', '', 2, 'aktif', '2020-07-29 12:15:19', '2020-07-29 12:15:19', 1, 1),
(3735, 'qwery', '234234', 'noimage.jpg', 'noimage.jpg', 'Toko', '234234', 'Tn', 'qwert', 'wqeqw@ss.x', '1231', '123134', 'sd gdfgd fdh', 'eqweqw', 1, 'AKTIF', '2021-05-10 12:08:26', '2021-05-10 12:08:26', 2, 2),
(3736, 'testa', 'etsta', 'noimage.jpg', 'noimage.jpg', 'CV', '-testa', 'MR', 'testa', 'testa@email.com', 'testa', 'testa', 'setseta', 'testa', 1, 'AKTIF', '2021-05-24 09:10:32', '2021-05-24 09:12:50', 2, 2),
(3737, 'a', 'a', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'Tn', 'a', 'a@email.com', 'a', 'a', 'a', 'a', 1, 'nonaktif', '2021-05-24 09:11:45', '2021-05-24 09:12:09', 2, 2),
(3738, 'Andryan Dedy', 'sdfsdfsd', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'MR', 'MBPD', 'calandra.alencia@gmail.com', 'dfsdfsd', '081288983824', 'Taman Palem Lestari Blok B18 no 37, Cengkareng, Jakarta Barat', 'sdfds', 1, 'AKTIF', '2021-06-09 01:15:28', '2021-06-09 01:15:28', 2, 2),
(3739, 'Joshua', '-', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'Tn', 'Isupport', 'joshua@isupport.com', '-', '-', '-', '-', 3, 'AKTIF', '2021-06-26 12:24:28', '2021-06-26 12:24:28', 5, 5);

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
-- Dumping data for table `mstr_employee`
--

INSERT INTO `mstr_employee` (`id_pk_employee`, `emp_nama`, `emp_npwp`, `emp_ktp`, `emp_hp`, `emp_alamat`, `emp_kode_pos`, `emp_foto_npwp`, `emp_foto_ktp`, `emp_foto_lain`, `emp_foto`, `emp_gaji`, `emp_startdate`, `emp_enddate`, `emp_rek`, `emp_gender`, `emp_suff`, `emp_status`, `emp_create_date`, `emp_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Joshua Natan', '029831239489', '129382931038', '089616961915', '-', '11610', 'Render32.png', 'render12.png', 'noimage.jpg', 'Pas_Foto_Joshua_Natan_Wijaya3.jpg', 10000000, '2020-07-25 00:00:00', '2020-08-01 00:00:00', '123123', 'PRIA', 'MR', 'AKTIF', '2020-07-24 20:58:23', '2020-07-24 20:58:23', 2, 2),
(2, 'wivina daicy', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'nonaktif', '2021-04-24 03:03:07', '2021-05-24 09:20:08', 2, 2),
(3, 'bc', '123', '122', '123', 'b23', '122', 'pexels-andre-furtado-3707171.jpg', 'pexels-andre-furtado-3707171.jpg', 'pexels-artem-beliaikin-8531991.jpg', 'pexels-artem-beliaikin-8531991.jpg', 123, '2021-04-28 00:00:00', '2021-06-03 00:00:00', '122', 'WANITA', 'MRS', 'AKTIF', '2021-05-24 21:13:42', '2021-05-24 21:13:42', 2, 2),
(4, 'calandra', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AKTIF', '2021-06-09 12:58:18', '2021-06-09 12:58:18', 3, 3),
(5, 'andre', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AKTIF', '2021-06-09 01:01:08', '2021-06-09 01:01:08', 3, 3),
(6, 'putri', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AKTIF', '2021-06-09 01:10:57', '2021-06-09 01:10:57', 4, 4),
(7, 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AKTIF', '2021-06-09 01:32:22', '2021-06-09 01:32:22', 2, 2),
(8, 'test user name', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AKTIF', '2021-06-09 08:58:29', '2021-06-09 08:58:29', 5, 5);

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
-- Dumping data for table `mstr_jabatan`
--

INSERT INTO `mstr_jabatan` (`id_pk_jabatan`, `jabatan_nama`, `jabatan_status`, `jabatan_create_date`, `jabatan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2021-06-09 02:10:33', 1, 4),
(2, 'admin2', 'nonaktif', '2020-06-22 07:51:13', '2020-07-06 08:59:34', 1, 1),
(3, 'admin3', 'nonaktif', '2020-06-22 07:53:15', '2020-07-06 08:59:37', 1, 1),
(4, 'admin4', 'nonaktif', '2020-06-22 08:02:21', '2020-07-06 08:59:40', 1, 1),
(5, 'user dan hak akses', 'nonaktif', '2021-05-24 10:13:08', '2021-05-24 10:13:47', 2, 2),
(6, 'user dan role', 'nonaktif', '2021-05-24 10:14:15', '2021-05-24 10:39:19', 2, 2),
(7, 'user and role', 'AKTIF', '2021-05-24 10:39:34', '2021-05-24 10:39:34', 2, 2),
(8, 'Admin Umum', 'nonaktif', '2021-06-09 01:29:15', '2021-06-09 01:29:28', 2, 2),
(9, 'Penjualan', 'AKTIF', '2021-06-09 02:11:09', '2021-06-09 02:11:09', 4, 4);

--
-- Triggers `mstr_jabatan`
--
DELIMITER $$
CREATE TRIGGER `trg_insert_new_jabatan_to_all_hak_akses` AFTER INSERT ON `mstr_jabatan` FOR EACH ROW begin
            /* insert new jabatan to all hak akses*/
            set @id_jabatan = new.id_pk_jabatan;
            insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
            select @id_jabatan,id_pk_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_menu;
        end
$$
DELIMITER ;

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
-- Dumping data for table `mstr_marketplace`
--

INSERT INTO `mstr_marketplace` (`id_pk_marketplace`, `marketplace_nama`, `marketplace_ket`, `marketplace_biaya`, `marketplace_status`, `marketplace_create_date`, `marketplace_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Tokopedia', '-', 50, 'aktif', '2020-06-27 09:02:02', '2020-06-27 09:20:39', 1, 1),
(2, 'Shopee', '-', 40, 'AKTIF', '2020-06-27 11:07:26', '2020-06-27 11:07:26', 1, 1),
(3, 'Buka Lapak', '-', 15, 'AKTIF', '2020-07-23 04:53:22', '2021-06-09 01:26:29', 2, 2),
(4, 'yooooooooooo111', '-', 123111, 'nonaktif', '2021-05-24 09:34:16', '2021-05-24 09:36:21', 2, 2),
(5, 'Lazada', 'Lazada', 20, 'AKTIF', '2021-06-09 01:25:53', '2021-06-09 01:25:53', 2, 2);

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
-- Dumping data for table `mstr_menu`
--

INSERT INTO `mstr_menu` (`id_pk_menu`, `menu_name`, `menu_display`, `menu_icon`, `menu_category`, `menu_status`, `menu_create_date`, `menu_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'menu', 'Menu', 'menu', 'GENERAL', 'AKTIF', '2020-06-21 11:27:06', '2020-07-24 08:07:10', 1, 2),
(2, 'roles', 'Role & Hak Akses', 'key', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-07-24 08:12:03', 1, 2),
(3, 'barang', 'Barang', 'storage', 'GENERAL', 'AKTIF', '2020-06-21 11:38:11', '2020-07-24 08:02:19', 1, 2),
(4, 'barang_jenis', 'Jenis Barang', 'bookmark', 'GENERAL', 'AKTIF', '2020-06-21 11:38:23', '2020-07-24 08:05:35', 1, 2),
(5, 'barang_merk', 'Merk Barang', 'tag', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-07-24 08:07:19', 1, 2),
(6, 'customer', 'Customer', 'globe', 'GENERAL', 'AKTIF', '2020-06-21 11:38:44', '2020-07-24 08:03:36', 1, 2),
(7, 'employee', 'Employee', 'assignment-account', 'GENERAL', 'AKTIF', '2020-06-21 11:38:54', '2020-07-24 08:05:19', 1, 2),
(8, 'pembelian', 'Pembelian', 'shopping-cart', 'CABANG', 'AKTIF', '2020-06-21 11:39:36', '2020-07-28 11:59:33', 1, 2),
(9, 'penerimaan/cabang', 'Penerimaan', 'assignment-return', 'CABANG', 'AKTIF', '2020-06-21 11:40:07', '2020-07-28 11:59:44', 1, 2),
(10, 'pengiriman/cabang', 'Pengiriman', 'truck', 'CABANG', 'AKTIF', '2020-06-21 11:40:52', '2020-07-28 11:59:59', 1, 2),
(11, 'pengiriman/permintaan_gudang', 'Pengiriman Permintaan', 'truck', 'GUDANG', 'AKTIF', '2020-06-21 11:41:04', '2020-07-24 08:08:21', 1, 2),
(12, 'penjualan', 'Penjualan', 'label-heart', 'CABANG', 'AKTIF', '2020-06-21 11:41:23', '2020-07-28 12:00:16', 1, 2),
(13, 'permintaan', 'Permintaan', 'assignment-alert', 'CABANG', 'AKTIF', '2020-06-21 11:41:33', '2020-07-28 12:01:34', 1, 2),
(14, 'retur', 'Retur', 'layers-off', 'CABANG', 'AKTIF', '2020-06-21 11:41:42', '2020-07-28 12:00:38', 1, 2),
(15, 'satuan', 'Satuan', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:41:58', '2020-07-02 10:04:35', 1, 1),
(16, 'supplier', 'Supplier', 'gas-station', 'GENERAL', 'AKTIF', '2020-06-21 11:42:07', '2020-07-24 08:15:20', 1, 2),
(17, 'toko', 'Toko', 'store-24', 'GENERAL', 'AKTIF', '2020-06-21 11:42:16', '2020-07-24 08:04:29', 1, 2),
(18, 'user', 'User', 'library', 'GENERAL', 'AKTIF', '2020-06-21 11:42:28', '2020-07-24 08:15:59', 1, 2),
(19, 'warehouse', 'Warehouse', 'city-alt', 'GENERAL', 'AKTIF', '2020-06-21 11:42:37', '2020-07-24 08:16:20', 1, 2),
(20, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 02:52:25', 1, 1),
(21, 'toko/brg_cabang', 'Stok Cabang', 'dns', 'CABANG', 'AKTIF', '2020-06-22 07:50:23', '2020-07-24 08:13:50', 1, 2),
(22, 'pemenuhan/cabang', 'PEMENUHAN CABANG', 'edit', 'CABANG', 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 02:52:08', 1, 1),
(23, 'penerimaan/gudang', 'Penerimaan Gudang', 'assignment-return', 'GUDANG', 'AKTIF', '2020-06-22 06:10:33', '2020-07-24 08:08:47', 1, 2),
(24, 'pengiriman/permintaan', 'Pengiriman Permintaan', 'truck', 'CABANG', 'AKTIF', '2020-06-26 10:07:22', '2020-07-24 08:08:24', 1, 2),
(25, 'marketplace', 'Marketplace', 'globe', 'GENERAL', 'AKTIF', '2020-06-27 07:36:59', '2020-07-24 08:06:54', 1, 2),
(26, 'penerimaan/permintaan', 'Penerimaan Permintaan', 'assignment-return', 'CABANG', 'AKTIF', '2020-06-30 09:26:26', '2020-07-24 08:08:50', 1, 2),
(27, 'retur/konfirmasi', 'Konfirmasi Retur', 'assignment-check', 'CABANG', 'AKTIF', '2020-07-02 11:03:30', '2020-07-24 08:06:08', 1, 2),
(28, 'toko/cabang_toko', 'Daftar Cabang', 'store', 'TOKO', 'AKTIF', '2020-07-06 08:49:56', '2020-07-24 08:04:41', 1, 2),
(29, 'toko/dashboard_toko', 'Dashboard Toko', 'chart', 'TOKO', 'AKTIF', '2020-07-06 09:13:00', '2020-07-24 08:05:00', 1, 2),
(30, 'toko/dashboard_cabang', 'Dashboard Cabang', 'chart', 'CABANG', 'AKTIF', '2020-07-06 09:15:25', '2020-07-24 08:04:54', 1, 2),
(31, 'warehouse/dashboard_gudang', 'Dashboard Gudang', 'chart', 'GUDANG', 'AKTIF', '2020-07-06 09:43:54', '2020-07-24 08:04:57', 1, 2),
(32, 'warehouse/brg_warehouse', 'Stok Gudang', 'dns', 'GUDANG', 'AKTIF', '2020-07-06 09:59:33', '2020-07-24 08:13:53', 1, 2),
(33, 'permintaan/lain_gudang', 'Permintaan Barang', 'assignment-alert', 'GUDANG', 'AKTIF', '2020-07-07 10:38:41', '2020-07-24 08:11:13', 1, 2),
(34, 'penawaran', 'Penawaran', 'email', 'CABANG', 'AKTIF', '2020-07-10 09:45:16', '2020-07-24 08:07:55', 1, 2),
(35, 'toko/pengaturan_cabang', 'Pengaturan Cabang', 'settings', 'CABANG', 'AKTIF', '2020-07-14 11:28:08', '2020-07-24 08:09:15', 1, 2),
(36, 'toko/pengaturan_toko', 'Pengaturan Toko', 'settings', 'TOKO', 'AKTIF', '2020-07-14 11:29:16', '2020-07-24 08:09:20', 1, 2),
(37, 'warehouse/pengaturan_warehouse', 'Pengaturan Gudang', 'settings', 'GUDANG', 'AKTIF', '2020-07-14 11:29:41', '2020-07-29 09:31:04', 1, 2),
(38, 'barang/katalog', 'Katalog', 'collection-item-5', 'GENERAL', 'AKTIF', '2020-07-15 08:20:40', '2020-07-24 08:05:49', 2, 2),
(39, 'toko/katalog', 'Katalog', 'collection-item-5', 'CABANG', 'AKTIF', '2020-07-15 08:20:56', '2020-07-24 08:05:56', 2, 2),
(40, 'log', 'Log Sistem', 'assignment', 'GENERAL', 'AKTIF', '2020-07-17 02:06:25', '2020-07-24 08:06:34', 2, 2),
(41, 'permintaan/lain', 'Permintaan Barang', 'code-setting', 'CABANG', 'AKTIF', '2020-07-28 11:37:56', '2020-07-28 11:41:09', 2, 2),
(42, 'dashboard/generate', 'Dashboard Umum', 'chart', 'GENERAL', 'AKTIF', '2020-07-29 11:45:28', '2020-07-30 05:39:02', 2, 2),
(44, 'customer/toko', 'Customer Toko', 'globe', 'TOKO', 'AKTIF', '2021-05-09 08:51:50', '2021-05-09 09:01:23', 2, 2),
(45, 'test123', 'test123', 'test123', 'GUDANG', 'nonaktif', '2021-05-24 09:44:33', '2021-05-24 10:08:23', 2, 2);

--
-- Triggers `mstr_menu`
--
DELIMITER $$
CREATE TRIGGER `trg_insert_new_menu_to_all_hak_akses` AFTER INSERT ON `mstr_menu` FOR EACH ROW begin
	/* insert new menu to all hak akses*/
	set @id_menu = new.id_pk_menu;
	insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
	select id_pk_jabatan,@id_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_jabatan;
end
$$
DELIMITER ;

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
-- Dumping data for table `mstr_pembelian`
--

INSERT INTO `mstr_pembelian` (`id_pk_pembelian`, `pem_pk_nomor`, `pem_tgl`, `pem_status`, `id_fk_supp`, `id_fk_cabang`, `pem_create_date`, `pem_last_modified`, `id_create_data`, `id_last_modified`, `no_control`, `bln_control`, `thn_control`) VALUES
(23, 'MSTRCABANG3-PEMBELIAN-2020-08-01-000001', '2020-08-01', 'aktif', 1, 2, '2020-07-25 12:43:41', '2020-07-27 09:57:27', 2, 2, 1, 7, 2020),
(24, 'MSTRCABANG3-PEMBELIAN-2020-07-29-000002', '2020-07-30', 'aktif', 1, 2, '2020-07-27 08:51:25', '2020-07-27 08:53:01', 2, 2, 2, 7, 2020),
(26, 'CBGKRC1-PEMBELIAN-2020-07-30-000001', '2020-07-30', 'selesai', 2, 1, '2020-07-29 08:47:06', '2021-06-09 02:01:44', 2, 2, 1, 7, 2020),
(25, 'MSTRCABANG3-PEMBELIAN-2020-07-29-000003', '2020-07-29', 'aktif', 2, 2, '2020-07-27 08:58:14', '2020-07-27 09:57:34', 2, 2, 3, 7, 2020),
(27, 'CBG2-PEMBELIAN-2021-06-05-000001', '2021-06-12', 'nonaktif', 2, 4, '2021-05-29 12:49:53', '2021-05-29 12:51:00', 2, 2, 1, 6, 2021),
(28, 'CBG2-PEMBELIAN-2021-06-05-000002', '2021-06-05', 'selesai', 3, 4, '2021-05-29 12:50:54', '2021-05-29 12:51:02', 2, 2, 2, 6, 2021),
(29, 'PS-PEMBELIAN-2021-06-18-000001', '2021-06-27', 'AKTIF', 5, 1, '2021-06-09 10:20:05', '2021-06-10 08:24:20', 5, 5, 1, 6, 2021),
(30, 'PS-PEMBELIAN-2021-06-18-000002', '2021-06-18', 'AKTIF', 2, 1, '2021-06-09 10:20:13', '2021-06-10 01:52:42', 5, 5, 2, 6, 2021),
(31, 'PS-PEMBELIAN-2021-06-12-000003', '2021-06-12', 'selesai', 2, 1, '2021-06-10 01:53:29', '2021-06-10 01:53:44', 5, 5, 3, 6, 2021),
(32, 'CBG1-PEMBELIAN-2021-06-24-000001', '2021-06-24', 'AKTIF', 2, 3, '2021-06-17 12:02:52', '2021-06-17 12:02:52', 5, 5, 1, 6, 2021);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penawaran`
--

CREATE TABLE `mstr_penawaran` (
  `id_pk_penawaran` int(11) NOT NULL,
  `penawaran_no` varchar(50) NOT NULL,
  `penawaran_subject` varchar(100) DEFAULT NULL,
  `penawaran_content` varchar(600) DEFAULT NULL,
  `penawaran_notes` varchar(600) DEFAULT NULL,
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
-- Dumping data for table `mstr_penawaran`
--

INSERT INTO `mstr_penawaran` (`id_pk_penawaran`, `penawaran_no`, `penawaran_subject`, `penawaran_content`, `penawaran_notes`, `penawaran_file`, `penawaran_tgl`, `penawaran_refrensi`, `penawaran_status`, `id_fk_cabang`, `penawaran_create_date`, `penawaran_last_modified`, `id_create_date`, `id_last_modified`) VALUES
(1, '', 'Subjek', 'Konten', 'notes', 'TH8621.jpg', '2020-07-16 00:00:00', 'Penawar', 'AKTIF', 1, '2020-07-14 08:51:57', '2020-07-14 09:00:03', 1, 1),
(2, '', 'Subjek2', 'Ikonten2', 'notesn2', 'noimage.jpg', '2020-07-15 00:00:00', 'Penawaran 22', 'AKTIF', 1, '2020-07-14 08:54:35', '2020-07-14 09:02:38', 1, 1),
(3, '', 'ASDF3', 'ASDF3', 'ASDF3', NULL, '2021-06-05 00:00:00', '6', 'AKTIF', 4, '2021-05-29 12:07:07', '2021-05-29 12:12:43', 2, 2),
(4, '', 'ASDF', 'ASDF', 'ASDF', NULL, '2021-05-29 00:00:00', '2', 'AKTIF', 4, '2021-05-29 12:07:21', '2021-05-29 12:16:02', 2, 2),
(5, '', 'ASDFaa', 'ASDFaaa', 'ASDFaaa', NULL, '2021-05-29 00:00:00', '2', 'nonaktif', 4, '2021-05-29 12:10:02', '2021-05-29 12:19:44', 2, 2),
(6, '', 'Hi', 'OKE BOS', 'ASDA', NULL, '2021-06-09 00:00:00', '2', 'AKTIF', 1, '2021-06-09 01:41:47', '2021-06-09 01:41:47', 2, 2),
(7, '', 'Hi', 'OKE BOS', 'ASDA', NULL, '2021-06-09 00:00:00', '2', 'nonaktif', 1, '2021-06-09 01:41:54', '2021-06-09 01:42:27', 2, 2),
(8, '', 'Hi', 'OKE BOS', 'ASDA', NULL, '2021-06-09 00:00:00', '2', 'nonaktif', 1, '2021-06-09 01:41:56', '2021-06-09 01:42:29', 2, 2),
(9, '', 'test', 'test', 'test', NULL, '2021-06-17 00:00:00', '2', 'AKTIF', 1, '2021-06-09 09:44:43', '2021-06-09 09:44:43', 5, 5),
(10, '', 'test', 'test', 'test', NULL, '2021-06-17 00:00:00', '2', 'AKTIF', 1, '2021-06-09 09:44:48', '2021-06-09 09:44:48', 5, 5),
(11, 'NOMORPENAWRAN123', 'Penawaran Harga Revisi 2 - 15 Oktober 2021', 'Yang kami hormati, Bapak Andi.\r\n\r\nBerikut penawaran yang kami ajukan atas kebutuhan Bapak dalam mendukung pelaksanaan program kerja tahun 2022.\r\nKetersediaan barang-barang yang kami tawarkan di bawah bergantung pada tanggal persetujuan.\r\n\r\nBerikut metode pembayaran yang dapat kami tawarkan kepada Bapak:\r\n1. Full Payment 100% bayar di muka\r\n2. Down payment 50% + pelunasan 50% setelah barang diterima', 'Yang kami hormati, Bapak Andi.\r\n\r\nBerikut penawaran yang kami ajukan atas kebutuhan Bapak dalam mendukung pelaksanaan program kerja tahun 2022.\r\nKetersediaan barang-barang yang kami tawarkan di bawah bergantung pada tanggal persetujuan.\r\n\r\nBerikut metode pembayaran yang dapat kami tawarkan kepada Bapak:\r\n1. Full Payment 100% bayar di muka\r\n2. Down payment 50% + pelunasan 50% setelah barang diterima', NULL, '2021-10-30 00:00:00', '960', 'AKTIF', 5, '2021-10-16 07:33:01', '2021-10-16 08:24:26', 4, 4),
(12, 'NO-123-544-2020', '-', '-', '-', NULL, '2021-10-29 00:00:00', '1617', 'AKTIF', 5, '2021-10-16 07:53:53', '2021-10-16 07:53:53', 4, 4);

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
-- Dumping data for table `mstr_penerimaan`
--

INSERT INTO `mstr_penerimaan` (`id_pk_penerimaan`, `penerimaan_tgl`, `penerimaan_status`, `penerimaan_tipe`, `id_fk_pembelian`, `id_fk_retur`, `penerimaan_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `penerimaan_create_date`, `penerimaan_last_modified`, `id_create_data`, `id_last_modified`, `no_control`, `bln_control`, `thn_control`) VALUES
(9, '2020-07-01 12:17:51', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:17:51', '2020-07-01 12:28:21', 1, 1, NULL, NULL, NULL),
(10, '2020-07-01 12:18:24', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:18:24', '2020-07-01 12:28:17', 1, 1, NULL, NULL, NULL),
(11, '2020-07-01 12:29:02', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:02', '2020-07-01 12:29:09', 1, 1, NULL, NULL, NULL),
(12, '2020-07-01 12:29:20', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:20', '2020-07-01 12:30:28', 1, 1, NULL, NULL, NULL),
(13, '2020-07-01 12:29:22', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:22', '2020-07-01 12:29:30', 1, 1, NULL, NULL, NULL),
(14, '2020-07-01 12:31:57', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:31:57', '2020-07-01 12:32:03', 1, 1, NULL, NULL, NULL),
(15, '2020-07-01 12:33:04', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:33:04', '2020-07-01 12:33:11', 1, 1, NULL, NULL, NULL),
(16, '1111-11-11 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:21', 1, 1, NULL, NULL, NULL),
(17, '2020-07-18 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-04 09:52:12', '2020-07-04 09:52:12', 1, 1, NULL, NULL, NULL),
(18, '2020-07-03 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1, NULL, NULL, NULL),
(19, '1111-11-11 00:00:00', 'nonaktif', 'pembelian', 4, 0, 'WAREHOUSE', 1, NULL, '2020-07-07 10:35:22', '2020-07-07 10:35:52', 1, 1, NULL, NULL, NULL),
(20, '2020-07-22 14:28:54', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-22 02:28:54', '2020-07-22 02:28:54', 2, 2, NULL, NULL, NULL),
(21, '2020-07-22 14:31:31', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 1, '2020-07-22 02:31:31', '2020-07-22 02:31:31', 2, 2, NULL, NULL, NULL),
(22, '2020-07-24 00:00:00', 'AKTIF', 'pembelian', 4, 0, 'CABANG', NULL, 2, '2020-07-24 07:38:20', '2020-07-24 07:40:08', 2, 2, NULL, NULL, NULL),
(23, '2020-07-25 00:00:00', 'nonaktif', 'retur', 0, 1, 'CABANG', NULL, 2, '2020-07-24 07:40:36', '2020-07-27 11:24:14', 2, 2, NULL, NULL, NULL),
(24, '2020-07-28 00:00:00', 'nonaktif', 'pembelian', 24, 0, 'CABANG', NULL, 2, '2020-07-27 10:31:54', '2020-07-27 10:33:17', 2, 2, NULL, NULL, NULL),
(25, '2020-07-28 00:00:00', 'nonaktif', 'pembelian', 24, 0, 'CABANG', NULL, 2, '2020-07-27 10:33:05', '2020-07-27 10:33:15', 2, 2, NULL, NULL, NULL),
(26, '2020-07-31 00:00:00', 'nonaktif', 'pembelian', 24, 0, 'CABANG', NULL, 2, '2020-07-27 10:34:29', '2021-05-29 04:56:16', 2, 2, NULL, NULL, NULL),
(27, '2020-07-28 00:00:00', 'AKTIF', 'pembelian', 25, 0, 'CABANG', NULL, 2, '2020-07-27 10:37:34', '2020-07-27 10:41:21', 2, 2, NULL, NULL, NULL),
(28, '2020-07-30 00:00:00', 'nonaktif', 'retur', 0, 2, 'CABANG', NULL, 2, '2020-07-27 11:23:56', '2021-05-29 04:54:44', 2, 2, NULL, NULL, NULL),
(29, '2020-08-04 00:00:00', 'AKTIF', 'retur', 0, 2, 'CABANG', NULL, 2, '2020-07-27 11:35:52', '2020-07-27 11:35:52', 2, 2, NULL, NULL, NULL),
(30, '2020-07-28 14:28:54', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 2, '2020-07-28 02:28:54', '2020-07-28 02:29:16', 2, 2, NULL, NULL, NULL),
(31, '2020-07-28 14:29:45', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 2, '2020-07-28 02:29:45', '2020-07-28 02:29:58', 2, 2, NULL, NULL, NULL),
(32, '2020-07-28 14:30:49', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 2, '2020-07-28 02:30:49', '2020-07-28 03:38:51', 2, 2, NULL, NULL, NULL),
(33, '2020-07-28 15:34:26', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 2, '2020-07-28 03:34:26', '2020-07-28 03:34:26', 2, 2, NULL, NULL, NULL),
(34, '2020-07-28 15:34:35', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 2, '2020-07-28 03:34:35', '2020-07-28 03:34:35', 2, 2, NULL, NULL, NULL),
(35, '2020-07-28 15:41:07', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 2, '2020-07-28 03:41:07', '2020-07-28 03:41:07', 2, 2, NULL, NULL, NULL),
(36, '2020-07-28 15:42:09', 'nonaktif', 'permintaan', 0, 0, 'Cabang', NULL, 2, '2020-07-28 03:42:09', '2021-05-29 05:25:25', 2, 2, NULL, NULL, NULL),
(37, '2020-07-28 15:44:21', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 2, '2020-07-28 03:44:21', '2020-07-28 03:44:21', 2, 2, NULL, NULL, NULL),
(38, '2020-07-28 15:44:41', 'nonaktif', 'permintaan', 0, 0, 'Cabang', NULL, 2, '2020-07-28 03:44:41', '2021-05-29 05:25:31', 2, 2, NULL, NULL, NULL),
(39, '2020-07-31 00:00:00', 'AKTIF', 'pembelian', 26, 0, 'WAREHOUSE', 2, NULL, '2020-07-29 08:57:26', '2020-07-29 09:30:13', 2, 2, NULL, NULL, NULL),
(40, '2020-07-29 10:26:31', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 2, '2020-07-29 10:26:31', '2020-07-29 10:26:31', 2, 2, NULL, NULL, NULL),
(41, '2021-05-29 17:25:28', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 2, '2021-05-29 05:25:28', '2021-05-29 05:26:21', 2, 2, NULL, NULL, NULL),
(42, '2021-05-29 17:26:31', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 2, '2021-05-29 05:26:31', '2021-05-29 05:26:31', 2, 2, NULL, NULL, NULL),
(43, '2021-05-29 17:26:34', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 2, '2021-05-29 05:26:34', '2021-05-29 05:26:34', 2, 2, NULL, NULL, NULL),
(44, '2021-06-17 00:00:00', 'AKTIF', 'pembelian', 29, 0, 'WAREHOUSE', 11, NULL, '2021-06-09 10:22:59', '2021-06-09 10:23:16', 5, 5, NULL, NULL, NULL),
(45, '2021-06-11 00:00:00', 'AKTIF', 'pembelian', 29, 0, 'CABANG', NULL, 1, '2021-06-10 01:54:25', '2021-06-10 01:54:25', 5, 5, NULL, NULL, NULL),
(46, '2021-06-15 00:00:00', 'nonaktif', 'retur', 0, 5, 'CABANG', NULL, 1, '2021-06-15 07:53:06', '2021-06-15 07:53:32', 5, 5, NULL, NULL, NULL),
(47, '2021-07-02 00:00:00', 'AKTIF', 'retur', 0, 5, 'CABANG', NULL, 1, '2021-06-15 07:53:43', '2021-06-15 07:53:43', 5, 5, NULL, NULL, NULL),
(48, '2021-06-16 22:32:41', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2021-06-16 10:32:41', '2021-06-16 10:32:41', 5, 5, NULL, NULL, NULL),
(49, '2021-06-16 22:49:59', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 1, '2021-06-16 10:49:59', '2021-06-16 10:49:59', 5, 5, NULL, NULL, NULL),
(50, '2021-06-16 22:50:02', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 1, '2021-06-16 10:50:02', '2021-06-16 10:50:02', 5, 5, NULL, NULL, NULL),
(51, '2021-06-26 00:00:00', 'AKTIF', 'pembelian', 29, 0, 'WAREHOUSE', 11, NULL, '2021-06-17 12:01:48', '2021-06-17 12:01:48', 5, 5, NULL, NULL, NULL),
(52, '2021-06-18 00:00:00', 'AKTIF', 'pembelian', 32, 0, 'WAREHOUSE', 5, NULL, '2021-06-17 12:03:34', '2021-06-17 12:03:34', 5, 5, NULL, NULL, NULL),
(53, '2021-06-17 00:18:27', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 1, '2021-06-17 12:18:27', '2021-06-17 12:18:27', 5, 5, NULL, NULL, NULL),
(54, '2021-06-17 00:18:31', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 1, '2021-06-17 12:18:31', '2021-06-17 12:18:31', 5, 5, NULL, NULL, NULL),
(55, '2021-06-17 00:18:33', 'aktif', 'permintaan', 0, 0, 'Cabang', NULL, 1, '2021-06-17 12:18:33', '2021-06-17 12:18:33', 5, 5, NULL, NULL, NULL);

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
-- Dumping data for table `mstr_pengiriman`
--

INSERT INTO `mstr_pengiriman` (`id_pk_pengiriman`, `pengiriman_no`, `pengiriman_tgl`, `pengiriman_status`, `pengiriman_tipe`, `id_fk_penjualan`, `id_fk_retur`, `pengiriman_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `pengiriman_create_date`, `pengiriman_last_modified`, `id_create_data`, `id_last_modified`, `no_control`, `bln_control`, `thn_control`) VALUES
(1, 'CBG2-PENGIRIMAN-2021-06-05-000001', '2021-06-05 00:00:00', 'AKTIF', 'penjualan', 1, 0, 'cabang', NULL, 4, '2021-06-05 08:22:16', '2021-06-05 08:22:21', 2, 2, 1, 6, 2021),
(2, 'CBG2-PENGIRIMAN-2021-06-05-000002', '2021-06-05 00:00:00', 'AKTIF', 'penjualan', 2, 0, 'cabang', NULL, 4, '2021-06-05 08:26:44', '2021-06-05 08:27:16', 2, 2, 2, 6, 2021),
(3, 'CBG2-PENGIRIMAN-2021-06-05-000003', '2021-06-05 00:00:00', 'AKTIF', 'penjualan', 2, 0, 'cabang', NULL, 4, '2021-06-05 08:27:31', '2021-06-05 08:27:31', 2, 2, 3, 6, 2021),
(4, 'CBG2-PENGIRIMAN-2021-06-05-000004', '2021-06-05 00:00:00', 'AKTIF', 'penjualan', 1, 0, 'cabang', NULL, 4, '2021-06-05 08:44:56', '2021-06-05 08:44:56', 2, 2, 4, 6, 2021),
(5, 'CBG2-PENGIRIMAN-2021-06-05-000005', '2021-06-05 00:00:00', 'AKTIF', 'penjualan', 1, 0, 'cabang', NULL, 4, '2021-06-05 08:56:29', '2021-06-05 11:11:29', 2, 2, 5, 6, 2021),
(6, 'CBG2-PENGIRIMAN-2021-06-18-000006', '2021-06-18 00:00:00', 'AKTIF', 'penjualan', 1, 0, 'cabang', NULL, 4, '2021-06-05 11:11:55', '2021-06-05 11:11:55', 2, 2, 6, 6, 2021),
(7, 'CBG2-PENGIRIMAN-2021-06-16-000007', '2021-06-16 00:00:00', 'nonaktif', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:07:00', '2021-06-07 10:09:44', 2, 2, 7, 6, 2021),
(8, 'CBG2-PENGIRIMAN-2021-06-09-000007', '2021-06-10 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:24:15', '2021-06-07 10:49:25', 2, 2, 7, 6, 2021),
(9, 'CBG2-PENGIRIMAN-2021-06-24-000008', '2021-06-24 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:49:37', '2021-06-07 10:50:59', 2, 2, 8, 6, 2021),
(10, 'CBG2-PENGIRIMAN-2021-06-16-000009', '2021-06-16 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:51:15', '2021-06-07 10:51:44', 2, 2, 9, 6, 2021),
(11, 'CBG2-PENGIRIMAN-2021-06-18-000010', '2021-06-18 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:51:59', '2021-06-07 10:51:59', 2, 2, 10, 6, 2021),
(12, 'CBG2-PENGIRIMAN-2021-07-02-000001', '2021-07-02 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:53:05', '2021-06-07 10:53:05', 2, 2, 1, 7, 2021),
(13, 'CBG2-PENGIRIMAN-2021-06-25-000011', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:55:22', '2021-06-07 10:55:22', 2, 2, 11, 6, 2021),
(14, 'CBG2-PENGIRIMAN-2021-06-25-000012', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:57:27', '2021-06-07 10:57:27', 2, 2, 12, 6, 2021),
(15, 'CBG2-PENGIRIMAN-2021-06-25-000013', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:57:55', '2021-06-07 10:57:55', 2, 2, 13, 6, 2021),
(16, 'CBG2-PENGIRIMAN-2021-06-25-000014', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:58:16', '2021-06-07 10:58:16', 2, 2, 14, 6, 2021),
(17, 'CBG2-PENGIRIMAN-2021-06-25-000015', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:58:37', '2021-06-07 10:58:37', 2, 2, 15, 6, 2021),
(18, 'CBG2-PENGIRIMAN-2021-06-25-000016', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:59:05', '2021-06-07 10:59:05', 2, 2, 16, 6, 2021),
(19, 'CBG2-PENGIRIMAN-2021-06-25-000017', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:59:21', '2021-06-07 10:59:21', 2, 2, 17, 6, 2021),
(20, 'CBG2-PENGIRIMAN-2021-06-25-000018', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:59:34', '2021-06-07 10:59:34', 2, 2, 18, 6, 2021),
(21, 'CBG2-PENGIRIMAN-2021-06-25-000019', '2021-06-25 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 10:59:46', '2021-06-07 10:59:46', 2, 2, 19, 6, 2021),
(22, 'CBG2-PENGIRIMAN-2021-06-24-000020', '2021-06-24 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 11:00:20', '2021-06-07 11:00:20', 2, 2, 20, 6, 2021),
(23, 'CBG2-PENGIRIMAN-2021-06-24-000021', '2021-06-24 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 11:01:22', '2021-06-07 11:01:22', 2, 2, 21, 6, 2021),
(24, 'CBG2-PENGIRIMAN-2021-06-24-000022', '2021-06-24 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 11:08:48', '2021-06-07 11:08:48', 2, 2, 22, 6, 2021),
(25, 'CBG2-PENGIRIMAN-2021-06-24-000023', '2021-06-24 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 11:09:18', '2021-06-07 11:09:18', 2, 2, 23, 6, 2021),
(26, 'CBG2-PENGIRIMAN-2021-06-18-000024', '2021-06-18 00:00:00', 'AKTIF', 'retur', 0, 2, 'cabang', NULL, 4, '2021-06-07 11:09:45', '2021-06-07 11:09:45', 2, 2, 24, 6, 2021),
(27, 'CBG2-PENGIRIMAN-2021-06-10-000025', '2021-06-10 00:00:00', 'AKTIF', 'penjualan', 2, 0, 'cabang', NULL, 4, '2021-06-07 11:10:17', '2021-06-07 11:10:34', 2, 2, 25, 6, 2021),
(28, 'CBG2-PENGIRIMAN-2021-06-25-000026', '2021-06-25 00:00:00', 'AKTIF', 'penjualan', 2, 0, 'cabang', NULL, 4, '2021-06-07 11:12:59', '2021-06-07 11:12:59', 2, 2, 26, 6, 2021),
(29, 'CBG2-PENGIRIMAN-2021-06-17-000027', '2021-06-17 00:00:00', 'AKTIF', 'penjualan', 2, 0, 'cabang', NULL, 4, '2021-06-07 11:13:25', '2021-06-07 11:13:25', 2, 2, 27, 6, 2021),
(30, 'CBG2-PENGIRIMAN-2021-06-11-000028', '2021-06-11 00:00:00', 'AKTIF', 'penjualan', 1, 0, 'cabang', NULL, 4, '2021-06-07 11:13:42', '2021-06-07 11:13:42', 2, 2, 28, 6, 2021),
(31, 'CBG2-PENGIRIMAN-2021-06-11-000029', '2021-06-11 00:00:00', 'AKTIF', 'penjualan', 1, 0, 'cabang', NULL, 4, '2021-06-07 11:13:51', '2021-06-07 11:14:09', 2, 2, 29, 6, 2021),
(32, 'PS-PENGIRIMAN-2021-06-09-000001', '2021-06-11 00:00:00', 'AKTIF', 'penjualan', 3, 0, 'cabang', NULL, 1, '2021-06-09 01:55:04', '2021-06-09 01:55:48', 2, 2, 1, 6, 2021),
(33, 'PS-PENGIRIMAN-2021-06-18-000002', '2021-06-18 00:00:00', 'AKTIF', 'retur', 0, 6, 'cabang', NULL, 1, '2021-06-15 08:07:45', '2021-06-15 08:07:45', 5, 5, 2, 6, 2021),
(34, 'PS-PENGIRIMAN-2021-06-18-000003', '2021-06-18 00:00:00', 'nonaktif', 'retur', 0, 6, 'cabang', NULL, 1, '2021-06-15 08:07:48', '2021-06-15 08:08:41', 5, 5, 3, 6, 2021),
(35, 'PS-PENGIRIMAN-2021-06-18-000004', '2021-06-18 00:00:00', 'nonaktif', 'retur', 0, 6, 'cabang', NULL, 1, '2021-06-15 08:07:53', '2021-06-15 08:08:45', 5, 5, 4, 6, 2021),
(36, 'PS-PENGIRIMAN-2021-06-18-000005', '2021-06-18 00:00:00', 'AKTIF', 'retur', 0, 6, 'cabang', NULL, 1, '2021-06-15 08:08:33', '2021-06-15 08:08:33', 5, 5, 5, 6, 2021),
(37, 'CBG1-PENGIRIMAN-2021-06-16-000001', '2021-06-16 22:30:11', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 3, '2021-06-16 10:30:11', '2021-06-16 10:45:51', 5, 5, 1, 6, 2021),
(38, 'CBG1-PENGIRIMAN-2021-06-16-000002', '2021-06-16 22:31:55', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 3, '2021-06-16 10:31:55', '2021-06-16 10:31:55', 5, 5, 2, 6, 2021),
(39, 'PRINDAH-PENGIRIMAN-2021-06-16-000001', '2021-06-16 22:43:20', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 5, '2021-06-16 10:43:20', '2021-06-16 10:43:20', 5, 5, 1, 6, 2021),
(40, 'PRINDAH-PENGIRIMAN-2021-06-16-000002', '2021-06-16 22:43:44', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 5, '2021-06-16 10:43:44', '2021-06-16 10:43:44', 5, 5, 2, 6, 2021),
(41, 'CBG1-PENGIRIMAN-2021-06-16-000003', '2021-06-16 22:46:02', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 3, '2021-06-16 10:46:02', '2021-06-16 10:46:02', 5, 5, 3, 6, 2021),
(42, 'CBG1-PENGIRIMAN-2021-06-17-000004', '2021-06-17 00:08:02', 'aktif', 'permintaan', 0, 0, 'warehouse', 5, NULL, '2021-06-17 12:08:02', '2021-06-17 12:08:02', 5, 5, 4, 6, 2021),
(43, 'CBG1-PENGIRIMAN-2021-06-17-000004', '2021-06-17 00:08:05', 'aktif', 'permintaan', 0, 0, 'warehouse', 5, NULL, '2021-06-17 12:08:05', '2021-06-17 12:08:05', 5, 5, 4, 6, 2021),
(44, 'PS-PENGIRIMAN-2021-07-03-000001', '2021-07-03 00:00:00', 'AKTIF', 'penjualan', NULL, 0, 'cabang', NULL, NULL, '2021-06-26 12:30:39', '2021-06-26 12:30:39', 5, 5, 1, 7, 2021),
(45, 'PS-PENGIRIMAN-2021-07-03-000001', '2021-07-03 00:00:00', 'AKTIF', 'penjualan', 5, 0, 'cabang', NULL, NULL, '2021-06-26 12:36:26', '2021-06-26 12:36:26', 5, 5, 1, 7, 2021),
(46, 'PS-PENGIRIMAN-2021-07-03-000001', '2021-07-03 00:00:00', 'AKTIF', 'penjualan', 5, 0, 'cabang', NULL, NULL, '2021-06-26 12:43:19', '2021-06-26 12:43:19', 5, 5, 1, 7, 2021),
(47, 'PS-PENGIRIMAN-2021-06-26-000006', '2021-06-26 00:00:00', 'AKTIF', 'penjualan', 5, 0, 'cabang', NULL, NULL, '2021-06-26 12:43:38', '2021-06-26 12:43:38', 5, 5, 6, 6, 2021),
(48, 'PS-PENGIRIMAN-2021-07-03-000001', '2021-07-03 00:00:00', 'nonaktif', 'penjualan', 5, 0, 'cabang', NULL, 1, '2021-06-26 12:48:20', '2021-06-26 12:49:47', 5, 5, 1, 7, 2021),
(49, 'PS-PENGIRIMAN-2021-07-03-000001', '2021-07-03 00:00:00', 'AKTIF', 'retur', 0, 10, 'cabang', NULL, 1, '2021-06-26 01:02:09', '2021-06-26 01:02:09', 5, 5, 1, 7, 2021),
(50, 'PS-PENGIRIMAN-2021-07-03-000002', '2021-07-03 00:00:00', 'nonaktif', 'retur', 0, 10, 'cabang', NULL, 1, '2021-06-26 01:02:58', '2021-06-26 01:09:47', 5, 5, 2, 7, 2021),
(51, 'PRINDAH-PENGIRIMAN-2021-06-26-000003', '2021-06-26 10:49:02', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 5, '2021-06-26 10:49:02', '2021-06-26 10:49:02', 5, 5, 3, 6, 2021),
(52, 'PS-PENGIRIMAN-2021-06-26-000006', '2021-06-26 11:07:24', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 1, '2021-06-26 11:07:24', '2021-06-26 01:05:12', 5, 5, 6, 6, 2021),
(53, 'PS-PENGIRIMAN-2021-06-26-000007', '2021-06-26 11:07:27', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 1, '2021-06-26 11:07:27', '2021-06-26 01:05:17', 5, 5, 7, 6, 2021),
(54, 'PS-PENGIRIMAN-2021-07-03-000003', '2021-07-03 00:00:00', 'nonaktif', 'penjualan', 5, 0, 'cabang', NULL, 1, '2021-06-26 12:51:05', '2021-06-26 01:08:26', 5, 5, 3, 7, 2021),
(55, 'PS-PENGIRIMAN-2021-07-03-000003', '2021-07-03 00:00:00', 'nonaktif', 'penjualan', 5, 0, 'cabang', NULL, 1, '2021-06-26 01:08:45', '2021-06-26 01:08:52', 5, 5, 3, 7, 2021);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penjualan`
--

CREATE TABLE `mstr_penjualan` (
  `id_pk_penjualan` int(11) NOT NULL,
  `penj_nomor` varchar(100) DEFAULT NULL,
  `penj_nominal` bigint(20) DEFAULT 0,
  `penj_nominal_byr` bigint(20) DEFAULT 0,
  `penj_tgl` datetime DEFAULT NULL,
  `penj_dateline_tgl` datetime DEFAULT NULL,
  `penj_jenis` varchar(50) DEFAULT NULL,
  `penj_tipe_pembayaran` varchar(50) DEFAULT NULL COMMENT 'ini diubah jadi toggle PPN ',
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
-- Dumping data for table `mstr_penjualan`
--

INSERT INTO `mstr_penjualan` (`id_pk_penjualan`, `penj_nomor`, `penj_nominal`, `penj_nominal_byr`, `penj_tgl`, `penj_dateline_tgl`, `penj_jenis`, `penj_tipe_pembayaran`, `penj_status`, `id_fk_customer`, `id_fk_cabang`, `penj_create_date`, `penj_last_modified`, `id_create_data`, `id_last_modified`, `no_control`, `bln_control`, `thn_control`) VALUES
(1, 'CBG2-PENJUALAN-2021-06-05-000001', 24000, 10000, '2021-06-05 00:00:00', '2021-06-19 00:00:00', 'ONLINE', '1', 'AKTIF', 2, 4, '2021-06-05 17:38:14', '2021-06-07 08:34:28', 2, 2, 1, 6, 2021),
(2, 'CBG2-PENJUALAN-2021-06-05-000002', 3200000, 3200000, '2021-06-05 00:00:00', '2021-06-19 00:00:00', 'ONLINE', '0', 'AKTIF', 6, 4, '2021-06-05 17:46:37', '2021-06-05 07:48:34', 2, 2, 2, 6, 2021),
(3, 'PS-PENJUALAN-2021-06-10-000001', 500000, 550000, '2021-06-10 00:00:00', '2021-06-12 00:00:00', 'ONLINE', '1', 'selesai', 2, 1, '2021-06-09 01:46:58', '2021-06-26 12:20:26', 2, 5, 1, 6, 2021),
(4, 'PS-PENJUALAN-2021-06-10-000002', 500000, 200000, '2021-06-10 00:00:00', '2021-06-12 00:00:00', 'ONLINE', '1', 'AKTIF', 2, 1, '2021-06-09 01:47:03', '2021-06-09 01:50:40', 2, 2, 2, 6, 2021),
(5, 'PS-PENJUALAN-2021-06-26-000003', 2000000, 2200000, '2021-06-26 00:00:00', '2021-07-10 00:00:00', 'OFFLINE', '1', 'AKTIF', 3739, 1, '2021-06-26 00:25:45', '2021-06-26 12:25:45', 5, 5, 3, 6, 2021),
(6, 'CBG1-PENJUALAN-2021-10-23-000001', 140000, 0, '2021-10-23 00:00:00', '2021-11-06 00:00:00', 'OFFLINE', '0', 'AKTIF', 960, 3, '2021-10-16 08:01:06', '2021-10-16 08:01:06', 4, 4, 1, 10, 2021),
(7, 'CBG1-PENJUALAN-2021-10-23-000002', 1400000, 1540000, '2021-10-23 00:00:00', '2021-10-30 00:00:00', 'OFFLINE', '1', 'AKTIF', 960, 3, '2021-10-16 08:02:21', '2021-10-16 08:03:38', 4, 4, 2, 10, 2021);

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
  `id_retur_confirm` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_retur`
--

INSERT INTO `mstr_retur` (`id_pk_retur`, `id_fk_penjualan`, `retur_no`, `retur_tgl`, `retur_tipe`, `retur_status`, `retur_create_date`, `retur_last_modified`, `id_create_data`, `id_last_modified`, `no_control`, `bln_control`, `thn_control`, `retur_confirm_date`, `id_retur_confirm`) VALUES
(1, 1, 'CBG2-RETUR-2021-06-09-000001', '2021-06-09 00:00:00', 'UANG', 'nonaktif', '2021-06-07 08:52:38', '2021-06-07 08:54:25', 2, 2, 1, 6, 2021, NULL, NULL),
(2, 1, 'CBG2-RETUR-2021-06-08-000002', '2021-06-08 00:00:00', 'BARANG', 'aktif', '2021-06-07 08:53:17', '2021-06-07 09:52:40', 2, 2, 2, 6, 2021, '2021-06-07 09:52:59', 2),
(3, 1, 'CBG2-RETUR-2021-06-16-000003', '2021-06-16 00:00:00', 'UANG', 'aktif', '2021-06-07 08:55:02', '2021-06-07 09:25:32', 2, 2, 3, 6, 2021, '2021-06-07 09:53:05', 2),
(4, 2, 'CBG2-RETUR-2021-06-18-000004', '2021-06-18 00:00:00', 'UANG', 'aktif', '2021-06-07 09:56:20', '2021-06-07 09:56:20', 2, 2, 4, 6, 2021, '2021-06-07 10:01:44', 2),
(5, 3, 'PS-RETUR-2021-06-04-000001', '2021-06-04 00:00:00', 'UANG', 'aktif', '2021-06-09 02:00:12', '2021-06-09 02:00:57', 2, 2, 1, 6, 2021, '2021-06-15 07:52:46', 5),
(6, 3, 'PS-RETUR-2021-06-17-000002', '2021-06-17 00:00:00', 'BARANG', 'aktif', '2021-06-15 08:01:33', '2021-06-15 08:01:33', 5, 5, 2, 6, 2021, '2021-06-15 08:07:02', 5),
(7, 3, 'PS-RETUR-2021-06-17-000003', '2021-06-17 00:00:00', 'BARANG', 'nonaktif', '2021-06-15 08:01:40', '2021-06-15 08:07:08', 5, 5, 3, 6, 2021, NULL, NULL),
(8, 3, 'PS-RETUR-2021-06-17-000004', '2021-06-17 00:00:00', 'BARANG', 'aktif', '2021-06-15 08:06:13', '2021-06-15 08:06:13', 5, 5, 4, 6, 2021, '2021-06-15 08:07:05', 5),
(9, 3, 'PS-RETUR-2021-06-17-000005', '2021-06-17 00:00:00', 'BARANG', 'nonaktif', '2021-06-15 08:06:22', '2021-06-15 08:07:10', 5, 5, 5, 6, 2021, NULL, NULL),
(10, 5, 'PS-RETUR-2021-07-03-000001', '2021-07-03 00:00:00', 'BARANG', 'aktif', '2021-06-26 01:01:03', '2021-06-26 01:01:03', 5, 5, 1, 7, 2021, '2021-06-26 01:01:14', 5);

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
-- Dumping data for table `mstr_satuan`
--

INSERT INTO `mstr_satuan` (`id_pk_satuan`, `satuan_nama`, `satuan_rumus`, `satuan_status`, `satuan_create_date`, `satuan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'PCS', '1', 'AKTIF', '2020-06-22 08:36:13', '2020-06-22 08:36:13', 1, 1),
(2, 'BOX', '40', 'AKTIF', '2020-06-22 08:36:19', '2020-06-22 08:36:19', 1, 1),
(3, 'LUSIN', '12', 'AKTIF', '2020-06-22 08:36:23', '2020-06-22 08:36:23', 1, 1),
(4, 'BALL-48', '48', 'AKTIF', '2020-06-22 09:28:15', '2020-06-22 09:28:15', 1, 1),
(5, 'test3', '1', 'nonaktif', '2021-05-24 10:40:32', '2021-05-24 10:40:40', 2, 2),
(6, 'Kodi', '500', 'nonaktif', '2021-06-09 01:29:56', '2021-06-09 01:30:23', 2, 2),
(7, 'KODI', '500', 'AKTIF', '2021-06-09 01:30:28', '2021-06-09 01:30:28', 2, 2);

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
-- Dumping data for table `mstr_supplier`
--

INSERT INTO `mstr_supplier` (`id_pk_sup`, `sup_nama`, `sup_no_npwp`, `sup_foto_npwp`, `sup_foto_kartu_nama`, `sup_badan_usaha`, `sup_no_rekening`, `sup_suff`, `sup_perusahaan`, `sup_email`, `sup_telp`, `sup_hp`, `sup_alamat`, `sup_keterangan`, `sup_status`, `sup_create_date`, `sup_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'pica', '-a', 'Render3_final.png', 'noimage.jpg', 'CV', '-a', 'MRS', 'suppliera', 'email@email.coma', '12345a', '12345a', '-a', '-a', 'nonaktif', '2020-07-02 08:21:23', '2021-06-09 01:31:46', 1, 2),
(2, 'Joshua Natan', '-', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'Tn', 'Isupport', 'joshuanatan98@gmail.com', '123456', '09876567890', '-', '-', 'AKTIF', '2020-07-27 08:56:26', '2020-07-27 08:56:26', 2, 2),
(3, 'Jojo', '-', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'Tn', 'Nusantara Adivista', 'jojo@gmail.com', '123123', '123123', '-', '-', 'nonaktif', '2020-07-27 08:57:07', '2021-06-09 01:31:43', 2, 2),
(4, 'testa', '123123333', 'pexels-andre-furtado-3707171.jpg', 'pexels-artem-beliaikin-8531991.jpg', 'Toko', '33', 'Tn', 'testa', 'test@email.com33', '12343', '1233', 'asfdaf3333', '1131233', 'nonaktif', '2021-05-24 10:40:57', '2021-05-24 10:41:28', 2, 2),
(5, 'Calandra', 'dfssdf', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'MS', 'Mamoru', 'calandra.alencia@gmail.com', '081288983824', '081288983824', 'Taman Palem Lestari Blok B18 no 37, Cengkareng, Jakarta Barat', 'dfdsfs', 'AKTIF', '2021-06-09 01:31:16', '2021-06-09 01:31:37', 2, 2);

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
-- Dumping data for table `mstr_toko`
--

INSERT INTO `mstr_toko` (`id_pk_toko`, `toko_logo`, `toko_nama`, `toko_kop_surat`, `toko_nonpkp`, `toko_pernyataan_rek`, `toko_ttd`, `toko_kode`, `toko_status`, `toko_create_date`, `toko_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'majumandiri.jpg', 'Maju Mandiri', 'Render3.png', 'Render3_final1.png', 'Render4.png', 'Tembok.jpg', 'MM', 'AKTIF', '2020-07-02 09:36:12', '2021-06-09 01:02:07', 1, 3),
(2, 'mmsafety.jpg', 'MM Safety', 'Dating.png', 'Pendaftaran_SYNC_STUDY.png', 'Dating.png', 'granite_floor.jpg', 'MMS', 'AKTIF', '2020-07-05 11:57:18', '2021-06-09 01:02:26', 1, 3),
(3, 'logo_pusat_safety.png', 'Pusat Safety', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', 'PS', 'AKTIF', '2020-07-29 12:30:31', '2021-06-09 01:04:53', 2, 3),
(4, 'logomaju.jpg', 'Maju Abadi', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', 'render1.png', 'MA', 'AKTIF', '2020-07-29 12:32:49', '2021-06-09 01:02:52', 2, 3),
(5, 'indotama.jpg', 'Indotama Maju Mandiri', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', 'IND', 'AKTIF', '2021-05-24 10:41:55', '2021-06-09 01:03:28', 2, 3);

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
-- Dumping data for table `mstr_user`
--

INSERT INTO `mstr_user` (`id_pk_user`, `user_name`, `user_pass`, `user_email`, `user_status`, `id_fk_role`, `id_fk_employee`, `user_last_modified`, `user_create_date`, `id_create_date`, `id_last_modified`) VALUES
(2, 'puteri', 'e10adc3949ba59abbe56e057f20f883e', 'puteri@gmail.com', 'AKTIF', 1, 6, '2021-06-09 01:11:08', '2020-07-14 09:53:58', 1, 4),
(3, 'wivina', 'e10adc3949ba59abbe56e057f20f883e', 'daicy.choice@gmail.com', 'AKTIF', 1, 2, '2021-04-24 03:26:19', '2021-04-24 03:26:19', 2, 2),
(4, 'calandra', 'e10adc3949ba59abbe56e057f20f883e', 'calandra.alencia@gmail.com', 'AKTIF', 1, 4, '2021-06-09 02:12:51', '2021-06-09 12:58:18', 3, 5),
(5, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin@gmail.com', 'AKTIF', 1, 7, '2021-06-09 02:08:18', '2021-06-09 01:01:08', 3, 5),
(6, 'testusername', '4297f44b13955235245b2497399d7a93', 'testusername@email.com', 'AKTIF', 9, 8, '2021-06-09 08:59:23', '2021-06-09 08:58:29', 5, 6);

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
  `id_fk_cabang` int(11) NOT NULL,
  `warehouse_status` varchar(15) DEFAULT NULL,
  `warehouse_create_date` datetime DEFAULT NULL,
  `warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_warehouse`
--

INSERT INTO `mstr_warehouse` (`id_pk_warehouse`, `warehouse_nama`, `warehouse_alamat`, `warehouse_notelp`, `warehouse_desc`, `id_fk_cabang`, `warehouse_status`, `warehouse_create_date`, `warehouse_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'GUDANG', 'Puri Indah', '12345', '-', 1, 'nonaktif', '2020-06-21 11:45:42', '2021-04-12 11:27:11', 1, 2),
(2, 'GUDANG 23', 'Jalan asdasd Green Lake City3', '02161945673', 'deskripsi si si sis3', 0, 'AKTIF', '2020-06-22 01:24:49', '2020-07-29 09:31:15', 1, 2),
(3, 'aabbbb', 'aabbbbbbb', 'aabbbbbbb', 'aabbbbbbb', 1, 'nonaktif', '2021-04-12 11:34:17', '2021-06-09 01:33:34', 2, 2),
(4, 'aabb', 'aabb', 'aabb', 'aabb', 2, 'nonaktif', '2021-04-12 11:34:20', '2021-04-25 04:39:21', 2, 2),
(5, 'erwer', '34234', '234234', '24342', 3, 'AKTIF', '2021-04-12 11:39:35', '2021-06-16 11:59:34', 2, 5),
(6, 'aaaa', '234', '3', '234', 1, 'nonaktif', '2021-04-12 11:41:26', '2021-04-25 10:05:25', 2, 2),
(7, 'AAAA', 'aa', 'aa', 'aa', -1, 'nonaktif', '2021-04-12 11:42:30', '2021-06-09 02:03:23', 2, 2),
(8, 'aawewerwerwer', 'aawerwerwer', 'aawerwerwerwerwer', 'aawerwerw', -1, 'nonaktif', '2021-04-12 11:42:42', '2021-06-09 01:33:37', 2, 2),
(9, 'sadsd', 'asdasd', 'asdasd', 'qewqwe', -1, 'AKTIF', '2021-04-12 11:43:31', '2021-04-12 11:43:31', 2, 2),
(10, 'qwerty', 'qwerty', '09822', 'qwedwed', -1, 'AKTIF', '2021-04-25 03:49:43', '2021-04-25 03:49:43', 2, 2),
(11, 'asdzxcaa', 'qweasdaa', '123450987654aa', 'asdasdasdasaa', 1, 'AKTIF', '2021-04-25 04:13:17', '2021-06-16 11:59:31', 2, 5),
(12, 'asdadsc', 'sadvsrf', '56756', 'jhkjk', 2, 'nonaktif', '2021-04-25 04:22:09', '2021-06-09 01:33:39', 2, 2),
(13, 'test', 'test', '123', 'test', 1, 'AKTIF', '2021-06-09 10:31:06', '2021-06-09 10:31:06', 5, 5),
(14, 'AAAA', 'aa', 'w', 's', 3, 'AKTIF', '2021-06-10 01:50:18', '2021-06-16 11:58:58', 5, 5);

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
-- Dumping data for table `tbl_barang_kombinasi`
--

INSERT INTO `tbl_barang_kombinasi` (`id_pk_barang_kombinasi`, `id_barang_utama`, `id_barang_kombinasi`, `barang_kombinasi_qty`, `barang_kombinasi_status`, `barang_kombinasi_create_date`, `barang_kombinasi_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:27', 1, 1),
(2, 5, 4, 3, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:27', 1, 1),
(3, 6, 1, 2, 'aktif', '2020-06-22 17:14:55', '2020-06-22 17:15:39', 1, 1),
(4, 6, 2, 2, 'aktif', '2020-06-22 17:14:55', '2020-07-06 10:07:36', 1, 1),
(5, 7, 1, 3, 'nonaktif', '2020-06-22 21:17:46', '2020-07-03 18:21:11', 1, 1),
(6, 7, 2, 2, 'aktif', '2020-06-22 21:17:46', '2020-07-06 10:08:54', 1, 1),
(7, 6, 1, 1, 'aktif', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1),
(8, 6, 2, 1, 'nonaktif', '2020-06-29 12:17:37', '2020-07-04 10:27:50', 1, 1),
(9, 7, 3, 3, 'aktif', '2020-06-29 12:17:53', '2020-07-06 10:08:54', 1, 1),
(10, 7, 4, 4, 'aktif', '2020-06-29 12:17:53', '2020-07-06 10:08:54', 1, 1),
(11, 7, 5, 5, 'nonaktif', '2020-06-29 12:17:53', '2020-07-06 17:25:26', 1, 1),
(12, 6, 4, 4, 'aktif', '2020-07-04 10:28:29', '2020-07-06 10:07:36', 1, 1),
(13, 6, 3, 3, 'aktif', '2020-07-04 10:28:29', '2020-07-06 10:07:36', 1, 1),
(14, 17, 9, 123, 'aktif', '2020-07-23 15:16:24', '2020-07-23 15:16:24', 2, 2),
(15, 17, 7, 112, 'aktif', '2020-07-23 15:16:24', '2020-07-23 15:16:24', 2, 2),
(16, 18, 6, 123, 'aktif', '2020-07-23 15:18:05', '2020-07-23 15:18:05', 2, 2),
(17, 18, 14, 123, 'aktif', '2020-07-23 15:18:05', '2020-07-23 15:18:05', 2, 2),
(18, 23, 5, 123, 'nonaktif', '2020-07-23 15:28:24', '2020-07-25 19:35:53', 2, 2),
(19, 23, 8, 111, 'nonaktif', '2020-07-23 15:28:24', '2020-07-25 19:35:54', 2, 2),
(20, 23, 9, 4343, 'nonaktif', '2020-07-23 15:28:24', '2020-07-25 19:35:55', 2, 2),
(21, 24, 5, 12, 'aktif', '2020-07-23 16:50:11', '2020-07-23 16:50:11', 2, 2),
(22, 24, 9, 155, 'aktif', '2020-07-23 16:50:11', '2020-07-23 16:50:11', 2, 2),
(23, 25, 2, 100, 'aktif', '2020-07-25 14:57:01', '2020-07-25 14:57:01', 2, 2),
(24, 25, 4, 100, 'aktif', '2020-07-25 14:57:01', '2020-07-25 14:57:01', 2, 2),
(25, 23, 1, 10, 'aktif', '2020-07-25 21:07:59', '2020-07-25 21:07:59', 2, 2),
(26, 23, 24, 50, 'aktif', '2020-07-25 21:07:59', '2020-07-26 21:45:10', 2, 2),
(27, 23, 13, 10, 'aktif', '2020-07-25 21:07:59', '2020-07-26 21:45:10', 2, 2),
(28, 23, 17, 10, 'nonaktif', '2020-07-25 21:07:59', '2020-07-25 22:37:46', 2, 2),
(29, 20, 1, 10, 'nonaktif', '2020-07-25 21:08:40', '2021-06-25 21:17:19', 2, 5),
(30, 20, 1, 10, 'nonaktif', '2020-07-25 21:09:46', '2021-06-25 21:17:18', 2, 5),
(31, 20, 1, 10, 'nonaktif', '2020-07-25 21:10:20', '2021-06-25 21:17:18', 2, 5),
(32, 20, 1, 10, 'nonaktif', '2020-07-25 21:10:41', '2021-06-25 21:17:18', 2, 5),
(33, 23, 15, 10, 'aktif', '2020-07-25 21:15:45', '2020-07-26 21:45:10', 2, 2),
(34, 20, 5, 10, 'nonaktif', '2020-07-25 21:17:12', '2021-06-25 21:17:17', 2, 5),
(35, 20, 4, 10, 'nonaktif', '2020-07-25 21:17:12', '2021-06-25 21:17:17', 2, 5),
(36, 20, 22, 10, 'nonaktif', '2020-07-25 21:17:12', '2021-06-25 21:17:16', 2, 5),
(50, 176, 13, 1, 'aktif', '2020-07-31 16:43:17', '2020-07-31 16:43:17', 2, 2),
(49, 175, 13, 1, 'aktif', '2020-07-31 13:18:32', '2020-07-31 13:18:32', 2, 2),
(48, 14, 13, 30, 'aktif', '2020-07-25 21:32:25', '2020-07-25 21:32:25', 2, 2),
(45, 14, 8, 10, 'aktif', '2020-07-25 21:31:48', '2020-07-25 21:32:25', 2, 2),
(47, 14, 24, 30, 'aktif', '2020-07-25 21:32:25', '2020-07-25 21:32:25', 2, 2),
(51, 176, 17, 1, 'aktif', '2020-07-31 16:43:17', '2020-07-31 16:43:17', 2, 2),
(52, 20, 0, 0, 'aktif', '2021-06-25 21:15:12', '2021-06-25 21:15:12', 5, 5);

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
-- Dumping data for table `tbl_brg_cabang`
--

INSERT INTO `tbl_brg_cabang` (`id_pk_brg_cabang`, `brg_cabang_qty`, `brg_cabang_notes`, `brg_cabang_status`, `brg_cabang_last_price`, `id_fk_brg`, `id_fk_cabang`, `brg_cabang_create_date`, `brg_cabang_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 300, '-', 'AKTIF', 5000, 1, 1, '2020-07-26 09:43:43', '2021-06-09 10:20:13', 2, 5),
(2, 300, 'Auto insert from checking construct', 'aktif', 1300, 3, 1, '2020-07-26 09:43:43', '2020-07-27 08:58:14', 2, 2),
(3, 300, 'Auto insert from checking construct', 'aktif', 3400, 13, 2, '2020-07-26 09:43:43', '2020-07-27 08:58:14', 2, 2),
(4, 300, 'Auto insert from checking construct', 'aktif', 0, 15, 2, '2020-07-26 09:43:43', '2020-07-26 09:43:43', 2, 2),
(5, 300, '-', 'AKTIF', 15000, 20, 2, '2020-07-26 09:43:43', '2020-07-27 08:51:26', 2, 2),
(6, 300, 'Auto insert from checking construct', 'aktif', 0, 5, 2, '2020-07-26 09:43:43', '2020-07-26 09:43:43', 2, 2),
(7, 300, 'Auto insert from checking construct', 'aktif', 20000, 4, 2, '2020-07-26 09:43:43', '2020-07-27 08:51:26', 2, 2),
(8, 300, 'Auto insert from checking construct', 'aktif', 0, 22, 2, '2020-07-26 09:43:43', '2020-07-26 09:43:43', 2, 2),
(12, 3300, '-', 'AKTIF', 0, 23, 1, '2020-07-28 12:33:53', '2020-07-28 12:33:53', 2, 2),
(9, 300, '-', 'AKTIF', 10000, 14, 2, '2020-07-26 09:43:43', '2020-07-27 08:58:14', 2, 2),
(10, 300, 'Auto insert from checking construct', 'nonaktif', 0, 8, 2, '2020-07-26 09:43:43', '2020-07-26 09:57:39', 2, 2),
(11, 300, 'Auto insert from checking construct', 'aktif', 0, 8, 2, '2020-07-26 09:57:39', '2020-07-26 09:57:39', 2, 2),
(13, 3300, 'Auto insert from checking construct', 'aktif', 0, 24, 1, '2020-07-28 12:33:53', '2020-07-28 12:33:53', 2, 2),
(14, 201, 'Auto insert from checking construct', 'aktif', 0, 13, 1, '2020-07-28 12:33:53', '2020-07-28 12:33:53', 2, 2),
(15, 201, 'Auto insert from checking construct', 'aktif', 0, 15, 1, '2020-07-28 12:33:53', '2020-07-28 12:33:53', 2, 2),
(16, 300, '-', 'AKTIF', 15000, 20, 1, '2020-07-28 12:33:53', '2021-06-09 10:20:13', 2, 5),
(17, 300, 'Auto insert from checking construct', 'aktif', 0, 5, 1, '2020-07-28 12:33:53', '2020-07-28 12:33:53', 2, 2),
(18, 300, 'Auto insert from checking construct', 'aktif', 23000, 4, 1, '2020-07-28 12:33:53', '2020-07-29 08:47:06', 2, 2),
(19, 290, 'Auto insert from checking construct', 'aktif', 0, 22, 1, '2020-07-28 12:33:53', '2020-07-28 12:33:53', 2, 2),
(20, 300, '-', 'AKTIF', 0, 127, 2, '2020-07-29 07:38:43', '2020-07-29 07:38:43', 2, 2),
(21, 168, '-', 'AKTIF', 0, 9, 4, '2020-07-30 05:23:03', '2020-07-30 05:23:03', 2, 2),
(22, 16, '-', 'AKTIF', 123123, 10, 4, '2020-07-30 05:23:03', '2021-05-29 12:50:54', 2, 2),
(23, 300, '-', 'AKTIF', 0, 11, 4, '2020-07-30 05:23:03', '2020-07-30 05:23:03', 2, 2),
(24, 42, '-', 'AKTIF', 0, 14, 4, '2020-07-30 05:23:03', '2020-07-30 05:23:03', 2, 2),
(25, 243, '-', 'AKTIF', 0, 66, 4, '2021-05-09 07:39:56', '2021-05-09 07:39:56', 2, 2),
(26, 5400, '-', 'AKTIF', 2000, 23, 3, '2021-06-16 09:41:00', '2021-06-17 12:02:52', 5, 5),
(27, 2500, '-', 'AKTIF', 1000, 24, 3, '2021-06-16 09:41:19', '2021-06-17 12:02:52', 5, 5),
(28, 400, '-', 'AKTIF', 0, 23, 5, '2021-06-16 09:49:17', '2021-06-16 09:49:17', 5, 5),
(29, 2300, '-', 'AKTIF', 0, 24, 5, '2021-06-16 09:49:17', '2021-06-16 09:49:17', 5, 5),
(30, 10000, '-', 'AKTIF', 0, 12, 5, '2021-06-20 05:18:08', '2021-06-20 05:18:08', 5, 5);

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
-- Dumping data for table `tbl_brg_pembelian`
--

INSERT INTO `tbl_brg_pembelian` (`id_pk_brg_pembelian`, `brg_pem_qty`, `brg_pem_satuan`, `brg_pem_harga`, `brg_pem_note`, `brg_pem_status`, `id_fk_pembelian`, `id_fk_barang`, `brg_pem_create_date`, `brg_pem_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'pcs', 1000, '-', 'AKTIF', 4, 6, '2020-07-17 10:40:25', '2020-07-17 10:40:25', 2, 2),
(2, 200, 'Pcs', 20000, '-', 'AKTIF', 23, 3, '2020-07-25 12:43:41', '2020-07-25 01:23:53', 2, 2),
(3, 1200, 'Pcs', 5000, '-', 'AKTIF', 23, 4, '2020-07-25 12:44:13', '2020-07-25 01:23:53', 2, 2),
(4, 1400, 'Pcs', 2300, '-', 'AKTIF', 23, 5, '2020-07-25 01:23:53', '2020-07-25 01:23:53', 2, 2),
(5, 2000, 'Pcs', 16000, '-', 'AKTIF', 24, 20, '2020-07-27 08:51:25', '2020-07-27 08:53:01', 2, 2),
(6, 1000, 'Pcs', 17000, '-', 'nonaktif', NULL, 4, '2020-07-27 08:51:25', '2020-07-27 09:47:29', 2, 2),
(7, 300, 'Pcs', 18000, '-', 'AKTIF', 24, 23, '2020-07-27 08:51:25', '2020-07-27 08:53:01', 2, 2),
(8, 1200, 'Pcs', 10000, '-', 'AKTIF', 25, 14, '2020-07-27 08:58:14', '2020-07-27 08:58:14', 2, 2),
(9, 1300, 'Pcs', 5000, '-', 'AKTIF', 25, 23, '2020-07-27 08:58:14', '2020-07-27 08:58:14', 2, 2),
(10, 600, 'Pcs', 3400, '-', 'AKTIF', 25, 13, '2020-07-27 08:58:14', '2020-07-27 08:58:14', 2, 2),
(11, 5000, 'Box', 1300, '-', 'AKTIF', 25, 24, '2020-07-27 08:58:14', '2020-07-27 08:58:14', 2, 2),
(12, 1000, 'Pcs', 23000, '-', 'AKTIF', 26, 2, '2020-07-29 08:47:06', '2021-06-09 01:53:43', 2, 2),
(13, 50, 'Pcs', 15000, '-', 'AKTIF', 26, 20, '2020-07-29 08:47:06', '2021-06-09 01:53:43', 2, 2),
(14, 111111, 'Pcs', 111111, '1111', 'AKTIF', 27, 9, '2021-05-29 12:49:53', '2021-05-29 12:50:33', 2, 2),
(15, 123123, 'Pcs', 123123, '123132', 'AKTIF', 28, 10, '2021-05-29 12:50:54', '2021-05-29 12:50:54', 2, 2),
(16, 10000, 'wkwk', 5000, '-', 'AKTIF', 29, 1, '2021-06-09 10:20:05', '2021-06-10 08:24:20', 5, 5),
(17, 20000, 'hehe', 15000, '-', 'AKTIF', 29, 20, '2021-06-09 10:20:05', '2021-06-10 08:24:20', 5, 5),
(18, 1000, 'Pcs', 5000, '-', 'AKTIF', 30, 1, '2021-06-09 10:20:13', '2021-06-10 01:52:42', 5, 5),
(19, 2000, 'Pcs', 15000, '-', 'AKTIF', 30, 20, '2021-06-09 10:20:13', '2021-06-10 01:52:42', 5, 5),
(20, 100, 'wkwk', 2000, '-', 'AKTIF', 29, 22, '2021-06-10 08:22:14', '2021-06-10 08:24:20', 5, 5),
(21, 100, 'wkwk', 2000, '-', 'nonaktif', NULL, 22, '2021-06-10 08:22:19', '2021-06-10 08:22:36', 5, 5),
(22, 1, 'jeen', 1000, '-', 'AKTIF', 29, 2, '2021-06-10 08:24:20', '2021-06-10 08:24:20', 5, 5),
(23, 1000, 'Pcs', 1000, '-', 'AKTIF', 32, 24, '2021-06-17 12:02:52', '2021-06-17 12:02:52', 5, 5),
(24, 2000, 'Pcs', 2000, '-', 'AKTIF', 32, 23, '2021-06-17 12:02:52', '2021-06-17 12:02:52', 5, 5);

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
-- Dumping data for table `tbl_brg_pemenuhan`
--

INSERT INTO `tbl_brg_pemenuhan` (`id_pk_brg_pemenuhan`, `brg_pemenuhan_qty`, `brg_pemenuhan_tipe`, `brg_pemenuhan_status`, `id_fk_brg_permintaan`, `id_fk_cabang`, `id_fk_warehouse`, `brg_pemenuhan_create_date`, `brg_pemenuhan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1000, 'CABANG', 'Diterima', 1, 1, 0, '2020-07-28 07:54:11', '2020-07-28 03:44:21', 2, 2),
(2, 2000, 'CABANG', 'Diterima', 1, 1, 0, '2020-07-28 07:55:37', '2020-07-28 03:41:08', 2, 2),
(3, 200, 'CABANG', 'Diterima', 2, 1, 0, '2020-07-28 07:55:45', '2021-05-29 05:26:32', 2, 2),
(4, 2000, 'CABANG', 'nonaktif', 2, 1, 0, '2020-07-28 07:56:47', '2020-07-28 12:56:50', 2, 2),
(5, 200, 'CABANG', 'Diterima', 2, 1, 0, '2020-07-28 07:56:56', '2021-05-29 05:26:34', 2, 2),
(6, 30, 'CABANG', 'AKTIF', 3, 1, 0, '2020-07-28 10:49:38', '2020-07-28 10:49:38', 2, 2),
(7, 10000, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:51:29', '2020-07-29 09:51:57', 2, 2),
(8, 100, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:52:08', '2020-07-29 09:55:24', 2, 2),
(9, 200, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:52:49', '2020-07-29 09:52:52', 2, 2),
(10, 200, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:55:20', '2020-07-29 09:55:54', 2, 2),
(11, 100, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:55:37', '2020-07-29 09:55:42', 2, 2),
(12, 100, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:55:51', '2020-07-29 09:56:30', 2, 2),
(13, 200, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:56:27', '2020-07-29 09:56:42', 2, 2),
(14, 300, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:56:39', '2020-07-29 09:57:30', 2, 2),
(15, 300, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:57:27', '2020-07-29 10:08:34', 2, 2),
(16, 500, 'CABANG', 'nonaktif', 2, 1, 0, '2020-07-29 04:58:33', '2020-07-29 09:58:37', 2, 2),
(17, 400, 'WAREHOUSE', 'nonaktif', 2, 0, 2, '2020-07-29 04:59:42', '2020-07-29 09:59:44', 2, 2),
(18, 30, 'WAREHOUSE', 'Diterima', 2, 0, 2, '2020-07-29 05:08:38', '2020-07-29 10:26:31', 2, 2),
(19, 200, 'CABANG', 'nonaktif', 18, 3, 0, '2021-06-16 04:47:36', '2021-06-16 09:47:58', 5, 5),
(20, 400, 'CABANG', 'nonaktif', 20, 3, 0, '2021-06-16 04:47:46', '2021-06-16 09:48:01', 5, 5),
(21, 500, 'CABANG', 'Diterima', 20, 3, 0, '2021-06-16 04:47:51', '2021-06-16 10:32:42', 5, 5),
(22, 600, 'CABANG', 'Diterima', 18, 3, 0, '2021-06-16 04:47:55', '2021-06-16 10:49:59', 5, 5),
(23, 600, 'CABANG', 'nonaktif', 18, 5, 0, '2021-06-16 04:49:34', '2021-06-20 11:10:40', 5, 5),
(24, 700, 'CABANG', 'Diterima', 20, 5, 0, '2021-06-16 04:49:41', '2021-06-16 10:50:03', 5, 5),
(25, 200, 'WAREHOUSE', 'Diterima', 18, 0, 5, '2021-06-16 07:07:41', '2021-06-17 12:18:27', 5, 5),
(26, 300, 'WAREHOUSE', 'Diterima', 20, 0, 5, '2021-06-16 07:07:44', '2021-06-17 12:18:31', 5, 5),
(27, 1000, 'CABANG', 'Perjalanan', 18, 5, 0, '2021-06-20 06:10:38', '2021-06-26 10:49:03', 5, 5),
(28, 10, 'CABANG', 'Aktif', 21, 1, 0, '2021-06-26 06:06:08', '2021-06-26 01:05:12', 5, 5),
(29, 20, 'CABANG', 'Aktif', 22, 1, 0, '2021-06-26 06:06:12', '2021-06-26 01:05:18', 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_penawaran`
--

CREATE TABLE `tbl_brg_penawaran` (
  `id_pk_brg_penawaran` int(11) NOT NULL,
  `id_fk_brg` int(11) NOT NULL,
  `brg_penawaran_qty` int(11) DEFAULT NULL,
  `brg_penawaran_satuan` varchar(40) DEFAULT NULL,
  `brg_penawaran_price` int(11) DEFAULT NULL,
  `brg_penawaran_notes` varchar(400) DEFAULT NULL,
  `brg_penawaran_status` varchar(40) DEFAULT NULL,
  `id_fk_penawaran` int(11) DEFAULT NULL,
  `brg_penawaran_id_create` int(11) DEFAULT NULL,
  `brg_penawaran_id_update` int(11) DEFAULT NULL,
  `brg_penawaran_id_delete` int(11) DEFAULT NULL,
  `brg_penawaran_tgl_create` datetime DEFAULT NULL,
  `brg_penawaran_tgl_update` datetime DEFAULT NULL,
  `brg_penawaran_tgl_delete` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_brg_penawaran`
--

INSERT INTO `tbl_brg_penawaran` (`id_pk_brg_penawaran`, `id_fk_brg`, `brg_penawaran_qty`, `brg_penawaran_satuan`, `brg_penawaran_price`, `brg_penawaran_notes`, `brg_penawaran_status`, `id_fk_penawaran`, `brg_penawaran_id_create`, `brg_penawaran_id_update`, `brg_penawaran_id_delete`, `brg_penawaran_tgl_create`, `brg_penawaran_tgl_update`, `brg_penawaran_tgl_delete`) VALUES
(1, 11, 123, 'pcs', 123123, '123', 'aktif', 3, 2, NULL, NULL, '2021-05-29 12:12:44', NULL, NULL),
(2, 10, 1234, 'pcs', 1231234, '1234', 'aktif', 4, 2, 2, NULL, '2021-05-29 12:15:42', '2021-05-29 12:16:02', NULL),
(3, 14, 1233, 'Pcs', 1233, '1233', 'aktif', 5, 2, 2, NULL, '2021-05-29 12:18:14', '2021-05-29 12:19:28', NULL),
(4, 2, 5, 'Pcs', 250000, '', 'aktif', 6, 2, NULL, NULL, '2021-06-09 01:41:47', NULL, NULL),
(5, 2, 5, 'Pcs', 250000, '', 'aktif', 7, 2, NULL, NULL, '2021-06-09 01:41:55', NULL, NULL),
(6, 2, 5, 'Pcs', 250000, '', 'aktif', 8, 2, NULL, NULL, '2021-06-09 01:41:56', NULL, NULL),
(7, 2, 1000, 'Pcs', 1000, '-', 'aktif', 9, 5, NULL, NULL, '2021-06-09 21:44:43', NULL, NULL),
(8, 2, 1000, 'Pcs', 1000, '-', 'aktif', 10, 5, NULL, NULL, '2021-06-09 21:44:48', NULL, NULL),
(9, 12, 1000, 'Pcs', 1000, '-', 'aktif', 11, 4, 4, NULL, '2021-10-16 07:33:01', '2021-10-16 08:24:26', NULL),
(10, 12, 100, 'Pcs', 2000, '-', 'aktif', 12, 4, NULL, NULL, '2021-10-16 07:53:53', NULL, NULL),
(11, 24, 340, 'Pcs', 4300, '-', 'aktif', 12, 4, NULL, NULL, '2021-10-16 07:53:53', NULL, NULL),
(12, 24, 2000, 'Pcs', 2000, '-', 'aktif', 11, 4, NULL, NULL, '2021-10-16 08:24:26', NULL, NULL);

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
-- Dumping data for table `tbl_brg_penerimaan`
--

INSERT INTO `tbl_brg_penerimaan` (`id_pk_brg_penerimaan`, `brg_penerimaan_qty`, `brg_penerimaan_note`, `id_fk_penerimaan`, `id_fk_brg_pembelian`, `id_fk_brg_retur`, `id_fk_brg_pengiriman`, `id_fk_satuan`, `brg_penerimaan_create_date`, `brg_penerimaan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(6, 0, '-', 9, 0, 0, 5, 1, '2020-07-01 12:17:51', '2020-07-01 12:28:21', 1, 1),
(7, 0, '-', 10, 0, 0, 3, 1, '2020-07-01 12:18:24', '2020-07-01 12:28:17', 1, 1),
(8, 0, '-', 11, 0, 0, 3, 1, '2020-07-01 12:29:02', '2020-07-01 12:29:10', 1, 1),
(9, 0, '-', 12, 0, 0, 5, 1, '2020-07-01 12:29:20', '2020-07-01 12:30:28', 1, 1),
(10, 0, '-', 13, 0, 0, 3, 1, '2020-07-01 12:29:22', '2020-07-01 12:29:30', 1, 1),
(11, 0, '-', 14, 0, 0, 3, 1, '2020-07-01 12:31:57', '2020-07-01 12:32:03', 1, 1),
(12, 0, '-', 15, 0, 0, 5, 1, '2020-07-01 12:33:04', '2020-07-01 12:33:11', 1, 1),
(13, 0, '-', 16, 1, 0, 0, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:21', 1, 1),
(14, 0, '-', 16, 2, 0, 0, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:21', 1, 1),
(15, 100, '-', 17, 1, 0, 0, 1, '2020-07-04 09:52:12', '2020-07-04 09:52:12', 1, 1),
(16, 100, '-', 17, 2, 0, 0, 1, '2020-07-04 09:52:12', '2020-07-04 09:52:12', 1, 1),
(17, 100, '-', 18, 1, 0, 0, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1),
(18, 100, '-', 18, 2, 0, 0, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1),
(19, 0, '-', 19, 8, 0, 0, 1, '2020-07-07 10:35:22', '2020-07-07 10:35:52', 1, 1),
(20, 0, '-', 19, 9, 0, 0, 1, '2020-07-07 10:35:22', '2020-07-07 10:35:52', 1, 1),
(21, 10, '-', 20, 0, 0, 3, 1, '2020-07-22 02:28:54', '2020-07-22 02:28:54', 2, 2),
(22, 10, '-', 21, 0, 0, 5, 1, '2020-07-22 02:31:31', '2020-07-22 02:31:31', 2, 2),
(23, 123123123, '-', 22, 1, 0, 0, 2, '2020-07-24 07:38:20', '2020-07-24 07:40:09', 2, 2),
(24, 0, '', 23, 0, 1, 0, 1, '2020-07-24 07:40:36', '2020-07-27 11:24:15', 2, 2),
(25, 0, '', 23, 0, 2, 0, 1, '2020-07-24 07:40:36', '2020-07-27 11:24:15', 2, 2),
(26, 0, '', 23, 0, 3, 0, 1, '2020-07-24 07:40:36', '2020-07-27 11:24:15', 2, 2),
(28, 0, '-', 25, 5, 0, 0, 1, '2020-07-27 10:33:05', '2020-07-27 10:33:15', 2, 2),
(29, 0, '-', 25, 7, 0, 0, 1, '2020-07-27 10:33:05', '2020-07-27 10:33:15', 2, 2),
(30, 0, 'asdfasdf', 26, 5, 0, 0, 3, '2020-07-27 10:34:29', '2021-05-29 04:56:16', 2, 2),
(31, 0, 'asdfasdf', 26, 7, 0, 0, 1, '2020-07-27 10:34:29', '2021-05-29 04:56:16', 2, 2),
(32, 100, '-', 27, 8, 0, 0, 1, '2020-07-27 10:37:34', '2020-07-27 10:41:21', 2, 2),
(33, 100, '-', 27, 9, 0, 0, 1, '2020-07-27 10:37:34', '2020-07-27 10:41:21', 2, 2),
(34, 100, '-', 27, 10, 0, 0, 1, '2020-07-27 10:37:34', '2020-07-27 10:41:21', 2, 2),
(35, 100, '-', 27, 11, 0, 0, 1, '2020-07-27 10:37:34', '2020-07-27 10:41:21', 2, 2),
(36, 0, 'asdfasdf', 28, 0, 4, 0, 1, '2020-07-27 11:23:56', '2021-05-29 04:54:44', 2, 2),
(37, 0, 'asdfasdfas', 28, 0, 5, 0, 1, '2020-07-27 11:23:56', '2021-05-29 04:54:44', 2, 2),
(38, 5, '-', 29, 0, 4, 0, 3, '2020-07-27 11:35:52', '2020-07-27 11:35:52', 2, 2),
(39, 50, '-', 29, 0, 5, 0, 1, '2020-07-27 11:35:52', '2020-07-27 11:35:52', 2, 2),
(40, 0, '-', 30, 0, 0, 32, 1, '2020-07-28 02:28:54', '2020-07-28 02:29:16', 2, 2),
(41, 0, '-', 31, 0, 0, 32, 1, '2020-07-28 02:29:45', '2020-07-28 02:29:58', 2, 2),
(42, 0, '-', 32, 0, 0, 31, 1, '2020-07-28 02:30:49', '2020-07-28 03:38:51', 2, 2),
(43, 0, '-', 33, 0, 0, 0, 1, '2020-07-28 03:34:26', '2020-07-28 03:34:26', 2, 2),
(44, 0, '-', 34, 0, 0, 0, 1, '2020-07-28 03:34:35', '2020-07-28 03:34:35', 2, 2),
(45, 2000, '-', 35, 0, 0, 32, 1, '2020-07-28 03:41:07', '2020-07-28 03:41:07', 2, 2),
(46, 0, '-', 36, 0, 0, 31, 1, '2020-07-28 03:42:09', '2021-05-29 05:25:25', 2, 2),
(47, 1000, '-', 37, 0, 0, 35, 1, '2020-07-28 03:44:21', '2020-07-28 03:44:21', 2, 2),
(48, 0, '-', 38, 0, 0, 33, 1, '2020-07-28 03:44:42', '2021-05-29 05:25:31', 2, 2),
(49, 5000, '-', 39, 12, 0, 0, 1, '2020-07-29 08:57:26', '2020-07-29 09:30:13', 2, 2),
(50, 500, '-', 39, 13, 0, 0, 1, '2020-07-29 08:57:26', '2020-07-29 09:30:13', 2, 2),
(51, 30, '-', 40, 0, 0, 37, 1, '2020-07-29 10:26:31', '2020-07-29 10:26:31', 2, 2),
(52, 0, '-', 41, 0, 0, 31, 1, '2021-05-29 05:25:28', '2021-05-29 05:26:21', 2, 2),
(53, 200, '-', 42, 0, 0, 31, 1, '2021-05-29 05:26:31', '2021-05-29 05:26:31', 2, 2),
(54, 200, '-', 43, 0, 0, 33, 1, '2021-05-29 05:26:34', '2021-05-29 05:26:34', 2, 2),
(55, 200, '-', 44, 16, 0, 0, 3, '2021-06-09 10:22:59', '2021-06-09 10:23:16', 5, 5),
(56, 200, '-', 44, 17, 0, 0, 3, '2021-06-09 10:22:59', '2021-06-09 10:23:16', 5, 5),
(57, 1000, '', 45, 16, 0, 0, 1, '2021-06-10 01:54:25', '2021-06-10 01:54:25', 5, 5),
(58, 2000, '', 45, 17, 0, 0, 1, '2021-06-10 01:54:25', '2021-06-10 01:54:25', 5, 5),
(59, 0, '-', 46, 0, 9, 0, 1, '2021-06-15 07:53:06', '2021-06-15 07:53:32', 5, 5),
(60, 2, '-', 47, 0, 9, 0, 1, '2021-06-15 07:53:43', '2021-06-15 07:53:43', 5, 5),
(61, 500, '-', 48, 0, 0, 110, 1, '2021-06-16 10:32:42', '2021-06-16 10:32:42', 5, 5),
(62, 600, '-', 49, 0, 0, 113, 1, '2021-06-16 10:49:59', '2021-06-16 10:49:59', 5, 5),
(63, 700, '-', 50, 0, 0, 112, 1, '2021-06-16 10:50:02', '2021-06-16 10:50:02', 5, 5),
(64, 10, '-', 51, 16, 0, 0, 1, '2021-06-17 12:01:48', '2021-06-17 12:01:48', 5, 5),
(65, 10, '-', 51, 17, 0, 0, 1, '2021-06-17 12:01:48', '2021-06-17 12:01:48', 5, 5),
(66, 10, '-', 51, 20, 0, 0, 1, '2021-06-17 12:01:48', '2021-06-17 12:01:48', 5, 5),
(67, 10, '-', 51, 22, 0, 0, 1, '2021-06-17 12:01:48', '2021-06-17 12:01:48', 5, 5),
(68, 10, '-', 52, 23, 0, 0, 1, '2021-06-17 12:03:34', '2021-06-17 12:03:34', 5, 5),
(69, 10, '-1', 52, 24, 0, 0, 1, '2021-06-17 12:03:34', '2021-06-17 12:03:34', 5, 5),
(70, 200, '-', 53, 0, 0, 114, 1, '2021-06-17 12:18:27', '2021-06-17 12:18:27', 5, 5),
(71, 300, '-', 54, 0, 0, 115, 1, '2021-06-17 12:18:31', '2021-06-17 12:18:31', 5, 5),
(72, 600, '-', 55, 0, 0, 111, 1, '2021-06-17 12:18:33', '2021-06-17 12:18:33', 5, 5);

--
-- Triggers `tbl_brg_penerimaan`
--
DELIMITER $$
CREATE TRIGGER `trg_update_brg_cabang_after_update_brg_penerimaan` AFTER UPDATE ON `tbl_brg_penerimaan` FOR EACH ROW begin
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
-- Dumping data for table `tbl_brg_pengiriman`
--

INSERT INTO `tbl_brg_pengiriman` (`id_pk_brg_pengiriman`, `brg_pengiriman_qty`, `brg_pengiriman_note`, `id_fk_pengiriman`, `id_fk_brg_penjualan`, `id_fk_brg_retur_kembali`, `id_fk_brg_pemenuhan`, `id_fk_satuan`, `brg_pengiriman_create_date`, `brg_pengiriman_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, '-', 1, 1, 0, 0, 1, '2021-06-05 08:22:16', '2021-06-05 08:22:21', 2, 2),
(2, 1, '-', 1, 2, 0, 0, 1, '2021-06-05 08:22:16', '2021-06-05 08:22:21', 2, 2),
(3, 400, '-', 2, 3, 0, 0, 1, '2021-06-05 08:26:44', '2021-06-05 08:27:17', 2, 2),
(4, 400, '-', 3, 3, 0, 0, 1, '2021-06-05 08:27:31', '2021-06-05 08:27:31', 2, 2),
(5, 1, '-', 4, 1, 0, 0, 1, '2021-06-05 08:44:56', '2021-06-05 08:44:56', 2, 2),
(6, 100, '-', 5, 1, 0, 0, 1, '2021-06-05 08:56:29', '2021-06-05 11:11:29', 2, 2),
(7, 0, '1', 5, 2, 0, 0, 4, '2021-06-05 08:56:29', '2021-06-05 11:11:29', 2, 2),
(8, 0, '-', 6, 1, 0, 0, 4, '2021-06-05 11:11:55', '2021-06-05 11:11:55', 2, 2),
(9, 0, '-', 6, 2, 0, 0, 4, '2021-06-05 11:11:55', '2021-06-05 11:11:55', 2, 2),
(10, 0, '-', 7, 0, 1, 0, 1, '2021-06-07 10:07:00', '2021-06-07 10:09:44', 2, 2),
(11, 0, '-', 7, 0, 2, 0, 1, '2021-06-07 10:07:00', '2021-06-07 10:09:44', 2, 2),
(12, 0, '-', 7, 0, 3, 0, 1, '2021-06-07 10:07:00', '2021-06-07 10:09:44', 2, 2),
(13, 0, '-', 7, 0, 4, 0, 1, '2021-06-07 10:07:00', '2021-06-07 10:09:44', 2, 2),
(14, 5, '-', 8, 0, 1, 0, 1, '2021-06-07 10:24:15', '2021-06-07 10:49:25', 2, 2),
(15, 1, '-', 8, 0, 2, 0, 3, '2021-06-07 10:24:15', '2021-06-07 10:49:25', 2, 2),
(16, 1, '-', 8, 0, 3, 0, 2, '2021-06-07 10:24:15', '2021-06-07 10:49:25', 2, 2),
(17, 5, '-', 8, 0, 4, 0, 4, '2021-06-07 10:24:15', '2021-06-07 10:49:11', 2, 2),
(18, 1, '-', 9, 0, 1, 0, 1, '2021-06-07 10:49:37', '2021-06-07 10:51:00', 2, 2),
(19, 1, '-', 9, 0, 2, 0, 1, '2021-06-07 10:49:37', '2021-06-07 10:51:00', 2, 2),
(20, 1, '-', 9, 0, 3, 0, 1, '2021-06-07 10:49:37', '2021-06-07 10:51:00', 2, 2),
(21, 1, '-', 9, 0, 4, 0, 1, '2021-06-07 10:49:37', '2021-06-07 10:51:00', 2, 2),
(22, 1, '-', 10, 0, 1, 0, 1, '2021-06-07 10:51:16', '2021-06-07 10:51:44', 2, 2),
(23, 1, '-', 10, 0, 2, 0, 1, '2021-06-07 10:51:16', '2021-06-07 10:51:44', 2, 2),
(24, 1, '-', 10, 0, 3, 0, 1, '2021-06-07 10:51:16', '2021-06-07 10:51:44', 2, 2),
(25, 1, '-', 10, 0, 4, 0, 1, '2021-06-07 10:51:16', '2021-06-07 10:51:44', 2, 2),
(26, 0, '-', 11, 0, 1, 0, 1, '2021-06-07 10:51:59', '2021-06-07 10:51:59', 2, 2),
(27, 0, '-', 11, 0, 2, 0, 1, '2021-06-07 10:51:59', '2021-06-07 10:51:59', 2, 2),
(28, 0, '-', 11, 0, 3, 0, 1, '2021-06-07 10:51:59', '2021-06-07 10:51:59', 2, 2),
(29, 0, '-', 11, 0, 4, 0, 1, '2021-06-07 10:51:59', '2021-06-07 10:51:59', 2, 2),
(30, 0, '-', 12, 0, 1, 0, 1, '2021-06-07 10:53:05', '2021-06-07 10:53:05', 2, 2),
(31, 0, '-', 12, 0, 2, 0, 1, '2021-06-07 10:53:05', '2021-06-07 10:53:05', 2, 2),
(32, 0, '-', 12, 0, 3, 0, 1, '2021-06-07 10:53:05', '2021-06-07 10:53:05', 2, 2),
(33, 0, '-', 12, 0, 4, 0, 1, '2021-06-07 10:53:05', '2021-06-07 10:53:05', 2, 2),
(34, 0, '-', 13, 0, 1, 0, 1, '2021-06-07 10:55:22', '2021-06-07 10:55:22', 2, 2),
(35, 0, '-', 13, 0, 2, 0, 1, '2021-06-07 10:55:22', '2021-06-07 10:55:22', 2, 2),
(36, 0, '-', 13, 0, 3, 0, 1, '2021-06-07 10:55:22', '2021-06-07 10:55:22', 2, 2),
(37, 0, '-', 13, 0, 4, 0, 1, '2021-06-07 10:55:22', '2021-06-07 10:55:22', 2, 2),
(38, 0, '-', 14, 0, 1, 0, 1, '2021-06-07 10:57:27', '2021-06-07 10:57:27', 2, 2),
(39, 0, '-', 14, 0, 2, 0, 1, '2021-06-07 10:57:27', '2021-06-07 10:57:27', 2, 2),
(40, 0, '-', 14, 0, 3, 0, 1, '2021-06-07 10:57:27', '2021-06-07 10:57:27', 2, 2),
(41, 0, '-', 14, 0, 4, 0, 1, '2021-06-07 10:57:27', '2021-06-07 10:57:27', 2, 2),
(42, 0, '-', 15, 0, 1, 0, 1, '2021-06-07 10:57:55', '2021-06-07 10:57:55', 2, 2),
(43, 0, '-', 15, 0, 2, 0, 1, '2021-06-07 10:57:55', '2021-06-07 10:57:55', 2, 2),
(44, 0, '-', 15, 0, 3, 0, 1, '2021-06-07 10:57:55', '2021-06-07 10:57:55', 2, 2),
(45, 0, '-', 15, 0, 4, 0, 1, '2021-06-07 10:57:55', '2021-06-07 10:57:55', 2, 2),
(46, 0, '-', 16, 0, 1, 0, 1, '2021-06-07 10:58:16', '2021-06-07 10:58:16', 2, 2),
(47, 0, '-', 16, 0, 2, 0, 1, '2021-06-07 10:58:16', '2021-06-07 10:58:16', 2, 2),
(48, 0, '-', 16, 0, 3, 0, 1, '2021-06-07 10:58:16', '2021-06-07 10:58:16', 2, 2),
(49, 0, '-', 16, 0, 4, 0, 1, '2021-06-07 10:58:16', '2021-06-07 10:58:16', 2, 2),
(50, 0, '-', 17, 0, 1, 0, 1, '2021-06-07 10:58:37', '2021-06-07 10:58:37', 2, 2),
(51, 0, '-', 17, 0, 2, 0, 1, '2021-06-07 10:58:37', '2021-06-07 10:58:37', 2, 2),
(52, 0, '-', 17, 0, 3, 0, 1, '2021-06-07 10:58:37', '2021-06-07 10:58:37', 2, 2),
(53, 0, '-', 17, 0, 4, 0, 1, '2021-06-07 10:58:37', '2021-06-07 10:58:37', 2, 2),
(54, 0, '-', 18, 0, 1, 0, 1, '2021-06-07 10:59:05', '2021-06-07 10:59:05', 2, 2),
(55, 0, '-', 18, 0, 2, 0, 1, '2021-06-07 10:59:05', '2021-06-07 10:59:05', 2, 2),
(56, 0, '-', 18, 0, 3, 0, 1, '2021-06-07 10:59:05', '2021-06-07 10:59:05', 2, 2),
(57, 0, '-', 18, 0, 4, 0, 1, '2021-06-07 10:59:05', '2021-06-07 10:59:05', 2, 2),
(58, 0, '-', 19, 0, 1, 0, 1, '2021-06-07 10:59:21', '2021-06-07 10:59:21', 2, 2),
(59, 0, '-', 19, 0, 2, 0, 1, '2021-06-07 10:59:21', '2021-06-07 10:59:21', 2, 2),
(60, 0, '-', 19, 0, 3, 0, 1, '2021-06-07 10:59:21', '2021-06-07 10:59:21', 2, 2),
(61, 0, '-', 19, 0, 4, 0, 1, '2021-06-07 10:59:21', '2021-06-07 10:59:21', 2, 2),
(62, 0, '-', 20, 0, 1, 0, 1, '2021-06-07 10:59:34', '2021-06-07 10:59:34', 2, 2),
(63, 0, '-', 20, 0, 2, 0, 1, '2021-06-07 10:59:34', '2021-06-07 10:59:34', 2, 2),
(64, 0, '-', 20, 0, 3, 0, 1, '2021-06-07 10:59:34', '2021-06-07 10:59:34', 2, 2),
(65, 0, '-', 20, 0, 4, 0, 1, '2021-06-07 10:59:34', '2021-06-07 10:59:34', 2, 2),
(66, 0, '-', 21, 0, 1, 0, 1, '2021-06-07 10:59:46', '2021-06-07 10:59:46', 2, 2),
(67, 0, '-', 21, 0, 2, 0, 1, '2021-06-07 10:59:46', '2021-06-07 10:59:46', 2, 2),
(68, 0, '-', 21, 0, 3, 0, 1, '2021-06-07 10:59:46', '2021-06-07 10:59:46', 2, 2),
(69, 0, '-', 21, 0, 4, 0, 1, '2021-06-07 10:59:46', '2021-06-07 10:59:46', 2, 2),
(70, 0, '-', 22, 0, 1, 0, 1, '2021-06-07 11:00:20', '2021-06-07 11:00:20', 2, 2),
(71, 0, '-', 22, 0, 2, 0, 1, '2021-06-07 11:00:20', '2021-06-07 11:00:20', 2, 2),
(72, 0, '-', 22, 0, 3, 0, 1, '2021-06-07 11:00:20', '2021-06-07 11:00:20', 2, 2),
(73, 0, '-', 22, 0, 4, 0, 1, '2021-06-07 11:00:20', '2021-06-07 11:00:20', 2, 2),
(74, 0, '-', 23, 0, 1, 0, 1, '2021-06-07 11:01:22', '2021-06-07 11:01:22', 2, 2),
(75, 0, '-', 23, 0, 2, 0, 1, '2021-06-07 11:01:22', '2021-06-07 11:01:22', 2, 2),
(76, 0, '-', 23, 0, 3, 0, 1, '2021-06-07 11:01:22', '2021-06-07 11:01:22', 2, 2),
(77, 0, '-', 23, 0, 4, 0, 1, '2021-06-07 11:01:22', '2021-06-07 11:01:22', 2, 2),
(78, 1, '-', 24, 0, 1, 0, 1, '2021-06-07 11:08:48', '2021-06-07 11:08:48', 2, 2),
(79, 2, '-', 24, 0, 2, 0, 1, '2021-06-07 11:08:48', '2021-06-07 11:08:48', 2, 2),
(80, 2, '-', 24, 0, 3, 0, 1, '2021-06-07 11:08:48', '2021-06-07 11:08:48', 2, 2),
(81, 1, '-', 24, 0, 4, 0, 1, '2021-06-07 11:08:48', '2021-06-07 11:08:48', 2, 2),
(82, 1, '-', 25, 0, 1, 0, 1, '2021-06-07 11:09:18', '2021-06-07 11:09:18', 2, 2),
(83, 2, '-', 25, 0, 2, 0, 1, '2021-06-07 11:09:18', '2021-06-07 11:09:18', 2, 2),
(84, 2, '-', 25, 0, 3, 0, 1, '2021-06-07 11:09:18', '2021-06-07 11:09:18', 2, 2),
(85, 1, '-', 25, 0, 4, 0, 1, '2021-06-07 11:09:18', '2021-06-07 11:09:18', 2, 2),
(86, 123, '-', 26, 0, 1, 0, 1, '2021-06-07 11:09:45', '2021-06-07 11:09:45', 2, 2),
(87, 12, '-', 26, 0, 2, 0, 1, '2021-06-07 11:09:45', '2021-06-07 11:09:45', 2, 2),
(88, 1, '-', 26, 0, 3, 0, 1, '2021-06-07 11:09:45', '2021-06-07 11:09:45', 2, 2),
(89, 2, '-', 26, 0, 4, 0, 1, '2021-06-07 11:09:45', '2021-06-07 11:09:45', 2, 2),
(90, 2, '-', 27, 3, 0, 0, 1, '2021-06-07 11:10:17', '2021-06-07 11:10:34', 2, 2),
(91, 2, '-', 27, 4, 0, 0, 1, '2021-06-07 11:10:17', '2021-06-07 11:10:34', 2, 2),
(92, 1, '-', 28, 3, 0, 0, 1, '2021-06-07 11:12:59', '2021-06-07 11:12:59', 2, 2),
(93, 3, '-', 28, 4, 0, 0, 1, '2021-06-07 11:12:59', '2021-06-07 11:12:59', 2, 2),
(94, 5, '-', 29, 3, 0, 0, 1, '2021-06-07 11:13:25', '2021-06-07 11:13:25', 2, 2),
(95, 5, '-', 29, 4, 0, 0, 1, '2021-06-07 11:13:25', '2021-06-07 11:13:25', 2, 2),
(96, 0, '123', 30, 1, 0, 0, 1, '2021-06-07 11:13:42', '2021-06-07 11:13:42', 2, 2),
(97, 123, '123', 30, 2, 0, 0, 1, '2021-06-07 11:13:42', '2021-06-07 11:13:42', 2, 2),
(98, 12, '123', 31, 1, 0, 0, 1, '2021-06-07 11:13:52', '2021-06-07 11:14:02', 2, 2),
(99, 123, '123', 31, 2, 0, 0, 1, '2021-06-07 11:13:52', '2021-06-07 11:13:52', 2, 2),
(100, 0, '-', 32, 5, 0, 0, 1, '2021-06-09 01:55:04', '2021-06-09 01:55:04', 2, 2),
(101, 0, '-', 33, 0, 5, 0, 1, '2021-06-15 08:07:45', '2021-06-15 08:07:45', 5, 5),
(102, 0, '-', 33, 0, 6, 0, 1, '2021-06-15 08:07:45', '2021-06-15 08:07:45', 5, 5),
(103, 0, '-', 34, 0, 5, 0, 1, '2021-06-15 08:07:48', '2021-06-15 08:08:41', 5, 5),
(104, 0, '-', 34, 0, 6, 0, 1, '2021-06-15 08:07:48', '2021-06-15 08:08:41', 5, 5),
(105, 0, '-', 35, 0, 5, 0, 1, '2021-06-15 08:07:53', '2021-06-15 08:08:45', 5, 5),
(106, 0, '-', 35, 0, 6, 0, 1, '2021-06-15 08:07:53', '2021-06-15 08:08:45', 5, 5),
(107, 10, '-', 36, 0, 5, 0, 1, '2021-06-15 08:08:33', '2021-06-15 08:08:33', 5, 5),
(108, 0, '-', 36, 0, 6, 0, 1, '2021-06-15 08:08:33', '2021-06-15 08:08:33', 5, 5),
(109, 0, '-', 37, 0, 0, 22, 1, '2021-06-16 10:30:12', '2021-06-16 10:45:51', 5, 5),
(110, 500, '-', 38, 0, 0, 21, 1, '2021-06-16 10:31:55', '2021-06-16 10:31:55', 5, 5),
(111, 600, '-', 39, 0, 0, 23, 1, '2021-06-16 10:43:20', '2021-06-16 10:43:20', 5, 5),
(112, 700, '-', 40, 0, 0, 24, 1, '2021-06-16 10:43:44', '2021-06-16 10:43:44', 5, 5),
(113, 600, '-', 41, 0, 0, 22, 1, '2021-06-16 10:46:02', '2021-06-16 10:46:02', 5, 5),
(114, 200, '-', 42, 0, 0, 25, 1, '2021-06-17 12:08:02', '2021-06-17 12:08:02', 5, 5),
(115, 300, '-', 43, 0, 0, 26, 1, '2021-06-17 12:08:05', '2021-06-17 12:08:05', 5, 5),
(116, 100, '-', 44, 7, 0, 0, 1, '2021-06-26 12:30:40', '2021-06-26 12:30:40', 5, 5),
(117, 100, '-', 44, 8, 0, 0, 1, '2021-06-26 12:30:40', '2021-06-26 12:30:40', 5, 5),
(118, 100, '-', 45, 7, 0, 0, 1, '2021-06-26 12:36:27', '2021-06-26 12:36:27', 5, 5),
(119, 100, '-', 45, 8, 0, 0, 1, '2021-06-26 12:36:27', '2021-06-26 12:36:27', 5, 5),
(120, 10, '-', 46, 7, 0, 0, 1, '2021-06-26 12:43:19', '2021-06-26 12:43:19', 5, 5),
(121, 10, '-', 46, 8, 0, 0, 1, '2021-06-26 12:43:19', '2021-06-26 12:43:19', 5, 5),
(122, 10, '-', 47, 7, 0, 0, 1, '2021-06-26 12:43:38', '2021-06-26 12:43:38', 5, 5),
(123, 10, '-', 47, 8, 0, 0, 1, '2021-06-26 12:43:38', '2021-06-26 12:43:38', 5, 5),
(124, 0, '-', 48, 7, 0, 0, 1, '2021-06-26 12:48:20', '2021-06-26 12:49:47', 5, 5),
(125, 0, '-', 48, 8, 0, 0, 1, '2021-06-26 12:48:20', '2021-06-26 12:49:47', 5, 5),
(126, 99, '-', 49, 0, 13, 0, 1, '2021-06-26 01:02:09', '2021-06-26 01:02:09', 5, 5),
(127, 99, '-', 49, 0, 14, 0, 1, '2021-06-26 01:02:09', '2021-06-26 01:02:09', 5, 5),
(128, 0, '-', 50, 0, 13, 0, 2, '2021-06-26 01:02:58', '2021-06-26 01:09:47', 5, 5),
(129, 0, '-', 50, 0, 14, 0, 2, '2021-06-26 01:02:58', '2021-06-26 01:09:47', 5, 5),
(130, 1000, '-', 51, 0, 0, 27, 1, '2021-06-26 10:49:03', '2021-06-26 10:49:03', 5, 5),
(131, 0, '-', 52, 0, 0, 28, 1, '2021-06-26 11:07:24', '2021-06-26 01:05:12', 5, 5),
(132, 0, '-', 53, 0, 0, 29, 1, '2021-06-26 11:07:27', '2021-06-26 01:05:17', 5, 5),
(133, 0, '-', 54, 7, 0, 0, 2, '2021-06-26 12:51:05', '2021-06-26 01:08:27', 5, 5),
(134, 0, '-', 54, 8, 0, 0, 2, '2021-06-26 12:51:05', '2021-06-26 01:08:27', 5, 5),
(135, 0, '-', 55, 7, 0, 0, 2, '2021-06-26 01:08:45', '2021-06-26 01:08:52', 5, 5),
(136, 0, '-', 55, 8, 0, 0, 2, '2021-06-26 01:08:45', '2021-06-26 01:08:52', 5, 5);

--
-- Triggers `tbl_brg_pengiriman`
--
DELIMITER $$
CREATE TRIGGER `trg_update_brg_cabang_after_insert_brg_pengiriman` AFTER INSERT ON `tbl_brg_pengiriman` FOR EACH ROW begin
	
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
CREATE TRIGGER `trg_update_brg_cabang_after_update_brg_pengiriman` AFTER UPDATE ON `tbl_brg_pengiriman` FOR EACH ROW begin
	
            
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
-- Dumping data for table `tbl_brg_penjualan`
--

INSERT INTO `tbl_brg_penjualan` (`id_pk_brg_penjualan`, `brg_penjualan_qty_real`, `brg_penjualan_satuan_real`, `brg_penjualan_qty`, `brg_penjualan_satuan`, `brg_penjualan_harga`, `brg_penjualan_note`, `brg_penjualan_status`, `id_fk_penjualan`, `id_fk_barang`, `brg_penjualan_create_date`, `brg_penjualan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 0, 'Pcs', 2, 'Pcs', 1000, '-', 'AKTIF', 1, 14, '2021-06-05 05:38:14', '2021-06-07 08:34:28', 2, 2),
(2, 0, 'Pcs', 2, 'Pcs', 1000, '-', 'AKTIF', 1, 10, '2021-06-05 05:38:14', '2021-06-07 08:34:28', 2, 2),
(3, 0, 'Pcs', 1000, 'Pcs', 1000, '-', 'AKTIF', 2, 10, '2021-06-05 05:46:37', '2021-06-05 07:48:34', 2, 2),
(4, 0, 'Pcs', 2000, 'Pcs', 1000, '-', 'AKTIF', 2, 66, '2021-06-05 05:46:37', '2021-06-05 07:48:34', 2, 2),
(5, 0, 'Pcs', 2, 'Pcs', 250000, '', 'AKTIF', 3, 2, '2021-06-09 01:46:58', '2021-06-09 01:49:21', 2, 2),
(6, 0, 'Pcs', 2, 'Pcs', 250000, '', 'AKTIF', 4, 2, '2021-06-09 01:47:03', '2021-06-09 01:50:40', 2, 2),
(7, 0, 'Pcs', 1000, 'Pcs', 1000, '-', 'AKTIF', 5, 23, '2021-06-26 12:25:45', '2021-06-26 12:25:45', 5, 5),
(8, 0, 'Pcs', 1000, 'Pcs', 1000, '-', 'AKTIF', 5, 24, '2021-06-26 12:25:45', '2021-06-26 12:25:45', 5, 5),
(9, 0, 'Pcs', 100, 'Pcs', 1000, '-', 'AKTIF', 6, 24, '2021-10-16 08:01:06', '2021-10-16 08:01:06', 4, 4),
(10, 0, 'Pcs', 200, 'Pcs', 200, '-', 'AKTIF', 6, 23, '2021-10-16 08:01:06', '2021-10-16 08:01:06', 4, 4),
(11, 0, 'Pcs', 1000, 'Pcs', 1000, '-', 'AKTIF', 7, 24, '2021-10-16 08:02:21', '2021-10-16 08:03:38', 4, 4),
(12, 0, 'Pcs', 2000, 'Pcs', 200, '-', 'AKTIF', 7, 23, '2021-10-16 08:03:06', '2021-10-16 08:03:38', 4, 4);

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
-- Dumping data for table `tbl_brg_permintaan`
--

INSERT INTO `tbl_brg_permintaan` (`id_pk_brg_permintaan`, `brg_permintaan_qty`, `brg_permintaan_notes`, `brg_permintaan_deadline`, `brg_permintaan_status`, `id_fk_brg`, `id_fk_cabang`, `brg_permintaan_create_date`, `brg_permintaan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1500, '-', '2020-07-29', 'SELESAI', 24, 2, '2020-07-28 01:40:07', '2020-07-28 10:45:00', 2, 2),
(2, 1000, '-', '2020-07-31', 'aktif', 23, 2, '2020-07-28 01:42:40', '2020-07-28 11:42:48', 2, 2),
(3, 10000, '-', '2020-07-29', 'aktif', 23, 2, '2020-07-28 01:42:58', '2020-07-28 01:42:58', 2, 2),
(4, 1000, '-', '2020-07-31', 'SELESAI', 1, 1, '2020-07-30 02:37:07', '2021-06-09 01:59:06', 2, 2),
(5, 2000, '-', '2020-07-31', 'SELESAI', 13, 1, '2020-07-30 02:37:22', '2021-06-09 01:56:35', 2, 2),
(6, 1200, '-', '2020-08-07', 'SELESAI', 13, 1, '2020-07-30 02:42:33', '2021-06-09 01:56:52', 2, 2),
(7, 1000, '-', '2020-07-31', 'SELESAI', 22, 1, '2020-07-30 10:13:09', '2021-06-09 01:56:37', 2, 2),
(8, 15, 'sd', '2021-06-09', 'SELESAI', 1, 1, '2021-06-09 01:57:26', '2021-06-09 01:58:00', 2, 2),
(9, 1000, '-', '2021-06-09', 'BATAL', 1, 1, '2021-06-09 10:12:00', '2021-06-09 10:12:10', 5, 5),
(10, 20000, '-', '2021-06-24', 'BATAL', 24, 1, '2021-06-15 08:12:27', '2021-06-15 03:12:44', 5, 5),
(11, 1000, '-', '2021-06-25', 'BATAL', 23, 1, '2021-06-16 09:05:25', '2021-06-16 04:05:35', 5, 5),
(12, 1000, '-', '2021-06-24', 'BATAL', 24, 1, '2021-06-16 09:09:15', '2021-06-16 04:09:18', 5, 5),
(13, 1000, '-', '2021-06-25', 'BATAL', 13, 1, '2021-06-16 09:10:49', '2021-06-16 04:10:52', 5, 5),
(14, 1000, '-', '2021-06-25', 'BATAL', 23, 1, '2021-06-16 09:32:18', '2021-06-16 04:33:52', 5, 5),
(15, 1000, '-', '2021-06-17', 'BATAL', 23, 1, '2021-06-16 09:34:08', '2021-06-16 04:34:10', 5, 5),
(16, 1000, '-', '2021-06-25', 'BATAL', 23, 1, '2021-06-16 09:34:32', '2021-06-16 04:34:34', 5, 5),
(17, 1000, '-', '2021-06-24', 'BATAL', 23, 1, '2021-06-16 09:35:56', '2021-06-16 04:35:58', 5, 5),
(18, 1000, '-', '2021-06-24', 'aktif', 23, 1, '2021-06-16 09:36:06', '2021-06-16 09:36:06', 5, 5),
(19, 2000, '-', '2021-06-24', 'aktif', 13, 1, '2021-06-16 09:36:16', '2021-06-16 09:36:16', 5, 5),
(20, 3000, '-=', '2021-06-25', 'aktif', 24, 1, '2021-06-16 09:36:25', '2021-06-16 09:36:25', 5, 5),
(21, 100, '-', '2021-07-10', 'aktif', 23, 5, '2021-06-26 11:03:34', '2021-06-26 11:05:37', 5, 5),
(22, 200, '-', '2021-07-10', 'aktif', 24, 5, '2021-06-26 11:03:42', '2021-06-26 11:05:42', 5, 5),
(23, 123, '-', '2021-07-10', 'aktif', 12, 5, '2021-06-26 11:03:51', '2021-06-26 11:05:47', 5, 5);

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
-- Dumping data for table `tbl_brg_pindah`
--

INSERT INTO `tbl_brg_pindah` (`id_pk_brg_pindah`, `brg_pindah_sumber`, `id_fk_refrensi_sumber`, `id_brg_awal`, `id_brg_tujuan`, `id_fk_cabang`, `brg_pindah_qty`, `brg_pindah_status`, `brg_pindah_create_date`, `brg_pindah_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'penjualan', 1, 9, 66, 4, 1, 'AKTIF', '2021-06-05 17:37:10', '2021-06-05 17:37:10', 2, 2),
(2, 'penjualan', 1, 10, 14, 4, 1, 'AKTIF', '2021-06-05 17:37:10', '2021-06-05 17:37:10', 2, 2),
(3, 'penjualan', 1, 11, 9, 4, 1, 'AKTIF', '2021-06-05 17:37:10', '2021-06-05 17:37:10', 2, 2);

--
-- Triggers `tbl_brg_pindah`
--
DELIMITER $$
CREATE TRIGGER `trg_update_brg_cabang_after_insert_brg_pindah` AFTER INSERT ON `tbl_brg_pindah` FOR EACH ROW begin
	/*update barang cabang*/
	select id_pk_satuan into @id_satuan from mstr_satuan where mstr_satuan.satuan_rumus = 1 LIMIT 1;            
    
END
$$
DELIMITER ;

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
-- Dumping data for table `tbl_brg_warehouse`
--

INSERT INTO `tbl_brg_warehouse` (`id_pk_brg_warehouse`, `brg_warehouse_qty`, `brg_warehouse_notes`, `brg_warehouse_status`, `id_fk_brg`, `id_fk_warehouse`, `brg_warehouse_create_date`, `brg_warehouse_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 11, '-', 'AKTIF', 23, 2, '2020-07-29 08:20:00', '2020-07-29 08:20:19', 2, 2),
(2, 555, '-', 'aktif', 24, 2, '2020-07-29 08:20:00', '2020-07-29 08:34:23', 2, 2),
(3, 2700, 'Auto insert from checking construct', 'aktif', 13, 2, '2020-07-29 08:20:00', '2020-07-29 08:20:00', 2, 2),
(4, 2700, 'Auto insert from checking construct', 'aktif', 15, 2, '2020-07-29 08:20:00', '2020-07-29 08:20:00', 2, 2),
(5, 566, '-', 'AKTIF', 20, 2, '2020-07-29 08:21:01', '2020-07-29 08:21:01', 2, 2),
(6, 5666, 'Auto insert from checking construct', 'aktif', 5, 2, '2020-07-29 08:21:02', '2020-07-29 08:21:02', 2, 2),
(7, 12000, 'Auto insert from checking construct', 'aktif', 4, 2, '2020-07-29 08:21:02', '2020-07-29 08:21:02', 2, 2),
(8, 7000, 'Auto insert from checking construct', 'aktif', 22, 2, '2020-07-29 08:21:02', '2020-07-29 08:21:02', 2, 2),
(9, 10, '-', 'AKTIF', 9, 4, '2021-04-24 03:28:49', '2021-04-24 03:28:49', 2, 2),
(10, 1000, '-', 'AKTIF', 8, 5, '2021-06-09 09:18:42', '2021-06-09 09:18:42', 5, 5),
(11, 2000, '-', 'AKTIF', 9, 5, '2021-06-09 09:33:18', '2021-06-09 09:33:18', 5, 5),
(12, 1000, '-=', 'AKTIF', 8, 11, '2021-06-09 10:32:43', '2021-06-09 10:32:43', 5, 5),
(13, 2000, '-', 'AKTIF', 10, 11, '2021-06-09 10:33:05', '2021-06-09 10:33:05', 5, 5),
(14, 800, '-', 'AKTIF', 23, 5, '2021-06-17 12:07:29', '2021-06-17 12:07:29', 5, 5),
(15, 700, '-', 'AKTIF', 24, 5, '2021-06-17 12:07:29', '2021-06-17 12:07:29', 5, 5);

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
-- Dumping data for table `tbl_cabang_admin`
--

INSERT INTO `tbl_cabang_admin` (`id_pk_cabang_admin`, `id_fk_cabang`, `id_fk_user`, `cabang_admin_status`, `cabang_admin_create_date`, `cabang_admin_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'nonaktif', '2020-06-21 11:45:03', '2020-07-06 09:31:47', 1, 1),
(2, 2, 1, 'AKTIF', '2020-06-22 11:59:26', '2020-06-22 11:59:26', 1, 1),
(3, 1, 2, 'AKTIF', '2020-06-22 05:21:26', '2020-06-22 05:21:26', 1, 1),
(4, 1, 3, 'AKTIF', '2020-06-22 05:21:26', '2020-06-22 05:21:26', 1, 1),
(5, 2, 2, 'AKTIF', '2020-06-22 06:48:17', '2020-06-22 06:48:17', 1, 1),
(6, 2, 3, 'AKTIF', '2020-06-22 06:48:17', '2020-06-22 06:48:17', 1, 1),
(7, 1, 1, 'AKTIF', '2020-07-07 10:46:18', '2020-07-07 10:46:18', 1, 1),
(8, 4, 2, 'AKTIF', '2020-07-29 11:16:00', '2020-07-29 11:16:00', 2, 2),
(9, 1, 5, 'AKTIF', '2021-06-09 10:01:57', '2021-06-09 10:01:57', 5, 5),
(10, 3, 5, 'AKTIF', '2021-06-09 09:50:52', '2021-06-09 09:50:52', 5, 5),
(11, 5, 5, 'AKTIF', '2021-06-16 09:48:41', '2021-06-16 09:48:41', 5, 5),
(12, 5, 4, 'AKTIF', '2021-10-15 08:19:43', '2021-10-15 08:19:43', 4, 4),
(13, 3, 4, 'AKTIF', '2021-10-15 08:20:54', '2021-10-15 08:20:54', 4, 4);

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
-- Dumping data for table `tbl_hak_akses`
--

INSERT INTO `tbl_hak_akses` (`id_pk_hak_akses`, `id_fk_jabatan`, `id_fk_menu`, `hak_akses_status`, `hak_akses_create_date`, `hak_akses_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1),
(2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1),
(3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1),
(4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1),
(5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1),
(6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1),
(7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1),
(8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1),
(9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1),
(10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1),
(11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1),
(12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1),
(13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1),
(14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1),
(15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1),
(16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1),
(17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1),
(18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1),
(19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1),
(20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1),
(21, 1, 21, 'aktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1),
(22, 2, 1, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(23, 2, 2, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(24, 2, 3, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(25, 2, 4, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(26, 2, 5, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(27, 2, 6, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(28, 2, 7, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(29, 2, 8, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(30, 2, 9, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(31, 2, 10, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(32, 2, 11, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(33, 2, 12, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(34, 2, 13, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(35, 2, 14, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(36, 2, 15, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(37, 2, 16, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(38, 2, 17, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(39, 2, 18, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(40, 2, 19, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(41, 2, 20, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(42, 2, 21, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(43, 3, 1, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(44, 3, 2, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(45, 3, 3, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(46, 3, 4, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(47, 3, 5, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(48, 3, 6, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(49, 3, 7, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(50, 3, 8, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(51, 3, 9, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(52, 3, 10, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(53, 3, 11, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(54, 3, 12, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(55, 3, 13, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(56, 3, 14, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(57, 3, 15, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(58, 3, 16, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(59, 3, 17, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(60, 3, 18, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(61, 3, 19, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(62, 3, 20, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(63, 3, 21, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(64, 4, 1, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(65, 4, 2, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(66, 4, 3, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(67, 4, 4, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(68, 4, 5, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(69, 4, 6, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(70, 4, 7, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(71, 4, 8, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(72, 4, 9, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(73, 4, 10, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(74, 4, 11, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(75, 4, 12, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(76, 4, 13, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(77, 4, 14, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(78, 4, 15, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(79, 4, 16, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(80, 4, 17, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(81, 4, 18, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(82, 4, 19, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(83, 4, 20, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(84, 4, 21, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1),
(86, 2, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1),
(87, 3, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1),
(88, 4, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1),
(89, 1, 23, 'aktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1),
(90, 2, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1),
(91, 3, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1),
(92, 4, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1),
(93, 1, 24, 'aktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1),
(94, 2, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1),
(95, 3, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1),
(96, 4, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1),
(97, 1, 25, 'aktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1),
(98, 2, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1),
(99, 3, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1),
(100, 4, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1),
(101, 1, 26, 'aktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1),
(102, 2, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1),
(103, 3, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1),
(104, 4, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1),
(105, 1, 27, 'aktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1),
(106, 2, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1),
(107, 3, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1),
(108, 4, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1),
(109, 1, 28, 'aktif', '2020-07-06 08:49:56', '2020-07-06 08:49:56', 1, 1),
(110, 2, 28, 'nonaktif', '2020-07-06 08:49:56', '2020-07-06 08:49:56', 1, 1),
(111, 3, 28, 'nonaktif', '2020-07-06 08:49:56', '2020-07-06 08:49:56', 1, 1),
(112, 4, 28, 'nonaktif', '2020-07-06 08:49:56', '2020-07-06 08:49:56', 1, 1),
(113, 1, 29, 'aktif', '2020-07-06 09:13:00', '2020-07-06 09:13:00', 1, 1),
(114, 2, 29, 'nonaktif', '2020-07-06 09:13:00', '2020-07-06 09:13:00', 1, 1),
(115, 3, 29, 'nonaktif', '2020-07-06 09:13:00', '2020-07-06 09:13:00', 1, 1),
(116, 4, 29, 'nonaktif', '2020-07-06 09:13:00', '2020-07-06 09:13:00', 1, 1),
(117, 1, 30, 'aktif', '2020-07-06 09:15:25', '2020-07-06 09:15:25', 1, 1),
(118, 2, 30, 'nonaktif', '2020-07-06 09:15:25', '2020-07-06 09:15:25', 1, 1),
(119, 3, 30, 'nonaktif', '2020-07-06 09:15:25', '2020-07-06 09:15:25', 1, 1),
(120, 4, 30, 'nonaktif', '2020-07-06 09:15:25', '2020-07-06 09:15:25', 1, 1),
(121, 1, 31, 'aktif', '2020-07-06 09:43:54', '2020-07-06 09:43:54', 1, 1),
(122, 2, 31, 'nonaktif', '2020-07-06 09:43:54', '2020-07-06 09:43:54', 1, 1),
(123, 3, 31, 'nonaktif', '2020-07-06 09:43:54', '2020-07-06 09:43:54', 1, 1),
(124, 4, 31, 'nonaktif', '2020-07-06 09:43:54', '2020-07-06 09:43:54', 1, 1),
(125, 1, 32, 'aktif', '2020-07-06 09:59:33', '2020-07-06 09:59:33', 1, 1),
(126, 2, 32, 'nonaktif', '2020-07-06 09:59:33', '2020-07-06 09:59:33', 1, 1),
(127, 3, 32, 'nonaktif', '2020-07-06 09:59:33', '2020-07-06 09:59:33', 1, 1),
(128, 4, 32, 'nonaktif', '2020-07-06 09:59:33', '2020-07-06 09:59:33', 1, 1),
(129, 1, 33, 'aktif', '2020-07-07 10:38:41', '2020-07-07 10:38:41', 1, 1),
(130, 2, 33, 'nonaktif', '2020-07-07 10:38:41', '2020-07-07 10:38:41', 1, 1),
(131, 3, 33, 'nonaktif', '2020-07-07 10:38:41', '2020-07-07 10:38:41', 1, 1),
(132, 4, 33, 'nonaktif', '2020-07-07 10:38:41', '2020-07-07 10:38:41', 1, 1),
(133, 1, 34, 'aktif', '2020-07-10 09:45:16', '2020-07-10 09:45:16', 1, 1),
(134, 2, 34, 'nonaktif', '2020-07-10 09:45:16', '2020-07-10 09:45:16', 1, 1),
(135, 3, 34, 'nonaktif', '2020-07-10 09:45:16', '2020-07-10 09:45:16', 1, 1),
(136, 4, 34, 'nonaktif', '2020-07-10 09:45:16', '2020-07-10 09:45:16', 1, 1),
(137, 1, 35, 'aktif', '2020-07-14 11:28:08', '2020-07-14 11:28:08', 1, 1),
(138, 2, 35, 'nonaktif', '2020-07-14 11:28:08', '2020-07-14 11:28:08', 1, 1),
(139, 3, 35, 'nonaktif', '2020-07-14 11:28:08', '2020-07-14 11:28:08', 1, 1),
(140, 4, 35, 'nonaktif', '2020-07-14 11:28:08', '2020-07-14 11:28:08', 1, 1),
(141, 1, 36, 'aktif', '2020-07-14 11:29:16', '2020-07-14 11:29:16', 1, 1),
(142, 2, 36, 'nonaktif', '2020-07-14 11:29:16', '2020-07-14 11:29:16', 1, 1),
(143, 3, 36, 'nonaktif', '2020-07-14 11:29:16', '2020-07-14 11:29:16', 1, 1),
(144, 4, 36, 'nonaktif', '2020-07-14 11:29:16', '2020-07-14 11:29:16', 1, 1),
(145, 1, 37, 'aktif', '2020-07-14 11:29:41', '2020-07-14 11:29:41', 1, 1),
(146, 2, 37, 'nonaktif', '2020-07-14 11:29:41', '2020-07-14 11:29:41', 1, 1),
(147, 3, 37, 'nonaktif', '2020-07-14 11:29:41', '2020-07-14 11:29:41', 1, 1),
(148, 4, 37, 'nonaktif', '2020-07-14 11:29:41', '2020-07-14 11:29:41', 1, 1),
(149, 1, 38, 'aktif', '2020-07-15 08:20:40', '2020-07-15 08:20:40', 2, 2),
(150, 2, 38, 'nonaktif', '2020-07-15 08:20:40', '2020-07-15 08:20:40', 2, 2),
(151, 3, 38, 'nonaktif', '2020-07-15 08:20:40', '2020-07-15 08:20:40', 2, 2),
(152, 4, 38, 'nonaktif', '2020-07-15 08:20:40', '2020-07-15 08:20:40', 2, 2),
(153, 1, 39, 'aktif', '2020-07-15 08:20:56', '2020-07-15 08:20:56', 2, 2),
(154, 2, 39, 'nonaktif', '2020-07-15 08:20:56', '2020-07-15 08:20:56', 2, 2),
(155, 3, 39, 'nonaktif', '2020-07-15 08:20:56', '2020-07-15 08:20:56', 2, 2),
(156, 4, 39, 'nonaktif', '2020-07-15 08:20:56', '2020-07-15 08:20:56', 2, 2),
(157, 1, 40, 'aktif', '2020-07-17 02:06:25', '2020-07-17 02:06:25', 2, 2),
(158, 2, 40, 'nonaktif', '2020-07-17 02:06:25', '2020-07-17 02:06:25', 2, 2),
(159, 3, 40, 'nonaktif', '2020-07-17 02:06:25', '2020-07-17 02:06:25', 2, 2),
(160, 4, 40, 'nonaktif', '2020-07-17 02:06:25', '2020-07-17 02:06:25', 2, 2),
(161, 1, 41, 'aktif', '2020-07-28 11:37:56', '2020-07-28 11:37:56', 2, 2),
(162, 2, 41, 'nonaktif', '2020-07-28 11:37:56', '2020-07-28 11:37:56', 2, 2),
(163, 3, 41, 'nonaktif', '2020-07-28 11:37:56', '2020-07-28 11:37:56', 2, 2),
(164, 4, 41, 'nonaktif', '2020-07-28 11:37:56', '2020-07-28 11:37:56', 2, 2),
(165, 1, 42, 'aktif', '2020-07-29 11:45:28', '2020-07-29 11:45:28', 2, 2),
(166, 2, 42, 'nonaktif', '2020-07-29 11:45:28', '2020-07-29 11:45:28', 2, 2),
(167, 3, 42, 'nonaktif', '2020-07-29 11:45:28', '2020-07-29 11:45:28', 2, 2),
(168, 4, 42, 'nonaktif', '2020-07-29 11:45:28', '2020-07-29 11:45:28', 2, 2),
(169, 1, 44, 'aktif', NULL, NULL, NULL, NULL),
(170, 2, 44, 'nonaktif', NULL, NULL, NULL, NULL),
(171, 3, 44, 'nonaktif', NULL, NULL, NULL, NULL),
(172, 4, 44, 'nonaktif', NULL, NULL, NULL, NULL),
(173, 1, 45, 'nonaktif', NULL, NULL, NULL, NULL),
(174, 1, 46, 'nonaktif', NULL, NULL, NULL, NULL),
(175, 1, 47, 'nonaktif', NULL, NULL, NULL, NULL),
(176, 2, 47, 'nonaktif', NULL, NULL, NULL, NULL),
(177, 3, 47, 'nonaktif', NULL, NULL, NULL, NULL),
(178, 4, 47, 'nonaktif', NULL, NULL, NULL, NULL),
(179, 7, 1, 'nonaktif', NULL, NULL, NULL, NULL),
(180, 7, 2, 'aktif', NULL, NULL, NULL, NULL),
(181, 7, 3, 'nonaktif', NULL, NULL, NULL, NULL),
(182, 7, 4, 'nonaktif', NULL, NULL, NULL, NULL),
(183, 7, 5, 'nonaktif', NULL, NULL, NULL, NULL),
(184, 7, 6, 'nonaktif', NULL, NULL, NULL, NULL),
(185, 7, 7, 'nonaktif', NULL, NULL, NULL, NULL),
(186, 7, 8, 'nonaktif', NULL, NULL, NULL, NULL),
(187, 7, 9, 'nonaktif', NULL, NULL, NULL, NULL),
(188, 7, 10, 'nonaktif', NULL, NULL, NULL, NULL),
(189, 7, 11, 'nonaktif', NULL, NULL, NULL, NULL),
(190, 7, 12, 'nonaktif', NULL, NULL, NULL, NULL),
(191, 7, 13, 'nonaktif', NULL, NULL, NULL, NULL),
(192, 7, 14, 'nonaktif', NULL, NULL, NULL, NULL),
(193, 7, 15, 'nonaktif', NULL, NULL, NULL, NULL),
(194, 7, 16, 'nonaktif', NULL, NULL, NULL, NULL),
(195, 7, 17, 'nonaktif', NULL, NULL, NULL, NULL),
(196, 7, 18, 'aktif', NULL, NULL, NULL, NULL),
(197, 7, 19, 'nonaktif', NULL, NULL, NULL, NULL),
(198, 7, 20, 'nonaktif', NULL, NULL, NULL, NULL),
(199, 7, 21, 'nonaktif', NULL, NULL, NULL, NULL),
(200, 7, 22, 'nonaktif', NULL, NULL, NULL, NULL),
(201, 7, 23, 'nonaktif', NULL, NULL, NULL, NULL),
(202, 7, 24, 'nonaktif', NULL, NULL, NULL, NULL),
(203, 7, 25, 'nonaktif', NULL, NULL, NULL, NULL),
(204, 7, 26, 'nonaktif', NULL, NULL, NULL, NULL),
(205, 7, 27, 'nonaktif', NULL, NULL, NULL, NULL),
(206, 7, 28, 'nonaktif', NULL, NULL, NULL, NULL),
(207, 7, 29, 'nonaktif', NULL, NULL, NULL, NULL),
(208, 7, 30, 'nonaktif', NULL, NULL, NULL, NULL),
(209, 7, 31, 'nonaktif', NULL, NULL, NULL, NULL),
(210, 7, 32, 'nonaktif', NULL, NULL, NULL, NULL),
(211, 7, 33, 'nonaktif', NULL, NULL, NULL, NULL),
(212, 7, 34, 'nonaktif', NULL, NULL, NULL, NULL),
(213, 7, 35, 'nonaktif', NULL, NULL, NULL, NULL),
(214, 7, 36, 'nonaktif', NULL, NULL, NULL, NULL),
(215, 7, 37, 'nonaktif', NULL, NULL, NULL, NULL),
(216, 7, 38, 'nonaktif', NULL, NULL, NULL, NULL),
(217, 7, 39, 'nonaktif', NULL, NULL, NULL, NULL),
(218, 7, 40, 'nonaktif', NULL, NULL, NULL, NULL),
(219, 7, 41, 'nonaktif', NULL, NULL, NULL, NULL),
(220, 7, 42, 'nonaktif', NULL, NULL, NULL, NULL),
(221, 7, 44, 'nonaktif', NULL, NULL, NULL, NULL),
(222, 7, 45, 'nonaktif', NULL, NULL, NULL, NULL),
(223, 8, 1, 'nonaktif', NULL, NULL, NULL, NULL),
(224, 8, 2, 'nonaktif', NULL, NULL, NULL, NULL),
(225, 8, 3, 'aktif', NULL, NULL, NULL, NULL),
(226, 8, 4, 'aktif', NULL, NULL, NULL, NULL),
(227, 8, 5, 'aktif', NULL, NULL, NULL, NULL),
(228, 8, 6, 'nonaktif', NULL, NULL, NULL, NULL),
(229, 8, 7, 'nonaktif', NULL, NULL, NULL, NULL),
(230, 8, 8, 'nonaktif', NULL, NULL, NULL, NULL),
(231, 8, 9, 'nonaktif', NULL, NULL, NULL, NULL),
(232, 8, 10, 'nonaktif', NULL, NULL, NULL, NULL),
(233, 8, 11, 'nonaktif', NULL, NULL, NULL, NULL),
(234, 8, 12, 'nonaktif', NULL, NULL, NULL, NULL),
(235, 8, 13, 'nonaktif', NULL, NULL, NULL, NULL),
(236, 8, 14, 'nonaktif', NULL, NULL, NULL, NULL),
(237, 8, 15, 'nonaktif', NULL, NULL, NULL, NULL),
(238, 8, 16, 'nonaktif', NULL, NULL, NULL, NULL),
(239, 8, 17, 'nonaktif', NULL, NULL, NULL, NULL),
(240, 8, 18, 'nonaktif', NULL, NULL, NULL, NULL),
(241, 8, 19, 'nonaktif', NULL, NULL, NULL, NULL),
(242, 8, 20, 'nonaktif', NULL, NULL, NULL, NULL),
(243, 8, 21, 'nonaktif', NULL, NULL, NULL, NULL),
(244, 8, 22, 'nonaktif', NULL, NULL, NULL, NULL),
(245, 8, 23, 'nonaktif', NULL, NULL, NULL, NULL),
(246, 8, 24, 'nonaktif', NULL, NULL, NULL, NULL),
(247, 8, 25, 'nonaktif', NULL, NULL, NULL, NULL),
(248, 8, 26, 'nonaktif', NULL, NULL, NULL, NULL),
(249, 8, 27, 'nonaktif', NULL, NULL, NULL, NULL),
(250, 8, 28, 'nonaktif', NULL, NULL, NULL, NULL),
(251, 8, 29, 'nonaktif', NULL, NULL, NULL, NULL),
(252, 8, 30, 'nonaktif', NULL, NULL, NULL, NULL),
(253, 8, 31, 'nonaktif', NULL, NULL, NULL, NULL),
(254, 8, 32, 'nonaktif', NULL, NULL, NULL, NULL),
(255, 8, 33, 'nonaktif', NULL, NULL, NULL, NULL),
(256, 8, 34, 'nonaktif', NULL, NULL, NULL, NULL),
(257, 8, 35, 'nonaktif', NULL, NULL, NULL, NULL),
(258, 8, 36, 'nonaktif', NULL, NULL, NULL, NULL),
(259, 8, 37, 'nonaktif', NULL, NULL, NULL, NULL),
(260, 8, 38, 'aktif', NULL, NULL, NULL, NULL),
(261, 8, 39, 'aktif', NULL, NULL, NULL, NULL),
(262, 8, 40, 'nonaktif', NULL, NULL, NULL, NULL),
(263, 8, 41, 'nonaktif', NULL, NULL, NULL, NULL),
(264, 8, 42, 'nonaktif', NULL, NULL, NULL, NULL),
(265, 8, 44, 'nonaktif', NULL, NULL, NULL, NULL),
(266, 8, 45, 'nonaktif', NULL, NULL, NULL, NULL),
(267, 9, 1, 'nonaktif', NULL, NULL, NULL, NULL),
(268, 9, 2, 'nonaktif', NULL, NULL, NULL, NULL),
(269, 9, 3, 'nonaktif', NULL, NULL, NULL, NULL),
(270, 9, 4, 'nonaktif', NULL, NULL, NULL, NULL),
(271, 9, 5, 'nonaktif', NULL, NULL, NULL, NULL),
(272, 9, 6, 'nonaktif', NULL, NULL, NULL, NULL),
(273, 9, 7, 'nonaktif', NULL, NULL, NULL, NULL),
(274, 9, 8, 'nonaktif', NULL, NULL, NULL, NULL),
(275, 9, 9, 'nonaktif', NULL, NULL, NULL, NULL),
(276, 9, 10, 'nonaktif', NULL, NULL, NULL, NULL),
(277, 9, 11, 'nonaktif', NULL, NULL, NULL, NULL),
(278, 9, 12, 'aktif', NULL, NULL, NULL, NULL),
(279, 9, 13, 'nonaktif', NULL, NULL, NULL, NULL),
(280, 9, 14, 'nonaktif', NULL, NULL, NULL, NULL),
(281, 9, 15, 'nonaktif', NULL, NULL, NULL, NULL),
(282, 9, 16, 'nonaktif', NULL, NULL, NULL, NULL),
(283, 9, 17, 'nonaktif', NULL, NULL, NULL, NULL),
(284, 9, 18, 'nonaktif', NULL, NULL, NULL, NULL),
(285, 9, 19, 'nonaktif', NULL, NULL, NULL, NULL),
(286, 9, 20, 'nonaktif', NULL, NULL, NULL, NULL),
(287, 9, 21, 'nonaktif', NULL, NULL, NULL, NULL),
(288, 9, 22, 'nonaktif', NULL, NULL, NULL, NULL),
(289, 9, 23, 'nonaktif', NULL, NULL, NULL, NULL),
(290, 9, 24, 'nonaktif', NULL, NULL, NULL, NULL),
(291, 9, 25, 'nonaktif', NULL, NULL, NULL, NULL),
(292, 9, 26, 'nonaktif', NULL, NULL, NULL, NULL),
(293, 9, 27, 'nonaktif', NULL, NULL, NULL, NULL),
(294, 9, 28, 'nonaktif', NULL, NULL, NULL, NULL),
(295, 9, 29, 'nonaktif', NULL, NULL, NULL, NULL),
(296, 9, 30, 'nonaktif', NULL, NULL, NULL, NULL),
(297, 9, 31, 'nonaktif', NULL, NULL, NULL, NULL),
(298, 9, 32, 'nonaktif', NULL, NULL, NULL, NULL),
(299, 9, 33, 'nonaktif', NULL, NULL, NULL, NULL),
(300, 9, 34, 'nonaktif', NULL, NULL, NULL, NULL),
(301, 9, 35, 'nonaktif', NULL, NULL, NULL, NULL),
(302, 9, 36, 'nonaktif', NULL, NULL, NULL, NULL),
(303, 9, 37, 'nonaktif', NULL, NULL, NULL, NULL),
(304, 9, 38, 'nonaktif', NULL, NULL, NULL, NULL),
(305, 9, 39, 'nonaktif', NULL, NULL, NULL, NULL),
(306, 9, 40, 'nonaktif', NULL, NULL, NULL, NULL),
(307, 9, 41, 'nonaktif', NULL, NULL, NULL, NULL),
(308, 9, 42, 'nonaktif', NULL, NULL, NULL, NULL),
(309, 9, 44, 'nonaktif', NULL, NULL, NULL, NULL),
(310, 9, 45, 'nonaktif', NULL, NULL, NULL, NULL);

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
-- Dumping data for table `tbl_penjualan_online`
--

INSERT INTO `tbl_penjualan_online` (`id_pk_penjualan_online`, `penj_on_marketplace`, `penj_on_no_resi`, `penj_on_kurir`, `penj_on_status`, `id_fk_penjualan`, `penj_on_create_date`, `penj_on_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, '1', '1234', 'JNE REG', 'AKTIF', 1, '2020-07-27 01:42:15', '2021-06-07 08:34:28', 2, 2),
(2, '2', '1234', 'JNE', 'AKTIF', 5, '2020-07-30 05:24:47', '2020-07-30 05:24:47', 2, 2),
(3, '1', '1234', 'JNE', 'AKTIF', 8, '2020-07-31 05:22:46', '2020-07-31 05:22:46', 2, 2),
(4, NULL, '123', 'Antar Aja', 'AKTIF', 4, '2021-06-05 05:15:16', '2021-06-09 01:50:40', 2, 2),
(5, NULL, '123', 'Antar Aja', 'AKTIF', 5, '2021-06-05 05:15:37', '2021-06-05 05:15:37', 2, 2),
(6, NULL, '123', 'Antar Aja', 'AKTIF', 6, '2021-06-05 05:20:40', '2021-06-05 05:32:37', 2, 2),
(7, NULL, '123123', 'Gojek', 'AKTIF', 7, '2021-06-05 05:30:49', '2021-10-16 08:03:38', 2, 4),
(8, '2', '1234', 'JNE REG', 'AKTIF', 8, '2021-06-05 05:34:52', '2021-06-05 05:34:52', 2, 2),
(9, '1', '1234', 'JNE REG', 'AKTIF', 1, '2021-06-05 05:38:14', '2021-06-07 08:34:28', 2, 2),
(10, '1', '123', 'Gojek', 'AKTIF', 2, '2021-06-05 05:46:37', '2021-06-05 07:48:34', 2, 2),
(11, '5', '12+46464545456', 'JNE YES', 'AKTIF', 3, '2021-06-09 01:46:58', '2021-06-09 01:49:21', 2, 2),
(12, NULL, '123', 'Antar Aja', 'AKTIF', 4, '2021-06-09 01:47:03', '2021-06-09 01:50:40', 2, 2),
(13, NULL, '1234', 'JNE REG', 'AKTIF', 5, '2021-06-26 12:25:45', '2021-06-26 12:25:45', 5, 5),
(14, NULL, '123', 'JNE REG', 'AKTIF', 6, '2021-10-16 08:01:06', '2021-10-16 08:01:06', 4, 4),
(15, NULL, '123123', 'Gojek', 'AKTIF', 7, '2021-10-16 08:02:21', '2021-10-16 08:03:38', 4, 4);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penjualan_pembayaran`
--

CREATE TABLE `tbl_penjualan_pembayaran` (
  `id_pk_penjualan_pembayaran` int(11) NOT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_nama` varchar(100) DEFAULT NULL,
  `penjualan_pmbyrn_persen` varchar(100) DEFAULT NULL COMMENT 'revisi table jadi metode pembayaran',
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
-- Dumping data for table `tbl_penjualan_pembayaran`
--

INSERT INTO `tbl_penjualan_pembayaran` (`id_pk_penjualan_pembayaran`, `id_fk_penjualan`, `penjualan_pmbyrn_nama`, `penjualan_pmbyrn_persen`, `penjualan_pmbyrn_nominal`, `penjualan_pmbyrn_notes`, `penjualan_pmbyrn_dateline`, `penjualan_pmbyrn_status`, `penjualan_pmbyrn_create_date`, `penjualan_pmbyrn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'Down Payment 1', 'Cash', 10000, '-', '2021-06-12 00:00:00', 'aktif', '2021-06-05 05:38:14', '2021-06-07 08:34:28', 2, 2),
(2, 1, 'Full Payment', 'Transfer', 12000, '-', '2021-06-19 00:00:00', 'belum lunas', '2021-06-05 05:38:14', '2021-06-07 08:34:28', 2, 2),
(3, 2, 'Down Payment 1', 'Transfer', 3000000, '-', '2021-06-24 00:00:00', 'aktif', '2021-06-05 05:46:37', '2021-06-05 07:48:34', 2, 2),
(4, 2, 'Tempo', 'Cash', 200000, '-', '2021-06-19 00:00:00', 'aktif', '2021-06-05 05:46:37', '2021-06-05 07:48:34', 2, 2),
(5, 3, 'Down Payment 1', 'Cash', 200000, '', '2021-06-18 00:00:00', 'aktif', '2021-06-09 01:46:58', '2021-06-09 01:49:21', 2, 2),
(6, 4, 'Down Payment 1', 'Cash', 200000, '', '2021-06-18 00:00:00', 'aktif', '2021-06-09 01:47:03', '2021-06-09 01:50:40', 2, 2),
(7, 3, 'Down Payment 2', 'Cash', 350000, '', '2021-06-11 00:00:00', 'aktif', '2021-06-09 01:48:56', '2021-06-09 01:49:21', 2, 2),
(8, 5, 'Down Payment 1', 'Cash', 1200000, '-', '2021-06-26 00:00:00', 'aktif', '2021-06-26 12:25:45', '2021-06-26 12:25:45', 5, 5),
(9, 5, 'Down Payment 1', 'Debit', 1000000, '-', '2021-07-10 00:00:00', 'aktif', '2021-06-26 12:25:45', '2021-06-26 12:25:45', 5, 5),
(10, 7, 'Full Payment', 'Cash', 1540000, '-', '2021-10-16 00:00:00', 'aktif', '2021-10-16 08:02:21', '2021-10-16 08:03:38', 4, 4);

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

--
-- Dumping data for table `tbl_retur_brg`
--

INSERT INTO `tbl_retur_brg` (`id_pk_retur_brg`, `id_fk_retur`, `id_fk_brg`, `retur_brg_qty`, `retur_brg_satuan`, `retur_brg_notes`, `retur_brg_status`, `retur_create_date`, `retur_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 14, 10, 'Pcs', '-', 'aktif', '2021-06-07 08:52:38', '2021-06-07 08:52:38', 2, 2),
(2, 1, 10, 10, 'Pcs', '-', 'aktif', '2021-06-07 08:52:38', '2021-06-07 08:52:38', 2, 2),
(3, 2, 14, 9, 'Pcs', '8', 'aktif', '2021-06-07 08:53:17', '2021-06-07 09:52:40', 2, 2),
(4, 2, 10, 0, 'Pcs', '8', 'aktif', '2021-06-07 08:53:17', '2021-06-07 09:52:40', 2, 2),
(5, 3, 14, 10, 'Pcs', '-', 'aktif', '2021-06-07 08:55:02', '2021-06-07 09:25:32', 2, 2),
(6, 3, 10, 1, 'Pcs', '-', 'nonaktif', '2021-06-07 08:55:02', '2021-06-07 09:25:31', 2, 2),
(7, 4, 10, 1, 'Pcs', '-', 'aktif', '2021-06-07 09:56:20', '2021-06-07 09:56:20', 2, 2),
(8, 4, 66, 1, 'Pcs', '-', 'aktif', '2021-06-07 09:56:20', '2021-06-07 09:56:20', 2, 2),
(9, 5, 2, 1, 'Pcs', 'rusak 1 ', 'aktif', '2021-06-09 02:00:12', '2021-06-09 02:00:57', 2, 2),
(10, 6, 2, 1, 'Pcs', '-', 'aktif', '2021-06-15 08:01:33', '2021-06-15 08:01:33', 5, 5),
(11, 7, 2, 1, 'Pcs', '-', 'aktif', '2021-06-15 08:01:40', '2021-06-15 08:01:40', 5, 5),
(12, 8, 2, 1, 'Pcs', '-', 'aktif', '2021-06-15 08:06:13', '2021-06-15 08:06:13', 5, 5),
(13, 9, 2, 1, 'Pcs', '-', 'aktif', '2021-06-15 08:06:22', '2021-06-15 08:06:22', 5, 5),
(14, 10, 23, 100, 'Pcs', '-', 'aktif', '2021-06-26 01:01:03', '2021-06-26 01:01:03', 5, 5),
(15, 10, 24, 100, 'Pcs', '-', 'aktif', '2021-06-26 01:01:03', '2021-06-26 01:01:03', 5, 5);

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
-- Dumping data for table `tbl_retur_kembali`
--

INSERT INTO `tbl_retur_kembali` (`id_pk_retur_kembali`, `retur_kembali_qty`, `retur_kembali_satuan`, `retur_kembali_harga`, `retur_kembali_note`, `retur_kembali_status`, `id_fk_retur`, `id_fk_brg`, `retur_kembali_create_date`, `retur_kembali_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 90, 'Pcs', 80, '9', 'aktif', 2, 9, '2021-06-07 08:53:17', '2021-06-07 09:52:40', 2, 2),
(2, 90, 'Pcs', 80, '9', 'aktif', 2, 10, '2021-06-07 08:53:17', '2021-06-07 09:52:40', 2, 2),
(3, 100, 'Pcs', 10, '0', 'aktif', 2, 66, '2021-06-07 08:54:10', '2021-06-07 09:52:40', 2, 2),
(4, 10, 'Pcs', 10, '0', 'aktif', 2, 14, '2021-06-07 09:52:40', '2021-06-07 09:52:40', 2, 2),
(5, 10, 'Pcs', 1000, '-', 'aktif', 6, 22, '2021-06-15 08:01:33', '2021-06-15 08:01:33', 5, 5),
(6, 10, 'Pcs', 1000, '-', 'aktif', 6, 2, '2021-06-15 08:01:33', '2021-06-15 08:01:33', 5, 5),
(7, 10, 'Pcs', 1000, '-', 'aktif', 7, 22, '2021-06-15 08:01:40', '2021-06-15 08:01:40', 5, 5),
(8, 10, 'Pcs', 1000, '-', 'aktif', 7, 2, '2021-06-15 08:01:40', '2021-06-15 08:01:40', 5, 5),
(9, 10, 'Pcs', 1000, '-', 'aktif', 8, 22, '2021-06-15 08:06:14', '2021-06-15 08:06:14', 5, 5),
(10, 10, 'Pcs', 1000, '-', 'aktif', 8, 2, '2021-06-15 08:06:14', '2021-06-15 08:06:14', 5, 5),
(11, 10, 'Pcs', 1000, '-', 'aktif', 9, 22, '2021-06-15 08:06:22', '2021-06-15 08:06:22', 5, 5),
(12, 10, 'Pcs', 1000, '-', 'aktif', 9, 2, '2021-06-15 08:06:22', '2021-06-15 08:06:22', 5, 5),
(13, 200, 'Pcs', 2000, '-', 'aktif', 10, 13, '2021-06-26 01:01:03', '2021-06-26 01:01:03', 5, 5),
(14, 300, 'Pcs', 3000, '-', 'aktif', 10, 15, '2021-06-26 01:01:03', '2021-06-26 01:01:03', 5, 5);

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
-- Dumping data for table `tbl_tambahan_pembelian`
--

INSERT INTO `tbl_tambahan_pembelian` (`id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_pembelian`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'TAMBAHAN 1', 1, 'Pcs', 12000, '-', 'AKTIF', 1, '2020-06-22 08:18:34', '0000-00-00 00:00:00', 20, 1),
(2, 'PARKIR', 1, 'Jam', 4000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1),
(3, 'Kurir', 1, 'Trip', 13000, '-', 'AKTIF', 3, '2020-06-22 05:28:46', '0000-00-00 00:00:00', 20, 1),
(4, 'tambahan1', 1, 'pcs', 123, '-', 'AKTIF', 4, '2020-07-17 10:40:25', '0000-00-00 00:00:00', 20, 2),
(5, 'Tambahan 1', 2, 'Pcs', 34000, '-', 'AKTIF', 23, '2020-07-25 12:44:13', '0000-00-00 00:00:00', 20, 2),
(6, 'Tambahan 2 ', 10, 'Pcs', 45000, '-', 'AKTIF', 23, '2020-07-25 01:23:53', '0000-00-00 00:00:00', 20, 2),
(7, 'Tambahan 1', 1000, 'Pcs', 5000, '-', 'nonaktif', 24, '2020-07-27 08:51:26', '0000-00-00 00:00:00', 20, 2),
(8, 'Tambahan 2', 400, 'Pcs', 23000, '-', 'AKTIF', 24, '2020-07-27 08:51:26', '0000-00-00 00:00:00', 20, 2),
(9, 'Tambahan 1 ', 100, 'Pcs', 1500, '-', 'AKTIF', 26, '2020-07-29 08:47:07', '0000-00-00 00:00:00', 20, 2),
(10, 'Tambahan 2', 111111, 'Pcs', 111111, '1111', 'AKTIF', 27, '2021-05-29 12:49:53', '0000-00-00 00:00:00', 21, 2),
(11, 'Tambahan1', 123123, 'Pcs', 123123, '123123', 'AKTIF', 28, '2021-05-29 12:50:54', '0000-00-00 00:00:00', 21, 2),
(12, 'tambahan1', 100000, 'Pcs', 100, '-', 'AKTIF', 29, '2021-06-10 08:19:48', '0000-00-00 00:00:00', 21, 5),
(13, 'tambahan11', 1000, 'Pcs', 1000, '-', 'AKTIF', 32, '2021-06-17 12:02:52', '0000-00-00 00:00:00', 21, 5);

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
-- Dumping data for table `tbl_tambahan_penjualan`
--

INSERT INTO `tbl_tambahan_penjualan` (`id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_penjualan`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Tambahan 1', 10, 'Pcs', 1000, '-', 'AKTIF', 1, '2021-06-05 05:38:14', '2021-06-07 08:34:28', 2, 2),
(2, 'Tambahan 2', 10, 'Pcs', 1000, '-', 'AKTIF', 1, '2021-06-05 05:38:14', '2021-06-07 08:34:28', 2, 2),
(3, 'Tambahan 1', 2000, 'Pcs', 100, '-', 'AKTIF', 2, '2021-06-05 05:46:37', '2021-06-05 07:48:34', 2, 2);

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
-- Dumping data for table `tbl_toko_admin`
--

INSERT INTO `tbl_toko_admin` (`id_pk_toko_admin`, `id_fk_toko`, `id_fk_user`, `toko_admin_status`, `toko_admin_create_date`, `toko_admin_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'AKTIF', '2020-06-21 11:44:24', '2020-06-21 11:44:24', 1, 1),
(2, 2, 1, 'AKTIF', '2020-06-22 02:59:25', '2020-06-22 02:59:25', 1, 1),
(3, 1, 2, 'nonaktif', '2020-06-22 05:20:09', '2021-06-09 01:06:03', 1, 3),
(4, 1, 3, 'nonaktif', '2020-06-22 05:20:09', '2021-04-24 03:57:36', 1, 2),
(5, 2, 2, 'nonaktif', '2020-06-22 06:47:48', '2021-06-09 01:06:18', 1, 3),
(6, 2, 3, 'nonaktif', '2020-06-22 06:47:48', '2021-06-09 01:06:21', 1, 3),
(7, 5, 5, 'AKTIF', '2021-06-09 01:05:15', '2021-06-09 01:05:15', 3, 3),
(8, 5, 4, 'AKTIF', '2021-06-09 01:05:15', '2021-06-09 01:05:15', 3, 3),
(9, 4, 4, 'AKTIF', '2021-06-09 01:05:30', '2021-06-09 01:05:30', 3, 3),
(10, 4, 5, 'AKTIF', '2021-06-09 01:05:30', '2021-06-09 01:05:30', 3, 3),
(11, 3, 4, 'AKTIF', '2021-06-09 01:05:49', '2021-06-09 01:05:49', 3, 3),
(12, 3, 5, 'AKTIF', '2021-06-09 01:05:49', '2021-06-09 01:05:49', 3, 3),
(13, 1, 4, 'AKTIF', '2021-06-09 01:06:08', '2021-06-09 01:06:08', 3, 3),
(14, 1, 5, 'AKTIF', '2021-06-09 01:06:08', '2021-06-09 01:06:08', 3, 3),
(15, 2, 4, 'AKTIF', '2021-06-09 01:06:26', '2021-06-09 01:06:26', 3, 3),
(16, 2, 2, 'nonaktif', '2021-06-09 01:06:26', '2021-06-09 01:06:51', 3, 3),
(17, 2, 5, 'AKTIF', '2021-06-09 01:06:56', '2021-06-09 01:06:56', 3, 3),
(18, 3, 2, 'AKTIF', '2021-06-09 01:11:32', '2021-06-09 01:11:32', 4, 4);

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
-- Dumping data for table `tbl_warehouse_admin`
--

INSERT INTO `tbl_warehouse_admin` (`id_pk_warehouse_admin`, `id_fk_warehouse`, `id_fk_user`, `warehouse_admin_status`, `warehouse_admin_create_date`, `warehouse_admin_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'AKTIF', '2020-06-21 11:45:53', '2020-06-21 11:45:53', 1, 1),
(2, 2, 1, 'AKTIF', '2020-07-05 11:06:55', '2020-07-05 11:06:55', 1, 1),
(3, 1, 2, 'AKTIF', '2020-07-15 08:17:49', '2020-07-15 08:17:49', 2, 2),
(4, 2, 2, 'AKTIF', '2020-07-15 08:19:36', '2020-07-15 08:19:36', 2, 2),
(5, 4, 2, 'AKTIF', '2021-04-17 09:59:38', '2021-04-17 09:59:38', 2, 2),
(6, 7, 2, 'AKTIF', '2021-04-24 02:53:54', '2021-04-24 02:53:54', 2, 2),
(7, 7, 2, 'AKTIF', '2021-04-24 02:54:27', '2021-04-24 02:54:27', 2, 2),
(8, 7, 2, 'AKTIF', '2021-04-24 02:57:05', '2021-04-24 02:57:05', 2, 2),
(9, 4, 3, 'AKTIF', '2021-04-24 03:27:35', '2021-04-24 03:27:35', 2, 2),
(10, 5, 5, 'AKTIF', '2021-06-09 09:32:49', '2021-06-09 09:32:49', 5, 5),
(11, 11, 5, 'AKTIF', '2021-06-09 10:16:00', '2021-06-09 10:16:00', 5, 5);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_brg_cabang_aktif`
-- (See below for the actual view)
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_brg_kombinasi_final`
-- (See below for the actual view)
--
-- --------------------------------------------------------

--
-- Stand-in structure for view `v_brg_warehouse_aktif`
-- (See below for the actual view)
--

-- --------------------------------------------------------

--
-- Structure for view `v_brg_cabang_aktif`
--
DROP TABLE IF EXISTS `v_brg_cabang_aktif`;

CREATE VIEW `v_brg_cabang_aktif` AS SELECT `tbl_brg_cabang`.`id_pk_brg_cabang` AS `id_pk_brg_cabang`, `tbl_brg_cabang`.`brg_cabang_qty` AS `brg_cabang_qty`, `tbl_brg_cabang`.`brg_cabang_status` AS `brg_cabang_status`, `tbl_brg_cabang`.`brg_cabang_last_price` AS `brg_cabang_last_price`, `tbl_brg_cabang`.`id_fk_brg` AS `id_fk_brg`, `tbl_brg_cabang`.`id_fk_cabang` AS `id_fk_cabang`, `mstr_barang`.`brg_nama` AS `brg_nama` FROM (`tbl_brg_cabang` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_brg_cabang`.`id_fk_brg`)) WHERE `tbl_brg_cabang`.`brg_cabang_status` = 'aktif' AND `mstr_barang`.`brg_status` = 'aktif' ORDER BY `tbl_brg_cabang`.`id_fk_brg` ASC, `tbl_brg_cabang`.`id_fk_cabang` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_brg_kombinasi_final`
--
DROP TABLE IF EXISTS `v_brg_kombinasi_final`;

CREATE VIEW `v_brg_kombinasi_final` AS SELECT `tbl_barang_kombinasi`.`id_pk_barang_kombinasi` AS `id_pk_barang_kombinasi`, `tbl_barang_kombinasi`.`id_barang_utama` AS `id_barang_utama`, `tbl_barang_kombinasi`.`id_barang_kombinasi` AS `id_barang_kombinasi`, sum(`tbl_barang_kombinasi`.`barang_kombinasi_qty`) AS `barang_kombinasi_qty`, `tbl_barang_kombinasi`.`barang_kombinasi_status` AS `barang_kombinasi_status` FROM (`tbl_barang_kombinasi` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_barang_kombinasi`.`id_barang_kombinasi`)) WHERE `tbl_barang_kombinasi`.`barang_kombinasi_status` = 'aktif' AND `mstr_barang`.`brg_status` = 'aktif' GROUP BY `tbl_barang_kombinasi`.`id_barang_utama`, `tbl_barang_kombinasi`.`id_barang_kombinasi` ;

-- --------------------------------------------------------

--
-- Structure for view `v_brg_warehouse_aktif`
--
DROP TABLE IF EXISTS `v_brg_warehouse_aktif`;

CREATE VIEW `v_brg_warehouse_aktif` AS SELECT `tbl_brg_warehouse`.`id_pk_brg_warehouse` AS `id_pk_brg_warehouse`, `tbl_brg_warehouse`.`brg_warehouse_qty` AS `brg_warehouse_qty`, `tbl_brg_warehouse`.`brg_warehouse_status` AS `brg_warehouse_status`, `tbl_brg_warehouse`.`id_fk_brg` AS `id_fk_brg`, `tbl_brg_warehouse`.`id_fk_warehouse` AS `id_fk_warehouse`, `mstr_barang`.`brg_nama` AS `brg_nama` FROM (`tbl_brg_warehouse` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_brg_warehouse`.`id_fk_brg`)) WHERE `tbl_brg_warehouse`.`brg_warehouse_status` = 'aktif' AND `mstr_barang`.`brg_status` = 'aktif' ORDER BY `tbl_brg_warehouse`.`id_fk_brg` ASC, `tbl_brg_warehouse`.`id_fk_warehouse` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log_all`
--
ALTER TABLE `log_all`
  ADD PRIMARY KEY (`id_pk_log_all`);

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
-- Indexes for table `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  ADD PRIMARY KEY (`id_pk_brg_merk`);

--
-- Indexes for table `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  ADD PRIMARY KEY (`id_pk_cabang`);

--
-- Indexes for table `mstr_customer`
--
ALTER TABLE `mstr_customer`
  ADD PRIMARY KEY (`id_pk_cust`);

--
-- Indexes for table `mstr_employee`
--
ALTER TABLE `mstr_employee`
  ADD PRIMARY KEY (`id_pk_employee`);

--
-- Indexes for table `mstr_jabatan`
--
ALTER TABLE `mstr_jabatan`
  ADD PRIMARY KEY (`id_pk_jabatan`);

--
-- Indexes for table `mstr_marketplace`
--
ALTER TABLE `mstr_marketplace`
  ADD PRIMARY KEY (`id_pk_marketplace`);

--
-- Indexes for table `mstr_menu`
--
ALTER TABLE `mstr_menu`
  ADD PRIMARY KEY (`id_pk_menu`);

--
-- Indexes for table `mstr_pembelian`
--
ALTER TABLE `mstr_pembelian`
  ADD PRIMARY KEY (`id_pk_pembelian`);

--
-- Indexes for table `mstr_penawaran`
--
ALTER TABLE `mstr_penawaran`
  ADD PRIMARY KEY (`id_pk_penawaran`);

--
-- Indexes for table `mstr_penerimaan`
--
ALTER TABLE `mstr_penerimaan`
  ADD PRIMARY KEY (`id_pk_penerimaan`);

--
-- Indexes for table `mstr_pengiriman`
--
ALTER TABLE `mstr_pengiriman`
  ADD PRIMARY KEY (`id_pk_pengiriman`);

--
-- Indexes for table `mstr_penjualan`
--
ALTER TABLE `mstr_penjualan`
  ADD PRIMARY KEY (`id_pk_penjualan`);

--
-- Indexes for table `mstr_retur`
--
ALTER TABLE `mstr_retur`
  ADD PRIMARY KEY (`id_pk_retur`);

--
-- Indexes for table `mstr_satuan`
--
ALTER TABLE `mstr_satuan`
  ADD PRIMARY KEY (`id_pk_satuan`);

--
-- Indexes for table `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  ADD PRIMARY KEY (`id_pk_sup`);

--
-- Indexes for table `mstr_toko`
--
ALTER TABLE `mstr_toko`
  ADD PRIMARY KEY (`id_pk_toko`);

--
-- Indexes for table `mstr_user`
--
ALTER TABLE `mstr_user`
  ADD PRIMARY KEY (`id_pk_user`);

--
-- Indexes for table `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  ADD PRIMARY KEY (`id_pk_warehouse`);

--
-- Indexes for table `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  ADD PRIMARY KEY (`id_pk_barang_kombinasi`);

--
-- Indexes for table `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  ADD PRIMARY KEY (`id_pk_brg_cabang`);

--
-- Indexes for table `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  ADD PRIMARY KEY (`id_pk_brg_pembelian`);

--
-- Indexes for table `tbl_brg_pemenuhan`
--
ALTER TABLE `tbl_brg_pemenuhan`
  ADD PRIMARY KEY (`id_pk_brg_pemenuhan`);

--
-- Indexes for table `tbl_brg_penawaran`
--
ALTER TABLE `tbl_brg_penawaran`
  ADD PRIMARY KEY (`id_pk_brg_penawaran`);

--
-- Indexes for table `tbl_brg_penerimaan`
--
ALTER TABLE `tbl_brg_penerimaan`
  ADD PRIMARY KEY (`id_pk_brg_penerimaan`);

--
-- Indexes for table `tbl_brg_pengiriman`
--
ALTER TABLE `tbl_brg_pengiriman`
  ADD PRIMARY KEY (`id_pk_brg_pengiriman`);

--
-- Indexes for table `tbl_brg_penjualan`
--
ALTER TABLE `tbl_brg_penjualan`
  ADD PRIMARY KEY (`id_pk_brg_penjualan`);

--
-- Indexes for table `tbl_brg_permintaan`
--
ALTER TABLE `tbl_brg_permintaan`
  ADD PRIMARY KEY (`id_pk_brg_permintaan`);

--
-- Indexes for table `tbl_brg_pindah`
--
ALTER TABLE `tbl_brg_pindah`
  ADD PRIMARY KEY (`id_pk_brg_pindah`);

--
-- Indexes for table `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  ADD PRIMARY KEY (`id_pk_brg_warehouse`);

--
-- Indexes for table `tbl_cabang_admin`
--
ALTER TABLE `tbl_cabang_admin`
  ADD PRIMARY KEY (`id_pk_cabang_admin`);

--
-- Indexes for table `tbl_hak_akses`
--
ALTER TABLE `tbl_hak_akses`
  ADD PRIMARY KEY (`id_pk_hak_akses`);

--
-- Indexes for table `tbl_penjualan_online`
--
ALTER TABLE `tbl_penjualan_online`
  ADD PRIMARY KEY (`id_pk_penjualan_online`);

--
-- Indexes for table `tbl_penjualan_pembayaran`
--
ALTER TABLE `tbl_penjualan_pembayaran`
  ADD PRIMARY KEY (`id_pk_penjualan_pembayaran`);

--
-- Indexes for table `tbl_retur_brg`
--
ALTER TABLE `tbl_retur_brg`
  ADD PRIMARY KEY (`id_pk_retur_brg`);

--
-- Indexes for table `tbl_retur_kembali`
--
ALTER TABLE `tbl_retur_kembali`
  ADD PRIMARY KEY (`id_pk_retur_kembali`);

--
-- Indexes for table `tbl_tambahan_pembelian`
--
ALTER TABLE `tbl_tambahan_pembelian`
  ADD PRIMARY KEY (`id_pk_tmbhn`);

--
-- Indexes for table `tbl_tambahan_penjualan`
--
ALTER TABLE `tbl_tambahan_penjualan`
  ADD PRIMARY KEY (`id_pk_tmbhn`);

--
-- Indexes for table `tbl_toko_admin`
--
ALTER TABLE `tbl_toko_admin`
  ADD PRIMARY KEY (`id_pk_toko_admin`);

--
-- Indexes for table `tbl_warehouse_admin`
--
ALTER TABLE `tbl_warehouse_admin`
  ADD PRIMARY KEY (`id_pk_warehouse_admin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log_all`
--
ALTER TABLE `log_all`
  MODIFY `id_pk_log_all` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=456;

--
-- AUTO_INCREMENT for table `mstr_barang`
--
ALTER TABLE `mstr_barang`
  MODIFY `id_pk_brg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  MODIFY `id_pk_brg_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  MODIFY `id_pk_brg_merk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  MODIFY `id_pk_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mstr_customer`
--
ALTER TABLE `mstr_customer`
  MODIFY `id_pk_cust` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3740;

--
-- AUTO_INCREMENT for table `mstr_employee`
--
ALTER TABLE `mstr_employee`
  MODIFY `id_pk_employee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mstr_jabatan`
--
ALTER TABLE `mstr_jabatan`
  MODIFY `id_pk_jabatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mstr_marketplace`
--
ALTER TABLE `mstr_marketplace`
  MODIFY `id_pk_marketplace` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mstr_menu`
--
ALTER TABLE `mstr_menu`
  MODIFY `id_pk_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `mstr_pembelian`
--
ALTER TABLE `mstr_pembelian`
  MODIFY `id_pk_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `mstr_penawaran`
--
ALTER TABLE `mstr_penawaran`
  MODIFY `id_pk_penawaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mstr_penerimaan`
--
ALTER TABLE `mstr_penerimaan`
  MODIFY `id_pk_penerimaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `mstr_pengiriman`
--
ALTER TABLE `mstr_pengiriman`
  MODIFY `id_pk_pengiriman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `mstr_penjualan`
--
ALTER TABLE `mstr_penjualan`
  MODIFY `id_pk_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mstr_retur`
--
ALTER TABLE `mstr_retur`
  MODIFY `id_pk_retur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `mstr_satuan`
--
ALTER TABLE `mstr_satuan`
  MODIFY `id_pk_satuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  MODIFY `id_pk_sup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mstr_toko`
--
ALTER TABLE `mstr_toko`
  MODIFY `id_pk_toko` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mstr_user`
--
ALTER TABLE `mstr_user`
  MODIFY `id_pk_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  MODIFY `id_pk_warehouse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  MODIFY `id_pk_barang_kombinasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  MODIFY `id_pk_brg_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  MODIFY `id_pk_brg_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tbl_brg_pemenuhan`
--
ALTER TABLE `tbl_brg_pemenuhan`
  MODIFY `id_pk_brg_pemenuhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tbl_brg_penawaran`
--
ALTER TABLE `tbl_brg_penawaran`
  MODIFY `id_pk_brg_penawaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_brg_penerimaan`
--
ALTER TABLE `tbl_brg_penerimaan`
  MODIFY `id_pk_brg_penerimaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `tbl_brg_pengiriman`
--
ALTER TABLE `tbl_brg_pengiriman`
  MODIFY `id_pk_brg_pengiriman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `tbl_brg_penjualan`
--
ALTER TABLE `tbl_brg_penjualan`
  MODIFY `id_pk_brg_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_brg_permintaan`
--
ALTER TABLE `tbl_brg_permintaan`
  MODIFY `id_pk_brg_permintaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tbl_brg_pindah`
--
ALTER TABLE `tbl_brg_pindah`
  MODIFY `id_pk_brg_pindah` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  MODIFY `id_pk_brg_warehouse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_cabang_admin`
--
ALTER TABLE `tbl_cabang_admin`
  MODIFY `id_pk_cabang_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_hak_akses`
--
ALTER TABLE `tbl_hak_akses`
  MODIFY `id_pk_hak_akses` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=311;

--
-- AUTO_INCREMENT for table `tbl_penjualan_online`
--
ALTER TABLE `tbl_penjualan_online`
  MODIFY `id_pk_penjualan_online` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_penjualan_pembayaran`
--
ALTER TABLE `tbl_penjualan_pembayaran`
  MODIFY `id_pk_penjualan_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_retur_brg`
--
ALTER TABLE `tbl_retur_brg`
  MODIFY `id_pk_retur_brg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_retur_kembali`
--
ALTER TABLE `tbl_retur_kembali`
  MODIFY `id_pk_retur_kembali` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_tambahan_pembelian`
--
ALTER TABLE `tbl_tambahan_pembelian`
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_tambahan_penjualan`
--
ALTER TABLE `tbl_tambahan_penjualan`
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_toko_admin`
--
ALTER TABLE `tbl_toko_admin`
  MODIFY `id_pk_toko_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_warehouse_admin`
--
ALTER TABLE `tbl_warehouse_admin`
  MODIFY `id_pk_warehouse_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
