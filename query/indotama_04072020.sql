-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2020 at 05:56 PM
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
CREATE PROCEDURE `check_and_auto_insert_brg_cabang` (IN `id_fk_brg_in` INT, IN `id_fk_cabang_in` INT, IN `id_create_data_in` INT)  begin
            set @a = "";
            select count(id_pk_brg_cabang) into @a from tbl_brg_cabang where id_fk_brg = id_fk_brg_in and id_fk_cabang = id_fk_cabang_in;

            if !@a>0
            then
                insert into tbl_brg_cabang 
                values (null,0,'Auto insert from database before penerimaan barang','aktif',0,id_fk_brg_in,id_fk_cabang_in,now(),now(),id_create_data_in,id_create_data_in);
            end if;
        end$$

CREATE PROCEDURE `insert_log_all` (IN `id_user` INT, IN `log_date` DATETIME, IN `log_text` VARCHAR(100), OUT `id_log_all` INT)  begin
	insert into log_all(id_user,log_date,log) values(id_user,log_date,log_text);
    select last_insert_id() into id_log_all ;
end$$

CREATE PROCEDURE `list_barang_kombinasi_cabang` ()  begin

declare finished int default 0;
declare id_barang_utama_var int default 0;
declare id_cabang_var int default 0;
    
declare brg_kombinasi_cur cursor for 
select id_barang_utama,id_fk_cabang
from tbl_barang_kombinasi
inner join mstr_barang on mstr_barang.id_pk_brg = tbl_barang_kombinasi.id_barang_utama
inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = mstr_barang.id_pk_brg
where mstr_barang.brg_status = 'aktif' 
and tbl_barang_kombinasi.barang_kombinasi_status = 'aktif' 
and tbl_brg_cabang.brg_cabang_status = 'aktif'
group by id_barang_utama,id_fk_cabang
/*supaya urutan dari yang paling awal dibuat, hingga yang akhir dibuat sehingga apabila terdapat kombinasi yang merupakan gabungan dari kombinasi lainnya jadi bisa terupdate dahulu sehingga dapat berjalan 1x. kalau tidak diurutkan berdasarkan id_pk_brg, maka dapat saja kombinasi yang terakhir terupdate terlebih dahulu daripada anggotanya menjadi tidak akurat. prinsipnya, update anggota dahulu sampe beres, baru update kombinasi lain yang menggunakan kombinasi sebelumnya*/
order by id_pk_brg,id_fk_cabang;

declare continue handler 
for not found set finished = 1;

open brg_kombinasi_cur;
mstr_kombinasi_loop:LOOP
	fetch brg_kombinasi_cur into id_barang_utama_var,id_cabang_var;
    
    call update_latest_stok_mstr_brg_kombinasi(id_barang_utama_var,id_cabang_var,@new_stok);
    if finished = 1 then
		leave mstr_kombinasi_loop;
	end if;
    
    update tbl_brg_cabang set brg_cabang_qty = @new_stok 
    where id_fk_brg = id_barang_utama_var
    and id_fk_cabang = id_cabang_var;
    
END LOOP mstr_kombinasi_loop;
end$$

CREATE PROCEDURE `ubah_satuan_barang` (IN `id_satuan_in` INT, INOUT `brg_qty` DOUBLE)  begin
            declare conversion_exp varchar(20);
            select satuan_rumus 
            into conversion_exp
            from mstr_satuan
            where id_pk_satuan = id_satuan_in;
            
            set brg_qty = conversion_exp * brg_qty;
            
        end$$

CREATE PROCEDURE `update_latest_stok_mstr_brg_kombinasi` (IN `id_barang_utama_in` INT, IN `id_fk_cabang_in` INT, OUT `new_stok_in` DOUBLE)  begin
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

            call update_stok_kombinasi_barang_cabang(id_barang,barang_masuk, barang_keluar, id_cabang);

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
        end$$

CREATE PROCEDURE `update_stok_kombinasi_barang_cabang` (IN `id_barang_utama_in` INT, IN `qty_brg_masuk_in` DOUBLE, IN `qty_brg_keluar_in` DOUBLE, IN `id_cabang_in` INT)  begin
            update tbl_barang_kombinasi
            inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
            set brg_cabang_qty = brg_cabang_qty+(barang_kombinasi_qty*qty_brg_masuk_in)-(barang_kombinasi_qty*qty_brg_keluar_in)
            where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in and barang_kombinasi_status = 'aktif';
        end$$

CREATE PROCEDURE `use_combinasi_barang` (IN `id_barang_utama_in` INT, IN `qty_brg_masuk_in` DOUBLE, IN `id_cabang_in` INT, IN `jenis_transaksi` VARCHAR(15))  begin
	if jenis_transaksi = "penerimaan"
    then 
		update tbl_barang_kombinasi
		inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
		set brg_cabang_qty = brg_cabang_qty+(barang_kombinasi_qty*qty_brg_masuk_in)
		where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in;
    elseif jenis_transaksi = "pengiriman"
    then
		update tbl_barang_kombinasi
		inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
		set brg_cabang_qty = brg_cabang_qty-(barang_kombinasi_qty*qty_brg_masuk_in)
		where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in;
    end if;
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
  `log` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `log_all`
--

INSERT INTO `log_all` (`id_log_all`, `id_user`, `log_date`, `log`) VALUES
(1, 1, '2020-06-21 11:16:34', '1 INSERT DATA AT2020-06-21 11:16:34'),
(2, 0, '2020-06-21 23:26:35', '0 insert data at 2020-06-21 23:26:35'),
(3, 1, '2020-06-21 11:27:06', '1 insert data at2020-06-21 11:27:06'),
(4, 1, '2020-06-21 11:28:42', '1 insert data at2020-06-21 11:28:42'),
(5, 1, '2020-06-21 11:28:57', '1 insert data at 2020-06-21 11:28:57'),
(6, 1, '2020-06-21 11:28:57', '1 insert data at2020-06-21 11:28:57'),
(7, 1, '2020-06-21 11:28:57', '1 insert data at2020-06-21 11:28:57'),
(8, 1, '2020-06-21 11:29:04', '1 update data at 2020-06-21 11:29:04'),
(9, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(10, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(11, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(12, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(13, 1, '2020-06-21 11:38:11', '1 insert data at2020-06-21 11:38:11'),
(14, 1, '2020-06-21 11:38:11', '1 insert data at2020-06-21 11:38:11'),
(15, 1, '2020-06-21 11:38:23', '1 insert data at2020-06-21 11:38:23'),
(16, 1, '2020-06-21 11:38:23', '1 insert data at2020-06-21 11:38:23'),
(17, 1, '2020-06-21 11:38:35', '1 insert data at2020-06-21 11:38:35'),
(18, 1, '2020-06-21 11:38:35', '1 insert data at2020-06-21 11:38:35'),
(19, 1, '2020-06-21 11:38:44', '1 insert data at2020-06-21 11:38:44'),
(20, 1, '2020-06-21 11:38:44', '1 insert data at2020-06-21 11:38:44'),
(21, 1, '2020-06-21 11:38:54', '1 insert data at2020-06-21 11:38:54'),
(22, 1, '2020-06-21 11:38:54', '1 insert data at2020-06-21 11:38:54'),
(23, 1, '2020-06-21 11:39:36', '1 insert data at2020-06-21 11:39:36'),
(24, 1, '2020-06-21 11:39:36', '1 insert data at2020-06-21 11:39:36'),
(25, 1, '2020-06-21 11:40:07', '1 insert data at2020-06-21 11:40:07'),
(26, 1, '2020-06-21 11:40:07', '1 insert data at2020-06-21 11:40:07'),
(27, 1, '2020-06-21 11:40:15', '1 update data at2020-06-21 11:40:15'),
(28, 1, '2020-06-21 11:40:52', '1 insert data at2020-06-21 11:40:52'),
(29, 1, '2020-06-21 11:40:52', '1 insert data at2020-06-21 11:40:52'),
(30, 1, '2020-06-21 11:41:04', '1 insert data at2020-06-21 11:41:04'),
(31, 1, '2020-06-21 11:41:04', '1 insert data at2020-06-21 11:41:04'),
(32, 1, '2020-06-21 11:41:23', '1 insert data at2020-06-21 11:41:23'),
(33, 1, '2020-06-21 11:41:23', '1 insert data at2020-06-21 11:41:23'),
(34, 1, '2020-06-21 11:41:33', '1 insert data at2020-06-21 11:41:33'),
(35, 1, '2020-06-21 11:41:33', '1 insert data at2020-06-21 11:41:33'),
(36, 1, '2020-06-21 11:41:42', '1 insert data at2020-06-21 11:41:42'),
(37, 1, '2020-06-21 11:41:42', '1 insert data at2020-06-21 11:41:42'),
(38, 1, '2020-06-21 11:41:58', '1 insert data at2020-06-21 11:41:58'),
(39, 1, '2020-06-21 11:41:58', '1 insert data at2020-06-21 11:41:58'),
(40, 1, '2020-06-21 11:42:07', '1 insert data at2020-06-21 11:42:07'),
(41, 1, '2020-06-21 11:42:07', '1 insert data at2020-06-21 11:42:07'),
(42, 1, '2020-06-21 11:42:16', '1 insert data at2020-06-21 11:42:16'),
(43, 1, '2020-06-21 11:42:16', '1 insert data at2020-06-21 11:42:16'),
(44, 1, '2020-06-21 11:42:28', '1 insert data at2020-06-21 11:42:28'),
(45, 1, '2020-06-21 11:42:28', '1 insert data at2020-06-21 11:42:28'),
(46, 1, '2020-06-21 11:42:37', '1 insert data at2020-06-21 11:42:37'),
(47, 1, '2020-06-21 11:42:37', '1 insert data at2020-06-21 11:42:37'),
(48, 1, '2020-06-21 11:42:53', '1 update data at 2020-06-21 11:42:53'),
(49, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(50, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(51, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(52, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(53, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(54, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(55, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(56, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(57, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(58, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(59, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(60, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(61, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(62, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(63, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(64, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(65, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(66, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(67, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(68, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(69, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(70, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(71, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(72, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(73, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(74, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(75, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(76, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(77, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(78, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(79, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(80, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(81, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(82, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(83, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(84, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(85, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(86, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(87, 1, '2020-06-21 11:43:08', '1 update data at2020-06-21 11:43:08'),
(88, 1, '2020-06-21 11:44:14', '1 insert data at 2020-06-21 11:44:14'),
(89, 1, '2020-06-21 11:44:24', '1 insert data at2020-06-21 11:44:24'),
(90, 1, '2020-06-21 11:44:49', '1 insert data at2020-06-21 11:44:49'),
(91, 1, '2020-06-21 11:45:03', '1 insert data at2020-06-21 11:45:03'),
(92, 1, '2020-06-21 11:45:42', '1 insert data at2020-06-21 11:45:42'),
(93, 1, '2020-06-21 11:45:53', '1 insert data at2020-06-21 11:45:53'),
(94, 1, '2020-06-22 12:12:04', '1 insert data at2020-06-22 12:12:04'),
(95, 1, '2020-06-22 12:12:04', '1 insert data at2020-06-22 12:12:04'),
(96, 1, '2020-06-22 07:50:23', '1 insert data at2020-06-22 07:50:23'),
(97, 1, '2020-06-22 07:50:23', '1 insert data at2020-06-22 07:50:23'),
(98, 1, '2020-06-22 07:50:39', '1 update data at 2020-06-22 07:50:39'),
(99, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(100, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(101, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(102, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(103, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(104, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(105, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(106, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(107, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(108, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(109, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(110, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(111, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(112, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(113, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(114, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(115, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(116, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(117, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(118, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(119, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(120, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(121, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(122, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(123, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(124, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(125, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(126, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(127, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(128, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(129, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(130, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(131, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(132, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(133, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(134, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(135, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(136, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(137, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(138, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(139, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(140, 1, '2020-06-22 07:51:13', '1 insert data at 2020-06-22 07:51:13'),
(141, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(142, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(143, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(144, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(145, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(146, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(147, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(148, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(149, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(150, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(151, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(152, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(153, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(154, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(155, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(156, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(157, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(158, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(159, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(160, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(161, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(162, 1, '2020-06-22 07:51:42', '1 update data at 2020-06-22 07:51:42'),
(163, 1, '2020-06-22 07:53:15', '1 insert data at 2020-06-22 07:53:15'),
(164, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(165, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(166, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(167, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(168, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(169, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(170, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(171, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(172, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(173, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(174, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(175, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(176, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(177, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(178, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(179, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(180, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(181, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(182, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(183, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(184, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(185, 1, '2020-06-22 08:02:21', '1 insert data at 2020-06-22 08:02:21'),
(186, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(187, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(188, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(189, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(190, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(191, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(192, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(193, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(194, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(195, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(196, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(197, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(198, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(199, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(200, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(201, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(202, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(203, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(204, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(205, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(206, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(207, 1, '2020-06-22 08:02:21', '1 update data at2020-06-22 08:02:21'),
(208, 1, '2020-06-22 08:02:21', '1 update data at2020-06-22 08:02:21'),
(209, 1, '2020-06-22 08:03:15', '1 insert data at2020-06-22 08:03:15'),
(210, 1, '2020-06-22 08:03:15', '1 insert data at2020-06-22 08:03:15'),
(211, 1, '2020-06-22 08:03:15', '1 insert data at2020-06-22 08:03:15'),
(212, 1, '2020-06-22 08:03:23', '1 insert data at2020-06-22 08:03:23'),
(213, 1, '2020-06-22 08:03:23', '1 insert data at2020-06-22 08:03:23'),
(214, 1, '2020-06-22 08:03:23', '1 insert data at2020-06-22 08:03:23'),
(215, 1, '2020-06-22 08:03:32', '1 insert data at2020-06-22 08:03:32'),
(216, 1, '2020-06-22 08:03:32', '1 insert data at2020-06-22 08:03:32'),
(217, 1, '2020-06-22 08:03:32', '1 insert data at2020-06-22 08:03:32'),
(218, 1, '2020-06-22 08:03:53', '1 insert data at 2020-06-22 08:03:53'),
(219, 1, '2020-06-22 08:04:32', '1 insert data at 2020-06-22 08:04:32'),
(220, 1, '2020-06-22 08:04:32', '1 insert data at 2020-06-22 08:04:32'),
(221, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(222, 1, '2020-06-22 08:07:25', '1 update data at 2020-06-22 08:07:25'),
(223, 1, '2020-06-22 08:07:27', '1 update data at 2020-06-22 08:07:27'),
(224, 1, '2020-06-22 08:07:40', '1 insert data at 2020-06-22 08:07:40'),
(225, 1, '2020-06-22 08:07:40', '1 insert data at 2020-06-22 08:07:40'),
(226, 1, '2020-06-22 08:07:40', '1 insert data at 2020-06-22 08:07:40'),
(227, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(228, 1, '2020-06-22 08:08:58', '1 update data at 2020-06-22 08:08:58'),
(229, 1, '2020-06-22 08:09:00', '1 update data at 2020-06-22 08:09:00'),
(230, 1, '2020-06-22 08:09:14', '1 insert data at 2020-06-22 08:09:14'),
(231, 1, '2020-06-22 08:09:14', '1 insert data at 2020-06-22 08:09:14'),
(232, 1, '2020-06-22 08:09:14', '1 insert data at 2020-06-22 08:09:14'),
(233, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(234, 1, '2020-06-22 08:10:06', '1 update data at 2020-06-22 08:10:06'),
(235, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(236, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(237, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(238, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(239, 1, '2020-06-22 08:18:34', '1 update data at2020-06-22 08:18:34'),
(240, 1, '2020-06-22 08:18:34', '1 update data at2020-06-22 08:18:34'),
(241, 1, '2020-06-22 08:18:34', '1 update data at2020-06-22 08:18:34'),
(242, 1, '0000-00-00 00:00:00', '1 insert data at0000-00-00 00:00:00'),
(243, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(244, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(245, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(246, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(247, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(248, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(249, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(250, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(251, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(252, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(253, 1, '0000-00-00 00:00:00', '1 insert data at0000-00-00 00:00:00'),
(254, 1, '2020-06-22 08:26:54', '1 update data at2020-06-22 08:26:54'),
(255, 1, '2020-06-22 08:26:54', '1 update data at2020-06-22 08:26:54'),
(256, 1, '2020-06-22 08:26:54', '1 update data at2020-06-22 08:26:54'),
(257, 1, '0000-00-00 00:00:00', '1 update data at0000-00-00 00:00:00'),
(258, 1, '2020-06-22 08:27:08', '1 update data at2020-06-22 08:27:08'),
(259, 1, '2020-06-22 08:27:08', '1 update data at2020-06-22 08:27:08'),
(260, 1, '2020-06-22 08:27:08', '1 update data at2020-06-22 08:27:08'),
(261, 1, '2020-06-22 08:27:08', '1 insert data at2020-06-22 08:27:08'),
(262, 1, '0000-00-00 00:00:00', '1 update data at0000-00-00 00:00:00'),
(263, 1, '2020-06-22 08:27:18', '1 update data at2020-06-22 08:27:18'),
(264, 1, '2020-06-22 08:36:13', '1 insert data at2020-06-22 08:36:13'),
(265, 1, '2020-06-22 08:36:19', '1 insert data at2020-06-22 08:36:19'),
(266, 1, '2020-06-22 08:36:23', '1 insert data at2020-06-22 08:36:23'),
(267, 1, '2020-06-22 08:52:15', '1 insert data at2020-06-22 08:52:15'),
(268, 1, '2020-06-22 08:52:15', '1 insert data at2020-06-22 08:52:15'),
(269, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(270, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(271, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(272, 1, '2020-06-22 08:52:15', '1 insert data at2020-06-22 08:52:15'),
(273, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(274, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(275, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(276, 1, '2020-06-22 08:52:58', '1 update data at2020-06-22 08:52:58'),
(277, 1, '2020-06-22 08:52:58', '1 update data at2020-06-22 08:52:58'),
(278, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(279, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(280, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(281, 1, '2020-06-22 08:52:58', '1 update data at2020-06-22 08:52:58'),
(282, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(283, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(284, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(285, 1, '2020-06-22 08:54:59', '1 update data at2020-06-22 08:54:59'),
(286, 1, '2020-06-22 08:54:59', '1 update data at2020-06-22 08:54:59'),
(287, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(288, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(289, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(290, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(291, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(292, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(293, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(294, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(295, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(296, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(297, 1, '2020-06-22 09:00:41', '1 update data at2020-06-22 09:00:41'),
(298, 1, '2020-06-22 09:00:41', '1 update data at2020-06-22 09:00:41'),
(299, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(300, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(301, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(302, 1, '2020-06-22 09:00:41', '1 update data at2020-06-22 09:00:41'),
(303, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(304, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(305, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(306, 1, '2020-06-22 09:00:47', '1 update data at2020-06-22 09:00:47'),
(307, 1, '2020-06-22 09:00:47', '1 update data at2020-06-22 09:00:47'),
(308, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(309, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(310, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(311, 1, '2020-06-22 09:00:47', '1 update data at2020-06-22 09:00:47'),
(312, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(313, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(314, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(315, 1, '2020-06-22 09:08:48', '1 insert data at2020-06-22 09:08:48'),
(316, 1, '2020-06-22 09:38:00', '1 insert data at2020-06-22 09:38:00'),
(317, 1, '2020-06-22 09:39:50', '1 insert data at 2020-06-22 09:39:50'),
(318, 1, '2020-06-22 09:39:50', '1 insert data at2020-06-22 09:39:50'),
(319, 1, '2020-06-22 09:39:50', '1 insert data at2020-06-22 09:39:50'),
(320, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(321, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(322, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(323, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(324, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(325, 1, '2020-06-22 09:38:00', '1 update data at2020-06-22 09:38:00'),
(326, 1, '2020-06-22 09:53:30', '1 update data at2020-06-22 09:53:30'),
(327, 1, '2020-06-22 09:53:30', '1 update data at2020-06-22 09:53:30'),
(328, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(329, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(330, 1, '2020-06-22 09:55:58', '1 insert data at2020-06-22 09:55:58'),
(331, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(332, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(333, 1, '2020-06-22 09:55:58', '1 insert data at2020-06-22 09:55:58'),
(334, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(335, 1, '2020-06-22 09:55:58', '1 insert data at2020-06-22 09:55:58'),
(336, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(337, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(338, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(339, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(340, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(341, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(342, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(343, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(344, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(345, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(346, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(347, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(348, 1, '2020-06-22 10:24:24', '1 insert data at2020-06-22 10:24:24'),
(349, 1, '2020-06-22 10:24:24', '1 insert data at2020-06-22 10:24:24'),
(350, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(351, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(352, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(353, 1, '2020-06-22 10:24:24', '1 insert data at2020-06-22 10:24:24'),
(354, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(355, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(356, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(357, 1, '2020-06-22 10:24:24', '1 insert data at2020-06-22 10:24:24'),
(358, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(359, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(360, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(361, 1, '2020-06-22 10:24:37', '1 update data at2020-06-22 10:24:37'),
(362, 1, '2020-06-22 10:24:37', '1 update data at2020-06-22 10:24:37'),
(363, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(364, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(365, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(366, 1, '2020-06-22 10:24:37', '1 update data at2020-06-22 10:24:37'),
(367, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(368, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(369, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(370, 1, '2020-06-22 10:24:37', '1 update data at2020-06-22 10:24:37'),
(371, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(372, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(373, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(374, 1, '2020-06-22 10:29:40', '1 update data at2020-06-22 10:29:40'),
(375, 1, '2020-06-22 10:29:40', '1 update data at2020-06-22 10:29:40'),
(376, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(377, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(378, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(379, 1, '2020-06-22 10:29:40', '1 update data at2020-06-22 10:29:40'),
(380, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(381, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(382, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(383, 1, '2020-06-22 10:29:40', '1 update data at2020-06-22 10:29:40'),
(384, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(385, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(386, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(387, 1, '2020-06-22 10:48:36', '1 insert data at2020-06-22 10:48:36'),
(388, 1, '2020-06-22 10:48:36', '1 insert data at2020-06-22 10:48:36'),
(389, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(390, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(391, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(392, 1, '2020-06-22 10:48:36', '1 insert data at2020-06-22 10:48:36'),
(393, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(394, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(395, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(396, 1, '2020-06-22 10:48:36', '1 insert data at2020-06-22 10:48:36'),
(397, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(398, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(399, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(400, 1, '2020-06-22 10:48:48', '1 insert data at2020-06-22 10:48:48'),
(401, 1, '2020-06-22 10:48:48', '1 insert data at2020-06-22 10:48:48'),
(402, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(403, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(404, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(405, 1, '2020-06-22 10:48:48', '1 insert data at2020-06-22 10:48:48'),
(406, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(407, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(408, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(409, 1, '2020-06-22 10:48:48', '1 insert data at2020-06-22 10:48:48'),
(410, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(411, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(412, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(413, 1, '2020-06-22 10:50:14', '1 update data at2020-06-22 10:50:14'),
(414, 1, '2020-06-22 10:50:14', '1 update data at2020-06-22 10:50:14'),
(415, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(416, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(417, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(418, 1, '2020-06-22 10:50:14', '1 update data at2020-06-22 10:50:14'),
(419, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(420, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(421, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(422, 1, '2020-06-22 10:50:14', '1 update data at2020-06-22 10:50:14'),
(423, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(424, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(425, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(426, 1, '2020-06-22 10:52:54', '1 insert data at 2020-06-22 10:52:54'),
(427, 1, '2020-06-22 10:52:54', '1 insert data at 2020-06-22 10:52:54'),
(428, 1, '2020-06-22 10:52:54', '1 insert data at 2020-06-22 10:52:54'),
(429, 1, '2020-06-22 10:52:54', '1 insert data at2020-06-22 10:52:54'),
(430, 1, '2020-06-22 10:54:41', '1 update data at 2020-06-22 10:54:41'),
(431, 1, '2020-06-22 10:54:41', '1 update data at 2020-06-22 10:54:41'),
(432, 1, '2020-06-22 10:54:41', '1 update data at 2020-06-22 10:54:41'),
(433, 1, '2020-06-22 10:54:41', '1 update data at2020-06-22 10:54:41'),
(434, 1, '2020-06-22 10:59:26', '1 insert data at2020-06-22 10:59:26'),
(435, 1, '2020-06-22 10:59:26', '1 insert data at2020-06-22 10:59:26'),
(436, 1, '2020-06-22 11:02:12', '1 insert data at2020-06-22 11:02:12'),
(437, 1, '2020-06-22 11:03:50', '1 update data at2020-06-22 11:03:50'),
(438, 1, '2020-06-22 10:59:26', '1 update data at2020-06-22 10:59:26'),
(439, 1, '2020-06-22 11:53:18', '1 insert data at 2020-06-22 11:53:18'),
(440, 1, '2020-06-22 11:53:39', '1 update data at 2020-06-22 11:53:39'),
(441, 1, '2020-06-22 11:56:00', '1 insert data at2020-06-22 11:56:00'),
(442, 1, '2020-06-22 11:59:26', '1 insert data at2020-06-22 11:59:26'),
(443, 1, '2020-06-22 12:32:52', '1 insert data at2020-06-22 12:32:52'),
(444, 1, '2020-06-22 12:32:52', '1 insert data at2020-06-22 12:32:52'),
(445, 1, '2020-06-22 12:32:52', '1 insert data at2020-06-22 12:32:52'),
(446, 1, '2020-06-22 12:32:52', '1 insert data at2020-06-22 12:32:52'),
(447, 1, '2020-06-22 12:32:52', '1 insert data at2020-06-22 12:32:52'),
(448, 1, '2020-06-22 12:33:02', '1 update data at 2020-06-22 12:33:02'),
(449, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(450, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(451, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(452, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(453, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(454, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(455, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(456, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(457, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(458, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(459, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(460, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(461, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(462, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(463, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(464, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(465, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(466, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(467, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(468, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(469, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(470, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(471, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(472, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(473, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(474, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(475, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(476, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(477, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(478, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(479, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(480, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(481, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(482, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(483, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(484, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(485, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(486, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(487, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(488, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(489, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(490, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(491, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(492, 1, '2020-06-22 12:33:51', '1 update data at2020-06-22 12:33:51'),
(493, 1, '2020-06-22 12:42:09', '1 update data at 2020-06-22 12:42:09'),
(494, 1, '2020-06-22 12:43:35', '1 insert data at 2020-06-22 12:43:35'),
(495, 1, '2020-06-22 12:43:35', '1 insert data at 2020-06-22 12:43:35'),
(496, 1, '2020-06-22 12:43:35', '1 insert data at 2020-06-22 12:43:35'),
(497, 1, '2020-06-22 12:43:35', '1 insert data at 2020-06-22 12:43:35'),
(498, 1, '2020-06-22 12:43:35', '1 insert data at 2020-06-22 12:43:35'),
(499, 1, '2020-06-22 08:37:11', '1 update data at 2020-06-22 08:37:11'),
(500, 1, '2020-06-22 08:37:11', '1 insert data at 2020-06-22 08:37:11'),
(501, 1, '2020-06-22 08:55:25', '1 update data at 2020-06-22 08:55:25'),
(502, 1, '2020-06-22 08:55:25', '1 insert data at 2020-06-22 08:55:25'),
(503, 1, NULL, NULL),
(504, 1, '2020-06-22 02:19:54', '1 update data at 2020-06-22 02:19:54'),
(505, 1, '2020-06-22 09:20:19', '1 update data at 2020-06-22 09:20:19'),
(506, 1, '2020-06-22 09:20:19', '1 insert data at 2020-06-22 09:20:19'),
(507, 1, '2020-06-22 02:20:24', '1 update data at 2020-06-22 02:20:24'),
(508, 1, '2020-06-22 02:21:14', '1 update data at 2020-06-22 02:21:14'),
(509, 1, '2020-06-22 02:51:35', '1 update data at 2020-06-22 02:51:35'),
(510, 1, '2020-06-22 02:52:08', '1 update data at2020-06-22 02:52:08'),
(511, 1, '2020-06-22 02:52:25', '1 update data at2020-06-22 02:52:25'),
(512, 1, '2020-06-22 02:55:01', '1 update data at 2020-06-22 02:55:01'),
(513, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(514, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(515, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(516, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(517, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(518, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(519, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(520, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(521, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(522, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(523, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(524, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(525, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(526, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(527, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(528, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(529, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(530, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(531, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(532, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(533, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(534, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(535, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(536, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(537, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(538, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(539, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(540, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(541, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(542, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(543, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(544, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(545, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(546, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(547, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(548, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(549, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(550, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(551, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(552, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(553, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(554, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(555, 1, '2020-06-22 02:55:56', '1 insert data at2020-06-22 02:55:56'),
(556, 1, '2020-06-22 02:59:25', '1 insert data at2020-06-22 02:59:25'),
(557, 1, '2020-06-22 03:01:02', '1 insert data at 2020-06-22 03:01:02'),
(558, 1, '2020-06-22 03:01:02', '1 insert data at 2020-06-22 03:01:02'),
(559, 1, '2020-06-22 03:01:02', '1 insert data at 2020-06-22 03:01:02'),
(560, 1, '2020-06-22 03:01:02', '1 insert data at 2020-06-22 03:01:02'),
(561, 1, '2020-06-22 03:01:02', '1 insert data at 2020-06-22 03:01:02'),
(562, 1, '2020-06-22 08:01:19', '1 update data at 2020-06-22 08:01:19'),
(563, 1, '2020-06-22 08:01:19', '1 insert data at 2020-06-22 08:01:19'),
(564, 1, '2020-06-22 03:01:24', '1 update data at 2020-06-22 03:01:24'),
(565, 1, '2020-06-22 04:48:43', '1 update data at 2020-06-22 04:48:43'),
(566, 1, '2020-06-22 04:54:11', '1 update data at 2020-06-22 04:54:11'),
(567, 1, '2020-06-22 04:54:26', '1 update data at 2020-06-22 04:54:26'),
(568, 1, '2020-06-22 05:00:24', '1 update data at 2020-06-22 05:00:24'),
(569, 1, '2020-06-22 05:00:52', '1 update data at 2020-06-22 05:00:52'),
(570, 1, '2020-06-22 05:00:55', '1 update data at 2020-06-22 05:00:55'),
(571, 1, '2020-06-22 05:09:47', '1 update data at 2020-06-22 05:09:47'),
(572, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(573, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(574, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(575, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(576, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(577, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(578, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(579, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(580, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(581, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(582, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(583, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(584, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(585, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(586, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(587, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(588, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(589, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(590, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(591, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(592, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(593, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(594, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(595, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(596, 1, '2020-06-22 05:10:25', '1 update data at 2020-06-22 05:10:25'),
(597, 1, '2020-06-22 05:11:24', '1 insert data at 2020-06-22 05:11:24'),
(598, 1, '2020-06-22 05:14:55', '1 insert data at2020-06-22 05:14:55'),
(599, 1, '2020-06-22 05:14:55', '1 insert data at2020-06-22 05:14:55'),
(600, 1, '2020-06-22 05:14:55', '1 insert data at2020-06-22 05:14:55'),
(601, 1, '2020-06-22 17:14:55', '1 insert data at2020-06-22 17:14:55'),
(602, 1, '2020-06-22 17:14:55', '1 insert data at2020-06-22 17:14:55'),
(603, 1, '2020-06-22 05:15:38', '1 update data at2020-06-22 05:15:38'),
(604, 1, '2020-06-22 17:15:39', '1 update data at2020-06-22 17:15:39'),
(605, 1, '2020-06-22 17:15:39', '1 update data at2020-06-22 17:15:39'),
(606, 1, '2020-06-22 05:18:53', '1 update data at 2020-06-22 05:18:53'),
(607, 1, '2020-06-22 05:18:56', '1 update data at 2020-06-22 05:18:56'),
(608, 1, '2020-06-22 05:19:00', '1 update data at 2020-06-22 05:19:00'),
(609, 1, '2020-06-22 05:20:09', '1 insert data at2020-06-22 05:20:09'),
(610, 1, '2020-06-22 05:20:09', '1 insert data at2020-06-22 05:20:09'),
(611, 1, '2020-06-22 05:21:26', '1 insert data at2020-06-22 05:21:26'),
(612, 1, '2020-06-22 05:21:26', '1 insert data at2020-06-22 05:21:26'),
(613, 1, '2020-06-22 05:24:03', '1 update data at 2020-06-22 05:24:03'),
(614, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(615, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(616, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(617, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(618, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(619, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(620, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(621, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(622, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(623, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(624, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(625, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(626, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(627, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(628, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(629, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(630, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(631, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(632, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(633, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(634, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(635, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(636, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(637, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(638, 1, '2020-06-22 07:51:13', '1 update data at2020-06-22 07:51:13'),
(639, 1, '2020-06-22 05:26:20', '1 insert data at 2020-06-22 05:26:20'),
(640, 1, '2020-06-22 05:28:46', '1 insert data at2020-06-22 05:28:46'),
(641, 1, '2020-06-22 05:28:46', '1 insert data at2020-06-22 05:28:46'),
(642, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(643, 1, '2020-06-22 05:28:46', '1 insert data at2020-06-22 05:28:46'),
(644, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(645, 1, '0000-00-00 00:00:00', '1 insert data at0000-00-00 00:00:00'),
(646, 1, '2020-06-22 05:30:58', '1 insert data at2020-06-22 05:30:58'),
(647, 1, '2020-06-22 05:30:58', '1 insert data at2020-06-22 05:30:58'),
(648, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(649, 1, '2020-06-22 05:30:58', '1 insert data at2020-06-22 05:30:58'),
(650, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(651, 1, '2020-06-22 05:31:37', '1 insert data at2020-06-22 05:31:37'),
(652, 1, '2020-06-22 05:31:37', '1 insert data at2020-06-22 05:31:37'),
(653, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(654, 1, '2020-06-22 05:31:37', '1 insert data at2020-06-22 05:31:37'),
(655, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(656, 1, '2020-06-22 17:33:00', '1 insert data at2020-06-22 17:33:00'),
(657, 1, '2020-06-22 05:37:45', '1 insert data at2020-06-22 05:37:45'),
(658, 1, '2020-06-22 05:37:45', '1 insert data at2020-06-22 05:37:45'),
(659, 1, '2020-06-22 05:37:45', '1 insert data at2020-06-22 05:37:45'),
(660, 1, '2020-06-22 05:37:45', '1 insert data at2020-06-22 05:37:45'),
(661, 1, '2020-06-22 05:37:45', '1 insert data at2020-06-22 05:37:45'),
(662, 1, '2020-06-22 05:37:45', '1 insert data at2020-06-22 05:37:45'),
(663, 1, '2020-06-22 05:37:45', '1 insert data at2020-06-22 05:37:45'),
(664, 1, '2020-06-22 17:33:00', '1 update data at2020-06-22 17:33:00'),
(665, 1, '2020-06-22 05:41:33', '1 insert data at2020-06-22 05:41:33'),
(666, 1, '2020-06-22 05:41:33', '1 insert data at2020-06-22 05:41:33'),
(667, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(668, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(669, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(670, 1, '2020-06-22 05:41:33', '1 insert data at2020-06-22 05:41:33'),
(671, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(672, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(673, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(674, 1, '2020-06-22 05:41:33', '1 insert data at2020-06-22 05:41:33'),
(675, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(676, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(677, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(678, 1, '2020-06-22 05:42:11', '1 insert data at2020-06-22 05:42:11'),
(679, 1, '2020-06-22 05:42:11', '1 insert data at2020-06-22 05:42:11'),
(680, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(681, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(682, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(683, 1, '2020-06-22 05:42:11', '1 insert data at2020-06-22 05:42:11'),
(684, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(685, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(686, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(687, 1, '2020-06-22 05:42:11', '1 insert data at2020-06-22 05:42:11'),
(688, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(689, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(690, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(691, 1, '2020-06-22 05:42:34', '1 update data at2020-06-22 05:42:34'),
(692, 1, '2020-06-22 05:42:34', '1 update data at2020-06-22 05:42:34'),
(693, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(694, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(695, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(696, 1, '2020-06-22 05:42:34', '1 update data at2020-06-22 05:42:34'),
(697, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(698, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(699, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(700, 1, '2020-06-22 05:42:34', '1 update data at2020-06-22 05:42:34'),
(701, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(702, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(703, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(704, 1, '2020-06-22 05:45:19', '1 insert data at 2020-06-22 05:45:19'),
(705, 1, '2020-06-22 05:45:19', '1 insert data at 2020-06-22 05:45:19'),
(706, 1, '2020-06-22 05:45:19', '1 insert data at 2020-06-22 05:45:19'),
(707, 1, '2020-06-22 05:45:19', '1 insert data at2020-06-22 05:45:19'),
(708, 1, '2020-06-22 05:46:58', '1 update data at 2020-06-22 05:46:58'),
(709, 1, '2020-06-22 05:46:58', '1 update data at 2020-06-22 05:46:58'),
(710, 1, '2020-06-22 05:46:58', '1 update data at 2020-06-22 05:46:58'),
(711, 1, '2020-06-22 05:46:58', '1 update data at2020-06-22 05:46:58'),
(712, 1, '2020-06-22 05:49:10', '1 insert data at 2020-06-22 05:49:10');
INSERT INTO `log_all` (`id_log_all`, `id_user`, `log_date`, `log`) VALUES
(713, 1, '2020-06-22 10:50:21', '1 update data at 2020-06-22 10:50:21'),
(714, 1, '2020-06-22 10:50:21', '1 insert data at 2020-06-22 10:50:21'),
(715, 1, '2020-06-22 10:50:35', '1 update data at 2020-06-22 10:50:35'),
(716, 1, '2020-06-22 10:50:35', '1 insert data at 2020-06-22 10:50:35'),
(717, 1, '2020-06-22 10:50:35', '1 update data at 2020-06-22 10:50:35'),
(718, 1, '2020-06-22 10:50:35', '1 insert data at 2020-06-22 10:50:35'),
(719, 1, '2020-06-22 10:50:36', '1 update data at 2020-06-22 10:50:36'),
(720, 1, '2020-06-22 10:50:36', '1 insert data at 2020-06-22 10:50:36'),
(721, 1, '2020-06-22 10:50:36', '1 update data at 2020-06-22 10:50:36'),
(722, 1, '2020-06-22 10:50:36', '1 insert data at 2020-06-22 10:50:36'),
(723, 1, '2020-06-22 10:50:36', '1 update data at 2020-06-22 10:50:36'),
(724, 1, '2020-06-22 10:50:36', '1 insert data at 2020-06-22 10:50:36'),
(725, 1, '2020-06-22 10:50:36', '1 update data at 2020-06-22 10:50:36'),
(726, 1, '2020-06-22 10:50:36', '1 insert data at 2020-06-22 10:50:36'),
(727, 1, '2020-06-22 05:50:58', '1 update data at 2020-06-22 05:50:58'),
(728, 1, '2020-06-22 06:01:50', '1 update data at2020-06-22 06:01:50'),
(729, 1, '2020-06-22 06:01:50', '1 update data at2020-06-22 06:01:50'),
(730, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(731, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(732, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(733, 1, '2020-06-22 06:01:50', '1 update data at2020-06-22 06:01:50'),
(734, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(735, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(736, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(737, 1, '2020-06-22 06:01:50', '1 update data at2020-06-22 06:01:50'),
(738, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(739, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(740, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(741, 1, '2020-06-22 06:01:56', '1 update data at2020-06-22 06:01:56'),
(742, 1, '2020-06-22 06:01:56', '1 update data at2020-06-22 06:01:56'),
(743, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(744, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(745, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(746, 1, '2020-06-22 06:01:56', '1 update data at2020-06-22 06:01:56'),
(747, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(748, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(749, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(750, 1, '2020-06-22 06:01:56', '1 update data at2020-06-22 06:01:56'),
(751, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(752, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(753, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(754, 1, '2020-06-22 06:05:31', '1 update data at2020-06-22 06:05:31'),
(755, 1, '2020-06-22 06:05:31', '1 update data at2020-06-22 06:05:31'),
(756, 1, '2020-06-22 06:05:31', '1 update data at2020-06-22 06:05:31'),
(757, 1, '2020-06-22 06:05:31', '1 update data at2020-06-22 06:05:31'),
(758, 1, '2020-06-22 06:05:31', '1 update data at2020-06-22 06:05:31'),
(759, 1, '2020-06-22 06:05:31', '1 update data at2020-06-22 06:05:31'),
(760, 1, '2020-06-22 06:05:31', '1 update data at2020-06-22 06:05:31'),
(761, 1, '2020-06-22 06:06:00', '1 insert data at2020-06-22 06:06:00'),
(762, 1, '2020-06-22 06:06:00', '1 insert data at2020-06-22 06:06:00'),
(763, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(764, 1, '2020-06-22 06:06:00', '1 insert data at2020-06-22 06:06:00'),
(765, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(766, 1, '2020-06-22 06:10:33', '1 insert data at2020-06-22 06:10:33'),
(767, 1, '2020-06-22 06:10:33', '1 insert data at2020-06-22 06:10:33'),
(768, 1, '2020-06-22 06:10:33', '1 insert data at2020-06-22 06:10:33'),
(769, 1, '2020-06-22 06:10:33', '1 insert data at2020-06-22 06:10:33'),
(770, 1, '2020-06-22 06:10:33', '1 insert data at2020-06-22 06:10:33'),
(771, 1, '2020-06-22 06:10:43', '1 update data at 2020-06-22 06:10:43'),
(772, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(773, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(774, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(775, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(776, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(777, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(778, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(779, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(780, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(781, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(782, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(783, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(784, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(785, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(786, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(787, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(788, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(789, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(790, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(791, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(792, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(793, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(794, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(795, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(796, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(797, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(798, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(799, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(800, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(801, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(802, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(803, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(804, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(805, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(806, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(807, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(808, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(809, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(810, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(811, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(812, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(813, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(814, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(815, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(816, 1, '2020-06-22 06:42:00', '1 insert data at2020-06-22 06:42:00'),
(817, 1, '2020-06-22 06:42:24', '1 insert data at2020-06-22 06:42:24'),
(818, 1, '2020-06-22 06:42:24', '1 insert data at2020-06-22 06:42:24'),
(819, 1, '2020-06-22 06:42:24', '1 insert data at2020-06-22 06:42:24'),
(820, 1, '2020-06-22 06:42:38', '1 insert data at2020-06-22 06:42:38'),
(821, 1, '2020-06-22 06:42:38', '1 insert data at2020-06-22 06:42:38'),
(822, 1, '2020-06-22 06:42:38', '1 insert data at2020-06-22 06:42:38'),
(823, 1, '2020-06-22 06:44:01', '1 insert data at2020-06-22 06:44:01'),
(824, 1, '2020-06-22 06:44:01', '1 insert data at2020-06-22 06:44:01'),
(825, 1, '2020-06-22 06:44:01', '1 insert data at2020-06-22 06:44:01'),
(826, 1, '2020-06-22 06:44:21', '1 insert data at2020-06-22 06:44:21'),
(827, 1, '2020-06-22 06:45:17', '1 insert data at2020-06-22 06:45:17'),
(828, 1, '2020-06-22 06:45:33', '1 update data at2020-06-22 06:45:33'),
(829, 1, '2020-06-22 06:45:35', '1 update data at2020-06-22 06:45:35'),
(830, 1, '2020-06-22 06:45:37', '1 update data at2020-06-22 06:45:37'),
(831, 1, '2020-06-22 06:45:40', '1 update data at2020-06-22 06:45:40'),
(832, 1, '2020-06-22 06:45:42', '1 update data at2020-06-22 06:45:42'),
(833, 1, '2020-06-22 06:47:48', '1 insert data at2020-06-22 06:47:48'),
(834, 1, '2020-06-22 06:47:48', '1 insert data at2020-06-22 06:47:48'),
(835, 1, '2020-06-22 06:48:17', '1 insert data at2020-06-22 06:48:17'),
(836, 1, '2020-06-22 06:48:17', '1 insert data at2020-06-22 06:48:17'),
(837, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(838, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(839, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(840, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(841, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(842, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(843, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(844, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(845, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(846, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(847, 1, '2020-06-22 09:17:46', '1 insert data at2020-06-22 09:17:46'),
(848, 1, '2020-06-22 09:17:46', '1 insert data at2020-06-22 09:17:46'),
(849, 1, '2020-06-22 21:17:46', '1 insert data at2020-06-22 21:17:46'),
(850, 1, '2020-06-22 21:17:46', '1 insert data at2020-06-22 21:17:46'),
(851, 1, '2020-06-22 09:18:26', '1 update data at2020-06-22 09:18:26'),
(852, 1, '2020-06-22 09:28:15', '1 insert data at2020-06-22 09:28:15'),
(853, 1, '2020-06-22 10:03:21', '1 insert data at2020-06-22 10:03:21'),
(854, 1, '2020-06-22 10:03:21', '1 insert data at2020-06-22 10:03:21'),
(855, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(856, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(857, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(858, 1, '2020-06-22 10:03:21', '1 insert data at2020-06-22 10:03:21'),
(859, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(860, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(861, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(862, 1, '2020-06-22 22:08:52', '1 insert data at2020-06-22 22:08:52'),
(863, 1, '2020-06-22 22:08:52', '1 insert data at2020-06-22 22:08:52'),
(864, 1, '2020-06-23 03:17:44', '1 insert data at 2020-06-23 03:17:44'),
(865, 1, '2020-06-23 03:17:44', '1 insert data at 2020-06-23 03:17:44'),
(866, 1, '2020-06-23 03:17:45', '1 insert data at2020-06-23 03:17:45'),
(867, 1, '2020-06-23 03:19:56', '1 insert data at 2020-06-23 03:19:56'),
(868, 1, '2020-06-23 03:19:56', '1 insert data at 2020-06-23 03:19:56'),
(869, 1, '2020-06-23 03:19:56', '1 insert data at2020-06-23 03:19:56'),
(870, 1, '2020-06-23 03:19:56', '1 insert data at2020-06-23 03:19:56'),
(871, 1, '2020-06-23 03:19:56', '1 insert data at2020-06-23 03:19:56'),
(872, 1, '2020-06-22 09:00:47', '1 insert data at2020-06-22 09:00:47'),
(873, 1, '2020-06-22 05:30:58', '1 insert data at2020-06-22 05:30:58'),
(874, 1, '2020-06-22 05:31:37', '1 insert data at2020-06-22 05:31:37'),
(875, 1, '2020-06-22 10:03:21', '1 insert data at2020-06-22 10:03:21'),
(876, 1, '2020-06-23 04:42:49', '1 insert data at2020-06-23 04:42:49'),
(877, 1, '2020-06-23 04:42:49', '1 insert data at2020-06-23 04:42:49'),
(878, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(879, 1, '2020-06-23 04:42:49', '1 insert data at2020-06-23 04:42:49'),
(880, 1, '2020-06-23 10:27:25', '1 insert data at2020-06-23 10:27:25'),
(881, 1, '2020-06-23 10:27:25', '1 insert data at2020-06-23 10:27:25'),
(882, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(883, 1, '2020-06-23 10:27:25', '1 insert data at2020-06-23 10:27:25'),
(884, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(885, 1, '2020-06-23 10:28:23', '1 insert data at2020-06-23 10:28:23'),
(886, 1, '2020-06-23 10:28:23', '1 insert data at2020-06-23 10:28:23'),
(887, 1, '2020-06-23 10:28:23', '1 insert data at2020-06-23 10:28:23'),
(888, 1, '2020-06-23 10:30:55', '1 insert data at2020-06-23 10:30:55'),
(889, 1, '2020-06-23 10:30:55', '1 insert data at2020-06-23 10:30:55'),
(890, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(891, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(892, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(893, 1, '2020-06-23 10:30:55', '1 insert data at2020-06-23 10:30:55'),
(894, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(895, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(896, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(897, 1, '2020-06-23 10:31:36', '1 insert data at2020-06-23 10:31:36'),
(898, 1, '2020-06-23 10:31:36', '1 insert data at2020-06-23 10:31:36'),
(899, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(900, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(901, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(902, 1, '2020-06-23 10:31:36', '1 insert data at2020-06-23 10:31:36'),
(903, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(904, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(905, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(906, 1, '2020-06-23 10:32:41', '1 insert data at2020-06-23 10:32:41'),
(907, 1, '2020-06-23 10:32:42', '1 insert data at2020-06-23 10:32:42'),
(908, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(909, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(910, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(911, 1, '2020-06-23 10:32:42', '1 insert data at2020-06-23 10:32:42'),
(912, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(913, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(914, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(915, 1, '2020-06-23 10:32:56', '1 insert data at2020-06-23 10:32:56'),
(916, 1, '2020-06-23 10:32:56', '1 insert data at2020-06-23 10:32:56'),
(917, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(918, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(919, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(920, 1, '2020-06-23 10:32:56', '1 insert data at2020-06-23 10:32:56'),
(921, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(922, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(923, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(924, 1, '2020-06-23 10:33:03', '1 insert data at2020-06-23 10:33:03'),
(925, 1, '2020-06-23 10:33:03', '1 insert data at2020-06-23 10:33:03'),
(926, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(927, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(928, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(929, 1, '2020-06-23 10:33:03', '1 insert data at2020-06-23 10:33:03'),
(930, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(931, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(932, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(933, 1, '2020-06-23 10:33:16', '1 insert data at2020-06-23 10:33:16'),
(934, 1, '2020-06-23 10:33:16', '1 insert data at2020-06-23 10:33:16'),
(935, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(936, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(937, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(938, 1, '2020-06-23 10:33:16', '1 insert data at2020-06-23 10:33:16'),
(939, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(940, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(941, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(942, 1, '2020-06-23 10:37:24', '1 insert data at2020-06-23 10:37:24'),
(943, 1, '2020-06-23 10:37:24', '1 insert data at2020-06-23 10:37:24'),
(944, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(945, 1, '2020-06-23 10:37:24', '1 insert data at2020-06-23 10:37:24'),
(946, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(947, 1, '2020-06-23 10:40:06', '1 insert data at2020-06-23 10:40:06'),
(948, 1, '2020-06-23 10:40:06', '1 insert data at2020-06-23 10:40:06'),
(949, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(950, 1, '2020-06-23 10:40:06', '1 insert data at2020-06-23 10:40:06'),
(951, 1, '2020-06-23 10:44:58', '1 insert data at2020-06-23 10:44:58'),
(952, 1, '2020-06-23 10:44:58', '1 insert data at2020-06-23 10:44:58'),
(953, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(954, 1, '2020-06-23 10:44:58', '1 insert data at2020-06-23 10:44:58'),
(955, 1, '2020-06-23 10:45:55', '1 insert data at2020-06-23 10:45:55'),
(956, 1, '2020-06-23 10:45:55', '1 insert data at2020-06-23 10:45:55'),
(957, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(958, 1, '2020-06-23 10:45:55', '1 insert data at2020-06-23 10:45:55'),
(959, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(960, 1, '2020-06-23 10:46:08', '1 update data at 2020-06-23 10:46:08'),
(961, 1, '2020-06-23 10:46:12', '1 insert data at2020-06-23 10:46:12'),
(962, 1, '2020-06-23 10:46:12', '1 insert data at2020-06-23 10:46:12'),
(963, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(964, 1, '2020-06-23 10:46:12', '1 insert data at2020-06-23 10:46:12'),
(965, 1, '2020-06-23 10:46:08', '1 update data at 2020-06-23 10:46:08'),
(966, 1, '2020-06-23 11:16:17', '1 insert data at2020-06-23 11:16:17'),
(967, 1, '2020-06-23 11:16:29', '1 update data at2020-06-23 11:16:29'),
(968, 1, '2020-06-23 11:16:33', '1 update data at2020-06-23 11:16:33'),
(969, 1, '2020-06-23 11:16:36', '1 update data at2020-06-23 11:16:36'),
(970, 1, '2020-06-23 11:16:38', '1 update data at2020-06-23 11:16:38'),
(971, 1, '2020-06-23 11:16:49', '1 insert data at2020-06-23 11:16:49'),
(972, 1, '2020-06-23 11:27:59', '1 insert data at2020-06-23 11:27:59'),
(973, 1, '2020-06-23 11:27:59', '1 insert data at2020-06-23 11:27:59'),
(974, 1, '2020-06-23 10:46:08', '1 update data at 2020-06-23 10:46:08'),
(975, 1, '2020-06-23 11:27:59', '1 insert data at2020-06-23 11:27:59'),
(976, 1, '2020-06-23 11:31:56', '1 insert data at2020-06-23 11:31:56'),
(977, 1, '2020-06-23 11:31:56', '1 insert data at2020-06-23 11:31:56'),
(978, 1, '2020-06-23 11:31:56', '1 insert data at2020-06-23 11:31:56'),
(979, 1, '2020-06-23 11:33:21', '1 insert data at2020-06-23 11:33:21'),
(980, 1, '2020-06-23 11:33:21', '1 insert data at2020-06-23 11:33:21'),
(981, 1, '2020-06-23 11:33:21', '1 insert data at2020-06-23 11:33:21'),
(982, 1, '2020-06-24 12:24:12', '1 insert data at2020-06-24 12:24:12'),
(983, 1, '2020-06-24 12:24:12', '1 insert data at2020-06-24 12:24:12'),
(984, 1, '2020-06-24 12:24:12', '1 insert data at2020-06-24 12:24:12'),
(985, 1, '2020-06-24 12:24:12', '1 insert data at2020-06-24 12:24:12'),
(986, 1, '2020-06-24 12:24:12', '1 insert data at2020-06-24 12:24:12'),
(987, 1, '2020-06-24 12:24:12', '1 insert data at2020-06-24 12:24:12'),
(988, 1, '2020-06-24 12:33:42', '1 insert data at2020-06-24 12:33:42'),
(989, 1, '2020-06-24 12:33:42', '1 insert data at2020-06-24 12:33:42'),
(990, 1, '2020-06-24 12:34:23', '1 insert data at2020-06-24 12:34:23'),
(991, 1, '2020-06-24 12:34:23', '1 insert data at2020-06-24 12:34:23'),
(992, 1, '2020-06-24 12:35:13', '1 insert data at2020-06-24 12:35:13'),
(993, 1, '2020-06-24 12:35:13', '1 insert data at2020-06-24 12:35:13'),
(994, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(995, 1, '2020-06-24 12:35:13', '1 insert data at2020-06-24 12:35:13'),
(996, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(997, 1, '2020-06-24 12:35:24', '1 insert data at2020-06-24 12:35:24'),
(998, 1, '2020-06-24 12:35:24', '1 insert data at2020-06-24 12:35:24'),
(999, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1000, 1, '2020-06-24 12:35:24', '1 insert data at2020-06-24 12:35:24'),
(1001, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1002, 1, '2020-06-24 08:38:18', '1 update data at2020-06-24 08:38:18'),
(1003, 1, '2020-06-24 08:38:18', '1 update data at2020-06-24 08:38:18'),
(1004, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1005, 1, '2020-06-24 08:38:18', '1 update data at2020-06-24 08:38:18'),
(1006, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1007, 1, '2020-06-24 08:42:25', '1 update data at2020-06-24 08:42:25'),
(1008, 1, '2020-06-24 08:42:25', '1 update data at2020-06-24 08:42:25'),
(1009, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1010, 1, '2020-06-24 08:42:25', '1 update data at2020-06-24 08:42:25'),
(1011, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1012, 1, '2020-06-24 08:50:22', '1 update data at2020-06-24 08:50:22'),
(1013, 1, '2020-06-24 08:50:25', '1 update data at2020-06-24 08:50:25'),
(1014, 1, '2020-06-24 08:50:28', '1 update data at2020-06-24 08:50:28'),
(1015, 1, '2020-06-24 08:50:30', '1 update data at2020-06-24 08:50:30'),
(1016, 1, '2020-06-24 08:50:33', '1 update data at2020-06-24 08:50:33'),
(1017, 1, '2020-06-24 08:50:35', '1 update data at2020-06-24 08:50:35'),
(1018, 1, '2020-06-24 08:50:35', '1 update data at2020-06-24 08:50:35'),
(1019, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1020, 1, '2020-06-24 08:50:35', '1 update data at2020-06-24 08:50:35'),
(1021, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1022, 1, '2020-06-24 08:50:38', '1 update data at2020-06-24 08:50:38'),
(1023, 1, '2020-06-24 08:50:35', '1 update data at2020-06-24 08:50:35'),
(1024, 1, '2020-06-24 08:51:32', '1 update data at2020-06-24 08:51:32'),
(1025, 1, '2020-06-24 08:51:32', '1 update data at2020-06-24 08:51:32'),
(1026, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1027, 1, '2020-06-24 08:51:32', '1 update data at2020-06-24 08:51:32'),
(1028, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1029, 1, '2020-06-24 08:51:40', '1 update data at2020-06-24 08:51:40'),
(1030, 1, '2020-06-24 08:51:44', '1 update data at2020-06-24 08:51:44'),
(1031, 1, '2020-06-25 08:45:58', '1 insert data at2020-06-25 08:45:58'),
(1032, 1, '2020-06-25 08:45:58', '1 insert data at2020-06-25 08:45:58'),
(1033, 1, '2020-06-25 08:45:58', '1 insert data at2020-06-25 08:45:58'),
(1034, 1, '2020-06-25 10:06:53', '1 insert data at2020-06-25 10:06:53'),
(1035, 1, '2020-06-25 10:06:53', '1 insert data at2020-06-25 10:06:53'),
(1036, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1037, 1, '2020-06-25 10:06:53', '1 insert data at2020-06-25 10:06:53'),
(1038, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1039, 1, '2020-06-25 10:54:52', '1 insert data at2020-06-25 10:54:52'),
(1040, 1, '2020-06-25 11:07:39', '1 insert data at2020-06-25 11:07:39'),
(1041, 1, '2020-06-25 12:51:11', '1 insert data at2020-06-25 12:51:11'),
(1042, 1, '2020-06-25 12:53:54', '1 insert data at2020-06-25 12:53:54'),
(1043, 1, '2020-06-25 12:55:35', '1 insert data at2020-06-25 12:55:35'),
(1044, 1, '2020-06-25 12:55:53', '1 insert data at2020-06-25 12:55:53'),
(1045, 1, '2020-06-25 12:58:29', '1 insert data at2020-06-25 12:58:29'),
(1046, 1, '2020-06-25 12:59:07', '1 insert data at2020-06-25 12:59:07'),
(1047, 1, '2020-06-25 12:59:07', '1 insert data at2020-06-25 12:59:07'),
(1048, 1, '2020-06-25 12:59:53', '1 insert data at2020-06-25 12:59:53'),
(1049, 1, '2020-06-25 12:59:53', '1 insert data at2020-06-25 12:59:53'),
(1050, 1, '2020-06-25 01:02:23', '1 insert data at2020-06-25 01:02:23'),
(1051, 1, '2020-06-25 01:02:23', '1 insert data at2020-06-25 01:02:23'),
(1052, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1053, 1, '2020-06-25 01:03:11', '1 insert data at2020-06-25 01:03:11'),
(1054, 1, '2020-06-25 01:03:11', '1 insert data at2020-06-25 01:03:11'),
(1055, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1056, 1, '2020-06-25 01:05:14', '1 insert data at2020-06-25 01:05:14'),
(1057, 1, '2020-06-25 01:05:14', '1 insert data at2020-06-25 01:05:14'),
(1058, 1, '2020-06-25 01:07:52', '1 insert data at2020-06-25 01:07:52'),
(1059, 1, '2020-06-25 01:07:52', '1 insert data at2020-06-25 01:07:52'),
(1060, 1, '2020-06-25 01:08:51', '1 insert data at2020-06-25 01:08:51'),
(1061, 1, '2020-06-25 01:08:51', '1 insert data at2020-06-25 01:08:51'),
(1062, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1063, 1, '2020-06-25 01:08:51', '1 insert data at2020-06-25 01:08:51'),
(1064, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1065, 1, '2020-06-25 07:10:03', '1 update data at2020-06-25 07:10:03'),
(1066, 1, '2020-06-25 07:10:03', '1 update data at2020-06-25 07:10:03'),
(1067, 1, '2020-06-25 07:10:07', '1 update data at2020-06-25 07:10:07'),
(1068, 1, '2020-06-25 07:10:07', '1 update data at2020-06-25 07:10:07'),
(1069, 1, '2020-06-25 07:10:54', '1 update data at2020-06-25 07:10:54'),
(1070, 1, '2020-06-25 07:11:05', '1 insert data at2020-06-25 07:11:05'),
(1071, 1, '2020-06-25 07:11:05', '1 insert data at2020-06-25 07:11:05'),
(1072, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1073, 1, '2020-06-25 07:11:05', '1 insert data at2020-06-25 07:11:05'),
(1074, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1075, 1, '2020-06-25 07:28:07', '1 insert data at2020-06-25 07:28:07'),
(1076, 1, '2020-06-25 07:28:08', '1 insert data at2020-06-25 07:28:08'),
(1077, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1078, 1, '2020-06-25 07:37:57', '1 update data at2020-06-25 07:37:57'),
(1079, 1, '2020-06-25 07:38:14', '1 update data at2020-06-25 07:38:14'),
(1080, 1, '2020-06-25 07:40:25', '1 update data at2020-06-25 07:40:25'),
(1081, 1, '2020-06-25 07:41:07', '1 update data at2020-06-25 07:41:07'),
(1082, 1, '2020-06-25 07:42:11', '1 update data at2020-06-25 07:42:11'),
(1083, 1, '2020-06-25 07:42:11', '1 update data at2020-06-25 07:42:11'),
(1084, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1085, 1, '2020-06-25 07:42:20', '1 update data at2020-06-25 07:42:20'),
(1086, 1, '2020-06-25 07:42:20', '1 update data at2020-06-25 07:42:20'),
(1087, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1088, 1, '2020-06-25 07:49:04', '1 update data at2020-06-25 07:49:04'),
(1089, 1, '2020-06-25 07:49:06', '1 update data at2020-06-25 07:49:06'),
(1090, 1, '2020-06-25 07:49:09', '1 update data at2020-06-25 07:49:09'),
(1091, 1, '2020-06-25 07:49:12', '1 update data at2020-06-25 07:49:12'),
(1092, 1, '2020-06-25 07:49:14', '1 update data at2020-06-25 07:49:14'),
(1093, 1, '2020-06-25 07:49:16', '1 update data at2020-06-25 07:49:16'),
(1094, 1, '2020-06-25 07:49:19', '1 update data at2020-06-25 07:49:19'),
(1095, 1, '2020-06-25 07:49:21', '1 update data at2020-06-25 07:49:21'),
(1096, 1, '2020-06-25 07:49:23', '1 update data at2020-06-25 07:49:23'),
(1097, 1, '2020-06-25 07:49:25', '1 update data at2020-06-25 07:49:25'),
(1098, 1, '2020-06-25 07:49:29', '1 update data at2020-06-25 07:49:29'),
(1099, 1, '2020-06-25 07:49:32', '1 update data at2020-06-25 07:49:32'),
(1100, 1, '2020-06-25 07:51:07', '1 update data at2020-06-25 07:51:07'),
(1101, 1, '2020-06-25 07:51:07', '1 update data at2020-06-25 07:51:07'),
(1102, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1103, 1, '2020-06-26 10:07:22', '1 insert data at2020-06-26 10:07:22'),
(1104, 1, '2020-06-26 10:07:22', '1 insert data at2020-06-26 10:07:22'),
(1105, 1, '2020-06-26 10:07:22', '1 insert data at2020-06-26 10:07:22'),
(1106, 1, '2020-06-26 10:07:22', '1 insert data at2020-06-26 10:07:22'),
(1107, 1, '2020-06-26 10:07:22', '1 insert data at2020-06-26 10:07:22'),
(1108, 1, '2020-06-26 10:14:31', '1 update data at 2020-06-26 10:14:31'),
(1109, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1110, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1111, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1112, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1113, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1114, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1115, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1116, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1117, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1118, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1119, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1120, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1121, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1122, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1123, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1124, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1125, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1126, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1127, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1128, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(1129, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1130, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(1131, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1132, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1133, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1134, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1135, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1136, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1137, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1138, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1139, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1140, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1141, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1142, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1143, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1144, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1145, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1146, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1147, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1148, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1149, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1150, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1151, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1152, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1153, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1154, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1155, 1, '2020-06-27 07:36:59', '1 insert data at2020-06-27 07:36:59'),
(1156, 1, '2020-06-27 07:36:59', '1 insert data at2020-06-27 07:36:59'),
(1157, 1, '2020-06-27 07:36:59', '1 insert data at2020-06-27 07:36:59'),
(1158, 1, '2020-06-27 07:36:59', '1 insert data at2020-06-27 07:36:59'),
(1159, 1, '2020-06-27 07:36:59', '1 insert data at2020-06-27 07:36:59'),
(1160, 1, '2020-06-27 07:49:21', '1 update data at 2020-06-27 07:49:21'),
(1161, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1162, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1163, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1164, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1165, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1166, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1167, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1168, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1169, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1170, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1171, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1172, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1173, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1174, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1175, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1176, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1177, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1178, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1179, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1180, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(1181, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1182, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(1183, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1184, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1185, 1, '2020-06-27 07:36:59', '1 update data at2020-06-27 07:36:59'),
(1186, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1187, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1188, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1189, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1190, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1191, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1192, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1193, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1194, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1195, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1196, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1197, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1198, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1199, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1200, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1201, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1202, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1203, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1204, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1205, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1206, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1207, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1208, 1, '2020-06-27 07:36:59', '1 update data at2020-06-27 07:36:59'),
(1209, 1, '2020-06-27 09:02:02', '1 insert data at2020-06-27 09:02:02'),
(1210, 1, '2020-06-27 09:19:09', '1 update data at2020-06-27 09:19:09'),
(1211, 1, '2020-06-27 09:19:15', '1 update data at2020-06-27 09:19:15'),
(1212, 1, '2020-06-27 09:20:39', '1 update data at2020-06-27 09:20:39'),
(1213, 1, '2020-06-27 09:20:39', '1 update data at2020-06-27 09:20:39'),
(1214, 1, '2020-06-27 11:05:31', '1 update data at2020-06-27 11:05:31'),
(1215, 1, '2020-06-27 11:05:32', '1 update data at2020-06-27 11:05:32'),
(1216, 1, '2020-06-27 11:05:32', '1 update data at2020-06-27 11:05:32'),
(1217, 1, '2020-06-27 11:05:32', '1 update data at2020-06-27 11:05:32'),
(1218, 1, '2020-06-27 11:05:32', '1 update data at2020-06-27 11:05:32'),
(1219, 1, '2020-06-27 11:07:26', '1 insert data at2020-06-27 11:07:26'),
(1220, 1, '2020-06-27 11:07:37', '1 update data at2020-06-27 11:07:37'),
(1221, 1, '2020-06-27 11:07:37', '1 update data at2020-06-27 11:07:37'),
(1222, 1, '2020-06-27 11:07:37', '1 update data at2020-06-27 11:07:37'),
(1223, 1, '2020-06-27 11:07:37', '1 update data at2020-06-27 11:07:37'),
(1224, 1, '2020-06-27 11:07:37', '1 update data at2020-06-27 11:07:37'),
(1225, 1, '2020-06-27 11:07:50', '1 update data at2020-06-27 11:07:50'),
(1226, 1, '2020-06-27 11:07:50', '1 update data at2020-06-27 11:07:50'),
(1227, 1, '2020-06-27 11:07:50', '1 update data at2020-06-27 11:07:50'),
(1228, 1, '2020-06-27 11:07:50', '1 update data at2020-06-27 11:07:50'),
(1229, 1, '2020-06-27 11:07:50', '1 update data at2020-06-27 11:07:50'),
(1230, 1, '2020-06-29 12:16:04', '1 insert data at2020-06-29 12:16:04'),
(1231, 1, '2020-06-29 12:16:14', '1 insert data at2020-06-29 12:16:14'),
(1232, 1, '2020-06-29 12:16:24', '1 insert data at2020-06-29 12:16:24'),
(1233, 1, '2020-06-29 12:16:32', '1 insert data at2020-06-29 12:16:32'),
(1234, 1, '2020-06-29 12:16:32', '1 insert data at2020-06-29 12:16:32'),
(1235, 1, '2020-06-29 12:16:42', '1 insert data at2020-06-29 12:16:42'),
(1236, 1, '2020-06-29 12:17:37', '1 insert data at2020-06-29 12:17:37'),
(1237, 1, '2020-06-29 12:17:37', '1 insert data at2020-06-29 12:17:37'),
(1238, 1, '2020-06-29 12:17:37', '1 insert data at2020-06-29 12:17:37'),
(1239, 1, '2020-06-29 12:17:37', '1 insert data at2020-06-29 12:17:37'),
(1240, 1, '2020-06-29 12:17:37', '1 insert data at2020-06-29 12:17:37'),
(1241, 1, '2020-06-29 12:17:53', '1 insert data at2020-06-29 12:17:53'),
(1242, 1, '2020-06-29 12:17:53', '1 insert data at2020-06-29 12:17:53'),
(1243, 1, '2020-06-29 12:17:53', '1 insert data at2020-06-29 12:17:53'),
(1244, 1, '2020-06-29 12:17:53', '1 insert data at2020-06-29 12:17:53'),
(1245, 1, '2020-06-29 12:18:09', '1 insert data at 2020-06-29 12:18:09'),
(1246, 1, '2020-06-29 01:51:03', '1 update data at 2020-06-29 01:51:03'),
(1247, 1, '2020-06-29 01:51:12', '1 update data at 2020-06-29 01:51:12'),
(1248, 1, '2020-06-29 04:45:45', '1 insert data at2020-06-29 04:45:45'),
(1249, 1, '2020-06-29 04:45:45', '1 insert data at2020-06-29 04:45:45'),
(1250, 1, '2020-06-29 04:50:34', '1 insert data at2020-06-29 04:50:34'),
(1251, 1, '2020-06-29 04:50:34', '1 insert data at2020-06-29 04:50:34'),
(1252, 1, '2020-06-29 04:55:55', '1 insert data at2020-06-29 04:55:55'),
(1253, 1, '2020-06-29 04:55:55', '1 insert data at2020-06-29 04:55:55'),
(1254, 1, '2020-06-29 04:55:55', '1 update data at 2020-06-29 04:55:55'),
(1255, 1, '2020-06-29 04:58:12', '1 insert data at2020-06-29 04:58:12'),
(1256, 1, '2020-06-29 04:58:12', '1 insert data at2020-06-29 04:58:12'),
(1257, 1, '2020-06-29 04:58:12', '1 update data at 2020-06-29 04:58:12'),
(1258, 1, '2020-06-29 05:01:57', '1 insert data at2020-06-29 05:01:57'),
(1259, 1, '2020-06-29 05:01:57', '1 insert data at2020-06-29 05:01:57'),
(1260, 1, '2020-06-29 05:01:57', '1 update data at 2020-06-29 05:01:57'),
(1261, 1, '2020-06-29 05:02:08', '1 insert data at2020-06-29 05:02:08'),
(1262, 1, '2020-06-29 05:02:08', '1 insert data at2020-06-29 05:02:08'),
(1263, 1, '2020-06-29 05:02:08', '1 update data at 2020-06-29 05:02:08'),
(1264, 1, '2020-06-29 05:02:18', '1 insert data at2020-06-29 05:02:18'),
(1265, 1, '2020-06-29 05:02:18', '1 insert data at2020-06-29 05:02:18'),
(1266, 1, '2020-06-29 05:02:18', '1 update data at 2020-06-29 05:02:18'),
(1267, 1, '2020-06-29 05:02:21', '1 insert data at2020-06-29 05:02:21'),
(1268, 1, '2020-06-29 05:02:21', '1 insert data at2020-06-29 05:02:21'),
(1269, 1, '2020-06-29 05:02:21', '1 update data at 2020-06-29 05:02:21'),
(1270, 1, '2020-06-29 05:02:24', '1 insert data at2020-06-29 05:02:24'),
(1271, 1, '2020-06-29 05:02:24', '1 insert data at2020-06-29 05:02:24'),
(1272, 1, '2020-06-29 05:02:24', '1 update data at 2020-06-29 05:02:24'),
(1273, 1, '2020-06-29 05:26:28', '1 update data at2020-06-29 05:26:28'),
(1274, 1, '2020-06-29 05:26:28', '1 update data at2020-06-29 05:26:28'),
(1275, 1, '2020-06-29 05:33:23', '1 update data at2020-06-29 05:33:23'),
(1276, 1, '2020-06-29 05:33:23', '1 update data at2020-06-29 05:33:23'),
(1277, 1, '2020-06-29 05:33:37', '1 update data at2020-06-29 05:33:37'),
(1278, 1, '2020-06-29 05:33:37', '1 update data at2020-06-29 05:33:37'),
(1279, 1, '2020-06-29 05:40:13', '1 update data at2020-06-29 05:40:13'),
(1280, 1, '2020-06-29 05:40:13', '1 update data at2020-06-29 05:40:13'),
(1281, 1, '2020-06-29 05:02:24', '1 update data at 2020-06-29 05:02:24'),
(1282, 1, '2020-06-29 05:02:18', '1 update data at 2020-06-29 05:02:18'),
(1283, 1, '2020-06-29 05:02:21', '1 update data at 2020-06-29 05:02:21'),
(1284, 1, '2020-06-29 05:02:08', '1 update data at 2020-06-29 05:02:08'),
(1285, 1, '2020-06-29 05:44:13', '1 insert data at2020-06-29 05:44:13'),
(1286, 1, '2020-06-29 05:44:14', '1 insert data at2020-06-29 05:44:14'),
(1287, 1, '2020-06-29 05:44:14', '1 update data at 2020-06-29 05:44:14'),
(1288, 1, '2020-06-29 05:44:27', '1 insert data at2020-06-29 05:44:27'),
(1289, 1, '2020-06-29 05:44:27', '1 insert data at2020-06-29 05:44:27'),
(1290, 1, '2020-06-29 05:44:27', '1 update data at 2020-06-29 05:44:27'),
(1291, 1, '2020-06-29 05:44:49', '1 insert data at2020-06-29 05:44:49'),
(1292, 1, '2020-06-29 05:44:49', '1 insert data at2020-06-29 05:44:49'),
(1293, 1, '2020-06-29 05:44:49', '1 update data at 2020-06-29 05:44:49'),
(1294, 1, '2020-06-29 05:44:54', '1 insert data at2020-06-29 05:44:54'),
(1295, 1, '2020-06-29 05:44:54', '1 insert data at2020-06-29 05:44:54'),
(1296, 1, '2020-06-29 05:44:54', '1 update data at 2020-06-29 05:44:54'),
(1297, 1, '2020-06-29 05:44:14', '1 update data at 2020-06-29 05:44:14'),
(1298, 1, '2020-06-29 05:44:54', '1 update data at 2020-06-29 05:44:54'),
(1299, 1, '2020-06-29 05:44:49', '1 update data at 2020-06-29 05:44:49'),
(1300, 1, '2020-06-29 05:44:27', '1 update data at 2020-06-29 05:44:27'),
(1301, 1, '2020-06-29 05:50:13', '1 insert data at2020-06-29 05:50:13'),
(1302, 1, '2020-06-29 05:50:13', '1 insert data at2020-06-29 05:50:13'),
(1303, 1, '2020-06-29 05:50:13', '1 update data at 2020-06-29 05:50:13'),
(1304, 1, '2020-06-29 05:52:47', '1 insert data at2020-06-29 05:52:47'),
(1305, 1, '2020-06-29 05:52:47', '1 insert data at2020-06-29 05:52:47'),
(1306, 1, '2020-06-29 05:52:47', '1 update data at 2020-06-29 05:52:47'),
(1307, 1, '2020-06-29 05:53:44', '1 insert data at2020-06-29 05:53:44'),
(1308, 1, '2020-06-29 05:53:44', '1 insert data at2020-06-29 05:53:44'),
(1309, 1, '2020-06-29 05:53:44', '1 update data at 2020-06-29 05:53:44'),
(1310, 1, '2020-06-29 05:54:11', '1 insert data at2020-06-29 05:54:11'),
(1311, 1, '2020-06-29 05:54:11', '1 insert data at2020-06-29 05:54:11'),
(1312, 1, '2020-06-29 05:54:11', '1 update data at 2020-06-29 05:54:11'),
(1313, 1, '2020-06-29 05:54:56', '1 update data at2020-06-29 05:54:56'),
(1314, 1, '2020-06-29 05:54:56', '1 update data at2020-06-29 05:54:56'),
(1315, 1, '2020-06-29 05:54:11', '1 update data at 2020-06-29 05:54:11'),
(1316, 1, '2020-06-29 05:52:47', '1 update data at 2020-06-29 05:52:47'),
(1317, 1, '2020-06-29 05:50:13', '1 update data at 2020-06-29 05:50:13'),
(1318, 1, '2020-06-29 05:53:44', '1 update data at 2020-06-29 05:53:44'),
(1319, 1, '2020-06-29 05:55:39', '1 insert data at2020-06-29 05:55:39'),
(1320, 1, '2020-06-29 05:55:39', '1 insert data at2020-06-29 05:55:39'),
(1321, 1, '2020-06-29 05:55:39', '1 update data at 2020-06-29 05:55:39'),
(1322, 1, '2020-06-29 05:55:43', '1 insert data at2020-06-29 05:55:43'),
(1323, 1, '2020-06-29 05:55:43', '1 insert data at2020-06-29 05:55:43'),
(1324, 1, '2020-06-29 05:55:43', '1 update data at 2020-06-29 05:55:43'),
(1325, 1, '2020-06-29 05:55:46', '1 insert data at2020-06-29 05:55:46'),
(1326, 1, '2020-06-29 05:55:46', '1 insert data at2020-06-29 05:55:46'),
(1327, 1, '2020-06-29 05:55:46', '1 update data at 2020-06-29 05:55:46'),
(1328, 1, '2020-06-29 05:55:49', '1 insert data at2020-06-29 05:55:49'),
(1329, 1, '2020-06-29 05:55:49', '1 insert data at2020-06-29 05:55:49'),
(1330, 1, '2020-06-29 05:55:49', '1 update data at 2020-06-29 05:55:49'),
(1331, 1, '2020-06-29 05:55:59', '1 update data at2020-06-29 05:55:59'),
(1332, 1, '2020-06-29 05:55:59', '1 update data at2020-06-29 05:55:59'),
(1333, 1, '2020-06-29 05:55:59', '1 update data at 2020-06-29 05:55:59'),
(1334, 1, '2020-06-29 05:56:03', '1 update data at2020-06-29 05:56:03'),
(1335, 1, '2020-06-29 05:56:03', '1 update data at2020-06-29 05:56:03'),
(1336, 1, '2020-06-29 05:56:03', '1 update data at 2020-06-29 05:56:03'),
(1337, 1, '2020-06-29 05:59:17', '1 insert data at2020-06-29 05:59:17'),
(1338, 1, '2020-06-29 05:59:17', '1 insert data at2020-06-29 05:59:17'),
(1339, 1, '2020-06-29 05:59:17', '1 update data at 2020-06-29 05:59:17'),
(1340, 1, '2020-06-29 06:02:14', '1 insert data at2020-06-29 06:02:14'),
(1341, 1, '2020-06-29 06:02:14', '1 insert data at2020-06-29 06:02:14'),
(1342, 1, '2020-06-29 06:02:14', '1 update data at 2020-06-29 06:02:14'),
(1343, 1, '2020-06-29 06:04:32', '1 update data at2020-06-29 06:04:32'),
(1344, 1, '2020-06-29 06:04:32', '1 update data at2020-06-29 06:04:32'),
(1345, 1, '2020-06-29 06:04:32', '1 update data at 2020-06-29 06:04:32'),
(1346, 1, '2020-06-29 06:04:37', '1 insert data at2020-06-29 06:04:37'),
(1347, 1, '2020-06-29 06:04:37', '1 insert data at2020-06-29 06:04:37'),
(1348, 1, '2020-06-29 06:04:37', '1 update data at 2020-06-29 06:04:37'),
(1349, 1, '2020-06-29 06:07:05', '1 update data at2020-06-29 06:07:05'),
(1350, 1, '2020-06-29 06:07:05', '1 update data at2020-06-29 06:07:05'),
(1351, 1, '2020-06-29 06:07:05', '1 update data at 2020-06-29 06:07:05'),
(1352, 1, '2020-06-29 06:07:08', '1 insert data at2020-06-29 06:07:08'),
(1353, 1, '2020-06-29 06:07:08', '1 insert data at2020-06-29 06:07:08'),
(1354, 1, '2020-06-29 05:55:43', '1 update data at 2020-06-29 05:55:43'),
(1355, 1, '2020-06-29 05:55:39', '1 update data at 2020-06-29 05:55:39'),
(1356, 1, '2020-06-29 05:59:17', '1 update data at 2020-06-29 05:59:17'),
(1357, 1, '2020-06-29 06:09:05', '1 insert data at2020-06-29 06:09:05'),
(1358, 1, '2020-06-29 06:09:05', '1 insert data at2020-06-29 06:09:05'),
(1359, 1, '2020-06-29 06:09:34', '1 insert data at2020-06-29 06:09:34'),
(1360, 1, '2020-06-29 06:09:34', '1 insert data at2020-06-29 06:09:34'),
(1361, 1, '2020-06-29 06:10:03', '1 insert data at2020-06-29 06:10:03'),
(1362, 1, '2020-06-29 06:10:03', '1 insert data at2020-06-29 06:10:03'),
(1363, 1, '2020-06-29 06:10:03', '1 update data at 2020-06-29 06:10:03'),
(1364, 1, '2020-06-29 06:10:26', '1 insert data at2020-06-29 06:10:26'),
(1365, 1, '2020-06-29 06:10:27', '1 insert data at2020-06-29 06:10:27'),
(1366, 1, '2020-06-29 06:10:27', '1 update data at 2020-06-29 06:10:27'),
(1367, 1, '2020-06-29 06:10:43', '1 insert data at2020-06-29 06:10:43'),
(1368, 1, '2020-06-29 06:10:43', '1 insert data at2020-06-29 06:10:43'),
(1369, 1, '2020-06-29 06:10:43', '1 update data at 2020-06-29 06:10:43'),
(1370, 1, '2020-06-29 06:11:11', '1 insert data at2020-06-29 06:11:11'),
(1371, 1, '2020-06-29 06:11:11', '1 insert data at2020-06-29 06:11:11'),
(1372, 1, '2020-06-29 06:11:38', '1 insert data at2020-06-29 06:11:38'),
(1373, 1, '2020-06-29 06:11:38', '1 insert data at2020-06-29 06:11:38'),
(1374, 1, '2020-06-29 06:11:47', '1 insert data at2020-06-29 06:11:47'),
(1375, 1, '2020-06-29 06:11:47', '1 insert data at2020-06-29 06:11:47'),
(1376, 1, '2020-06-29 06:11:58', '1 insert data at2020-06-29 06:11:58'),
(1377, 1, '2020-06-29 06:11:58', '1 insert data at2020-06-29 06:11:58'),
(1378, 1, '2020-06-29 06:13:19', '1 insert data at2020-06-29 06:13:19'),
(1379, 1, '2020-06-29 06:13:19', '1 insert data at2020-06-29 06:13:19'),
(1380, 1, '2020-06-29 06:18:49', '1 insert data at2020-06-29 06:18:49'),
(1381, 1, '2020-06-29 06:18:49', '1 insert data at2020-06-29 06:18:49'),
(1382, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1383, 1, '2020-06-29 06:18:49', '1 update data at 2020-06-29 06:18:49'),
(1384, 1, '2020-06-29 06:18:59', '1 update data at2020-06-29 06:18:59'),
(1385, 1, '2020-06-29 06:18:59', '1 update data at2020-06-29 06:18:59'),
(1386, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1387, 1, '2020-06-29 06:18:59', '1 update data at 2020-06-29 06:18:59'),
(1388, 1, '2020-06-29 06:26:23', '1 update data at2020-06-29 06:26:23'),
(1389, 1, '2020-06-29 06:26:23', '1 update data at2020-06-29 06:26:23'),
(1390, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1391, 1, '2020-06-29 06:26:23', '1 update data at 2020-06-29 06:26:23'),
(1392, 1, '2020-06-29 06:26:31', '1 insert data at2020-06-29 06:26:31'),
(1393, 1, '2020-06-29 06:26:31', '1 insert data at2020-06-29 06:26:31'),
(1394, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1395, 1, '2020-06-29 06:26:31', '1 update data at 2020-06-29 06:26:31'),
(1396, 1, '2020-06-29 06:26:37', '1 update data at2020-06-29 06:26:37'),
(1397, 1, '2020-06-29 06:26:37', '1 update data at2020-06-29 06:26:37'),
(1398, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1399, 1, '2020-06-29 06:26:37', '1 update data at 2020-06-29 06:26:37'),
(1400, 1, '2020-06-29 06:27:12', '1 insert data at2020-06-29 06:27:12'),
(1401, 1, '2020-06-29 06:27:13', '1 insert data at2020-06-29 06:27:13'),
(1402, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1403, 1, '2020-06-29 06:27:13', '1 insert data at2020-06-29 06:27:13'),
(1404, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1405, 1, '2020-06-29 06:27:36', '1 update data at2020-06-29 06:27:36'),
(1406, 1, '2020-06-29 06:27:36', '1 update data at2020-06-29 06:27:36'),
(1407, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1408, 1, '2020-06-29 06:27:36', '1 update data at2020-06-29 06:27:36'),
(1409, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1410, 1, '2020-06-29 06:27:46', '1 update data at2020-06-29 06:27:46'),
(1411, 1, '2020-06-29 06:27:46', '1 update data at2020-06-29 06:27:46'),
(1412, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1413, 1, '2020-06-29 06:27:46', '1 update data at2020-06-29 06:27:46'),
(1414, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1415, 1, '2020-06-30 09:26:26', '1 insert data at2020-06-30 09:26:26'),
(1416, 1, '2020-06-30 09:26:26', '1 insert data at2020-06-30 09:26:26');
INSERT INTO `log_all` (`id_log_all`, `id_user`, `log_date`, `log`) VALUES
(1417, 1, '2020-06-30 09:26:26', '1 insert data at2020-06-30 09:26:26'),
(1418, 1, '2020-06-30 09:26:26', '1 insert data at2020-06-30 09:26:26'),
(1419, 1, '2020-06-30 09:26:26', '1 insert data at2020-06-30 09:26:26'),
(1420, 1, '2020-06-30 09:26:57', '1 update data at 2020-06-30 09:26:57'),
(1421, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1422, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1423, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1424, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1425, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1426, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1427, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1428, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1429, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1430, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1431, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1432, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1433, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1434, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1435, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1436, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1437, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1438, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1439, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1440, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(1441, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1442, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(1443, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1444, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1445, 1, '2020-06-27 07:36:59', '1 update data at2020-06-27 07:36:59'),
(1446, 1, '2020-06-30 09:26:26', '1 update data at2020-06-30 09:26:26'),
(1447, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1448, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1449, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1450, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1451, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1452, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1453, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1454, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1455, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1456, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1457, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1458, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1459, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1460, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1461, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1462, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1463, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1464, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1465, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1466, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1467, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1468, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1469, 1, '2020-06-27 07:36:59', '1 update data at2020-06-27 07:36:59'),
(1470, 1, '2020-06-30 09:26:26', '1 update data at2020-06-30 09:26:26'),
(1471, 1, '2020-06-30 09:31:31', '1 update data at2020-06-30 09:31:31'),
(1472, 1, '2020-07-01 12:08:45', '1 insert data at2020-07-01 12:08:45'),
(1473, 1, '2020-07-01 12:08:45', '1 insert data at2020-07-01 12:08:45'),
(1474, 1, '2020-07-01 12:10:36', '1 insert data at2020-07-01 12:10:36'),
(1475, 1, '2020-07-01 12:10:36', '1 insert data at2020-07-01 12:10:36'),
(1476, 1, '2020-07-01 12:11:01', '1 insert data at2020-07-01 12:11:01'),
(1477, 1, '2020-07-01 12:11:01', '1 insert data at2020-07-01 12:11:01'),
(1478, 1, '2020-07-01 12:11:27', '1 insert data at2020-07-01 12:11:27'),
(1479, 1, '2020-07-01 12:11:27', '1 insert data at2020-07-01 12:11:27'),
(1480, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1481, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1482, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(1483, 1, '2020-07-01 12:11:27', '1 update data at 2020-07-01 12:11:27'),
(1484, 1, '2020-07-01 12:12:32', '1 insert data at2020-07-01 12:12:32'),
(1485, 1, '2020-07-01 12:12:32', '1 insert data at2020-07-01 12:12:32'),
(1486, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1487, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1488, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(1489, 1, '2020-07-01 12:12:32', '1 update data at 2020-07-01 12:12:32'),
(1490, 1, '2020-07-01 12:13:14', '1 insert data at2020-07-01 12:13:14'),
(1491, 1, '2020-07-01 12:13:14', '1 insert data at2020-07-01 12:13:14'),
(1492, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1493, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1494, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(1495, 1, '2020-07-01 12:13:14', '1 update data at 2020-07-01 12:13:14'),
(1496, 1, '2020-07-01 12:13:38', '1 insert data at2020-07-01 12:13:38'),
(1497, 1, '2020-07-01 12:13:39', '1 insert data at2020-07-01 12:13:39'),
(1498, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1499, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1500, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(1501, 1, '2020-07-01 12:13:39', '1 update data at 2020-07-01 12:13:39'),
(1502, 1, '2020-07-01 12:14:19', '1 insert data at2020-07-01 12:14:19'),
(1503, 1, '2020-07-01 12:14:19', '1 insert data at2020-07-01 12:14:19'),
(1504, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1505, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1506, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(1507, 1, '2020-07-01 12:14:19', '1 update data at 2020-07-01 12:14:19'),
(1508, 1, '2020-07-01 12:17:51', '1 insert data at2020-07-01 12:17:51'),
(1509, 1, '2020-07-01 12:17:51', '1 insert data at2020-07-01 12:17:51'),
(1510, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1511, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1512, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(1513, 1, '2020-07-01 12:17:51', '1 update data at 2020-07-01 12:17:51'),
(1514, 1, '2020-07-01 12:18:24', '1 insert data at2020-07-01 12:18:24'),
(1515, 1, '2020-07-01 12:18:24', '1 insert data at2020-07-01 12:18:24'),
(1516, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1517, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1518, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(1519, 1, '2020-07-01 12:18:24', '1 update data at 2020-07-01 12:18:24'),
(1520, 1, '2020-07-01 12:28:17', '1 update data at2020-07-01 12:28:17'),
(1521, 1, '2020-07-01 12:28:17', '1 update data at2020-07-01 12:28:17'),
(1522, 1, '2020-07-01 12:28:17', '1 update data at 2020-07-01 12:28:17'),
(1523, 1, '2020-07-01 12:28:21', '1 update data at2020-07-01 12:28:21'),
(1524, 1, '2020-07-01 12:28:21', '1 update data at2020-07-01 12:28:21'),
(1525, 1, '2020-07-01 12:28:21', '1 update data at 2020-07-01 12:28:21'),
(1526, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1527, 1, '2020-07-01 12:29:02', '1 insert data at2020-07-01 12:29:02'),
(1528, 1, '2020-07-01 12:29:02', '1 insert data at2020-07-01 12:29:02'),
(1529, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1530, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1531, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1532, 1, '2020-07-01 12:29:02', '1 update data at 2020-07-01 12:29:02'),
(1533, 1, '2020-07-01 12:29:09', '1 update data at2020-07-01 12:29:09'),
(1534, 1, '2020-07-01 12:29:10', '1 update data at2020-07-01 12:29:10'),
(1535, 1, '2020-07-01 12:29:10', '1 update data at 2020-07-01 12:29:10'),
(1536, 1, '2020-07-01 12:29:20', '1 insert data at2020-07-01 12:29:20'),
(1537, 1, '2020-07-01 12:29:20', '1 insert data at2020-07-01 12:29:20'),
(1538, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1539, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1540, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1541, 1, '2020-07-01 12:29:20', '1 update data at 2020-07-01 12:29:20'),
(1542, 1, '2020-07-01 12:29:22', '1 insert data at2020-07-01 12:29:22'),
(1543, 1, '2020-07-01 12:29:22', '1 insert data at2020-07-01 12:29:22'),
(1544, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1545, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1546, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1547, 1, '2020-07-01 12:29:22', '1 update data at 2020-07-01 12:29:22'),
(1548, 1, '2020-07-01 12:29:30', '1 update data at2020-07-01 12:29:30'),
(1549, 1, '2020-07-01 12:29:30', '1 update data at2020-07-01 12:29:30'),
(1550, 1, '2020-07-01 12:29:30', '1 update data at 2020-07-01 12:29:30'),
(1551, 1, '2020-07-01 12:30:28', '1 update data at2020-07-01 12:30:28'),
(1552, 1, '2020-07-01 12:30:28', '1 update data at2020-07-01 12:30:28'),
(1553, 1, '2020-07-01 12:30:28', '1 update data at 2020-07-01 12:30:28'),
(1554, 1, '2020-07-01 12:31:57', '1 insert data at2020-07-01 12:31:57'),
(1555, 1, '2020-07-01 12:31:57', '1 insert data at2020-07-01 12:31:57'),
(1556, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1557, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1558, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1559, 1, '2020-07-01 12:31:57', '1 update data at 2020-07-01 12:31:57'),
(1560, 1, '2020-07-01 12:32:03', '1 update data at2020-07-01 12:32:03'),
(1561, 1, '2020-07-01 12:32:03', '1 update data at2020-07-01 12:32:03'),
(1562, 1, '2020-07-01 12:32:03', '1 update data at 2020-07-01 12:32:03'),
(1563, 1, '2020-07-01 12:33:04', '1 insert data at2020-07-01 12:33:04'),
(1564, 1, '2020-07-01 12:33:04', '1 insert data at2020-07-01 12:33:04'),
(1565, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1566, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1567, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1568, 1, '2020-07-01 12:33:05', '1 update data at 2020-07-01 12:33:05'),
(1569, 1, '2020-07-01 12:33:11', '1 update data at2020-07-01 12:33:11'),
(1570, 1, '2020-07-01 12:33:11', '1 update data at2020-07-01 12:33:11'),
(1571, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1572, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1573, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1574, 1, '2020-07-01 12:33:11', '1 update data at 2020-07-01 12:33:11'),
(1575, 1, '2020-07-01 12:36:05', '1 insert data at2020-07-01 12:36:05'),
(1576, 1, '2020-07-01 12:36:05', '1 insert data at2020-07-01 12:36:05'),
(1577, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1578, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1579, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1580, 1, '2020-07-01 12:36:05', '1 insert data at2020-07-01 12:36:05'),
(1581, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1582, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1583, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1584, 1, '2020-07-01 12:36:21', '1 update data at2020-07-01 12:36:21'),
(1585, 1, '2020-07-01 12:36:21', '1 update data at2020-07-01 12:36:21'),
(1586, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1587, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1588, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1589, 1, '2020-07-01 12:36:21', '1 update data at2020-07-01 12:36:21'),
(1590, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1591, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1592, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1593, 1, '2020-07-02 10:02:33', '1 update data at2020-07-02 10:02:33'),
(1594, 1, '2020-07-02 10:02:39', '1 update data at2020-07-02 10:02:39'),
(1595, 1, '2020-07-02 10:02:45', '1 update data at2020-07-02 10:02:45'),
(1596, 1, '2020-07-02 10:02:50', '1 update data at2020-07-02 10:02:50'),
(1597, 1, '2020-07-02 10:02:56', '1 update data at2020-07-02 10:02:56'),
(1598, 1, '2020-07-02 10:03:00', '1 update data at2020-07-02 10:03:00'),
(1599, 1, '2020-07-02 10:03:08', '1 update data at2020-07-02 10:03:08'),
(1600, 1, '2020-07-02 10:03:13', '1 update data at2020-07-02 10:03:13'),
(1601, 1, '2020-07-02 10:03:24', '1 update data at2020-07-02 10:03:24'),
(1602, 1, '2020-07-02 10:03:30', '1 update data at2020-07-02 10:03:30'),
(1603, 1, '2020-07-02 10:03:35', '1 update data at2020-07-02 10:03:35'),
(1604, 1, '2020-07-02 10:03:42', '1 update data at2020-07-02 10:03:42'),
(1605, 1, '2020-07-02 10:03:50', '1 update data at2020-07-02 10:03:50'),
(1606, 1, '2020-07-02 10:03:58', '1 update data at2020-07-02 10:03:58'),
(1607, 1, '2020-07-02 10:04:04', '1 update data at2020-07-02 10:04:04'),
(1608, 1, '2020-07-02 10:04:11', '1 update data at2020-07-02 10:04:11'),
(1609, 1, '2020-07-02 10:04:16', '1 update data at2020-07-02 10:04:16'),
(1610, 1, '2020-07-02 10:04:21', '1 update data at2020-07-02 10:04:21'),
(1611, 1, '2020-07-02 10:04:26', '1 update data at2020-07-02 10:04:26'),
(1612, 1, '2020-07-02 10:04:30', '1 update data at2020-07-02 10:04:30'),
(1613, 1, '2020-07-02 10:04:35', '1 update data at2020-07-02 10:04:35'),
(1614, 1, '2020-07-02 10:04:39', '1 update data at2020-07-02 10:04:39'),
(1615, 1, '2020-07-02 10:04:45', '1 update data at2020-07-02 10:04:45'),
(1616, 1, '2020-07-02 10:04:49', '1 update data at2020-07-02 10:04:49'),
(1617, 1, '2020-07-02 10:04:53', '1 update data at2020-07-02 10:04:53'),
(1618, 1, '2020-07-02 10:04:57', '1 update data at2020-07-02 10:04:57'),
(1619, 1, '2020-07-02 05:36:41', '1 insert data at 2020-07-02 05:36:41'),
(1620, 1, '2020-07-02 05:39:25', '1 insert data at 2020-07-02 05:39:25'),
(1621, 1, '2020-07-02 05:40:03', '1 insert data at 2020-07-02 05:40:03'),
(1622, 1, '2020-07-02 05:40:45', '1 insert data at 2020-07-02 05:40:45'),
(1623, 1, '2020-07-02 07:03:22', '1 update data at 2020-07-02 07:03:22'),
(1624, 1, '2020-07-02 07:03:58', '1 update data at 2020-07-02 07:03:58'),
(1625, 1, '2020-07-02 07:30:05', '1 insert data at 2020-07-02 07:30:05'),
(1626, 1, '2020-07-02 07:37:39', '1 insert data at 2020-07-02 07:37:39'),
(1627, 1, '2020-07-02 08:21:23', '1 insert data at2020-07-02 08:21:23'),
(1628, 1, '2020-07-02 08:28:43', '1 update data at2020-07-02 08:28:43'),
(1629, 1, '2020-07-02 08:36:54', '1 update data at2020-07-02 08:36:54'),
(1630, 1, '2020-07-02 08:37:09', '1 update data at2020-07-02 08:37:09'),
(1631, 1, '2020-07-02 09:36:12', '1 insert data at 2020-07-02 09:36:12'),
(1632, 1, '2020-07-02 09:53:34', '1 update data at 2020-07-02 09:53:34'),
(1633, 1, '2020-07-02 09:54:33', '1 update data at 2020-07-02 09:54:33'),
(1634, 1, '2020-07-02 09:55:05', '1 update data at 2020-07-02 09:55:05'),
(1635, 1, '2020-07-02 10:10:14', '1 insert data at2020-07-02 10:10:14'),
(1636, 1, '2020-07-02 10:10:56', '1 update data at2020-07-02 10:10:56'),
(1637, 1, '2020-07-02 11:03:30', '1 insert data at2020-07-02 11:03:30'),
(1638, 1, '2020-07-02 11:03:30', '1 insert data at2020-07-02 11:03:30'),
(1639, 1, '2020-07-02 11:03:30', '1 insert data at2020-07-02 11:03:30'),
(1640, 1, '2020-07-02 11:03:30', '1 insert data at2020-07-02 11:03:30'),
(1641, 1, '2020-07-02 11:03:30', '1 insert data at2020-07-02 11:03:30'),
(1642, 1, '2020-07-02 11:03:48', '1 update data at 2020-07-02 11:03:48'),
(1643, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1644, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1645, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1646, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1647, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1648, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1649, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1650, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1651, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1652, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1653, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1654, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1655, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1656, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1657, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1658, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1659, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1660, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1661, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1662, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(1663, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1664, 1, '2020-06-22 12:32:52', '1 update data at2020-06-22 12:32:52'),
(1665, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1666, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1667, 1, '2020-06-27 07:36:59', '1 update data at2020-06-27 07:36:59'),
(1668, 1, '2020-06-30 09:26:26', '1 update data at2020-06-30 09:26:26'),
(1669, 1, '2020-07-02 11:03:30', '1 update data at2020-07-02 11:03:30'),
(1670, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1671, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(1672, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(1673, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(1674, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(1675, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(1676, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(1677, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(1678, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(1679, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(1680, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(1681, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(1682, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(1683, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(1684, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(1685, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(1686, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(1687, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(1688, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(1689, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(1690, 1, '2020-06-22 06:10:33', '1 update data at2020-06-22 06:10:33'),
(1691, 1, '2020-06-26 10:07:22', '1 update data at2020-06-26 10:07:22'),
(1692, 1, '2020-06-27 07:36:59', '1 update data at2020-06-27 07:36:59'),
(1693, 1, '2020-06-30 09:26:26', '1 update data at2020-06-30 09:26:26'),
(1694, 1, '2020-07-02 11:03:30', '1 update data at2020-07-02 11:03:30'),
(1695, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(1696, 1, '2020-06-22 06:55:38', '1 update data at2020-06-22 06:55:38'),
(1697, 1, '2020-06-27 11:07:50', '1 update data at2020-06-27 11:07:50'),
(1698, 1, '2020-06-22 06:45:42', '1 update data at2020-06-22 06:45:42'),
(1699, 1, '2020-06-22 06:45:40', '1 update data at2020-06-22 06:45:40'),
(1700, 1, '2020-06-22 06:45:37', '1 update data at2020-06-22 06:45:37'),
(1701, 1, '2020-06-22 06:45:35', '1 update data at2020-06-22 06:45:35'),
(1702, 1, '2020-06-22 06:44:21', '1 update data at2020-06-22 06:44:21'),
(1703, 1, '2020-06-22 06:45:33', '1 update data at2020-06-22 06:45:33'),
(1704, 1, '2020-07-03 18:21:11', '1 update data at2020-07-03 18:21:11'),
(1705, 1, '2020-07-03 06:21:30', '1 update data at2020-07-03 06:21:30'),
(1706, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1707, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1708, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1709, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1710, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1711, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1712, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1713, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1714, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1715, 1, '2020-06-22 01:46:33', '1 update data at 2020-06-22 01:46:33'),
(1716, 1, '2020-06-22 01:46:33', '1 update data at 2020-06-22 01:46:33'),
(1717, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1718, 1, '2020-06-23 10:46:08', '1 update data at 2020-06-23 10:46:08'),
(1719, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1720, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1721, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1722, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1723, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1724, 1, '2020-07-03 07:14:26', '1 insert data at2020-07-03 07:14:26'),
(1725, 1, '2020-07-04 08:16:25', '1 update data at 2020-07-04 08:16:25'),
(1726, 1, '2020-07-04 08:55:46', '1 update data at 2020-07-04 08:55:46'),
(1727, 1, '2020-07-04 09:08:25', '1 insert data at 2020-07-04 09:08:25'),
(1728, 1, '2020-07-04 09:08:25', '1 insert data at 2020-07-04 09:08:25'),
(1729, 1, '2020-07-04 09:11:07', '1 update data at 2020-07-04 09:11:07'),
(1730, 1, '2020-07-04 09:11:07', '1 insert data at 2020-07-04 09:11:07'),
(1731, 1, '2020-07-04 09:11:13', '1 update data at 2020-07-04 09:11:13'),
(1732, 1, '2020-07-04 09:11:13', '1 insert data at 2020-07-04 09:11:13'),
(1733, 1, '2020-07-04 09:11:27', '1 update data at 2020-07-04 09:11:27'),
(1734, 1, '2020-07-04 09:11:50', '1 update data at 2020-07-04 09:11:50'),
(1735, 1, '2020-07-04 09:11:50', '1 insert data at 2020-07-04 09:11:50'),
(1736, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1737, 1, '2020-07-04 09:16:55', '1 update data at 2020-07-04 09:16:55'),
(1738, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1739, 1, '2020-07-04 10:18:46', '1 insert data at 2020-07-04 10:18:46'),
(1740, 1, '2020-07-04 10:18:46', '1 insert data at 2020-07-04 10:18:46'),
(1741, 1, '2020-07-04 10:18:46', '1 insert data at 2020-07-04 10:18:46'),
(1742, 1, '2020-07-04 10:25:03', '1 update data at 2020-07-04 10:25:03'),
(1743, 1, '2020-07-04 10:25:06', '1 update data at 2020-07-04 10:25:06'),
(1744, 1, '2020-07-04 10:25:10', '1 update data at 2020-07-04 10:25:10'),
(1745, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1746, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1747, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1748, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1749, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1750, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1751, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1752, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1753, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1754, 1, '2020-07-04 10:25:03', '1 update data at 2020-07-04 10:25:03'),
(1755, 1, '2020-07-04 08:16:25', '1 update data at 2020-07-04 08:16:25'),
(1756, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1757, 1, '2020-07-04 10:25:10', '1 update data at 2020-07-04 10:25:10'),
(1758, 1, '2020-06-22 05:28:46', '1 update data at 2020-06-22 05:28:46'),
(1759, 1, '2020-07-04 08:55:46', '1 update data at 2020-07-04 08:55:46'),
(1760, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1761, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1762, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1763, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1764, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1765, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1766, 1, '2020-07-04 09:11:07', '1 update data at 2020-07-04 09:11:07'),
(1767, 1, '2020-07-04 09:11:13', '1 update data at 2020-07-04 09:11:13'),
(1768, 1, '2020-07-04 09:11:50', '1 update data at 2020-07-04 09:11:50'),
(1769, 1, '2020-07-04 09:16:55', '1 update data at 2020-07-04 09:16:55'),
(1770, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1771, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1772, 1, '2020-07-04 10:25:06', '1 update data at 2020-07-04 10:25:06'),
(1773, 1, '2020-07-04 10:27:00', '1 update data at2020-07-04 10:27:00'),
(1774, 1, '2020-07-04 10:27:00', '1 update data at2020-07-04 10:27:00'),
(1775, 1, '2020-07-04 10:27:00', '1 update data at2020-07-04 10:27:00'),
(1776, 1, '2020-07-04 10:27:10', '1 update data at2020-07-04 10:27:10'),
(1777, 1, '2020-07-04 10:27:10', '1 update data at2020-07-04 10:27:10'),
(1778, 1, '2020-07-04 10:27:10', '1 update data at2020-07-04 10:27:10'),
(1779, 1, '2020-07-04 10:27:19', '1 update data at2020-07-04 10:27:19'),
(1780, 1, '2020-07-04 10:27:19', '1 update data at2020-07-04 10:27:19'),
(1781, 1, '2020-07-04 10:27:19', '1 update data at2020-07-04 10:27:19'),
(1782, 1, '2020-07-04 10:27:27', '1 update data at2020-07-04 10:27:27'),
(1783, 1, '2020-07-04 10:27:27', '1 update data at2020-07-04 10:27:27'),
(1784, 1, '2020-07-04 10:27:27', '1 update data at2020-07-04 10:27:27'),
(1785, 1, '2020-07-04 10:27:50', '1 update data at2020-07-04 10:27:50'),
(1786, 1, '2020-07-04 10:27:59', '1 update data at2020-07-04 10:27:59'),
(1787, 1, '2020-07-04 10:27:59', '1 update data at2020-07-04 10:27:59'),
(1788, 1, '2020-07-04 10:28:29', '1 update data at2020-07-04 10:28:29'),
(1789, 1, '2020-07-04 10:28:29', '1 update data at2020-07-04 10:28:29'),
(1790, 1, '2020-07-04 10:28:29', '1 insert data at2020-07-04 10:28:29'),
(1791, 1, '2020-07-04 10:28:29', '1 insert data at2020-07-04 10:28:29'),
(1792, 1, '2020-07-04 10:29:14', '1 update data at 2020-07-04 10:29:14'),
(1793, 1, '2020-07-04 10:29:26', '1 update data at 2020-07-04 10:29:26'),
(1794, 1, '2020-07-04 10:30:42', '1 update data at 2020-07-04 10:30:42'),
(1795, 1, '2020-07-04 12:04:55', '1 update data at2020-07-04 12:04:55'),
(1796, 1, '2020-07-04 12:04:55', '1 update data at2020-07-04 12:04:55'),
(1797, 1, '2020-07-04 12:04:55', '1 update data at2020-07-04 12:04:55'),
(1798, 1, '2020-07-04 12:04:55', '1 update data at2020-07-04 12:04:55'),
(1799, 1, '2020-07-04 13:57:22', '1 insert data at2020-07-04 13:57:22'),
(1800, 1, '2020-07-04 14:20:52', '1 insert data at2020-07-04 14:20:52'),
(1801, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1802, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1803, 1, '2020-07-04 10:25:03', '1 update data at 2020-07-04 10:25:03'),
(1804, 1, '2020-07-04 10:25:10', '1 update data at 2020-07-04 10:25:10'),
(1805, 1, '2020-07-04 14:21:15', '1 insert data at2020-07-04 14:21:15'),
(1806, 1, '2020-07-04 10:25:10', '1 update data at 2020-07-04 10:25:10'),
(1807, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1808, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1809, 1, '2020-07-04 10:25:03', '1 update data at 2020-07-04 10:25:03'),
(1810, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1811, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1812, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1813, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1814, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1815, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1816, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1817, 1, '2020-07-04 09:04:38', '1 update data at 2020-07-04 09:04:38'),
(1818, 1, '2020-07-04 09:04:49', '1 update data at 2020-07-04 09:04:49'),
(1819, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1820, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1821, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1822, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1823, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1824, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1825, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1826, 1, '2020-07-04 09:06:01', '1 update data at 2020-07-04 09:06:01'),
(1827, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1828, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1829, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1830, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1831, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1832, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1833, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1834, 1, '2020-07-04 09:06:30', '1 update data at 2020-07-04 09:06:30'),
(1835, 1, '2020-07-04 09:06:34', '1 update data at 2020-07-04 09:06:34'),
(1836, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1837, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1838, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1839, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1840, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1841, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1842, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1843, 1, '2020-07-04 09:06:57', '1 update data at 2020-07-04 09:06:57'),
(1844, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1845, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1846, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1847, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1848, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1849, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1850, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1851, 1, '2020-07-04 09:08:08', '1 update data at 2020-07-04 09:08:08'),
(1852, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1853, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1854, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1855, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1856, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1857, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1858, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1859, 1, '2020-07-04 09:08:35', '1 update data at 2020-07-04 09:08:35'),
(1860, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1861, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1862, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1863, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1864, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1865, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1866, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1867, 1, '2020-07-04 09:08:53', '1 update data at 2020-07-04 09:08:53'),
(1868, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1869, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1870, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1871, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1872, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1873, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1874, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1875, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1876, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1877, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1878, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1879, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1880, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1881, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1882, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1883, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1884, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1885, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1886, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1887, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1888, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1889, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1890, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1891, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1892, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1893, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1894, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1895, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1896, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1897, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1898, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1899, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1900, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1901, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1902, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1903, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1904, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1905, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1906, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1907, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1908, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1909, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1910, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1911, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1912, 1, '2020-07-04 09:45:06', '1 update data at 2020-07-04 09:45:06'),
(1913, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1914, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1915, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1916, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1917, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1918, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1919, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1920, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1921, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1922, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1923, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1924, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1925, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1926, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1927, 1, '2020-07-04 09:45:53', '1 update data at 2020-07-04 09:45:53'),
(1928, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1929, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1930, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1931, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1932, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1933, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1934, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1935, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1936, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1937, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1938, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1939, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1940, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1941, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1942, 1, '2020-07-04 09:48:13', '1 update data at 2020-07-04 09:48:13'),
(1943, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1944, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1945, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1946, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1947, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1948, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1949, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1950, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1951, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1952, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1953, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1954, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1955, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1956, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1957, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1958, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1959, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1960, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1961, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1962, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1963, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1964, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1965, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1966, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1967, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1968, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1969, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1970, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1971, 1, '2020-07-04 09:52:12', '1 insert data at2020-07-04 09:52:12'),
(1972, 1, '2020-07-04 09:52:12', '1 insert data at2020-07-04 09:52:12'),
(1973, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(1974, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(1975, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(1976, 1, '2020-07-04 09:52:12', '1 insert data at2020-07-04 09:52:12'),
(1977, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1978, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(1979, 1, '2020-07-04 10:25:03', '1 update data at 2020-07-04 10:25:03'),
(1980, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1981, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1982, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1983, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1984, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1985, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1986, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1987, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1988, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1989, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1990, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1991, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1992, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(1993, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(1994, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(1995, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(1996, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(1997, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(1998, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(1999, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(2000, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(2001, 1, '2020-07-04 09:52:43', '1 update data at 2020-07-04 09:52:43'),
(2002, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(2003, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(2004, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(2005, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(2006, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(2007, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(2008, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(2009, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(2010, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(2011, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(2012, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(2013, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(2014, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(2015, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(2016, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(2017, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(2018, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(2019, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(2020, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(2021, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(2022, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(2023, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(2024, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(2025, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(2026, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(2027, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(2028, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(2029, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(2030, 1, '2020-07-04 09:53:02', '1 update data at 2020-07-04 09:53:02'),
(2031, 1, '2020-07-04 10:18:19', '1 update data at 2020-07-04 10:18:19'),
(2032, 1, '2020-07-04 10:25:16', '1 update data at 2020-07-04 10:25:16'),
(2033, 1, '2020-06-22 03:01:02', '1 update data at 2020-06-22 03:01:02'),
(2034, 1, '2020-06-22 05:26:20', '1 update data at 2020-06-22 05:26:20'),
(2035, 1, '2020-07-04 09:16:51', '1 update data at 2020-07-04 09:16:51'),
(2036, 1, '2020-07-04 10:25:21', '1 update data at 2020-07-04 10:25:21'),
(2037, 1, '2020-06-29 12:18:09', '1 update data at 2020-06-29 12:18:09'),
(2038, 1, '2020-07-04 09:54:07', '1 insert data at2020-07-04 09:54:07'),
(2039, 1, '2020-07-04 09:54:07', '1 insert data at2020-07-04 09:54:07'),
(2040, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(2041, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(2042, 1, '2020-07-01 12:28:58', '1 update data at 2020-07-01 12:28:58'),
(2043, 1, '2020-07-04 09:54:07', '1 insert data at2020-07-04 09:54:07'),
(2044, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(2045, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(2046, 1, '2020-07-04 10:25:03', '1 update data at 2020-07-04 10:25:03');

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

INSERT INTO `mstr_barang` (`id_pk_brg`, `brg_kode`, `brg_nama`, `brg_ket`, `brg_minimal`, `brg_satuan`, `brg_image`, `brg_harga`, `brg_status`, `brg_create_date`, `brg_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_brg_jenis`, `id_fk_brg_merk`) VALUES
(1, 'Barang 1', 'Barang 1', '-', 10, 'Pcs', '-', 10000, 'nonaktif', '2020-06-29 12:16:04', '2020-07-03 06:21:30', 1, 1, 1, 1),
(2, 'Barang 2', 'Barang 2', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:14', '2020-06-29 12:16:14', 1, 1, 2, 2),
(3, 'Barang 3', 'Barang 3', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:24', '2020-06-29 12:16:24', 1, 1, 3, 3),
(4, 'Barang 4', 'Barang 4', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:32', '2020-06-29 12:16:32', 1, 1, 4, 8),
(5, 'Barang 5', 'Barang 5', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:42', '2020-07-04 10:27:27', 1, 1, 5, 7),
(6, 'Kombinasi 1', 'Kombinasi 1', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:17:37', '2020-07-04 12:04:55', 1, 1, 7, 9),
(7, 'Kombinasi 2', 'Kombinasi 2', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1, 7, 9);

--
-- Triggers `mstr_barang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang` AFTER INSERT ON `mstr_barang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_log(executed_function,
            id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after insert',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang` AFTER UPDATE ON `mstr_barang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_log(executed_function,
            id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after update',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
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
-- Dumping data for table `mstr_barang_jenis`
--

INSERT INTO `mstr_barang_jenis` (`id_pk_brg_jenis`, `brg_jenis_nama`, `brg_jenis_status`, `brg_jenis_create_date`, `brg_jenis_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'JENIS 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1),
(2, 'JENIS 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1),
(3, 'JENIS 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1),
(4, 'Jenis 4', 'AKTIF', '2020-06-22 10:55:47', '2020-06-22 10:55:47', 1, 1),
(5, 'JENIS 5', 'AKTIF', '2020-06-22 05:14:55', '2020-06-22 05:14:55', 1, 1),
(6, 'JENIS6', 'AKTIF', '2020-06-22 09:17:46', '2020-06-22 09:17:46', 1, 1),
(7, 'Kombinasi', 'AKTIF', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1);

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

--
-- Dumping data for table `mstr_barang_jenis_log`
--

INSERT INTO `mstr_barang_jenis_log` (`id_pk_brg_jenis_log`, `executed_function`, `id_pk_brg_jenis`, `brg_jenis_nama`, `brg_jenis_status`, `brg_jenis_create_date`, `brg_jenis_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'JENIS 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 209),
(2, 'after insert', 2, 'JENIS 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 212),
(3, 'after insert', 3, 'JENIS 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 215),
(4, 'after insert', 4, 'Jenis 4', 'AKTIF', '2020-06-22 10:55:47', '2020-06-22 10:55:47', 1, 1, 363),
(5, 'after insert', 5, 'JENIS 5', 'AKTIF', '2020-06-22 05:14:55', '2020-06-22 05:14:55', 1, 1, 598),
(6, 'after insert', 6, 'JENIS6', 'AKTIF', '2020-06-22 09:17:46', '2020-06-22 09:17:46', 1, 1, 847),
(7, 'after insert', 7, 'Kombinasi', 'AKTIF', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1, 1236);

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
  `brg_status` varchar(15) DEFAULT NULL,
  `brg_create_date` datetime DEFAULT NULL,
  `brg_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_brg_jenis` int(11) DEFAULT NULL,
  `id_fk_brg_merk` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mstr_barang_log`
--

INSERT INTO `mstr_barang_log` (`id_pk_brg_log`, `executed_function`, `id_pk_brg`, `brg_kode`, `brg_nama`, `brg_ket`, `brg_minimal`, `brg_satuan`, `brg_image`, `brg_harga`, `brg_status`, `brg_create_date`, `brg_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_brg_jenis`, `id_fk_brg_merk`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Barang 1', 'Barang 1', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:04', '2020-06-29 12:16:04', 1, 1, 1, 1, 1230),
(2, 'after insert', 2, 'Barang 2', 'Barang 2', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:14', '2020-06-29 12:16:14', 1, 1, 2, 2, 1231),
(3, 'after insert', 3, 'Barang 3', 'Barang 3', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:24', '2020-06-29 12:16:24', 1, 1, 3, 3, 1232),
(4, 'after insert', 4, 'Barang 4', 'Barang 4', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:32', '2020-06-29 12:16:32', 1, 1, 4, 8, 1234),
(5, 'after insert', 5, 'Barang 5', 'Barang 5', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:42', '2020-06-29 12:16:42', 1, 1, 5, 7, 1235),
(6, 'after insert', 6, 'Kombinasi 1', 'Kombinasi 1', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1, 7, 9, 1238),
(7, 'after insert', 7, 'Kombinasi 2', 'Kombinasi 2', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1, 7, 9, 1241),
(8, 'after update', 1, 'Barang 1', 'Barang 1', '-', 10, 'Pcs', '-', 10000, 'nonaktif', '2020-06-29 12:16:04', '2020-07-03 06:21:30', 1, 1, 1, 1, 1705),
(9, 'after update', 5, 'Barang 5', 'Barang 5', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:42', '2020-07-04 10:27:00', 1, 1, 5, 7, 1773),
(10, 'after update', 5, 'Barang 5', 'Barang 5', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:42', '2020-07-04 10:27:10', 1, 1, 5, 7, 1776),
(11, 'after update', 5, 'Barang 5', 'Barang 5', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:42', '2020-07-04 10:27:19', 1, 1, 5, 7, 1779),
(12, 'after update', 5, 'Barang 5', 'Barang 5', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:16:42', '2020-07-04 10:27:27', 1, 1, 5, 7, 1782),
(13, 'after update', 6, 'Kombinasi 1', 'Kombinasi 1', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:17:37', '2020-07-04 10:27:59', 1, 1, 7, 9, 1786),
(14, 'after update', 6, 'Kombinasi 1', 'Kombinasi 1', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:17:37', '2020-07-04 10:28:29', 1, 1, 7, 9, 1788),
(15, 'after update', 6, 'Kombinasi 1', 'Kombinasi 1', '-', 10, 'Pcs', '-', 10000, 'AKTIF', '2020-06-29 12:17:37', '2020-07-04 12:04:55', 1, 1, 7, 9, 1795);

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
(1, 'MERK 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1),
(2, 'MERK 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1),
(3, 'MERK 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1),
(4, 'MERK 31', 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 11:09:15', 1, 1),
(5, 'MERK 12', 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 11:09:04', 1, 1),
(6, 'MERK 13', 'AKTIF', '2020-06-22 10:39:37', '2020-06-22 11:09:08', 1, 1),
(7, 'MERK 5', 'AKTIF', '2020-06-22 05:14:55', '2020-06-22 05:14:55', 1, 1),
(8, 'MERK 4', 'AKTIF', '2020-06-29 12:16:32', '2020-06-29 12:16:32', 1, 1),
(9, 'Merk Kombinasi', 'AKTIF', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1);

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

--
-- Dumping data for table `mstr_barang_merk_log`
--

INSERT INTO `mstr_barang_merk_log` (`id_pk_brg_merk_log`, `executed_function`, `id_pk_brg_merk`, `brg_merk_nama`, `brg_merk_status`, `brg_merk_create_date`, `brg_merk_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'MERK 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 210),
(2, 'after insert', 2, 'MERK 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 213),
(3, 'after insert', 3, 'MERK 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 216),
(4, 'after insert', 4, 'MERK 3', 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 10:37:14', 1, 1, 348),
(5, 'after insert', 5, 'MERK 1', 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 10:39:16', 1, 1, 350),
(6, 'after insert', 6, 'MERK 1', 'AKTIF', '2020-06-22 10:39:37', '2020-06-22 10:39:37', 1, 1, 354),
(7, 'after update', 5, 'MERK 12', 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 11:09:04', 1, 1, 392),
(8, 'after update', 6, 'MERK 13', 'AKTIF', '2020-06-22 10:39:37', '2020-06-22 11:09:08', 1, 1, 393),
(9, 'after update', 4, 'MERK 31', 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 11:09:15', 1, 1, 394),
(10, 'after insert', 7, 'MERK 5', 'AKTIF', '2020-06-22 05:14:55', '2020-06-22 05:14:55', 1, 1, 599),
(11, 'after insert', 8, 'MERK 4', 'AKTIF', '2020-06-29 12:16:32', '2020-06-29 12:16:32', 1, 1, 1233),
(12, 'after insert', 9, 'Merk Kombinasi', 'AKTIF', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1, 1237);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_cabang`
--

CREATE TABLE `mstr_cabang` (
  `id_pk_cabang` int(11) NOT NULL,
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

INSERT INTO `mstr_cabang` (`id_pk_cabang`, `cabang_daerah`, `cabang_kop_surat`, `cabang_nonpkp`, `cabang_pernyataan_rek`, `cabang_notelp`, `cabang_alamat`, `cabang_status`, `cabang_create_date`, `cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_toko`) VALUES
(1, 'Karawaci2', 'Pendaftaran_SYNC_STUDY.png', 'Pendaftaran_SYNC_STUDY.png', 'Pendaftaran_SYNC_STUDY.png', '12342', 'karawaci2', 'AKTIF', '2020-07-02 10:10:14', '2020-07-02 10:10:56', 1, 1, 1),
(2, 'Kota', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', '12345', 'Kota', 'AKTIF', '2020-07-03 07:14:26', '2020-07-03 07:14:26', 1, 1, 1);

--
-- Triggers `mstr_cabang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_cabang` AFTER INSERT ON `mstr_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_cabang_log(executed_function,id_pk_cabang,cabang_daerah,cabang_kop_surat,cabang_nonpkp,cabang_pernyataan_rek,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_create_data,id_last_modified,id_fk_toko,id_log_all) values ('after insert',new.id_pk_cabang,new.cabang_daerah,new.cabang_kop_surat,new.cabang_nonpkp,new.cabang_pernyataan_rek,new.cabang_notelp,new.cabang_alamat,new.cabang_status,new.cabang_create_date,new.cabang_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_toko,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_cabang` AFTER UPDATE ON `mstr_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_cabang_log(executed_function,id_pk_cabang,cabang_daerah,cabang_kop_surat,cabang_nonpkp,cabang_pernyataan_rek,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_create_data,id_last_modified,id_fk_toko,id_log_all) values ('after update',new.id_pk_cabang,new.cabang_daerah,new.cabang_kop_surat,new.cabang_nonpkp,new.cabang_pernyataan_rek,new.cabang_notelp,new.cabang_alamat,new.cabang_status,new.cabang_create_date,new.cabang_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_toko,@id_log_all);
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

--
-- Dumping data for table `mstr_cabang_log`
--

INSERT INTO `mstr_cabang_log` (`id_pk_cabang_log`, `executed_function`, `id_pk_cabang`, `cabang_daerah`, `cabang_kop_surat`, `cabang_nonpkp`, `cabang_pernyataan_rek`, `cabang_notelp`, `cabang_alamat`, `cabang_status`, `cabang_create_date`, `cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_toko`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Karawaci', 'IMG_5301.jpg', 'IMG_5301.jpg', 'IMG_5301.jpg', '1234', 'karawaci', 'AKTIF', '2020-07-02 10:10:14', '2020-07-02 10:10:14', 1, 1, 1, 1635),
(2, 'after update', 1, 'Karawaci2', 'Pendaftaran_SYNC_STUDY.png', 'Pendaftaran_SYNC_STUDY.png', 'Pendaftaran_SYNC_STUDY.png', '12342', 'karawaci2', 'AKTIF', '2020-07-02 10:10:14', '2020-07-02 10:10:56', 1, 1, 1, 1636),
(3, 'after insert', 2, 'Kota', 'noimage.jpg', 'noimage.jpg', 'noimage.jpg', '12345', 'Kota', 'AKTIF', '2020-07-03 07:14:26', '2020-07-03 07:14:26', 1, 1, 1, 1724);

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
(1, 'Joshua Natan2', '-2', 'Pendaftaran_SYNC_STUDY.png', 'Dating.png', 'CV', '-2', 'MR', 'ISupport2', 'joshuanatan.jn@gmail.com2', '1234562', '1234562', '-2', '-2', NULL, 'AKTIF', '2020-07-02 05:36:41', '2020-07-02 07:03:58', 1, 1),
(2, 'Joshua Natan', '-', 'academic_transcript11.jpg', 'academic_transcript21.jpg', 'Toko', '-', 'Tn', 'ISupport', 'joshuanatan.jn@gmail.com', '123456', '123456', '-', '-', NULL, 'AKTIF', '2020-07-02 05:39:25', '2020-07-02 05:39:25', 1, 1),
(3, 'Joshua Natan', '-', 'academic_transcript12.jpg', 'academic_transcript22.jpg', 'Toko', '-', 'Tn', 'ISupport', 'joshuanatan.jn@gmail.com', '123456', '123456', '-', '-', NULL, 'AKTIF', '2020-07-02 05:40:03', '2020-07-02 05:40:03', 1, 1),
(4, 'Joshua Natan', '-', 'academic_transcript13.jpg', 'academic_transcript2.jpg', 'Toko', '-', 'Tn', 'ISupport', 'joshuanatan.jn@gmail.com', '123456', '123456', '-', '-', NULL, 'AKTIF', '2020-07-02 05:40:45', '2020-07-02 05:40:45', 1, 1),
(5, 'asdf', '1234', '-', '-', 'Toko', '-', 'Tn', 'asdf', 'asdf@email.com', '1234', '1234', '-', 'asdf', NULL, 'AKTIF', '2020-07-02 07:30:05', '2020-07-02 07:30:05', 1, 1),
(6, 'a', '123', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'Tn', 'a', 'a@email.com', '1', '1', '-', 'a', NULL, 'AKTIF', '2020-07-02 07:37:39', '2020-07-02 07:37:39', 1, 1);

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

--
-- Dumping data for table `mstr_customer_log`
--

INSERT INTO `mstr_customer_log` (`id_pk_cust_log`, `executed_function`, `id_pk_cust`, `cust_name`, `cust_no_npwp`, `cust_foto_npwp`, `cust_foto_kartu_nama`, `cust_badan_usaha`, `cust_no_rekening`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Joshua Natan', '-', 'academic_transcript1.jpg', 'academic_transcript2.jpg', 'Toko', '-', 'Tn', 'ISupport', 'joshuanatan.jn@gmail.com', '123456', '123456', '-', '-', NULL, 'AKTIF', '2020-07-02 05:36:41', '2020-07-02 05:36:41', 1, 1, 1619),
(2, 'after insert', 2, 'Joshua Natan', '-', 'academic_transcript11.jpg', 'academic_transcript21.jpg', 'Toko', '-', 'Tn', 'ISupport', 'joshuanatan.jn@gmail.com', '123456', '123456', '-', '-', NULL, 'AKTIF', '2020-07-02 05:39:25', '2020-07-02 05:39:25', 1, 1, 1620),
(3, 'after insert', 3, 'Joshua Natan', '-', 'academic_transcript12.jpg', 'academic_transcript22.jpg', 'Toko', '-', 'Tn', 'ISupport', 'joshuanatan.jn@gmail.com', '123456', '123456', '-', '-', NULL, 'AKTIF', '2020-07-02 05:40:03', '2020-07-02 05:40:03', 1, 1, 1621),
(4, 'after insert', 4, 'Joshua Natan', '-', 'academic_transcript13.jpg', 'academic_transcript2.jpg', 'Toko', '-', 'Tn', 'ISupport', 'joshuanatan.jn@gmail.com', '123456', '123456', '-', '-', NULL, 'AKTIF', '2020-07-02 05:40:45', '2020-07-02 05:40:45', 1, 1, 1622),
(5, 'after update', 1, 'Joshua Natan2', '-2', 'academic_transcript1.jpg', 'academic_transcript2.jpg', 'CV', '-2', 'MR', 'ISupport2', 'joshuanatan.jn@gmail.com2', '1234562', '1234562', '-2', '-2', NULL, 'AKTIF', '2020-07-02 05:36:41', '2020-07-02 07:03:22', 1, 1, 1623),
(6, 'after update', 1, 'Joshua Natan2', '-2', 'Pendaftaran_SYNC_STUDY.png', 'Dating.png', 'CV', '-2', 'MR', 'ISupport2', 'joshuanatan.jn@gmail.com2', '1234562', '1234562', '-2', '-2', NULL, 'AKTIF', '2020-07-02 05:36:41', '2020-07-02 07:03:58', 1, 1, 1624),
(7, 'after insert', 5, 'asdf', '1234', '-', '-', 'Toko', '-', 'Tn', 'asdf', 'asdf@email.com', '1234', '1234', '-', 'asdf', NULL, 'AKTIF', '2020-07-02 07:30:05', '2020-07-02 07:30:05', 1, 1, 1625),
(8, 'after insert', 6, 'a', '123', 'noimage.jpg', 'noimage.jpg', 'Toko', '-', 'Tn', 'a', 'a@email.com', '1', '1', '-', 'a', NULL, 'AKTIF', '2020-07-02 07:37:39', '2020-07-02 07:37:39', 1, 1, 1626);

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
  `id_fk_toko` int(11) DEFAULT NULL,
  `emp_create_date` datetime DEFAULT NULL,
  `emp_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_employee`
--

INSERT INTO `mstr_employee` (`id_pk_employee`, `emp_nama`, `emp_npwp`, `emp_ktp`, `emp_hp`, `emp_alamat`, `emp_kode_pos`, `emp_foto_npwp`, `emp_foto_ktp`, `emp_foto_lain`, `emp_foto`, `emp_gaji`, `emp_startdate`, `emp_enddate`, `emp_rek`, `emp_gender`, `emp_suff`, `emp_status`, `id_fk_toko`, `emp_create_date`, `emp_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Budi Pegawai', '98765432345678', '45678987654', '0865456754', 'jalan kamboja no 3 jakarta selatan', '34567', 'Screenshot_261.png', 'Screenshot_631.png', 'Screenshot_661.png', 'Screenshot_281.png', 6000000, '2019-08-01 00:00:00', '0000-00-00 00:00:00', '3456788', 'PRIA', 'MR', 'AKTIF', 1, '2020-06-22 10:45:26', '2020-06-22 10:45:26', 1, 1),
(2, 'Yane Pegawai', '567890', '876545678', '08765438888', 'jalan haha no 23 tangerang selatan', '87657', 'Screenshot_22.png', 'Screenshot_15.png', 'Screenshot_244.png', 'Screenshot_47.png', 7000000, '2020-01-01 00:00:00', '2020-06-15 00:00:00', '345678', 'WANITA', 'MRS', 'AKTIF', 1, '2020-06-22 10:55:19', '2020-06-22 10:55:19', 1, 1);

--
-- Triggers `mstr_employee`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_employee` AFTER INSERT ON `mstr_employee` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,id_fk_toko,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.id_fk_toko,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_employee` AFTER UPDATE ON `mstr_employee` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,id_fk_toko,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.id_fk_toko,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
  `id_fk_toko` int(11) DEFAULT NULL,
  `emp_create_date` datetime DEFAULT NULL,
  `emp_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_employee_log`
--

INSERT INTO `mstr_employee_log` (`id_pk_employee_log`, `executed_function`, `id_pk_employee`, `emp_nama`, `emp_npwp`, `emp_ktp`, `emp_hp`, `emp_alamat`, `emp_kode_pos`, `emp_foto_npwp`, `emp_foto_ktp`, `emp_foto_lain`, `emp_foto`, `emp_gaji`, `emp_startdate`, `emp_enddate`, `emp_rek`, `emp_gender`, `emp_suff`, `emp_status`, `id_fk_toko`, `emp_create_date`, `emp_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Budi Pegawai', '98765432345678', '45678987654', '0865456754', 'jalan kamboja no 3 jakarta selatan', '34567', 'Screenshot_261.png', 'Screenshot_631.png', 'Screenshot_661.png', 'Screenshot_281.png', 6000000, '2019-08-01 00:00:00', '0000-00-00 00:00:00', '3456788', 'PRIA', 'MR', 'AKTIF', 0, '2020-06-22 10:45:26', '2020-06-22 10:45:26', 1, 1, 360),
(2, 'after update', 1, 'Budi Pegawai', '98765432345678', '45678987654', '0865456754', 'jalan kamboja no 3 jakarta selatan', '34567', 'Screenshot_261.png', 'Screenshot_631.png', 'Screenshot_661.png', 'Screenshot_281.png', 6000000, '2019-08-01 00:00:00', '0000-00-00 00:00:00', '3456788', 'PRIA', 'MR', 'AKTIF', 1, '2020-06-22 10:45:26', '2020-06-22 10:45:26', 1, 1, 361),
(3, 'after insert', 2, 'Yane Pegawai', '567890', '876545678', '08765438888', 'jalan haha no 23 tangerang selatan', '87657', 'Screenshot_22.png', 'Screenshot_15.png', 'Screenshot_244.png', 'Screenshot_47.png', 7000000, '2020-01-01 00:00:00', '2020-06-15 00:00:00', '345678', 'WANITA', 'MRS', 'AKTIF', 1, '2020-06-22 10:55:19', '2020-06-22 10:55:19', 1, 1, 362);

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
(1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-07-02 11:03:48', 1, 1),
(2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 05:24:03', 1, 1),
(3, 'admin3', 'AKTIF', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(4, 'admin4', 'AKTIF', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1);

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

--
-- Dumping data for table `mstr_jabatan_log`
--

INSERT INTO `mstr_jabatan_log` (`id_pk_jabatan_log`, `executed_function`, `id_pk_jabatan`, `jabatan_nama`, `jabatan_status`, `jabatan_create_date`, `jabatan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 5),
(2, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-21 11:29:04', 1, 1, 8),
(3, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-21 11:42:53', 1, 1, 48),
(4, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-22 07:50:39', 1, 1, 98),
(5, 'after insert', 2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 140),
(6, 'after update', 2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 07:51:42', 1, 1, 162),
(7, 'after insert', 3, 'admin3', 'AKTIF', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 163),
(8, 'after insert', 4, 'admin4', 'AKTIF', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 185),
(9, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-22 12:33:02', 1, 1, 448),
(10, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-22 02:55:01', 1, 1, 512),
(11, 'after update', 2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 05:09:47', 1, 1, 571),
(12, 'after update', 2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 05:24:03', 1, 1, 613),
(13, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-22 06:10:43', 1, 1, 771),
(14, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-26 10:14:31', 1, 1, 1108),
(15, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-27 07:49:21', 1, 1, 1160),
(16, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-30 09:26:57', 1, 1, 1420),
(17, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-07-02 11:03:48', 1, 1, 1642);

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
(2, 'Shopee', '-', 40, 'AKTIF', '2020-06-27 11:07:26', '2020-06-27 11:07:26', 1, 1);

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

--
-- Dumping data for table `mstr_marketplace_log`
--

INSERT INTO `mstr_marketplace_log` (`id_pk_marketplace_log`, `executed_function`, `id_pk_marketplace`, `marketplace_nama`, `marketplace_ket`, `marketplace_biaya`, `marketplace_status`, `marketplace_create_date`, `marketplace_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Tokopedia', '-', 40, 'AKTIF', '2020-06-27 09:02:02', '2020-06-27 09:02:02', 1, 1, 1209),
(2, 'after update', 1, 'Tokopedia2', '-', 40, 'AKTIF', '2020-06-27 09:02:02', '2020-06-27 09:19:09', 1, 1, 1210),
(3, 'after update', 1, 'Tokopedia', '-', 50, 'AKTIF', '2020-06-27 09:02:02', '2020-06-27 09:19:15', 1, 1, 1211),
(4, 'after update', 1, 'Tokopedia', '-', 50, 'nonaktif', '2020-06-27 09:02:02', '2020-06-27 09:20:39', 1, 1, 1212),
(5, 'after update', 1, 'Tokopedia', '-', 50, 'aktif', '2020-06-27 09:02:02', '2020-06-27 09:20:39', 1, 1, 1213),
(6, 'after insert', 2, 'Shopee', '-', 40, 'AKTIF', '2020-06-27 11:07:26', '2020-06-27 11:07:26', 1, 1, 1219);

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
(1, 'menu', 'Menu', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:27:06', '2020-07-02 10:03:00', 1, 1),
(2, 'roles', 'Role', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-07-02 10:04:30', 1, 1),
(3, 'barang', 'Barang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:11', '2020-07-02 10:02:33', 1, 1),
(4, 'barang_jenis', 'Jenis Barang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:23', '2020-07-02 10:02:50', 1, 1),
(5, 'barang_merk', 'Merk Barang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-07-02 10:03:24', 1, 1),
(6, 'customer', 'Customer', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:44', '2020-07-02 10:02:39', 1, 1),
(7, 'employee', 'Employee', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:54', '2020-07-02 10:02:45', 1, 1),
(8, 'pembelian', 'Pembelian Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:39:36', '2020-07-02 10:03:30', 1, 1),
(9, 'penerimaan/cabang', 'Penerimaan Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:07', '2020-07-02 10:03:35', 1, 1),
(10, 'pengiriman/cabang', 'Pengiriman Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:52', '2020-07-02 10:03:58', 1, 1),
(11, 'pengiriman/warehouse', 'Pengiriman Gudang', 'edit', 'GUDANG', 'AKTIF', '2020-06-21 11:41:04', '2020-07-02 10:04:04', 1, 1),
(12, 'penjualan', 'Penjualan Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:23', '2020-07-02 10:04:16', 1, 1),
(13, 'permintaan', 'Permintaan Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:33', '2020-07-02 10:04:21', 1, 1),
(14, 'retur', 'Retur Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:42', '2020-07-02 10:04:26', 1, 1),
(15, 'satuan', 'Satuan', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:41:58', '2020-07-02 10:04:35', 1, 1),
(16, 'supplier', 'Supplier', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:07', '2020-07-02 10:04:45', 1, 1),
(17, 'toko', 'Toko', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:16', '2020-07-02 10:04:49', 1, 1),
(18, 'user', 'User', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:28', '2020-07-02 10:04:53', 1, 1),
(19, 'warehouse', 'Warehouse', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:37', '2020-07-02 10:04:57', 1, 1),
(20, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 02:52:25', 1, 1),
(21, 'toko/brg_cabang', 'Stok Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-22 07:50:23', '2020-07-02 10:04:39', 1, 1),
(22, 'pemenuhan/cabang', 'PEMENUHAN CABANG', 'edit', 'CABANG', 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 02:52:08', 1, 1),
(23, 'penerimaan/gudang', 'Penerimaan Gudang', 'edit', 'GUDANG', 'AKTIF', '2020-06-22 06:10:33', '2020-07-02 10:03:42', 1, 1),
(24, 'pengiriman/permintaan', 'Pengiriman Permintaan', 'edit', 'CABANG', 'AKTIF', '2020-06-26 10:07:22', '2020-07-02 10:04:11', 1, 1),
(25, 'marketplace', 'Marketplace', 'edit', 'GENERAL', 'AKTIF', '2020-06-27 07:36:59', '2020-07-02 10:02:56', 1, 1),
(26, 'penerimaan/permintaan', 'Penerimaan Permintaan', 'edit', 'CABANG', 'AKTIF', '2020-06-30 09:26:26', '2020-07-02 10:03:50', 1, 1),
(27, 'retur/konfirmasi', 'Konfirmasi Retur', 'edit', 'CABANG', 'AKTIF', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1);

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

--
-- Dumping data for table `mstr_menu_log`
--

INSERT INTO `mstr_menu_log` (`id_pk_menu_log`, `executed_function`, `id_pk_menu`, `menu_name`, `menu_display`, `menu_icon`, `menu_category`, `menu_status`, `menu_create_date`, `menu_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'menu', 'MENU', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:27:06', '2020-06-21 11:27:06', 1, 1, 3),
(2, 'after insert', 2, 'role', 'ROLE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-06-21 11:28:42', 1, 1, 4),
(3, 'after insert', 3, 'barang', 'BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 13),
(4, 'after insert', 4, 'barang_jenis', 'JENIS BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 15),
(5, 'after insert', 5, 'barang_merk', 'MERK BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 17),
(6, 'after insert', 6, 'customer', 'CUSTOMER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 19),
(7, 'after insert', 7, 'employee', 'EMPLOYEE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 21),
(8, 'after insert', 8, 'pembelian', 'PEMBELIAN CABANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 23),
(9, 'after insert', 9, 'penerimaan/cabang', 'PENERIMAAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 25),
(10, 'after update', 8, 'pembelian', 'PEMBELIAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:39:36', '2020-06-21 11:40:15', 1, 1, 27),
(11, 'after insert', 10, 'pengiriman/cabang', 'PENGIRIMAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 28),
(12, 'after insert', 11, 'pengiriman/warehouse', 'PENGIRIMAN GUDANG', 'edit', 'GUDANG', 'AKTIF', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 30),
(13, 'after insert', 12, 'penjualan', 'PENJUALAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 32),
(14, 'after insert', 13, 'permintaan', 'PERMINTAAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 34),
(15, 'after insert', 14, 'retur', 'RETUR CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 36),
(16, 'after insert', 15, 'satuan', 'SATUAN', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 38),
(17, 'after insert', 16, 'supplier', 'SUPPLIER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 40),
(18, 'after insert', 17, 'toko', 'TOKO', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 42),
(19, 'after insert', 18, 'user', 'USER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 44),
(20, 'after insert', 19, 'warehouse', 'WAREHOUSE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 46),
(21, 'after update', 2, 'roles', 'ROLE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-06-21 11:43:08', 1, 1, 87),
(22, 'after insert', 20, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 94),
(23, 'after insert', 21, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 96),
(24, 'after insert', 22, 'pemenuhan', 'PEMENUHAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 443),
(25, 'after update', 22, 'pemenuhan/cabang', 'PEMENUHAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 12:32:52', '2020-06-22 12:33:51', 1, 1, 492),
(26, 'after update', 22, 'pemenuhan/cabang', 'PEMENUHAN CABANG', 'edit', 'CABANG', 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 02:52:08', 1, 1, 510),
(27, 'after update', 20, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 02:52:25', 1, 1, 511),
(28, 'after insert', 23, 'penerimaan/gudang', 'PENERIMAAN GUDANG', 'edit', 'GUDANG', 'AKTIF', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 766),
(29, 'after insert', 24, 'pengiriman/permintaan', 'PENGIRIMAN PERMINTAAN', 'edit', 'CABANG', 'AKTIF', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1103),
(30, 'after insert', 25, 'marketplace', 'MARKETPLACE', 'edit', 'GENERAL', 'AKTIF', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1155),
(31, 'after insert', 26, 'penerimaan/permintaan', 'PENERIMAAN PERMINTAAN', 'edit', 'TOKO', 'AKTIF', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1415),
(32, 'after update', 26, 'penerimaan/permintaan', 'PENERIMAAN PERMINTAAN', 'edit', 'CABANG', 'AKTIF', '2020-06-30 09:26:26', '2020-06-30 09:31:31', 1, 1, 1471),
(33, 'after update', 3, 'barang', 'Barang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:11', '2020-07-02 10:02:33', 1, 1, 1593),
(34, 'after update', 6, 'customer', 'Customer', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:44', '2020-07-02 10:02:39', 1, 1, 1594),
(35, 'after update', 7, 'employee', 'Employee', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:54', '2020-07-02 10:02:45', 1, 1, 1595),
(36, 'after update', 4, 'barang_jenis', 'Jenis Barang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:23', '2020-07-02 10:02:50', 1, 1, 1596),
(37, 'after update', 25, 'marketplace', 'Marketplace', 'edit', 'GENERAL', 'AKTIF', '2020-06-27 07:36:59', '2020-07-02 10:02:56', 1, 1, 1597),
(38, 'after update', 1, 'menu', 'Menu', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:27:06', '2020-07-02 10:03:00', 1, 1, 1598),
(39, 'after update', 5, 'barang_merk', 'Merk Barang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-07-02 10:03:08', 1, 1, 1599),
(40, 'after update', 5, 'barang_merk', 'Pembelian Cabang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-07-02 10:03:13', 1, 1, 1600),
(41, 'after update', 5, 'barang_merk', 'Merk Barang', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-07-02 10:03:24', 1, 1, 1601),
(42, 'after update', 8, 'pembelian', 'Pembelian Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:39:36', '2020-07-02 10:03:30', 1, 1, 1602),
(43, 'after update', 9, 'penerimaan/cabang', 'Penerimaan Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:07', '2020-07-02 10:03:35', 1, 1, 1603),
(44, 'after update', 23, 'penerimaan/gudang', 'Penerimaan Gudang', 'edit', 'GUDANG', 'AKTIF', '2020-06-22 06:10:33', '2020-07-02 10:03:42', 1, 1, 1604),
(45, 'after update', 26, 'penerimaan/permintaan', 'Penerimaan Permintaan', 'edit', 'CABANG', 'AKTIF', '2020-06-30 09:26:26', '2020-07-02 10:03:50', 1, 1, 1605),
(46, 'after update', 10, 'pengiriman/cabang', 'Pengiriman Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:52', '2020-07-02 10:03:58', 1, 1, 1606),
(47, 'after update', 11, 'pengiriman/warehouse', 'Pengiriman Gudang', 'edit', 'GUDANG', 'AKTIF', '2020-06-21 11:41:04', '2020-07-02 10:04:04', 1, 1, 1607),
(48, 'after update', 24, 'pengiriman/permintaan', 'Pengiriman Permintaan', 'edit', 'CABANG', 'AKTIF', '2020-06-26 10:07:22', '2020-07-02 10:04:11', 1, 1, 1608),
(49, 'after update', 12, 'penjualan', 'Penjualan Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:23', '2020-07-02 10:04:16', 1, 1, 1609),
(50, 'after update', 13, 'permintaan', 'Permintaan Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:33', '2020-07-02 10:04:21', 1, 1, 1610),
(51, 'after update', 14, 'retur', 'Retur Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:42', '2020-07-02 10:04:26', 1, 1, 1611),
(52, 'after update', 2, 'roles', 'Role', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-07-02 10:04:30', 1, 1, 1612),
(53, 'after update', 15, 'satuan', 'Satuan', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:41:58', '2020-07-02 10:04:35', 1, 1, 1613),
(54, 'after update', 21, 'toko/brg_cabang', 'Stok Cabang', 'edit', 'CABANG', 'AKTIF', '2020-06-22 07:50:23', '2020-07-02 10:04:39', 1, 1, 1614),
(55, 'after update', 16, 'supplier', 'Supplier', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:07', '2020-07-02 10:04:45', 1, 1, 1615),
(56, 'after update', 17, 'toko', 'Toko', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:16', '2020-07-02 10:04:49', 1, 1, 1616),
(57, 'after update', 18, 'user', 'User', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:28', '2020-07-02 10:04:53', 1, 1, 1617),
(58, 'after update', 19, 'warehouse', 'Warehouse', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:37', '2020-07-02 10:04:57', 1, 1, 1618),
(59, 'after insert', 27, 'retur/konfirmasi', 'Konfirmasi Retur', 'edit', 'CABANG', 'AKTIF', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1, 1637);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_pembelian`
--

CREATE TABLE `mstr_pembelian` (
  `id_pk_pembelian` int(11) NOT NULL,
  `pem_pk_nomor` varchar(30) DEFAULT NULL,
  `pem_tgl` date DEFAULT NULL,
  `pem_status` varchar(15) DEFAULT NULL,
  `id_fk_supp` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pem_create_date` datetime DEFAULT NULL,
  `pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_pembelian`
--

INSERT INTO `mstr_pembelian` (`id_pk_pembelian`, `pem_pk_nomor`, `pem_tgl`, `pem_status`, `id_fk_supp`, `id_fk_cabang`, `pem_create_date`, `pem_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'nomorpembelian1', '1111-11-11', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1),
(3, 'nomorpembelian1', '2222-02-22', 'AKTIF', 3, 2, '2020-06-22 05:28:46', '2020-06-22 05:28:46', 1, 1);

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
  `pem_pk_nomor` varchar(30) DEFAULT NULL,
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

--
-- Dumping data for table `mstr_pembelian_log`
--

INSERT INTO `mstr_pembelian_log` (`id_pk_pembelian_log`, `executed_function`, `id_pk_pembelian`, `pem_pk_nomor`, `pem_tgl`, `pem_status`, `id_fk_supp`, `id_fk_cabang`, `pem_create_date`, `pem_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'nomorpembelian1', '1111-11-11', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 236),
(2, 'after update', 1, 'nomorpembelian1', '1111-11-11', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1, 239),
(3, 'after insert', 2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 244),
(4, 'after update', 2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:26:54', 1, 1, 254),
(5, 'after update', 2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1, 258),
(6, 'after insert', 3, 'nomorpembelian1', '2222-02-22', 'AKTIF', 3, 2, '2020-06-22 05:28:46', '2020-06-22 05:28:46', 1, 1, 640);

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
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mstr_penerimaan`
--

INSERT INTO `mstr_penerimaan` (`id_pk_penerimaan`, `penerimaan_tgl`, `penerimaan_status`, `penerimaan_tipe`, `id_fk_pembelian`, `id_fk_retur`, `penerimaan_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `penerimaan_create_date`, `penerimaan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(9, '2020-07-01 12:17:51', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:17:51', '2020-07-01 12:28:21', 1, 1),
(10, '2020-07-01 12:18:24', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:18:24', '2020-07-01 12:28:17', 1, 1),
(11, '2020-07-01 12:29:02', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:02', '2020-07-01 12:29:09', 1, 1),
(12, '2020-07-01 12:29:20', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:20', '2020-07-01 12:30:28', 1, 1),
(13, '2020-07-01 12:29:22', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:22', '2020-07-01 12:29:30', 1, 1),
(14, '2020-07-01 12:31:57', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:31:57', '2020-07-01 12:32:03', 1, 1),
(15, '2020-07-01 12:33:04', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:33:04', '2020-07-01 12:33:11', 1, 1),
(16, '1111-11-11 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:21', 1, 1),
(17, '2020-07-18 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-04 09:52:12', '2020-07-04 09:52:12', 1, 1),
(18, '2020-07-03 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1);

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

--
-- Dumping data for table `mstr_penerimaan_log`
--

INSERT INTO `mstr_penerimaan_log` (`id_pk_penerimaan_log`, `executed_function`, `id_pk_penerimaan`, `penerimaan_tgl`, `penerimaan_status`, `penerimaan_tipe`, `id_fk_pembelian`, `id_fk_retur`, `penerimaan_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `penerimaan_create_date`, `penerimaan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, '2020-07-01 12:08:45', 'aktif', NULL, 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:08:45', '2020-07-01 12:08:45', 1, 1, 1472),
(2, 'after insert', 2, '2020-07-01 12:10:36', 'aktif', NULL, 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:10:36', '2020-07-01 12:10:36', 1, 1, 1474),
(3, 'after insert', 3, '2020-07-01 12:11:01', 'aktif', NULL, 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:11:01', '2020-07-01 12:11:01', 1, 1, 1476),
(4, 'after insert', 4, '2020-07-01 12:11:27', 'aktif', NULL, 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:11:27', '2020-07-01 12:11:27', 1, 1, 1478),
(5, 'after insert', 5, '2020-07-01 12:12:32', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:12:32', '2020-07-01 12:12:32', 1, 1, 1484),
(6, 'after insert', 6, '2020-07-01 12:13:14', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:13:14', '2020-07-01 12:13:14', 1, 1, 1490),
(7, 'after insert', 7, '2020-07-01 12:13:38', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:13:38', '2020-07-01 12:13:38', 1, 1, 1496),
(8, 'after insert', 8, '2020-07-01 12:14:19', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:14:19', '2020-07-01 12:14:19', 1, 1, 1502),
(9, 'after insert', 9, '2020-07-01 12:17:51', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:17:51', '2020-07-01 12:17:51', 1, 1, 1508),
(10, 'after insert', 10, '2020-07-01 12:18:24', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:18:24', '2020-07-01 12:18:24', 1, 1, 1514),
(11, 'after update', 10, '2020-07-01 12:18:24', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:18:24', '2020-07-01 12:28:17', 1, 1, 1520),
(12, 'after update', 9, '2020-07-01 12:17:51', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:17:51', '2020-07-01 12:28:21', 1, 1, 1523),
(13, 'after insert', 11, '2020-07-01 12:29:02', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:02', '2020-07-01 12:29:02', 1, 1, 1527),
(14, 'after update', 11, '2020-07-01 12:29:02', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:02', '2020-07-01 12:29:09', 1, 1, 1533),
(15, 'after insert', 12, '2020-07-01 12:29:20', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:20', '2020-07-01 12:29:20', 1, 1, 1536),
(16, 'after insert', 13, '2020-07-01 12:29:22', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:22', '2020-07-01 12:29:22', 1, 1, 1542),
(17, 'after update', 13, '2020-07-01 12:29:22', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:22', '2020-07-01 12:29:30', 1, 1, 1548),
(18, 'after update', 12, '2020-07-01 12:29:20', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:29:20', '2020-07-01 12:30:28', 1, 1, 1551),
(19, 'after insert', 14, '2020-07-01 12:31:57', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:31:57', '2020-07-01 12:31:57', 1, 1, 1554),
(20, 'after update', 14, '2020-07-01 12:31:57', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:31:57', '2020-07-01 12:32:03', 1, 1, 1560),
(21, 'after insert', 15, '2020-07-01 12:33:04', 'aktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:33:04', '2020-07-01 12:33:04', 1, 1, 1563),
(22, 'after update', 15, '2020-07-01 12:33:04', 'nonaktif', 'permintaan', 0, 0, 'CABANG', NULL, 1, '2020-07-01 12:33:04', '2020-07-01 12:33:11', 1, 1, 1569),
(23, 'after insert', 16, '1111-11-11 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:05', 1, 1, 1575),
(24, 'after update', 16, '1111-11-11 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:21', 1, 1, 1584),
(25, 'after insert', 17, '2020-07-18 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-04 09:52:12', '2020-07-04 09:52:12', 1, 1, 1971),
(26, 'after insert', 18, '2020-07-03 00:00:00', 'AKTIF', 'pembelian', 1, 0, 'CABANG', NULL, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1, 2038);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_pengiriman`
--

CREATE TABLE `mstr_pengiriman` (
  `id_pk_pengiriman` int(11) NOT NULL,
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
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mstr_pengiriman`
--

INSERT INTO `mstr_pengiriman` (`id_pk_pengiriman`, `pengiriman_tgl`, `pengiriman_status`, `pengiriman_tipe`, `id_fk_penjualan`, `id_fk_retur`, `pengiriman_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `pengiriman_create_date`, `pengiriman_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(14, '2020-06-29 17:02:24', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:24', '2020-06-29 05:26:28', 1, 1),
(15, '2020-06-29 17:44:13', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:13', '2020-06-29 05:44:13', 1, 1),
(16, '2020-06-29 17:44:27', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:27', '2020-06-29 05:44:27', 1, 1),
(17, '2020-06-29 17:44:49', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:49', '2020-06-29 05:44:49', 1, 1),
(18, '2020-06-29 17:44:54', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:54', '2020-06-29 05:44:54', 1, 1),
(19, '2020-06-29 17:50:13', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:50:13', '2020-06-29 05:50:13', 1, 1),
(20, '2020-06-29 17:52:47', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:52:47', '2020-06-29 05:52:47', 1, 1),
(21, '2020-06-29 17:53:44', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:53:44', '2020-06-29 05:53:44', 1, 1),
(22, '2020-06-29 17:54:11', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:54:11', '2020-06-29 05:54:56', 1, 1),
(23, '2020-06-29 17:55:39', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:39', '2020-06-29 05:55:39', 1, 1),
(24, '2020-06-29 17:55:43', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:43', '2020-06-29 05:55:43', 1, 1),
(25, '2020-06-29 17:55:46', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:46', '2020-06-29 05:55:59', 1, 1),
(26, '2020-06-29 17:55:49', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:49', '2020-06-29 05:56:03', 1, 1),
(27, '2020-06-29 17:59:17', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:59:17', '2020-06-29 05:59:17', 1, 1),
(28, '2020-06-29 18:02:14', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:02:14', '2020-06-29 06:04:32', 1, 1),
(29, '2020-06-29 18:04:37', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:04:37', '2020-06-29 06:07:05', 1, 1),
(30, '2020-06-29 18:07:08', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:07:08', '2020-06-29 06:07:08', 1, 1),
(31, '2020-06-29 18:09:05', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:09:05', '2020-06-29 06:09:05', 1, 1),
(32, '2020-06-29 18:09:34', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:09:34', '2020-06-29 06:09:34', 1, 1),
(33, '2020-06-29 18:10:03', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:10:03', '2020-06-29 06:10:03', 1, 1),
(34, '2020-06-29 18:10:26', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:10:26', '2020-06-29 06:18:59', 1, 1),
(35, '2020-06-29 18:10:43', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:10:43', '2020-06-29 06:10:43', 1, 1),
(36, '2020-06-29 18:11:11', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:11', '2020-06-29 06:11:11', 1, 1),
(37, '2020-06-29 18:11:38', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:38', '2020-06-29 06:11:38', 1, 1),
(38, '2020-06-29 18:11:47', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:47', '2020-06-29 06:11:47', 1, 1),
(39, '2020-06-29 18:11:58', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:58', '2020-06-29 06:11:58', 1, 1),
(40, '2020-06-29 18:13:19', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:13:19', '2020-06-29 06:13:19', 1, 1),
(41, '2020-06-29 18:18:49', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:18:49', '2020-06-29 06:26:23', 1, 1),
(42, '2020-06-29 18:26:31', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:26:31', '2020-06-29 06:26:37', 1, 1),
(43, '1111-11-11 00:00:00', 'nonaktif', 'penjualan', 2, 0, 'cabang', NULL, 2, '2020-06-29 06:27:12', '2020-06-29 06:27:46', 1, 1);

--
-- Triggers `mstr_pengiriman`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pengiriman` AFTER INSERT ON `mstr_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pengiriman_log(executed_function,id_pk_pengiriman,pengiriman_tgl,pengiriman_status,pengiriman_tipe,id_fk_penjualan,id_fk_retur,pengiriman_tempat,id_fk_warehouse,id_fk_cabang,pengiriman_create_date,pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_pengiriman,new.pengiriman_tgl,new.pengiriman_status,new.pengiriman_tipe,new.id_fk_penjualan,new.id_fk_retur,new.pengiriman_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.pengiriman_create_date,new.pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_pengiriman` AFTER UPDATE ON `mstr_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pengiriman_log(executed_function,id_pk_pengiriman,pengiriman_tgl,pengiriman_status,pengiriman_tipe,id_fk_penjualan,id_fk_retur,pengiriman_tempat,id_fk_warehouse,id_fk_cabang,pengiriman_create_date,pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_pengiriman,new.pengiriman_tgl,new.pengiriman_status,new.pengiriman_tipe,new.id_fk_penjualan,new.id_fk_retur,new.pengiriman_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.pengiriman_create_date,new.pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mstr_pengiriman_log`
--

INSERT INTO `mstr_pengiriman_log` (`id_pk_pengiriman_log`, `executed_function`, `id_pk_pengiriman`, `pengiriman_tgl`, `pengiriman_status`, `pengiriman_tipe`, `id_fk_penjualan`, `id_fk_retur`, `pengiriman_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `pengiriman_create_date`, `pengiriman_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, '2020-06-29 16:45:45', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:45:45', '2020-06-29 04:45:45', 1, 1, 1248),
(2, 'after insert', 2, '2020-06-29 16:45:45', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:45:45', '2020-06-29 04:45:45', 1, 1, 1249),
(3, 'after insert', 3, '2020-06-29 16:50:34', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:50:34', '2020-06-29 04:50:34', 1, 1, 1250),
(4, 'after insert', 4, '2020-06-29 16:50:34', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:50:34', '2020-06-29 04:50:34', 1, 1, 1251),
(5, 'after insert', 5, '2020-06-29 16:55:55', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:55:55', '2020-06-29 04:55:55', 1, 1, 1252),
(6, 'after insert', 6, '2020-06-29 16:55:55', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:55:55', '2020-06-29 04:55:55', 1, 1, 1253),
(7, 'after insert', 7, '2020-06-29 16:58:12', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:58:12', '2020-06-29 04:58:12', 1, 1, 1255),
(8, 'after insert', 8, '2020-06-29 16:58:12', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 04:58:12', '2020-06-29 04:58:12', 1, 1, 1256),
(9, 'after insert', 9, '2020-06-29 17:01:57', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:01:57', '2020-06-29 05:01:57', 1, 1, 1258),
(10, 'after insert', 10, '2020-06-29 17:01:57', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:01:57', '2020-06-29 05:01:57', 1, 1, 1259),
(11, 'after insert', 11, '2020-06-29 17:02:08', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:08', '2020-06-29 05:02:08', 1, 1, 1261),
(12, 'after insert', 12, '2020-06-29 17:02:18', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:18', '2020-06-29 05:02:18', 1, 1, 1264),
(13, 'after insert', 13, '2020-06-29 17:02:21', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:21', '2020-06-29 05:02:21', 1, 1, 1267),
(14, 'after insert', 14, '2020-06-29 17:02:24', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:24', '2020-06-29 05:02:24', 1, 1, 1270),
(15, 'after update', 14, '2020-06-29 17:02:24', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:24', '2020-06-29 05:26:28', 1, 1, 1273),
(16, 'after update', 11, '2020-06-29 17:02:08', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:08', '2020-06-29 05:33:23', 1, 1, 1275),
(17, 'after update', 12, '2020-06-29 17:02:18', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:18', '2020-06-29 05:33:37', 1, 1, 1277),
(18, 'after update', 13, '2020-06-29 17:02:21', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:02:21', '2020-06-29 05:40:13', 1, 1, 1279),
(19, 'after insert', 15, '2020-06-29 17:44:13', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:13', '2020-06-29 05:44:13', 1, 1, 1285),
(20, 'after insert', 16, '2020-06-29 17:44:27', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:27', '2020-06-29 05:44:27', 1, 1, 1288),
(21, 'after insert', 17, '2020-06-29 17:44:49', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:49', '2020-06-29 05:44:49', 1, 1, 1291),
(22, 'after insert', 18, '2020-06-29 17:44:54', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:44:54', '2020-06-29 05:44:54', 1, 1, 1294),
(23, 'after insert', 19, '2020-06-29 17:50:13', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:50:13', '2020-06-29 05:50:13', 1, 1, 1301),
(24, 'after insert', 20, '2020-06-29 17:52:47', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:52:47', '2020-06-29 05:52:47', 1, 1, 1304),
(25, 'after insert', 21, '2020-06-29 17:53:44', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:53:44', '2020-06-29 05:53:44', 1, 1, 1307),
(26, 'after insert', 22, '2020-06-29 17:54:11', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:54:11', '2020-06-29 05:54:11', 1, 1, 1310),
(27, 'after update', 22, '2020-06-29 17:54:11', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:54:11', '2020-06-29 05:54:56', 1, 1, 1313),
(28, 'after insert', 23, '2020-06-29 17:55:39', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:39', '2020-06-29 05:55:39', 1, 1, 1319),
(29, 'after insert', 24, '2020-06-29 17:55:43', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:43', '2020-06-29 05:55:43', 1, 1, 1322),
(30, 'after insert', 25, '2020-06-29 17:55:46', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:46', '2020-06-29 05:55:46', 1, 1, 1325),
(31, 'after insert', 26, '2020-06-29 17:55:49', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:49', '2020-06-29 05:55:49', 1, 1, 1328),
(32, 'after update', 25, '2020-06-29 17:55:46', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:46', '2020-06-29 05:55:59', 1, 1, 1331),
(33, 'after update', 26, '2020-06-29 17:55:49', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:55:49', '2020-06-29 05:56:03', 1, 1, 1334),
(34, 'after insert', 27, '2020-06-29 17:59:17', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 05:59:17', '2020-06-29 05:59:17', 1, 1, 1337),
(35, 'after insert', 28, '2020-06-29 18:02:14', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:02:14', '2020-06-29 06:02:14', 1, 1, 1340),
(36, 'after update', 28, '2020-06-29 18:02:14', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:02:14', '2020-06-29 06:04:32', 1, 1, 1343),
(37, 'after insert', 29, '2020-06-29 18:04:37', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:04:37', '2020-06-29 06:04:37', 1, 1, 1346),
(38, 'after update', 29, '2020-06-29 18:04:37', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:04:37', '2020-06-29 06:07:05', 1, 1, 1349),
(39, 'after insert', 30, '2020-06-29 18:07:08', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:07:08', '2020-06-29 06:07:08', 1, 1, 1352),
(40, 'after insert', 31, '2020-06-29 18:09:05', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:09:05', '2020-06-29 06:09:05', 1, 1, 1357),
(41, 'after insert', 32, '2020-06-29 18:09:34', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:09:34', '2020-06-29 06:09:34', 1, 1, 1359),
(42, 'after insert', 33, '2020-06-29 18:10:03', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:10:03', '2020-06-29 06:10:03', 1, 1, 1361),
(43, 'after insert', 34, '2020-06-29 18:10:26', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:10:26', '2020-06-29 06:10:26', 1, 1, 1364),
(44, 'after insert', 35, '2020-06-29 18:10:43', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:10:43', '2020-06-29 06:10:43', 1, 1, 1367),
(45, 'after insert', 36, '2020-06-29 18:11:11', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:11', '2020-06-29 06:11:11', 1, 1, 1370),
(46, 'after insert', 37, '2020-06-29 18:11:38', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:38', '2020-06-29 06:11:38', 1, 1, 1372),
(47, 'after insert', 38, '2020-06-29 18:11:47', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:47', '2020-06-29 06:11:47', 1, 1, 1374),
(48, 'after insert', 39, '2020-06-29 18:11:58', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:11:58', '2020-06-29 06:11:58', 1, 1, 1376),
(49, 'after insert', 40, '2020-06-29 18:13:19', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:13:19', '2020-06-29 06:13:19', 1, 1, 1378),
(50, 'after insert', 41, '2020-06-29 18:18:49', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:18:49', '2020-06-29 06:18:49', 1, 1, 1380),
(51, 'after update', 34, '2020-06-29 18:10:26', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:10:26', '2020-06-29 06:18:59', 1, 1, 1384),
(52, 'after update', 41, '2020-06-29 18:18:49', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:18:49', '2020-06-29 06:26:23', 1, 1, 1388),
(53, 'after insert', 42, '2020-06-29 18:26:31', 'aktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:26:31', '2020-06-29 06:26:31', 1, 1, 1392),
(54, 'after update', 42, '2020-06-29 18:26:31', 'nonaktif', 'permintaan', 0, 0, 'cabang', NULL, 2, '2020-06-29 06:26:31', '2020-06-29 06:26:37', 1, 1, 1396),
(55, 'after insert', 43, '1111-11-11 00:00:00', 'AKTIF', 'penjualan', 2, 0, 'cabang', NULL, 2, '2020-06-29 06:27:12', '2020-06-29 06:27:12', 1, 1, 1400),
(56, 'after update', 43, '1111-11-11 00:00:00', 'AKTIF', 'penjualan', 2, 0, 'cabang', NULL, 2, '2020-06-29 06:27:12', '2020-06-29 06:27:36', 1, 1, 1405),
(57, 'after update', 43, '1111-11-11 00:00:00', 'nonaktif', 'penjualan', 2, 0, 'cabang', NULL, 2, '2020-06-29 06:27:12', '2020-06-29 06:27:46', 1, 1, 1410);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_penjualan`
--

CREATE TABLE `mstr_penjualan` (
  `id_pk_penjualan` int(11) NOT NULL,
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
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_penjualan`
--

INSERT INTO `mstr_penjualan` (`id_pk_penjualan`, `penj_nomor`, `penj_tgl`, `penj_dateline_tgl`, `penj_jenis`, `penj_tipe_pembayaran`, `penj_status`, `id_fk_customer`, `id_fk_cabang`, `penj_create_date`, `penj_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 06:55:38', 1, 1),
(2, 'nomorpenjualan4', '2222-02-22 00:00:00', '3333-03-31 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1),
(3, 'nopenj12', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:42:00', '2020-06-22 06:45:42', 1, 1),
(4, 'nopenj123', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:42:24', '2020-06-22 06:45:40', 1, 1),
(5, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:42:38', '2020-06-22 06:45:37', 1, 1),
(6, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:44:01', '2020-06-22 06:45:35', 1, 1),
(7, 'nompern1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 1, '2020-06-22 06:44:21', '2020-06-22 06:44:21', 1, 1),
(8, 'nopenj90', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:45:17', '2020-06-22 06:45:33', 1, 1);

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

--
-- Dumping data for table `mstr_penjualan_log`
--

INSERT INTO `mstr_penjualan_log` (`id_pk_penjualan_log`, `executed_function`, `id_pk_penjualan`, `penj_nomor`, `penj_tgl`, `penj_dateline_tgl`, `penj_jenis`, `penj_tipe_pembayaran`, `penj_status`, `id_fk_customer`, `id_fk_cabang`, `penj_create_date`, `penj_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 09:39:50', 1, 1, 318),
(2, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 09:53:30', 1, 1, 326),
(3, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 09:55:58', 1, 1, 328),
(4, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 10:05:28', 1, 1, 338),
(5, 'after insert', 2, 'nomorpenjualan1', '2222-02-22 00:00:00', '3333-03-31 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 2, '2020-06-22 05:37:45', '2020-06-22 05:37:45', 1, 1, 657),
(6, 'after update', 2, 'nomorpenjualan4', '2222-02-22 00:00:00', '3333-03-31 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 2, '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1, 754),
(7, 'after insert', 3, 'nopenj12', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 2, '2020-06-22 06:42:00', '2020-06-22 06:42:00', 1, 1, 816),
(8, 'after insert', 4, 'nopenj123', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 2, '2020-06-22 06:42:24', '2020-06-22 06:42:24', 1, 1, 817),
(9, 'after insert', 5, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 2, '2020-06-22 06:42:38', '2020-06-22 06:42:38', 1, 1, 820),
(10, 'after insert', 6, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 2, '2020-06-22 06:44:01', '2020-06-22 06:44:01', 1, 1, 823),
(11, 'after insert', 7, 'nompern1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 2, '2020-06-22 06:44:21', '2020-06-22 06:44:21', 1, 1, 826),
(12, 'after insert', 8, 'nopenj90', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 2, '2020-06-22 06:45:17', '2020-06-22 06:45:17', 1, 1, 827),
(13, 'after update', 8, 'nopenj90', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 2, '2020-06-22 06:45:17', '2020-06-22 06:45:33', 1, 1, 828),
(14, 'after update', 6, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 2, '2020-06-22 06:44:01', '2020-06-22 06:45:35', 1, 1, 829),
(15, 'after update', 5, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 2, '2020-06-22 06:42:38', '2020-06-22 06:45:37', 1, 1, 830),
(16, 'after update', 4, 'nopenj123', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 2, '2020-06-22 06:42:24', '2020-06-22 06:45:40', 1, 1, 831),
(17, 'after update', 3, 'nopenj12', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 2, '2020-06-22 06:42:00', '2020-06-22 06:45:42', 1, 1, 832),
(18, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 06:55:38', 1, 1, 837),
(19, 'after update', 2, 'nomorpenjualan4', '2222-02-22 00:00:00', '3333-03-31 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 2, '2020-06-22 05:37:45', '2020-06-27 11:05:31', 1, 1, 1214),
(20, 'after update', 2, 'nomorpenjualan4', '2222-02-22 00:00:00', '3333-03-31 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 2, '2020-06-22 05:37:45', '2020-06-27 11:07:37', 1, 1, 1220),
(21, 'after update', 2, 'nomorpenjualan4', '2222-02-22 00:00:00', '3333-03-31 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 2, '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1, 1225),
(22, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 06:55:38', 1, 1, 1695),
(23, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 06:55:38', 1, 1, 1696),
(24, 'after update', 2, 'nomorpenjualan4', '2222-02-22 00:00:00', '3333-03-31 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1, 1697),
(25, 'after update', 3, 'nopenj12', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:42:00', '2020-06-22 06:45:42', 1, 1, 1698),
(26, 'after update', 4, 'nopenj123', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:42:24', '2020-06-22 06:45:40', 1, 1, 1699),
(27, 'after update', 5, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:42:38', '2020-06-22 06:45:37', 1, 1, 1700),
(28, 'after update', 6, 'nopenj1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:44:01', '2020-06-22 06:45:35', 1, 1, 1701),
(29, 'after update', 7, 'nompern1234', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'AKTIF', 1, 1, '2020-06-22 06:44:21', '2020-06-22 06:44:21', 1, 1, 1702),
(30, 'after update', 8, 'nopenj90', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'OFFLINE', 'FULL PAYMENT', 'nonaktif', 1, 1, '2020-06-22 06:45:17', '2020-06-22 06:45:33', 1, 1, 1703);

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
  `retur_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_retur`
--

INSERT INTO `mstr_retur` (`id_pk_retur`, `id_fk_penjualan`, `retur_no`, `retur_tgl`, `retur_tipe`, `retur_status`, `retur_create_date`, `retur_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'nomorretur1', '1111-11-11 00:00:00', 'BARANG', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:54:54', 1, 1),
(2, 1, 'nomorretur2', '2020-06-24 00:00:00', 'UANG', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1),
(3, 2, 'nomoretur0001', '1111-11-11 00:00:00', 'BARANG', 'aktif', '2020-06-23 03:17:44', '2020-06-23 03:17:44', 1, 1),
(4, 2, 'nomoretur0002', '1111-11-11 00:00:00', 'BARANG', 'aktif', '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1);

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
  `retur_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
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
-- Dumping data for table `mstr_satuan`
--

INSERT INTO `mstr_satuan` (`id_pk_satuan`, `satuan_nama`, `satuan_rumus`, `satuan_status`, `satuan_create_date`, `satuan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'PCS', '1', 'AKTIF', '2020-06-22 08:36:13', '2020-06-22 08:36:13', 1, 1),
(2, 'BOX', '40', 'AKTIF', '2020-06-22 08:36:19', '2020-06-22 08:36:19', 1, 1),
(3, 'LUSIN', '12', 'AKTIF', '2020-06-22 08:36:23', '2020-06-22 08:36:23', 1, 1),
(4, 'BALL-48', '48', 'AKTIF', '2020-06-22 09:28:15', '2020-06-22 09:28:15', 1, 1);

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

--
-- Dumping data for table `mstr_satuan_log`
--

INSERT INTO `mstr_satuan_log` (`id_pk_satuan_log`, `executed_function`, `id_pk_satuan`, `satuan_nama`, `satuan_rumus`, `satuan_status`, `satuan_create_date`, `satuan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'PCS', 'AKTIF', '1', '2020-06-22 08:36:13', '2020-06-22 08:36:13', 1, 1, 264),
(2, 'after insert', 2, 'BOX', 'AKTIF', '40', '2020-06-22 08:36:19', '2020-06-22 08:36:19', 1, 1, 265),
(3, 'after insert', 3, 'LUSIN', 'AKTIF', '12', '2020-06-22 08:36:23', '2020-06-22 08:36:23', 1, 1, 266),
(4, 'after insert', 4, 'BALL-48', 'AKTIF', '48', '2020-06-22 09:28:15', '2020-06-22 09:28:15', 1, 1, 852);

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
-- Dumping data for table `mstr_supplier`
--

INSERT INTO `mstr_supplier` (`id_pk_sup`, `sup_nama`, `sup_no_npwp`, `sup_foto_npwp`, `sup_foto_kartu_nama`, `sup_badan_usaha`, `sup_no_rekening`, `sup_suff`, `sup_perusahaan`, `sup_email`, `sup_telp`, `sup_hp`, `sup_alamat`, `sup_keterangan`, `sup_status`, `sup_create_date`, `sup_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'pica', '-a', 'IMG_53011.jpg', 'Dating2.png', 'CV', '-a', 'MRS', 'suppliera', 'email@email.coma', '12345a', '12345a', '-a', '-a', 'AKTIF', '2020-07-02 08:21:23', '2020-07-02 08:37:09', 1, 1);

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

--
-- Dumping data for table `mstr_supplier_log`
--

INSERT INTO `mstr_supplier_log` (`id_pk_sup_log`, `executed_function`, `id_pk_sup`, `sup_nama`, `sup_no_npwp`, `sup_foto_npwp`, `sup_foto_kartu_nama`, `sup_badan_usaha`, `sup_no_rekening`, `sup_suff`, `sup_perusahaan`, `sup_email`, `sup_telp`, `sup_hp`, `sup_alamat`, `sup_keterangan`, `sup_status`, `sup_create_date`, `sup_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'pic', '-', 'Logo_Variant-01.png', 'Dating.png', 'Toko', '-', 'Tn', 'supplier', 'email@email.com', '12345', '12345', '-', '-', 'AKTIF', '2020-07-02 08:21:23', '2020-07-02 08:21:23', 1, 1, 1627),
(2, 'after update', 1, 'pic', '-', 'WhatsApp_Image_2020-05-29_at_11_34_451.jpeg', 'WhatsApp_Image_2020-05-29_at_11_35_241.jpeg', 'Toko', '-', 'Tn', 'supplier', 'email@email.com', '12345', '12345', '-', '-', 'AKTIF', '2020-07-02 08:21:23', '2020-07-02 08:28:43', 1, 1, 1628),
(3, 'after update', 1, 'pic', '-', 'IMG_5301.jpg', 'Dating1.png', 'Toko', '-', 'Tn', 'supplier', 'email@email.com', '12345', '12345', '-', '-', 'AKTIF', '2020-07-02 08:21:23', '2020-07-02 08:36:54', 1, 1, 1629),
(4, 'after update', 1, 'pica', '-a', 'IMG_53011.jpg', 'Dating2.png', 'CV', '-a', 'MRS', 'suppliera', 'email@email.coma', '12345a', '12345a', '-a', '-a', 'AKTIF', '2020-07-02 08:21:23', '2020-07-02 08:37:09', 1, 1, 1630);

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

INSERT INTO `mstr_toko` (`id_pk_toko`, `toko_logo`, `toko_nama`, `toko_kop_surat`, `toko_nonpkp`, `toko_pernyataan_rek`, `toko_kode`, `toko_status`, `toko_create_date`, `toko_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Pendaftaran_SYNC_STUDY.png', 'Maju Mandiri', 'IMG_53013.jpg', 'IMG_53013.jpg', 'IMG_53013.jpg', 'MM', 'AKTIF', '2020-07-02 09:36:12', '2020-07-02 09:55:05', 1, 1);

--
-- Triggers `mstr_toko`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_toko` AFTER INSERT ON `mstr_toko` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_logo,toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_toko,new.toko_logo,new.toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_toko` AFTER UPDATE ON `mstr_toko` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_logo,toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_toko,new.toko_logo,new.toko_nama,toko_kop_surat,toko_nonpkp,toko_pernyataan_rek,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
  `toko_kode` varchar(20) DEFAULT NULL,
  `toko_status` varchar(15) DEFAULT NULL,
  `toko_create_date` datetime DEFAULT NULL,
  `toko_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mstr_toko_log`
--

INSERT INTO `mstr_toko_log` (`id_pk_toko_log`, `executed_function`, `id_pk_toko`, `toko_logo`, `toko_nama`, `toko_kop_surat`, `toko_nonpkp`, `toko_pernyataan_rek`, `toko_kode`, `toko_status`, `toko_create_date`, `toko_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Dating2.png', 'Maju Mandiri', NULL, NULL, NULL, 'MM', 'AKTIF', '2020-07-02 09:36:12', '2020-07-02 09:36:12', 1, 1, 1631),
(2, 'after update', 1, 'IMG_53011.jpg', 'Maju Mandiri', NULL, NULL, NULL, 'MM', 'AKTIF', '2020-07-02 09:36:12', '2020-07-02 09:53:34', 1, 1, 1632),
(3, 'after update', 1, 'Logo_Variant-01.png', 'Maju Mandiri', NULL, NULL, NULL, 'MM', 'AKTIF', '2020-07-02 09:36:12', '2020-07-02 09:54:33', 1, 1, 1633),
(4, 'after update', 1, 'Pendaftaran_SYNC_STUDY.png', 'Maju Mandiri', NULL, NULL, NULL, 'MM', 'AKTIF', '2020-07-02 09:36:12', '2020-07-02 09:55:05', 1, 1, 1634);

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
  `user_last_modified` datetime DEFAULT NULL,
  `user_create_date` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_user`
--

INSERT INTO `mstr_user` (`id_pk_user`, `user_name`, `user_pass`, `user_email`, `user_status`, `id_fk_role`, `user_last_modified`, `user_create_date`, `id_create_date`, `id_last_modified`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@example.com', 'AKTIF', 1, '2020-06-21 23:26:35', '2020-06-21 23:26:35', 0, 0),
(2, 'adminku2', 'e807f1fcf82d132f9bb018ca6738a19f', 'elfkyushfly@gmail.com', 'AKTIF', 2, '2020-06-22 05:10:25', '2020-06-22 11:06:08', 1, 1),
(3, 'admin3', '25d55ad283aa400af464c76d713c07ad', 'admin3@example.com', 'AKTIF', 2, '2020-06-22 05:11:24', '2020-06-22 05:11:24', 1, 1);

--
-- Triggers `mstr_user`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_user` AFTER INSERT ON `mstr_user` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at',' ', new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after insert',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_user` AFTER UPDATE ON `mstr_user` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after update',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
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
  `user_last_modified` datetime DEFAULT NULL,
  `user_create_date` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mstr_user_log`
--

INSERT INTO `mstr_user_log` (`id_pk_user_log`, `executed_function`, `id_pk_user`, `user_name`, `user_pass`, `user_email`, `user_status`, `id_fk_role`, `user_last_modified`, `user_create_date`, `id_create_date`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@example.com', 'AKTIF', 1, '2020-06-21 23:26:35', '2020-06-21 23:26:35', 0, 0, 2),
(2, 'after insert', 2, 'adminku2', 'e807f1fcf82d132f9bb018ca6738a19f', 'elfkyushfly@gmail.com', 'AKTIF', 2, '2020-06-22 11:06:08', '2020-06-22 11:06:08', 1, 1, 391),
(3, 'after update', 2, 'adminku2', 'e807f1fcf82d132f9bb018ca6738a19f', 'elfkyushfly@gmail.com', 'AKTIF', 2, '2020-06-22 05:10:25', '2020-06-22 11:06:08', 1, 1, 596),
(4, 'after insert', 3, 'admin3', '25d55ad283aa400af464c76d713c07ad', 'admin3@example.com', 'AKTIF', 2, '2020-06-22 05:11:24', '2020-06-22 05:11:24', 1, 1, 597);

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
-- Dumping data for table `mstr_warehouse`
--

INSERT INTO `mstr_warehouse` (`id_pk_warehouse`, `warehouse_nama`, `warehouse_alamat`, `warehouse_notelp`, `warehouse_desc`, `warehouse_status`, `warehouse_create_date`, `warehouse_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'GUDANG 1', 'Puri Indah', '12345', '-', 'AKTIF', '2020-06-21 11:45:42', '2020-06-21 11:45:42', 1, 1),
(2, 'GUDANG 2', 'Jalan asdasd Green Lake City', '0216194567', 'deskripsi si si sis', 'AKTIF', '2020-06-22 01:24:49', '2020-06-22 01:25:01', 1, 1);

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

--
-- Dumping data for table `mstr_warehouse_log`
--

INSERT INTO `mstr_warehouse_log` (`id_pk_warehouse_log`, `executed_function`, `id_pk_warehouse`, `warehouse_nama`, `warehouse_alamat`, `warehouse_notelp`, `warehouse_desc`, `warehouse_status`, `warehouse_create_date`, `warehouse_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'GUDANG 1', 'Puri Indah', '12345', '-', 'AKTIF', '2020-06-21 11:45:42', '2020-06-21 11:45:42', 1, 1, 92),
(2, 'after insert', 2, 'GUDANG 2', 'Jalan asdasd Green Lake City', '0216194567', 'deskripsi si si si', 'AKTIF', '2020-06-22 01:24:49', '2020-06-22 01:24:49', 1, 1, 395),
(3, 'after update', 2, 'GUDANG 2', 'Jalan asdasd Green Lake City', '0216194567', 'deskripsi si si sis', 'AKTIF', '2020-06-22 01:24:49', '2020-06-22 01:25:01', 1, 1, 396);

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
(4, 6, 2, 3, 'aktif', '2020-06-22 17:14:55', '2020-07-04 12:04:55', 1, 1),
(5, 7, 1, 3, 'nonaktif', '2020-06-22 21:17:46', '2020-07-03 18:21:11', 1, 1),
(6, 7, 2, 3, 'aktif', '2020-06-22 21:17:46', '2020-06-22 21:17:46', 1, 1),
(7, 6, 1, 1, 'aktif', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1),
(8, 6, 2, 1, 'nonaktif', '2020-06-29 12:17:37', '2020-07-04 10:27:50', 1, 1),
(9, 7, 3, 2, 'aktif', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1),
(10, 7, 4, 2, 'aktif', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1),
(11, 7, 5, 2, 'aktif', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1),
(12, 6, 4, 4, 'aktif', '2020-07-04 10:28:29', '2020-07-04 12:04:55', 1, 1),
(13, 6, 3, 3, 'aktif', '2020-07-04 10:28:29', '2020-07-04 12:04:55', 1, 1);

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

--
-- Dumping data for table `tbl_barang_kombinasi_log`
--

INSERT INTO `tbl_barang_kombinasi_log` (`id_pk_barang_kombinasi_log`, `executed_function`, `id_pk_barang_kombinasi`, `id_barang_utama`, `id_barang_kombinasi`, `barang_kombinasi_qty`, `barang_kombinasi_status`, `barang_kombinasi_create_date`, `barang_kombinasi_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:16', 1, 1, 352),
(2, 'after insert', 2, 5, 4, 6, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:16', 1, 1, 353),
(3, 'after update', 1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1, 356),
(4, 'after update', 2, 5, 4, 6, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1, 357),
(5, 'after insert', 3, 6, 1, 1, 'aktif', '2020-06-22 17:14:55', '2020-06-22 17:14:55', 1, 1, 601),
(6, 'after insert', 4, 6, 2, 1, 'aktif', '2020-06-22 17:14:55', '2020-06-22 17:14:55', 1, 1, 602),
(7, 'after update', 3, 6, 1, 2, 'aktif', '2020-06-22 17:14:55', '2020-06-22 17:15:39', 1, 1, 604),
(8, 'after update', 4, 6, 2, 1, 'aktif', '2020-06-22 17:14:55', '2020-06-22 17:15:39', 1, 1, 605),
(9, 'after insert', 5, 7, 1, 3, 'aktif', '2020-06-22 21:17:46', '2020-06-22 21:17:46', 1, 1, 849),
(10, 'after insert', 6, 7, 2, 3, 'aktif', '2020-06-22 21:17:46', '2020-06-22 21:17:46', 1, 1, 850),
(11, 'after insert', 7, 6, 1, 1, 'aktif', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1, 1239),
(12, 'after insert', 8, 6, 2, 1, 'aktif', '2020-06-29 12:17:37', '2020-06-29 12:17:37', 1, 1, 1240),
(13, 'after insert', 9, 7, 3, 2, 'aktif', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1, 1242),
(14, 'after insert', 10, 7, 4, 2, 'aktif', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1, 1243),
(15, 'after insert', 11, 7, 5, 2, 'aktif', '2020-06-29 12:17:53', '2020-06-29 12:17:53', 1, 1, 1244),
(16, 'after update', 5, 7, 1, 3, 'nonaktif', '2020-06-22 21:17:46', '2020-07-03 18:21:11', 1, 1, 1704),
(17, 'after update', 1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:00', 1, 1, 1774),
(18, 'after update', 2, 5, 4, 5, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:00', 1, 1, 1775),
(19, 'after update', 1, 5, 2, 3, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:10', 1, 1, 1777),
(20, 'after update', 2, 5, 4, 5, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:10', 1, 1, 1778),
(21, 'after update', 1, 5, 2, 3, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:19', 1, 1, 1780),
(22, 'after update', 2, 5, 4, 3, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:19', 1, 1, 1781),
(23, 'after update', 1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:27', 1, 1, 1783),
(24, 'after update', 2, 5, 4, 3, 'aktif', '2020-06-22 10:39:16', '2020-07-04 10:27:27', 1, 1, 1784),
(25, 'after update', 8, 6, 2, 1, 'nonaktif', '2020-06-29 12:17:37', '2020-07-04 10:27:50', 1, 1, 1785),
(26, 'after update', 4, 6, 2, 3, 'aktif', '2020-06-22 17:14:55', '2020-07-04 10:27:59', 1, 1, 1787),
(27, 'after update', 4, 6, 2, 3, 'aktif', '2020-06-22 17:14:55', '2020-07-04 10:28:29', 1, 1, 1789),
(28, 'after insert', 12, 6, 4, 4, 'aktif', '2020-07-04 10:28:29', '2020-07-04 10:28:29', 1, 1, 1790),
(29, 'after insert', 13, 6, 3, 3, 'aktif', '2020-07-04 10:28:29', '2020-07-04 10:28:29', 1, 1, 1791),
(30, 'after update', 4, 6, 2, 3, 'aktif', '2020-06-22 17:14:55', '2020-07-04 12:04:55', 1, 1, 1796),
(31, 'after update', 12, 6, 4, 4, 'aktif', '2020-07-04 10:28:29', '2020-07-04 12:04:55', 1, 1, 1797),
(32, 'after update', 13, 6, 3, 3, 'aktif', '2020-07-04 10:28:29', '2020-07-04 12:04:55', 1, 1, 1798);

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

--
-- Dumping data for table `tbl_barang_ukuran`
--

INSERT INTO `tbl_barang_ukuran` (`ID_PK_BARANG_UKURAN`, `ID_FK_BARANG`, `UKURAN`, `BRG_UKURAN_STATUS`, `BRG_UKURAN_CREATE_DATE`, `BRG_UKURAN_LAST_MODIFIED`, `ID_CREATE_DATE`, `ID_LAST_MODIFIED`) VALUES
(4, 11, ',', 'AKTIF', '2020-05-08 15:54:57', '2020-05-08 15:54:57', 1, 1),
(8, 12, ',', 'AKTIF', '2020-05-08 15:57:11', '2020-05-08 15:57:11', 1, 1),
(9, 13, '5', 'AKTIF', '2020-05-08 15:58:36', '2020-05-08 15:58:36', 1, 1),
(10, 13, '6', 'AKTIF', '2020-05-08 15:58:36', '2020-05-08 15:58:36', 1, 1),
(11, 13, '7', 'AKTIF', '2020-05-08 15:58:36', '2020-05-08 15:58:36', 1, 1),
(12, 14, '5', 'AKTIF', '2020-05-08 15:58:45', '2020-05-08 15:58:45', 1, 1),
(13, 14, '6', 'AKTIF', '2020-05-08 15:58:45', '2020-05-08 15:58:45', 1, 1),
(14, 14, '7', 'AKTIF', '2020-05-08 15:58:45', '2020-05-08 15:58:45', 1, 1),
(15, 15, '33', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(16, 15, '44', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(17, 15, '55', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(18, 15, '66', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(23, 44, '11', 'AKTIF', '2020-05-21 23:41:10', '2020-05-21 23:41:10', 1, 1),
(24, 44, '12', 'AKTIF', '2020-05-21 23:41:10', '2020-05-21 23:41:10', 1, 1),
(25, 44, '13', 'AKTIF', '2020-05-21 23:41:10', '2020-05-21 23:41:10', 1, 1),
(59, 45, '11', 'AKTIF', '2020-05-26 08:11:44', '2020-05-26 08:11:44', 1, 1),
(60, 3, '7', 'AKTIF', '2020-05-29 03:38:40', '2020-05-29 03:38:40', 1, 1),
(61, 3, '5', 'AKTIF', '2020-05-29 03:38:40', '2020-05-29 03:38:40', 1, 1),
(62, 3, '6', 'AKTIF', '2020-05-29 03:38:40', '2020-05-29 03:38:40', 1, 1),
(63, 43, '11', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(64, 43, '22', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(65, 43, '33', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(66, 43, '44', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(70, 2, '11', 'AKTIF', '2020-05-29 03:40:17', '2020-05-29 03:40:17', 1, 1),
(71, 2, '12', 'AKTIF', '2020-05-29 03:40:17', '2020-05-29 03:40:17', 1, 1),
(72, 2, '13', 'AKTIF', '2020-05-29 03:40:17', '2020-05-29 03:40:17', 1, 1),
(73, 4, '1', 'AKTIF', '2020-05-29 03:41:03', '2020-05-29 03:41:03', 1, 1),
(74, 4, '2', 'AKTIF', '2020-05-29 03:41:03', '2020-05-29 03:41:03', 1, 1),
(75, 4, '3', 'AKTIF', '2020-05-29 03:41:03', '2020-05-29 03:41:03', 1, 1);

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
(1, 210, '-', 'nonaktif', 0, 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1),
(2, 205, '-', 'nonaktif', 30000, 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1),
(3, 10, '-', 'nonaktif', 40000, 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1),
(4, 210, '-', 'nonaktif', 0, 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1),
(5, 205, '-', 'nonaktif', 30000, 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1),
(6, 10, '-', 'nonaktif', 40000, 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1),
(7, 210, '-', 'AKTIF', 0, 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1),
(8, 205, '-', 'AKTIF', 30000, 2, 1, '2020-06-22 08:09:14', '2020-07-04 10:25:03', 1, 1),
(9, 10, '-', 'nonaktif', 40000, 3, 1, '2020-06-22 08:09:14', '2020-07-04 08:16:25', 1, 1),
(10, 21, 'poiuytrewq', 'nonaktif', 0, 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1),
(11, 100, 'Auto insert from item existance check', 'aktif', 0, 4, 1, '2020-06-22 01:46:33', '2020-07-04 09:53:02', 1, 1),
(12, 10, '-', 'AKTIF', 15000, 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1),
(13, 300, '-', 'AKTIF', 14000, 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:48:13', 1, 1),
(14, 300, '-', 'AKTIF', 0, 3, 2, '2020-06-22 03:01:02', '2020-07-04 09:06:57', 1, 1),
(15, 400, '-', 'AKTIF', 0, 4, 2, '2020-06-22 03:01:02', '2020-07-04 09:06:34', 1, 1),
(16, 60, '-', 'AKTIF', 0, 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1),
(17, 100, '-', 'AKTIF', 0, 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1),
(18, 30, '-', 'AKTIF', 0, 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1),
(19, 10, '-', 'nonaktif', 0, 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1),
(20, 10, 'Auto insert from item existance check', 'nonaktif', 0, 3, 1, '2020-07-04 09:08:25', '2020-07-04 09:11:07', 1, 1),
(21, 10, 'Auto insert from item existance check', 'nonaktif', 0, 3, 1, '2020-07-04 09:11:07', '2020-07-04 09:11:13', 1, 1),
(22, 10, 'Auto insert from item existance check', 'nonaktif', 0, 3, 1, '2020-07-04 09:11:13', '2020-07-04 09:11:50', 1, 1),
(23, 10, 'Auto insert from item existance check', 'nonaktif', 0, 3, 1, '2020-07-04 09:11:50', '2020-07-04 09:16:55', 1, 1),
(24, 21, '-', 'AKTIF', 0, 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1),
(25, 10, '-', 'AKTIF', 0, 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1),
(26, 100, 'Auto insert from item existance check', 'aktif', 0, 3, 1, '2020-07-04 10:18:46', '2020-07-04 09:52:43', 1, 1);

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

--
-- Dumping data for table `tbl_brg_cabang_log`
--

INSERT INTO `tbl_brg_cabang_log` (`id_pk_brg_cabang_log`, `executed_function`, `id_pk_brg_cabang`, `brg_cabang_qty`, `brg_cabang_last_price`, `brg_cabang_notes`, `brg_cabang_status`, `id_fk_brg`, `id_fk_cabang`, `brg_cabang_create_date`, `brg_cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 0, 10, '-', 'AKTIF', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:03:53', 1, 1, 218),
(2, 'after insert', 2, 0, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:04:32', 1, 1, 219),
(3, 'after insert', 3, 0, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:04:32', 1, 1, 220),
(4, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 221),
(5, 'after update', 2, 0, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:07:25', 1, 1, 222),
(6, 'after update', 3, 0, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:07:27', 1, 1, 223),
(7, 'after insert', 4, 0, 10, '-', 'AKTIF', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:07:40', 1, 1, 224),
(8, 'after insert', 5, 0, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:07:40', 1, 1, 225),
(9, 'after insert', 6, 0, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:07:40', 1, 1, 226),
(10, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 227),
(11, 'after update', 5, 0, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:58', 1, 1, 228),
(12, 'after update', 6, 0, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:09:00', 1, 1, 229),
(13, 'after insert', 7, 0, 10, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:09:14', 1, 1, 230),
(14, 'after insert', 8, 0, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:09:14', 1, 1, 231),
(15, 'after insert', 9, 0, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:09:14', 1, 1, 232),
(16, 'after update', 7, 0, 15, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 233),
(17, 'after update', 8, 0, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:06', 1, 1, 234),
(18, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 246),
(19, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 247),
(20, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 248),
(21, 'after update', 3, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 250),
(22, 'after update', 6, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 251),
(23, 'after update', 9, 40000, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 252),
(24, 'after update', 1, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 269),
(25, 'after update', 4, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 270),
(26, 'after update', 7, 0, 16, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 271),
(27, 'after update', 2, 30000, 11, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 273),
(28, 'after update', 5, 30000, 11, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 274),
(29, 'after update', 8, 30000, 16, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 275),
(30, 'after update', 1, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 278),
(31, 'after update', 4, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 279),
(32, 'after update', 7, 0, 16, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 280),
(33, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 282),
(34, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 283),
(35, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 284),
(36, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 289),
(37, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 290),
(38, 'after update', 7, 0, 15, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 291),
(39, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 293),
(40, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 294),
(41, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 295),
(42, 'after update', 1, 0, 12, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 299),
(43, 'after update', 4, 0, 12, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 300),
(44, 'after update', 7, 0, 17, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 301),
(45, 'after update', 2, 30000, 12, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 303),
(46, 'after update', 5, 30000, 12, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 304),
(47, 'after update', 8, 30000, 17, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 305),
(48, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 308),
(49, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 309),
(50, 'after update', 7, 0, 15, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 310),
(51, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 312),
(52, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 313),
(53, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 314),
(54, 'after insert', 10, 0, 88, 'poiuytrewq', 'AKTIF', 5, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1, 399),
(55, 'after insert', 11, 0, 0, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1, 400),
(56, 'after insert', 12, 0, 100, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 557),
(57, 'after insert', 13, 0, 100, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 558),
(58, 'after insert', 14, 0, 100, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 559),
(59, 'after insert', 15, 0, 100, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 560),
(60, 'after insert', 16, 0, 100, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 561),
(61, 'after insert', 17, 0, 10, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 639),
(62, 'after update', 12, 15000, 100, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 642),
(63, 'after update', 13, 14000, 100, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 644),
(64, 'after update', 12, 15000, 101, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 648),
(65, 'after update', 13, 14000, 101, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 650),
(66, 'after update', 12, 15000, 141, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 653),
(67, 'after update', 13, 14000, 113, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 655),
(68, 'after update', 1, 0, 9, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 667),
(69, 'after update', 4, 0, 9, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 668),
(70, 'after update', 7, 0, 14, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 669),
(71, 'after update', 2, 30000, 9, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 671),
(72, 'after update', 5, 30000, 9, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 672),
(73, 'after update', 8, 30000, 14, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 673),
(74, 'after update', 3, 40000, 9, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 675),
(75, 'after update', 6, 40000, 9, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 676),
(76, 'after update', 9, 40000, 9, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 677),
(77, 'after update', 1, 0, 8, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 680),
(78, 'after update', 4, 0, 8, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 681),
(79, 'after update', 7, 0, 13, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 682),
(80, 'after update', 2, 30000, 8, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 684),
(81, 'after update', 5, 30000, 8, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 685),
(82, 'after update', 8, 30000, 13, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 686),
(83, 'after update', 3, 40000, 8, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 688),
(84, 'after update', 6, 40000, 8, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 689),
(85, 'after update', 9, 40000, 8, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 690),
(86, 'after update', 1, 0, 7, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 693),
(87, 'after update', 4, 0, 7, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 694),
(88, 'after update', 7, 0, 12, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 695),
(89, 'after update', 2, 30000, 7, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 697),
(90, 'after update', 5, 30000, 7, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 698),
(91, 'after update', 8, 30000, 12, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 699),
(92, 'after update', 3, 40000, 7, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 701),
(93, 'after update', 6, 40000, 7, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 702),
(94, 'after update', 9, 40000, 7, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 703),
(95, 'after update', 1, 0, 8, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 730),
(96, 'after update', 4, 0, 8, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 731),
(97, 'after update', 7, 0, 13, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 732),
(98, 'after update', 2, 30000, 8, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 734),
(99, 'after update', 5, 30000, 8, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 735),
(100, 'after update', 8, 30000, 13, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 736),
(101, 'after update', 3, 40000, 8, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 738),
(102, 'after update', 6, 40000, 8, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 739),
(103, 'after update', 9, 40000, 8, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 740),
(104, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 743),
(105, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 744),
(106, 'after update', 7, 0, 15, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 745),
(107, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 747),
(108, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 748),
(109, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 749),
(110, 'after update', 3, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 751),
(111, 'after update', 6, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 752),
(112, 'after update', 9, 40000, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 753),
(113, 'after update', 12, 15000, 131, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 763),
(114, 'after update', 14, 0, 88, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 765),
(115, 'after update', 1, 0, 58, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 855),
(116, 'after update', 4, 0, 58, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 856),
(117, 'after update', 7, 0, 63, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 857),
(118, 'after update', 2, 30000, 11, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 859),
(119, 'after update', 5, 30000, 11, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 860),
(120, 'after update', 8, 30000, 16, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 861),
(121, 'after update', 13, 14000, NULL, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 878),
(122, 'after update', 12, 15000, 141, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 882),
(123, 'after update', 13, 14000, NULL, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 884),
(124, 'after update', 1, 0, 68, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 890),
(125, 'after update', 4, 0, 68, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 891),
(126, 'after update', 7, 0, 73, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 892),
(127, 'after update', 2, 30000, 21, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 894),
(128, 'after update', 5, 30000, 21, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 895),
(129, 'after update', 8, 30000, 26, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 896),
(130, 'after update', 1, 0, 78, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 899),
(131, 'after update', 4, 0, 78, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 900),
(132, 'after update', 7, 0, 83, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 901),
(133, 'after update', 2, 30000, 31, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 903),
(134, 'after update', 5, 30000, 31, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 904),
(135, 'after update', 8, 30000, 36, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 905),
(136, 'after update', 1, 0, 88, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 908),
(137, 'after update', 4, 0, 88, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 909),
(138, 'after update', 7, 0, 93, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 910),
(139, 'after update', 2, 30000, 41, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 912),
(140, 'after update', 5, 30000, 41, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 913),
(141, 'after update', 8, 30000, 46, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 914),
(142, 'after update', 1, 0, 98, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 917),
(143, 'after update', 4, 0, 98, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 918),
(144, 'after update', 7, 0, 103, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 919),
(145, 'after update', 2, 30000, 51, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 921),
(146, 'after update', 5, 30000, 51, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 922),
(147, 'after update', 8, 30000, 56, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 923),
(148, 'after update', 1, 0, 108, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 926),
(149, 'after update', 4, 0, 108, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 927),
(150, 'after update', 7, 0, 113, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 928),
(151, 'after update', 2, 30000, 61, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 930),
(152, 'after update', 5, 30000, 61, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 931),
(153, 'after update', 8, 30000, 66, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 932),
(154, 'after update', 1, 0, 118, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 935),
(155, 'after update', 4, 0, 118, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 936),
(156, 'after update', 7, 0, 123, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 937),
(157, 'after update', 2, 30000, 71, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 939),
(158, 'after update', 5, 30000, 71, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 940),
(159, 'after update', 8, 30000, 76, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 941),
(160, 'after update', 12, 15000, 151, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 944),
(161, 'after update', 13, 14000, NULL, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 946),
(162, 'after update', 13, 14000, NULL, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 949),
(163, 'after update', 13, 14000, NULL, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 953),
(164, 'after update', 12, 15000, 251, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 957),
(165, 'after update', 13, 14000, NULL, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 959),
(166, 'after update', 13, 14000, 100, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-23 10:46:08', 1, 1, 960),
(167, 'after update', 12, 15000, 351, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 963),
(168, 'after update', 13, 14000, 200, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-23 10:46:08', 1, 1, 965),
(169, 'after update', 13, 14000, 210, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-23 10:46:08', 1, 1, 974),
(170, 'after update', 12, 15000, 361, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 994),
(171, 'after update', 14, 0, 98, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 996),
(172, 'after update', 12, 15000, 371, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 999),
(173, 'after update', 14, 0, 108, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1001),
(174, 'after update', 12, 15000, 383, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1004),
(175, 'after update', 14, 0, 120, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1006),
(176, 'after update', 12, 15000, 383, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1009),
(177, 'after update', 14, 0, 978, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1011),
(178, 'after update', 12, 15000, 361, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1019),
(179, 'after update', 14, 0, 98, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1021),
(180, 'after update', 12, 15000, 371, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1026),
(181, 'after update', 14, 0, 498, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1028),
(182, 'after update', 12, 15000, 361, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1036),
(183, 'after update', 14, 0, 483, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1038),
(184, 'after update', 15, 0, 90, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1052),
(185, 'after update', 15, 0, 80, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1055),
(186, 'after update', 12, 15000, 351, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1062),
(187, 'after update', 14, 0, 473, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1064),
(188, 'after update', 12, 15000, 341, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1072),
(189, 'after update', 14, 0, 453, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1074),
(190, 'after update', 15, 0, 70, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1077),
(191, 'after update', 15, 0, 160, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1084),
(192, 'after update', 15, 0, 1060, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1087),
(193, 'after update', 15, 0, 60, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1102),
(194, 'after insert', 18, 0, 100, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1245),
(195, 'after update', 12, 15000, 331, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1382),
(196, 'after update', 12, 15000, 321, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1386),
(197, 'after update', 12, 15000, 331, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1390),
(198, 'after update', 12, 15000, 321, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1394),
(199, 'after update', 12, 15000, 331, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1398),
(200, 'after update', 12, 15000, 321, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1402),
(201, 'after update', 14, 0, 443, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1404),
(202, 'after update', 12, 15000, 316, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1407),
(203, 'after update', 14, 0, 443, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1409),
(204, 'after update', 12, 15000, 331, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1412),
(205, 'after update', 14, 0, 453, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1414),
(206, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1480),
(207, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1481),
(208, 'after update', 7, 0, NULL, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 1482),
(209, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1486),
(210, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1487),
(211, 'after update', 7, 0, NULL, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 1488),
(212, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1492),
(213, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1493),
(214, 'after update', 7, 0, NULL, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 1494),
(215, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1498),
(216, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1499),
(217, 'after update', 7, 0, NULL, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 1500),
(218, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1504),
(219, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1505),
(220, 'after update', 7, 0, NULL, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 1506),
(221, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1510),
(222, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1511),
(223, 'after update', 7, 0, NULL, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 1512),
(224, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1516),
(225, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1517),
(226, 'after update', 7, 0, NULL, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 1518),
(227, 'after update', 7, 0, 1000, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1526),
(228, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1529),
(229, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1530),
(230, 'after update', 7, 0, 1010, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1531),
(231, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1538),
(232, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1539),
(233, 'after update', 7, 0, 1020, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1540),
(234, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1544),
(235, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1545),
(236, 'after update', 7, 0, 1030, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1546),
(237, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1556),
(238, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1557),
(239, 'after update', 7, 0, 1040, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1558),
(240, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1565),
(241, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1566),
(242, 'after update', 7, 0, 1050, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1567),
(243, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1571),
(244, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1572),
(245, 'after update', 7, 0, 1040, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1573),
(246, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1577),
(247, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1578),
(248, 'after update', 7, 0, 1050, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1579),
(249, 'after update', 2, 30000, 81, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1581),
(250, 'after update', 5, 30000, 81, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1582),
(251, 'after update', 8, 30000, 86, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 1583),
(252, 'after update', 1, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1586),
(253, 'after update', 4, 0, NULL, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1587),
(254, 'after update', 7, 0, 1040, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1588),
(255, 'after update', 2, 30000, 71, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1590),
(256, 'after update', 5, 30000, 71, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1591),
(257, 'after update', 8, 30000, 76, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 1592),
(258, 'after update', 1, 0, 100, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1706),
(259, 'after update', 2, 30000, 100, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1707),
(260, 'after update', 3, 40000, 100, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1708),
(261, 'after update', 4, 0, 100, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1709),
(262, 'after update', 5, 30000, 100, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1710),
(263, 'after update', 6, 40000, 100, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1711),
(264, 'after update', 7, 0, 100, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1712),
(265, 'after update', 8, 30000, 100, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 1713),
(266, 'after update', 9, 40000, 100, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 1714),
(267, 'after update', 10, 0, 100, 'poiuytrewq', 'AKTIF', 5, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1, 1715),
(268, 'after update', 11, 0, 100, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1, 1716),
(269, 'after update', 12, 15000, 100, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1717),
(270, 'after update', 13, 14000, 100, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-06-23 10:46:08', 1, 1, 1718),
(271, 'after update', 14, 0, 100, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1719),
(272, 'after update', 15, 0, 100, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1720),
(273, 'after update', 16, 0, 100, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1721),
(274, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1722),
(275, 'after update', 18, 0, 100, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1723),
(276, 'after update', 9, 40000, 100, '-', 'nonaktif', 3, 1, '2020-06-22 08:09:14', '2020-07-04 08:16:25', 1, 1, 1725),
(277, 'after update', 13, 14000, 1005, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 08:55:46', 1, 1, 1726),
(278, 'after insert', 19, 0, 100, '-', 'AKTIF', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:08:25', 1, 1, 1727),
(279, 'after insert', 20, 0, 0, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 09:08:25', '2020-07-04 09:08:25', 1, 1, 1728),
(280, 'after update', 20, 0, 0, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:08:25', '2020-07-04 09:11:07', 1, 1, 1729),
(281, 'after insert', 21, 0, 0, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 09:11:07', '2020-07-04 09:11:07', 1, 1, 1730),
(282, 'after update', 21, 0, 0, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:11:07', '2020-07-04 09:11:13', 1, 1, 1731),
(283, 'after insert', 22, 0, 0, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 09:11:13', '2020-07-04 09:11:13', 1, 1, 1732),
(284, 'after update', 22, 0, 0, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 09:11:13', '2020-07-04 09:11:27', 1, 1, 1733),
(285, 'after update', 22, 0, 0, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:11:13', '2020-07-04 09:11:50', 1, 1, 1734),
(286, 'after insert', 23, 0, 0, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 09:11:50', '2020-07-04 09:11:50', 1, 1, 1735),
(287, 'after update', 19, 0, 100, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1736),
(288, 'after update', 23, 0, 0, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:11:50', '2020-07-04 09:16:55', 1, 1, 1737),
(289, 'after update', 10, 0, 100, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1738),
(290, 'after insert', 24, 0, 100, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:18:46', 1, 1, 1739),
(291, 'after insert', 25, 0, 100, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:18:46', 1, 1, 1740),
(292, 'after insert', 26, 0, 0, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 10:18:46', '2020-07-04 10:18:46', 1, 1, 1741),
(293, 'after update', 8, 30000, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-07-04 10:25:03', 1, 1, 1742),
(294, 'after update', 26, 0, 10, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:06', 1, 1, 1743),
(295, 'after update', 11, 0, 10, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-07-04 10:25:10', 1, 1, 1744),
(296, 'after update', 24, 0, 10, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1745),
(297, 'after update', 25, 0, 10, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1746),
(298, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1747),
(299, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1748),
(300, 'after update', 3, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1749),
(301, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1750),
(302, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1751),
(303, 'after update', 6, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1752),
(304, 'after update', 7, 0, 10, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1753),
(305, 'after update', 8, 30000, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-07-04 10:25:03', 1, 1, 1754),
(306, 'after update', 9, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:09:14', '2020-07-04 08:16:25', 1, 1, 1755),
(307, 'after update', 10, 0, 10, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1756),
(308, 'after update', 11, 0, 10, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-07-04 10:25:10', 1, 1, 1757),
(309, 'after update', 12, 15000, 10, '-', 'AKTIF', 1, 2, '2020-06-22 03:01:02', '2020-06-22 05:28:46', 1, 1, 1758),
(310, 'after update', 13, 14000, 10, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 08:55:46', 1, 1, 1759),
(311, 'after update', 14, 0, 10, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1760),
(312, 'after update', 15, 0, 10, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1761),
(313, 'after update', 16, 0, 10, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1762),
(314, 'after update', 17, 0, 10, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1763),
(315, 'after update', 18, 0, 10, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1764),
(316, 'after update', 19, 0, 10, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1765),
(317, 'after update', 20, 0, 10, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:08:25', '2020-07-04 09:11:07', 1, 1, 1766),
(318, 'after update', 21, 0, 10, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:11:07', '2020-07-04 09:11:13', 1, 1, 1767),
(319, 'after update', 22, 0, 10, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:11:13', '2020-07-04 09:11:50', 1, 1, 1768),
(320, 'after update', 23, 0, 10, 'Auto insert from item existance check', 'nonaktif', 3, 1, '2020-07-04 09:11:50', '2020-07-04 09:16:55', 1, 1, 1769),
(321, 'after update', 24, 0, 10, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1770),
(322, 'after update', 25, 0, 10, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1771),
(323, 'after update', 26, 0, 10, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:06', 1, 1, 1772),
(324, 'after update', 14, 0, 20, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-07-04 10:29:14', 1, 1, 1792),
(325, 'after update', 15, 0, 20, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-07-04 10:29:26', 1, 1, 1793),
(326, 'after update', 13, 14000, 50, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 10:30:42', 1, 1, 1794),
(327, 'after update', 2, 30000, 0, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1801),
(328, 'after update', 5, 30000, 0, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1802),
(329, 'after update', 8, 30000, 0, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-07-04 10:25:03', 1, 1, 1803),
(330, 'after update', 11, 0, 20, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-07-04 10:25:10', 1, 1, 1804),
(331, 'after update', 11, 0, 15, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-07-04 10:25:10', 1, 1, 1806),
(332, 'after update', 2, 30000, 5, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1807),
(333, 'after update', 5, 30000, 5, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1808),
(334, 'after update', 8, 30000, 5, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-07-04 10:25:03', 1, 1, 1809),
(335, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1810),
(336, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1811),
(337, 'after update', 16, 0, 6, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1812),
(338, 'after update', 17, 0, 5, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1813),
(339, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1814),
(340, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1815),
(341, 'after update', 18, 0, 3, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1816),
(342, 'after update', 13, 14000, 100, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:04:38', 1, 1, 1817),
(343, 'after update', 15, 0, 50, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-07-04 09:04:49', 1, 1, 1818),
(344, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1819),
(345, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1820),
(346, 'after update', 16, 0, 16, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1821),
(347, 'after update', 17, 0, 6, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1822),
(348, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1823),
(349, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1824),
(350, 'after update', 18, 0, 8, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1825),
(351, 'after update', 15, 0, 200, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-07-04 09:06:01', 1, 1, 1826),
(352, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1827),
(353, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1828),
(354, 'after update', 16, 0, 20, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1829),
(355, 'after update', 17, 0, 6, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1830),
(356, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1831),
(357, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1832),
(358, 'after update', 18, 0, 10, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1833),
(359, 'after update', 13, 14000, 300, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:06:30', 1, 1, 1834),
(360, 'after update', 15, 0, 400, '-', 'AKTIF', 4, 2, '2020-06-22 03:01:02', '2020-07-04 09:06:34', 1, 1, 1835),
(361, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1836),
(362, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1837),
(363, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1838),
(364, 'after update', 17, 0, 6, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1839),
(365, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1840),
(366, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1841),
(367, 'after update', 18, 0, 10, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1842),
(368, 'after update', 14, 0, 300, '-', 'AKTIF', 3, 2, '2020-06-22 03:01:02', '2020-07-04 09:06:57', 1, 1, 1843),
(369, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1844),
(370, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1845),
(371, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1846),
(372, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1847),
(373, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1848),
(374, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1849),
(375, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1850),
(376, 'after update', 13, 14000, 500, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:08:08', 1, 1, 1851),
(377, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1852),
(378, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1853),
(379, 'after update', 16, 0, 100, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1854),
(380, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1855),
(381, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1856),
(382, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1857),
(383, 'after update', 18, 0, 50, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1858),
(384, 'after update', 13, 14000, 10, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:08:35', 1, 1, 1859),
(385, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1860),
(386, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1861),
(387, 'after update', 16, 0, 2, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1862),
(388, 'after update', 17, 0, 3, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1863),
(389, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1864),
(390, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1865),
(391, 'after update', 18, 0, 1, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1866),
(392, 'after update', 13, 14000, 0, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:08:53', 1, 1, 1867),
(393, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1868),
(394, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1869),
(395, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1870),
(396, 'after update', 17, 0, 0, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1871),
(397, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1872),
(398, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1873),
(399, 'after update', 18, 0, 0, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1874),
(400, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1875),
(401, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1876),
(402, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1877),
(403, 'after update', 17, 0, 0, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1878),
(404, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1879),
(405, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1880),
(406, 'after update', 18, 0, 0, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1881),
(407, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1882),
(408, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1883),
(409, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1884),
(410, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1885),
(411, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1886),
(412, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1887),
(413, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1888),
(414, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1889),
(415, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1890),
(416, 'after update', 17, 0, 0, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1891),
(417, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1892),
(418, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1893),
(419, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1894),
(420, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1895),
(421, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1896),
(422, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1897),
(423, 'after update', 17, 0, 0, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1898),
(424, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1899),
(425, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1900),
(426, 'after update', 18, 0, 0, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1901),
(427, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1902),
(428, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1903),
(429, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1904),
(430, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1905),
(431, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1906),
(432, 'after update', 16, 0, 0, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1907),
(433, 'after update', 17, 0, 0, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1908),
(434, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1909),
(435, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1910),
(436, 'after update', 18, 0, 0, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1911);
INSERT INTO `tbl_brg_cabang_log` (`id_pk_brg_cabang_log`, `executed_function`, `id_pk_brg_cabang`, `brg_cabang_qty`, `brg_cabang_last_price`, `brg_cabang_notes`, `brg_cabang_status`, `id_fk_brg`, `id_fk_cabang`, `brg_cabang_create_date`, `brg_cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(437, 'after update', 13, 14000, 100, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:45:06', 1, 1, 1912),
(438, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1913),
(439, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1914),
(440, 'after update', 16, 0, 20, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1915),
(441, 'after update', 17, 0, 33, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1916),
(442, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1917),
(443, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1918),
(444, 'after update', 18, 0, 10, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1919),
(445, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1920),
(446, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1921),
(447, 'after update', 16, 0, 20, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1922),
(448, 'after update', 17, 0, 33, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1923),
(449, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1924),
(450, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1925),
(451, 'after update', 18, 0, 10, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1926),
(452, 'after update', 13, 14000, 200, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:45:53', 1, 1, 1927),
(453, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1928),
(454, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1929),
(455, 'after update', 16, 0, 40, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1930),
(456, 'after update', 17, 0, 66, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1931),
(457, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1932),
(458, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1933),
(459, 'after update', 18, 0, 20, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1934),
(460, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1935),
(461, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1936),
(462, 'after update', 16, 0, 40, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1937),
(463, 'after update', 17, 0, 66, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1938),
(464, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1939),
(465, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1940),
(466, 'after update', 18, 0, 20, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1941),
(467, 'after update', 13, 14000, 300, '-', 'AKTIF', 2, 2, '2020-06-22 03:01:02', '2020-07-04 09:48:13', 1, 1, 1942),
(468, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1943),
(469, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1944),
(470, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1945),
(471, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1946),
(472, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1947),
(473, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1948),
(474, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1949),
(475, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1950),
(476, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1951),
(477, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1952),
(478, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1953),
(479, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1954),
(480, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1955),
(481, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1956),
(482, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1957),
(483, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1958),
(484, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1959),
(485, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1960),
(486, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1961),
(487, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1962),
(488, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1963),
(489, 'after update', 10, 0, 1, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1964),
(490, 'after update', 24, 0, 1, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1965),
(491, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1966),
(492, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1967),
(493, 'after update', 19, 0, 0, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1968),
(494, 'after update', 25, 0, 0, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1969),
(495, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1970),
(496, 'after update', 1, 0, 110, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 1973),
(497, 'after update', 4, 0, 110, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 1974),
(498, 'after update', 7, 0, 110, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 1975),
(499, 'after update', 2, 30000, 105, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 1977),
(500, 'after update', 5, 30000, 105, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 1978),
(501, 'after update', 8, 30000, 105, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-07-04 10:25:03', 1, 1, 1979),
(502, 'after update', 10, 0, 5, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1980),
(503, 'after update', 24, 0, 5, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1981),
(504, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1982),
(505, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1983),
(506, 'after update', 19, 0, 2, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1984),
(507, 'after update', 25, 0, 2, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1985),
(508, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1986),
(509, 'after update', 10, 0, 5, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1987),
(510, 'after update', 24, 0, 5, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1988),
(511, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1989),
(512, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1990),
(513, 'after update', 19, 0, 2, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1991),
(514, 'after update', 25, 0, 2, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1992),
(515, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 1993),
(516, 'after update', 10, 0, 5, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 1994),
(517, 'after update', 24, 0, 5, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 1995),
(518, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 1996),
(519, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 1997),
(520, 'after update', 19, 0, 2, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 1998),
(521, 'after update', 25, 0, 2, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 1999),
(522, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 2000),
(523, 'after update', 26, 0, 100, 'Auto insert from item existance check', 'aktif', 3, 1, '2020-07-04 10:18:46', '2020-07-04 09:52:43', 1, 1, 2001),
(524, 'after update', 10, 0, 5, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 2002),
(525, 'after update', 24, 0, 5, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 2003),
(526, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 2004),
(527, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 2005),
(528, 'after update', 19, 0, 2, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 2006),
(529, 'after update', 25, 0, 2, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 2007),
(530, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 2008),
(531, 'after update', 10, 0, 5, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 2009),
(532, 'after update', 24, 0, 5, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 2010),
(533, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 2011),
(534, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 2012),
(535, 'after update', 19, 0, 2, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 2013),
(536, 'after update', 25, 0, 2, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 2014),
(537, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 2015),
(538, 'after update', 10, 0, 5, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 2016),
(539, 'after update', 24, 0, 5, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 2017),
(540, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 2018),
(541, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 2019),
(542, 'after update', 19, 0, 2, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 2020),
(543, 'after update', 25, 0, 2, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 2021),
(544, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 2022),
(545, 'after update', 10, 0, 5, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 2023),
(546, 'after update', 24, 0, 5, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 2024),
(547, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 2025),
(548, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 2026),
(549, 'after update', 19, 0, 2, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 2027),
(550, 'after update', 25, 0, 2, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 2028),
(551, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 2029),
(552, 'after update', 11, 0, 100, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-07-04 09:53:02', 1, 1, 2030),
(553, 'after update', 10, 0, 21, 'poiuytrewq', 'nonaktif', 5, 1, '2020-06-22 01:46:33', '2020-07-04 10:18:19', 1, 1, 2031),
(554, 'after update', 24, 0, 21, '-', 'AKTIF', 5, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:16', 1, 1, 2032),
(555, 'after update', 16, 0, 60, '-', 'AKTIF', 5, 2, '2020-06-22 03:01:02', '2020-06-22 03:01:02', 1, 1, 2033),
(556, 'after update', 17, 0, 100, '-', 'AKTIF', 6, 2, '2020-06-22 05:26:20', '2020-06-22 05:26:20', 1, 1, 2034),
(557, 'after update', 19, 0, 10, '-', 'nonaktif', 7, 1, '2020-07-04 09:08:25', '2020-07-04 09:16:51', 1, 1, 2035),
(558, 'after update', 25, 0, 10, '-', 'AKTIF', 7, 1, '2020-07-04 10:18:46', '2020-07-04 10:25:21', 1, 1, 2036),
(559, 'after update', 18, 0, 30, '-', 'AKTIF', 7, 2, '2020-06-29 12:18:09', '2020-06-29 12:18:09', 1, 1, 2037),
(560, 'after update', 1, 0, 210, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 2040),
(561, 'after update', 4, 0, 210, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 2041),
(562, 'after update', 7, 0, 210, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-07-01 12:28:58', 1, 1, 2042),
(563, 'after update', 2, 30000, 205, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 2044),
(564, 'after update', 5, 30000, 205, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 2045),
(565, 'after update', 8, 30000, 205, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-07-04 10:25:03', 1, 1, 2046);

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_brg_pembelian`
--

INSERT INTO `tbl_brg_pembelian` (`id_pk_brg_pembelian`, `brg_pem_qty`, `brg_pem_satuan`, `brg_pem_harga`, `brg_pem_note`, `brg_pem_status`, `id_fk_pembelian`, `id_fk_barang`, `brg_pem_create_date`, `brg_pem_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'pcs', 10000, '-', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(2, 2, 'pcs', 11000, '-', 'AKTIF', 1, 2, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(3, 4, 'Pcs', 30000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1),
(4, 5, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1),
(5, 10, 'Pcs', 20000, '-', 'nonaktif', 2, 1, '2020-06-22 08:27:08', '2020-06-22 08:27:18', 1, 1),
(6, 15, 'Item', 15000, '-', 'AKTIF', 3, 1, '2020-06-22 05:28:46', '2020-06-22 05:28:46', 1, 1),
(7, 10, 'Item', 14000, '-', 'AKTIF', 3, 2, '2020-06-22 05:28:46', '2020-06-22 05:28:46', 1, 1);

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_brg_pembelian_log`
--

INSERT INTO `tbl_brg_pembelian_log` (`id_pk_brg_pembelian_log`, `executed_function`, `id_pk_brg_pembelian`, `brg_pem_qty`, `brg_pem_satuan`, `brg_pem_harga`, `brg_pem_note`, `brg_pem_status`, `id_fk_pembelian`, `id_fk_barang`, `brg_pem_create_date`, `brg_pem_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 'pcs', 10000, '-', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 237),
(2, 'after insert', 2, 2, 'pcs', 11000, '-', 'AKTIF', 1, 2, '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 238),
(3, 'after update', 1, 1, 'pcs', 10000, '-', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1, 240),
(4, 'after update', 2, 2, 'pcs', 11000, '-', 'AKTIF', 1, 2, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1, 241),
(5, 'after insert', 3, 4, 'Pcs', 30000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 245),
(6, 'after insert', 4, 5, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 249),
(7, 'after update', 3, 4, 'Pcs', 300000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:26:54', 1, 1, 255),
(8, 'after update', 4, 5, 'Pcs', 400000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:26:54', 1, 1, 256),
(9, 'after update', 3, 4, 'Pcs', 30000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1, 259),
(10, 'after update', 4, 5, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1, 260),
(11, 'after insert', 5, 10, 'Pcs', 20000, '-', 'AKTIF', 2, 1, '2020-06-22 08:27:08', '2020-06-22 08:27:08', 1, 1, 261),
(12, 'after update', 5, 10, 'Pcs', 20000, '-', 'nonaktif', 2, 1, '2020-06-22 08:27:08', '2020-06-22 08:27:18', 1, 1, 263),
(13, 'after insert', 6, 15, 'Item', 15000, '-', 'AKTIF', 3, 1, '2020-06-22 05:28:46', '2020-06-22 05:28:46', 1, 1, 641),
(14, 'after insert', 7, 10, 'Item', 14000, '-', 'AKTIF', 3, 2, '2020-06-22 05:28:46', '2020-06-22 05:28:46', 1, 1, 643);

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
(1, 10, 'CABANG', 'nonaktif', 1, 2, 0, '2020-06-22 08:37:11', '2020-06-22 02:20:24', 1, 1),
(2, 5, 'CABANG', 'nonaktif', 1, 2, 0, '2020-06-22 08:55:25', '2020-06-22 02:19:54', 1, 1),
(3, 17, 'CABANG', 'Perjalanan', 1, 2, 0, '2020-06-22 09:20:19', '2020-07-01 12:13:14', 1, 1),
(4, 10, 'CABANG', 'nonaktif', 1, 2, 0, '2020-06-22 08:01:19', '2020-06-22 03:01:24', 1, 1),
(5, 10, 'CABANG', 'Diterima', 1, 2, 0, '2020-06-22 10:50:21', '2020-07-01 12:14:19', 1, 1),
(6, 10, 'CABANG', 'Perjalanan', 1, 2, 0, '2020-06-22 10:50:35', '2020-07-01 12:33:11', 1, 1),
(7, 10, 'CABANG', 'Aktif', 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:26:37', 1, 1),
(8, 10, 'CABANG', 'Perjalanan', 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:32:03', 1, 1),
(9, 10, 'CABANG', 'nonaktif', 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 01:51:12', 1, 1),
(10, 10, 'CABANG', 'nonaktif', 1, 2, 0, '2020-06-22 10:50:36', '2020-06-22 05:50:58', 1, 1),
(11, 10, 'CABANG', 'nonaktif', 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 01:51:03', 1, 1);

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

--
-- Dumping data for table `tbl_brg_pemenuhan_log`
--

INSERT INTO `tbl_brg_pemenuhan_log` (`id_pk_brg_pemenuhan_log`, `executed_function`, `id_pk_brg_pemenuhan`, `brg_pemenuhan_qty`, `brg_pemenuhan_tipe`, `brg_pemenuhan_status`, `id_fk_brg_permintaan`, `id_fk_cabang`, `id_fk_warehouse`, `brg_pemenuhan_create_date`, `brg_pemenuhan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 08:37:11', '2020-06-22 08:37:11', 1, 1, 500),
(2, 'after insert', 2, 5, 'CABANG', NULL, 1, 2, 0, '2020-06-22 08:55:25', '2020-06-22 08:55:25', 1, 1, 502),
(3, 'after insert', 2, 5, 'CABANG', NULL, 1, 2, 0, '2020-06-22 08:55:25', NULL, 1, 1, 503),
(4, 'after insert', 2, 5, 'CABANG', NULL, 1, 2, 0, '2020-06-22 08:55:25', '2020-06-22 02:19:54', 1, 1, 504),
(5, 'after insert', 3, 17, 'CABANG', NULL, 1, 2, 0, '2020-06-22 09:20:19', '2020-06-22 09:20:19', 1, 1, 506),
(6, 'after insert', 1, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 08:37:11', '2020-06-22 02:20:24', 1, 1, 507),
(7, 'after insert', 3, 17, 'CABANG', NULL, 1, 2, 0, '2020-06-22 09:20:19', '2020-06-22 02:21:14', 1, 1, 508),
(8, 'after insert', 4, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 08:01:19', '2020-06-22 08:01:19', 1, 1, 563),
(9, 'after insert', 4, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 08:01:19', '2020-06-22 03:01:24', 1, 1, 564),
(10, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-22 10:50:21', 1, 1, 714),
(11, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-22 10:50:35', 1, 1, 716),
(12, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-22 10:50:35', 1, 1, 718),
(13, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-22 10:50:36', 1, 1, 720),
(14, 'after insert', 9, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-22 10:50:36', 1, 1, 722),
(15, 'after insert', 10, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-22 10:50:36', 1, 1, 724),
(16, 'after insert', 11, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-22 10:50:36', 1, 1, 726),
(17, 'after insert', 10, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-22 05:50:58', 1, 1, 727),
(18, 'after insert', 11, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 01:51:03', 1, 1, 1246),
(19, 'after insert', 9, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 01:51:12', 1, 1, 1247),
(20, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 04:55:55', 1, 1, 1254),
(21, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 04:58:12', 1, 1, 1257),
(22, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:01:57', 1, 1, 1260),
(23, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:02:08', 1, 1, 1263),
(24, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:02:18', 1, 1, 1266),
(25, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:02:21', 1, 1, 1269),
(26, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:02:24', 1, 1, 1272),
(27, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:02:24', 1, 1, 1281),
(28, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:02:18', 1, 1, 1282),
(29, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:02:21', 1, 1, 1283),
(30, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:02:08', 1, 1, 1284),
(31, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:44:14', 1, 1, 1287),
(32, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:44:27', 1, 1, 1290),
(33, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:44:49', 1, 1, 1293),
(34, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:44:54', 1, 1, 1296),
(35, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:44:14', 1, 1, 1297),
(36, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:44:54', 1, 1, 1298),
(37, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:44:49', 1, 1, 1299),
(38, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:44:27', 1, 1, 1300),
(39, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:50:13', 1, 1, 1303),
(40, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:52:47', 1, 1, 1306),
(41, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:53:44', 1, 1, 1309),
(42, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:54:11', 1, 1, 1312),
(43, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:54:11', 1, 1, 1315),
(44, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:52:47', 1, 1, 1316),
(45, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:50:13', 1, 1, 1317),
(46, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:53:44', 1, 1, 1318),
(47, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:55:39', 1, 1, 1321),
(48, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:55:43', 1, 1, 1324),
(49, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:55:46', 1, 1, 1327),
(50, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:55:49', 1, 1, 1330),
(51, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:55:59', 1, 1, 1333),
(52, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:56:03', 1, 1, 1336),
(53, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:59:17', 1, 1, 1339),
(54, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:02:14', 1, 1, 1342),
(55, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:04:32', 1, 1, 1345),
(56, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:04:37', 1, 1, 1348),
(57, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:07:05', 1, 1, 1351),
(58, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 05:55:43', 1, 1, 1354),
(59, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 05:55:39', 1, 1, 1355),
(60, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 05:59:17', 1, 1, 1356),
(61, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-06-29 06:10:03', 1, 1, 1363),
(62, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:10:27', 1, 1, 1366),
(63, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:10:43', 1, 1, 1369),
(64, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 06:18:49', 1, 1, 1383),
(65, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:18:59', 1, 1, 1387),
(66, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-06-29 06:26:23', 1, 1, 1391),
(67, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:26:31', 1, 1, 1395),
(68, 'after insert', 7, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-06-29 06:26:37', 1, 1, 1399),
(69, 'after insert', 3, 17, 'CABANG', NULL, 1, 2, 0, '2020-06-22 09:20:19', '2020-07-01 12:11:27', 1, 1, 1483),
(70, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-07-01 12:12:32', 1, 1, 1489),
(71, 'after insert', 3, 17, 'CABANG', NULL, 1, 2, 0, '2020-06-22 09:20:19', '2020-07-01 12:13:14', 1, 1, 1495),
(72, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-07-01 12:13:39', 1, 1, 1501),
(73, 'after insert', 5, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:21', '2020-07-01 12:14:19', 1, 1, 1507),
(74, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-07-01 12:17:51', 1, 1, 1513),
(75, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:18:24', 1, 1, 1519),
(76, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:28:17', 1, 1, 1522),
(77, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-07-01 12:28:21', 1, 1, 1525),
(78, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:29:02', 1, 1, 1532),
(79, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:29:10', 1, 1, 1535),
(80, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-07-01 12:29:20', 1, 1, 1541),
(81, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:29:22', 1, 1, 1547),
(82, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:29:30', 1, 1, 1550),
(83, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-07-01 12:30:28', 1, 1, 1553),
(84, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:31:57', 1, 1, 1559),
(85, 'after insert', 8, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:36', '2020-07-01 12:32:03', 1, 1, 1562),
(86, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-07-01 12:33:05', 1, 1, 1568),
(87, 'after insert', 6, 10, 'CABANG', NULL, 1, 2, 0, '2020-06-22 10:50:35', '2020-07-01 12:33:11', 1, 1, 1574);

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
(18, 100, '-', 18, 2, 0, 0, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1);

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

--
-- Dumping data for table `tbl_brg_penerimaan_log`
--

INSERT INTO `tbl_brg_penerimaan_log` (`id_pk_brg_penerimaan_log`, `executed_function`, `id_pk_brg_penerimaan`, `brg_penerimaan_qty`, `brg_penerimaan_note`, `id_fk_penerimaan`, `id_fk_brg_pembelian`, `id_fk_brg_retur`, `id_fk_brg_pengiriman`, `id_fk_satuan`, `brg_penerimaan_create_date`, `brg_penerimaan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, NULL, '-', 4, 0, 0, 3, 1, '2020-07-01 12:11:27', '2020-07-01 12:11:27', 1, 1, 1479),
(2, 'after insert', 2, NULL, '-', 5, 0, 0, 5, 1, '2020-07-01 12:12:32', '2020-07-01 12:12:32', 1, 1, 1485),
(3, 'after insert', 3, NULL, '-', 6, 0, 0, 3, 1, '2020-07-01 12:13:14', '2020-07-01 12:13:14', 1, 1, 1491),
(4, 'after insert', 4, 10, '-', 7, 0, 0, 5, 1, '2020-07-01 12:13:39', '2020-07-01 12:13:39', 1, 1, 1497),
(5, 'after insert', 5, 10, '-', 8, 0, 0, 5, 1, '2020-07-01 12:14:19', '2020-07-01 12:14:19', 1, 1, 1503),
(6, 'after insert', 6, 10, '-', 9, 0, 0, 5, 1, '2020-07-01 12:17:51', '2020-07-01 12:17:51', 1, 1, 1509),
(7, 'after insert', 7, 10, '-', 10, 0, 0, 3, 1, '2020-07-01 12:18:24', '2020-07-01 12:18:24', 1, 1, 1515),
(8, 'after insert', 7, 0, '-', 10, 0, 0, 3, 1, '2020-07-01 12:18:24', '2020-07-01 12:28:17', 1, 1, 1521),
(9, 'after insert', 6, 0, '-', 9, 0, 0, 5, 1, '2020-07-01 12:17:51', '2020-07-01 12:28:21', 1, 1, 1524),
(10, 'after insert', 8, 10, '-', 11, 0, 0, 3, 1, '2020-07-01 12:29:02', '2020-07-01 12:29:02', 1, 1, 1528),
(11, 'after insert', 8, 0, '-', 11, 0, 0, 3, 1, '2020-07-01 12:29:02', '2020-07-01 12:29:10', 1, 1, 1534),
(12, 'after insert', 9, 10, '-', 12, 0, 0, 5, 1, '2020-07-01 12:29:20', '2020-07-01 12:29:20', 1, 1, 1537),
(13, 'after insert', 10, 10, '-', 13, 0, 0, 3, 1, '2020-07-01 12:29:22', '2020-07-01 12:29:22', 1, 1, 1543),
(14, 'after insert', 10, 0, '-', 13, 0, 0, 3, 1, '2020-07-01 12:29:22', '2020-07-01 12:29:30', 1, 1, 1549),
(15, 'after insert', 9, 0, '-', 12, 0, 0, 5, 1, '2020-07-01 12:29:20', '2020-07-01 12:30:28', 1, 1, 1552),
(16, 'after insert', 11, 10, '-', 14, 0, 0, 3, 1, '2020-07-01 12:31:57', '2020-07-01 12:31:57', 1, 1, 1555),
(17, 'after insert', 11, 0, '-', 14, 0, 0, 3, 1, '2020-07-01 12:31:57', '2020-07-01 12:32:03', 1, 1, 1561),
(18, 'after insert', 12, 10, '-', 15, 0, 0, 5, 1, '2020-07-01 12:33:04', '2020-07-01 12:33:04', 1, 1, 1564),
(19, 'after insert', 12, 0, '-', 15, 0, 0, 5, 1, '2020-07-01 12:33:04', '2020-07-01 12:33:11', 1, 1, 1570),
(20, 'after insert', 13, 10, '-', 16, 1, 0, 0, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:05', 1, 1, 1576),
(21, 'after insert', 14, 10, '-', 16, 2, 0, 0, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:05', 1, 1, 1580),
(22, 'after insert', 13, 0, '-', 16, 1, 0, 0, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:21', 1, 1, 1585),
(23, 'after insert', 14, 0, '-', 16, 2, 0, 0, 1, '2020-07-01 12:36:05', '2020-07-01 12:36:21', 1, 1, 1589),
(24, 'after insert', 15, 100, '-', 17, 1, 0, 0, 1, '2020-07-04 09:52:12', '2020-07-04 09:52:12', 1, 1, 1972),
(25, 'after insert', 16, 100, '-', 17, 2, 0, 0, 1, '2020-07-04 09:52:12', '2020-07-04 09:52:12', 1, 1, 1976),
(26, 'after insert', 17, 100, '-', 18, 1, 0, 0, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1, 2039),
(27, 'after insert', 18, 100, '-', 18, 2, 0, 0, 1, '2020-07-04 09:54:07', '2020-07-04 09:54:07', 1, 1, 2043);

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
(3, 10, '-', 33, 0, 0, 8, 1, '2020-06-29 06:10:03', '2020-06-29 06:10:03', 1, 1),
(4, 0, '-', 34, 0, 0, 7, 1, '2020-06-29 06:10:27', '2020-06-29 06:18:59', 1, 1),
(5, 10, '-', 35, 0, 0, 6, 1, '2020-06-29 06:10:43', '2020-06-29 06:10:43', 1, 1),
(11, 0, '-', 41, 0, 0, 5, 1, '2020-06-29 06:18:49', '2020-06-29 06:26:23', 1, 1),
(12, 0, '-', 42, 0, 0, 7, 1, '2020-06-29 06:26:31', '2020-06-29 06:26:37', 1, 1),
(13, 0, '-', 43, 4, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:46', 1, 1),
(14, 0, '-', 43, 5, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:46', 1, 1);

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
            call update_stok_barang_warehouse(@id_barang,@id_warehouse,0,0,@brg_pengiriman_qty,@id_satuan_terima);
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
            call update_stok_barang_cabang(@id_barang,@id_warehouse,@brg_keluar_qty,@id_satuan_keluar,@brg_pengiriman_qty,@id_satuan_terima);
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

--
-- Dumping data for table `tbl_brg_pengiriman_log`
--

INSERT INTO `tbl_brg_pengiriman_log` (`id_pk_brg_pengiriman_log`, `executed_function`, `id_pk_brg_pengiriman`, `brg_pengiriman_qty`, `brg_pengiriman_note`, `id_fk_pengiriman`, `id_fk_brg_penjualan`, `id_fk_brg_retur_kembali`, `id_fk_brg_pemenuhan`, `id_fk_satuan`, `brg_pengiriman_create_date`, `brg_pengiriman_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(3, 'after insert', 3, 10, '-', 33, 0, 0, 8, 1, '2020-06-29 06:10:03', '2020-06-29 06:10:03', 1, 1, 1362),
(4, 'after insert', 4, 10, '-', 34, 0, 0, 7, 1, '2020-06-29 06:10:27', '2020-06-29 06:10:27', 1, 1, 1365),
(5, 'after insert', 5, 10, '-', 35, 0, 0, 6, 1, '2020-06-29 06:10:43', '2020-06-29 06:10:43', 1, 1, 1368),
(11, 'after insert', 11, 10, '-', 41, 0, 0, 5, 1, '2020-06-29 06:18:49', '2020-06-29 06:18:49', 1, 1, 1381),
(12, 'after update', 4, 0, '-', 34, 0, 0, 7, 1, '2020-06-29 06:10:27', '2020-06-29 06:18:59', 1, 1, 1385),
(13, 'after update', 11, 0, '-', 41, 0, 0, 5, 1, '2020-06-29 06:18:49', '2020-06-29 06:26:23', 1, 1, 1389),
(14, 'after insert', 12, 10, '-', 42, 0, 0, 7, 1, '2020-06-29 06:26:31', '2020-06-29 06:26:31', 1, 1, 1393),
(15, 'after update', 12, 0, '-', 42, 0, 0, 7, 1, '2020-06-29 06:26:31', '2020-06-29 06:26:37', 1, 1, 1397),
(16, 'after insert', 13, 10, '-', 43, 4, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:13', 1, 1, 1401),
(17, 'after insert', 14, 10, '-', 43, 5, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:13', 1, 1, 1403),
(18, 'after update', 13, 15, '-', 43, 4, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:36', 1, 1, 1406),
(19, 'after update', 14, 10, '-', 43, 5, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:36', 1, 1, 1408),
(20, 'after update', 13, 0, '-', 43, 4, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:46', 1, 1, 1411),
(21, 'after update', 14, 0, '-', 43, 5, 0, 0, 1, '2020-06-29 06:27:13', '2020-06-29 06:27:46', 1, 1, 1413);

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
(1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1),
(2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1),
(3, 3, 'Pcs', 4, 'Pcs', 4000, '-', 'AKTIF', 1, 3, '2020-06-22 09:55:58', '2020-06-22 06:55:38', 1, 1),
(4, 10, 'Pcs', 15, 'Pcs', 30000, '-', 'AKTIF', 2, 1, '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1),
(5, 15, 'Pcs', 15, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1),
(6, 10, 'Pcs', 20, 'Pcs', 40000, '-', 'AKTIF', 4, 4, '2020-06-22 06:42:24', '2020-06-22 06:42:24', 1, 1),
(7, 10, 'Pcs', 20, 'Pcs', 40000, '-', 'AKTIF', 5, 4, '2020-06-22 06:42:38', '2020-06-22 06:42:38', 1, 1),
(8, 10, 'Pcs', 20, 'Pcs', 40000, '-', 'AKTIF', 6, 4, '2020-06-22 06:44:01', '2020-06-22 06:44:01', 1, 1);

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

--
-- Dumping data for table `tbl_brg_penjualan_log`
--

INSERT INTO `tbl_brg_penjualan_log` (`id_pk_brg_penjualan_log`, `executed_function`, `id_pk_brg_penjualan`, `brg_penjualan_qty_real`, `brg_penjualan_satuan_real`, `brg_penjualan_qty`, `brg_penjualan_satuan`, `brg_penjualan_harga`, `brg_penjualan_note`, `brg_penjualan_status`, `id_fk_penjualan`, `id_fk_barang`, `brg_penjualan_create_date`, `brg_penjualan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 320),
(2, 'after insert', 2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 321),
(3, 'after insert', 3, 3, 'Pcs', 4, 'Pcs', 40000, '-', 'AKTIF', 1, 3, '2020-06-22 09:55:58', '2020-06-22 09:55:58', 1, 1, 330),
(4, 'after update', 1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 331),
(5, 'after update', 2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 332),
(6, 'after update', 1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 340),
(7, 'after update', 2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 341),
(8, 'after update', 3, 3, 'Pcs', 4, 'Pcs', 4000, '-', 'AKTIF', 1, 3, '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1, 342),
(9, 'after insert', 4, 10, 'Pcs', 15, 'Pcs', 30000, '-', 'AKTIF', 2, 1, '2020-06-22 05:37:45', '2020-06-22 05:37:45', 1, 1, 659),
(10, 'after insert', 5, 15, 'Pcs', 15, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 05:37:45', '2020-06-22 05:37:45', 1, 1, 660),
(11, 'after update', 4, 10, 'Pcs', 15, 'Pcs', 30000, '-', 'AKTIF', 2, 1, '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1, 756),
(12, 'after update', 5, 15, 'Pcs', 15, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1, 757),
(13, 'after insert', 6, 10, 'Pcs', 20, 'Pcs', 40000, '-', 'AKTIF', 4, 4, '2020-06-22 06:42:24', '2020-06-22 06:42:24', 1, 1, 818),
(14, 'after insert', 7, 10, 'Pcs', 20, 'Pcs', 40000, '-', 'AKTIF', 5, 4, '2020-06-22 06:42:38', '2020-06-22 06:42:38', 1, 1, 821),
(15, 'after insert', 8, 10, 'Pcs', 20, 'Pcs', 40000, '-', 'AKTIF', 6, 4, '2020-06-22 06:44:01', '2020-06-22 06:44:01', 1, 1, 824),
(16, 'after update', 1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1, 839),
(17, 'after update', 2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1, 840),
(18, 'after update', 3, 3, 'Pcs', 4, 'Pcs', 4000, '-', 'AKTIF', 1, 3, '2020-06-22 09:55:58', '2020-06-22 06:55:38', 1, 1, 841);

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
(1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:36', 1, 1),
(2, 100, '-', '2222-03-22', 'BELUM', 1, 2, '2020-06-22 05:49:10', '2020-06-22 05:49:10', 1, 1);

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

--
-- Dumping data for table `tbl_brg_permintaan_log`
--

INSERT INTO `tbl_brg_permintaan_log` (`id_pk_penerimaan_log`, `executed_function`, `id_pk_brg_permintaan`, `brg_permintaan_qty`, `brg_permintaan_notes`, `brg_permintaan_deadline`, `brg_permintaan_status`, `id_fk_brg`, `id_fk_cabang`, `brg_permintaan_create_date`, `brg_permintaan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, '-', '1111-11-11', 'BELUM', 1, 1, '2020-06-22 11:53:18', '2020-06-22 11:53:18', 1, 1, 439),
(2, 'after insert', 1, 3, '-', '1111-11-11', 'BELUM', 1, 1, '2020-06-22 11:53:18', '2020-06-22 11:53:39', 1, 1, 440),
(3, 'after insert', 1, 3, '-', '2222-02-22', 'BELUM', 1, 1, '2020-06-22 11:53:18', '2020-06-22 12:42:09', 1, 1, 493),
(4, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 08:37:11', 1, 1, 499),
(5, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 08:55:25', 1, 1, 501),
(6, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 09:20:19', 1, 1, 505),
(7, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 08:01:19', 1, 1, 562),
(8, 'after insert', 2, 100, '-', '2222-03-22', 'BELUM', 1, 2, '2020-06-22 05:49:10', '2020-06-22 05:49:10', 1, 1, 712),
(9, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:21', 1, 1, 713),
(10, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:35', 1, 1, 715),
(11, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:35', 1, 1, 717),
(12, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:36', 1, 1, 719),
(13, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:36', 1, 1, 721),
(14, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:36', 1, 1, 723),
(15, 'after insert', 1, 3, '-', '2222-02-22', 'SEDANG', 1, 1, '2020-06-22 11:53:18', '2020-06-22 10:50:36', 1, 1, 725);

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
(1, 'penjualan', 0, 2, 4, 1, 10, 'AKTIF', '2020-07-04 13:57:22', '2020-07-04 13:57:22', 1, 1),
(2, 'penjualan', 1, 2, 4, 1, 10, 'AKTIF', '2020-07-04 14:20:52', '2020-07-04 14:20:52', 1, 1),
(3, 'penjualan', 1, 4, 2, 1, 5, 'AKTIF', '2020-07-04 14:21:15', '2020-07-04 14:21:15', 1, 1);

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

            /*last lagi kerjain trigger ini - kurang id_satuan_keluar, dan id_satuan_terima. pokoknya harus dapet yang pcs*/
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

--
-- Dumping data for table `tbl_brg_pindah_log`
--

INSERT INTO `tbl_brg_pindah_log` (`id_pk_brg_pindah_log`, `executed_function`, `id_pk_brg_pindah`, `brg_pindah_sumber`, `id_fk_refrensi_sumber`, `id_brg_awal`, `id_brg_tujuan`, `id_fk_cabang`, `brg_pindah_qty`, `brg_pindah_status`, `brg_pindah_create_date`, `brg_pindah_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'penjualan', 0, 2, 4, 1, 10, 'AKTIF', '2020-07-04 13:57:22', '2020-07-04 13:57:22', 1, 1, 1799),
(2, 'after insert', 2, 'penjualan', 1, 2, 4, 1, 10, 'AKTIF', '2020-07-04 14:20:52', '2020-07-04 14:20:52', 1, 1, 1800),
(3, 'after insert', 3, 'penjualan', 1, 4, 2, 1, 5, 'AKTIF', '2020-07-04 14:21:15', '2020-07-04 14:21:15', 1, 1, 1805);

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
-- Dumping data for table `tbl_brg_warehouse`
--

INSERT INTO `tbl_brg_warehouse` (`id_pk_brg_warehouse`, `brg_warehouse_qty`, `brg_warehouse_notes`, `brg_warehouse_status`, `id_fk_brg`, `id_fk_warehouse`, `brg_warehouse_create_date`, `brg_warehouse_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 8, 'aaa', 'AKTIF', 1, 1, '2020-06-22 01:31:25', '2020-06-22 01:44:56', 1, 1);

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

--
-- Dumping data for table `tbl_brg_warehouse_log`
--

INSERT INTO `tbl_brg_warehouse_log` (`id_pk_brg_warehouse_log`, `executed_function`, `id_pk_brg_warehouse`, `brg_warehouse_qty`, `brg_warehouse_notes`, `brg_warehouse_status`, `id_fk_brg`, `id_fk_warehouse`, `brg_warehouse_create_date`, `brg_warehouse_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 88, 'aaa', 'AKTIF', 1, 1, '2020-06-22 01:31:25', '2020-06-22 01:31:25', 1, 1, 397),
(2, 'after update', 1, 8, 'aaa', 'AKTIF', 1, 1, '2020-06-22 01:31:25', '2020-06-22 01:44:56', 1, 1, 398);

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
(1, 1, 1, 'AKTIF', '2020-06-21 11:45:03', '2020-06-21 11:45:03', 1, 1),
(2, 2, 1, 'AKTIF', '2020-06-22 11:59:26', '2020-06-22 11:59:26', 1, 1),
(3, 1, 2, 'AKTIF', '2020-06-22 05:21:26', '2020-06-22 05:21:26', 1, 1),
(4, 1, 3, 'AKTIF', '2020-06-22 05:21:26', '2020-06-22 05:21:26', 1, 1),
(5, 2, 2, 'AKTIF', '2020-06-22 06:48:17', '2020-06-22 06:48:17', 1, 1),
(6, 2, 3, 'AKTIF', '2020-06-22 06:48:17', '2020-06-22 06:48:17', 1, 1);

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

--
-- Dumping data for table `tbl_cabang_admin_log`
--

INSERT INTO `tbl_cabang_admin_log` (`id_pk_cabang_admin_log`, `executed_function`, `id_pk_cabang_admin`, `id_fk_cabang`, `id_fk_user`, `cabang_admin_status`, `cabang_admin_create_date`, `cabang_admin_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'AKTIF', '2020-06-21 11:45:03', '2020-06-21 11:45:03', 1, 1, 91),
(2, 'after insert', 2, 2, 1, 'AKTIF', '2020-06-22 11:59:26', '2020-06-22 11:59:26', 1, 1, 442),
(3, 'after insert', 3, 1, 2, 'AKTIF', '2020-06-22 05:21:26', '2020-06-22 05:21:26', 1, 1, 611),
(4, 'after insert', 4, 1, 3, 'AKTIF', '2020-06-22 05:21:26', '2020-06-22 05:21:26', 1, 1, 612),
(5, 'after insert', 5, 2, 2, 'AKTIF', '2020-06-22 06:48:17', '2020-06-22 06:48:17', 1, 1, 835),
(6, 'after insert', 6, 2, 3, 'AKTIF', '2020-06-22 06:48:17', '2020-06-22 06:48:17', 1, 1, 836);

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
(108, 4, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1);

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

--
-- Dumping data for table `tbl_hak_akses_log`
--

INSERT INTO `tbl_hak_akses_log` (`id_pk_hak_akses_log`, `executed_function`, `id_pk_hak_akses`, `id_fk_jabatan`, `id_fk_menu`, `hak_akses_status`, `hak_akses_create_date`, `hak_akses_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 6),
(2, 'after insert', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 7),
(3, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 9),
(4, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 10),
(5, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 11),
(6, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 12),
(7, 'after insert', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 14),
(8, 'after insert', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 16),
(9, 'after insert', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 18),
(10, 'after insert', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 20),
(11, 'after insert', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 22),
(12, 'after insert', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 24),
(13, 'after insert', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 26),
(14, 'after insert', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 29),
(15, 'after insert', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 31),
(16, 'after insert', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 33),
(17, 'after insert', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 35),
(18, 'after insert', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 37),
(19, 'after insert', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 39),
(20, 'after insert', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 41),
(21, 'after insert', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 43),
(22, 'after insert', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 45),
(23, 'after insert', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 47),
(24, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 49),
(25, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 50),
(26, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 51),
(27, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 52),
(28, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 53),
(29, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 54),
(30, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 55),
(31, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 56),
(32, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 57),
(33, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 58),
(34, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 59),
(35, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 60),
(36, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 61),
(37, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 62),
(38, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 63),
(39, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 64),
(40, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 65),
(41, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 66),
(42, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 67),
(43, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 68),
(44, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 69),
(45, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 70),
(46, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 71),
(47, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 72),
(48, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 73),
(49, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 74),
(50, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 75),
(51, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 76),
(52, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 77),
(53, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 78),
(54, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 79),
(55, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 80),
(56, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 81),
(57, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 82),
(58, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 83),
(59, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 84),
(60, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 85),
(61, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 86),
(62, 'after insert', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 95),
(63, 'after insert', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 97),
(64, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 99),
(65, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 100),
(66, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 101),
(67, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 102),
(68, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 103),
(69, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 104),
(70, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 105),
(71, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 106),
(72, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 107),
(73, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 108),
(74, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 109),
(75, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 110),
(76, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 111),
(77, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 112),
(78, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 113),
(79, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 114),
(80, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 115),
(81, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 116),
(82, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 117),
(83, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 118),
(84, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 119),
(85, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 120),
(86, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 121),
(87, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 122),
(88, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 123),
(89, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 124),
(90, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 125),
(91, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 126),
(92, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 127),
(93, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 128),
(94, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 129),
(95, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 130),
(96, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 131),
(97, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 132),
(98, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 133),
(99, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 134),
(100, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 135),
(101, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 136),
(102, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 137),
(103, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 138),
(104, 'after update', 20, 1, 20, 'aktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 139),
(105, 'after insert', 22, 2, 1, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 141),
(106, 'after insert', 23, 2, 2, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 142),
(107, 'after insert', 24, 2, 3, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 143),
(108, 'after insert', 25, 2, 4, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 144),
(109, 'after insert', 26, 2, 5, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 145),
(110, 'after insert', 27, 2, 6, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 146),
(111, 'after insert', 28, 2, 7, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 147),
(112, 'after insert', 29, 2, 8, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 148),
(113, 'after insert', 30, 2, 9, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 149),
(114, 'after insert', 31, 2, 10, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 150),
(115, 'after insert', 32, 2, 11, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 151),
(116, 'after insert', 33, 2, 12, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 152),
(117, 'after insert', 34, 2, 13, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 153),
(118, 'after insert', 35, 2, 14, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 154),
(119, 'after insert', 36, 2, 15, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 155),
(120, 'after insert', 37, 2, 16, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 156),
(121, 'after insert', 38, 2, 17, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 157),
(122, 'after insert', 39, 2, 18, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 158),
(123, 'after insert', 40, 2, 19, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 159),
(124, 'after insert', 41, 2, 20, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 160),
(125, 'after insert', 42, 2, 21, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 161),
(126, 'after insert', 43, 3, 1, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 164),
(127, 'after insert', 44, 3, 2, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 165),
(128, 'after insert', 45, 3, 3, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 166),
(129, 'after insert', 46, 3, 4, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 167),
(130, 'after insert', 47, 3, 5, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 168),
(131, 'after insert', 48, 3, 6, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 169),
(132, 'after insert', 49, 3, 7, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 170),
(133, 'after insert', 50, 3, 8, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 171),
(134, 'after insert', 51, 3, 9, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 172),
(135, 'after insert', 52, 3, 10, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 173),
(136, 'after insert', 53, 3, 11, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 174),
(137, 'after insert', 54, 3, 12, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 175),
(138, 'after insert', 55, 3, 13, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 176),
(139, 'after insert', 56, 3, 14, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 177),
(140, 'after insert', 57, 3, 15, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 178),
(141, 'after insert', 58, 3, 16, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 179),
(142, 'after insert', 59, 3, 17, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 180),
(143, 'after insert', 60, 3, 18, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 181),
(144, 'after insert', 61, 3, 19, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 182),
(145, 'after insert', 62, 3, 20, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 183),
(146, 'after insert', 63, 3, 21, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 184),
(147, 'after insert', 64, 4, 1, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 186),
(148, 'after insert', 65, 4, 2, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 187),
(149, 'after insert', 66, 4, 3, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 188),
(150, 'after insert', 67, 4, 4, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 189),
(151, 'after insert', 68, 4, 5, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 190),
(152, 'after insert', 69, 4, 6, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 191),
(153, 'after insert', 70, 4, 7, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 192),
(154, 'after insert', 71, 4, 8, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 193),
(155, 'after insert', 72, 4, 9, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 194),
(156, 'after insert', 73, 4, 10, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 195),
(157, 'after insert', 74, 4, 11, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 196),
(158, 'after insert', 75, 4, 12, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 197),
(159, 'after insert', 76, 4, 13, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 198),
(160, 'after insert', 77, 4, 14, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 199),
(161, 'after insert', 78, 4, 15, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 200),
(162, 'after insert', 79, 4, 16, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 201),
(163, 'after insert', 80, 4, 17, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 202),
(164, 'after insert', 81, 4, 18, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 203),
(165, 'after insert', 82, 4, 19, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 204),
(166, 'after insert', 83, 4, 20, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 205),
(167, 'after insert', 84, 4, 21, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 206),
(168, 'after update', 64, 4, 1, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 207),
(169, 'after update', 65, 4, 2, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 208),
(170, 'after insert', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 444),
(171, 'after insert', 86, 2, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 445),
(172, 'after insert', 87, 3, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 446),
(173, 'after insert', 88, 4, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 447),
(174, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 449),
(175, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 450),
(176, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 451),
(177, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 452),
(178, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 453),
(179, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 454),
(180, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 455),
(181, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 456),
(182, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 457),
(183, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 458),
(184, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 459),
(185, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 460),
(186, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 461),
(187, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 462),
(188, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 463),
(189, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 464),
(190, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 465),
(191, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 466),
(192, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 467),
(193, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 468),
(194, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 469),
(195, 'after update', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 470),
(196, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 471),
(197, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 472),
(198, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 473),
(199, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 474),
(200, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 475),
(201, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 476),
(202, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 477),
(203, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 478),
(204, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 479),
(205, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 480),
(206, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 481),
(207, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 482),
(208, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 483),
(209, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 484),
(210, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 485),
(211, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 486),
(212, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 487),
(213, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 488),
(214, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 489),
(215, 'after update', 20, 1, 20, 'aktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 490),
(216, 'after update', 85, 1, 22, 'aktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 491),
(217, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 513),
(218, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 514),
(219, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 515),
(220, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 516),
(221, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 517),
(222, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 518),
(223, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 519),
(224, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 520),
(225, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 521),
(226, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 522),
(227, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 523),
(228, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 524),
(229, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 525),
(230, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 526),
(231, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 527),
(232, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 528),
(233, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 529),
(234, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 530),
(235, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 531),
(236, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 532),
(237, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 533),
(238, 'after update', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 534),
(239, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 535),
(240, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 536),
(241, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 537),
(242, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 538),
(243, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 539),
(244, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 540),
(245, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 541),
(246, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 542),
(247, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 543),
(248, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 544),
(249, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 545),
(250, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 546),
(251, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 547),
(252, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 548),
(253, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 549),
(254, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 550),
(255, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 551),
(256, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 552),
(257, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 553),
(258, 'after update', 21, 1, 21, 'aktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 554),
(259, 'after update', 22, 2, 1, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 572),
(260, 'after update', 23, 2, 2, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 573),
(261, 'after update', 24, 2, 3, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 574),
(262, 'after update', 25, 2, 4, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 575),
(263, 'after update', 26, 2, 5, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 576),
(264, 'after update', 27, 2, 6, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 577),
(265, 'after update', 28, 2, 7, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 578),
(266, 'after update', 29, 2, 8, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 579),
(267, 'after update', 30, 2, 9, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 580),
(268, 'after update', 31, 2, 10, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 581),
(269, 'after update', 32, 2, 11, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 582),
(270, 'after update', 33, 2, 12, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 583),
(271, 'after update', 34, 2, 13, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 584),
(272, 'after update', 35, 2, 14, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 585),
(273, 'after update', 36, 2, 15, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 586),
(274, 'after update', 37, 2, 16, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 587),
(275, 'after update', 38, 2, 17, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 588),
(276, 'after update', 39, 2, 18, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 589),
(277, 'after update', 40, 2, 19, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 590),
(278, 'after update', 41, 2, 20, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 591),
(279, 'after update', 42, 2, 21, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 592),
(280, 'after update', 86, 2, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 593),
(281, 'after update', 22, 2, 1, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 594),
(282, 'after update', 24, 2, 3, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 595),
(283, 'after update', 22, 2, 1, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 614),
(284, 'after update', 23, 2, 2, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 615),
(285, 'after update', 24, 2, 3, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 616),
(286, 'after update', 25, 2, 4, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 617),
(287, 'after update', 26, 2, 5, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 618),
(288, 'after update', 27, 2, 6, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 619),
(289, 'after update', 28, 2, 7, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 620),
(290, 'after update', 29, 2, 8, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 621),
(291, 'after update', 30, 2, 9, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 622),
(292, 'after update', 31, 2, 10, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 623),
(293, 'after update', 32, 2, 11, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 624),
(294, 'after update', 33, 2, 12, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 625),
(295, 'after update', 34, 2, 13, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 626),
(296, 'after update', 35, 2, 14, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 627),
(297, 'after update', 36, 2, 15, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 628),
(298, 'after update', 37, 2, 16, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 629),
(299, 'after update', 38, 2, 17, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 630),
(300, 'after update', 39, 2, 18, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 631),
(301, 'after update', 40, 2, 19, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 632),
(302, 'after update', 41, 2, 20, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 633),
(303, 'after update', 42, 2, 21, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 634),
(304, 'after update', 86, 2, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 635),
(305, 'after update', 22, 2, 1, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 636),
(306, 'after update', 24, 2, 3, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 637),
(307, 'after update', 42, 2, 21, 'aktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 638),
(308, 'after insert', 89, 1, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 767),
(309, 'after insert', 90, 2, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 768),
(310, 'after insert', 91, 3, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 769),
(311, 'after insert', 92, 4, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 770),
(312, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 772),
(313, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 773),
(314, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 774),
(315, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 775),
(316, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 776),
(317, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 777),
(318, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 778),
(319, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 779),
(320, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 780),
(321, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 781),
(322, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 782),
(323, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 783),
(324, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 784),
(325, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 785),
(326, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 786),
(327, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 787),
(328, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 788),
(329, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 789),
(330, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 790),
(331, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 791),
(332, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 792),
(333, 'after update', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 793),
(334, 'after update', 89, 1, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 794),
(335, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 795),
(336, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 796),
(337, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 797),
(338, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 798),
(339, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 799),
(340, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 800),
(341, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 801),
(342, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 802),
(343, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 803),
(344, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 804),
(345, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 805),
(346, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 806),
(347, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 807),
(348, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 808),
(349, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 809),
(350, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 810),
(351, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 811),
(352, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 812),
(353, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 813),
(354, 'after update', 21, 1, 21, 'aktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 814),
(355, 'after update', 89, 1, 23, 'aktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 815),
(356, 'after insert', 93, 1, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1104),
(357, 'after insert', 94, 2, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1105),
(358, 'after insert', 95, 3, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1106),
(359, 'after insert', 96, 4, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1107),
(360, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1109),
(361, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1110),
(362, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1111),
(363, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1112),
(364, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1113),
(365, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1114),
(366, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1115),
(367, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1116),
(368, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1117),
(369, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1118),
(370, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1119),
(371, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1120),
(372, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1121),
(373, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1122),
(374, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1123),
(375, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1124),
(376, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1125),
(377, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1126),
(378, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1127),
(379, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 1128),
(380, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1129),
(381, 'after update', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 1130),
(382, 'after update', 89, 1, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1131),
(383, 'after update', 93, 1, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1132),
(384, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1133),
(385, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1134),
(386, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1135),
(387, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1136),
(388, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1137),
(389, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1138),
(390, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1139),
(391, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1140),
(392, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1141),
(393, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1142),
(394, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1143),
(395, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1144),
(396, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1145),
(397, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1146),
(398, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1147),
(399, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1148),
(400, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1149),
(401, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1150),
(402, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1151),
(403, 'after update', 21, 1, 21, 'aktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1152),
(404, 'after update', 89, 1, 23, 'aktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1153),
(405, 'after update', 93, 1, 24, 'aktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1154),
(406, 'after insert', 97, 1, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1156),
(407, 'after insert', 98, 2, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1157),
(408, 'after insert', 99, 3, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1158),
(409, 'after insert', 100, 4, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1159),
(410, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1161),
(411, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1162),
(412, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1163),
(413, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1164),
(414, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1165),
(415, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1166),
(416, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1167),
(417, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1168),
(418, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1169),
(419, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1170),
(420, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1171),
(421, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1172),
(422, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1173),
(423, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1174),
(424, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1175),
(425, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1176),
(426, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1177),
(427, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1178),
(428, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1179),
(429, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 1180),
(430, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1181),
(431, 'after update', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 1182),
(432, 'after update', 89, 1, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1183),
(433, 'after update', 93, 1, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1184),
(434, 'after update', 97, 1, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1185),
(435, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1186),
(436, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1187),
(437, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1188),
(438, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1189),
(439, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1190),
(440, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1191),
(441, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1192),
(442, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1193),
(443, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1194),
(444, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1195),
(445, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1196),
(446, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1197),
(447, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1198),
(448, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1199),
(449, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1200),
(450, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1201),
(451, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1202),
(452, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1203),
(453, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1204),
(454, 'after update', 21, 1, 21, 'aktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1205),
(455, 'after update', 89, 1, 23, 'aktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1206),
(456, 'after update', 93, 1, 24, 'aktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1207),
(457, 'after update', 97, 1, 25, 'aktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1208),
(458, 'after insert', 101, 1, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1416),
(459, 'after insert', 102, 2, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1417),
(460, 'after insert', 103, 3, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1418),
(461, 'after insert', 104, 4, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1419),
(462, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1421),
(463, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1422),
(464, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1423),
(465, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1424),
(466, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1425),
(467, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1426),
(468, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1427),
(469, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1428),
(470, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1429),
(471, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1430),
(472, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1431),
(473, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1432),
(474, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1433),
(475, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1434),
(476, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1435),
(477, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1436),
(478, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1437),
(479, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1438),
(480, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1439),
(481, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 1440),
(482, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1441),
(483, 'after update', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 1442),
(484, 'after update', 89, 1, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1443),
(485, 'after update', 93, 1, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1444),
(486, 'after update', 97, 1, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1445),
(487, 'after update', 101, 1, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1446),
(488, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1447),
(489, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1448),
(490, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1449),
(491, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1450),
(492, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1451),
(493, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1452),
(494, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1453),
(495, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1454),
(496, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1455),
(497, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1456),
(498, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1457),
(499, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1458),
(500, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1459),
(501, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1460);
INSERT INTO `tbl_hak_akses_log` (`id_pk_hak_akses_log`, `executed_function`, `id_pk_hak_akses`, `id_fk_jabatan`, `id_fk_menu`, `hak_akses_status`, `hak_akses_create_date`, `hak_akses_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(502, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1461),
(503, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1462),
(504, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1463),
(505, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1464),
(506, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1465),
(507, 'after update', 21, 1, 21, 'aktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1466),
(508, 'after update', 89, 1, 23, 'aktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1467),
(509, 'after update', 93, 1, 24, 'aktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1468),
(510, 'after update', 97, 1, 25, 'aktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1469),
(511, 'after update', 101, 1, 26, 'aktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1470),
(512, 'after insert', 105, 1, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1, 1638),
(513, 'after insert', 106, 2, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1, 1639),
(514, 'after insert', 107, 3, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1, 1640),
(515, 'after insert', 108, 4, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1, 1641),
(516, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1643),
(517, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1644),
(518, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1645),
(519, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1646),
(520, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1647),
(521, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1648),
(522, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1649),
(523, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1650),
(524, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1651),
(525, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1652),
(526, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1653),
(527, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1654),
(528, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1655),
(529, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1656),
(530, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1657),
(531, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1658),
(532, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1659),
(533, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1660),
(534, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1661),
(535, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 1662),
(536, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1663),
(537, 'after update', 85, 1, 22, 'nonaktif', '2020-06-22 12:32:52', '2020-06-22 12:32:52', 1, 1, 1664),
(538, 'after update', 89, 1, 23, 'nonaktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1665),
(539, 'after update', 93, 1, 24, 'nonaktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1666),
(540, 'after update', 97, 1, 25, 'nonaktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1667),
(541, 'after update', 101, 1, 26, 'nonaktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1668),
(542, 'after update', 105, 1, 27, 'nonaktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1, 1669),
(543, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1670),
(544, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 1671),
(545, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 1672),
(546, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 1673),
(547, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 1674),
(548, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 1675),
(549, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 1676),
(550, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 1677),
(551, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 1678),
(552, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 1679),
(553, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 1680),
(554, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 1681),
(555, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 1682),
(556, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 1683),
(557, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 1684),
(558, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 1685),
(559, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 1686),
(560, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 1687),
(561, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 1688),
(562, 'after update', 21, 1, 21, 'aktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 1689),
(563, 'after update', 89, 1, 23, 'aktif', '2020-06-22 06:10:33', '2020-06-22 06:10:33', 1, 1, 1690),
(564, 'after update', 93, 1, 24, 'aktif', '2020-06-26 10:07:22', '2020-06-26 10:07:22', 1, 1, 1691),
(565, 'after update', 97, 1, 25, 'aktif', '2020-06-27 07:36:59', '2020-06-27 07:36:59', 1, 1, 1692),
(566, 'after update', 101, 1, 26, 'aktif', '2020-06-30 09:26:26', '2020-06-30 09:26:26', 1, 1, 1693),
(567, 'after update', 105, 1, 27, 'aktif', '2020-07-02 11:03:30', '2020-07-02 11:03:30', 1, 1, 1694);

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
(1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 06:55:38', 1, 1),
(2, '1', '12345678', 'JNE', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1);

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

--
-- Dumping data for table `tbl_penjualan_online_log`
--

INSERT INTO `tbl_penjualan_online_log` (`id_pk_penjualan_online_log`, `executed_function`, `id_pk_penjualan_online`, `penj_on_marketplace`, `penj_on_no_resi`, `penj_on_kurir`, `penj_on_status`, `id_fk_penjualan`, `penj_on_create_date`, `penj_on_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 09:39:50', 1, 1, 319),
(2, 'after update', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 09:53:30', 1, 1, 327),
(3, 'after update', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 09:55:58', 1, 1, 329),
(4, 'after update', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 10:05:28', 1, 1, 339),
(5, 'after insert', 2, 'Tokopedia', '12345678', 'JNE', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-22 05:37:45', 1, 1, 658),
(6, 'after update', 2, 'Tokopedia', '12345678', 'JNE', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1, 755),
(7, 'after update', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 06:55:38', 1, 1, 838),
(8, 'after update', 2, '1', '12345678', 'JNE', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:05:32', 1, 1, 1215),
(9, 'after update', 2, '2', '12345678', 'JNE', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:07:37', 1, 1, 1221),
(10, 'after update', 2, '1', '12345678', 'JNE', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1, 1226);

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
-- Dumping data for table `tbl_penjualan_pembayaran`
--

INSERT INTO `tbl_penjualan_pembayaran` (`id_pk_penjualan_pembayaran`, `id_fk_penjualan`, `penjualan_pmbyrn_nama`, `penjualan_pmbyrn_persen`, `penjualan_pmbyrn_nominal`, `penjualan_pmbyrn_notes`, `penjualan_pmbyrn_dateline`, `penjualan_pmbyrn_status`, `penjualan_pmbyrn_create_date`, `penjualan_pmbyrn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'Pembayaran DP', 50, 62000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1),
(2, 1, 'Pelunasan 1', 25, 31000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1),
(3, 1, 'Pelunasan 2', 25, 31000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:55:58', '2020-06-22 06:55:38', 1, 1),
(4, 2, 'Pembayaran 1', 50, 527500, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1),
(5, 2, 'Pembayaran 2', 50, 527500, '-', '3333-03-31 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1);

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

--
-- Dumping data for table `tbl_penjualan_pembayaran_log`
--

INSERT INTO `tbl_penjualan_pembayaran_log` (`id_pk_penjualan_pembayaran_log`, `executed_function`, `id_pk_penjualan_pembayaran`, `id_fk_penjualan`, `penjualan_pmbyrn_nama`, `penjualan_pmbyrn_persen`, `penjualan_pmbyrn_nominal`, `penjualan_pmbyrn_notes`, `penjualan_pmbyrn_dateline`, `penjualan_pmbyrn_status`, `penjualan_pmbyrn_create_date`, `penjualan_pmbyrn_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 'Pembayaran DP', 50, 52000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 323),
(2, 'after insert', 2, 1, 'Pelunasan', 50, 52000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 324),
(3, 'after insert', 3, 1, 'Pelunasan 2', 25, 26000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:55:58', '2020-06-22 09:55:58', 1, 1, 335),
(4, 'after update', 1, 1, 'Pembayaran DP', 50, 52000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 336),
(5, 'after update', 2, 1, 'Pelunasan 1', 25, 26000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 337),
(6, 'after update', 1, 1, 'Pembayaran DP', 50, 62000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 345),
(7, 'after update', 2, 1, 'Pelunasan 1', 25, 31000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 346),
(8, 'after update', 3, 1, 'Pelunasan 2', 25, 31000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1, 347),
(9, 'after insert', 4, 2, 'Pembayaran 1', 50, 527500, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-22 05:37:45', 1, 1, 662),
(10, 'after insert', 5, 2, 'Pembayaran 2', 50, 527500, '-', '3333-03-31 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-22 05:37:45', 1, 1, 663),
(11, 'after update', 4, 2, 'Pembayaran 1', 50, 527500, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1, 759),
(12, 'after update', 5, 2, 'Pembayaran 2', 50, 527500, '-', '3333-03-31 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1, 760),
(13, 'after update', 1, 1, 'Pembayaran DP', 50, 62000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1, 844),
(14, 'after update', 2, 1, 'Pelunasan 1', 25, 31000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1, 845),
(15, 'after update', 3, 1, 'Pelunasan 2', 25, 31000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:55:58', '2020-06-22 06:55:38', 1, 1, 846),
(16, 'after update', 4, 2, 'Pembayaran 1', 50, 527500, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:05:32', 1, 1, 1217),
(17, 'after update', 5, 2, 'Pembayaran 2', 50, 527500, '-', '3333-03-31 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:05:32', 1, 1, 1218),
(18, 'after update', 4, 2, 'Pembayaran 1', 50, 527500, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:07:37', 1, 1, 1223),
(19, 'after update', 5, 2, 'Pembayaran 2', 50, 527500, '-', '3333-03-31 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:07:37', 1, 1, 1224),
(20, 'after update', 4, 2, 'Pembayaran 1', 50, 527500, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1, 1228),
(21, 'after update', 5, 2, 'Pembayaran 2', 50, 527500, '-', '3333-03-31 00:00:00', 'AKTIF', '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1, 1229);

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
(1, 1, 1, 0, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1),
(2, 1, 2, 1, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1),
(3, 1, 3, 1, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1),
(4, 2, 1, 2, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1),
(5, 2, 2, 3, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1),
(6, 2, 3, 2, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1),
(7, 3, 1, 5, 'Pcs', '-', 'aktif', '2020-06-23 03:17:44', '2020-06-23 03:17:44', 1, 1),
(8, 3, 3, 10, 'Pcs', '-', 'aktif', '2020-06-23 03:17:44', '2020-06-23 03:17:44', 1, 1),
(9, 4, 1, 10, 'Pcs', '-', 'aktif', '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1),
(10, 4, 3, 15, 'Pcs', '-', 'aktif', '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1);

--
-- Triggers `tbl_retur_brg`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_retur` AFTER INSERT ON `tbl_retur_brg` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_brg_log(executed_function,id_pk_retur_brg,id_fk_retur,id_fk_brg,retur_brg_qty,retur_brg_satuan,retur_brg_notes,retur_brg_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_retur_brg,new.id_fk_retur,new.id_fk_brg,new.retur_brg_qty,new.retur_brg_satuan,new.retur_brg_notes,new.retur_brg_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_retur` AFTER UPDATE ON `tbl_retur_brg` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_brg_log(executed_function,id_pk_retur_brg,id_fk_retur,id_fk_brg,retur_brg_qty,retur_brg_satuan,retur_brg_notes,retur_brg_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_retur_brg,new.id_fk_retur,new.id_fk_brg,new.retur_brg_qty,new.retur_brg_satuan,new.retur_brg_notes,new.retur_brg_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

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

--
-- Dumping data for table `tbl_retur_brg_log`
--

INSERT INTO `tbl_retur_brg_log` (`id_pk_retur_log`, `executed_function`, `id_pk_retur_brg`, `id_fk_retur`, `id_fk_brg`, `retur_brg_qty`, `retur_brg_satuan`, `retur_brg_notes`, `retur_brg_status`, `retur_create_date`, `retur_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 0, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:52:54', 1, 1, 426),
(2, 'after insert', 2, 1, 2, 1, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:52:54', 1, 1, 427),
(3, 'after insert', 3, 1, 3, 0, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:52:54', 1, 1, 428),
(4, 'after update', 1, 1, 1, 0, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1, 430),
(5, 'after update', 2, 1, 2, 1, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1, 431),
(6, 'after update', 3, 1, 3, 1, 'Pcs', '-', 'aktif', '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1, 432),
(7, 'after insert', 4, 2, 1, 2, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:45:19', 1, 1, 704),
(8, 'after insert', 5, 2, 2, 3, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:45:19', 1, 1, 705),
(9, 'after insert', 6, 2, 3, 2, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:45:19', 1, 1, 706),
(10, 'after update', 4, 2, 1, 2, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1, 708),
(11, 'after update', 5, 2, 2, 3, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1, 709),
(12, 'after update', 6, 2, 3, 2, 'Pcs', '-', 'aktif', '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1, 710),
(13, 'after insert', 7, 3, 1, 5, 'Pcs', '-', 'aktif', '2020-06-23 03:17:44', '2020-06-23 03:17:44', 1, 1, 864),
(14, 'after insert', 8, 3, 3, 10, 'Pcs', '-', 'aktif', '2020-06-23 03:17:44', '2020-06-23 03:17:44', 1, 1, 865),
(15, 'after insert', 9, 4, 1, 10, 'Pcs', '-', 'aktif', '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1, 867),
(16, 'after insert', 10, 4, 3, 15, 'Pcs', '-', 'aktif', '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1, 868);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_retur_kembali`
--

CREATE TABLE `tbl_retur_kembali` (
  `id_pk_retur_kembali` int(11) NOT NULL,
  `retur_kembali_qty_real` double DEFAULT NULL,
  `retur_kembali_satuan_real` varchar(20) DEFAULT NULL,
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

INSERT INTO `tbl_retur_kembali` (`id_pk_retur_kembali`, `retur_kembali_qty_real`, `retur_kembali_satuan_real`, `retur_kembali_qty`, `retur_kembali_satuan`, `retur_kembali_harga`, `retur_kembali_note`, `retur_kembali_status`, `id_fk_retur`, `id_fk_brg`, `retur_kembali_create_date`, `retur_kembali_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 2, 'Pcs', 3, 'Pcs', 20000, '-', 'aktif', 1, 3, '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1),
(2, 4, 'Pcs', 5, 'Pcs', 30000, '-', 'aktif', 2, 4, '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1),
(3, 4, 'Pcs', 4, 'Pcs', 10000, '-', 'aktif', 3, 4, '2020-06-23 03:17:45', '2020-06-23 03:17:45', 1, 1),
(4, 4, 'Pcs', 4, 'Pcs', 10000, '-', 'aktif', 4, 4, '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1),
(5, 5, 'Pcs', 10, 'Pcs', 20000, '-', 'aktif', 4, 5, '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1),
(6, 10, 'Pcs', 10, 'Pcs', 50000, '-', 'aktif', 4, 6, '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1);

--
-- Triggers `tbl_retur_kembali`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_retur_kembali` AFTER INSERT ON `tbl_retur_kembali` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty_real,retur_kembali_satuan_real,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_retur_kembali,new.retur_kembali_qty_real,new.retur_kembali_satuan_real,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_retur_kembali` AFTER UPDATE ON `tbl_retur_kembali` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty_real,retur_kembali_satuan_real,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_retur_kembali,new.retur_kembali_qty_real,new.retur_kembali_satuan_real,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
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
  `retur_kembali_qty_real` double DEFAULT NULL,
  `retur_kembali_satuan_real` varchar(20) DEFAULT NULL,
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

--
-- Dumping data for table `tbl_retur_kembali_log`
--

INSERT INTO `tbl_retur_kembali_log` (`id_pk_retur_kembali_log`, `executed_function`, `id_pk_retur_kembali`, `retur_kembali_qty_real`, `retur_kembali_satuan_real`, `retur_kembali_qty`, `retur_kembali_satuan`, `retur_kembali_harga`, `retur_kembali_note`, `retur_kembali_status`, `id_fk_retur`, `id_fk_brg`, `retur_kembali_create_date`, `retur_kembali_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 2, 'Pcs', 2, 'Pcs', 20000, '-', 'aktif', 1, 3, '2020-06-22 10:52:54', '2020-06-22 10:52:54', 1, 1, 429),
(2, 'after update', 1, 2, 'Pcs', 3, 'Pcs', 20000, '-', 'aktif', 1, 3, '2020-06-22 10:52:54', '2020-06-22 10:54:41', 1, 1, 433),
(3, 'after insert', 2, 4, 'Pcs', 5, 'Pcs', 30000, '-', 'aktif', 2, 4, '2020-06-22 05:45:19', '2020-06-22 05:45:19', 1, 1, 707),
(4, 'after update', 2, 4, 'Pcs', 5, 'Pcs', 30000, '-', 'aktif', 2, 4, '2020-06-22 05:45:19', '2020-06-22 05:46:58', 1, 1, 711),
(5, 'after insert', 3, 4, 'Pcs', 4, 'Pcs', 10000, '-', 'aktif', 3, 4, '2020-06-23 03:17:45', '2020-06-23 03:17:45', 1, 1, 866),
(6, 'after insert', 4, 4, 'Pcs', 4, 'Pcs', 10000, '-', 'aktif', 4, 4, '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1, 869),
(7, 'after insert', 5, 5, 'Pcs', 10, 'Pcs', 20000, '-', 'aktif', 4, 5, '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1, 870),
(8, 'after insert', 6, 10, 'Pcs', 10, 'Pcs', 50000, '-', 'aktif', 4, 6, '2020-06-23 03:19:56', '2020-06-23 03:19:56', 1, 1, 871);

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
-- Dumping data for table `tbl_tambahan_pembelian`
--

INSERT INTO `tbl_tambahan_pembelian` (`id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_pembelian`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'TAMBAHAN 1', 1, 'Pcs', 12000, '-', 'AKTIF', 1, '2020-06-22 08:18:34', '0000-00-00 00:00:00', 20, 1),
(2, 'PARKIR', 1, 'Jam', 4000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1),
(3, 'Kurir', 1, 'Trip', 13000, '-', 'AKTIF', 3, '2020-06-22 05:28:46', '0000-00-00 00:00:00', 20, 1);

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

--
-- Dumping data for table `tbl_tambahan_pembelian_log`
--

INSERT INTO `tbl_tambahan_pembelian_log` (`id_pk_tmbhn_log`, `executed_function`, `id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_pembelian`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'TAMBAHAN 1', 1, 'Pcs', 12000, '-', 'AKTIF', 1, '2020-06-22 08:18:34', '0000-00-00 00:00:00', 20, 1, 242),
(2, 'after insert', 2, 'PARKIR', 1, 'Jam', 4000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1, 253),
(3, 'after update', 2, 'PARKIR', 1, 'Jam', 40000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1, 257),
(4, 'after update', 2, 'PARKIR', 1, 'Jam', 4000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1, 262),
(5, 'after insert', 3, 'Kurir', 1, 'Trip', 13000, '-', 'AKTIF', 3, '2020-06-22 05:28:46', '0000-00-00 00:00:00', 20, 1, 645);

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
(1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1),
(2, 'Kurir', 1, 'Trip', 4000, '-', 'AKTIF', 1, '2020-06-22 09:55:58', '2020-06-22 06:55:38', 1, 1),
(3, 'Uang Parkir', 1, 'Jam', 5000, '-', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1),
(4, 'tambahan', 1, 'pcs', 1000, '-', 'AKTIF', 4, '2020-06-22 06:42:24', '2020-06-22 06:42:24', 1, 1),
(5, 'tambahan', 1, 'pcs', 1000, '-', 'AKTIF', 5, '2020-06-22 06:42:38', '2020-06-22 06:42:38', 1, 1),
(6, 'tambahan', 1, 'pcs', 1000, '-', 'AKTIF', 6, '2020-06-22 06:44:01', '2020-06-22 06:44:01', 1, 1);

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

--
-- Dumping data for table `tbl_tambahan_penjualan_log`
--

INSERT INTO `tbl_tambahan_penjualan_log` (`id_pk_tmbhn_log`, `executed_function`, `id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_penjualan`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 322),
(2, 'after insert', 2, 'Kurir', 1, 'Trip', 4000, '-', 'AKTIF', 1, '2020-06-22 09:55:58', '2020-06-22 09:55:58', 1, 1, 333),
(3, 'after update', 1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 334),
(4, 'after update', 1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 343),
(5, 'after update', 2, 'Kurir', 1, 'Trip', 4000, '-', 'AKTIF', 1, '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1, 344),
(6, 'after insert', 3, 'Uang Parkir', 1, 'Jam', 5000, '-', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-22 05:37:45', 1, 1, 661),
(7, 'after update', 3, 'Uang Parkir', 1, 'Jam', 5000, '-', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-22 06:05:31', 1, 1, 758),
(8, 'after insert', 4, 'tambahan', 1, 'pcs', 1000, '-', 'AKTIF', 4, '2020-06-22 06:42:24', '2020-06-22 06:42:24', 1, 1, 819),
(9, 'after insert', 5, 'tambahan', 1, 'pcs', 1000, '-', 'AKTIF', 5, '2020-06-22 06:42:38', '2020-06-22 06:42:38', 1, 1, 822),
(10, 'after insert', 6, 'tambahan', 1, 'pcs', 1000, '-', 'AKTIF', 6, '2020-06-22 06:44:01', '2020-06-22 06:44:01', 1, 1, 825),
(11, 'after update', 1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 06:55:38', 1, 1, 842),
(12, 'after update', 2, 'Kurir', 1, 'Trip', 4000, '-', 'AKTIF', 1, '2020-06-22 09:55:58', '2020-06-22 06:55:38', 1, 1, 843),
(13, 'after update', 3, 'Uang Parkir', 1, 'Jam', 5000, '-', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:05:32', 1, 1, 1216),
(14, 'after update', 3, 'Uang Parkir', 1, 'Jam', 5000, '-', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:07:37', 1, 1, 1222),
(15, 'after update', 3, 'Uang Parkir', 1, 'Jam', 5000, '-', 'AKTIF', 2, '2020-06-22 05:37:45', '2020-06-27 11:07:50', 1, 1, 1227);

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
(3, 1, 2, 'AKTIF', '2020-06-22 05:20:09', '2020-06-22 05:20:09', 1, 1),
(4, 1, 3, 'AKTIF', '2020-06-22 05:20:09', '2020-06-22 05:20:09', 1, 1),
(5, 2, 2, 'AKTIF', '2020-06-22 06:47:48', '2020-06-22 06:47:48', 1, 1),
(6, 2, 3, 'AKTIF', '2020-06-22 06:47:48', '2020-06-22 06:47:48', 1, 1);

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

--
-- Dumping data for table `tbl_toko_admin_log`
--

INSERT INTO `tbl_toko_admin_log` (`id_pk_toko_admin_log`, `executed_function`, `id_pk_toko_admin`, `id_fk_toko`, `id_fk_user`, `toko_admin_status`, `toko_admin_create_date`, `toko_admin_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'AKTIF', '2020-06-21 11:44:24', '2020-06-21 11:44:24', 1, 1, 89),
(2, 'after insert', 2, 2, 1, 'AKTIF', '2020-06-22 02:59:25', '2020-06-22 02:59:25', 1, 1, 556),
(3, 'after insert', 3, 1, 2, 'AKTIF', '2020-06-22 05:20:09', '2020-06-22 05:20:09', 1, 1, 609),
(4, 'after insert', 4, 1, 3, 'AKTIF', '2020-06-22 05:20:09', '2020-06-22 05:20:09', 1, 1, 610),
(5, 'after insert', 5, 2, 2, 'AKTIF', '2020-06-22 06:47:48', '2020-06-22 06:47:48', 1, 1, 833),
(6, 'after insert', 6, 2, 3, 'AKTIF', '2020-06-22 06:47:48', '2020-06-22 06:47:48', 1, 1, 834);

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
(1, 1, 1, 'AKTIF', '2020-06-21 11:45:53', '2020-06-21 11:45:53', 1, 1);

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
-- Table structure for table `tbl_warehouse_admin_log`
--

CREATE TABLE `tbl_warehouse_admin_log` (
  `id_pk_warehouse_admin_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_warehouse_admin` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `warehouse_admin_status` varchar(15) DEFAULT NULL,
  `warehouse_admin_create_date` datetime DEFAULT NULL,
  `warehouse_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_warehouse_admin_log`
--

INSERT INTO `tbl_warehouse_admin_log` (`id_pk_warehouse_admin_log`, `executed_function`, `id_pk_warehouse_admin`, `id_fk_warehouse`, `id_fk_user`, `warehouse_admin_status`, `warehouse_admin_create_date`, `warehouse_admin_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'AKTIF', '2020-06-21 11:45:53', '2020-06-21 11:45:53', 1, 1, 93);

-- --------------------------------------------------------


--
-- Structure for view `v_brg_cabang_aktif`
--
DROP TABLE IF EXISTS `v_brg_cabang_aktif`;

CREATE VIEW `v_brg_cabang_aktif`  AS  select `tbl_brg_cabang`.`id_pk_brg_cabang` AS `id_pk_brg_cabang`,`tbl_brg_cabang`.`brg_cabang_qty` AS `brg_cabang_qty`,`tbl_brg_cabang`.`brg_cabang_status` AS `brg_cabang_status`,`tbl_brg_cabang`.`brg_cabang_last_price` AS `brg_cabang_last_price`,`tbl_brg_cabang`.`id_fk_brg` AS `id_fk_brg`,`tbl_brg_cabang`.`id_fk_cabang` AS `id_fk_cabang`,`mstr_barang`.`brg_nama` AS `brg_nama` from (`tbl_brg_cabang` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_brg_cabang`.`id_fk_brg`)) where `tbl_brg_cabang`.`brg_cabang_status` = 'aktif' and `mstr_barang`.`brg_status` = 'aktif' order by `tbl_brg_cabang`.`id_fk_brg`,`tbl_brg_cabang`.`id_fk_cabang` ;

-- --------------------------------------------------------

--
-- Structure for view `v_brg_kombinasi_final`
--
DROP TABLE IF EXISTS `v_brg_kombinasi_final`;

CREATE VIEW `v_brg_kombinasi_final`  AS  select `tbl_barang_kombinasi`.`id_pk_barang_kombinasi` AS `id_pk_barang_kombinasi`,`tbl_barang_kombinasi`.`id_barang_utama` AS `id_barang_utama`,`tbl_barang_kombinasi`.`id_barang_kombinasi` AS `id_barang_kombinasi`,sum(`tbl_barang_kombinasi`.`barang_kombinasi_qty`) AS `barang_kombinasi_qty`,`tbl_barang_kombinasi`.`barang_kombinasi_status` AS `barang_kombinasi_status` from (`tbl_barang_kombinasi` join `mstr_barang` on(`mstr_barang`.`id_pk_brg` = `tbl_barang_kombinasi`.`id_barang_kombinasi`)) where `tbl_barang_kombinasi`.`barang_kombinasi_status` = 'aktif' and `mstr_barang`.`brg_status` = 'aktif' group by `tbl_barang_kombinasi`.`id_barang_utama`,`tbl_barang_kombinasi`.`id_barang_kombinasi` ;

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
  MODIFY `id_log_all` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2047;

--
-- AUTO_INCREMENT for table `mstr_barang`
--
ALTER TABLE `mstr_barang`
  MODIFY `id_pk_brg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  MODIFY `id_pk_brg_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mstr_barang_jenis_log`
--
ALTER TABLE `mstr_barang_jenis_log`
  MODIFY `id_pk_brg_jenis_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mstr_barang_log`
--
ALTER TABLE `mstr_barang_log`
  MODIFY `id_pk_brg_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  MODIFY `id_pk_brg_merk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mstr_barang_merk_log`
--
ALTER TABLE `mstr_barang_merk_log`
  MODIFY `id_pk_brg_merk_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  MODIFY `id_pk_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_cabang_log`
--
ALTER TABLE `mstr_cabang_log`
  MODIFY `id_pk_cabang_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mstr_customer`
--
ALTER TABLE `mstr_customer`
  MODIFY `id_pk_cust` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mstr_customer_log`
--
ALTER TABLE `mstr_customer_log`
  MODIFY `id_pk_cust_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mstr_employee`
--
ALTER TABLE `mstr_employee`
  MODIFY `id_pk_employee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_employee_log`
--
ALTER TABLE `mstr_employee_log`
  MODIFY `id_pk_employee_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mstr_jabatan`
--
ALTER TABLE `mstr_jabatan`
  MODIFY `id_pk_jabatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mstr_jabatan_log`
--
ALTER TABLE `mstr_jabatan_log`
  MODIFY `id_pk_jabatan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `mstr_marketplace`
--
ALTER TABLE `mstr_marketplace`
  MODIFY `id_pk_marketplace` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_marketplace_log`
--
ALTER TABLE `mstr_marketplace_log`
  MODIFY `id_pk_marketplace_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mstr_menu`
--
ALTER TABLE `mstr_menu`
  MODIFY `id_pk_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `mstr_menu_log`
--
ALTER TABLE `mstr_menu_log`
  MODIFY `id_pk_menu_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `mstr_pembelian`
--
ALTER TABLE `mstr_pembelian`
  MODIFY `id_pk_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mstr_pembelian_log`
--
ALTER TABLE `mstr_pembelian_log`
  MODIFY `id_pk_pembelian_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mstr_penerimaan`
--
ALTER TABLE `mstr_penerimaan`
  MODIFY `id_pk_penerimaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `mstr_penerimaan_log`
--
ALTER TABLE `mstr_penerimaan_log`
  MODIFY `id_pk_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `mstr_pengiriman`
--
ALTER TABLE `mstr_pengiriman`
  MODIFY `id_pk_pengiriman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `mstr_pengiriman_log`
--
ALTER TABLE `mstr_pengiriman_log`
  MODIFY `id_pk_pengiriman_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `mstr_penjualan`
--
ALTER TABLE `mstr_penjualan`
  MODIFY `id_pk_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mstr_penjualan_log`
--
ALTER TABLE `mstr_penjualan_log`
  MODIFY `id_pk_penjualan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `mstr_retur`
--
ALTER TABLE `mstr_retur`
  MODIFY `id_pk_retur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mstr_retur_log`
--
ALTER TABLE `mstr_retur_log`
  MODIFY `id_pk_retur_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mstr_satuan`
--
ALTER TABLE `mstr_satuan`
  MODIFY `id_pk_satuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mstr_satuan_log`
--
ALTER TABLE `mstr_satuan_log`
  MODIFY `id_pk_satuan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id_pk_sup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mstr_supplier_log`
--
ALTER TABLE `mstr_supplier_log`
  MODIFY `id_pk_sup_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id_pk_toko` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mstr_toko_log`
--
ALTER TABLE `mstr_toko_log`
  MODIFY `id_pk_toko_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mstr_user`
--
ALTER TABLE `mstr_user`
  MODIFY `id_pk_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mstr_user_log`
--
ALTER TABLE `mstr_user_log`
  MODIFY `id_pk_user_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  MODIFY `id_pk_warehouse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_warehouse_log`
--
ALTER TABLE `mstr_warehouse_log`
  MODIFY `id_pk_warehouse_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  MODIFY `id_pk_barang_kombinasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_barang_kombinasi_log`
--
ALTER TABLE `tbl_barang_kombinasi_log`
  MODIFY `id_pk_barang_kombinasi_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tbl_barang_ukuran`
--
ALTER TABLE `tbl_barang_ukuran`
  MODIFY `ID_PK_BARANG_UKURAN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  MODIFY `id_pk_brg_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_brg_cabang_log`
--
ALTER TABLE `tbl_brg_cabang_log`
  MODIFY `id_pk_brg_cabang_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=566;

--
-- AUTO_INCREMENT for table `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  MODIFY `id_pk_brg_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_brg_pembelian_log`
--
ALTER TABLE `tbl_brg_pembelian_log`
  MODIFY `id_pk_brg_pembelian_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_brg_pemenuhan`
--
ALTER TABLE `tbl_brg_pemenuhan`
  MODIFY `id_pk_brg_pemenuhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_brg_pemenuhan_log`
--
ALTER TABLE `tbl_brg_pemenuhan_log`
  MODIFY `id_pk_brg_pemenuhan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `tbl_brg_penerimaan`
--
ALTER TABLE `tbl_brg_penerimaan`
  MODIFY `id_pk_brg_penerimaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_brg_penerimaan_log`
--
ALTER TABLE `tbl_brg_penerimaan_log`
  MODIFY `id_pk_brg_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tbl_brg_pengiriman`
--
ALTER TABLE `tbl_brg_pengiriman`
  MODIFY `id_pk_brg_pengiriman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_brg_pengiriman_log`
--
ALTER TABLE `tbl_brg_pengiriman_log`
  MODIFY `id_pk_brg_pengiriman_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_brg_penjualan`
--
ALTER TABLE `tbl_brg_penjualan`
  MODIFY `id_pk_brg_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_brg_penjualan_log`
--
ALTER TABLE `tbl_brg_penjualan_log`
  MODIFY `id_pk_brg_penjualan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_brg_permintaan`
--
ALTER TABLE `tbl_brg_permintaan`
  MODIFY `id_pk_brg_permintaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_brg_permintaan_log`
--
ALTER TABLE `tbl_brg_permintaan_log`
  MODIFY `id_pk_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_brg_pindah`
--
ALTER TABLE `tbl_brg_pindah`
  MODIFY `id_pk_brg_pindah` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_brg_pindah_log`
--
ALTER TABLE `tbl_brg_pindah_log`
  MODIFY `id_pk_brg_pindah_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id_pk_brg_warehouse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_brg_warehouse_log`
--
ALTER TABLE `tbl_brg_warehouse_log`
  MODIFY `id_pk_brg_warehouse_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_cabang_admin`
--
ALTER TABLE `tbl_cabang_admin`
  MODIFY `id_pk_cabang_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_cabang_admin_log`
--
ALTER TABLE `tbl_cabang_admin_log`
  MODIFY `id_pk_cabang_admin_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_hak_akses`
--
ALTER TABLE `tbl_hak_akses`
  MODIFY `id_pk_hak_akses` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `tbl_hak_akses_log`
--
ALTER TABLE `tbl_hak_akses_log`
  MODIFY `id_pk_hak_akses_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=568;

--
-- AUTO_INCREMENT for table `tbl_penjualan_online`
--
ALTER TABLE `tbl_penjualan_online`
  MODIFY `id_pk_penjualan_online` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_penjualan_online_log`
--
ALTER TABLE `tbl_penjualan_online_log`
  MODIFY `id_pk_penjualan_online_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_penjualan_pembayaran`
--
ALTER TABLE `tbl_penjualan_pembayaran`
  MODIFY `id_pk_penjualan_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_penjualan_pembayaran_log`
--
ALTER TABLE `tbl_penjualan_pembayaran_log`
  MODIFY `id_pk_penjualan_pembayaran_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_retur_brg`
--
ALTER TABLE `tbl_retur_brg`
  MODIFY `id_pk_retur_brg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_retur_brg_log`
--
ALTER TABLE `tbl_retur_brg_log`
  MODIFY `id_pk_retur_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_retur_kembali`
--
ALTER TABLE `tbl_retur_kembali`
  MODIFY `id_pk_retur_kembali` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_retur_kembali_log`
--
ALTER TABLE `tbl_retur_kembali_log`
  MODIFY `id_pk_retur_kembali_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_tambahan_pembelian_log`
--
ALTER TABLE `tbl_tambahan_pembelian_log`
  MODIFY `id_pk_tmbhn_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_tambahan_penjualan`
--
ALTER TABLE `tbl_tambahan_penjualan`
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_tambahan_penjualan_log`
--
ALTER TABLE `tbl_tambahan_penjualan_log`
  MODIFY `id_pk_tmbhn_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_toko_admin`
--
ALTER TABLE `tbl_toko_admin`
  MODIFY `id_pk_toko_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_toko_admin_log`
--
ALTER TABLE `tbl_toko_admin_log`
  MODIFY `id_pk_toko_admin_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_warehouse_admin`
--
ALTER TABLE `tbl_warehouse_admin`
  MODIFY `id_pk_warehouse_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_warehouse_admin_log`
--
ALTER TABLE `tbl_warehouse_admin_log`
  MODIFY `id_pk_warehouse_admin_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
